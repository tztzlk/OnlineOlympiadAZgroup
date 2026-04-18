import { createRouter, createWebHistory } from "vue-router";
import Home from "../page/Home.vue";
import Subject from "../page/Subject.vue";
import api from "../js/api";
import AdminLayout from "../page/admin/AdminLayout.vue";
import { firstAdminRoute, hasAdminAccess, hasAdminCapability } from "../js/adminAccess";

const routes = [
  { path: "/", name: "Home", component: Home },
  { path: "/subject", name: "Subject", component: Subject },
  { path: "/subjects/:subjectId", name: "SubjectDetail", component: () => import("../page/SubjectDetail.vue") },
  { path: "/about", name: "About", component: () => import("../page/About.vue") },
  { path: "/rules", name: "Rules", component: () => import("../page/Rules.vue") },
  { path: "/leaderboard", name: "Leaderboard", component: () => import("../page/Leaderboard.vue") },
  { path: "/certificate-check", name: "CertificateCheck", component: () => import("../page/CertificateCheck.vue") },
  { path: "/results", name: "Results", component: () => import("../page/Results.vue") },
  { path: "/profile/results/:resultId/mistakes", name: "ResultMistakes", component: () => import("../page/ResultMistakes.vue") },
  { path: "/profile/results/:resultId/certificate-preview", name: "CertificatePreview", component: () => import("../page/CertificatePreview.vue") },
  { path: "/login", name: "Login", component: () => import("../page/Login.vue") },
  { path: "/register", name: "Register", component: () => import("../page/Register.vue") },
  { path: "/forgot-password", name: "ForgotPassword", component: () => import("../page/ForgotPassword.vue") },
  { path: "/reset-password", name: "ResetPassword", component: () => import("../page/ResetPassword.vue") },
  { path: "/help-desk", name: "HelpDesk", component: () => import("../page/HelpDesk.vue") },
  {
    path: "/profile",
    component: () => import("../page/profile/ProfileLayout.vue"),
    children: [
      { path: "", name: "ProfileOverview", component: () => import("../page/profile/ProfileOverview.vue") },
      { path: "children", name: "ProfileChildren", component: () => import("../page/profile/ProfileChildren.vue") },
      { path: "olympiads", name: "ProfileOlympiads", component: () => import("../page/profile/ProfileOlympiads.vue") },
      { path: "payments", name: "ProfilePayments", component: () => import("../page/profile/ProfilePayments.vue") },
      { path: "training", name: "ProfileTraining", component: () => import("../page/profile/ProfileTraining.vue") },
      { path: "notifications", name: "ProfileNotifications", component: () => import("../page/profile/ProfileNotifications.vue") },
    ],
  },
  { path: "/request-success", name: "RequestSuccess", component: () => import("../page/RequestSuccess.vue") },
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
        meta: { requiresAdmin: true, adminCapability: "dashboard" },
        component: () => import("../page/admin/AdminDashboard.vue"),
      },
      {
        path: "requests",
        name: "AdminRequests",
        meta: { requiresAdmin: true, adminCapability: "requests" },
        component: () => import("../page/admin/AdminRequests.vue"),
      },
      {
        path: "quizzes",
        name: "AdminQuizzes",
        meta: { requiresAdmin: true, adminCapability: "quizzes" },
        component: () => import("../page/admin/AdminQuizzes.vue"),
      },
      {
        path: "results",
        name: "AdminResults",
        meta: { requiresAdmin: true, adminCapability: "results" },
        component: () => import("../page/admin/AdminResults.vue"),
      },
      {
        path: "payments",
        name: "AdminPayments",
        meta: { requiresAdmin: true, adminCapability: "payments" },
        component: () => import("../page/admin/AdminPayments.vue"),
      },
      {
        path: "callbacks",
        name: "AdminCallbacks",
        meta: { requiresAdmin: true, adminCapability: "callbacks" },
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
  const requiredCapability = to.matched.find((record) => record.meta.adminCapability)?.meta.adminCapability;

  if (to.path === "/admin-login" && token && sessionType === "admin") {
    try {
      const parsedUser = storedUser ? JSON.parse(storedUser) : null;
      return next(firstAdminRoute(parsedUser));
    } catch {
      return next({ name: "Home" });
    }
  }

  if (requiresAdmin) {
    if (!token) return next("/admin-login");

    try {
      const parsedUser = storedUser ? JSON.parse(storedUser) : null;

      if (hasAdminAccess(parsedUser) && sessionType === "admin") {
        if (!requiredCapability || hasAdminCapability(parsedUser, requiredCapability)) {
          return next();
        }

        return next(firstAdminRoute(parsedUser));
      }
    } catch {}

    try {
      const res = await api.get("/profile", {
        headers: { Authorization: `Bearer ${token}` },
      });

      const freshUser = res.data?.user ?? res.data;

      if (hasAdminAccess(freshUser)) {
        localStorage.setItem("user", JSON.stringify(freshUser));
        localStorage.setItem("session_type", "admin");

        if (!requiredCapability || hasAdminCapability(freshUser, requiredCapability)) {
          return next();
        }

        return next(firstAdminRoute(freshUser));
      }

      return next("/");
    } catch {
      return next("/admin-login");
    }
  }

  next();
});

export default router;
