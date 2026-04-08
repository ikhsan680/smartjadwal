<template>
  <div class="container-fluid py-4">
    <!-- Header -->
    <div class="mb-5">
      <h1 class="h2 text-dark fw-bold mb-2">Manajemen Mata Pelajaran</h1>
      <p class="text-muted">Kelola mata pelajaran dan kurikulum sekolah</p>
    </div>

    <!-- Add Button -->
    <div class="mb-4 d-flex justify-content-end gap-2">
      <button class="btn btn-outline-primary" @click="openFullscreen" :disabled="loading">
        <i class="bi bi-arrows-fullscreen me-2"></i>Fullscreen
      </button>
      <button class="btn btn-primary" @click="openModalMapel" :disabled="loading">
        <i class="bi bi-plus-lg me-2"></i>Tambah Mapel & Guru
      </button>
    </div>

    <!-- Table List of Mapel-Guru -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 pt-3 pb-2">
        <input
          v-model="mapelSearch"
          type="search"
          class="form-control"
          placeholder="Cari kode, mapel, atau nama guru..."
        />
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="px-3 py-3" style="width: 110px;">Kode</th>
                <th class="px-3 py-3">Mata Pelajaran</th>
                <th class="px-3 py-3">Guru</th>
                <th class="px-3 py-3 text-center" style="width: 170px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredDisplayedMapel.length === 0">
                <td colspan="4" class="text-center text-muted py-4">Belum ada relasi mapel dan guru.</td>
              </tr>
              <tr v-for="item in filteredDisplayedMapel" :key="item.key">
                <td class="px-3 py-3">
                  <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill">{{ item.kode }}</span>
                </td>
                <td class="px-3 py-3 fw-semibold text-dark">{{ item.mapel.nama }}</td>
                <td class="px-3 py-3 text-muted">{{ item.guru.nama }}</td>
                <td class="px-3 py-3">
                  <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-primary" @click="openEditModal(item.mapel.id, item.guru.id)" :disabled="loading" title="Ubah guru mapel">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="deleteGuruMapel(item.mapel.id, item.guru.id)" :disabled="loading" title="Hapus relasi">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Fullscreen Report Modal -->
    <div v-if="showFullscreen" class="fullscreen-modal-overlay" @click.self="closeFullscreen">
      <div class="fullscreen-modal-content">
        <div class="fullscreen-header">
          <h5 class="mb-0">Data Mata Pelajaran & Kode Guru</h5>
          <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-success" @click="downloadPDF" :disabled="loading">
              <i class="bi bi-file-pdf me-1"></i>Download PDF
            </button>
            <button class="btn btn-sm btn-outline-danger" @click="closeFullscreen" :disabled="loading">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>

        <div class="fullscreen-body" ref="fullscreenTableContent">
          <div class="report-paper">
            <table class="table table-bordered report-table mb-0">
              <colgroup>
                <col class="col-no" />
                <col class="col-guru" />
                <col class="col-mapel" />
                <col class="col-kode" />
              </colgroup>
              <thead>
                <tr>
                  <th colspan="4" class="report-title">Kode Guru</th>
                </tr>
                <tr>
                  <th class="no-col">No.</th>
                  <th>Nama Guru</th>
                  <th>Mata Pelajaran</th>
                  <th class="kode-col">Kode</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="mapelReportGroups.length === 0">
                  <td colspan="4" class="text-center text-muted py-4">Belum ada data mapel dan guru.</td>
                </tr>
                <template v-for="group in mapelReportGroups" :key="group.guruId">
                  <tr v-for="(row, rowIdx) in group.rows" :key="`${group.guruId}-${rowIdx}`">
                    <td
                      v-if="rowIdx === 0"
                      :rowspan="group.rows.length"
                      class="merge-cell merge-center no-col"
                    >
                      <span>{{ group.no }}</span>
                    </td>
                    <td
                      v-if="rowIdx === 0"
                      :rowspan="group.rows.length"
                      class="merge-cell merge-middle"
                    >
                      <span>{{ group.guruNama }}</span>
                    </td>
                    <td>{{ row.mapelNama }}</td>
                    <td class="kode-cell kode-col">{{ row.kode }}</td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Edit Master Data (Mapel & Guru) -->
    <div v-if="showModal && modalType === 'masterEdit'" class="modal d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Mapel & Guru</h5>
            <button type="button" class="btn-close" @click="closeEditModal"></button>
          </div>
          <div class="modal-body">
            <!-- Tips -->
            <div class="alert alert-info mb-4" role="alert" style="font-size: 0.9rem;">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Tips:</strong> Ubah nama, lalu klik tombol centang (✓) untuk menyimpan. Tombol akan berubah warna untuk menunjukkan status perubahan.
            </div>
            <ul class="nav nav-tabs mb-4" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-mapel" type="button" data-bs-toggle="tab" data-bs-target="#content-mapel" aria-selected="true">
                  <i class="bi bi-book me-2"></i>Mapel
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-guru" type="button" data-bs-toggle="tab" data-bs-target="#content-guru" aria-selected="false">
                  <i class="bi bi-person me-2"></i>Guru
                </button>
              </li>
            </ul>

            <div class="tab-content">
              <!-- Tab Mapel -->
              <div class="tab-pane fade show active" id="content-mapel" role="tabpanel">
                <div class="mb-3">
                  <label class="form-label">Daftar Mata Pelajaran</label>
                  <div class="list-group" style="max-height: 300px; overflow-y: auto;">
                    <div v-for="mapel in mapelList" :key="mapel.id" class="list-group-item d-flex gap-2">
                      <input v-model="masterEditData.mapel_name[mapel.id]" type="text" class="form-control form-control-sm" :placeholder="mapel.nama" />
                      <button type="button" :class="['btn', 'btn-sm', isMapelChanged(mapel.id) ? 'btn-danger' : 'btn-primary']" @click="saveMapelName(mapel.id)" :disabled="loading">
                        <i class="bi bi-check"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Tab Guru -->
              <div class="tab-pane fade" id="content-guru" role="tabpanel">
                <div class="mb-3">
                  <label class="form-label">Daftar Guru</label>
                  <div class="list-group" style="max-height: 300px; overflow-y: auto;">
                    <div v-for="guru in guruList" :key="guru.id" class="list-group-item d-flex gap-2">
                      <input v-model="masterEditData.guru_name[guru.id]" type="text" class="form-control form-control-sm" :placeholder="guru.nama" />
                      <button type="button" :class="['btn', 'btn-sm', isGuruChanged(guru.id) ? 'btn-danger' : 'btn-primary']" @click="saveGuruName(guru.id)" :disabled="loading">
                        <i class="bi bi-check"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2 justify-content-end mt-4">
              <button type="button" class="btn btn-secondary" @click="closeEditModal">Tutup</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-if="showModal && modalType === 'edit'" class="modal d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Mapel & Guru</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveEditMapelGuru">
              <div class="mb-3">
                <label class="form-label">Data Terpilih</label>
                <div class="border rounded p-3 bg-light-subtle">
                  <div class="small text-muted mb-1">Mata Pelajaran: <strong class="text-dark">{{ editData.mapel_nama_lama || '-' }}</strong></div>
                  <div class="small text-muted mb-1">Guru: <strong class="text-dark">{{ editData.guru_nama_lama || '-' }}</strong></div>
                  <div class="small text-muted mb-0">Kode: <strong class="text-dark">{{ getGuruKodeForMapel(editData.mapel_id_lama, editData.guru_id_lama) }}</strong></div>
                </div>
              </div>

              <div class="mb-3">
                <label for="editMapel" class="form-label">Nama Mata Pelajaran</label>
                <input v-model="editData.mapel_nama_baru" type="text" id="editMapel" class="form-control" required />
              </div>

              <div class="mb-3">
                <label for="editGuru" class="form-label">Nama Guru</label>
                <input v-model="editData.guru_nama_baru" type="text" id="editGuru" class="form-control" required />
              </div>

              <div class="mb-3">
                <label for="editKode" class="form-label">Geser Kode</label>
                <input v-model.number="editData.kode_baru" type="number" id="editKode" class="form-control" min="1" :max="maxGuruCode" required />
                <small class="text-muted d-block mt-2">
                  Jika kode dipindah, kode lain akan otomatis bergeser. Contoh: ubah 3 ke 23, maka kode lama 23 menjadi 22.
                </small>
              </div>

              <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="loading">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Mapel dengan Guru Lama -->
    <div v-if="showModal && modalType === 'mapel'" class="modal d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Mapel & guru</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="d-flex gap-2 mb-3">
              <button
                type="button"
                class="btn btn-sm"
                :class="relationModalTab === 'single' ? 'btn-primary' : 'btn-outline-primary'"
                @click="relationModalTab = 'single'"
              >
                Tambah Single
              </button>
              <button
                type="button"
                class="btn btn-sm"
                :class="relationModalTab === 'batch' ? 'btn-primary' : 'btn-outline-primary'"
                @click="relationModalTab = 'batch'"
              >
                Batch 1 Guru
              </button>
            </div>

            <form v-if="relationModalTab === 'single'" @submit.prevent="saveMapelGuruLama">
              <div class="mb-3">
                <label for="mapel" class="form-label">Pilih Mapel</label>
                <select v-model="formData.mapel_id" id="mapel" class="form-select" required>
                  <option value="">-- Pilih Mapel --</option>
                  <option value="new">+ Buat Mapel Baru</option>
                  <option v-for="mapel in mapelOptions" :key="mapel.id" :value="mapel.id">{{ mapel.nama }}</option>
                </select>
              </div>

              <div v-if="formData.mapel_id === 'new'" class="mb-3">
                <label for="namaMapelBaru2" class="form-label">Nama Mapel Baru</label>
                <input v-model="formData.nama_mapel_baru" type="text" id="namaMapelBaru2" class="form-control" placeholder="Contoh: Matematika" required />
              </div>

              <div class="mb-3">
                <label for="guru" class="form-label">Pilih Guru</label>
                <select v-model="formData.guru_id" id="guru" class="form-select" required>
                  <option value="">-- Pilih Guru --</option>
                  <option value="new">+ Buat Guru Baru</option>
                  <option v-for="guru in guruOptions" :key="guru.id" :value="guru.id">{{ guru.nama }}</option>
                </select>
              </div>

              <div v-if="formData.guru_id === 'new'" class="mb-3">
                <label for="namaGuruBaru3" class="form-label">Nama Guru Baru</label>
                <input v-model="formData.nama_guru_baru" type="text" id="namaGuruBaru3" class="form-control" placeholder="Contoh: Bu Ratna" required />
              </div>

              <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="loading">Simpan</button>
              </div>
            </form>

            <form v-else @submit.prevent="saveBatchMapelGuru">
              <div class="mb-3">
                <label for="batchGuru" class="form-label">Pilih Guru (untuk semua mapel)</label>
                <select v-model="batchFormData.guru_id" id="batchGuru" class="form-select" required>
                  <option value="">-- Pilih Guru --</option>
                  <option value="new">+ Buat Guru Baru</option>
                  <option v-for="guru in guruOptions" :key="`batch-guru-${guru.id}`" :value="guru.id">{{ guru.nama }}</option>
                </select>
              </div>

              <div v-if="batchFormData.guru_id === 'new'" class="mb-3">
                <label for="batchNamaGuruBaru" class="form-label">Nama Guru Baru</label>
                <input v-model="batchFormData.nama_guru_baru" type="text" id="batchNamaGuruBaru" class="form-control" placeholder="Contoh: Pak Adi" required />
              </div>

              <div class="batch-row-wrapper mb-3">
                <div class="small text-muted mb-2">Input banyak mapel untuk satu guru.</div>
                <div v-for="(row, rowIdx) in batchFormData.rows" :key="`batch-row-${rowIdx}`" class="batch-row-item">
                  <div class="row g-2 align-items-end">
                    <div class="col-7">
                      <label class="form-label">Mapel {{ rowIdx + 1 }}</label>
                      <select v-model="row.mapel_id" class="form-select" required>
                        <option value="">-- Pilih Mapel --</option>
                        <option value="new">+ Buat Mapel Baru</option>
                        <option v-for="mapel in mapelOptions" :key="`batch-mapel-${rowIdx}-${mapel.id}`" :value="mapel.id">{{ mapel.nama }}</option>
                      </select>
                    </div>
                    <div class="col-3">
                      <label class="form-label">Preview Kode</label>
                      <div class="form-control bg-light text-center fw-semibold">{{ getBatchPreviewCode(rowIdx) }}</div>
                    </div>
                    <div class="col-2 d-grid">
                      <button
                        type="button"
                        class="btn btn-outline-danger"
                        @click="removeBatchRow(rowIdx)"
                        :disabled="batchFormData.rows.length === 1"
                        title="Hapus baris"
                      >
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>
                  <div v-if="row.mapel_id === 'new'" class="mt-2">
                    <input v-model="row.nama_mapel_baru" type="text" class="form-control" placeholder="Nama mapel baru" required />
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between gap-2">
                <button type="button" class="btn btn-outline-primary" @click="addBatchRow">
                  <i class="bi bi-plus-lg me-1"></i>Tambah Baris
                </button>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                  <button type="submit" class="btn btn-primary" :disabled="loading">Simpan Batch</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Jadwal -->
    <div v-if="showModal && modalType === 'jadwal'" class="modal d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Tambah Jadwal</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <!-- Tabs untuk Pelajaran dan Kegiatan -->
            <ul class="nav nav-tabs mb-4" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-pelajaran" type="button" data-bs-toggle="tab" data-bs-target="#content-pelajaran" aria-selected="true">
                  <i class="bi bi-book me-2"></i>Pelajaran
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-kegiatan" type="button" data-bs-toggle="tab" data-bs-target="#content-kegiatan" aria-selected="false">
                  <i class="bi bi-star me-2"></i>Kegiatan
                </button>
              </li>
            </ul>

            <div class="tab-content">
              <!-- Tab Pelajaran -->
              <div class="tab-pane fade show active" id="content-pelajaran" role="tabpanel">
                <form @submit.prevent="saveJadwalPelajaran">
                  <div class="mb-3">
                    <label for="jadwalKelas" class="form-label">Kelas</label>
                    <select v-model="jadwalFormPelajaran.kelas_id" id="jadwalKelas" class="form-select" required>
                      <option value="">-- Pilih Kelas --</option>
                      <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                        {{ kelas.nama }}
                      </option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="jadwalMapel" class="form-label">Mapel & Guru</label>
                    <select v-model="jadwalFormPelajaran.mapel_guru" id="jadwalMapel" class="form-select" required>
                      <option value="">-- Pilih Mapel & Guru --</option>
                      <option v-for="item in displayedMapel" :key="`${item.mapel.id}-${item.guru.id}`" :value="`${item.mapel.id}-${item.guru.id}`">
                        {{ item.kode }} - {{ item.guru.nama }} ({{ item.mapel.nama }})
                      </option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="jadwalHari" class="form-label">Hari</label>
                    <select v-model="jadwalFormPelajaran.hari" id="jadwalHari" class="form-select" required>
                      <option value="">-- Pilih Hari --</option>
                      <option value="Senin">Senin</option>
                      <option value="Selasa">Selasa</option>
                      <option value="Rabu">Rabu</option>
                      <option value="Kamis">Kamis</option>
                      <option value="Jumat">Jumat</option>
                      <option value="Sabtu">Sabtu</option>
                    </select>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="jadwalMulai" class="form-label">Jam Mulai</label>
                        <input v-model="jadwalFormPelajaran.jam_mulai" type="time" id="jadwalMulai" class="form-control" required />
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="jadwalSelesai" class="form-label">Jam Selesai</label>
                        <input v-model="jadwalFormPelajaran.jam_selesai" type="time" id="jadwalSelesai" class="form-control" required />
                      </div>
                    </div>
                  </div>

                  <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                    <button type="submit" class="btn btn-primary" :disabled="loading">Simpan</button>
                  </div>
                </form>
              </div>

              <!-- Tab Kegiatan -->
              <div class="tab-pane fade" id="content-kegiatan" role="tabpanel">
                <form @submit.prevent="saveJadwalKegiatan">
                  <div class="mb-3">
                    <label for="jadwalKelasKegiatan" class="form-label">Kelas</label>
                    <select v-model="jadwalFormKegiatan.kelas_id" id="jadwalKelasKegiatan" class="form-select" required>
                      <option value="">-- Pilih Kelas --</option>
                      <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas.id">
                        {{ kelas.nama }}
                      </option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="jadwalKegiatanType" class="form-label">Kegiatan</label>
                    <select v-model="jadwalFormKegiatan.kegiatan_id" id="jadwalKegiatanType" class="form-select" required>
                      <option value="">-- Pilih Kegiatan --</option>
                      <option v-for="kegiatan in kegiatanList" :key="kegiatan.id" :value="kegiatan.id">
                        {{ kegiatan.nama }}
                      </option>
                    </select>
                  </div>

                  <div class="mb-3">
                    <label for="jadwalHariKegiatan" class="form-label">Hari</label>
                    <select v-model="jadwalFormKegiatan.hari" id="jadwalHariKegiatan" class="form-select" required>
                      <option value="">-- Pilih Hari --</option>
                      <option value="Senin">Senin</option>
                      <option value="Selasa">Selasa</option>
                      <option value="Rabu">Rabu</option>
                      <option value="Kamis">Kamis</option>
                      <option value="Jumat">Jumat</option>
                      <option value="Sabtu">Sabtu</option>
                    </select>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="jadwalMulaiKegiatan" class="form-label">Jam Mulai</label>
                        <input v-model="jadwalFormKegiatan.jam_mulai" type="time" id="jadwalMulaiKegiatan" class="form-control" required />
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label for="jadwalSelesaiKegiatan" class="form-label">Jam Selesai</label>
                        <input v-model="jadwalFormKegiatan.jam_selesai" type="time" id="jadwalSelesaiKegiatan" class="form-control" required />
                      </div>
                    </div>
                  </div>

                  <div class="d-flex gap-2 justify-content-end">
                    <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                    <button type="submit" class="btn btn-primary" :disabled="loading">Simpan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { mapelService, jadwalService, kelasService, kegiatanService } from '../services/resourceService'

