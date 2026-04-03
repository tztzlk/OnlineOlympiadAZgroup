import { createRouter, createWebHistory } from "vue-router";
import Home from "../page/Home.vue";
import Subject from "../page/Subject.vue";
import api from "../js/api";
import AdminLayout from "../page/admin/AdminLayout.vue";

const routes = [
  { path: "/", name: "Home", component: Home },
  { path: "/subject", name: "Subject", component: Subject },
  { path: "/about", name: "About", component: () => import("../page/About.vue") },
  { path: "/rules", name: "Rules", component: () => import("../page/Rules.vue") },
  { path: "/leaderboard", name: "Leaderboard", component: () => import("../page/Leaderboard.vue") },
  { path: "/certificate-check", name: "CertificateCheck", component: () => import("../page/CertificateCheck.vue") },
  { path: "/results", name: "Results", component: () => import("../page/Results.vue") },
  { path: "/profile/results/:resultId/certificate-preview", name: "CertificatePreview", component: () => import("../page/CertificatePreview.vue") },
  { path: "/login", name: "Login", component: () => import("../page/Login.vue") },
  { path: "/register", name: "Register", component: () => import("../page/Register.vue") },
  { path: "/forgot-password", name: "ForgotPassword", component: () => import("../page/ForgotPassword.vue") },
  { path: "/reset-password", name: "ResetPassword", component: () => import("../page/ResetPassword.vue") },
  { path: "/help-desk", name: "HelpDesk", component: () => import("../page/HelpDesk.vue") },
  { path: "/profile", name: "Profile", component: () => import("../page/Profile.vue") },
  { path: "/edit-profile", name: "EditProfile", component: () => import("../page/EditProfile.vue") },
  { path: "/waiting", name: "Waiting", component: () => import("../page/Waiting.vue") },
  { path: "/quiz/:subjectId", name: "Quiz", component: () => import("../page/Quiz.vue") },
  { path: "/training/:subjectId", name: "Training", component: () => import("../page/Training.vue") },
  {
    path: "/admin-login",
    name: "AdminLogin",
    component: () => import("../page/admin/AdminLogin.vue"),
  },
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
      {
        path: "payments",
        name: "AdminPayments",
        meta: { requiresAdmin: true },
        component: () => import("../page/admin/AdminPayments.vue"),
      },
      {
        path: "callbacks",
        name: "AdminCallbacks",
        meta: { requiresAdmin: true },
        component: () => import("../page/admin/AdminCallbacks.vue"),
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem("token");
  const storedUser = localStorage.getItem("user");
  const sessionType = localStorage.getItem("session_type");
  const requiresAdmin = to.matched.some((record) => record.meta.requiresAdmin);

  if (to.path === "/admin-login" && token && sessionType === "admin") {
    return next({ name: "AdminDashboard" });
  }

  if (requiresAdmin) {
    if (!token) return next("/admin-login");

    try {
      const parsedUser = storedUser ? JSON.parse(storedUser) : null;

      if (parsedUser?.is_admin && sessionType === "admin") {
        return next();
      }
    } catch {}

    try {
      const res = await api.get("/profile", {
        headers: { Authorization: `Bearer ${token}` },
      });

      const isAdmin = Boolean(res.data?.is_admin ?? res.data?.user?.is_admin);

      if (isAdmin) {
        const freshUser = res.data?.user ?? res.data;
        localStorage.setItem("user", JSON.stringify(freshUser));
        localStorage.setItem("session_type", "admin");
        return next();
      }

      return next("/");
    } catch {
      return next("/admin-login");
    }
  }

  next();
});

export default router;
