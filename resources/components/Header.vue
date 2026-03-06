<template>
  <header class="header" :class="{ scrolled: isScrolled, transparent: isTransparent }">
    <div class="header__container">

      <!-- Логотип -->
      <router-link to="/" class="header__logo">
        <div class="logo-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
        </div>
        <span>Онлайн-олимпиада</span>
      </router-link>

      <!-- Навигация -->
      <nav class="header__nav">
        <router-link to="/" class="header__link">Главная</router-link>
        <router-link to="/subject" class="header__link">Предметы</router-link>
        <router-link to="/rules" class="header__link">Правила</router-link>
      </nav>

      <!-- Пользователь -->
      <div class="header__user">
        <template v-if="loading">
          <div class="skeleton-user"></div>
        </template>
        <template v-else>
          <router-link v-if="userStore.isAuthenticated" to="/profile" class="header__profile">
            <div class="header__avatar">{{ avatarLetter }}</div>
            <span>{{ user?.name || 'Профиль' }}</span>
          </router-link>
          <button v-if="userStore.isAuthenticated" class="btn-logout" @click="logout">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Выйти
          </button>
          <template v-else>
            <router-link to="/login" class="btn-ghost">Войти</router-link>
            <router-link to="/register" class="btn-primary">Регистрация</router-link>
          </template>
        </template>
      </div>

      <!-- Бургер -->
      <button class="burger" @click="toggleMenu" :class="{ active: menuOpen }" aria-label="Меню">
        <span></span><span></span><span></span>
      </button>
    </div>

    <!-- Overlay -->
    <transition name="fade">
      <div v-if="menuOpen" class="overlay" @click="closeMenu"></div>
    </transition>

    <!-- Мобильное меню -->
    <transition name="slide">
      <div v-if="menuOpen" class="mobile-menu">
        <div class="mobile-menu__header">
          <div class="logo-icon small">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
          </div>
          <span class="mobile-menu__title">Олимпиада</span>
          <button class="mobile-close" @click="closeMenu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div v-if="userStore.isAuthenticated && user" class="mobile-profile">
          <div class="mobile-avatar">{{ avatarLetter }}</div>
          <div>
            <div class="mobile-name">{{ user.name }}</div>
            <div class="mobile-role">Участник олимпиады</div>
          </div>
        </div>

        <nav class="mobile-nav">
          <router-link @click="closeMenu" to="/" class="mobile-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Главная
          </router-link>
          <router-link @click="closeMenu" to="/subject" class="mobile-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            Предметы
          </router-link>
          <router-link @click="closeMenu" to="/rules" class="mobile-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Правила
          </router-link>
          <router-link @click="closeMenu" to="/results" class="mobile-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            Результаты
          </router-link>
          <router-link v-if="userStore.isAuthenticated" @click="closeMenu" to="/profile" class="mobile-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Профиль
          </router-link>
        </nav>

        <div class="mobile-footer">
          <button v-if="userStore.isAuthenticated" @click="logout" class="mobile-btn-logout">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Выйти из аккаунта
          </button>
          <template v-else>
            <router-link @click="closeMenu" to="/login" class="mobile-btn-ghost">Войти</router-link>
            <router-link @click="closeMenu" to="/register" class="mobile-btn-primary">Регистрация</router-link>
          </template>
        </div>
      </div>
    </transition>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'

const router = useRouter()
const menuOpen = ref(false)
const loading = ref(true)
const userStore = useUserStore()

const user = computed(() => userStore.user)

const avatarLetter = computed(() => {
  const name = user.value?.name || ''
  return name.charAt(0).toUpperCase() || '?'
})

const toggleMenu = () => menuOpen.value = !menuOpen.value
const closeMenu = () => menuOpen.value = false

const logout = async () => {
  try {
    await api.post('/logout', {}, {
      headers: { Authorization: `Bearer ${userStore.token}` }
    })
  } catch {}
  userStore.logout()
  router.push('/')
  closeMenu()
}

const isScrolled = ref(false)
const route = useRoute()
const isHome = computed(() => route.path === '/')
const isTransparent = computed(() => isHome.value && !isScrolled.value)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20
}