const MAPEL_KODE_ORDER_STORAGE_KEY = 'smart-jadwal-mapel-kode-order-v1'

const mapelList = ref([])
const guruList = ref([])
const showModal = ref(false)
const modalType = ref(null) // 'baru', 'mapel', atau 'edit'
const loading = ref(false)
const mapelSearch = ref('')
const showFullscreen = ref(false)
const fullscreenTableContent = ref(null)

const formData = ref({
  mapel_id: '',
  guru_id: '',
  nama_mapel_baru: '',
  nama_guru_baru: ''
})

const relationModalTab = ref('single')
const batchFormData = ref({
  guru_id: '',
  nama_guru_baru: '',
  rows: [{ mapel_id: '', nama_mapel_baru: '' }]
})

const editData = ref({
  relation_key: '',
  mapel_id_lama: '',
  mapel_nama_lama: '',
  mapel_nama_baru: '',
  guru_id_lama: '',
  guru_nama_lama: '',
  guru_nama_baru: '',
  kode_lama: '',
  kode_baru: ''
})
const guruCodeOrder = ref([])

// Jadwal Modal Data
const kelasList = ref([])
const kegiatanList = ref([])
const jadwalFormPelajaran = ref({
  kelas_id: '',
  mapel_guru: '', // format: "mapel_id-guru_id"
  hari: '',
  jam_mulai: '',
  jam_selesai: ''
})
const jadwalFormKegiatan = ref({
  kelas_id: '',
  kegiatan_id: '',
  hari: '',
  jam_mulai: '',
  jam_selesai: ''
})
const jadwalModalTab = ref('pelajaran') // Track which tab is active

