<template>
  <div class="container-fluid py-4">
    <div class="mb-5">
      <h1 class="h2 text-dark fw-bold mb-2">Manajemen Kegiatan</h1>
      <p class="text-muted">Kelola daftar kegiatan sekolah yang dipakai di jadwal</p>
    </div>

    <div class="mb-4 d-flex justify-content-end">
      <button class="btn btn-primary" @click="openModal" :disabled="loading">
        <i class="bi bi-plus-lg me-2"></i>Tambah Kegiatan
      </button>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0 pt-3 pb-2">
        <input
          v-model="kegiatanSearch"
          type="search"
          class="form-control"
          placeholder="Cari nama kegiatan..."
        />
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="px-3 py-3" style="width: 80px;">No</th>
                <th class="px-3 py-3">Nama Kegiatan</th>
                <th class="px-3 py-3 text-center" style="width: 140px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredKegiatan.length === 0">
                <td colspan="3" class="text-center text-muted py-4">Belum ada data kegiatan.</td>
              </tr>
              <tr v-for="(kegiatan, idx) in filteredKegiatan" :key="kegiatan.id">
                <td class="px-3 py-3 text-muted">{{ idx + 1 }}</td>
                <td class="px-3 py-3 fw-semibold text-dark">{{ kegiatan.nama }}</td>
                <td class="px-3 py-3">
                  <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-primary" @click="openEditModal(kegiatan)" :disabled="loading" title="Edit kegiatan">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="deleteKegiatan(kegiatan.id)" :disabled="loading" title="Hapus kegiatan">
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

    <div v-if="showModal" class="modal d-block" tabindex="-1" style="background-color: rgba(0, 0, 0, 0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ formData.isEdit ? 'Edit Kegiatan' : 'Tambah Kegiatan' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveKegiatan">
              <div class="mb-3">
                <label for="namaKegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                <input
                  v-model="formData.nama"
                  type="text"
                  id="namaKegiatan"
                  class="form-control"
                  placeholder="Contoh: Upacara Bendera"
                  required
                />
              </div>

              <div class="d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="loading || !formData.nama.trim()">
                  {{ formData.isEdit ? 'Update' : 'Simpan' }}
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
import { ref, computed, onMounted } from 'vue'
import { kegiatanService } from '../services/resourceService'

const kegiatanList = ref([])
const kegiatanSearch = ref('')
const showModal = ref(false)
const loading = ref(false)

const formData = ref({
  nama: '',
  isEdit: false,
  id: null
})

const filteredKegiatan = computed(() => {
  const keyword = kegiatanSearch.value.trim().toLowerCase()
  if (!keyword) return kegiatanList.value

  return kegiatanList.value.filter((kegiatan) => {
    return kegiatan.nama.toLowerCase().includes(keyword)
  })
})

onMounted(async () => {
  await loadKegiatan()
})

const loadKegiatan = async () => {
  try {
    loading.value = true
    const response = await kegiatanService.getAll()
    if (response.data.success) {
      kegiatanList.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading kegiatan:', error)
    alert('Gagal memuat data kegiatan')
  } finally {
    loading.value = false
  }
}

const openModal = () => {
  showModal.value = true
  formData.value = {
    nama: '',
    isEdit: false,
    id: null
  }
}

const openEditModal = (kegiatan) => {
  showModal.value = true
  formData.value = {
    nama: kegiatan.nama,
    isEdit: true,
    id: kegiatan.id
  }
}

const closeModal = () => {
  showModal.value = false
  formData.value = {
    nama: '',
    isEdit: false,
    id: null
  }
}

const saveKegiatan = async () => {
  const payload = {
    nama: formData.value.nama.trim()
  }

  if (!payload.nama) {
    alert('Nama kegiatan harus diisi!')
    return
  }

  try {
    loading.value = true

    if (formData.value.isEdit) {
      const response = await kegiatanService.update(formData.value.id, payload)
      if (response.data.success) {
        alert('Kegiatan berhasil diperbarui')
      }
    } else {
      const response = await kegiatanService.create(payload)
      if (response.data.success) {
        alert('Kegiatan berhasil ditambahkan')
      }
    }

    await loadKegiatan()
    closeModal()
  } catch (error) {
    console.error('Error saving kegiatan:', error)
    alert('Gagal menyimpan kegiatan: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const deleteKegiatan = async (id) => {
  if (!confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')) return

  try {
    loading.value = true
    const response = await kegiatanService.delete(id)
    if (response.data.success) {
      alert('Kegiatan berhasil dihapus')
      await loadKegiatan()
    }
  } catch (error) {
    console.error('Error deleting kegiatan:', error)
    alert('Gagal menghapus kegiatan: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.modal.d-block {
  display: block !important;
}

.modal-dialog {
  margin-top: 100px;
}

@media (max-width: 767.98px) {
  .container-fluid.py-4 {
    padding-top: 0.75rem !important;
  }

  .mb-4.d-flex.justify-content-end {
    justify-content: stretch !important;
  }

  .mb-4.d-flex.justify-content-end .btn {
    width: 100%;
  }

  .modal-dialog {
    margin: 0.75rem;
  }
}
</style>
