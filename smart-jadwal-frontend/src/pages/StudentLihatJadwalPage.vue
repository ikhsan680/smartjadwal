<template>
  <div class="container-fluid py-2 student-lihat-jadwal-page">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body py-3">
        <div class="schedule-controls">
          <div class="mode-toggle-group">
            <button
              @click="setViewMode('kelas-saya')"
              class="btn btn-sm"
              :class="viewMode === 'kelas-saya' ? 'btn-primary' : 'btn-outline-primary'"
              :disabled="loading || !kelasId"
            >
              Kelas Saya
            </button>
            <button
              @click="setViewMode('semua-kelas')"
              class="btn btn-sm"
              :class="viewMode === 'semua-kelas' ? 'btn-primary' : 'btn-outline-primary'"
              :disabled="loading"
            >
              Semua Kelas
            </button>
          </div>

          <div v-if="studentClass" class="selected-class-pill">
            {{ studentClass }}
          </div>

          <div class="quick-actions-group">
            <button
              @click="openFullscreen"
              class="btn btn-sm btn-outline-primary utility-btn"
              :disabled="loading || !canShowTable"
              title="Fullscreen"
            >
              <i class="bi bi-arrows-fullscreen"></i>
              <span class="utility-label">Fullscreen</span>
            </button>
            <button
              @click="refreshData"
              class="btn btn-sm btn-outline-secondary utility-btn"
              :disabled="loading"
              title="Refresh"
            >
              <i class="bi bi-arrow-clockwise"></i>
              <span class="utility-label">Refresh</span>
            </button>
            <button @click="goBack" class="btn btn-sm btn-outline-secondary utility-btn" title="Kembali">
              <i class="bi bi-arrow-left"></i>
              <span class="utility-label">Kembali</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="viewMode === 'kelas-saya' && kelasId && jadwalList.length > 0" class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr class="bg-light">
                <th class="p-2 text-center fw-semibold" style="width: 80px;">Jam</th>
                <th v-for="hariName in hari" :key="hariName" class="p-2 text-center fw-semibold">
                  {{ hariName }}
                </th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(slot, slotIndex) in timeSlots" :key="slot.id">
                <tr v-if="shouldRenderSingleKelasSlotRow(slot, slotIndex)" class="align-middle">
                  <td class="p-2 text-center fw-semibold text-primary" style="width: 120px; font-size: 0.85rem;">
                    {{ slot.jam_mulai }} - {{ slot.jam_selesai }}
                  </td>
                  <template v-for="(hariName, hariIndex) in hari" :key="`${slot.id}-${hariName}`">
                    <td
                      v-if="!shouldSkipSlotSingleKelas(hariName, slotIndex) && !shouldSkipHorizontalMergeSingleKelas(slotIndex, hariIndex)"
                      :rowspan="getRowspanForSlotSingleKelas(hariName, slotIndex)"
                      :colspan="getHorizontalMergeColspanSingleKelas(slotIndex, hariIndex)"
                      class="align-middle"
                      :style="{
                        verticalAlign: 'middle',
                        minHeight: getRowspanForSlotSingleKelas(hariName, slotIndex) > 1 ? '60px' : '40px',
                        padding: getMergedScheduleSingleKelas(hariName, slotIndex)?.kegiatan_id ? '0' : '8px',
                        ...(getMergedScheduleSingleKelas(hariName, slotIndex)?.kegiatan_id ? getKegiatanStyle(getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id) : {})
                      }"
                    >
                      <div
                        v-if="getMergedScheduleSingleKelas(hariName, slotIndex)"
                        class="schedule-cell"
                        :class="{ 'schedule-cell-kegiatan': getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id }"
                      >
                        <div
                          v-if="getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id"
                          class="w-100 h-100 d-flex align-items-center justify-content-center text-sm fw-semibold px-2"
                          :style="{ color: getKegiatanStyle(getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id).color }"
                        >
                          {{ getKegiatanDisplayName(getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id) }}
                        </div>
                        <div v-else class="w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                          <div v-if="getMergedScheduleSingleKelas(hariName, slotIndex).mapel_id" class="text-sm fw-semibold text-dark text-center w-100">
                            {{ getMapelDisplayName(getMergedScheduleSingleKelas(hariName, slotIndex).mapel_id) }}
                          </div>
                          <div
                            v-if="getMergedScheduleSingleKelas(hariName, slotIndex).mapel_id && getMergedScheduleSingleKelas(hariName, slotIndex).guru_id"
                            class="text-xs text-muted text-center w-100"
                          >
                            {{ getGuruName(getMergedScheduleSingleKelas(hariName, slotIndex).guru_id) }}
                          </div>
                        </div>
                      </div>
                    </td>
                  </template>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-else-if="viewMode === 'semua-kelas' && allKelasJadwalList.length > 0" class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive-semua-hari">
          <table class="table mb-0 table-semua-hari">
            <thead>
              <tr class="bg-light">
                <th class="p-1 text-center fw-semibold" style="width: 70px;"><div class="text-xl">Hari</div></th>
                <th class="p-1 text-center fw-semibold" style="width: 120px;"><div class="text-xl">Jam</div></th>
                <template v-for="tier in getTierKeys" :key="tier">
                  <th
                    :colspan="groupKelasByTier[tier]?.length || 1"
                    class="p-1 text-center fw-semibold text-xs"
                    :style="{ backgroundColor: getTierColor(tier).bg, color: getTierColor(tier).text }"
                  >
                    <div class="fw-bold">Kelas {{ getTierName(tier) }}</div>
                  </th>
                </template>
              </tr>
              <tr class="bg-light">
                <th style="width: 70px;"></th>
                <th style="width: 120px;"></th>
                <template v-for="tier in getTierKeys" :key="`names-${tier}`">
                  <template v-for="kelas in groupKelasByTier[tier]" :key="`${tier}-${kelas.id}`">
                    <th
                      class="p-1 text-center fw-semibold"
                      style="min-width: 50px; border-bottom: 2px solid #dee2e6; font-size: 0.70rem; white-space: nowrap;"
                      :style="{ borderRightColor: getTierColor(tier).bg }"
                    >
                      {{ kelas.nama }}
                    </th>
                  </template>
                </template>
              </tr>
            </thead>
            <tbody>
              <template v-for="hariName in hari" :key="hariName">
                <tr v-for="(slot, slotIndex) in allTimeSlots" :key="`${hariName}-${slot.id}`" class="align-middle">
                  <td
                    v-if="slotIndex === 0"
                    :rowspan="allTimeSlots.length"
                    class="p-2 text-center fw-semibold text-primary"
                    style="width: 70px; vertical-align: middle; border-right: 2px solid #dee2e6;"
                  >
                    <div class="text-xs fw-bold">{{ hariName }}</div>
                  </td>

                  <td
                    v-if="!shouldSkipJamSlot(hariName, slotIndex)"
                    :rowspan="getJamRowspan(hariName, slotIndex)"
                    class="p-1 text-center fw-semibold text-primary"
                    style="width: 120px; font-size: 0.8rem; border-right: 2px solid #dee2e6; white-space: nowrap;"
                  >
                    {{ slot.jam_mulai }} - {{ slot.jam_selesai }}
                  </td>

                  <template v-if="isCrossTierMerged(hariName, slot.id) && !shouldSkipKegiatanSlot(hariName, slotIndex)">
                    <td
                      :colspan="getCrossTierMergeColspan(hariName, slot.id)"
                      :rowspan="getKegiatanRowspan(hariName, slotIndex)"
                      class="text-center fw-semibold"
                      style="vertical-align: middle; background-color: transparent; padding: 0; width: 50px; min-width: 50px;"
                    >
                      <div class="schedule-cell-all schedule-cell-kegiatan" :style="getKegiatanStyle(getCrossTierMergeKegiatanId(hariName, slot.id))">
                        <div class="text-xs fw-semibold w-100 text-center kegiatan-wrap">
                          <span v-for="(line, lineIdx) in formatKegiatanLines(getKegiatanName(getCrossTierMergeKegiatanId(hariName, slot.id)))" :key="lineIdx" class="kegiatan-line">
                            {{ line }}
                          </span>
                        </div>
                      </div>
                    </td>
                  </template>

                  <template v-else v-for="tier in getTierKeys" :key="`tier-${tier}`">
                    <template v-for="(kelas, tierKelasIdx) in groupKelasByTier[tier]" :key="`${hariName}-${slot.id}-${kelas.id}`">
                      <template v-if="!isVerticalMergeSkipped(hariName, slotIndex, tier, tierKelasIdx)">
                        <td
                          v-if="shouldRenderMergedSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                          :colspan="getMergedSpanSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                          :rowspan="shouldRenderVerticalMergeCell(hariName, slotIndex, tier, tierKelasIdx) ? getVerticalMergeRowspan(hariName, slotIndex, tier, tierKelasIdx) : 1"
                          class="text-center fw-semibold"
                          style="vertical-align: middle; background-color: transparent; padding: 0; width: 50px; min-width: 50px;"
                        >
                          <div
                            class="schedule-cell-all schedule-cell-kegiatan"
                            :style="getKegiatanStyle(getMergedKegiatanIdSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx))"
                          >
                            <div class="text-xs fw-semibold w-100 text-center kegiatan-wrap">
                              <span v-for="(line, lineIdx) in formatKegiatanLines(getKegiatanName(getMergedKegiatanIdSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)))" :key="lineIdx" class="kegiatan-line">
                                {{ line }}
                              </span>
                            </div>
                          </div>
                        </td>

                        <td
                          v-else-if="!isPartOfMergedSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                          :rowspan="shouldRenderVerticalMergeCell(hariName, slotIndex, tier, tierKelasIdx) ? getVerticalMergeRowspan(hariName, slotIndex, tier, tierKelasIdx) : 1"
                          class="text-center"
                          :style="{
                            backgroundColor: getScheduleForAllKelas(slot.id, hariName, kelas.id)?.kegiatan_id ? 'transparent' : getTierColor(tier).bg,
                            minWidth: '50px',
                            verticalAlign: 'middle',
                            padding: getScheduleForAllKelas(slot.id, hariName, kelas.id)?.kegiatan_id ? '0' : '0.5rem'
                          }"
                        >
                          <div
                            v-if="getScheduleForAllKelas(slot.id, hariName, kelas.id)"
                            class="schedule-cell-all"
                            :style="getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id ? getKegiatanStyle(getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id) : {}"
                          >
                            <div v-if="getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id" class="text-xs fw-semibold px-1 py-1 kegiatan-wrap">
                              <span v-for="(line, lineIdx) in formatKegiatanLines(getKegiatanName(getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id))" :key="lineIdx" class="kegiatan-line">
                                {{ line }}
                              </span>
                            </div>
                            <div v-else class="fw-bold" style="font-size: 0.65rem;" :style="{ color: getTierColor(tier).text }">
                              {{ getGuruKode(getScheduleForAllKelas(slot.id, hariName, kelas.id)) }}
                            </div>
                          </div>
                        </td>
                      </template>
                    </template>
                  </template>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-else-if="viewMode === 'kelas-saya' && kelasId && !loading && jadwalList.length === 0" class="alert alert-info">
      <i class="bi bi-info-circle me-2"></i> Tidak ada jadwal untuk kelas ini
    </div>

    <div v-else-if="viewMode === 'semua-kelas' && !loading && allKelasJadwalList.length === 0" class="alert alert-warning">
      <i class="bi bi-exclamation-triangle me-2"></i>
      Tidak ada jadwal untuk ditampilkan.
    </div>

    <div v-else-if="loading" class="alert alert-info">
      <i class="bi bi-hourglass-split me-2"></i> Memuat data...
    </div>

    <div v-else class="alert alert-warning">
      <i class="bi bi-exclamation-triangle me-2"></i>
      Silakan kembali ke dashboard dan pilih kelas terlebih dahulu.
    </div>

    <div v-if="showFullscreen" class="fullscreen-modal-overlay" @click.self="closeFullscreen">
      <div class="fullscreen-modal-content">
        <div class="fullscreen-header">
          <div class="fullscreen-title">
            <h5 class="mb-0">Jadwal Pelajaran - {{ fullscreenTitle }}</h5>
          </div>
          <div class="fullscreen-controls">
            <button @click="downloadPDF" class="btn btn-sm btn-outline-success me-2" title="Download as PDF">
              <i class="bi bi-file-pdf"></i> Download PDF
            </button>
            <button @click="closeFullscreen" class="btn btn-sm btn-outline-danger" title="Tutup fullscreen">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>

        <div class="fullscreen-body">
          <div ref="fullscreenTableContent">
            <div v-if="viewMode === 'kelas-saya'">
              <h6 class="mb-2">{{ studentClass }}</h6>
              <table class="table table-sm mb-0 fullscreen-table">
                <thead>
                  <tr class="bg-light">
                    <th class="p-2 text-center fw-semibold" style="width: 100px;">Jam</th>
                    <th v-for="hariName in hari" :key="hariName" class="p-2 text-center fw-semibold">{{ hariName }}</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-for="(slot, slotIndex) in timeSlots" :key="slot.id">
                    <tr v-if="shouldRenderSingleKelasSlotRow(slot, slotIndex)" class="align-middle">
                      <td class="p-2 text-center fw-semibold text-primary" style="width: 100px; font-size: 0.9rem;">
                        {{ slot.jam_mulai }} - {{ slot.jam_selesai }}
                      </td>
                      <template v-for="(hariName, hariIndex) in hari" :key="`${slot.id}-${hariName}`">
                        <td
                          v-if="!shouldSkipSlotSingleKelas(hariName, slotIndex) && !shouldSkipHorizontalMergeSingleKelas(slotIndex, hariIndex)"
                          :rowspan="getRowspanForSlotSingleKelas(hariName, slotIndex)"
                          :colspan="getHorizontalMergeColspanSingleKelas(slotIndex, hariIndex)"
                          class="align-middle p-2"
                          :style="{
                            verticalAlign: 'middle',
                            minHeight: getRowspanForSlotSingleKelas(hariName, slotIndex) > 1 ? '70px' : '50px',
                            ...(getMergedScheduleSingleKelas(hariName, slotIndex)?.kegiatan_id ? getKegiatanStyle(getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id) : {})
                          }"
                        >
                          <div v-if="getMergedScheduleSingleKelas(hariName, slotIndex)" class="text-center">
                            <div
                              v-if="getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id"
                              class="fw-semibold"
                              :style="{ color: getKegiatanStyle(getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id).color }"
                            >
                              {{ getKegiatanDisplayName(getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id) }}
                            </div>
                            <div v-else>
                              <div v-if="getMergedScheduleSingleKelas(hariName, slotIndex).mapel_id" class="fw-semibold text-dark">
                                {{ getMapelDisplayName(getMergedScheduleSingleKelas(hariName, slotIndex).mapel_id) }}
                              </div>
                              <div v-if="getMergedScheduleSingleKelas(hariName, slotIndex).guru_id" class="text-muted small">
                                {{ getGuruName(getMergedScheduleSingleKelas(hariName, slotIndex).guru_id) }}
                              </div>
                            </div>
                          </div>
                        </td>
                      </template>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>

            <div v-else>
              <div class="table-responsive-semua-hari">
                <table class="table mb-0 table-semua-hari">
                  <thead>
                    <tr class="bg-light">
                      <th class="p-1 text-center fw-semibold" style="width: 70px;"><div class="text-xl">Hari</div></th>
                      <th class="p-1 text-center fw-semibold" style="width: 120px;"><div class="text-xl">Jam</div></th>
                      <template v-for="tier in getTierKeys" :key="tier">
                        <th
                          :colspan="groupKelasByTier[tier]?.length || 1"
                          class="p-1 text-center fw-semibold text-xs"
                          :style="{ backgroundColor: getTierColor(tier).bg, color: getTierColor(tier).text }"
                        >
                          <div class="fw-bold">Kelas {{ getTierName(tier) }}</div>
                        </th>
                      </template>
                    </tr>
                    <tr class="bg-light">
                      <th style="width: 70px;"></th>
                      <th style="width: 120px;"></th>
                      <template v-for="tier in getTierKeys" :key="`names-fs-${tier}`">
                        <template v-for="kelas in groupKelasByTier[tier]" :key="`fs-${tier}-${kelas.id}`">
                          <th
                            class="p-1 text-center fw-semibold"
                            style="min-width: 50px; border-bottom: 2px solid #dee2e6; font-size: 0.70rem; white-space: nowrap;"
                            :style="{ borderRightColor: getTierColor(tier).bg }"
                          >
                            {{ kelas.nama }}
                          </th>
                        </template>
                      </template>
                    </tr>
                  </thead>
                  <tbody>
                    <template v-for="hariName in hari" :key="`fs-${hariName}`">
                      <tr v-for="(slot, slotIndex) in allTimeSlots" :key="`fs-${hariName}-${slot.id}`" class="align-middle">
                        <td
                          v-if="slotIndex === 0"
                          :rowspan="allTimeSlots.length"
                          class="p-2 text-center fw-semibold text-primary"
                          style="width: 70px; vertical-align: middle; border-right: 2px solid #dee2e6;"
                        >
                          <div class="text-xs fw-bold">{{ hariName }}</div>
                        </td>

                        <td
                          v-if="!shouldSkipJamSlot(hariName, slotIndex)"
                          :rowspan="getJamRowspan(hariName, slotIndex)"
                          class="p-1 text-center fw-semibold text-primary"
                          style="width: 120px; font-size: 0.8rem; border-right: 2px solid #dee2e6; white-space: nowrap;"
                        >
                          {{ slot.jam_mulai }} - {{ slot.jam_selesai }}
                        </td>

                        <template v-if="isCrossTierMerged(hariName, slot.id) && !shouldSkipKegiatanSlot(hariName, slotIndex)">
                          <td
                            :colspan="getCrossTierMergeColspan(hariName, slot.id)"
                            :rowspan="getKegiatanRowspan(hariName, slotIndex)"
                            class="text-center fw-semibold"
                            style="vertical-align: middle; background-color: transparent; padding: 0; width: 50px; min-width: 50px;"
                          >
                            <div class="schedule-cell-all schedule-cell-kegiatan" :style="getKegiatanStyle(getCrossTierMergeKegiatanId(hariName, slot.id))">
                              <div class="text-xs fw-semibold w-100 text-center kegiatan-wrap">
                                <span v-for="(line, lineIdx) in formatKegiatanLines(getKegiatanName(getCrossTierMergeKegiatanId(hariName, slot.id)))" :key="lineIdx" class="kegiatan-line">
                                  {{ line }}
                                </span>
                              </div>
                            </div>
                          </td>
                        </template>

                        <template v-else v-for="tier in getTierKeys" :key="`fs-tier-${tier}`">
                          <template v-for="(kelas, tierKelasIdx) in groupKelasByTier[tier]" :key="`fs-${hariName}-${slot.id}-${kelas.id}`">
                            <template v-if="!isVerticalMergeSkipped(hariName, slotIndex, tier, tierKelasIdx)">
                              <td
                                v-if="shouldRenderMergedSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                                :colspan="getMergedSpanSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                                :rowspan="shouldRenderVerticalMergeCell(hariName, slotIndex, tier, tierKelasIdx) ? getVerticalMergeRowspan(hariName, slotIndex, tier, tierKelasIdx) : 1"
                                class="text-center fw-semibold"
                                style="vertical-align: middle; background-color: transparent; padding: 0; width: 50px; min-width: 50px;"
                              >
                                <div
                                  class="schedule-cell-all schedule-cell-kegiatan"
                                  :style="getKegiatanStyle(getMergedKegiatanIdSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx))"
                                >
                                  <div class="text-xs fw-semibold w-100 text-center kegiatan-wrap">
                                    <span v-for="(line, lineIdx) in formatKegiatanLines(getKegiatanName(getMergedKegiatanIdSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)))" :key="lineIdx" class="kegiatan-line">
                                      {{ line }}
                                    </span>
                                  </div>
                                </div>
                              </td>

                              <td
                                v-else-if="!isPartOfMergedSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                                :rowspan="shouldRenderVerticalMergeCell(hariName, slotIndex, tier, tierKelasIdx) ? getVerticalMergeRowspan(hariName, slotIndex, tier, tierKelasIdx) : 1"
                                class="text-center"
                                :style="{
                                  backgroundColor: getScheduleForAllKelas(slot.id, hariName, kelas.id)?.kegiatan_id ? 'transparent' : getTierColor(tier).bg,
                                  minWidth: '50px',
                                  verticalAlign: 'middle',
                                  padding: getScheduleForAllKelas(slot.id, hariName, kelas.id)?.kegiatan_id ? '0' : '0.5rem'
                                }"
                              >
                                <div
                                  v-if="getScheduleForAllKelas(slot.id, hariName, kelas.id)"
                                  class="schedule-cell-all"
                                  :style="getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id ? getKegiatanStyle(getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id) : {}"
                                >
                                  <div v-if="getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id" class="text-xs fw-semibold px-1 py-1 kegiatan-wrap">
                                    <span v-for="(line, lineIdx) in formatKegiatanLines(getKegiatanName(getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id))" :key="lineIdx" class="kegiatan-line">
                                      {{ line }}
                                    </span>
                                  </div>
                                  <div v-else class="fw-bold" style="font-size: 0.65rem;" :style="{ color: getTierColor(tier).text }">
                                    {{ getGuruKode(getScheduleForAllKelas(slot.id, hariName, kelas.id)) }}
                                  </div>
                                </div>
                              </td>
                            </template>
                          </template>
                        </template>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { jsPDF } from 'jspdf'
