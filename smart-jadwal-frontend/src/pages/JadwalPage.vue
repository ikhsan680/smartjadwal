<template>
  <div class="container-fluid py-2">
    <!-- Header -->
    <div class="mb-3">
      <h1 class="h3 text-dark fw-bold mb-1">Kelola Jadwal</h1>
      <p class="text-muted small">Kelola jadwal pelajaran dengan waktu fleksibel</p>
    </div>

    <!-- Control Section -->
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body py-3">
        <div class="d-flex align-items-center justify-content-between gap-2">
          <div class="d-flex align-items-center gap-3">
            <label class="text-sm text-dark fw-semibold mb-0">Pilih Kelas:</label>
            <select v-model="kelasAktif" class="form-select form-select-sm" style="max-width: 200px;">
              <option v-for="k in kelasOptions" :key="k.id" :value="k.id">
                {{ k.nama }}
              </option>
            </select>
          </div>
          <button class="btn btn-primary btn-sm" @click="openModal" :disabled="loading">
            <i class="bi bi-plus-lg"></i> Tambah Jadwal
          </button>
        </div>
      </div>
    </div>

    <!-- Weekly Schedule Cards -->
    <div class="row g-3 schedule-grid">
      <div v-for="hariName in hari" :key="hariName" class="col-12 col-md-6 col-lg-2-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-info-subtle py-3">
            <h5 class="text-dark fw-semibold mb-0">{{ hariName }}</h5>
          </div>
          <div class="card-body p-2">
            <div v-if="getJadwalByDay(hariName).length > 0" class="d-flex flex-column gap-2">
              <div 
                v-for="jadwal in getJadwalByDay(hariName)" 
                :key="jadwal.id"
                :class="['card-jadwal', 'rounded', 'p-3', 'border-start', 'border-3', jadwal.kegiatan_id ? 'bg-warning-subtle border-warning' : 'bg-primary-subtle border-primary']"
              >
                <div class="d-flex align-items-start justify-content-between mb-2">
                  <div class="flex-grow-1">
                    <!-- Pelajaran Format -->
                    <template v-if="!jadwal.kegiatan_id">
                      <div class="text-sm text-dark fw-semibold">{{ getMapelName(jadwal.mapel_id) }}</div>
                      <div class="text-sm text-muted">{{ getMapelGuru(jadwal.mapel_id, jadwal.guru_id) }}</div>
                    </template>
                    <!-- Kegiatan Format -->
                    <template v-else>
                      <div class="text-sm text-dark fw-semibold">{{ getKegiatanName(jadwal.kegiatan_id) }}</div>
                      <div class="text-sm text-muted">-</div>
                    </template>
                    <div :class="['text-xs', 'fw-bold', 'mt-2', jadwal.kegiatan_id ? 'text-warning' : 'text-primary']">
                      <i class="bi bi-clock"></i> {{ jadwal.jam_mulai }} - {{ jadwal.jam_selesai }}
                    </div>
                  </div>
                </div>

                <!-- Edit and Delete Buttons -->
                <div class="d-flex gap-1 mt-3">
                  <button 
                    :class="['btn', 'btn-sm', 'flex-1', jadwal.kegiatan_id ? 'btn-outline-warning' : 'btn-outline-primary']"
                    @click="openEditModal(jadwal)"
                    :disabled="loading"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button 
                    class="btn btn-sm btn-outline-danger flex-1"
                    @click="deleteJadwal(jadwal.id)"
                    :disabled="loading"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-muted py-4">
              <i class="bi bi-inbox" style="font-size: 2rem;"></i>
              <p class="small mt-2">Tidak ada jadwal</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tambah/Edit Jadwal -->
    <div v-if="showModal" class="modal d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
      <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width:900px;">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ formData.isEdit ? 'Edit Jadwal' : 'Tambah Jadwal' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="addJadwal">
              <div v-if="!formData.isEdit" class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                  <label class="form-label mb-0">Batch Jadwal Gabungan</label>
                  <button type="button" class="btn btn-sm btn-outline-primary" @click="addBatchRow">
                    <i class="bi bi-plus-lg"></i> Tambah Baris
                  </button>
                </div>

                <div class="alert alert-info alert-sm mb-3">
                  <small>
                    Kelas aktif: <strong>{{ kelasOptions.find(k => k.id === kelasAktif)?.nama || '-' }}</strong>. 
                    Anda bisa campur <strong>Pelajaran</strong> dan <strong>Kegiatan</strong> dalam satu batch.
                  </small>
                </div>

                <div class="table-responsive">
                  <table class="table table-sm align-middle mb-0">
                    <thead>
                      <tr>
                        <th style="min-width: 120px;">Tipe</th>
                        <th style="min-width: 260px;">Mapel/Guru atau Kegiatan</th>
                        <th style="min-width: 120px;">Hari</th>
                        <th style="min-width: 120px;">Mulai</th>
                        <th style="min-width: 120px;">Selesai</th>
                        <th style="width: 90px;">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(row, idx) in batchRows" :key="`batch-${idx}`">
                        <td>
                          <select v-model="row.jadwalType" class="form-select form-select-sm">
                            <option value="pelajaran">Pelajaran</option>
                            <option value="kegiatan">Kegiatan</option>
                          </select>
                        </td>
                        <td>
                          <select v-if="row.jadwalType === 'pelajaran'" v-model="row.mapel_guru" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Mapel & Guru --</option>
                            <option v-for="m in mapelWithKode" :key="`${m.id}-${m.guru_id}`" :value="`${m.id}-${m.guru_id}`">
                              {{ m.displayText }}
                            </option>
                          </select>
                          <select v-else v-model="row.kegiatan_id" class="form-select form-select-sm" required>
                            <option value="">-- Pilih Kegiatan --</option>
                            <option v-for="k in kegiatanList" :key="k.id" :value="k.id">
                              {{ k.nama }}
                            </option>
                          </select>
                        </td>
                        <td>
                          <select v-model="row.hari" class="form-select form-select-sm" required>
                            <option value="">-- Hari --</option>
                            <option v-for="h in hari" :key="h" :value="h">{{ h }}</option>
                          </select>
                        </td>
                        <td>
                          <input v-model="row.jam_mulai" type="time" class="form-control form-control-sm" required />
                        </td>
                        <td>
                          <input v-model="row.jam_selesai" type="time" class="form-control form-control-sm" required />
                        </td>
                        <td>
                          <button
                            type="button"
                            class="btn btn-sm btn-outline-danger"
                            @click="removeBatchRow(idx)"
                            :disabled="batchRows.length === 1"
                          >
                            Hapus
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <template v-else>
                <ul class="nav nav-tabs mb-4" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button 
                      :class="['nav-link', formData.jadwalType === 'pelajaran' ? 'active' : '']"
                      type="button"
                      @click="formData.jadwalType = 'pelajaran'"
                    >
                      <i class="bi bi-book me-2"></i>Pelajaran
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button 
                      :class="['nav-link', formData.jadwalType === 'kegiatan' ? 'active' : '']"
                      type="button"
                      @click="formData.jadwalType = 'kegiatan'"
                    >
                      <i class="bi bi-star me-2"></i>Kegiatan
                    </button>
                  </li>
                </ul>

                <div v-if="formData.jadwalType === 'pelajaran'">
                <div class="mb-3">
                  <label for="pilihHari" class="form-label">Hari</label>
                  <select v-model="formData.hari" id="pilihHari" class="form-select" required>
                    <option value="">-- Pilih Hari --</option>
                    <option v-for="h in hari" :key="h" :value="h">{{ h }}</option>
                  </select>
                </div>

                <div class="row">
                  <div class="col-6 mb-3">
                    <label for="jamMulai" class="form-label">Jam Mulai</label>
                    <input 
                      v-model="formData.jam_mulai" 
                      type="time" 
                      id="jamMulai" 
                      class="form-control" 
                      required 
                    />
                  </div>
                  <div class="col-6 mb-3">
                    <label for="jamSelesai" class="form-label">Jam Selesai</label>
                    <input 
                      v-model="formData.jam_selesai" 
                      type="time" 
                      id="jamSelesai" 
                      class="form-control" 
                      required 
                    />
                  </div>
                </div>

                <div class="mb-3">
                  <label for="pilihMapel" class="form-label">Mata Pelajaran</label>
                  <select v-model="formData.mapel_guru" id="pilihMapel" class="form-select" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <option v-for="m in mapelWithKode" :key="`${m.id}-${m.guru_id}`" :value="`${m.id}-${m.guru_id}`">
                      {{ m.displayText }}
                    </option>
                  </select>
                </div>
                </div>

                <div v-else>
                <div class="mb-3">
                  <label for="pilihHariKegiatan" class="form-label">Hari</label>
                  <select v-model="formData.hari" id="pilihHariKegiatan" class="form-select" required>
                    <option value="">-- Pilih Hari --</option>
                    <option v-for="h in hari" :key="h" :value="h">{{ h }}</option>
                  </select>
                </div>

                <div class="row">
                  <div class="col-6 mb-3">
                    <label for="jamMulaiKegiatan" class="form-label">Jam Mulai</label>
                    <input 
                      v-model="formData.jam_mulai" 
                      type="time" 
                      id="jamMulaiKegiatan" 
                      class="form-control" 
                      required 
                    />
                  </div>
                  <div class="col-6 mb-3">
                    <label for="jamSelesaiKegiatan" class="form-label">Jam Selesai</label>
                    <input 
                      v-model="formData.jam_selesai" 
                      type="time" 
                      id="jamSelesaiKegiatan" 
                      class="form-control" 
                      required 
                    />
                  </div>
                </div>

                <div class="mb-3">
                  <label for="pilihKegiatan" class="form-label">Kegiatan</label>
                  <select v-model="formData.kegiatan_id" id="pilihKegiatan" class="form-select" required>
                    <option value="">-- Pilih Kegiatan --</option>
                    <option v-for="k in kegiatanList" :key="k.id" :value="k.id">
                      {{ k.nama }}
                    </option>
                  </select>
                </div>
                </div>
              </template>

              <div v-if="formData.isEdit && formData.jam_mulai && formData.jam_selesai" class="alert alert-info alert-sm mb-3">
                <small>
                  <strong>Preview:</strong> {{ formData.hari }} | {{ formData.jam_mulai }} - {{ formData.jam_selesai }}
                </small>
              </div>

              <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="loading">
                  {{ formData.isEdit ? 'Update' : `Simpan Batch (${batchRows.length})` }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { jadwalService, kelasService, mapelService, kegiatanService } from '../services/resourceService'

const MAPEL_KODE_ORDER_STORAGE_KEY = 'smart-jadwal-mapel-kode-order-v1'

const kelasAktif = ref(null)
const kelasList = ref([])
const kelasOptions = ref([])
const jadwalList = ref([])
const mapelList = ref([])
const kegiatanList = ref([])
const showModal = ref(false)
const loading = ref(false)
const formData = ref({
  hari: '',
  jam_mulai: '',
  jam_selesai: '',
  mapel_guru: '',
  mapel_id: '',
  guru_id: '',
  kegiatan_id: '',
  kelas_id: '',
  jadwalType: 'pelajaran', // 'pelajaran' or 'kegiatan'
  isEdit: false,
  id: null
})

const makeBatchRow = () => ({
  jadwalType: 'pelajaran',
  hari: '',
  jam_mulai: '',
  jam_selesai: '',
  mapel_guru: '',
  kegiatan_id: ''
})

const batchRows = ref([makeBatchRow()])

const hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat']

// Computed property untuk mapel dengan kode guru
const mapelWithKode = computed(() => {
  const relationList = []
  const guruMapelCount = {}
  const guruMapelIndex = {}
  const guruBaseCodeMap = {}

  mapelList.value.forEach(mapel => {
    if (mapel.guru && Array.isArray(mapel.guru) && mapel.guru.length > 0) {
      mapel.guru.forEach(guru => {
        if (!guruMapelCount[guru.id]) {
          guruMapelCount[guru.id] = 0
          guruMapelIndex[guru.id] = 0
        }
        guruMapelCount[guru.id]++

        relationList.push({
          key: `${mapel.id}-${guru.id}`,
          id: mapel.id,
          nama: mapel.nama,
          guru: guru.nama,
          guru_id: guru.id
        })
      })
    }
  })

  const currentGuruIds = [...new Set(relationList.map((item) => Number(item.guru_id)))].sort((a, b) => a - b)

  let storedGuruOrder = []
  try {
    const raw = localStorage.getItem(MAPEL_KODE_ORDER_STORAGE_KEY)
    const parsed = raw ? JSON.parse(raw) : []
    if (Array.isArray(parsed)) {
      storedGuruOrder = parsed
        .map((item) => Number(item))
        .filter((item) => Number.isInteger(item) && item > 0)
    }
  } catch {
    storedGuruOrder = []
  }

  const validStoredGuruIds = storedGuruOrder.filter((id) => currentGuruIds.includes(id))
  const newGuruIds = currentGuruIds.filter((id) => !validStoredGuruIds.includes(id))
  const finalGuruOrder = [...validStoredGuruIds, ...newGuruIds]

  finalGuruOrder.forEach((guruId, idx) => {
    guruBaseCodeMap[guruId] = idx + 1
  })

  return relationList
    .map((item) => {
      const baseCode = guruBaseCodeMap[Number(item.guru_id)] || Number(item.guru_id)
      const mapelCount = guruMapelCount[item.guru_id] || 1
      const currentIndex = guruMapelIndex[item.guru_id] || 0

      let kode
      if (mapelCount === 1) {
        kode = String(baseCode)
      } else {
        const letter = String.fromCharCode(65 + currentIndex)
        kode = `${baseCode}${letter}`
      }

      guruMapelIndex[item.guru_id] = currentIndex + 1

      return {
        ...item,
        kode,
        displayText: `${kode}, ${item.nama}, ${item.guru}`
      }
    })
    .sort((a, b) => {
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
})

// Load kelas list on mount
onMounted(async () => {
  try {
    loading.value = true
    const response = await kelasService.getAll()
    if (response.data.success) {
      kelasList.value = response.data.data
      kelasOptions.value = kelasList.value.map(k => ({ id: k.id, nama: k.nama }))
      if (kelasOptions.value.length > 0) {
        kelasAktif.value = kelasOptions.value[0].id
      }
    }

    const mapelResponse = await mapelService.getAll()
    if (mapelResponse.data.success) {
      mapelList.value = mapelResponse.data.data
    }

    const kegiatanResponse = await kegiatanService.getAll()
    if (kegiatanResponse.data.success) {
      kegiatanList.value = kegiatanResponse.data.data
      console.log('Kegiatan loaded:', kegiatanList.value)
    }
  } catch (error) {
    console.error('Error loading data:', error)
    alert('Gagal memuat data. Silakan coba refresh halaman')
  } finally {
    loading.value = false
  }
})

// Watch mapel selection to auto-set guru
watch(() => formData.value.mapel_guru, (newMapelGuru) => {
  if (!newMapelGuru) {
    formData.value.mapel_id = ''
    formData.value.guru_id = ''
    return
  }

  const [mapelId, guruId] = newMapelGuru.split('-')
  formData.value.mapel_id = mapelId
  formData.value.guru_id = guruId
})

// Watch jadwalType to clear irrelevant fields when switching tabs
watch(() => formData.value.jadwalType, (newType) => {
  if (newType === 'pelajaran') {
    // Clear kegiatan field when switching to pelajaran
    formData.value.kegiatan_id = ''
  } else {
    // Clear mapel and guru fields when switching to kegiatan
    formData.value.mapel_guru = ''
    formData.value.mapel_id = ''
    formData.value.guru_id = ''
  }
})

// Watch kelasAktif to load jadwal
watch(kelasAktif, async (newKelasId) => {
  if (newKelasId) {
    await loadJadwal(newKelasId)
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

// Get all schedules for a specific day, sorted by time
const getJadwalByDay = (hariName) => {
  return jadwalList.value
    .filter(j => j.hari === hariName)
    .sort((a, b) => a.jam_mulai.localeCompare(b.jam_mulai))
}

const getMapelName = (mapelId) => {
  const mapel = mapelList.value.find(m => m.id === mapelId)
  return mapel ? mapel.nama : ''
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

const getKegiatanName = (kegiatanId) => {
  const kegiatan = kegiatanList.value.find(k => k.id === kegiatanId)
  return kegiatan ? kegiatan.nama : ''
}

const openModal = () => {
  // Ensure kegiatan list is loaded
  if (kegiatanList.value.length === 0) {
    kegiatanService.getAll().then(response => {
      if (response.data.success) {
        kegiatanList.value = response.data.data
        console.log('Kegiatan loaded:', kegiatanList.value)
      }
    }).catch(error => {
      console.error('Error loading kegiatan:', error)
    })
  }
  
  showModal.value = true
  batchRows.value = [makeBatchRow()]
  formData.value = {
    hari: '',
    jam_mulai: '',
    jam_selesai: '',
    mapel_guru: '',
    mapel_id: '',
    guru_id: '',
    kegiatan_id: '',
    kelas_id: kelasAktif.value,
    jadwalType: 'pelajaran',
    isEdit: false,
    id: null
  }
}

const closeModal = () => {
  showModal.value = false
  batchRows.value = [makeBatchRow()]
  formData.value = {
    hari: '',
    jam_mulai: '',
    jam_selesai: '',
    mapel_guru: '',
    mapel_id: '',
    guru_id: '',
    kegiatan_id: '',
    kelas_id: '',
    jadwalType: 'pelajaran',
    isEdit: false,
    id: null
  }
}

const addBatchRow = () => {
  batchRows.value.push(makeBatchRow())
}

const removeBatchRow = (index) => {
  if (batchRows.value.length === 1) return
  batchRows.value.splice(index, 1)
}

const isOverlapping = (startA, endA, startB, endB) => {
  return startA < endB && endA > startB
}

const validateBatchRows = (rows) => {
  for (let i = 0; i < rows.length; i++) {
    const row = rows[i]
    const rowNo = i + 1
    const typeLabel = row.jadwalType === 'kegiatan' ? 'Kegiatan' : 'Pelajaran'

    if (!row.hari || !row.jam_mulai || !row.jam_selesai) {
      return `${typeLabel} baris ${rowNo}: hari dan jam wajib diisi.`
    }

    if (row.jam_mulai >= row.jam_selesai) {
      return `${typeLabel} baris ${rowNo}: jam mulai harus lebih awal dari jam selesai.`
    }

    if (row.jadwalType === 'pelajaran' && !row.mapel_guru) {
      return `${typeLabel} baris ${rowNo}: mapel dan guru wajib dipilih.`
    }

    if (row.jadwalType === 'kegiatan' && !row.kegiatan_id) {
      return `${typeLabel} baris ${rowNo}: kegiatan wajib dipilih.`
    }
  }

  for (let i = 0; i < rows.length; i++) {
    for (let j = i + 1; j < rows.length; j++) {
      if (rows[i].hari !== rows[j].hari) continue

      if (isOverlapping(rows[i].jam_mulai, rows[i].jam_selesai, rows[j].jam_mulai, rows[j].jam_selesai)) {
        return `Baris ${i + 1} bentrok dengan baris ${j + 1} pada hari ${rows[i].hari}.`
      }
    }
  }

  return ''
}

const createBatchMixed = async () => {
  const validationMessage = validateBatchRows(batchRows.value)
  if (validationMessage) {
    alert(validationMessage)
    return
  }

  const kelasId = Number(formData.value.kelas_id || kelasAktif.value)
  if (!kelasId) {
    alert('Kelas tidak valid.')
    return
  }

  for (let i = 0; i < batchRows.value.length; i++) {
    const row = batchRows.value[i]
    let payload

    if (row.jadwalType === 'pelajaran') {
      const [mapelId, guruId] = row.mapel_guru.split('-')
      payload = {
        kelas_id: kelasId,
        mapel_id: Number(mapelId),
        guru_id: Number(guruId),
        hari: row.hari,
        jam_mulai: row.jam_mulai,
        jam_selesai: row.jam_selesai
      }
    } else {
      payload = {
        kelas_id: kelasId,
        kegiatan_id: Number(row.kegiatan_id),
        hari: row.hari,
        jam_mulai: row.jam_mulai,
        jam_selesai: row.jam_selesai
      }
    }

    try {
      await jadwalService.create(payload)
    } catch (error) {
      const message = error.response?.data?.message || error.message
      throw new Error(`Baris ${i + 1}: ${message}`)
    }
  }

  alert(`Berhasil menambahkan ${batchRows.value.length} jadwal.`)
}

const addJadwal = async () => {
  if (!formData.value.isEdit) {
    try {
      loading.value = true

      await createBatchMixed()

      await loadJadwal(kelasAktif.value)
      closeModal()
    } catch (error) {
      console.error('Error saving batch jadwal:', error)
      alert('Gagal menyimpan batch jadwal: ' + error.message)
    } finally {
      loading.value = false
    }
    return
  }

  // Validasi field umum
  if (!formData.value.hari || !formData.value.jam_mulai || !formData.value.jam_selesai) {
    alert('Semua field harus diisi!')
    return
  }

  if (formData.value.jam_mulai >= formData.value.jam_selesai) {
    alert('Jam mulai harus lebih awal dari jam selesai!')
    return
  }

  // Validasi spesifik tipe
  if (formData.value.jadwalType === 'pelajaran') {
    if (!formData.value.mapel_guru) {
      alert('Pilih mata pelajaran!')
      return
    }

    const [mapelId, guruId] = formData.value.mapel_guru.split('-')
    formData.value.mapel_id = mapelId
    formData.value.guru_id = guruId
  } else {
    if (!formData.value.kegiatan_id) {
      alert('Pilih kegiatan!')
      return
    }
  }

  try {
    loading.value = true
    let data = {}

    if (formData.value.jadwalType === 'pelajaran') {
      data = {
        kelas_id: formData.value.kelas_id,
        mapel_id: parseInt(formData.value.mapel_id),
        guru_id: parseInt(formData.value.guru_id),
        hari: formData.value.hari,
        jam_mulai: formData.value.jam_mulai,
        jam_selesai: formData.value.jam_selesai
      }
    } else {
      data = {
        kelas_id: formData.value.kelas_id,
        kegiatan_id: parseInt(formData.value.kegiatan_id),
        hari: formData.value.hari,
        jam_mulai: formData.value.jam_mulai,
        jam_selesai: formData.value.jam_selesai
      }
    }

    if (formData.value.isEdit) {
      const response = await jadwalService.update(formData.value.id, data)
      if (response.data.success) {
        alert('Jadwal berhasil diperbarui')
        await loadJadwal(kelasAktif.value)
        closeModal()
      }
    } else {
      const response = await jadwalService.create(data)
      if (response.data.success) {
        alert('Jadwal berhasil ditambahkan')
        await loadJadwal(kelasAktif.value)
        closeModal()
      }
    }
  } catch (error) {
    console.error('Error saving jadwal:', error)
    alert('Gagal menyimpan jadwal: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const openEditModal = (jadwal) => {
  if (jadwal) {
    // Determine if it's pelajaran or kegiatan
    const jadwalType = jadwal.kegiatan_id ? 'kegiatan' : 'pelajaran'
    
    formData.value = {
      hari: jadwal.hari,
      jam_mulai: jadwal.jam_mulai,
      jam_selesai: jadwal.jam_selesai,
      mapel_guru: jadwal.mapel_id && jadwal.guru_id ? `${jadwal.mapel_id}-${jadwal.guru_id}` : '',
      mapel_id: jadwal.mapel_id || '',
      guru_id: jadwal.guru_id || '',
      kegiatan_id: jadwal.kegiatan_id || '',
      kelas_id: jadwal.kelas_id,
      jadwalType: jadwalType,
      isEdit: true,
      id: jadwal.id
    }
    showModal.value = true
  }
}

const deleteJadwal = async (jadwalId) => {
  if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
    try {
      loading.value = true
      const response = await jadwalService.delete(jadwalId)
      if (response.data.success) {
        alert('Jadwal berhasil dihapus')
        await loadJadwal(kelasAktif.value)
      }
    } catch (error) {
      console.error('Error deleting jadwal:', error)
      alert('Gagal menghapus jadwal: ' + (error.response?.data?.message || error.message))
    } finally {
      loading.value = false
    }
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

.text-primary {
  color: #0c63e4 !important;
}

.card-jadwal {
  cursor: pointer;
  transition: all 0.2s ease;
}

.card-jadwal:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal.d-block {
  display: block !important;
}

.modal-dialog-centered {
  display: flex;
  align-items: center;
  min-height: calc(100% - 3.5rem);
}

.alert-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.85rem;
}

/* Display all 5 days in one row on large screens */
@media (min-width: 992px) {
  .schedule-grid > [class*="col-"] {
    flex: 0 0 20%;
    max-width: 20%;
  }
}

@media (max-width: 991.98px) {
  .schedule-grid > [class*="col-"] {
    flex: 0 0 50%;
    max-width: 50%;
  }

  .modal-dialog.modal-xl {
    max-width: calc(100vw - 24px) !important;
    margin: 0.75rem auto;
  }
}

@media (max-width: 767.98px) {
  .schedule-grid > [class*="col-"] {
    flex: 0 0 100%;
    max-width: 100%;
  }

  .card-body .d-flex.align-items-center.justify-content-between.gap-2 {
    flex-direction: column;
    align-items: stretch !important;
  }

  .card-body .d-flex.align-items-center.gap-3 {
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
}
</style>