const masterEditData = ref({
  mapel_name: {},
  guru_name: {},
  original_mapel_name: {},
  original_guru_name: {}
})

const isMapelChanged = (mapelId) => {
  return masterEditData.value.mapel_name[mapelId] !== masterEditData.value.original_mapel_name[mapelId]
}

const isGuruChanged = (guruId) => {
  return masterEditData.value.guru_name[guruId] !== masterEditData.value.original_guru_name[guruId]
}

const hasUnsavedChanges = computed(() => {
  // Check mapel changes
  for (let mapelId in masterEditData.value.mapel_name) {
    if (isMapelChanged(parseInt(mapelId))) return true
  }
  // Check guru changes
  for (let guruId in masterEditData.value.guru_name) {
    if (isGuruChanged(parseInt(guruId))) return true
  }
  return false
})

const mapelOptions = computed(() => {
  return mapelList.value.map(m => ({
    id: m.id,
    nama: m.nama
  }))
})

const guruOptions = computed(() => {
  return guruList.value.map(g => ({
    id: g.id,
    nama: g.nama
  }))
})

// Function untuk mendapatkan kode guru spesifik untuk mapel tertentu
const getGuruKodeForMapel = (mapelId, guruId) => {
  const item = displayedMapel.value.find(d => d.mapel.id === mapelId && d.guru.id === guruId)
  return item ? item.kode : '-'
}