onMounted(async () => {
  await userStore.fetchUser()
  loading.value = false
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; }

.header {
  width: 100%;
  background: rgba(255, 255, 255, 0.97);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border-bottom: 1px solid rgba(99, 102, 241, 0.1);
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
  position: fixed;
  top: 0;
  z-index: 1000;
  font-family: 'Manrope', sans-serif;
  transition: background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
}

/* Only transparent on homepage before scroll */
.header.transparent {
  background: transparent;
  border-bottom: 1px solid transparent;
  box-shadow: none;
  backdrop-filter: none;
  -webkit-backdrop-filter: none;
}

.header__container {
  max-width: 1200px;
  height: 68px;
  margin: 0 auto;
  padding: 0 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

/* Logo */
.header__logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  flex-shrink: 0;
}
.header__logo span {
  font-family: 'Playfair Display', serif;
  font-size: 18px;
  font-weight: 700;
  color: #1e1b4b;
  white-space: nowrap;
  transition: color 0.4s ease;
}
.header.transparent .header__logo span { color: white; }
.logo-icon {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.logo-icon.small {
  width: 28px; height: 28px;
  border-radius: 8px;
}

/* Nav */
.header__nav {
  display: flex;
  align-items: center;
  gap: 4px;
}
.header__link {
  color: #64748b;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  padding: 7px 14px;
  border-radius: 10px;
  transition: all 0.3s;
}
.header__link:hover,
.header__link.router-link-active {
  color: #4f46e5;
  background: #eef2ff;
}
.header.transparent .header__link { color: rgba(255,255,255,0.85); }
.header.transparent .header__link:hover,
.header.transparent .header__link.router-link-active {
  color: white;
  background: rgba(255,255,255,0.15);
}

/* User area */
.header__user {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.header__profile {
  display: flex;
  align-items: center;
  gap: 9px;
  text-decoration: none;
  padding: 6px 14px 6px 6px;
  border-radius: 40px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  transition: all 0.3s;
}
.header__profile:hover {
  border-color: #c7d2fe;
  background: #eef2ff;
}
.header__profile span {
  font-size: 13px;
  font-weight: 700;
  color: #374151;
  transition: color 0.3s;
}
.header.transparent .header__profile {
  border-color: rgba(255,255,255,0.3);
  background: rgba(255,255,255,0.12);
  backdrop-filter: blur(8px);
}
.header.transparent .header__profile:hover {
  background: rgba(255,255,255,0.22);
  border-color: rgba(255,255,255,0.5);
}
.header.transparent .header__profile span { color: white; }

.header__avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.btn-ghost {
  padding: 8px 16px;
  border-radius: 12px;
  background: transparent;
  color: #4f46e5;
  font-family: 'Manrope', sans-serif;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  border: 2px solid #c7d2fe;
  transition: all 0.3s;
  cursor: pointer;
}
.btn-ghost:hover { background: #eef2ff; }
.header.transparent .btn-ghost {
  background: rgba(255,255,255,0.12);
  color: white;
  border: 1px solid rgba(255,255,255,0.3);
  backdrop-filter: blur(6px);
}
.header.transparent .btn-ghost:hover { background: rgba(255,255,255,0.22); }

.btn-primary {
  padding: 8px 18px;
  border-radius: 12px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  transition: all 0.2s;
}
.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(99, 102, 241, 0.4);
}

.btn-logout {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 12px;
  background: #fff5f5;
  color: #ef4444;
  font-family: 'Manrope', sans-serif;
  font-size: 13px;
  font-weight: 700;
  border: 1px solid #fecaca;
  cursor: pointer;
  transition: all 0.3s;
}
.btn-logout:hover { background: #fee2e2; }
.header.transparent .btn-logout {
  background: rgba(255,80,80,0.15);
  color: rgba(255,200,200,1);
  border-color: rgba(255,100,100,0.3);
  backdrop-filter: blur(6px);
}
.header.transparent .btn-logout:hover { background: rgba(255,80,80,0.25); }

/* Skeleton */
.skeleton-user {
  width: 120px;
  height: 36px;
  border-radius: 40px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: skeleton-shimmer 1.4s infinite;
}
@keyframes skeleton-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Burger */
.burger {
  display: none;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
  width: 40px;
  height: 40px;
  padding: 8px;
  border-radius: 10px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.3s;
}
.burger:hover { background: #eef2ff; border-color: #c7d2fe; }
.burger span {
  display: block;
  height: 2px;
  background: #4f46e5;
  border-radius: 2px;
  transition: all 0.3s;
  transform-origin: center;
}
.header.transparent .burger {
  background: rgba(255,255,255,0.12);
  border-color: rgba(255,255,255,0.25);
  backdrop-filter: blur(6px);
}
.header.transparent .burger:hover { background: rgba(255,255,255,0.22); }
.header.transparent .burger span { background: white; }
.burger.active span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.burger.active span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.burger.active span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* Overlay */
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  backdrop-filter: blur(4px);
  z-index: 1500;
}

/* Mobile menu */
.mobile-menu {
  position: fixed;
  top: 0;
  left: 0;
  width: 300px;
  height: 100vh;
  background: white;
  box-shadow: 8px 0 40px rgba(0, 0, 0, 0.12);
  display: flex;
  flex-direction: column;
  z-index: 2000;
  overflow-y: auto;
}

.mobile-menu__header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 20px 18px;
  border-bottom: 1px solid #f1f5f9;
}
.mobile-menu__title {
  font-family: 'Playfair Display', serif;
  font-size: 16px;
  font-weight: 700;
  color: #1e1b4b;
  flex: 1;
}
.mobile-close {
  width: 32px; height: 32px;
  border-radius: 8px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}
.mobile-close:hover { background: #fee2e2; color: #ef4444; border-color: #fecaca; }

.mobile-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 20px;
  background: #fafbff;
  border-bottom: 1px solid #f1f5f9;
}
.mobile-avatar {
  width: 44px; height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  font-size: 16px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
}
.mobile-name { font-size: 15px; font-weight: 700; color: #1e1b4b; }
.mobile-role {
  font-size: 11px;
  font-weight: 600;
  color: #6366f1;
  background: #eef2ff;
  padding: 2px 8px;
  border-radius: 10px;
  display: inline-block;
  margin-top: 4px;
}

.mobile-nav {
  display: flex;
  flex-direction: column;
  padding: 12px;
  gap: 4px;
  flex: 1;
}
.mobile-link {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: #374151;
  font-size: 15px;
  font-weight: 600;
  padding: 12px 14px;
  border-radius: 14px;
  transition: all 0.2s;
}
.mobile-link svg { color: #94a3b8; flex-shrink: 0; }
.mobile-link:hover,
.mobile-link.router-link-active {
  background: #eef2ff;
  color: #4f46e5;
}
.mobile-link:hover svg,
.mobile-link.router-link-active svg { color: #4f46e5; }

.mobile-footer {
  padding: 16px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.mobile-btn-logout {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 12px;
  background: #fff5f5;
  color: #ef4444;
  font-family: 'Manrope', sans-serif;
  font-size: 14px;
  font-weight: 700;
  border: 1px solid #fecaca;
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.2s;
}
.mobile-btn-logout:hover { background: #fee2e2; }

.mobile-btn-ghost {
  display: block;
  text-align: center;
  padding: 12px;
  background: transparent;
  color: #4f46e5;
  font-size: 14px;
  font-weight: 700;
  border: 2px solid #c7d2fe;
  border-radius: 14px;
  text-decoration: none;
  transition: all 0.2s;
}
.mobile-btn-ghost:hover { background: #eef2ff; }

.mobile-btn-primary {
  display: block;
  text-align: center;
  padding: 12px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  font-size: 14px;
  font-weight: 700;
  border-radius: 14px;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  transition: all 0.2s;
}
.mobile-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4); }

/* Transitions */
.slide-enter-active, .slide-leave-active { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Responsive */
@media (max-width: 900px) {
  .header__nav, .header__user { display: none; }
  .burger { display: flex; }
}
</style>