import html2canvas from 'html2canvas'
import { jadwalService, kelasService, mapelService, kegiatanService } from '../services/resourceService'

const MAPEL_KODE_ORDER_STORAGE_KEY = 'smart-jadwal-mapel-kode-order-v1'

const router = useRouter()

const studentClass = ref('Belum dipilih')
const kelasId = ref(null)
const viewMode = ref('kelas-saya')
const kelasOptions = ref([])
const jadwalList = ref([])
const allKelasJadwalList = ref([])
const mapelList = ref([])
const kegiatanList = ref([])
const loading = ref(false)
const showFullscreen = ref(false)
const fullscreenTableContent = ref(null)
const mergedCellSemuaHariCache = ref({})
const verticalMergeCache = ref({})
const verticalMergeCacheSingleKelas = ref({})
const horizontalMergeSingleKelasCache = ref({})
const getJamMergeSemauHariCache = ref({})
const getKegiatanMergeSemauHariCache = ref({})

const hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']

const canShowTable = computed(() => {
  if (viewMode.value === 'kelas-saya') return jadwalList.value.length > 0
  return allKelasJadwalList.value.length > 0
})

const fullscreenTitle = computed(() => {
  return viewMode.value === 'semua-kelas' ? 'SEMUA KELAS' : studentClass.value
})

