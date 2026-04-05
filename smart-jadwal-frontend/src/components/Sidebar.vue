<template>
  <div class="sidebar bg-white border-end">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom sidebar-head">
      <span class="h5 mb-0">🎓 Smart Jadwal</span>
      <button
        class="btn btn-sm btn-outline-secondary d-lg-none"
        type="button"
        @click="toggleMobileMenu"
        :aria-expanded="isMobileMenuOpen ? 'true' : 'false'"
        aria-label="Toggle menu"
      >
        <i :class="isMobileMenuOpen ? 'bi bi-x-lg' : 'bi bi-list'"></i>
      </button>
    </div>

    <div class="sidebar-links" :class="{ open: isMobileMenuOpen }">
      <nav class="nav flex-column">
        <router-link to="/guru/lihat-jadwal" class="nav-link menu-item">
          <i class="bi bi-eye me-2"></i>Lihat Jadwal
        </router-link>

        <router-link to="/guru/jadwal" class="nav-link menu-item">
          <i class="bi bi-calendar3 me-2"></i>Kelola Jadwal
        </router-link>

        <router-link to="/guru/kelas" class="nav-link menu-item">
          <i class="bi bi-building me-2"></i>Kelas
        </router-link>

        <router-link to="/guru/kegiatan" class="nav-link menu-item">
          <i class="bi bi-stars me-2"></i>Kegiatan
        </router-link>

        <router-link to="/guru/mapel" class="nav-link menu-item">
          <i class="bi bi-book me-2"></i>Mata Pelajaran
        </router-link>
      </nav>

      <div class="sidebar-footer">
        <button class="btn btn-danger w-100" @click="logout">
          <i class="bi bi-box-arrow-right me-2"></i>Logout
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter, useRoute } from "vue-router"

const router = useRouter()
const route = useRoute()
const isMobileMenuOpen = ref(false)

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
}

watch(
  () => route.fullPath,
  () => {
    isMobileMenuOpen.value = false
  }
)

const logout = () => {
  localStorage.removeItem("token")
  localStorage.removeItem("user")
  router.push("/guru/login")
}
</script>

<style scoped>
.sidebar {
  width: 240px;
  height: 100vh;
  position: fixed;
  padding: 20px;
  overflow-y: auto;
  z-index: 1000;
}

.menu-item {
  color: #495057;
  text-decoration: none;
  border-radius: 6px;
  padding: 10px 12px;
  margin-bottom: 8px;
  transition: all 0.3s ease;
}

.menu-item:hover {
  background-color: #f8f9fa;
  color: #212529;
}

.menu-item.router-link-active {
  background-color: #e7f1ff;
  color: #0d6efd;
  font-weight: 500;
}

.nav {
  margin-top: 1rem;
}

.sidebar-footer {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 12px;
}

@media (max-width: 991.98px) {
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
    padding: 12px;
    border-right: 0 !important;
    border-bottom: 1px solid #dee2e6;
  }

  .sidebar-links {
    display: none;
  }

  .sidebar-links.open {
    display: block;
  }

  .nav {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 0.5rem;
  }

  .menu-item {
    margin-bottom: 0;
    padding: 8px 10px;
    font-size: 0.9rem;
  }

  .sidebar-footer {
    position: static;
    padding: 10px 0 0;
    margin-top: 8px;
  }
}
</style>
