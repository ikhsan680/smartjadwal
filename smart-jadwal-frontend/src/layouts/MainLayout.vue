<template>
  <div class="layout">
    <Sidebar />
    <div class="content">
      <router-view />
    </div>
  </div>
</template>

<script setup>
import Sidebar from "../components/Sidebar.vue"
</script>

<style>
.layout {
  display: flex;
}

.content {
  margin-left: 240px;
  padding: 30px;
  width: 100%;
  background-color: #f8f9fa;
  min-height: 100vh;
}

@media (max-width: 991.98px) {
  .layout {
    display: block;
  }

  .content {
    margin-left: 0;
    padding: 16px 14px;
  }

  .content .container-fluid {
    padding-left: 0.25rem;
    padding-right: 0.25rem;
  }

  .content h1,
  .content .h2,
  .content .h3 {
    line-height: 1.2;
  }
}

@media (max-width: 575.98px) {
  .content {
    padding: 12px;
  }

  .content h1,
  .content .h2,
  .content .h3 {
    font-size: 1.35rem !important;
  }

  .content .card-body {
    padding: 0.75rem;
  }
}
</style>