const clearCaches = () => {
  mergedCellSemuaHariCache.value = {}
  verticalMergeCache.value = {}
  verticalMergeCacheSingleKelas.value = {}
  horizontalMergeSingleKelasCache.value = {}
  getJamMergeSemauHariCache.value = {}
  getKegiatanMergeSemauHariCache.value = {}
}

const setViewMode = (mode) => {
  viewMode.value = mode
  clearCaches()
}

const goBack = () => {
  router.push('/siswa/dashboard')
}

const openFullscreen = () => {
  if (!canShowTable.value) return
  showFullscreen.value = true
}

const closeFullscreen = () => {
  showFullscreen.value = false
}

const timeToMinutes = (timeStr) => {
  if (!timeStr) return 0
  const [hours, minutes] = timeStr.split(':').map(Number)
  return hours * 60 + minutes
}

const getScheduleForAllKelas = (slotId, hariName, selectedKelasId) => {
  const [jam_mulai] = slotId.split('-')
  const slotStart = timeToMinutes(jam_mulai)

  return allKelasJadwalList.value.find(
    (j) =>
      j.kelas_id === selectedKelasId &&
      j.hari === hariName &&
      timeToMinutes(j.jam_mulai) <= slotStart &&
      timeToMinutes(j.jam_selesai) > slotStart
  )
}

const getMapelName = (mapelId) => {
  const mapel = mapelList.value.find((m) => m.id === mapelId)
  return mapel ? mapel.nama : ''
}

const getMapelDisplayName = (mapelId) => {
  const mapelName = getMapelName(mapelId)
  return mapelName || 'Pelajaran'
}

const getKegiatanName = (kegiatanId) => {
  if (!kegiatanId) return ''
  const kegiatan = kegiatanList.value.find((k) => k.id === kegiatanId)
  return kegiatan ? kegiatan.nama : ''
}

const getKegiatanDisplayName = (kegiatanId) => {
  const kegiatanName = getKegiatanName(kegiatanId)
  return kegiatanName || 'Kegiatan'
}

const formatKegiatanLines = (name) => {
  const text = String(name || '').trim()
  if (!text) return ['']

  const words = text.split(/\s+/)
  const lines = []
  const maxCharsPerLine = 8
  let currentLine = ''

  words.forEach((word) => {
    const nextLine = currentLine ? `${currentLine} ${word}` : word
    if (!currentLine || nextLine.length <= maxCharsPerLine) {
      currentLine = nextLine
    } else {
      lines.push(currentLine)
      currentLine = word
    }
  })

  if (currentLine) lines.push(currentLine)
  return lines
}

const getGuruName = (guruId) => {
  if (!guruId) return ''
  for (const mapel of mapelList.value) {
    if (mapel.guru && Array.isArray(mapel.guru)) {
      const guru = mapel.guru.find((g) => g.id === guruId)
      if (guru) return guru.nama
    }
  }
  return ''
}

