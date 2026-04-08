<template>
  <div class="container-fluid py-4">
    <!-- Header -->
    <div class="mb-5">
      <h1 class="h2 text-dark fw-bold mb-2">Manajemen Kelas</h1>
      <p class="text-muted">Kelola kelas dan pengelompokan siswa sekolah</p>
    </div>

    <!-- Add Button -->
    <div class="mb-4 d-flex justify-content-end gap-2">
      <button class="btn btn-outline-primary" @click="openFullscreen" :disabled="loading">
        <i class="bi bi-arrows-fullscreen me-2"></i>Fullscreen
      </button>
      <button class="btn btn-primary" @click="openModal" :disabled="loading">
        <i class="bi bi-plus-lg me-2"></i>Tambah Kelas
      </button>
    </div>

    <!-- Table List of Classes -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 pt-3 pb-2">
        <input
          v-model="kelasSearch"
          type="search"
          class="form-control"
          placeholder="Cari nama kelas, jurusan, atau wali kelas..."
        />
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="px-3 py-3">Nama Kelas</th>
                <th class="px-3 py-3">Jurusan</th>
                <th class="px-3 py-3">Wali Kelas</th>
                <th class="px-3 py-3 text-center" style="width: 140px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredKelas.length === 0">
                <td colspan="4" class="text-center text-muted py-4">Belum ada data kelas.</td>
              </tr>
              <tr v-for="kelas in filteredKelas" :key="kelas.id">
                <td class="px-3 py-3 fw-semibold text-dark">{{ kelas.nama }}</td>
                <td class="px-3 py-3 text-muted">{{ kelas.jurusan }}</td>
                <td class="px-3 py-3 text-muted">{{ kelas.wali_guru?.nama || kelas.wali_kelas || '-' }}</td>
                <td class="px-3 py-3">
                  <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-primary" @click="openEditModal(kelas)" :disabled="loading" title="Edit kelas">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="deleteKelas(kelas.id)" :disabled="loading" title="Hapus kelas">
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
          <h5 class="mb-0">Data Kelas</h5>
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
              <thead>
                <tr>
                  <th colspan="3" class="report-title">Data Kelas</th>
                </tr>
                <tr>
                  <th style="width: 70px;">No</th>
                  <th>Kelas</th>
                  <th>Wali kelas</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="kelasForReport.length === 0">
                  <td colspan="3" class="text-center text-muted py-4">Belum ada data kelas.</td>
                </tr>
                <tr v-for="(kelas, idx) in kelasForReport" :key="kelas.id">
                  <td class="text-center">{{ idx + 1 }}</td>
                  <td>{{ kelas.nama }}</td>
                  <td>{{ kelas.wali_guru?.nama || kelas.wali_kelas || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Tambah/Edit Kelas -->
    <div v-if="showModal" class="modal d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ formData.isEdit ? 'Edit Kelas' : 'Tambah Kelas' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveKelas">
              <!-- Tier Selection -->
              <div class="mb-3">
                <label for="tierKelas" class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                <select v-model="formData.tier" id="tierKelas" class="form-select" required>
                  <option value="">-- Pilih Tingkat --</option>
                  <option value="X">Kelas X (Sepuluh)</option>
                  <option value="XI">Kelas XI (Sebelas)</option>
                  <option value="XII">Kelas XII (Dua Belas)</option>
                </select>
              </div>

              <!-- Program/Jurusan Code -->
              <div class="mb-3">
                <label for="codeKelas" class="form-label">Kode Program <span class="text-danger">*</span></label>
                <select v-model="formData.codeSelect" id="codeKelas" class="form-select" required>
                  <option value="">-- Pilih Kode Program --</option>
                  <option v-for="option in programOptions" :key="option" :value="option">{{ option }}</option>
                  <option value="__new__">+ Tambah kode baru</option>
                </select>
              </div>

              <div v-if="formData.codeSelect === '__new__'" class="mb-3">
                <label for="newCodeKelas" class="form-label">Kode Program Baru <span class="text-danger">*</span></label>
                <input
                  v-model="formData.codeNew"
                  @input="formData.codeNew = formData.codeNew.toUpperCase()"
                  type="text"
                  id="newCodeKelas"
                  class="form-control"
                  placeholder="Contoh: TSM"
                  maxlength="10"
                  required
                />
              </div>

              <!-- Class Number (conditional) -->
              <div v-if="needsClassNumber" class="mb-3">
                <label for="nomorKelas" class="form-label">Nomor Kelas <span class="text-danger">*</span></label>
                <input v-model="formData.nomor" type="number" id="nomorKelas" class="form-control" placeholder="Contoh: 1, 2, 3" min="1" max="9" required />
              </div>

              <!-- Preview Nama Kelas -->
              <div class="mb-3">
                <label class="form-label">Preview Nama Kelas</label>
                <div class="alert alert-info mb-0">
                  <strong>{{ generateNamaKelas }}</strong>
                </div>
              </div>

              <div class="mb-3">
                <label for="jurusanKelas" class="form-label">Jurusan <span class="text-danger">*</span></label>
                <input v-model="formData.jurusan" type="text" id="jurusanKelas" class="form-control" placeholder="Contoh: Rekayasa Perangkat Lunak" required />
              </div>

              <div class="mb-3">
                <label for="waliKelasGuru" class="form-label">Wali Kelas</label>
                <select v-model="formData.wali_guru_id" id="waliKelasGuru" class="form-select">
                  <option value="">-- Pilih Guru --</option>
                  <option value="new">+ Tambah Guru Baru</option>
                  <option v-for="guru in guruList" :key="guru.id" :value="guru.id">{{ guru.nama }}</option>
                </select>
              </div>

              <div v-if="formData.wali_guru_id === 'new'" class="mb-3">
                <label for="waliKelasGuruBaru" class="form-label">Nama Guru Baru</label>
                <input
                  v-model="formData.nama_wali_guru_baru"
                  type="text"
                  id="waliKelasGuruBaru"
                  class="form-control"
                  placeholder="Contoh: Ibu Sinta"
                  required
                />
              </div>

              <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="loading || !formData.tier || !selectedCode || !formData.jurusan || (needsClassNumber && !formData.nomor) || (formData.wali_guru_id === 'new' && !formData.nama_wali_guru_baru.trim())">{{ formData.isEdit ? 'Update' : 'Simpan' }}</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { kelasService, mapelService } from '../services/resourceService'

const kelasList = ref([])
const guruList = ref([])
const showModal = ref(false)
const loading = ref(false)
const showFullscreen = ref(false)
const fullscreenTableContent = ref(null)
const DEFAULT_PROGRAM_OPTIONS = ['RPL', 'AKL', 'PPLG', 'TJKT']

const formData = ref({
  tier: '',
  codeSelect: '',
  codeNew: '',
  nomor: '',
  nama: '',
  jurusan: '',
  wali_guru_id: '',
  nama_wali_guru_baru: '',
  wali_kelas: '',
  isEdit: false,
  id: null
})

const programOptions = ref([...DEFAULT_PROGRAM_OPTIONS])
const kelasSearch = ref('')

const filteredKelas = computed(() => {
  const keyword = kelasSearch.value.trim().toLowerCase()
  if (!keyword) return kelasList.value

  return kelasList.value.filter((kelas) => {
    return [kelas.nama, kelas.jurusan, kelas.wali_guru?.nama || kelas.wali_kelas || '']
      .join(' ')
      .toLowerCase()
      .includes(keyword)
  })
})

const kelasForReport = computed(() => {
  return [...kelasList.value].sort((a, b) => a.nama.localeCompare(b.nama))
})

const selectedCode = computed(() => {
  if (formData.value.codeSelect === '__new__') {
    return formData.value.codeNew.trim().toUpperCase()
  }
  return formData.value.codeSelect
})

// Computed property untuk generate nama kelas otomatis
const countKelasWithTierCode = (tier, code) => {
  return kelasList.value.filter(k => {
    // Skip kelas yang sedang diedit
    if (formData.value.isEdit && k.id === formData.value.id) {
      return false
    }
    
    // Parse nama kelas untuk extract tier dan code
    const parts = k.nama.split(' ')
    if (parts.length >= 2) {
      const kelastier = parts[0]
      const kelascode = parts[1]
      return kelastier === tier && kelascode === code
    }
    return false
  }).length
}

// Check apakah perlu nomor kelas
const needsClassNumber = computed(() => {
  if (!formData.value.tier || !selectedCode.value) return false
  const count = countKelasWithTierCode(formData.value.tier, selectedCode.value)
  return count > 0 // Jika sudah ada kelas dengan kombinasi ini, perlu nomor
})

// Computed property untuk generate nama kelas otomatis
const generateNamaKelas = computed(() => {
  if (formData.value.tier && selectedCode.value) {
    const count = countKelasWithTierCode(formData.value.tier, selectedCode.value)
    
    if (count === 0) {
      // Ini adalah kelas pertama/satu-satunya dengan kombinasi ini, jadi tidak pakai nomor
      return `${formData.value.tier} ${selectedCode.value}`
    } else {
      // Ada kelas lain dengan kombinasi ini, jadi perlu nomor
      if (formData.value.nomor) {
        return `${formData.value.tier} ${selectedCode.value} ${formData.value.nomor}`
      }
      return `${formData.value.tier} ${selectedCode.value} [nomor kelas]`
    }
  }
  return '(Preview nama kelas akan muncul di sini)'
})

onMounted(async () => {
  await Promise.all([loadKelas(), loadGuru()])
})

const loadKelas = async () => {
  try {
    loading.value = true
    const response = await kelasService.getAll()
    if (response.data.success) {
      kelasList.value = response.data.data

      // Sinkronkan opsi kode program dari data kelas yang sudah ada
      const extractedCodes = response.data.data
        .map(item => parseNamaKelas(item.nama).code)
        .filter(Boolean)
      programOptions.value = [...new Set([...DEFAULT_PROGRAM_OPTIONS, ...extractedCodes])]
    }
  } catch (error) {
    console.error('Error loading kelas:', error)
    alert('Gagal memuat data kelas')
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
    alert('Gagal memuat data guru')
  }
}

const openModal = () => {
  showModal.value = true
  formData.value = {
    tier: '',
    codeSelect: '',
    codeNew: '',
    nomor: '',
    nama: '',
    jurusan: '',
    wali_guru_id: '',
    nama_wali_guru_baru: '',
    wali_kelas: '',
    isEdit: false,
    id: null
  }
}

const closeModal = () => {
  showModal.value = false
  formData.value = {
    tier: '',
    codeSelect: '',
    codeNew: '',
    nomor: '',
    nama: '',
    jurusan: '',
    wali_guru_id: '',
    nama_wali_guru_baru: '',
    wali_kelas: '',
    isEdit: false,
    id: null
  }
}

const saveKelas = async () => {
  if (!formData.value.tier || !selectedCode.value || !formData.value.jurusan) {
    alert('Semua field yang ditandai (*) harus diisi!')
    return
  }

  if (!/^[A-Z0-9]{2,10}$/.test(selectedCode.value)) {
    alert('Kode program harus 2-10 karakter huruf/angka tanpa spasi!')
    return
  }

  // Validasi nomor kelas jika diperlukan
  if (needsClassNumber.value && !formData.value.nomor) {
    alert('Nomor kelas harus diisi untuk kelas dengan program yang sama!')
    return
  }

  try {
    loading.value = true

    let waliGuruId = null
    if (formData.value.wali_guru_id === 'new') {
      const namaGuruBaru = formData.value.nama_wali_guru_baru.trim()
      if (!namaGuruBaru) {
        alert('Nama guru baru untuk wali kelas harus diisi!')
        return
      }

      const guruResponse = await mapelService.createGuru({ nama: namaGuruBaru })
      if (guruResponse.data.success) {
        waliGuruId = guruResponse.data.data.id
        await loadGuru()
      }
    } else if (formData.value.wali_guru_id) {
      waliGuruId = parseInt(formData.value.wali_guru_id)
    }
    
    // Generate nama kelas automatically
    let namaKelas
    if (needsClassNumber.value && formData.value.nomor) {
      // Ada kelas lain dengan kombinasi tier+code, gunakan nomor
      const nomor = parseInt(formData.value.nomor)
      if (isNaN(nomor) || nomor < 1 || nomor > 9) {
        alert('Nomor kelas harus berupa angka 1-9!')
        return
      }
      namaKelas = `${formData.value.tier} ${selectedCode.value} ${nomor}`
    } else {
      // Kelas pertama/satu-satunya dengan kombinasi ini, jangan gunakan nomor
      namaKelas = `${formData.value.tier} ${selectedCode.value}`
    }

    if (formData.value.codeSelect === '__new__' && !programOptions.value.includes(selectedCode.value)) {
      programOptions.value = [...programOptions.value, selectedCode.value]
    }
    
    const data = {
      nama: namaKelas,
      jurusan: formData.value.jurusan,
      wali_guru_id: waliGuruId,
      wali_kelas: null
    }

    if (formData.value.isEdit) {
      const response = await kelasService.update(formData.value.id, data)
      if (response.data.success) {
        alert('Kelas berhasil diperbarui')
        await loadKelas()
        closeModal()
      }
    } else {
      const response = await kelasService.create(data)
      if (response.data.success) {
        alert('Kelas berhasil ditambahkan')
        await loadKelas()
        closeModal()
      }
    }
  } catch (error) {
    console.error('Error saving kelas:', error)
    alert('Gagal menyimpan kelas: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

// Parse nama kelas and extract tier, code, nomor
const parseNamaKelas = (nama) => {
  const parts = nama.split(' ')
  if (parts.length >= 2) {
    return {
      tier: parts[0], // X, XI, XII
      code: parts[1], // RPL, AKL, PPLG, etc
      nomor: parts.length >= 3 ? parts[2] : '' // Nomor hanya ada jika 3+ bagian
    }
  }
  return {
    tier: '',
    code: '',
    nomor: ''
  }
}

const openEditModal = (kelas) => {
  const parsed = parseNamaKelas(kelas.nama)
  const matchedGuru = guruList.value.find((guru) => guru.nama === kelas.wali_kelas)
  const existingWaliGuruId = kelas.wali_guru_id || matchedGuru?.id || ''

  const hasOption = programOptions.value.includes(parsed.code)
  formData.value = {
    tier: parsed.tier,
    codeSelect: hasOption ? parsed.code : '__new__',
    codeNew: hasOption ? '' : parsed.code,
    nomor: parsed.nomor,
    nama: kelas.nama,
    jurusan: kelas.jurusan,
    wali_guru_id: existingWaliGuruId,
    nama_wali_guru_baru: '',
    wali_kelas: kelas.wali_kelas || '',
    isEdit: true,
    id: kelas.id
  }
  showModal.value = true
}

const deleteKelas = async (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus kelas ini?')) {
    try {
      loading.value = true
      const response = await kelasService.delete(id)
      if (response.data.success) {
        alert('Kelas berhasil dihapus')
        await loadKelas()
      }
    } catch (error) {
      console.error('Error deleting kelas:', error)
      alert('Gagal menghapus kelas: ' + (error.response?.data?.message || error.message))
    } finally {
      loading.value = false
    }
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

      // Clone konten ke area tersembunyi agar seluruh tabel tercapture (tidak terpotong scroll container).
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

      pdf.save('data-kelas-a4.pdf')
      return
    } catch (libError) {
      console.warn('jsPDF/html2canvas tidak tersedia, fallback print:', libError.message)
    }

    const printWindow = window.open('', '_blank')
    if (!printWindow) throw new Error('Popup diblokir browser')

    printWindow.document.write(`
      <html>
        <head>
          <title>Data Kelas</title>
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
  color: #0c63e4;
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
  width: min(1200px, 100%);
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
  max-width: 900px;
  margin: 0 auto;
  background: #fff;
  font-family: 'Times New Roman', Times, serif;
  color: #111;
}

.report-table {
  border: 2px solid #1f1f1f;
  font-size: 15px;
}

.report-table th,
.report-table td {
  border: 1px solid #2f2f2f;
  vertical-align: middle;
  padding: 6px 8px;
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

@media (max-width: 991.98px) {
  .container-fluid.py-4 {
    padding-top: 0.75rem !important;
  }

  .fullscreen-modal-content {
    width: 100%;
    height: 94vh;
    border-radius: 8px;
  }
}

@media (max-width: 767.98px) {
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
