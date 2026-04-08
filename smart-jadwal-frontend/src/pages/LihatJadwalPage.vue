<template>
  <div class="container-fluid py-2">
    <!-- Header -->
    <div class="mb-3">
      <h1 class="h3 text-dark fw-bold mb-1">Lihat Jadwal</h1>
      <p class="text-muted small">Tampilan jadwal pelajaran mingguan per kelas</p>
    </div>

    <!-- Control Section -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body py-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <label class="text-sm text-dark fw-semibold mb-0">Pilih Kelas:</label>
          <select v-model="kelasAktif" class="form-select form-select-sm" style="max-width: 250px;">
            <option :value="null">-- Pilih Kelas --</option>
            <option :value="'semua-kelas'">SEMUA KELAS</option>
            <option v-for="k in kelasOptions" :key="k.id" :value="k.id">
              {{ k.nama }}
            </option>
          </select>
          <button 
            @click="openFullscreen" 
            class="btn btn-sm btn-outline-primary"
            :disabled="!kelasAktif"
            title="Buka fullscreen dengan zoom dan PDF"
          >
            <i class="bi bi-arrows-fullscreen"></i> Fullscreen
          </button>
          <button 
            @click="refreshKelasList" 
            class="btn btn-sm btn-outline-secondary"
            :disabled="loading"
            title="Refresh data kelas dan jadwal"
          >
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Timetable - Single Kelas View -->
    <div v-if="kelasAktif && kelasAktif !== 'semua-kelas' && jadwalList.length > 0" class="card border-0 shadow-sm">
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
                  <!-- Skip this cell if it's part of a merged cell -->
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
                    <div v-if="getMergedScheduleSingleKelas(hariName, slotIndex)" class="schedule-cell" :class="{ 'schedule-cell-kegiatan': getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id }">
                      <div v-if="getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id" class="w-100 h-100 d-flex align-items-center justify-content-center text-sm fw-semibold px-2" :style="{ color: getKegiatanStyle(getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id).color }">
                        {{ getKegiatanDisplayName(getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id) }}
                      </div>
                      <div v-else class="w-100 h-100 d-flex flex-column justify-content-center align-items-center">
                        <div v-if="getMergedScheduleSingleKelas(hariName, slotIndex).mapel_id" class="text-sm fw-semibold text-dark text-center w-100">
                          {{ getMapelDisplayName(getMergedScheduleSingleKelas(hariName, slotIndex).mapel_id) }}
                        </div>
                        <div v-if="getMergedScheduleSingleKelas(hariName, slotIndex).mapel_id && getMergedScheduleSingleKelas(hariName, slotIndex).guru_id" class="text-xs text-muted text-center w-100">
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

    <!-- Timetable - Semua Hari View -->
    <div v-else-if="kelasAktif === 'semua-kelas' && allKelasJadwalList.length > 0" class="card border-0 shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive-semua-hari">
          <table class="table mb-0 table-semua-hari">
            <!-- Tier Headers -->
            <thead>
              <tr class="bg-light">
                <th class="p-1 text-center fw-semibold" style="width: 70px;">
                  <div class="text-xl">Hari</div>
                </th>
                <th class="p-1 text-center fw-semibold" style="width: 120px;">
                  <div class="text-xl">Jam</div>
                </th>
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
              <!-- Sub-header with kelas names -->
              <tr class="bg-light">
                <th style="width: 70px;"></th>
                <th style="width: 120px;"></th>
                <template v-for="tier in getTierKeys" :key="`names-${tier}`">
                  <template v-for="(kelas, idx) in groupKelasByTier[tier]" :key="`${tier}-${kelas.id}`">
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
              <template v-for="(hariName, hariIdx) in hari" :key="hariName">
                <tr v-for="(slot, slotIndex) in allTimeSlots" :key="`${hariName}-${slot.id}`" class="align-middle">
                  <!-- Hari Column (merged vertically per day) -->
                  <td 
                    v-if="slotIndex === 0"
                    :rowspan="allTimeSlots.length"
                    class="p-2 text-center fw-semibold text-primary"
                    style="width: 70px; vertical-align: middle; border-right: 2px solid #dee2e6;"
                  >
                    <div class="text-xs fw-bold">{{ hariName }}</div>
                  </td>
                  
                  <!-- Jam Column -->
                  <td 
                    v-if="!shouldSkipJamSlot(hariName, slotIndex)"
                    :rowspan="getJamRowspan(hariName, slotIndex)"
                    class="p-1 text-center fw-semibold text-primary" 
                    style="width: 120px; font-size: 0.8rem; border-right: 2px solid #dee2e6; white-space: nowrap;"
                  >
                    {{ slot.jam_mulai }} - {{ slot.jam_selesai }}
                  </td>

                  <!-- Cross-tier merge: if same kegiatan across ALL tiers -->
                  <template v-if="isCrossTierMerged(hariName, slot.id) && !shouldSkipKegiatanSlot(hariName, slotIndex)">
                    <td 
                      :colspan="getCrossTierMergeColspan(hariName, slot.id)"
                      :rowspan="getKegiatanRowspan(hariName, slotIndex)"
                      class="text-center fw-semibold"
                      style="vertical-align: middle; background-color: transparent; padding: 0; width: 50px; min-width: 50px;"
                    >
                      <div class="schedule-cell-all schedule-cell-kegiatan"
                           :style="getKegiatanStyle(getCrossTierMergeKegiatanId(hariName, slot.id))"
                      >
                        <div class="text-xs fw-semibold w-100 text-center">
                          {{ getKegiatanName(getCrossTierMergeKegiatanId(hariName, slot.id)) }}
                        </div>
                      </div>
                    </td>
                  </template>

                  <!-- Normal tier-based rendering (no cross-tier merge) -->
                  <template v-else v-for="tier in getTierKeys" :key="`tier-${tier}`">
                    <template v-for="(kelas, tierKelasIdx) in groupKelasByTier[tier]" :key="`${hariName}-${slot.id}-${kelas.id}`">
                      <!-- Skip cells that are part of vertical merge (not the first one) -->
                      <template v-if="!isVerticalMergeSkipped(hariName, slotIndex, tier, tierKelasIdx)">
                        <!-- Render merged cell untuk kegiatan yang sama -->
                        <td 
                          v-if="shouldRenderMergedSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                          :colspan="getMergedSpanSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                          :rowspan="shouldRenderVerticalMergeCell(hariName, slotIndex, tier, tierKelasIdx) ? getVerticalMergeRowspan(hariName, slotIndex, tier, tierKelasIdx) : 1"
                          class="text-center fw-semibold"
                          style="vertical-align: middle; background-color: transparent; padding: 0; width: 50px; min-width: 50px;"
                        >
                          <div class="schedule-cell-all schedule-cell-kegiatan"
                               :style="getKegiatanStyle(getMergedKegiatanIdSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx))"
                               :colspan="getMergedSpanSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                          >
                            <div class="text-xs fw-semibold w-100 text-center">
                              {{ getKegiatanName(getMergedKegiatanIdSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)) }}
                            </div>
                          </div>
                        </td>

                        <!-- Render normal cell jika tidak di-merge -->
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
                          <div v-if="getScheduleForAllKelas(slot.id, hariName, kelas.id)" 
                               class="schedule-cell-all"
                               :style="getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id ? getKegiatanStyle(getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id) : {}"
                          >
                            <div v-if="getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id" 
                                 class="text-xs fw-semibold px-1 py-1">
                              {{ getKegiatanName(getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id) }}
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

    <!-- Empty State -->
    <div v-else-if="kelasAktif && kelasAktif !== 'semua-kelas' && jadwalList.length === 0" class="alert alert-info">
      <i class="bi bi-info-circle me-2"></i> Tidak ada jadwal untuk kelas ini
    </div>

    <!-- Empty State for Semua Kelas -->
    <div v-else-if="kelasAktif === 'semua-kelas' && allKelasJadwalList.length === 0 && !loading" class="alert alert-warning">
      <i class="bi bi-exclamation-triangle me-2"></i> 
      Tidak ada jadwal untuk ditampilkan. Pastikan data jadwal sudah ada di sistem.
      <div class="text-xs mt-2 text-muted">
        (Data: {{ allKelasJadwalList.length }} jadwal dimuat, Kelas: {{ sortedKelasList.length }})
      </div>
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="alert alert-info">
      <i class="bi bi-hourglass-split me-2"></i> Memuat data...
    </div>

    <!-- Initial State -->
    <div v-else class="alert alert-secondary">
      <i class="bi bi-cursor-text me-2"></i> Pilih kelas untuk melihat jadwal
    </div>

    <!-- Fullscreen Modal -->
    <div v-if="showFullscreen" class="fullscreen-modal-overlay" @click.self="closeFullscreen">
      <div class="fullscreen-modal-content">
        <!-- Fullscreen Header -->
        <div class="fullscreen-header">
          <div class="fullscreen-title">
            <h5 class="mb-0">Jadwal Pelajaran - {{ kelasAktif === 'semua-kelas' ? 'SEMUA KELAS' : getKelasName(kelasAktif) }}</h5>
          </div>
          <div class="fullscreen-controls">
            <!-- Download Button -->
            <button 
              v-if="kelasAktif"
              @click="downloadPDF" 
              class="btn btn-sm btn-outline-success me-2"
              title="Download as PDF"
            >
              <i class="bi bi-file-pdf"></i> Download PDF
            </button>
            <!-- Close Button -->
            <button 
              @click="closeFullscreen" 
              class="btn btn-sm btn-outline-danger"
              title="Tutup fullscreen"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>

        <!-- Fullscreen Table Content -->
        <div class="fullscreen-body">
          <div ref="fullscreenTableContent">
            <!-- Single Kelas Table -->
            <div v-if="kelasAktif && kelasAktif !== 'semua-kelas'">
              <h6 class="mb-2">{{ getKelasName(kelasAktif) }}</h6>
              <table class="table table-sm mb-0 fullscreen-table">
                <thead>
                  <tr class="bg-light">
                    <th class="p-2 text-center fw-semibold" style="width: 100px;">Jam</th>
                    <th v-for="hariName in hari" :key="hariName" class="p-2 text-center fw-semibold">
                      {{ hariName }}
                    </th>
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
                          <div v-if="getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id" class="fw-semibold" :style="{ color: getKegiatanStyle(getMergedScheduleSingleKelas(hariName, slotIndex).kegiatan_id).color }">
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

            <!-- All Kelas Table -->
            <div v-else-if="kelasAktif === 'semua-kelas'">
              <div class="table-responsive-semua-hari">
                <table class="table mb-0 table-semua-hari">
                  <!-- Tier Headers -->
                  <thead>
                    <tr class="bg-light">
                      <th class="p-1 text-center fw-semibold" style="width: 70px;">
                        <div class="text-xl">Hari</div>
                      </th>
                      <th class="p-1 text-center fw-semibold" style="width: 120px;">
                        <div class="text-xl">Jam</div>
                      </th>
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
                    <!-- Sub-header with kelas names -->
                    <tr class="bg-light">
                      <th style="width: 70px;"></th>
                      <th style="width: 120px;"></th>
                      <template v-for="tier in getTierKeys" :key="`names-${tier}`">
                        <template v-for="(kelas, idx) in groupKelasByTier[tier]" :key="`${tier}-${kelas.id}`">
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
                    <template v-for="(hariName, hariIdx) in hari" :key="hariName">
                      <tr v-for="(slot, slotIndex) in allTimeSlots" :key="`${hariName}-${slot.id}`" class="align-middle">
                        <!-- Hari Column (merged vertically per day) -->
                        <td 
                          v-if="slotIndex === 0"
                          :rowspan="allTimeSlots.length"
                          class="p-2 text-center fw-semibold text-primary"
                          style="width: 70px; vertical-align: middle; border-right: 2px solid #dee2e6;"
                        >
                          <div class="text-xs fw-bold">{{ hariName }}</div>
                        </td>
                        
                        <!-- Jam Column -->
                        <td 
                          v-if="!shouldSkipJamSlot(hariName, slotIndex)"
                          :rowspan="getJamRowspan(hariName, slotIndex)"
                          class="p-1 text-center fw-semibold text-primary" 
                          style="width: 120px; font-size: 0.8rem; border-right: 2px solid #dee2e6; white-space: nowrap;"
                        >
                          {{ slot.jam_mulai }} - {{ slot.jam_selesai }}
                        </td>

                        <!-- Cross-tier merge: if same kegiatan across ALL tiers -->
                        <template v-if="isCrossTierMerged(hariName, slot.id) && !shouldSkipKegiatanSlot(hariName, slotIndex)">
                          <td 
                            :colspan="getCrossTierMergeColspan(hariName, slot.id)"
                            :rowspan="getKegiatanRowspan(hariName, slotIndex)"
                            class="text-center fw-semibold"
                            style="vertical-align: middle; background-color: transparent; padding: 0; width: 50px; min-width: 50px;"
                          >
                            <div class="schedule-cell-all schedule-cell-kegiatan"
                                 :style="getKegiatanStyle(getCrossTierMergeKegiatanId(hariName, slot.id))"
                            >
                              <div class="text-xs fw-semibold w-100 text-center">
                                {{ getKegiatanName(getCrossTierMergeKegiatanId(hariName, slot.id)) }}
                              </div>
                            </div>
                          </td>
                        </template>

                        <!-- Normal tier-based rendering (no cross-tier merge) -->
                        <template v-else v-for="tier in getTierKeys" :key="`tier-${tier}`">
                          <template v-for="(kelas, tierKelasIdx) in groupKelasByTier[tier]" :key="`${hariName}-${slot.id}-${kelas.id}`">
                            <!-- Skip cells that are part of vertical merge (not the first one) -->
                            <template v-if="!isVerticalMergeSkipped(hariName, slotIndex, tier, tierKelasIdx)">
                              <!-- Render merged cell untuk kegiatan yang sama -->
                              <td 
                                v-if="shouldRenderMergedSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                                :colspan="getMergedSpanSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                                :rowspan="shouldRenderVerticalMergeCell(hariName, slotIndex, tier, tierKelasIdx) ? getVerticalMergeRowspan(hariName, slotIndex, tier, tierKelasIdx) : 1"
                                class="text-center fw-semibold"
                                style="vertical-align: middle; background-color: transparent; padding: 0; width: 50px; min-width: 50px;"
                              >
                                <div class="schedule-cell-all schedule-cell-kegiatan"
                                     :style="getKegiatanStyle(getMergedKegiatanIdSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx))"
                                     :colspan="getMergedSpanSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)"
                                >
                                  <div class="text-xs fw-semibold w-100 text-center">
                                    {{ getKegiatanName(getMergedKegiatanIdSemuaHariTierAware(hariName, slot.id, tier, tierKelasIdx)) }}
                                  </div>
                                </div>
                              </td>

                              <!-- Render normal cell jika tidak di-merge -->
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
                                <div v-if="getScheduleForAllKelas(slot.id, hariName, kelas.id)" 
                                     class="schedule-cell-all"
                                     :style="getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id ? getKegiatanStyle(getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id) : {}"
                                >
                                  <div v-if="getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id" 
                                       class="text-xs fw-semibold px-1 py-1">
                                    {{ getKegiatanName(getScheduleForAllKelas(slot.id, hariName, kelas.id).kegiatan_id) }}
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
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue'
import { jsPDF } from 'jspdf'
import html2canvas from 'html2canvas'
import { jadwalService, kelasService, mapelService, kegiatanService } from '../services/resourceService'