const getKegiatanStyle = (kegiatanId) => {
  const colors = [
    { bg: '#FFE8E8', text: '#C1272D' },
    { bg: '#FFF3E0', text: '#E65100' },
    { bg: '#FFF9C4', text: '#F57F17' },
    { bg: '#E8F5E9', text: '#2E7D32' },
    { bg: '#E3F2FD', text: '#1565C0' },
    { bg: '#F3E5F5', text: '#6A1B9A' },
    { bg: '#FCE4EC', text: '#C2185B' }
  ]

  const colorIndex = (kegiatanId - 1) % colors.length
  const color = colors[colorIndex]

  return {
    backgroundColor: color.bg,
    color: color.text
  }
}

const getCurrentGuruIds = () => {
  const guruIds = new Set()
  mapelList.value.forEach((mapel) => {
    if (mapel.guru && Array.isArray(mapel.guru)) {
      mapel.guru.forEach((guru) => {
        const guruId = Number(guru.id)
        if (Number.isInteger(guruId) && guruId > 0) guruIds.add(guruId)
      })
    }
  })
  return Array.from(guruIds).sort((a, b) => a - b)
}

const getStoredGuruOrder = () => {
  try {
    const raw = localStorage.getItem(MAPEL_KODE_ORDER_STORAGE_KEY)
    const parsed = raw ? JSON.parse(raw) : []
    if (!Array.isArray(parsed)) return []
    return parsed.map((item) => Number(item)).filter((item) => Number.isInteger(item) && item > 0)
  } catch {
    return []
  }
}

const getFinalGuruOrder = () => {
  const currentGuruIds = getCurrentGuruIds()
  if (currentGuruIds.length === 0) return []

  const storedGuruIds = getStoredGuruOrder()
  const validStored = storedGuruIds.filter((id) => currentGuruIds.includes(id))
  const newGuruIds = currentGuruIds.filter((id) => !validStored.includes(id))
  return [...validStored, ...newGuruIds]
}

const getRelationDisplayCode = (mapelId, guruId) => {
  const relationKey = `${mapelId}-${guruId}`
  const guruOrder = getFinalGuruOrder()
  const baseIndex = guruOrder.indexOf(Number(guruId))
  if (baseIndex < 0) return ''

  const baseCode = baseIndex + 1
  const sameGuruKeys = []

  mapelList.value.forEach((mapel) => {
    if (mapel.guru && Array.isArray(mapel.guru)) {
      mapel.guru.forEach((guru) => {
        if (Number(guru.id) === Number(guruId)) sameGuruKeys.push(`${mapel.id}-${guru.id}`)
      })
    }
  })

  if (sameGuruKeys.length === 0) return ''
  const mapelPosition = sameGuruKeys.findIndex((key) => key === relationKey)
  if (mapelPosition < 0) return ''
  if (sameGuruKeys.length === 1) return String(baseCode)

  const letter = String.fromCharCode(65 + Math.max(0, mapelPosition))
  return `${baseCode}${letter}`
}

const getGuruKode = (jadwal) => {
  if (!jadwal) return ''
  if (jadwal.kegiatan_id) return getKegiatanName(jadwal.kegiatan_id)
  if (!jadwal.mapel_id || !jadwal.guru_id) return ''

  const shiftedCode = getRelationDisplayCode(jadwal.mapel_id, jadwal.guru_id)
  if (shiftedCode) return shiftedCode
  return jadwal.guru_id.toString()
}

const getAllTimePoints = () => {
  if (jadwalList.value.length === 0) return []
  const times = new Set()
  jadwalList.value.forEach((jadwal) => {
    times.add(jadwal.jam_mulai)
    times.add(jadwal.jam_selesai)
  })
  return Array.from(times).sort((a, b) => a.localeCompare(b))
}

const getAllTimePointsForAllKelas = () => {
  if (allKelasJadwalList.value.length === 0) return []
  const times = new Set()
  allKelasJadwalList.value.forEach((jadwal) => {
    times.add(jadwal.jam_mulai)
    times.add(jadwal.jam_selesai)
  })
  return Array.from(times).sort((a, b) => a.localeCompare(b))
}

const timeSlots = computed(() => {
  const timePoints = getAllTimePoints()
  if (timePoints.length < 2) return []

  const slots = []
  for (let i = 0; i < timePoints.length - 1; i++) {
    slots.push({
      id: `${timePoints[i]}-${timePoints[i + 1]}`,
      jam_mulai: timePoints[i],
      jam_selesai: timePoints[i + 1],
      index: i
    })
  }
  return slots
})

const allTimeSlots = computed(() => {
  const timePoints = getAllTimePointsForAllKelas()
  if (timePoints.length < 2) return []

  const slots = []
  for (let i = 0; i < timePoints.length - 1; i++) {
    slots.push({
      id: `${timePoints[i]}-${timePoints[i + 1]}`,
      jam_mulai: timePoints[i],
      jam_selesai: timePoints[i + 1],
      index: i
    })
  }
  return slots
})

const getScheduleForTimeDay = (slotId, hariName) => {
  const [jam_mulai] = slotId.split('-')
  return jadwalList.value.find((j) => j.hari === hariName && j.jam_mulai <= jam_mulai && j.jam_selesai > jam_mulai)
}

const sortedKelasList = computed(() => {
  const uniqueKelas = new Map()
  allKelasJadwalList.value.forEach((jadwal) => {
    if (jadwal.kelas && !uniqueKelas.has(jadwal.kelas.id)) {
      uniqueKelas.set(jadwal.kelas.id, jadwal.kelas)
    }
  })

  return Array.from(uniqueKelas.values()).sort((a, b) => {
    const getTingkat = (nama) => {
      if (nama.includes('XII')) return 3
      if (nama.includes('XI')) return 2
      if (nama.includes('X')) return 1
      return 0
    }

    const tingkatA = getTingkat(a.nama)
    const tingkatB = getTingkat(b.nama)
    if (tingkatA !== tingkatB) return tingkatA - tingkatB
    return a.nama.localeCompare(b.nama)
  })
})

const getTierFromKelas = (kelasNama) => {
  if (kelasNama.includes('XII')) return 3
  if (kelasNama.includes('XI')) return 2
  if (kelasNama.includes('X')) return 1
  return 0
}

const getTierName = (tier) => {
  if (tier === 3) return 'XII'
  if (tier === 2) return 'XI'
  if (tier === 1) return 'X'
  return ''
}

const getTierColor = (tier) => {
  if (tier === 3) return { bg: '#E8F5E9', text: '#2E7D32' }
  if (tier === 2) return { bg: '#F3E5F5', text: '#6A1B9A' }
  if (tier === 1) return { bg: '#FFF9C4', text: '#F57F17' }
  return { bg: '#FFFFFF', text: '#000000' }
}

const groupKelasByTier = computed(() => {
  const groups = {}
  sortedKelasList.value.forEach((kelas) => {
    const tier = getTierFromKelas(kelas.nama)
    if (!groups[tier]) groups[tier] = []
    groups[tier].push(kelas)
  })
  return groups
})

const getTierKeys = computed(() => Object.keys(groupKelasByTier.value).map(Number).sort((a, b) => a - b))

const computeCrossTierMerge = (hariName, slotId) => {
  const tierKeys = getTierKeys.value || []
  if (!tierKeys || tierKeys.length === 0) return { merged: false, tiers: [] }

  let firstKegiatan = null
  let hasFirst = false

  for (const tier of tierKeys) {
    const kelasList = groupKelasByTier.value[tier] || []
    if (kelasList.length === 0) return { merged: false, tiers: [] }

    for (const kelas of kelasList) {
      const schedule = getScheduleForAllKelas(slotId, hariName, kelas.id)
      const kegiatanId = schedule?.kegiatan_id || null

      if (!hasFirst) {
        firstKegiatan = kegiatanId
        hasFirst = true
      }

      if (!firstKegiatan || kegiatanId !== firstKegiatan) {
        return { merged: false, tiers: [] }
      }
    }
  }

  return {
    merged: true,
    tiers: tierKeys.slice().sort((a, b) => Number(a) - Number(b)),
    kegiatanId: firstKegiatan
  }
}

const isCrossTierMerged = (hariName, slotId) => computeCrossTierMerge(hariName, slotId).merged
const getCrossTierMergeColspan = (hariName, slotId) => (computeCrossTierMerge(hariName, slotId).merged ? sortedKelasList.value.length : 0)
const getCrossTierMergeKegiatanId = (hariName, slotId) => computeCrossTierMerge(hariName, slotId).kegiatanId || null

