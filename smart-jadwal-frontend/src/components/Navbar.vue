<template>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">

  <span class="navbar-brand">Smart Jadwal</span>

  <div class="ms-auto">

    <button class="btn btn-light btn-sm" @click="logout">
      Logout
    </button>

  </div>

</nav>

</template>

<script setup>

import { useRouter } from "vue-router"

const router = useRouter()

const logout = () => {

  localStorage.removeItem("user")
  localStorage.removeItem("token")

  router.push("/login")

}

</script>