const MAPEL_KODE_ORDER_STORAGE_KEY = 'smart-jadwal-mapel-kode-order-v1'

const kelasAktif = ref(null)
const kelasOptions = ref([])
const jadwalList = ref([])
const allKelasJadwalList = ref([])
const mapelList = ref([])
const kegiatanList = ref([])
const loading = ref(false)
const hariTerpilih = ref('semua-hari')
const mergedCellCache = ref({})
const mergedCellStatusCache = ref({}) // Track which cells are merged/skipped
const mergedCellSemuaHariCache = ref({}) // Cache for "Semua Hari" merged cells
const verticalMergeCache = ref({}) // Cache for vertical kegiatan merge
const verticalMergeCacheSingleKelas = ref({}) // Cache for single kelas vertical merge
const verticalMergeCacheSemauKelas = ref({}) // Cache for semua kelas vertical merge (per kelas per day)
const showFullscreen = ref(false)
const fullscreenTableContent = ref(null)
let refreshInterval = null

const hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']

// Refresh kelas list
const refreshKelasList = async () => {
  try {
    loading.value = true
    console.log('🔄 Refresh button clicked')
    
    const response = await kelasService.getAll()
    if (response.data.success) {
      const newKelasList = response.data.data.map(k => ({ id: k.id, nama: k.nama }))
      kelasOptions.value = newKelasList
      console.log('✅ Kelas list refreshed:', newKelasList.length, 'kelas')
      
      // Clear all caches
      mergedCellCache.value = {}
      mergedCellStatusCache.value = {}
      mergedCellSemuaHariCache.value = {}
      verticalMergeCache.value = {}
      verticalMergeCacheSingleKelas.value = {}
      verticalMergeCacheSemauKelas.value = {}
      horizontalMergeSingleKelasCache.value = {}
      getJamMergeSemauHariCache.value = {}
      getKegiatanMergeSemauHariCache.value = {}
      console.log('✨ All caches cleared')
      
      // Reload jadwal based on current selection
      if (kelasAktif.value === 'semua-kelas') {
        console.log('📖 Reloading semua-kelas jadwal...')
        await loadAllKelasJadwal()
        console.log('✅ Semua-kelas jadwal reloaded:', allKelasJadwalList.value.length, 'items')
      } else if (kelasAktif.value) {
        console.log('📖 Reloading jadwal for kelas:', kelasAktif.value)
        await loadJadwal(kelasAktif.value)
        console.log('✅ Kelas jadwal reloaded:', jadwalList.value.length, 'items')
      }
    }
  } catch (error) {
    console.error('❌ Error refreshing kelas list:', error)
  } finally {
    loading.value = false
  }
}