const computeVerticalKegiatanMerge = (hariName, tier) => {
  const kelasList = groupKelasByTier.value[tier] || []
  const slotsList = allTimeSlots.value || []
  if (!kelasList || !slotsList || kelasList.length === 0 || slotsList.length === 0) return {}

  const mergeInfo = {}

  for (let tierKelasIdx = 0; tierKelasIdx < kelasList.length; tierKelasIdx++) {
    const kelas = kelasList[tierKelasIdx]
    let currentKegiatanId = null
    let mergeStart = null

    for (let slotIdx = 0; slotIdx < slotsList.length; slotIdx++) {
      const slot = slotsList[slotIdx]
      const schedule = getScheduleForAllKelas(slot.id, hariName, kelas.id)
      const slotKegiatanId = schedule?.kegiatan_id || null

      if (slotKegiatanId && slotKegiatanId === currentKegiatanId) {
        continue
      }

      if (currentKegiatanId && mergeStart !== null) {
        const rowspan = slotIdx - mergeStart
        if (rowspan > 1) {
          const key = `${tierKelasIdx}-${mergeStart}`
          mergeInfo[key] = { rowspan, kegiatanId: currentKegiatanId, startSlotIdx: mergeStart }
          for (let j = mergeStart + 1; j < slotIdx; j++) {
            mergeInfo[`${tierKelasIdx}-${j}-skipped`] = true
          }
        }
      }

      if (slotKegiatanId) {
        currentKegiatanId = slotKegiatanId
        mergeStart = slotIdx
      } else {
        currentKegiatanId = null
        mergeStart = null
      }
    }

    if (currentKegiatanId && mergeStart !== null) {
      const rowspan = slotsList.length - mergeStart
      if (rowspan > 1) {
        const key = `${tierKelasIdx}-${mergeStart}`
        mergeInfo[key] = { rowspan, kegiatanId: currentKegiatanId, startSlotIdx: mergeStart }
        for (let j = mergeStart + 1; j < slotsList.length; j++) {
          mergeInfo[`${tierKelasIdx}-${j}-skipped`] = true
        }
      }
    }
  }

  return mergeInfo
}

const getVerticalMergeInfo = (hariName, tier) => {
  const cacheKey = `${hariName}-tier${tier}`
  if (!verticalMergeCache.value[cacheKey]) {
    verticalMergeCache.value[cacheKey] = computeVerticalKegiatanMerge(hariName, tier)
  }
  return verticalMergeCache.value[cacheKey]
}

const shouldRenderVerticalMergeCell = (hariName, slotIdx, tier, tierKelasIdx) => getVerticalMergeInfo(hariName, tier)[`${tierKelasIdx}-${slotIdx}`]?.rowspan > 1
const getVerticalMergeRowspan = (hariName, slotIdx, tier, tierKelasIdx) => getVerticalMergeInfo(hariName, tier)[`${tierKelasIdx}-${slotIdx}`]?.rowspan || 1
const isVerticalMergeSkipped = (hariName, slotIdx, tier, tierKelasIdx) => getVerticalMergeInfo(hariName, tier)[`${tierKelasIdx}-${slotIdx}-skipped`] === true

const computeJamMergeSemauHari = (hariName) => {
  const slotsList = allTimeSlots.value
  if (!slotsList || slotsList.length === 0) return {}

  const tierKeys = getTierKeys.value || []
  const jamMergeInfo = {}
  let i = 0

  while (i < slotsList.length) {
    let rowspan = 1
    let j = i + 1
    let shouldMerge = false

    const firstSlotKegiatans = {}
    for (const tier of tierKeys) {
      const kelasList = groupKelasByTier.value[tier] || []
      if (kelasList.length > 0) {
        const schedule = getScheduleForAllKelas(slotsList[i].id, hariName, kelasList[0].id)
        if (schedule?.kegiatan_id) firstSlotKegiatans[tier] = schedule.kegiatan_id
      }
    }

    if (Object.keys(firstSlotKegiatans).length > 0) {
      while (j < slotsList.length) {
        let allMatch = true

        for (const tier of tierKeys) {
          const kelasList = groupKelasByTier.value[tier] || []
          if (kelasList.length === 0) continue

          const schedule = getScheduleForAllKelas(slotsList[j].id, hariName, kelasList[0].id)
          const nextKegiatanId = schedule?.kegiatan_id || null
          if (nextKegiatanId !== firstSlotKegiatans[tier]) {
            allMatch = false
            break
          }
        }

        if (allMatch) {
          rowspan++
          j++
        } else {
          break
        }
      }

      if (rowspan > 1) shouldMerge = true
    }

    if (shouldMerge && rowspan > 1) {
      jamMergeInfo[i] = { type: 'merge', rowspan }
      for (let k = i + 1; k < i + rowspan; k++) {
        jamMergeInfo[k] = { type: 'skip' }
      }
      i += rowspan
    } else {
      i++
    }
  }

  return jamMergeInfo
}

const getJamMergeSemauHariInfo = (hariName) => {
  if (!getJamMergeSemauHariCache.value[hariName]) {
    getJamMergeSemauHariCache.value[hariName] = computeJamMergeSemauHari(hariName)
  }
  return getJamMergeSemauHariCache.value[hariName]
}

const shouldSkipJamSlot = (hariName, slotIdx) => getJamMergeSemauHariInfo(hariName)[slotIdx]?.type === 'skip'
const getJamRowspan = (hariName, slotIdx) => getJamMergeSemauHariInfo(hariName)[slotIdx]?.rowspan || 1

const computeKegiatanMergeSemauHari = (hariName) => {
  const slotsList = allTimeSlots.value
  if (!slotsList || slotsList.length === 0) return {}

  const tierKeys = getTierKeys.value || []
  const kegiatanMergeInfo = {}
  let i = 0

  while (i < slotsList.length) {
    let rowspan = 1
    let j = i + 1
    let shouldMerge = false

    const firstSlotKegiatans = {}
    for (const tier of tierKeys) {
      const kelasList = groupKelasByTier.value[tier] || []
      if (kelasList.length > 0) {
        const schedule = getScheduleForAllKelas(slotsList[i].id, hariName, kelasList[0].id)
        if (schedule?.kegiatan_id) firstSlotKegiatans[tier] = schedule.kegiatan_id
      }
    }

    if (Object.keys(firstSlotKegiatans).length > 0) {
      while (j < slotsList.length) {
        let allMatch = true
        for (const tier of tierKeys) {
          const kelasList = groupKelasByTier.value[tier] || []
          if (kelasList.length === 0) continue

          const schedule = getScheduleForAllKelas(slotsList[j].id, hariName, kelasList[0].id)
          const nextKegiatanId = schedule?.kegiatan_id || null
          if (nextKegiatanId !== firstSlotKegiatans[tier]) {
            allMatch = false
            break
          }
        }

        if (allMatch) {
          rowspan++
          j++
        } else {
          break
        }
      }

      if (rowspan > 1) shouldMerge = true
    }

    if (shouldMerge && rowspan > 1) {
      kegiatanMergeInfo[i] = { type: 'merge', rowspan }
      for (let k = i + 1; k < i + rowspan; k++) {
        kegiatanMergeInfo[k] = { type: 'skip' }
      }
      i += rowspan
    } else {
      i++
    }
  }

  return kegiatanMergeInfo
}

const getKegiatanMergeSemauHariInfo = (hariName) => {
  if (!getKegiatanMergeSemauHariCache.value[hariName]) {
    getKegiatanMergeSemauHariCache.value[hariName] = computeKegiatanMergeSemauHari(hariName)
  }
  return getKegiatanMergeSemauHariCache.value[hariName]
}

const shouldSkipKegiatanSlot = (hariName, slotIdx) => getKegiatanMergeSemauHariInfo(hariName)[slotIdx]?.type === 'skip'
const getKegiatanRowspan = (hariName, slotIdx) => getKegiatanMergeSemauHariInfo(hariName)[slotIdx]?.rowspan || 1

const computeMergedCellsForSemuaHariTierAware = (hariName, slotId, tier) => {
  const kelasList = groupKelasByTier.value[tier] || []
  if (!kelasList || kelasList.length === 0) return {}

  const mergedInfo = {}
  const processed = new Set()

  for (let i = 0; i < kelasList.length; i++) {
    if (processed.has(i)) continue

    const schedule = getScheduleForAllKelas(slotId, hariName, kelasList[i].id)
    if (!schedule || !schedule.kegiatan_id) continue

    let span = 1
    const kegiatanId = schedule.kegiatan_id

    for (let j = i + 1; j < kelasList.length; j++) {
      if (processed.has(j)) break
      const nextSchedule = getScheduleForAllKelas(slotId, hariName, kelasList[j].id)
      if (nextSchedule && nextSchedule.kegiatan_id === kegiatanId) {
        span++
      } else {
        break
      }
    }

    if (span > 1) {
      mergedInfo[i] = { type: 'merged', span, kegiatanId, schedule }
      for (let j = i + 1; j < i + span; j++) {
        mergedInfo[j] = { type: 'skipped' }
        processed.add(j)
      }
      processed.add(i)
    }
  }

  return mergedInfo
}

