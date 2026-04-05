<template>
  <div class="student-dashboard">
    <!-- Welcome Card as Full Header -->
    <div class="welcome-card-header">
      <div class="welcome-left">
        <h2>Selamat Datang di smart jadwal</h2>
        <p>Pilih angkatan untuk melihat jadwal pelajaran anda</p>
      </div>
    </div>

    <!-- Angkatan Selection -->
    <div class="selection-section">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>Memuat data...</p>
      </div>

      <!-- Show Angkatan List -->
      <div v-else-if="!selectedAngkatan && angkatanList.length > 0" class="selection-grid">
        <button 
          v-for="angkatan in angkatanList" 
          :key="angkatan"
          @click="selectAngkatan(angkatan)"
          class="selection-card-btn"
        >
          <div class="card-header">
            <span class="icon"></span>
            <span class="title">Kelas {{ angkatan }}</span>
          </div>
          <div class="card-footer">
            <span class="count">{{ getKelasCountByAngkatan(angkatan) }} kelas</span>
            <i class="bi bi-arrow-right"></i>
          </div>
        </button>
      </div>

      <!-- Show Class List -->
      <div v-else-if="selectedAngkatan && filteredKelasList.length > 0">
        <div class="kelas-selection-header">
          <button @click="backToAngkatan" class="btn-back">
            <i class="bi bi-chevron-left"></i> Kembali
          </button>
          <h3 class="kelas-selection-title">Pilih Kelas - Angkatan {{ selectedAngkatan }}</h3>
        </div>
        <div class="selection-grid">
          <button 
            v-for="kelas in filteredKelasList" 
            :key="kelas.id"
            @click="selectClass(kelas)"
            class="selection-card-btn"
          >
            <div class="card-header">
              <span class="icon"></span>
              <div style="display: flex; flex-direction: column; gap: 2px;">
                <span class="title">{{ kelas.nama }}</span>
                <span style="font-size: 0.85rem; color: #9ca3af;">{{ kelas.jurusan }}</span>
              </div>
            </div>
            <div class="card-footer">
              <span class="count">Wali: {{ kelas.wali_kelas || '-' }}</span>
              <i class="bi bi-arrow-right"></i>
            </div>
          </button>
        </div>
      </div>

      <div v-else-if="!loading && angkatanList.length === 0" class="empty-state">
        <p>Tidak ada angkatan tersedia</p>
      </div>

      <div v-else-if="selectedAngkatan && filteredKelasList.length === 0" class="empty-state">
        <p>Tidak ada kelas untuk angkatan {{ selectedAngkatan }}</p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { kelasService } from '../services/resourceService'

const router = useRouter()

const studentName = ref('Siswa')
const kelasList = ref([])
const angkatanList = ref([])
const selectedAngkatan = ref(null)
const loading = ref(false)

const hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']

const getKelasCountByAngkatan = (angkatan) => {
  return kelasList.value.filter(kelas => extractAngkatan(kelas.nama) === angkatan).length
}

// Extract angkatan dari nama kelas (e.g., "X A" -> "X", "XI A" -> "XI", "XII A" -> "XII")
const extractAngkatan = (namaKelas) => {
  const match = namaKelas.match(/^(XII|XI|X)\b/)
  return match ? match[1] : null
}

// Filter kelas berdasarkan angkatan yang dipilih
const filteredKelasList = computed(() => {
  return kelasList.value.filter(kelas => {
    const angkatan = extractAngkatan(kelas.nama)
    return angkatan === selectedAngkatan.value
  })
})

const selectAngkatan = (angkatan) => {
  selectedAngkatan.value = angkatan
}

const backToAngkatan = () => {
  selectedAngkatan.value = null
}

const selectClass = (kelas) => {
  // Simpan angkatan dan kelas yang dipilih ke localStorage
  localStorage.setItem("selectedAngkatan", selectedAngkatan.value)
  localStorage.setItem("selectedKelas", JSON.stringify(kelas))
  // Navigate ke halaman lihat jadwal
  router.push("/siswa/lihat-jadwal")
}

const logout = () => {
  localStorage.removeItem("token")
  localStorage.removeItem("user")
  localStorage.removeItem("selectedAngkatan")
  localStorage.removeItem("selectedKelas")
  router.push("/siswa/login")
}

let timeInterval