const baseDisplayedMapel = computed(() => {
  const result = []

  mapelList.value.forEach(mapel => {
    if (mapel.guru && Array.isArray(mapel.guru) && mapel.guru.length > 0) {
      mapel.guru.forEach(guru => {
        result.push({
          key: `${mapel.id}-${guru.id}`,
          mapel: mapel,
          guru: guru
        })
      })
    }
  })
  return result
})

const maxGuruCode = computed(() => {
  return Math.max(1, new Set(baseDisplayedMapel.value.map(item => item.guru.id)).size)
})

const persistGuruCodeOrder = () => {
  localStorage.setItem(MAPEL_KODE_ORDER_STORAGE_KEY, JSON.stringify(guruCodeOrder.value))
}

const syncGuruCodeOrder = (baseItems) => {
  const currentGuruIds = [...new Set(baseItems.map(item => item.guru.id))].sort((a, b) => a - b)

  // Saat transisi halaman atau awal mount, data bisa kosong sesaat.
  // Jangan overwrite urutan tersimpan dengan array kosong.
  if (currentGuruIds.length === 0) {
    return
  }

  let storedGuruIds = []

  try {
    const raw = localStorage.getItem(MAPEL_KODE_ORDER_STORAGE_KEY)
    if (raw) {
      const parsed = JSON.parse(raw)
      if (Array.isArray(parsed)) {
        storedGuruIds = parsed
          .map(item => Number(item))
          .filter(item => Number.isInteger(item) && item > 0)
      }
    }
  } catch {
    storedGuruIds = []
  }

  const validStoredGuruIds = storedGuruIds.filter(id => currentGuruIds.includes(id))
  const newGuruIds = currentGuruIds.filter(id => !validStoredGuruIds.includes(id))

  guruCodeOrder.value = [...validStoredGuruIds, ...newGuruIds]
  persistGuruCodeOrder()
}

watch(baseDisplayedMapel, (items) => {
  syncGuruCodeOrder(items)
}, { immediate: true })

const displayedMapel = computed(() => {
  const guruMapelCount = {}
  const guruMapelIndex = {}
  const guruBaseCodeMap = {}

  guruCodeOrder.value.forEach((guruId, idx) => {
    guruBaseCodeMap[guruId] = idx + 1
  })

  const result = []

  baseDisplayedMapel.value.forEach((item) => {
    const guruId = item.guru.id

    if (!guruMapelCount[guruId]) {
      guruMapelCount[guruId] = 0
      guruMapelIndex[guruId] = 0
    }

    guruMapelCount[guruId]++
  })

  baseDisplayedMapel.value.forEach((item) => {
    const guruId = item.guru.id
    const baseCode = guruBaseCodeMap[guruId] || guruId
    const mapelCount = guruMapelCount[guruId] || 1
    const currentIndex = guruMapelIndex[guruId] || 0

    let kode
    if (mapelCount === 1) {
      kode = String(baseCode)
    } else {
      const letter = String.fromCharCode(65 + currentIndex)
      kode = `${baseCode}${letter}`
    }

    result.push({
      ...item,
      kode
    })

    guruMapelIndex[guruId] = currentIndex + 1
  })

  result.sort((a, b) => {
    const aMatch = a.kode.match(/^(\d+)([A-Z]?)$/)
    const bMatch = b.kode.match(/^(\d+)([A-Z]?)$/)

    if (aMatch && bMatch) {
      const aNum = parseInt(aMatch[1])
      const bNum = parseInt(bMatch[1])
      const aLetter = aMatch[2] || ''
      const bLetter = bMatch[2] || ''

      if (aNum !== bNum) return aNum - bNum
      if (aLetter === '' && bLetter !== '') return -1
      if (aLetter !== '' && bLetter === '') return 1
      return aLetter.localeCompare(bLetter)
    }

    return 0
  })

  return result
})

