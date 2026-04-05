<template>

<div class="min-vh-100 d-flex align-items-center justify-content-center bg-gradient">

  <div class="card border-0 shadow-lg p-4 login-card">

    <div class="text-center mb-4">

      <div class="logo-circle mb-3">
        🎓
      </div>

      <h2 class="fw-bold">Smart Jadwal</h2>
      <p class="text-muted">{{ isStudentLogin ? "Student Portal" : "Teacher / Admin Portal" }}</p>

    </div>

    <form @submit.prevent="login">

      <!-- EMAIL -->
      <div class="mb-3">
        <label class="form-label">Email</label>

        <input
          type="email"
          class="form-control"
          placeholder="Masukan Email"
          v-model="email"
          required
        >
      </div>

      <!-- PASSWORD -->
      <div class="mb-4">
        <label class="form-label">Password</label>

        <div class="input-group">

          <input
            :type="showPassword ? 'text' : 'password'"
            class="form-control"
            placeholder="Masukan Password"
            v-model="password"
            required
          >

          <button
            class="btn btn-outline-secondary"
            type="button"
            @click="togglePassword"
          >
            <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
          </button>

        </div>

      </div>

      <!-- BUTTON LOGIN -->
      <button class="btn btn-primary w-100">
        Login
      </button>

    </form>

    <div class="text-center mt-4">
      <a href="#" @click.prevent="goToStudentPortal" class="text-decoration-none text-primary">
        Student Portal →
      </a>
    </div>

  </div>

</div>

</template>

<script setup>

import { ref, computed } from "vue"
import { useRouter, useRoute } from "vue-router"
import api, { ensureCsrfCookie, setAuthToken } from "../services/api"

const router = useRouter()
const route = useRoute()

const email = ref("")
const password = ref("")
const showPassword = ref(false)

// Detect if this is student or guru login
const isStudentLogin = computed(() => {
  return route.path.includes("/siswa/")
})

const togglePassword = () => {
  showPassword.value = !showPassword.value
}

const goToStudentPortal = () => {
  router.push("/siswa/dashboard")
}

const login = async () => {

  try {

    // ambil CSRF sanctum
    await ensureCsrfCookie()

    // request login
    const res = await api.post("/api/login",{
      email: email.value,
      password: password.value
    })

    // simpan user
    localStorage.setItem("user", JSON.stringify(res.data.user))

    // simpan token
    localStorage.setItem("token", res.data.token)

    // set header auth pada api instance
    setAuthToken(res.data.token)

    // redirect ke dashboard sesuai role
    if (isStudentLogin.value) {
      router.push("/siswa/dashboard")
    } else {
      router.push("/guru/lihat-jadwal")
    }

  } catch(err){

    alert(err.response?.data?.message || "Login gagal")

  }

}

</script>

<style>

.bg-gradient{
  background: linear-gradient(135deg,#eaf2ff,#dbeafe);
}

.login-card{
  width:420px;
  border-radius:20px;
}

.logo-circle{
  width:65px;
  height:65px;
  background:#0d6efd;
  color:white;
  border-radius:50%;
  display:flex;
  align-items:center;
  justify-content:center;
  font-size:30px;
  margin:auto;
}

@media (max-width: 767.98px) {
  .login-card{
    width: min(420px, calc(100vw - 32px));
    padding: 20px !important;
    border-radius: 16px;
  }
}

@media (max-width: 575.98px) {
  .login-card{
    width: calc(100vw - 20px);
    padding: 16px !important;
  }
}

</style>