const getMergedSemuaHariTierAwareInfo = (hariName, slotId, tier) => {
  const cacheKey = `${hariName}-${slotId}-tier${tier}`
  if (!mergedCellSemuaHariCache.value[cacheKey]) {
    mergedCellSemuaHariCache.value[cacheKey] = computeMergedCellsForSemuaHariTierAware(hariName, slotId, tier)
  }
  return mergedCellSemuaHariCache.value[cacheKey]
}

const shouldRenderMergedSemuaHariTierAware = (hariName, slotId, tier, tierKelasIdx) =>
  getMergedSemuaHariTierAwareInfo(hariName, slotId, tier)[tierKelasIdx]?.type === 'merged'
const getMergedSpanSemuaHariTierAware = (hariName, slotId, tier, tierKelasIdx) =>
  getMergedSemuaHariTierAwareInfo(hariName, slotId, tier)[tierKelasIdx]?.span || 1
const isPartOfMergedSemuaHariTierAware = (hariName, slotId, tier, tierKelasIdx) =>
  getMergedSemuaHariTierAwareInfo(hariName, slotId, tier)[tierKelasIdx]?.type === 'skipped'
const getMergedKegiatanIdSemuaHariTierAware = (hariName, slotId, tier, tierKelasIdx) =>
  getMergedSemuaHariTierAwareInfo(hariName, slotId, tier)[tierKelasIdx]?.kegiatanId || null

const computeVerticalMergeSingleKelas = (hariName) => {
  const slots = timeSlots.value
  if (!slots || slots.length === 0) return {}

  const mergeInfo = {}
  let i = 0

  while (i < slots.length) {
    const currentSchedule = getScheduleForTimeDay(slots[i].id, hariName)
    if (!currentSchedule) {
      i++
      continue
    }

    const currentContent = currentSchedule.kegiatan_id || currentSchedule.mapel_id
    const currentGuru = currentSchedule.guru_id
    let rowspan = 1
    let j = i + 1

    while (j < slots.length) {
      const nextSchedule = getScheduleForTimeDay(slots[j].id, hariName)
      if (!nextSchedule) break

      const nextContent = nextSchedule.kegiatan_id || nextSchedule.mapel_id
      const nextGuru = nextSchedule.guru_id
      const currentSlotEnd = slots[j - 1].jam_selesai
      const nextSlotStart = slots[j].jam_mulai

      if (
        (currentSchedule.kegiatan_id && nextSchedule.kegiatan_id === currentContent) ||
        (!currentSchedule.kegiatan_id && !nextSchedule.kegiatan_id && nextContent === currentContent && nextGuru === currentGuru)
      ) {
        if (currentSlotEnd === nextSlotStart) {
          rowspan++
          j++
        } else {
          break
        }
      } else {
        break
      }
    }

    if (rowspan > 1) {
      mergeInfo[i] = { type: 'merge', rowspan, schedule: currentSchedule }
      for (let k = i + 1; k < i + rowspan; k++) {
        mergeInfo[k] = { type: 'skip' }
      }
      i += rowspan
    } else {
      mergeInfo[i] = { type: 'normal', rowspan: 1, schedule: currentSchedule }
      i++
    }
  }

  return mergeInfo
}

const getVerticalMergeSingleKelasInfo = (hariName) => {
  const cacheKey = `${kelasId.value}-${hariName}`
  if (!verticalMergeCacheSingleKelas.value[cacheKey]) {
    verticalMergeCacheSingleKelas.value[cacheKey] = computeVerticalMergeSingleKelas(hariName)
  }
  return verticalMergeCacheSingleKelas.value[cacheKey]
}

const shouldSkipSlotSingleKelas = (hariName, slotIndex) => getVerticalMergeSingleKelasInfo(hariName)[slotIndex]?.type === 'skip'
const getRowspanForSlotSingleKelas = (hariName, slotIndex) => getVerticalMergeSingleKelasInfo(hariName)[slotIndex]?.rowspan || 1
const getMergedScheduleSingleKelas = (hariName, slotIndex) => getVerticalMergeSingleKelasInfo(hariName)[slotIndex]?.schedule || null

const shouldRenderSingleKelasSlotRow = (slot, slotIndex) => {
  return hari.some((hariName) => {
    if (shouldSkipSlotSingleKelas(hariName, slotIndex)) return false
    const schedule = getMergedScheduleSingleKelas(hariName, slotIndex)
    if (!schedule) return false
    if (schedule.jam_mulai !== slot.jam_mulai) return false
    if (schedule.kegiatan_id) return true
    if (schedule.mapel_id) return true
    return false
  })
}

const computeHorizontalMergeSingleKelas = (slotIndex) => {
  const mergeInfo = {}
  let dayIndex = 0

  while (dayIndex < hari.length) {
    const hariName = hari[dayIndex]
    if (shouldSkipSlotSingleKelas(hariName, slotIndex)) {
      dayIndex++
      continue
    }

    const currentSchedule = getMergedScheduleSingleKelas(hariName, slotIndex)
    const currentRowspan = getRowspanForSlotSingleKelas(hariName, slotIndex)

    if (!currentSchedule?.kegiatan_id) {
      dayIndex++
      continue
    }

    let span = 1
    let nextIndex = dayIndex + 1

    while (nextIndex < hari.length) {
      const nextHari = hari[nextIndex]
      if (shouldSkipSlotSingleKelas(nextHari, slotIndex)) break

      const nextSchedule = getMergedScheduleSingleKelas(nextHari, slotIndex)
      const nextRowspan = getRowspanForSlotSingleKelas(nextHari, slotIndex)

      if (!nextSchedule?.kegiatan_id) break
      if (Number(nextSchedule.kegiatan_id) !== Number(currentSchedule.kegiatan_id)) break
      if (nextRowspan !== currentRowspan) break

      span++
      nextIndex++
    }

    if (span > 1) {
      mergeInfo[dayIndex] = { type: 'start', colspan: span }
      for (let i = dayIndex + 1; i < dayIndex + span; i++) {
        mergeInfo[i] = { type: 'skip' }
      }
      dayIndex += span
    } else {
      dayIndex++
    }
  }

  return mergeInfo
}

const getHorizontalMergeSingleKelasInfo = (slotIndex) => {
  const cacheKey = `${kelasId.value}-${slotIndex}`
  if (!horizontalMergeSingleKelasCache.value[cacheKey]) {
    horizontalMergeSingleKelasCache.value[cacheKey] = computeHorizontalMergeSingleKelas(slotIndex)
  }
  return horizontalMergeSingleKelasCache.value[cacheKey]
}

const shouldSkipHorizontalMergeSingleKelas = (slotIndex, hariIndex) => getHorizontalMergeSingleKelasInfo(slotIndex)[hariIndex]?.type === 'skip'
const getHorizontalMergeColspanSingleKelas = (slotIndex, hariIndex) => getHorizontalMergeSingleKelasInfo(slotIndex)[hariIndex]?.colspan || 1

const getKelasName = (id) => kelasOptions.value.find((k) => k.id === id)?.nama || 'Tidak diketahui'