const shiftGuruCode = (guruId, targetCode) => {
  const oldIndex = guruCodeOrder.value.indexOf(guruId)
  if (oldIndex === -1) return

  const maxCode = guruCodeOrder.value.length
  const newIndex = Math.min(Math.max(targetCode, 1), maxCode) - 1
  if (oldIndex === newIndex) return

  const updated = [...guruCodeOrder.value]
  const [moved] = updated.splice(oldIndex, 1)
  updated.splice(newIndex, 0, moved)
  guruCodeOrder.value = updated
  persistGuruCodeOrder()
}

const filteredDisplayedMapel = computed(() => {
  const keyword = mapelSearch.value.trim().toLowerCase()
  if (!keyword) return displayedMapel.value

  return displayedMapel.value.filter((item) => {
    return [item.kode, item.mapel.nama, item.guru.nama]
      .join(' ')
      .toLowerCase()
      .includes(keyword)
  })
})

const mapelReportGroups = computed(() => {
  const groups = []
  const byGuru = new Map()

  displayedMapel.value.forEach((item) => {
    const guruId = item.guru.id
    if (!byGuru.has(guruId)) {
      const group = {
        guruId,
        no: groups.length + 1,
        guruNama: item.guru.nama,
        rows: []
      }
      byGuru.set(guruId, group)
      groups.push(group)
    }

    byGuru.get(guruId).rows.push({
      mapelNama: item.mapel.nama,
      kode: item.kode
    })
  })

  return groups
})

onMounted(async () => {
  await loadMapel()
  await loadGuru()
})

