import { createRouter, createWebHistory } from "vue-router";
import Home from "../page/Home.vue";
import Subject from "../page/Subject.vue";
import api from "../js/api";

import AdminLayout from "../page/admin/AdminLayout.vue";

const routes = [
  { path: "/", name: "Home", component: Home },
  { path: "/subject", name: "Subject", component: Subject },
  { path: "/rules", name: "Rules", component: () => import("../page/Rules.vue") },
  { path: "/results", name: "Results", component: () => import("../page/Results.vue") },
  { path: "/login", name: "Login", component: () => import("../page/Login.vue") },
  { path: "/register", name: "Register", component: () => import("../page/Register.vue") },
  { path: "/profile", name: "Profile", component: () => import("../page/Profile.vue") },
  { path: "/edit-profile", name: "EditProfile", component: () => import("../page/EditProfile.vue") },
  { path: "/waiting", name: "Waiting", component: () => import("../page/Waiting.vue") },
  { path: "/quiz/:subjectId", name: "Quiz", component: () => import("../page/Quiz.vue") },

  // 🔐 Админ логин отдельно
  {
    path: "/admin-login",
    name: "AdminLogin",
    component: () => import("../page/admin/AdminLogin.vue"),
  },

  // 🔥 Админ панель (вложенная структура)
  {
    path: "/admin",
    component: AdminLayout,
    meta: { requiresAdmin: true },
   children: [
  {
    path: "",
    name: "AdminDashboard",
    meta: { requiresAdmin: true },
    component: () => import("../page/admin/AdminDashboard.vue"),
  },

  {
    path: "requests",
    name: "AdminRequests",
    meta: { requiresAdmin: true },
    component: () => import("../page/admin/AdminRequests.vue"),
  },

  {
    path: "quizzes",
    name: "AdminQuizzes",
    meta: { requiresAdmin: true },
    component: () => import("../page/admin/AdminQuizzes.vue"),
  },

  {
    path: "results",
    name: "AdminResults",
    meta: { requiresAdmin: true },
    component: () => import("../page/admin/AdminResults.vue"),
  },
],
  },
];  

const router = createRouter({
  history: createWebHistory(),
  routes,
});


// 🔐 Guard
router.beforeEach(async (to, from, next) => {

  const token = localStorage.getItem("token")

  if (to.meta.requiresAdmin) {

    if (!token) return next("/admin-login")

    try {

      const res = await api.get('/profile', {
        headers: { Authorization: `Bearer ${token}` }
      })

      const user = res.data

      if (user.is_admin) {
        return next()
      }

      return next("/")

    } catch {
      return next("/")
    }

  }

  next()
});

export default router;