const escapeHtml = (value) =>
  String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\"/g, '&quot;')
    .replace(/'/g, '&#39;')

const getSingleKelasExportCellContent = (schedule) => {
  if (!schedule) return { html: '', style: '' }

  if (schedule.kegiatan_id) {
    const kegiatanName = escapeHtml(getKegiatanDisplayName(schedule.kegiatan_id))
    const kegiatanStyle = getKegiatanStyle(schedule.kegiatan_id)
    return {
      html: `<div style="padding:4px 3px;line-height:1.2;">${kegiatanName}</div>`,
      style: `background:${kegiatanStyle.backgroundColor};color:${kegiatanStyle.color};font-weight:700;`
    }
  }

  const mapelName = escapeHtml(getMapelDisplayName(schedule.mapel_id))
  const guruName = escapeHtml(getGuruName(schedule.guru_id))
  return {
    html: `<div style="padding:4px 3px;line-height:1.2;"><div style="font-weight:700;color:#212529;">${mapelName}</div><div style="font-size:10px;color:#6c757d;">${guruName}</div></div>`,
    style: 'background:#ffffff;color:#212529;'
  }
}

const getSingleKelasExportMergeCell = (hariName, slotIndex) => {
  const mergeInfo = getVerticalMergeSingleKelasInfo(hariName)
  const info = mergeInfo[slotIndex]
  if (!info) return { type: 'empty', rowspan: 1, schedule: null }
  if (info.type === 'skip') return { type: 'skip', rowspan: 0, schedule: null }
  return { type: info.type, rowspan: info.rowspan || 1, schedule: info.schedule || null }
}

const buildSingleKelasExportTable = () => {
  const exportRoot = document.createElement('div')
  exportRoot.style.background = '#ffffff'
  exportRoot.style.padding = '0'
  exportRoot.style.margin = '0'

  const rowsHtml = timeSlots.value
    .map((slot, slotIndex) => {
      const cellsHtml = hari
        .map((hariName, hariIndex) => {
          const mergeCell = getSingleKelasExportMergeCell(hariName, slotIndex)
          if (mergeCell.type === 'skip') return ''
          if (shouldSkipHorizontalMergeSingleKelas(slotIndex, hariIndex)) return ''

          const cell = getSingleKelasExportCellContent(mergeCell.schedule)
          const horizontalColspan = getHorizontalMergeColspanSingleKelas(slotIndex, hariIndex)
          const rowspanAttr = mergeCell.rowspan > 1 ? ` rowspan="${mergeCell.rowspan}"` : ''
          const colspanAttr = horizontalColspan > 1 ? ` colspan="${horizontalColspan}"` : ''

          return `<td${rowspanAttr}${colspanAttr} style="border:1px solid #d5dbe1;text-align:center;vertical-align:middle;min-width:140px;height:46px;${cell.style}">${cell.html}</td>`
        })
        .join('')

      return `<tr><td style="border:1px solid #d5dbe1;text-align:center;color:#1f6feb;font-weight:700;white-space:nowrap;min-width:95px;height:46px;padding:0 6px;">${escapeHtml(slot.jam_mulai)} - ${escapeHtml(slot.jam_selesai)}</td>${cellsHtml}</tr>`
    })
    .join('')

  exportRoot.innerHTML = `
    <table style="border-collapse:collapse;table-layout:fixed;background:#ffffff;font-family:Arial,sans-serif;font-size:11px;width:auto;">
      <thead>
        <tr>
          <th style="border:1px solid #d5dbe1;background:#f1f3f5;color:#212529;font-weight:700;padding:6px 4px;min-width:95px;text-align:center;">Jam</th>
          ${hari
            .map(
              (hariName) =>
                `<th style="border:1px solid #d5dbe1;background:#f1f3f5;color:#212529;font-weight:700;padding:6px 4px;min-width:140px;text-align:center;">${escapeHtml(hariName)}</th>`
            )
            .join('')}
        </tr>
      </thead>
      <tbody>${rowsHtml}</tbody>
    </table>
  `

  return exportRoot
}

const downloadPDF = async () => {
  const margin = 3

  try {
    const element = fullscreenTableContent.value
    if (!element) return

    let canvas = null

    if (viewMode.value === 'semua-kelas') {
      const tableWrapper = element.querySelector('.table-responsive-semua-hari')
      const sourceTable = element.querySelector('.table-semua-hari')
      if (!tableWrapper || !sourceTable) throw new Error('Tabel jadwal tidak ditemukan')

      const originalWrapperStyles = {
        maxHeight: tableWrapper.style.maxHeight,
        overflowX: tableWrapper.style.overflowX,
        overflowY: tableWrapper.style.overflowY
      }
      const originalTableStyles = {
        width: sourceTable.style.width,
        maxWidth: sourceTable.style.maxWidth
      }
      const tableThead = sourceTable.querySelector('thead')
      const originalTheadPosition = tableThead ? tableThead.style.position : ''
      const originalTheadTop = tableThead ? tableThead.style.top : ''

      try {
        element.classList.add('pdf-export-mode')
        tableWrapper.style.maxHeight = 'none'
        tableWrapper.style.overflowX = 'visible'
        tableWrapper.style.overflowY = 'visible'
        sourceTable.style.width = `${Math.max(sourceTable.scrollWidth, sourceTable.clientWidth)}px`
        sourceTable.style.maxWidth = 'none'

        if (tableThead) {
          tableThead.style.position = 'static'
          tableThead.style.top = 'auto'
        }

        await new Promise((resolve) => requestAnimationFrame(resolve))

        const targetWidth = Math.max(sourceTable.scrollWidth, sourceTable.clientWidth)
        const targetHeight = Math.max(sourceTable.scrollHeight, sourceTable.clientHeight)
        const maxCanvasSide = 8000
        const canvasScale = Math.max(1, Math.min(2, maxCanvasSide / Math.max(targetWidth, targetHeight)))

        canvas = await html2canvas(sourceTable, {
          scale: canvasScale,
          useCORS: true,
          backgroundColor: '#ffffff',
          width: targetWidth,
          height: targetHeight,
          windowWidth: targetWidth,
          windowHeight: targetHeight,
          scrollX: 0,
          scrollY: 0
        })
      } finally {
        element.classList.remove('pdf-export-mode')
        tableWrapper.style.maxHeight = originalWrapperStyles.maxHeight
        tableWrapper.style.overflowX = originalWrapperStyles.overflowX
        tableWrapper.style.overflowY = originalWrapperStyles.overflowY
        sourceTable.style.width = originalTableStyles.width
        sourceTable.style.maxWidth = originalTableStyles.maxWidth
        if (tableThead) {
          tableThead.style.position = originalTheadPosition
          tableThead.style.top = originalTheadTop
        }
      }
    } else {
      const captureHost = document.createElement('div')
      captureHost.style.position = 'fixed'
      captureHost.style.left = '-100000px'
      captureHost.style.top = '0'
      captureHost.style.backgroundColor = '#ffffff'
      captureHost.style.padding = '0'
      captureHost.style.margin = '0'

      const exportTableRoot = buildSingleKelasExportTable()
      captureHost.appendChild(exportTableRoot)
      document.body.appendChild(captureHost)

      try {
        await new Promise((resolve) => requestAnimationFrame(resolve))
        const targetWidth = Math.max(exportTableRoot.scrollWidth, exportTableRoot.clientWidth)
        const targetHeight = Math.max(exportTableRoot.scrollHeight, exportTableRoot.clientHeight)
        const maxCanvasSide = 8000
        const canvasScale = Math.max(1, Math.min(2, maxCanvasSide / Math.max(targetWidth, targetHeight)))

        canvas = await html2canvas(exportTableRoot, {
          scale: canvasScale,
          useCORS: true,
          backgroundColor: '#ffffff',
          width: targetWidth,
          height: targetHeight,
          windowWidth: targetWidth,
          windowHeight: targetHeight,
          scrollX: 0,
          scrollY: 0
        })
      } finally {
        if (captureHost.parentNode) captureHost.parentNode.removeChild(captureHost)
      }
    }

    if (!canvas || !canvas.width || !canvas.height) throw new Error('Canvas kosong, tidak bisa dibuat PDF')

    const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })
    const pdfTitle = viewMode.value === 'semua-kelas' ? 'Jadwal Semua Kelas' : `Jadwal Kelas ${studentClass.value}`
    const titleSpace = 8

    const pageWidth = pdf.internal.pageSize.getWidth() - margin * 2
    const pageHeight = pdf.internal.pageSize.getHeight() - margin * 2 - titleSpace
    const scale = Math.min(pageWidth / canvas.width, pageHeight / canvas.height)
    const renderWidth = canvas.width * scale
    const renderHeight = canvas.height * scale
    const x = margin + (pageWidth - renderWidth) / 2
    const y = margin + titleSpace

    pdf.setFont('helvetica', 'bold')
    pdf.setFontSize(12)
    pdf.text(pdfTitle, pdf.internal.pageSize.getWidth() / 2, margin + 4, { align: 'center' })

    const imgData = canvas.toDataURL('image/png')
    pdf.addImage(imgData, 'PNG', x, y, renderWidth, renderHeight)

    while (pdf.getNumberOfPages() > 1) {
      pdf.deletePage(pdf.getNumberOfPages())
    }

    const filename = `Jadwal-${viewMode.value === 'semua-kelas' ? 'Semua-Kelas' : studentClass.value}-${new Date().toISOString().slice(0, 10)}.pdf`
    pdf.save(filename)
  } catch (error) {
    console.error('Error downloading PDF:', error)
    alert('Gagal download PDF: ' + error.message)
  }
}