const loadMapel = async () => {
  try {
    loading.value = true
    const response = await mapelService.getAll()
    if (response.data.success) {
      mapelList.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading mapel:', error)
    alert('Gagal memuat data mata pelajaran')
  } finally {
    loading.value = false
  }
}

const loadGuru = async () => {
  try {
    const response = await mapelService.getGuru()
    if (response.data.success) {
      guruList.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading guru:', error)
  }
}

const openModalMapel = () => {
  showModal.value = true
  modalType.value = 'mapel'
  relationModalTab.value = 'single'
  formData.value = {
    mapel_id: '',
    guru_id: '',
    nama_mapel_baru: '',
    nama_guru_baru: ''
  }
  batchFormData.value = {
    guru_id: '',
    nama_guru_baru: '',
    rows: [{ mapel_id: '', nama_mapel_baru: '' }]
  }
}

const openModalEdit = () => {
  showModal.value = true
  modalType.value = 'masterEdit'
  // Initialize master data dengan nama current
  masterEditData.value.mapel_name = {}
  masterEditData.value.guru_name = {}
  masterEditData.value.original_mapel_name = {}
  masterEditData.value.original_guru_name = {}
  mapelList.value.forEach(mapel => {
    masterEditData.value.mapel_name[mapel.id] = mapel.nama
    masterEditData.value.original_mapel_name[mapel.id] = mapel.nama
  })
  guruList.value.forEach(guru => {
    masterEditData.value.guru_name[guru.id] = guru.nama
    masterEditData.value.original_guru_name[guru.id] = guru.nama
  })
}

const closeModal = () => {
  showModal.value = false
  modalType.value = null
  relationModalTab.value = 'single'
  formData.value = {
    mapel_id: '',
    guru_id: '',
    nama_mapel_baru: '',
    nama_guru_baru: ''
  }
  batchFormData.value = {
    guru_id: '',
    nama_guru_baru: '',
    rows: [{ mapel_id: '', nama_mapel_baru: '' }]
  }
  editData.value = {
    relation_key: '',
    mapel_id_lama: '',
    mapel_nama_lama: '',
    mapel_nama_baru: '',
    guru_id_lama: '',
    guru_nama_lama: '',
    guru_nama_baru: '',
    kode_lama: '',
    kode_baru: ''
  }
  jadwalFormPelajaran.value = {
    kelas_id: '',
    mapel_guru: '',
    hari: '',
    jam_mulai: '',
    jam_selesai: ''
  }
  jadwalFormKegiatan.value = {
    kelas_id: '',
    kegiatan_id: '',
    hari: '',
    jam_mulai: '',
    jam_selesai: ''
  }
  masterEditData.value = {
    mapel_name: {},
    guru_name: {},
    original_mapel_name: {},
    original_guru_name: {}
  }
}

const addBatchRow = () => {
  batchFormData.value.rows.push({ mapel_id: '', nama_mapel_baru: '' })
}

const removeBatchRow = (idx) => {
  if (batchFormData.value.rows.length === 1) return
  batchFormData.value.rows.splice(idx, 1)
}

const getBaseCodeForGuru = (guruId) => {
  const parsedGuruId = Number(guruId)
  if (parsedGuruId && guruCodeOrder.value.includes(parsedGuruId)) {
    return guruCodeOrder.value.indexOf(parsedGuruId) + 1
  }
  return guruCodeOrder.value.length + 1
}

const getBatchPreviewCode = (rowIdx) => {
  const rows = batchFormData.value.rows
  const guruId = batchFormData.value.guru_id
  if (!rows[rowIdx]?.mapel_id || !guruId) return '-'

  const parsedGuruId = Number(guruId)
  const isExistingGuru = Number.isInteger(parsedGuruId) && parsedGuruId > 0
  const existingCount = isExistingGuru
    ? baseDisplayedMapel.value.filter((item) => item.guru.id === parsedGuruId).length
    : 0
  const totalForGuru = existingCount + rows.length
  const baseCode = getBaseCodeForGuru(guruId)

  if (totalForGuru <= 1) return String(baseCode)

  const letter = String.fromCharCode(65 + existingCount + rowIdx)
  return `${baseCode}${letter}`
}

const resolveBatchGuruId = async () => {
  const selectedGuruId = batchFormData.value.guru_id
  if (!selectedGuruId) {
    alert('Pilih guru untuk batch!')
    return null
  }

  if (selectedGuruId === 'new') {
    const namaGuruBaru = batchFormData.value.nama_guru_baru?.trim()
    if (!namaGuruBaru) {
      alert('Nama guru baru harus diisi!')
      return null
    }

    const response = await mapelService.createGuru({ nama: namaGuruBaru })
    if (!response.data.success) {
      throw new Error(response.data.message || 'Gagal membuat guru baru')
    }

    await loadGuru()
    return response.data.data.id
  }

  return Number(selectedGuruId)
}

const resolveBatchMapelId = async (row) => {
  if (!row.mapel_id) {
    throw new Error('Semua baris mapel harus diisi!')
  }

  if (row.mapel_id === 'new') {
    const namaMapelBaru = row.nama_mapel_baru?.trim()
    if (!namaMapelBaru) {
      throw new Error('Nama mapel baru pada batch harus diisi!')
    }

    const response = await mapelService.create({ nama: namaMapelBaru })
    if (!response.data.success) {
      throw new Error(response.data.message || 'Gagal membuat mapel baru')
    }

    await loadMapel()
    return response.data.data.id
  }

  return Number(row.mapel_id)
}

const saveBatchMapelGuru = async () => {
  const rows = batchFormData.value.rows

  if (!rows.length) {
    alert('Tambah minimal 1 baris mapel!')
    return
  }

  try {
    loading.value = true

    const guruId = await resolveBatchGuruId()
    if (!guruId) return

    const attachedInThisBatch = new Set()
    let successCount = 0
    let skippedCount = 0

    for (const row of rows) {
      const mapelId = await resolveBatchMapelId(row)
      const relationKey = `${mapelId}-${guruId}`

      if (attachedInThisBatch.has(relationKey)) {
        skippedCount++
        continue
      }

      const alreadyExists = baseDisplayedMapel.value.some((item) => item.mapel.id === mapelId && item.guru.id === guruId)
      if (alreadyExists) {
        skippedCount++
        continue
      }

      const response = await mapelService.attachGuru(mapelId, guruId)
      if (response.data.success) {
        attachedInThisBatch.add(relationKey)
        successCount++
      }
    }

    await Promise.all([loadMapel(), loadGuru()])

    if (successCount === 0) {
      alert('Tidak ada data baru yang disimpan. Kemungkinan relasi sudah ada semua.')
      return
    }

    const skippedInfo = skippedCount > 0 ? ` (${skippedCount} baris dilewati karena duplikat)` : ''
    alert(`Berhasil menambahkan ${successCount} relasi mapel-guru${skippedInfo}`)
    closeModal()
  } catch (error) {
    console.error('Error saving batch mapel-guru:', error)
    alert('Gagal menyimpan batch: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const closeEditModal = () => {
  if (modalType.value === 'masterEdit' && hasUnsavedChanges.value) {
    const confirm = window.confirm('Anda belum ngesave! Apakah Anda yakin ingin menutup?')
    if (!confirm) return
  }
  closeModal()
}

const saveMapelGuruLama = async () => {
  let mapelId = formData.value.mapel_id
  let guruId = formData.value.guru_id

  // Handle new mapel
  if (mapelId === 'new') {
    if (!formData.value.nama_mapel_baru.trim()) {
      alert('Nama mapel baru harus diisi!')
      return
    }
    try {
      const response = await mapelService.create({ nama: formData.value.nama_mapel_baru })
      if (response.data.success) {
        mapelId = response.data.data.id
        await loadMapel()
      } else {
        alert('Gagal membuat mapel')
        return
      }
    } catch (error) {
      alert('Gagal membuat mapel: ' + error.message)
      return
    }
  }

  // Handle new guru
  if (guruId === 'new') {
    if (!formData.value.nama_guru_baru.trim()) {
      alert('Nama guru baru harus diisi!')
      return
    }
    try {
      const response = await mapelService.createGuru({ nama: formData.value.nama_guru_baru })
      if (response.data.success) {
        guruId = response.data.data.id
        await loadGuru()
      } else {
        alert('Gagal membuat guru')
        return
      }
    } catch (error) {
      alert('Gagal membuat guru: ' + error.message)
      return
    }
  }

  if (!mapelId || !guruId) {
    alert('Pilih mapel dan guru!')
    return
  }

  try {
    loading.value = true
    const response = await mapelService.attachGuru(mapelId, guruId)
    if (response.data.success) {
      alert('Mapel berhasil ditambahkan ke guru')
      await loadMapel()
      closeModal()
    }
  } catch (error) {
    console.error('Error saving:', error)
    alert('Gagal menyimpan: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const deleteGuruMapel = async (mapelId, guruId) => {
  if (!confirm('Hapus guru dari mata pelajaran ini?')) return

  try {
    loading.value = true
    const response = await mapelService.detachGuru(mapelId, guruId)
    if (response.data.success) {
      alert('Guru berhasil dihapus')
      await loadMapel()
    }
  } catch (error) {
    console.error('Error deleting:', error)
    alert('Gagal menghapus: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const openEditModal = (mapelId, guruId) => {
  const mapel = mapelList.value.find(m => m.id === mapelId)
  const guru = guruList.value.find(g => g.id === guruId)
  const relationKey = `${mapelId}-${guruId}`
  const kodeSaatIni = getGuruKodeForMapel(mapelId, guruId)
  showModal.value = true
  modalType.value = 'edit'
  editData.value = {
    relation_key: relationKey,
    mapel_id_lama: mapelId,
    mapel_nama_lama: mapel?.nama || '',
    mapel_nama_baru: mapel?.nama || '',
    guru_id_lama: guruId,
    guru_nama_lama: guru?.nama || '',
    guru_nama_baru: guru?.nama || '',
    kode_lama: kodeSaatIni,
    kode_baru: kodeSaatIni
  }
}

const saveMapelName = async (mapelId) => {
  const newName = masterEditData.value.mapel_name[mapelId]?.trim()
  if (!newName) {
    alert('Nama mapel tidak boleh kosong!')
    return
  }

  const currentMapel = mapelList.value.find(m => m.id === mapelId)
  if (currentMapel.nama === newName) {
    alert('Nama sama, tidak ada perubahan')
    return
  }

  try {
    loading.value = true
    const response = await mapelService.update(mapelId, { nama: newName })
    if (response.data.success) {
      alert('Nama mapel berhasil diubah')
      masterEditData.value.original_mapel_name[mapelId] = newName
      await loadMapel()
    }
  } catch (error) {
    console.error('Error updating mapel:', error)
    alert('Gagal mengubah nama mapel: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const saveGuruName = async (guruId) => {
  const newName = masterEditData.value.guru_name[guruId]?.trim()
  if (!newName) {
    alert('Nama guru tidak boleh kosong!')
    return
  }

  const currentGuru = guruList.value.find(g => g.id === guruId)
  if (currentGuru.nama === newName) {
    alert('Nama sama, tidak ada perubahan')
    return
  }

  try {
    loading.value = true
    const response = await mapelService.updateGuru(guruId, { nama: newName })
    if (response.data.success) {
      alert('Nama guru berhasil diubah')
      masterEditData.value.original_guru_name[guruId] = newName
      await loadGuru()
    }
  } catch (error) {
    console.error('Error updating guru:', error)
    alert('Gagal mengubah nama guru: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const saveEditMapelGuru = async () => {
  const mapelNamaBaru = editData.value.mapel_nama_baru?.trim()
  const guruNamaBaru = editData.value.guru_nama_baru?.trim()
  const kodeBaru = parseInt(editData.value.kode_baru)

  if (!mapelNamaBaru) {
    alert('Nama mata pelajaran tidak boleh kosong!')
    return
  }

  if (!guruNamaBaru) {
    alert('Nama guru tidak boleh kosong!')
    return
  }

  if (isNaN(kodeBaru) || kodeBaru < 1 || kodeBaru > maxGuruCode.value) {
    alert(`Kode baru harus di antara 1 sampai ${maxGuruCode.value}`)
    return
  }

  const mapelTidakBerubah = mapelNamaBaru === editData.value.mapel_nama_lama
  const guruTidakBerubah = guruNamaBaru === editData.value.guru_nama_lama
  const kodeTidakBerubah = parseInt(editData.value.kode_lama) === kodeBaru

  if (mapelTidakBerubah && guruTidakBerubah && kodeTidakBerubah) {
    alert('Tidak ada perubahan data')
    return
  }

  try {
    loading.value = true

    if (!mapelTidakBerubah) {
      await mapelService.update(editData.value.mapel_id_lama, { nama: mapelNamaBaru })
    }

    if (!guruTidakBerubah) {
      await mapelService.updateGuru(editData.value.guru_id_lama, { nama: guruNamaBaru })
    }

    if (!kodeTidakBerubah) {
      shiftGuruCode(editData.value.guru_id_lama, kodeBaru)
    }

    alert('Data mapel dan guru berhasil diperbarui')
    await Promise.all([loadMapel(), loadGuru()])
    closeModal()
  } catch (error) {
    console.error('Error updating:', error)
    alert('Gagal memperbarui data: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

// Jadwal Modal Functions
const loadKelas = async () => {
  try {
    const response = await kelasService.getAll()
    if (response.data.success) {
      kelasList.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading kelas:', error)
  }
}

const loadKegiatan = async () => {
  try {
    const response = await kegiatanService.getAll()
    if (response.data.success) {
      kegiatanList.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading kegiatan:', error)
  }
}

const openModalJadwal = () => {
  showModal.value = true
  modalType.value = 'jadwal'
  jadwalModalTab.value = 'pelajaran'
  loadKelas()
  loadKegiatan()
}

const saveJadwalPelajaran = async () => {
  const form = jadwalFormPelajaran.value
  
  // Validate input
  if (!form.kelas_id) {
    alert('Pilih kelas!')
    return
  }
  if (!form.mapel_guru) {
    alert('Pilih mapel & guru!')
    return
  }
  if (!form.hari) {
    alert('Pilih hari!')
    return
  }
  if (!form.jam_mulai) {
    alert('Isi jam mulai!')
    return
  }
  if (!form.jam_selesai) {
    alert('Isi jam selesai!')
    return
  }

  try {
    loading.value = true
    
    // Parse mapel_guru format "mapel_id-guru_id"
    const [mapel_id, guru_id] = form.mapel_guru.split('-')
    
    const payload = {
      kelas_id: parseInt(form.kelas_id),
      mapel_id: parseInt(mapel_id),
      guru_id: parseInt(guru_id),
      hari: form.hari,
      jam_mulai: form.jam_mulai,
      jam_selesai: form.jam_selesai
    }
    
    const response = await jadwalService.create(payload)
    if (response.data.success) {
      alert('Jadwal pelajaran berhasil ditambahkan')
      closeModal()
      // Optionally reload mapel to show updated jadwal
      await loadMapel()
    } else {
      alert('Gagal menambahkan jadwal: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error) {
    console.error('Error saving jadwal:', error)
    alert('Gagal menambahkan jadwal: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const saveJadwalKegiatan = async () => {
  const form = jadwalFormKegiatan.value
  
  // Validate input
  if (!form.kelas_id) {
    alert('Pilih kelas!')
    return
  }
  if (!form.kegiatan_id) {
    alert('Pilih kegiatan!')
    return
  }
  if (!form.hari) {
    alert('Pilih hari!')
    return
  }
  if (!form.jam_mulai) {
    alert('Isi jam mulai!')
    return
  }
  if (!form.jam_selesai) {
    alert('Isi jam selesai!')
    return
  }

  try {
    loading.value = true
    
    const payload = {
      kelas_id: parseInt(form.kelas_id),
      kegiatan_id: parseInt(form.kegiatan_id),
      hari: form.hari,
      jam_mulai: form.jam_mulai,
      jam_selesai: form.jam_selesai
    }
    
    const response = await jadwalService.create(payload)
    if (response.data.success) {
      alert('Jadwal kegiatan berhasil ditambahkan')
      closeModal()
      // Optionally reload mapel to show updated jadwal
      await loadMapel()
    } else {
      alert('Gagal menambahkan jadwal: ' + (response.data.message || 'Unknown error'))
    }
  } catch (error) {
    console.error('Error saving jadwal kegiatan:', error)
    alert('Gagal menambahkan jadwal: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const openFullscreen = () => {
  showFullscreen.value = true
}

const closeFullscreen = () => {
  showFullscreen.value = false
}

const downloadPDF = async () => {
  const element = fullscreenTableContent.value
  if (!element) {
    alert('Konten laporan tidak ditemukan')
    return
  }

  const margin = 10
  let captureHost = null

  try {
    loading.value = true

    try {
      const { jsPDF } = await import('jspdf')
      const html2canvas = (await import('html2canvas')).default

      // Clone konten ke area tersembunyi agar capture PDF tidak terpotong oleh area scroll.
      captureHost = document.createElement('div')
      captureHost.style.position = 'fixed'
      captureHost.style.left = '-100000px'
      captureHost.style.top = '0'
      captureHost.style.background = '#ffffff'
      captureHost.style.padding = '0'
      captureHost.style.overflow = 'visible'

      const clonedElement = element.cloneNode(true)
      clonedElement.style.maxHeight = 'none'
      clonedElement.style.height = 'auto'
      clonedElement.style.overflow = 'visible'

      captureHost.appendChild(clonedElement)
      document.body.appendChild(captureHost)

      const canvas = await html2canvas(clonedElement, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff',
        windowWidth: Math.max(clonedElement.scrollWidth, clonedElement.clientWidth),
        windowHeight: Math.max(clonedElement.scrollHeight, clonedElement.clientHeight),
        scrollX: 0,
        scrollY: 0
      })

      if (captureHost && captureHost.parentNode) {
        captureHost.parentNode.removeChild(captureHost)
        captureHost = null
      }

      const imgData = canvas.toDataURL('image/png')
      const pdf = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' })

      const pdfWidth = pdf.internal.pageSize.getWidth() - (margin * 2)
      const pdfHeight = pdf.internal.pageSize.getHeight() - (margin * 2)
      const imgProps = pdf.getImageProperties(imgData)
      const scale = Math.min(pdfWidth / imgProps.width, pdfHeight / imgProps.height)
      const renderWidth = imgProps.width * scale
      const renderHeight = imgProps.height * scale
      const offsetX = margin + ((pdfWidth - renderWidth) / 2)
      const offsetY = margin + ((pdfHeight - renderHeight) / 2)

      // Paksa 1 halaman portrait dan scale otomatis agar seluruh tabel muat.
      pdf.addImage(imgData, 'PNG', offsetX, offsetY, renderWidth, renderHeight)

      pdf.save('data-mapel-dan-kode-guru-a4.pdf')
      return
    } catch (libError) {
      console.warn('jsPDF/html2canvas tidak tersedia, fallback print:', libError.message)
    }

    const printWindow = window.open('', '_blank')
    if (!printWindow) throw new Error('Popup diblokir browser')

    printWindow.document.write(`
      <html>
        <head>
          <title>Data Mata Pelajaran dan Kode Guru</title>
          <style>
            @page { size: A4 portrait; margin: 10mm; }
            body { font-family: 'Times New Roman', Times, serif; margin: 0; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #333; padding: 8px; }
            th { background: #f3f3f3; text-align: center; }
          </style>
        </head>
        <body>
          ${element.innerHTML}
        </body>
      </html>
    `)
    printWindow.document.close()
    printWindow.focus()
    printWindow.print()
  } catch (error) {
    console.error('Gagal download PDF:', error)
    alert('Gagal download PDF: ' + (error.message || 'Unknown error'))
  } finally {
    if (captureHost && captureHost.parentNode) {
      captureHost.parentNode.removeChild(captureHost)
    }
    loading.value = false
  }
}

</script>

<style scoped>

.btn-link {
  text-decoration: none;
}

.btn-link:hover {
  text-decoration: none;
}

.modal.d-block {
  display: block !important;
}

.modal-dialog {
  margin-top: 100px;
}

.fullscreen-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1060;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.fullscreen-modal-content {
  width: min(1300px, 100%);
  height: min(90vh, 900px);
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.fullscreen-header {
  padding: 12px 16px;
  border-bottom: 1px solid #e9ecef;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.fullscreen-body {
  padding: 16px;
  overflow: auto;
  background: #fff;
}

.report-paper {
  max-width: 1100px;
  margin: 0 auto;
  background: #fff;
  font-family: 'Times New Roman', Times, serif;
  color: #111;
}

.report-table {
  border: 2px solid #1f1f1f;
  font-size: 15px;
  border-collapse: separate;
  border-spacing: 0;
  table-layout: fixed;
  width: 100%;
}

.report-table .col-no {
  width: 56px;
}

.report-table .col-guru {
  width: 210px;
}

.report-table .col-mapel {
  width: auto;
}

.report-table .col-kode {
  width: 64px;
}

.report-table th,
.report-table td {
  border-right: 1px solid #2f2f2f;
  border-bottom: 1px solid #2f2f2f;
  vertical-align: middle;
  padding: 6px 8px;
  background: #fff;
}

.report-table .no-col,
.report-table .kode-col,
.report-table .kode-cell {
  text-align: center;
  vertical-align: middle;
  padding-left: 4px;
  padding-right: 4px;
}

.report-table tr > *:first-child {
  border-left: 1px solid #2f2f2f;
}

.report-table thead tr:first-child > * {
  border-top: 1px solid #2f2f2f;
}

.report-title {
  background: #efe6bf;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.report-table thead tr:nth-child(2) th {
  text-align: center;
  background: #f9f9f9;
}

.merge-cell {
  position: relative;
  z-index: 1;
  background: #fff;
}

.merge-cell span {
  display: block;
  width: 100%;
}

.merge-center {
  text-align: center;
  vertical-align: middle;
}

.merge-middle {
  text-align: center;
  vertical-align: middle;
}

@media (max-width: 991.98px) {
  .fullscreen-modal-content {
    width: 100%;
    height: 94vh;
    border-radius: 8px;
  }

  .modal-dialog.modal-lg {
    max-width: calc(100vw - 24px);
  }
}

@media (max-width: 767.98px) {
  .container-fluid.py-4 {
    padding-top: 0.75rem !important;
  }

  .mb-4.d-flex.justify-content-end.gap-2 {
    justify-content: stretch !important;
    flex-direction: column;
  }

  .mb-4.d-flex.justify-content-end.gap-2 .btn {
    width: 100%;
  }

  .modal-dialog {
    margin: 0.75rem;
  }

  .fullscreen-header {
    flex-direction: column;
    align-items: stretch;
    gap: 0.5rem;
  }

  .fullscreen-body {
    padding: 10px;
  }
}
</style>