// Handle page visibility change - refresh when tab becomes visible
const handleVisibilityChange = async () => {
  if (document.visibilityState === 'visible') {
    console.log('Page became visible, refreshing kelas list...')
    await refreshKelasList()
  }
}

// Load kelas list on mount
onMounted(async () => {
  try {
    loading.value = true
    const response = await kelasService.getAll()
    if (response.data.success) {
      kelasOptions.value = response.data.data.map(k => ({ id: k.id, nama: k.nama }))
      
      // Restore saved kelas from localStorage, default to semua-kelas
      const savedKelas = localStorage.getItem('lihatJadwalKelas')
      if (savedKelas) {
        const kelasValue = savedKelas === 'semua-kelas' ? 'semua-kelas' : parseInt(savedKelas)
        kelasAktif.value = kelasValue
        console.log('Restored saved kelas:', kelasValue)
      } else {
        // Default to semua-kelas if not saved
        kelasAktif.value = 'semua-kelas'
        console.log('No saved kelas, defaulting to semua-kelas')
      }
    }

    const mapelResponse = await mapelService.getAll()
    if (mapelResponse.data.success) {
      mapelList.value = mapelResponse.data.data
      console.log('Mapel loaded:', mapelList.value.length)
    }

    const kegiatanResponse = await kegiatanService.getAll()
    if (kegiatanResponse.data.success) {
      kegiatanList.value = kegiatanResponse.data.data
      console.log('Kegiatan loaded:', kegiatanList.value)
    }
    
    // Setup visibility change listener
    document.addEventListener('visibilitychange', handleVisibilityChange)
    
    // Setup periodic refresh every 30 seconds
    refreshInterval = setInterval(refreshKelasList, 30000)
    
  } catch (error) {
    console.error('Error loading data:', error)
    alert('Gagal memuat data kelas, mapel, dan kegiatan')
  } finally {
    loading.value = false
  }
})

// Watch kelasAktif to load jadwal and save to localStorage
watch(() => kelasAktif.value, async (newKelasId) => {
  console.log('🔄 Watch triggered: kelasAktif changed to:', newKelasId)
  console.log('📊 Current jadwalList before clear:', jadwalList.value.length)
  
  // Clear all caches when kelas changes
  mergedCellCache.value = {}
  mergedCellStatusCache.value = {}
  mergedCellSemuaHariCache.value = {}
  verticalMergeCache.value = {}
  verticalMergeCacheSingleKelas.value = {} // Clear vertical merge cache
  verticalMergeCacheSemauKelas.value = {} // Clear semua kelas vertical merge cache
  horizontalMergeSingleKelasCache.value = {}
  getJamMergeSemauHariCache.value = {} // Clear jam merge cache
  getKegiatanMergeSemauHariCache.value = {} // Clear kegiatan merge cache
  console.log('✨ All caches cleared')
  
  if (newKelasId === 'semua-kelas') {
    localStorage.setItem('lihatJadwalKelas', 'semua-kelas')
    console.log('📖 Loading semua kelas jadwal...')
    await loadAllKelasJadwal()
    console.log('✅ Semua kelas jadwal loaded:', allKelasJadwalList.value.length)
  } else if (newKelasId) {
    localStorage.setItem('lihatJadwalKelas', newKelasId)
    console.log('📖 Loading kelas jadwal for:', newKelasId)
    await loadJadwal(newKelasId)
    console.log('✅ Kelas jadwal loaded:', jadwalList.value.length, 'items')
  } else {
    jadwalList.value = []
    allKelasJadwalList.value = []
    console.log('🚫 No class selected')
  }
}, { flush: 'post' })

// Clear cache when hariTerpilih changes
watch(hariTerpilih, () => {
  mergedCellStatusCache.value = {}
  mergedCellSemuaHariCache.value = {}
  verticalMergeCacheSingleKelas.value = {}
  verticalMergeCacheSemauKelas.value = {}
  horizontalMergeSingleKelasCache.value = {}
  getJamMergeSemauHariCache.value = {}
  getKegiatanMergeSemauHariCache.value = {}
})

// Cleanup on component unmount
onBeforeUnmount(() => {
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})

