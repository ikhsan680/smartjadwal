import { createRouter, createWebHistory } from "vue-router"

import Login from "../pages/Login.vue"
import MainLayout from "../layouts/MainLayout.vue"
import LihatJadwalPage from "../pages/LihatJadwalPage.vue"
import JadwalPage from "../pages/JadwalPage.vue"
import KelasPage from "../pages/KelasPage.vue"
import MapelPage from "../pages/MapelPage.vue"
import KegiatanPage from "../pages/KegiatanPage.vue"
import StudentDashboard from "../pages/StudentDashboard.vue"
import StudentLihatJadwalPage from "../pages/StudentLihatJadwalPage.vue"

const routes = [
  // Student Routes
  {
    path: "/siswa/login",
    component: Login
  },
  {
    path: "/siswa/dashboard",
    component: StudentDashboard
  },
  {
    path: "/siswa/lihat-jadwal",
    component: StudentLihatJadwalPage
  },

  // Guru/Admin Routes
  {
    path: "/guru/login",
    component: Login
  },
  {
    path: "/guru",
    component: MainLayout,
    children: [
      {
        path: "",
        redirect: "/guru/login"
      },
      {
        path: "lihat-jadwal",
        component: LihatJadwalPage
      },
      {
        path: "jadwal",
        component: JadwalPage
      },
      {
        path: "kelas",
        component: KelasPage
      },
      {
        path: "mapel",
        component: MapelPage
      },
      {
        path: "kegiatan",
        component: KegiatanPage
      }
    ]
  },

  // Redirect default routes
  {
    path: "/",
    redirect: "/siswa/dashboard"
  },
  {
    path: "/:pathMatch(.*)*",
    redirect: "/siswa/login"
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to) => {
  const token = localStorage.getItem("token")
  const isGuruRoute = to.path.startsWith("/guru/") && to.path !== "/guru/login"

  if (isGuruRoute && !token) {
    return "/guru/login"
  }
})

export default router