const loadData = async () => {
  try {
    loading.value = true
    clearCaches()

    const savedKelas = localStorage.getItem('selectedKelas')
    if (!savedKelas) {
      kelasId.value = null
      studentClass.value = 'Belum dipilih'
      jadwalList.value = []
      allKelasJadwalList.value = []
      return
    }

    const kelasObj = JSON.parse(savedKelas)
    kelasId.value = kelasObj.id
    studentClass.value = kelasObj.nama || 'Kelas'

    const [jadwalSingleRes, jadwalAllRes, mapelRes, kegiatanRes, kelasRes] = await Promise.all([
      jadwalService.getByKelas(kelasId.value),
      jadwalService.getAll(),
      mapelService.getAll(),
      kegiatanService.getAll(),
      kelasService.getAll()
    ])

    jadwalList.value = jadwalSingleRes.data?.success ? jadwalSingleRes.data.data : []
    allKelasJadwalList.value = jadwalAllRes.data?.success ? jadwalAllRes.data.data : []
    mapelList.value = mapelRes.data?.success ? mapelRes.data.data : []
    kegiatanList.value = kegiatanRes.data?.success ? kegiatanRes.data.data : []
    kelasOptions.value = kelasRes.data?.success ? kelasRes.data.data.map((k) => ({ id: k.id, nama: k.nama })) : []
  } catch (error) {
    console.error('Error loading student schedule:', error)
    alert('Gagal memuat data jadwal')
    jadwalList.value = []
    allKelasJadwalList.value = []
  } finally {
    loading.value = false
    clearCaches()
  }
}

const refreshData = async () => {
  await loadData()
}

onMounted(async () => {
  await loadData()
})
</script>

<style scoped>
.text-sm {
  font-size: 0.875rem;
}

.text-xs {
  font-size: 0.75rem;
}

.table-responsive {
  max-height: 600px;
  overflow-y: auto;
}

.table-responsive-semua-hari {
  max-height: 800px;
  overflow-y: auto;
  overflow-x: auto;
}

table thead {
  position: sticky;
  top: 0;
  z-index: 10;
}

table {
  border-collapse: collapse;
  border: 2px solid #dee2e6;
}

table th {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6 !important;
  padding: 12px !important;
  font-weight: 600;
  text-align: center;
  vertical-align: middle;
}

table td {
  border: 1px solid #dee2e6 !important;
  padding: 0 !important;
  vertical-align: middle;
  height: 40px;
  background-color: #ffffff;
}

table td.p-2 {
  padding: 8px !important;
}

.table-semua-hari thead th {
  background-color: #f8f9fa;
  padding: 8px 4px !important;
  font-size: 0.85rem;
  border: 1px solid #dee2e6 !important;
}

.table-semua-hari tbody td {
  padding: 4px !important;
  height: 50px;
  text-align: center;
  vertical-align: middle;
  border: 1px solid #dee2e6 !important;
  padding: 0 !important;
}

.schedule-cell-all {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  padding: 0;
  border-radius: 4px;
  font-size: 0.85rem;
}

.schedule-cell-all.schedule-cell-kegiatan {
  padding: 0 !important;
  margin: 0 !important;
  border-radius: 0 !important;
  width: 100%;
  height: 100%;
}

.kegiatan-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  white-space: normal !important;
  word-break: break-word;
  line-height: 1.1;
}

.kegiatan-line {
  display: block;
  width: 100%;
  text-align: center;
}

.schedule-cell {
  padding: 10px;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.schedule-cell.schedule-cell-kegiatan {
  padding: 8px;
  background-color: transparent;
}

.schedule-cell.schedule-cell-kegiatan > div {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

td[rowspan] .schedule-cell {
  min-height: 60px;
  justify-content: center;
  align-items: center;
}

td[rowspan] .schedule-cell > div {
  width: 100%;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fullscreen-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
}

.fullscreen-modal-content {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: white;
  z-index: 10000;
  display: flex;
  flex-direction: column;
}

.fullscreen-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  flex-wrap: wrap;
  gap: 10px;
}

.fullscreen-title {
  flex: 1;
  min-width: 200px;
}

.fullscreen-title h5 {
  font-weight: 600;
  color: #212529;
}

.fullscreen-controls {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.fullscreen-body {
  flex: 1;
  overflow: auto;
  padding: 20px;
  background-color: white;
}

.fullscreen-table {
  border-collapse: collapse;
  width: 100%;
  table-layout: fixed;
}

.fullscreen-table thead {
  position: sticky;
  top: 0;
  z-index: 100;
}

.fullscreen-table th {
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  padding: 10px;
  font-weight: 600;
  text-align: center;
  vertical-align: middle;
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
  white-space: normal;
}

.fullscreen-table td {
  border: 1px solid #dee2e6;
  padding: 8px;
  text-align: center;
  vertical-align: middle;
  word-wrap: break-word;
  overflow-wrap: break-word;
  word-break: break-word;
  white-space: normal !important;
  width: 40px !important;
  min-width: 40px !important;
  max-width: 40px !important;
}

.fullscreen-table tbody tr:nth-child(even) {
  background-color: #f8f9fa;
}

.pdf-export-mode .table-semua-hari thead th {
  padding: 3px 2px !important;
  font-size: 0.6rem !important;
}

.pdf-export-mode .table-semua-hari tbody td {
  height: 30px !important;
  padding: 0 !important;
  font-size: 0.58rem !important;
}

.pdf-export-mode .table-semua-hari .schedule-cell-all > div {
  font-size: 0.55rem !important;
  line-height: 1.05 !important;
}

.schedule-controls {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 0.75rem;
}

.mode-toggle-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-self: start;
}

.quick-actions-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-self: end;
}

.selected-class-pill {
  font-size: 0.95rem;
  font-weight: 700;
  color: #1f2937;
  background: #eef2ff;
  border: 1px solid #c7d2fe;
  border-radius: 999px;
  padding: 0.35rem 0.85rem;
  line-height: 1.2;
  text-align: center;
  white-space: nowrap;
  justify-self: center;
}

.utility-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

@media (max-width: 991.98px) {
  .student-lihat-jadwal-page {
    padding-left: 0.25rem;
    padding-right: 0.25rem;
  }

  .table-responsive,
  .table-responsive-semua-hari {
    max-height: 72vh;
  }

  .fullscreen-body {
    padding: 14px;
  }
}

@media (max-width: 768px) {
  .schedule-controls {
    display: grid;
    grid-template-columns: 1fr auto;
    grid-template-areas:
      'class quick'
      'mode mode';
    align-items: center;
    gap: 0.5rem;
  }

  .mode-toggle-group {
    grid-area: mode;
    width: 100%;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.5rem;
  }

  .selected-class-pill {
    grid-area: class;
    justify-self: start;
    width: auto;
    background: transparent;
    border: 0;
    border-radius: 0;
    padding: 0;
    font-size: 1.08rem;
    font-weight: 700;
    white-space: normal;
  }

  .quick-actions-group {
    grid-area: quick;
    width: auto;
    display: flex;
    justify-self: end;
    justify-content: flex-end;
    gap: 0.4rem;
  }

  .mode-toggle-group .btn,
  .quick-actions-group .btn {
    width: 100%;
    margin-left: 0 !important;
  }

  .table th,
  .table td {
    font-size: 0.78rem;
  }

  .table-semua-hari thead th {
    font-size: 0.72rem;
    padding: 6px 3px !important;
  }

  .table-semua-hari tbody td {
    height: 44px;
  }

  .schedule-cell {
    padding: 8px;
  }

  .fullscreen-header {
    flex-direction: column;
    align-items: stretch;
  }

  .fullscreen-title {
    min-width: 0;
  }

  .fullscreen-title h5 {
    font-size: 1rem;
  }

  .fullscreen-controls {
    width: 100%;
  }

  .fullscreen-controls .btn {
    flex: 1;
  }

  .fullscreen-body {
    padding: 10px;
  }

  .fullscreen-table th,
  .fullscreen-table td {
    font-size: 0.75rem;
  }
}

@media (max-width: 575.98px) {
  .mode-toggle-group {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .quick-actions-group {
    justify-content: flex-end;
    width: auto;
    gap: 0.4rem;
  }

  .utility-btn {
    width: 36px !important;
    height: 34px;
    min-width: 36px;
    justify-content: center;
    padding: 0;
    border-radius: 8px;
  }

  .utility-btn i {
    font-size: 0.9rem;
  }

  .utility-label {
    display: none;
  }

  .selected-class-pill {
    font-size: 1rem;
    padding: 0;
  }

  .student-lihat-jadwal-page .h3 {
    font-size: 1.25rem;
  }

  .table th,
  .table td,
  .text-sm,
  .text-xs {
    font-size: 0.7rem !important;
  }

  .table-semua-hari thead th {
    font-size: 0.65rem;
  }

  .table-semua-hari tbody td {
    min-width: 44px !important;
    width: 44px !important;
  }
}
</style>