const loadJadwal = async (kelasId) => {
  try {
    loading.value = true
    const response = await jadwalService.getByKelas(kelasId)
    if (response.data.success) {
      jadwalList.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading jadwal:', error)
    jadwalList.value = []
  } finally {
    loading.value = false
  }
}

const loadAllKelasJadwal = async () => {
  try {
    loading.value = true
    // Load all schedules
    console.log('Loading all jadwal...')
    const response = await jadwalService.getAll()
    console.log('Jadwal response:', response)
    if (response.data.success) {
      allKelasJadwalList.value = response.data.data
      console.log('Jadwal loaded successfully:', allKelasJadwalList.value.length, 'items')
      // Clear all caches when data changes
      mergedCellSemuaHariCache.value = {}
      verticalMergeCache.value = {}
      getJamMergeSemauHariCache.value = {}
      getKegiatanMergeSemauHariCache.value = {}
    } else {
      console.warn('Jadwal API returned success=false')
    }
  } catch (error) {
    console.error('Error loading all jadwal:', error)
    allKelasJadwalList.value = []
  } finally {
    loading.value = false
  }
}

// Sorted kelas list for all kelas view
const sortedKelasList = computed(() => {
  const uniqueKelas = new Map()
  allKelasJadwalList.value.forEach(jadwal => {
    if (jadwal.kelas && !uniqueKelas.has(jadwal.kelas.id)) {
      uniqueKelas.set(jadwal.kelas.id, jadwal.kelas)
    }
  })
  
  // Sort by kelas nama
  return Array.from(uniqueKelas.values()).sort((a, b) => {
    // Extract tingkat (X, XI, XII)
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

// Generate all unique time points from schedules
const getAllTimePoints = () => {
  if (jadwalList.value.length === 0) return []
  
  const times = new Set()
  jadwalList.value.forEach(jadwal => {
    times.add(jadwal.jam_mulai)
    times.add(jadwal.jam_selesai)
  })
  
  return Array.from(times).sort((a, b) => a.localeCompare(b))
}

// Generate all unique time points for all kelas view
const getAllTimePointsForAllKelas = () => {
  if (allKelasJadwalList.value.length === 0) return []
  
  const times = new Set()
  allKelasJadwalList.value.forEach(jadwal => {
    times.add(jadwal.jam_mulai)
    times.add(jadwal.jam_selesai)
  })
  
  return Array.from(times).sort((a, b) => a.localeCompare(b))
}

// Generate time slots from consecutive time points
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

// Generate time slots for all kelas view
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

// Track which schedules have been rendered for each day
const renderedSchedules = computed(() => {
  const rendered = {}
  return rendered
})

// Get the schedule that contains this time slot
const getScheduleForTimeDay = (slotId, hari) => {
  const [jam_mulai, jam_selesai] = slotId.split('-')
  const schedule = jadwalList.value.find(j => 
    j.hari === hari && 
    j.jam_mulai <= jam_mulai && 
    j.jam_selesai > jam_mulai
  )
  return schedule
}

// Get schedule for all kelas view - always Monday for now (dapat diperluas ke hari lain)
const timeToMinutes = (timeStr) => {
  if (!timeStr) return 0
  const [hours, minutes] = timeStr.split(':').map(Number)
  return hours * 60 + minutes
}

const getScheduleForAllKelas = (slotId, hari, kelasId) => {
  const [jam_mulai, jam_selesai] = slotId.split('-')
  const slotStart = timeToMinutes(jam_mulai)
  
  const schedule = allKelasJadwalList.value.find(j => 
    j.kelas_id === kelasId &&
    j.hari === hari && 
    timeToMinutes(j.jam_mulai) <= slotStart && 
    timeToMinutes(j.jam_selesai) > slotStart
  )
  return schedule
}

const getMapelName = (mapelId) => {
  const mapel = mapelList.value.find(m => m.id === mapelId)
  return mapel ? mapel.nama : ''
}
                                
const getMapelDisplayName = (mapelId) => {
  const mapelName = getMapelName(mapelId)
  return mapelName || 'Pelajaran'
}

const getMapelGuru = (mapelId, guruId = null) => {
  const mapel = mapelList.value.find(m => m.id === mapelId)
  if (!mapel || !mapel.guru) return '-'
  
  // Jika ada guru_id spesifik, cari guru tersebut
  if (guruId && Array.isArray(mapel.guru)) {
    const guru = mapel.guru.find(g => g.id === guruId)
    return guru ? guru.nama : '-'
  }
  
  // Jika guru adalah array object, ambil nama-nama mereka
  if (Array.isArray(mapel.guru)) {
    return mapel.guru.map(g => g.nama).join(', ')
  }
  
  // Jika guru adalah string, return langsung
  return mapel.guru
}

const getCurrentGuruIds = () => {
  const guruIds = new Set()

  mapelList.value.forEach((mapel) => {
    if (mapel.guru && Array.isArray(mapel.guru)) {
      mapel.guru.forEach((guru) => {
        const guruId = Number(guru.id)
        if (Number.isInteger(guruId) && guruId > 0) {
          guruIds.add(guruId)
        }
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

    return parsed
      .map((item) => Number(item))
      .filter((item) => Number.isInteger(item) && item > 0)
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
        if (Number(guru.id) === Number(guruId)) {
          sameGuruKeys.push(`${mapel.id}-${guru.id}`)
        }
      })
    }
  })

  if (sameGuruKeys.length === 0) return ''

  const mapelPosition = sameGuruKeys.findIndex((key) => key === relationKey)
  if (mapelPosition < 0) return ''

  if (sameGuruKeys.length === 1) {
    return String(baseCode)
  }

  const letter = String.fromCharCode(65 + Math.max(0, mapelPosition))
  return `${baseCode}${letter}`
}

const getGuruKode = (jadwal) => {
  if (!jadwal) return ''
  
  // Jika kegiatan, return nama kegiatan
  if (jadwal.kegiatan_id) {
    return getKegiatanName(jadwal.kegiatan_id)
  }
  
  // Jika bukan mapel, return kosong
  if (!jadwal.mapel_id || !jadwal.guru_id) return ''

  const shiftedCode = getRelationDisplayCode(jadwal.mapel_id, jadwal.guru_id)
  if (shiftedCode) return shiftedCode

  return jadwal.guru_id.toString()
}

const getKegiatanName = (kegiatanId) => {
  if (!kegiatanId) return ''
  const kegiatan = kegiatanList.value.find(k => k.id === kegiatanId)
  return kegiatan ? kegiatan.nama : ''
}

const getKegiatanDisplayName = (kegiatanId) => {
  const kegiatanName = getKegiatanName(kegiatanId)
  return kegiatanName || 'Kegiatan'
}

const getGuruName = (guruId) => {
  if (!guruId) return ''
  // Cari guru dari semua mapel
  for (const mapel of mapelList.value) {
    if (mapel.guru && Array.isArray(mapel.guru)) {
      const guru = mapel.guru.find(g => g.id === guruId)
      if (guru) return guru.nama
    }
  }
  return ''
}

const getKegiatanStyle = (kegiatanId) => {
  const colors = [
    { bg: '#FFE8E8', text: '#C1272D' }, // Red
    { bg: '#FFF3E0', text: '#E65100' }, // Orange
    { bg: '#FFF9C4', text: '#F57F17' }, // Yellow
    { bg: '#E8F5E9', text: '#2E7D32' }, // Green
    { bg: '#E3F2FD', text: '#1565C0' }, // Blue
    { bg: '#F3E5F5', text: '#6A1B9A' }, // Purple
    { bg: '#FCE4EC', text: '#C2185B' }  // Pink
  ]
  
  // Use kegiatan_id as index (wrap around with modulo)
  const colorIndex = (kegiatanId - 1) % colors.length
  const color = colors[colorIndex]
  
  return {
    backgroundColor: color.bg,
    color: color.text
  }
}

// Helper function to detect merged cells for activities
const computeMergedCellsForSlot = (slotId) => {
  const kelasList = sortedKelasList.value
  if (!kelasList || kelasList.length === 0) return {}
  
  const mergedInfo = {} // key: kelasIdx, value: {type: 'merged', span, kegiatanId} or {type: 'skipped'}
  const processed = new Set()
  
  for (let i = 0; i < kelasList.length; i++) {
    if (processed.has(i)) continue
    
    const schedule = getScheduleForAllKelas(slotId, hariTerpilih.value, kelasList[i]?.id)
    
    if (!schedule) continue
    
    // For activities, check if subsequent kelas have the same activity
    if (schedule.kegiatan_id) {
      let span = 1
      
      for (let j = i + 1; j < kelasList.length; j++) {
        const nextSchedule = getScheduleForAllKelas(slotId, hariTerpilih.value, kelasList[j]?.id)
        
        if (nextSchedule && nextSchedule.kegiatan_id === schedule.kegiatan_id) {
          span++
        } else {
          break
        }
      }
      
      // If span > 1, mark as merged
      if (span > 1) {
        mergedInfo[i] = {
          type: 'merged',
          span: span,
          kegiatanId: schedule.kegiatan_id,
          schedule: schedule
        }
        
        // Mark subsequent kelas as skipped
        for (let j = i + 1; j < i + span; j++) {
          mergedInfo[j] = { type: 'skipped' }
          processed.add(j)
        }
        
        processed.add(i)
      }
    }
  }
  
  return mergedInfo
}

const getMergedCellInfoCached = (slotId) => {
  if (!mergedCellStatusCache.value[slotId]) {
    mergedCellStatusCache.value[slotId] = computeMergedCellsForSlot(slotId)
  }
  return mergedCellStatusCache.value[slotId]
}

const shouldRenderMergedCell = (slotId, kelasIdx) => {
  const mergedInfo = getMergedCellInfoCached(slotId)
  return mergedInfo[kelasIdx]?.type === 'merged'
}

const getMergedCellSpan = (slotId, kelasIdx) => {
  const mergedInfo = getMergedCellInfoCached(slotId)
  return mergedInfo[kelasIdx]?.span || 1
}

const isPartOfMergedCell = (slotId, kelasIdx) => {
  const mergedInfo = getMergedCellInfoCached(slotId)
  return mergedInfo[kelasIdx]?.type === 'skipped'
}

const isMergedKegiatan = (slotId, kelasIdx) => {
  const mergedInfo = getMergedCellInfoCached(slotId)
  return mergedInfo[kelasIdx]?.type === 'merged'
}

const getMergedKegiatanId = (slotId, kelasIdx) => {
  const mergedInfo = getMergedCellInfoCached(slotId)
  return mergedInfo[kelasIdx]?.kegiatanId || null
}

const getMergedGuruKode = (slotId, kelasIdx) => {
  const mergedInfo = getMergedCellInfoCached(slotId)
  if (mergedInfo[kelasIdx]?.schedule) {
    return getGuruKode(mergedInfo[kelasIdx].schedule)
  }
  return ''
}

// Helper functions for tier (perangkat) and colors
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
  if (tier === 3) return { bg: '#E8F5E9', text: '#2E7D32' } // Hijau
  if (tier === 2) return { bg: '#F3E5F5', text: '#6A1B9A' } // Purple
  if (tier === 1) return { bg: '#FFF9C4', text: '#F57F17' } // Kuning
  return { bg: '#FFFFFF', text: '#000000' }
}

const groupKelasByTier = computed(() => {
  const groups = {}
  sortedKelasList.value.forEach(kelas => {
    const tier = getTierFromKelas(kelas.nama)
    if (!groups[tier]) {
      groups[tier] = []
    }
    groups[tier].push(kelas)
  })
  return groups
})

const getTierKeys = computed(() => {
  return Object.keys(groupKelasByTier.value).map(Number).sort((a, b) => a - b)
})

const getScheduleStyle = (schedule) => {
  if (!schedule) return {}
  
  // Jika ada kegiatan, gunakan kegiatan color
  if (schedule.kegiatan_id) {
    return getKegiatanStyle(schedule.kegiatan_id)
  }
  
  // Jika ada mapel/guru, gunakan tier color
  if (schedule.mapel_id) {
    // Get tier from the kelas_id
    const kelas = sortedKelasList.value.find(k => k.id === schedule.kelas_id)
    if (kelas) {
      const tier = getTierFromKelas(kelas.nama)
      return getTierColor(tier)
    }
  }
  
  return {}
}

// Helper functions for cross-tier horizontal merge (when same kegiatan across ALL tiers)
const computeCrossTierMerge = (hariName, slotId) => {
  const tierKeys = getTierKeys.value || []
  
  if (!tierKeys || tierKeys.length === 0) return { merged: false, tiers: [] }
  
  let firstKegiatan = null
  let hasFirst = false
  
  for (const tier of tierKeys) {
    const kelasList = groupKelasByTier.value[tier] || []
    if (kelasList.length === 0) {
      return { merged: false, tiers: [] }
    }

    for (const kelas of kelasList) {
      const schedule = getScheduleForAllKelas(slotId, hariName, kelas.id)
      const kegiatanId = schedule?.kegiatan_id || null

      // Cross-tier merge hanya jika semua kelas punya kegiatan yang sama (non-null).
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

const isCrossTierMerged = (hariName, slotId) => {
  const mergeInfo = computeCrossTierMerge(hariName, slotId)
  return mergeInfo.merged
}

const getCrossTierMergeColspan = (hariName, slotId) => {
  const mergeInfo = computeCrossTierMerge(hariName, slotId)
  if (!mergeInfo.merged) return 0

  // Gunakan total kolom kelas agar jumlah kolom selalu konsisten dengan header.
  return sortedKelasList.value.length
}

const getCrossTierMergeKegiatanId = (hariName, slotId) => {
  const mergeInfo = computeCrossTierMerge(hariName, slotId)
  return mergeInfo.kegiatanId || null
}

// Helper functions for vertical kegiatan merge
const computeVerticalKegiatanMerge = (hariName, tier) => {
  const kelasList = groupKelasByTier.value[tier] || []
  const slotsList = allTimeSlots.value || []
  
  if (!kelasList || !slotsList || kelasList.length === 0 || slotsList.length === 0) return {}
  
  const mergeInfo = {} // key: `${tierKelasIdx}`, value: { rowspan, kegiatanId, startSlotIdx, isSkipped }
  
  // For each kelas in tier
  for (let tierKelasIdx = 0; tierKelasIdx < kelasList.length; tierKelasIdx++) {
    const kelas = kelasList[tierKelasIdx]
    let currentKegiatanId = null
    let mergeStart = null
    
    // Iterate through slots
    for (let slotIdx = 0; slotIdx < slotsList.length; slotIdx++) {
      const slot = slotsList[slotIdx]
      const schedule = getScheduleForAllKelas(slot.id, hariName, kelas.id)
      const slotKegiatanId = schedule?.kegiatan_id || null
      
      if (tierKelasIdx === 0 && slotIdx < 4) {
        console.log(`[VerticalMerge Debug] ${hariName} - Tier ${tier}, Kelas ${kelas.nama}, Slot ${slotIdx} (${slot.jam_mulai}): kegiatan=${slotKegiatanId}`)
      }
      
      if (slotKegiatanId && slotKegiatanId === currentKegiatanId) {
        // Same kegiatan, continue merging
        continue
      } else {
        // Kegiatan changed or ended
        if (currentKegiatanId && mergeStart !== null) {
          const rowspan = slotIdx - mergeStart
          if (rowspan > 1) {
            const key = `${tierKelasIdx}-${mergeStart}`
            mergeInfo[key] = {
              rowspan,
              kegiatanId: currentKegiatanId,
              startSlotIdx: mergeStart
            }
            
            // Mark subsequent slots as skipped
            for (let j = mergeStart + 1; j < slotIdx; j++) {
              const skipKey = `${tierKelasIdx}-${j}-skipped`
              mergeInfo[skipKey] = true
            }
          }
        }
        
        // Start new merge if has kegiatan
        if (slotKegiatanId) {
          currentKegiatanId = slotKegiatanId
          mergeStart = slotIdx
        } else {
          currentKegiatanId = null
          mergeStart = null
        }
      }
    }
    
    // Handle last kegiatan
    if (currentKegiatanId && mergeStart !== null) {
      const rowspan = slotsList.length - mergeStart
      if (rowspan > 1) {
        const key = `${tierKelasIdx}-${mergeStart}`
        mergeInfo[key] = {
          rowspan,
          kegiatanId: currentKegiatanId,
          startSlotIdx: mergeStart
        }
        
        for (let j = mergeStart + 1; j < slotsList.length; j++) {
          const skipKey = `${tierKelasIdx}-${j}-skipped`
          mergeInfo[skipKey] = true
        }
      }
    }
  }
  
  return mergeInfo
}

const getVerticalMergeInfo = (hariName, tier) => {
  const cacheKey = `${hariName}-tier${tier}`
  if (!verticalMergeCache.value[cacheKey]) {
    const mergeData = computeVerticalKegiatanMerge(hariName, tier)
    verticalMergeCache.value[cacheKey] = mergeData
    // Debug log
    if (Object.keys(mergeData).length > 0) {
      console.log(`[VerticalMerge] ${cacheKey}:`, mergeData)
    }
  }
  return verticalMergeCache.value[cacheKey]
}

const shouldRenderVerticalMergeCell = (hariName, slotIdx, tier, tierKelasIdx) => {
  const mergeInfo = getVerticalMergeInfo(hariName, tier)
  const key = `${tierKelasIdx}-${slotIdx}`
  return mergeInfo[key]?.rowspan > 1
}

const getVerticalMergeRowspan = (hariName, slotIdx, tier, tierKelasIdx) => {
  const mergeInfo = getVerticalMergeInfo(hariName, tier)
  const key = `${tierKelasIdx}-${slotIdx}`
  return mergeInfo[key]?.rowspan || 1
}

const isVerticalMergeSkipped = (hariName, slotIdx, tier, tierKelasIdx) => {
  const mergeInfo = getVerticalMergeInfo(hariName, tier)
  const key = `${tierKelasIdx}-${slotIdx}-skipped`
  const isSkipped = mergeInfo[key] === true
  
  // Debug for first few cells
  if (tier === 1 && tierKelasIdx === 0 && slotIdx < 4) {
    console.log(`[VerticalMerge] isSkipped(${hariName}, slot${slotIdx}, tier${tier}, kelas${tierKelasIdx}): ${isSkipped}`, mergeInfo)
  }
  
  return isSkipped
}

const getVerticalMergeKegiatanId = (hariName, slotIdx, tier, tierKelasIdx) => {
  const mergeInfo = getVerticalMergeInfo(hariName, tier)
  const key = `${tierKelasIdx}-${slotIdx}`
  return mergeInfo[key]?.kegiatanId || null
}

// ==================== Jam Column Merge for "Semua Hari" View ====================
const computeConsecutiveCrossTierMerge = (hariName) => {
  const slotsList = allTimeSlots.value
  if (!slotsList || slotsList.length === 0) return {}

  const mergeInfo = {}
  let i = 0

  while (i < slotsList.length) {
    const currentSlot = slotsList[i]
    if (!isCrossTierMerged(hariName, currentSlot.id)) {
      i++
      continue
    }

    const currentKegiatanId = getCrossTierMergeKegiatanId(hariName, currentSlot.id)
    if (!currentKegiatanId) {
      i++
      continue
    }

    let rowspan = 1
    let j = i + 1

    while (j < slotsList.length) {
      const nextSlot = slotsList[j]
      if (!isCrossTierMerged(hariName, nextSlot.id)) {
        break
      }

      const nextKegiatanId = getCrossTierMergeKegiatanId(hariName, nextSlot.id)
      if (nextKegiatanId !== currentKegiatanId) {
        break
      }

      rowspan++
      j++
    }

    if (rowspan > 1) {
      mergeInfo[i] = { type: 'merge', rowspan }
      for (let k = i + 1; k < i + rowspan; k++) {
        mergeInfo[k] = { type: 'skip' }
      }
      i += rowspan
    } else {
      i++
    }
  }

  return mergeInfo
}

// Compute vertical merge for jam column based on cross-tier kegiatan merge
const computeJamMergeSemauHari = (hariName) => {
  return computeConsecutiveCrossTierMerge(hariName)
}

const getJamMergeSemauHariCache = ref({})

const getJamMergeSemauHariInfo = (hariName) => {
  if (!getJamMergeSemauHariCache.value[hariName]) {
    getJamMergeSemauHariCache.value[hariName] = computeJamMergeSemauHari(hariName)
  }
  return getJamMergeSemauHariCache.value[hariName]
}

const shouldSkipJamSlot = (hariName, slotIdx) => {
  const mergeInfo = getJamMergeSemauHariInfo(hariName)
  return mergeInfo[slotIdx]?.type === 'skip'
}

const getJamRowspan = (hariName, slotIdx) => {
  const mergeInfo = getJamMergeSemauHariInfo(hariName)
  return mergeInfo[slotIdx]?.rowspan || 1
}

// ==================== Kegiatan Merge for "Semua Hari" View ====================
// Merge kegiatan yang sama pada slot berturut-turut di semua tier
const computeKegiatanMergeSemauHari = (hariName) => {
  return computeConsecutiveCrossTierMerge(hariName)
}

const getKegiatanMergeSemauHariCache = ref({})

const getKegiatanMergeSemauHariInfo = (hariName) => {
  if (!getKegiatanMergeSemauHariCache.value[hariName]) {
    getKegiatanMergeSemauHariCache.value[hariName] = computeKegiatanMergeSemauHari(hariName)
  }
  return getKegiatanMergeSemauHariCache.value[hariName]
}

const shouldSkipKegiatanSlot = (hariName, slotIdx) => {
  const mergeInfo = getKegiatanMergeSemauHariInfo(hariName)
  return mergeInfo[slotIdx]?.type === 'skip'
}

const getKegiatanRowspan = (hariName, slotIdx) => {
  const mergeInfo = getKegiatanMergeSemauHariInfo(hariName)
  return mergeInfo[slotIdx]?.rowspan || 1
}

// Helper functions for vertical kegiatan merge
const computeMergedCellsForSemuaHariTierAware = (hariName, slotId, tier) => {
  const kelasList = groupKelasByTier.value[tier] || []
  if (!kelasList || kelasList.length === 0) return {}
  
  const mergedInfo = {} // key: tierKelasIdx, value: {type: 'merged', span, kegiatanId} or {type: 'skipped'}
  const processed = new Set()
  
  for (let i = 0; i < kelasList.length; i++) {
    if (processed.has(i)) continue
    
    const kelas = kelasList[i]
    const schedule = getScheduleForAllKelas(slotId, hariName, kelas.id)
    
    if (!schedule) continue
    
    // Only merge kegiatan items
    if (schedule.kegiatan_id) {
      let span = 1
      const kegiatanId = schedule.kegiatan_id
      
      // Check if subsequent kelas in same tier have the same kegiatan
      for (let j = i + 1; j < kelasList.length; j++) {
        if (processed.has(j)) break
        
        const nextKelas = kelasList[j]
        const nextSchedule = getScheduleForAllKelas(slotId, hariName, nextKelas.id)
        
        // Only continue if next also has same kegiatan
        if (nextSchedule && nextSchedule.kegiatan_id === kegiatanId) {
          span++
        } else {
          break
        }
      }
      
      // If span > 1, mark as merged
      if (span > 1) {
        mergedInfo[i] = {
          type: 'merged',
          span: span,
          kegiatanId: kegiatanId,
          schedule: schedule
        }
        
        // Mark subsequent kelas as skipped
        for (let j = i + 1; j < i + span; j++) {
          mergedInfo[j] = { type: 'skipped' }
          processed.add(j)
        }
        
        processed.add(i)
      }
    }
  }
  
  return mergedInfo
}

const getMergedSemuaHariTierAwareCacheKey = (hariName, slotId, tier) => {
  return `${hariName}-${slotId}-tier${tier}`
}

const getMergedSemuaHariTierAwareInfo = (hariName, slotId, tier) => {
  const cacheKey = getMergedSemuaHariTierAwareCacheKey(hariName, slotId, tier)
  if (!mergedCellSemuaHariCache.value[cacheKey]) {
    const result = computeMergedCellsForSemuaHariTierAware(hariName, slotId, tier)
    mergedCellSemuaHariCache.value[cacheKey] = result
    
    // Debug: Check if merge detected
    if (Object.values(result).some(info => info?.type === 'merged')) {
      console.log(`DEBUG: Merge detected untuk ${hariName} ${slotId} tier ${tier}:`, result)
    }
  }
  return mergedCellSemuaHariCache.value[cacheKey]
}

const shouldRenderMergedSemuaHariTierAware = (hariName, slotId, tier, tierKelasIdx) => {
  const mergedInfo = getMergedSemuaHariTierAwareInfo(hariName, slotId, tier)
  return mergedInfo[tierKelasIdx]?.type === 'merged'
}

const getMergedSpanSemuaHariTierAware = (hariName, slotId, tier, tierKelasIdx) => {
  const mergedInfo = getMergedSemuaHariTierAwareInfo(hariName, slotId, tier)
  return mergedInfo[tierKelasIdx]?.span || 1
}

const isPartOfMergedSemuaHariTierAware = (hariName, slotId, tier, tierKelasIdx) => {
  const mergedInfo = getMergedSemuaHariTierAwareInfo(hariName, slotId, tier)
  return mergedInfo[tierKelasIdx]?.type === 'skipped'
}

const getMergedKegiatanIdSemuaHariTierAware = (hariName, slotId, tier, tierKelasIdx) => {
  const mergedInfo = getMergedSemuaHariTierAwareInfo(hariName, slotId, tier)
  return mergedInfo[tierKelasIdx]?.kegiatanId || null
}

// ==================== Vertical Merge for Single Kelas View ====================
// Compute vertical merge for same activity/mapel across consecutive time slots
const computeVerticalMergeSingleKelas = (hari) => {
  const slots = timeSlots.value
  if (!slots || slots.length === 0) return {}
  
  const mergeInfo = {} // key: `${slotIndex}`, value: { type: 'merge', rowspan, schedule } or { type: 'skip' } or { type: 'normal', schedule }
  let i = 0
  
  while (i < slots.length) {
    const currentSchedule = getScheduleForTimeDay(slots[i].id, hari)
    
    if (!currentSchedule) {
      i++
      continue
    }
    
    // Get the content identifier (kegiatan_id or mapel_id)
    const currentContent = currentSchedule.kegiatan_id || currentSchedule.mapel_id
    const currentGuru = currentSchedule.guru_id
    
    // Check consecutive slots with same activity/mapel
    let rowspan = 1
    let j = i + 1
    
    while (j < slots.length) {
      const nextSchedule = getScheduleForTimeDay(slots[j].id, hari)
      if (!nextSchedule) break
      
      const nextContent = nextSchedule.kegiatan_id || nextSchedule.mapel_id
      const nextGuru = nextSchedule.guru_id
      
      // Strict check: merge hanya jika:
      // 1. Sama activity/mapel dan guru
      // 2. jam_mulai slot berikutnya = jam_selesai slot sebelumnya (adjacent/consecutive)
      const currentSlotEnd = slots[j - 1].jam_selesai
      const nextSlotStart = slots[j].jam_mulai
      
      if ((currentSchedule.kegiatan_id && nextSchedule.kegiatan_id === currentContent) ||
          (!currentSchedule.kegiatan_id && !nextSchedule.kegiatan_id && nextContent === currentContent && nextGuru === currentGuru)) {
        // Check if slots are consecutive (adjacent in time)
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
    
    // If rowspan > 1, mark for merge
    if (rowspan > 1) {
      mergeInfo[i] = {
        type: 'merge',
        rowspan: rowspan,
        schedule: currentSchedule
      }
      
      // Mark subsequent slots as skip
      for (let k = i + 1; k < i + rowspan; k++) {
        mergeInfo[k] = { type: 'skip' }
      }
      
      i += rowspan
    } else {
      // Even if no merge, store the schedule so it can be displayed
      mergeInfo[i] = {
        type: 'normal',
        rowspan: 1,
        schedule: currentSchedule
      }
      i++
    }
  }
  
  return mergeInfo
}

const getVerticalMergeSingleKelasInfo = (hari) => {
  // Include kelasAktif in cache key to prevent cross-class cache collision
  const cacheKey = `${kelasAktif.value}-${hari}`
  if (!verticalMergeCacheSingleKelas.value[cacheKey]) {
    verticalMergeCacheSingleKelas.value[cacheKey] = computeVerticalMergeSingleKelas(hari)
  }
  return verticalMergeCacheSingleKelas.value[cacheKey]
}

const shouldSkipSlotSingleKelas = (hari, slotIndex) => {
  const mergeInfo = getVerticalMergeSingleKelasInfo(hari)
  return mergeInfo[slotIndex]?.type === 'skip'
}

const getRowspanForSlotSingleKelas = (hari, slotIndex) => {
  const mergeInfo = getVerticalMergeSingleKelasInfo(hari)
  return mergeInfo[slotIndex]?.rowspan || 1
}

const getMergedScheduleSingleKelas = (hari, slotIndex) => {
  const mergeInfo = getVerticalMergeSingleKelasInfo(hari)
  const info = mergeInfo[slotIndex]
  // Return schedule for both 'merge' and 'normal' types
  return info?.schedule || null
}

const shouldRenderSingleKelasSlotRow = (slot, slotIndex) => {
  // Render row hanya jika ada konten yang benar-benar bisa ditampilkan
  // dan jadwal mulai di jam slot ini.
  return hari.some((hariName) => {
    if (shouldSkipSlotSingleKelas(hariName, slotIndex)) return false

    const schedule = getMergedScheduleSingleKelas(hariName, slotIndex)
    if (!schedule) return false

    if (schedule.jam_mulai !== slot.jam_mulai) return false

    if (schedule.kegiatan_id) {
      // Jika kegiatan_id ada, slot dianggap valid meski nama referensi belum termuat.
      return true
    }

    if (schedule.mapel_id) {
      // Jika mapel_id ada, slot dianggap valid meski nama mapel belum termuat.
      return true
    }

    return false
  })
}

// ==================== Horizontal Merge for Single Kelas (same kegiatan across days) ====================
const horizontalMergeSingleKelasCache = ref({})

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
  const cacheKey = `${kelasAktif.value}-${slotIndex}`
  if (!horizontalMergeSingleKelasCache.value[cacheKey]) {
    horizontalMergeSingleKelasCache.value[cacheKey] = computeHorizontalMergeSingleKelas(slotIndex)
  }
  return horizontalMergeSingleKelasCache.value[cacheKey]
}

const shouldSkipHorizontalMergeSingleKelas = (slotIndex, hariIndex) => {
  const info = getHorizontalMergeSingleKelasInfo(slotIndex)
  return info[hariIndex]?.type === 'skip'
}

const getHorizontalMergeColspanSingleKelas = (slotIndex, hariIndex) => {
  const info = getHorizontalMergeSingleKelasInfo(slotIndex)
  return info[hariIndex]?.colspan || 1
}

// ==================== Vertical Merge for Semua Kelas Single-Day View ====================
// Compute vertical merge for same activity/mapel across consecutive time slots per class
const computeVerticalMergeSemauKelas = (hari, kelasId) => {
  const slots = allTimeSlots.value
  if (!slots || slots.length === 0) return {}
  
  const mergeInfo = {} // key: `${slotIndex}`, value: { type: 'merge', rowspan, schedule } or { type: 'skip' } or { type: 'normal', schedule }
  let i = 0
  
  while (i < slots.length) {
    const currentSchedule = getScheduleForAllKelas(slots[i].id, hari, kelasId)
    
    if (!currentSchedule) {
      i++
      continue
    }
    
    // Get the content identifier (kegiatan_id or mapel_id)
    const currentContent = currentSchedule.kegiatan_id || currentSchedule.mapel_id
    const currentGuru = currentSchedule.guru_id
    
    // Check consecutive slots with same activity/mapel
    let rowspan = 1
    let j = i + 1
    
    while (j < slots.length) {
      const nextSchedule = getScheduleForAllKelas(slots[j].id, hari, kelasId)
      if (!nextSchedule) break
      
      const nextContent = nextSchedule.kegiatan_id || nextSchedule.mapel_id
      const nextGuru = nextSchedule.guru_id
      
      // Strict check: merge hanya jika:
      // 1. Sama activity/mapel dan guru
      // 2. jam_mulai slot berikutnya = jam_selesai slot sebelumnya (adjacent/consecutive)
      const currentSlotEnd = slots[j - 1].jam_selesai
      const nextSlotStart = slots[j].jam_mulai
      
      if ((currentSchedule.kegiatan_id && nextSchedule.kegiatan_id === currentContent) ||
          (!currentSchedule.kegiatan_id && !nextSchedule.kegiatan_id && nextContent === currentContent && nextGuru === currentGuru)) {
        // Check if slots are consecutive (adjacent in time)
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
    
    // If rowspan > 1, mark for merge
    if (rowspan > 1) {
      mergeInfo[i] = {
        type: 'merge',
        rowspan: rowspan,
        schedule: currentSchedule
      }
      
      // Mark subsequent slots as skip
      for (let k = i + 1; k < i + rowspan; k++) {
        mergeInfo[k] = { type: 'skip' }
      }
      
      i += rowspan
    } else {
      // Even if no merge, store the schedule so it can be displayed
      mergeInfo[i] = {
        type: 'normal',
        rowspan: 1,
        schedule: currentSchedule
      }
      i++
    }
  }
  
  return mergeInfo
}

const getCacheKeySemauKelasVerticalMerge = (hari, kelasId) => `${hari}-${kelasId}`

const getVerticalMergeSemauKelasInfo = (hari, kelasId) => {
  const cacheKey = getCacheKeySemauKelasVerticalMerge(hari, kelasId)
  if (!verticalMergeCacheSemauKelas.value[cacheKey]) {
    verticalMergeCacheSemauKelas.value[cacheKey] = computeVerticalMergeSemauKelas(hari, kelasId)
  }
  return verticalMergeCacheSemauKelas.value[cacheKey]
}

const shouldSkipSlotSemauKelas = (hari, slotIndex, kelasId) => {
  const mergeInfo = getVerticalMergeSemauKelasInfo(hari, kelasId)
  return mergeInfo[slotIndex]?.type === 'skip'
}

const getRowspanSemauKelas = (hari, slotIndex, kelasId) => {
  const mergeInfo = getVerticalMergeSemauKelasInfo(hari, kelasId)
  return mergeInfo[slotIndex]?.rowspan || 1
}

const getMergedScheduleSemauKelas = (hari, slotIndex, kelasId) => {
  const mergeInfo = getVerticalMergeSemauKelasInfo(hari, kelasId)
  const info = mergeInfo[slotIndex]
  // Return schedule for both 'merge' and 'normal' types
  return info?.schedule || null
}

// ==================== Fullscreen, Zoom & PDF Export ====================
const openFullscreen = () => {
  showFullscreen.value = true
}

const closeFullscreen = () => {
  showFullscreen.value = false
}

const getKelasName = (kelasId) => {
  const kelas = kelasOptions.value.find(k => k.id === kelasId)
  return kelas ? kelas.nama : 'Tidak diketahui'
}

const escapeHtml = (value) => {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
}

const getSingleKelasExportCellContent = (schedule) => {
  if (!schedule) {
    return { html: '', style: '' }
  }

  if (schedule.kegiatan_id) {
    const kegiatanName = escapeHtml(getKegiatanDisplayName(schedule.kegiatan_id))
    const kegiatanStyle = getKegiatanStyle(schedule.kegiatan_id)
    const style = `background:${kegiatanStyle.backgroundColor};color:${kegiatanStyle.color};font-weight:700;`
    return {
      html: `<div style="padding:4px 3px;line-height:1.2;">${kegiatanName}</div>`,
      style
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

  if (!info) {
    return { type: 'empty', rowspan: 1, schedule: null }
  }

  if (info.type === 'skip') {
    return { type: 'skip', rowspan: 0, schedule: null }
  }

  return {
    type: info.type,
    rowspan: info.rowspan || 1,
    schedule: info.schedule || null
  }
}

const buildSingleKelasExportTable = () => {
  const exportRoot = document.createElement('div')
  exportRoot.style.background = '#ffffff'
  exportRoot.style.padding = '0'
  exportRoot.style.margin = '0'

  const rowsHtml = timeSlots.value.map((slot, slotIndex) => {
    const cellsHtml = hari.map((hariName, hariIndex) => {
      const mergeCell = getSingleKelasExportMergeCell(hariName, slotIndex)
      if (mergeCell.type === 'skip') {
        return ''
      }

      if (shouldSkipHorizontalMergeSingleKelas(slotIndex, hariIndex)) {
        return ''
      }

      const cell = getSingleKelasExportCellContent(mergeCell.schedule)
      const horizontalColspan = getHorizontalMergeColspanSingleKelas(slotIndex, hariIndex)
      const rowspanAttr = mergeCell.rowspan > 1 ? ` rowspan="${mergeCell.rowspan}"` : ''
      const colspanAttr = horizontalColspan > 1 ? ` colspan="${horizontalColspan}"` : ''
      return `<td${rowspanAttr}${colspanAttr} style="border:1px solid #d5dbe1;text-align:center;vertical-align:middle;min-width:140px;height:46px;${cell.style}">${cell.html}</td>`
    }).join('')

    return `<tr><td style="border:1px solid #d5dbe1;text-align:center;color:#1f6feb;font-weight:700;white-space:nowrap;min-width:95px;height:46px;padding:0 6px;">${escapeHtml(slot.jam_mulai)} - ${escapeHtml(slot.jam_selesai)}</td>${cellsHtml}</tr>`
  }).join('')

  exportRoot.innerHTML = `
    <table style="border-collapse:collapse;table-layout:fixed;background:#ffffff;font-family:Arial,sans-serif;font-size:11px;width:auto;">
      <thead>
        <tr>
          <th style="border:1px solid #d5dbe1;background:#f1f3f5;color:#212529;font-weight:700;padding:6px 4px;min-width:95px;text-align:center;">Jam</th>
          ${hari.map((hariName) => `<th style="border:1px solid #d5dbe1;background:#f1f3f5;color:#212529;font-weight:700;padding:6px 4px;min-width:140px;text-align:center;">${escapeHtml(hariName)}</th>`).join('')}
        </tr>
      </thead>
      <tbody>${rowsHtml}</tbody>
    </table>
  `

  return exportRoot
}

const downloadPDF = async () => {
  if (!kelasAktif.value) return

  const margin = 3

  try {
    const element = fullscreenTableContent.value
    if (!element) return

    try {
      const isSemuaKelas = kelasAktif.value === 'semua-kelas'
      let canvas = null

      if (isSemuaKelas) {
        const tableWrapper = element.querySelector('.table-responsive-semua-hari')
        const sourceTable = element.querySelector('.table-semua-hari')

        if (!tableWrapper || !sourceTable) {
          throw new Error('Tabel jadwal tidak ditemukan')
        }

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
          if (captureHost.parentNode) {
            captureHost.parentNode.removeChild(captureHost)
          }
        }
      }

      if (!canvas || !canvas.width || !canvas.height) {
        throw new Error('Canvas kosong, tidak bisa dibuat PDF')
      }

      const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
      })

      const pdfTitle = kelasAktif.value === 'semua-kelas'
        ? 'Jadwal Semua Kelas'
        : `Jadwal Kelas ${getKelasName(kelasAktif.value)}`
      const titleSpace = 8

      const pageWidth = pdf.internal.pageSize.getWidth() - (margin * 2)
      const pageHeight = pdf.internal.pageSize.getHeight() - (margin * 2) - titleSpace
      const scale = Math.min(pageWidth / canvas.width, pageHeight / canvas.height)
      const renderWidth = canvas.width * scale
      const renderHeight = canvas.height * scale
      const x = margin + ((pageWidth - renderWidth) / 2)
      const y = margin + titleSpace

      pdf.setFont('helvetica', 'bold')
      pdf.setFontSize(12)
      pdf.text(pdfTitle, pdf.internal.pageSize.getWidth() / 2, margin + 4, { align: 'center' })

      const imgData = canvas.toDataURL('image/png')
      pdf.addImage(imgData, 'PNG', x, y, renderWidth, renderHeight)
      while (pdf.getNumberOfPages() > 1) {
        pdf.deletePage(pdf.getNumberOfPages())
      }

      const filename = `Jadwal-${kelasAktif.value === 'semua-kelas' ? 'Semua-Kelas' : getKelasName(kelasAktif.value)}-${new Date().toISOString().slice(0, 10)}.pdf`
      pdf.save(filename)
    } catch (libError) {
      console.error('Gagal generate PDF dengan jsPDF/html2canvas:', libError)
      alert('Gagal download PDF. Silakan refresh halaman dan coba lagi.')
    }
  } catch (error) {
    console.error('Error downloading PDF:', error)
    alert('Gagal download PDF: ' + error.message)
  }
}
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

.table-responsive-xl {
  max-height: 600px;
  overflow-y: auto;
  overflow-x: auto;
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

table td.p-0 {
  padding: 0 !important;
}

.bg-primary-subtle {
  background-color: #e7f1ff !important;
  min-height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* Styles for "Semua Kelas" table */
.table-semua-kelas {
  table-layout: fixed;
}

.table-semua-kelas thead th {
  background-color: #f8f9fa;
  padding: 8px 4px !important;
  font-size: 0.85rem;
  word-wrap: break-word;
  border: 1px solid #dee2e6 !important;
}

.table-semua-kelas tbody td {
  padding: 4px !important;
  height: 50px;
  text-align: center;
  vertical-align: middle;
  border: 1px solid #dee2e6 !important;
  padding: 0 !important;
}

/* Styles for "Semua Hari" table */
.table-semua-hari {
  table-layout: auto;
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

.schedule-cell-all:not(.schedule-cell-kegiatan) {
  color: inherit;
  font-weight: 500;
}

td[colspan] .schedule-cell-all {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

td[colspan] .schedule-cell-all > div {
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
}

.schedule-cell-all {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  padding: 0;
}

.schedule-cell-all.schedule-cell-kegiatan {
  padding: 0;
}

/* Merged cell styling */
td[colspan] > .schedule-cell-all {
  width: 100%;
  height: 100%;
}

td[colspan] > .schedule-cell-all.schedule-cell-kegiatan {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  min-height: 50px;
}

td[colspan] > .schedule-cell-all.schedule-cell-kegiatan > div {
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

/* Merged cells styling for single kelas view */
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

/* Fullscreen Modal Styles */
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

.fullscreen-table th,
.fullscreen-table-all th {
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

.fullscreen-table td,
.fullscreen-table-all td {
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

.fullscreen-table tbody tr:nth-child(even),
.fullscreen-table-all tbody tr:nth-child(even) {
  background-color: #f8f9fa;
}

/* Override inline styles in fullscreen */
.fullscreen-body .schedule-cell-all {
  width: auto !important;
  padding: 4px !important;
  height: 100%;
  display: flex !important;
  align-items: center;
  justify-content: center;
}

.fullscreen-body .schedule-cell-all.schedule-cell-kegiatan {
  width: 100% !important;
  height: 100% !important;
  padding: 0 !important;
  margin: 0 !important;
  border-radius: 0 !important;
}

.fullscreen-body .schedule-cell-all > div {
  word-wrap: break-word !important;
  overflow-wrap: break-word !important;
  word-break: break-word !important;
  white-space: normal !important;
  width: auto !important;
  max-width: 70px !important;
  line-height: 1.3;
  font-size: 0.7rem;
}

.fullscreen-body table td {
  min-width: auto !important;
  width: auto !important;
  max-width: 75px !important;
  padding: 0 !important;
}

.fullscreen-body table td[style*="kegiatan_id"] {
  padding: 0 !important;
  background-color: inherit !important;
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

.pdf-export-single-mode .fullscreen-table {
  border-collapse: collapse !important;
  table-layout: fixed !important;
}

.pdf-export-single-mode .fullscreen-table thead {
  position: static !important;
  top: auto !important;
}

.pdf-export-single-mode .fullscreen-table th,
.pdf-export-single-mode .fullscreen-table td {
  height: auto !important;
  min-height: 0 !important;
  padding: 6px 4px !important;
  overflow: hidden !important;
}

.pdf-export-single-mode .fullscreen-table td[rowspan] .schedule-cell {
  min-height: 0 !important;
  height: auto !important;
  padding: 4px !important;
  overflow: hidden !important;
  justify-content: flex-start !important;
  align-items: center !important;
}

.pdf-export-single-mode .fullscreen-table td[rowspan] .schedule-cell > div,
.pdf-export-single-mode .fullscreen-table .schedule-cell > div {
  width: 100% !important;
  white-space: normal !important;
  overflow-wrap: break-word !important;
  word-break: break-word !important;
}

.pdf-export-single-mode .fullscreen-table td[rowspan] {
  vertical-align: top !important;
  overflow: hidden !important;
}

.pdf-export-single-mode .fullscreen-table td[rowspan] .h-100,
.pdf-export-single-mode .fullscreen-table td[rowspan] .w-100.h-100 {
  height: auto !important;
  min-height: 0 !important;
  align-items: flex-start !important;
  padding-top: 2px !important;
}

@media (max-width: 991.98px) {
  .fullscreen-body {
    padding: 12px;
  }

  .table-responsive-semua-hari {
    max-height: 70vh;
  }
}

@media (max-width: 767.98px) {
  .container-fluid.py-2 {
    padding-top: 0.5rem !important;
  }

  .fullscreen-header {
    padding: 10px 12px;
    align-items: flex-start;
  }

  .fullscreen-controls {
    width: 100%;
    justify-content: flex-start;
  }

  .fullscreen-body {
    padding: 10px;
  }

  table th,
  table td {
    font-size: 0.72rem;
  }
}

@media (max-width: 575.98px) {
  .card-body .d-flex.align-items-center.gap-3.flex-wrap {
    flex-direction: column;
    align-items: stretch !important;
    gap: 0.5rem !important;
  }

  .card-body .form-select {
    max-width: 100% !important;
  }

  .card-body .btn {
    width: 100%;
  }

  .text-xl {
    font-size: 0.7rem;
  }
}

@media print {
  .fullscreen-modal-overlay {
    background-color: transparent;
  }
  
  .fullscreen-modal-content {
    position: static;
    width: 100%;
    height: 100%;
    background-color: white;
  }
  
  .fullscreen-header {
    display: none;
  }
  
  .fullscreen-body {
    padding: 0;
    overflow: visible;
  }
}
</style>