onMounted(async () => {
  try {
    // Get user dari localStorage
    const user = localStorage.getItem("user")
    if (user) {
      const userData = JSON.parse(user)
      studentName.value = userData.name || 'Siswa'
    }

    // Load kelas data
    loading.value = true
    const kelasResponse = await kelasService.getAll()
    if (kelasResponse.data.success && kelasResponse.data.data.length > 0) {
      kelasList.value = kelasResponse.data.data

      // Extract unique angkatan dari semua kelas dan sort
      const angkatanSet = new Set()
      kelasList.value.forEach(kelas => {
        const angkatan = extractAngkatan(kelas.nama)
        if (angkatan) {
          angkatanSet.add(angkatan)
        }
      })
      
      // Sort berdasarkan urutan X, XI, XII
      const angkatanArray = Array.from(angkatanSet)
      angkatanArray.sort((a, b) => {
        const order = { 'X': 1, 'XI': 2, 'XII': 3 }
        return (order[a] || 999) - (order[b] || 999)
      })
      
      angkatanList.value = angkatanArray
    }
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval)
  }
})
</script>

<style scoped>
* {
  box-sizing: border-box;
}

.student-dashboard {
  min-height: 100vh;
  background: #f5f6f8;
  padding: 20px;
}

/* Welcome Card as Full Header */
.welcome-card-header {
  background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
  color: white;
  padding: 32px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 102, 204, 0.2);
  margin-bottom: 32px;
  margin-left: -20px;
  margin-right: -20px;
  margin-top: -20px;
  width: calc(100% + 40px);
}

.welcome-left h2 {
  font-size: 2rem;
  margin: 0 0 12px 0;
  font-weight: 700;
}

.welcome-left p {
  font-size: 1.2rem;
  margin: 0;
  opacity: 0.95;
  line-height: 1.5;
}



.btn-back {
  background: #f3f4f6;
  color: #4b5563;
  border: none;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s;
  font-weight: 600;
  white-space: nowrap;
}

.btn-back:hover {
  background: #e5e7eb;
}

.kelas-selection-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.kelas-selection-title {
  margin: 0;
  font-size: 1.2rem;
}

/* Selection Section */
.selection-section {
  background: white;
  border-radius: 8px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  max-width: 900px;
  margin-left: auto;
  margin-right: auto;
  margin-top: 90px;
  margin-bottom: 100px;
}

/* Loading */
.loading {
  text-align: center;
  padding: 40px 20px;
}

.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid #e5e7eb;
  border-top-color: #0066cc;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Selection Grid */
.selection-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
}

.selection-card-btn {
  background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 28px 24px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 160px;
  text-align: left;
  font-family: inherit;
  position: relative;
  overflow: hidden;
}

.selection-card-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #0066cc, #0052a3);
}

.selection-card-btn:hover {
  border-color: #0066cc;
  background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
  box-shadow: 0 8px 20px rgba(0, 102, 204, 0.2);
  transform: translateY(-4px);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.card-header .icon {
  font-size: 1.8rem;
}

.card-header .title {
  font-size: 1.2rem;
  color: #1a1a1a;
  font-weight: 700;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 1rem;
  color: #6b7280;
}

.card-footer .count {
  background: #e0e7ff;
  color: #0066cc;
  padding: 4px 10px;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.75rem;
}

.selection-card-btn i {
  font-size: 1.1rem;
  color: #d1d5db;
}

.selection-card-btn:hover i {
  color: #0066cc;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 32px 20px;
  color: #9ca3af;
}

.empty-state p {
  margin: 0;
  font-size: 0.95rem;
}

/* Responsive */
@media (max-width: 768px) {
  .student-dashboard {
    padding: 12px;
  }

  .welcome-card-header {
    flex-direction: column;
    text-align: center;
    padding: 24px 16px;
    margin-left: -12px;
    margin-right: -12px;
    margin-top: -12px;
    width: calc(100% + 24px);
  }

  .welcome-left h2 {
    font-size: 1.5rem;
  }

  .welcome-left p {
    font-size: 1rem;
  }



  .selection-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .selection-section {
    padding: 18px;
    margin-top: 48px;
    margin-bottom: 70px;
  }

  .selection-card-btn {
    padding: 20px 14px;
    min-height: 130px;
  }

  .card-header .title {
    font-size: 1rem;
  }

  .kelas-selection-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .kelas-selection-title {
    font-size: 1rem;
    line-height: 1.35;
  }

}

@media (max-width: 575.98px) {
  .selection-grid {
    grid-template-columns: 1fr;
  }

  .welcome-left h2 {
    font-size: 1.25rem;
  }

  .welcome-left p {
    font-size: 0.92rem;
  }

  .selection-section {
    margin-top: 36px;
    padding: 14px;
  }

  .selection-card-btn {
    min-height: 118px;
  }

  .card-footer {
    font-size: 0.85rem;
  }
}
</style>
