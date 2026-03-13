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

      <!-- Тема и пользователь -->
      <div class="header__user">
        <button
          type="button"
          class="header__theme-toggle"
          :aria-label="isDark ? 'Светлая тема' : 'Тёмная тема'"
          @click="toggleTheme"
        >
          <svg v-if="isDark" class="theme-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
          </svg>
          <svg v-else class="theme-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
          </svg>
        </button>
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
          <button type="button" class="mobile-theme-toggle" :aria-label="isDark ? 'Светлая тема' : 'Тёмная тема'" @click="toggleTheme(); closeMenu();">
            <svg v-if="isDark" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            <span>{{ isDark ? 'Светлая тема' : 'Тёмная тема' }}</span>
          </button>
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
import { useTheme } from '../js/composables/useTheme'
import api from '../js/api'

const router = useRouter()
const { isDark, toggle: toggleTheme } = useTheme()
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
* { box-sizing: border-box; }

.header {
  width: 100%;
  background: color-mix(in srgb, var(--card) 97%, transparent);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border-bottom: 1px solid var(--border);
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
  position: fixed;
  top: 0;
  z-index: 1000;
  transition: background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease;
}
.dark .header {
  box-shadow: 0 2px 20px rgba(0, 0, 0, 0.2);
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
  font-size: 18px;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
  transition: color 0.4s ease;
}
.header.transparent .header__logo span { color: white; }
.logo-icon {
  width: 36px;
  height: 36px;
  background: var(--accent);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);
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
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  padding: 7px 14px;
  border-radius: 10px;
  transition: all 0.3s;
}
.header__link:hover,
.header__link.router-link-active {
  color: var(--accent);
  background: color-mix(in srgb, var(--accent) 12%, transparent);
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

.header__theme-toggle {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--card);
  color: var(--text);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.2s, border-color 0.2s, color 0.2s;
}
.header__theme-toggle:hover {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}
.theme-icon {
  flex-shrink: 0;
}
.header.transparent .header__theme-toggle {
  border-color: rgba(255,255,255,0.25);
  background: rgba(255,255,255,0.1);
  color: #fff;
}
.header.transparent .header__theme-toggle:hover {
  background: rgba(255,255,255,0.2);
  border-color: rgba(255,255,255,0.4);
}

.header__profile {
  display: flex;
  align-items: center;
  gap: 9px;
  text-decoration: none;
  padding: 6px 14px 6px 6px;
  border-radius: 40px;
  border: 1px solid var(--border);
  background: color-mix(in srgb, var(--text) 4%, transparent);
  transition: all 0.3s;
}
.header__profile:hover {
  border-color: color-mix(in srgb, var(--accent) 40%, transparent);
  background: color-mix(in srgb, var(--accent) 8%, transparent);
}
.header__profile span {
  font-size: 13px;
  font-weight: 700;
  color: var(--text);
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
  background: var(--accent);
  color: #fff;
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
  color: var(--accent);
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  border: 2px solid color-mix(in srgb, var(--accent) 50%, transparent);
  transition: all 0.3s;
  cursor: pointer;
}
.btn-ghost:hover { background: color-mix(in srgb, var(--accent) 12%, transparent); }
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
  background: var(--accent);
  color: #fff;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);
  transition: all 0.2s;
}
.btn-primary:hover {
  background: var(--accent-hover);
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(225, 29, 72, 0.45);
}

.btn-logout {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 12px;
  background: #fff5f5;
  color: #ef4444;
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
  background: linear-gradient(90deg, var(--card) 25%, var(--border) 50%, var(--card) 75%);
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
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  cursor: pointer;
  transition: all 0.3s;
}
.burger:hover { background: rgba(225, 29, 72, 0.12); border-color: rgba(225, 29, 72, 0.3); }
.burger span {
  display: block;
  height: 2px;
  background: #E11D48;
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
  background: rgba(15, 15, 15, 0.6);
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
  background: #1A1A1A;
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 8px 0 40px rgba(0, 0, 0, 0.4);
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
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.mobile-menu__title {
  font-size: 16px;
  font-weight: 700;
  color: #FFFFFF;
  flex: 1;
}
.mobile-close {
  width: 32px; height: 32px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  color: #A1A1AA;
  transition: all 0.2s;
}
.mobile-close:hover { background: rgba(225, 29, 72, 0.15); color: #E11D48; border-color: rgba(225, 29, 72, 0.3); }

.mobile-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 20px;
  background: rgba(255, 255, 255, 0.02);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.mobile-avatar {
  width: 44px; height: 44px;
  border-radius: 50%;
  background: #E11D48;
  color: white;
  font-size: 16px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);
}
.mobile-name { font-size: 15px; font-weight: 700; color: #FFFFFF; }
.mobile-role {
  font-size: 11px;
  font-weight: 600;
  color: #E11D48;
  background: rgba(225, 29, 72, 0.15);
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
  color: #FFFFFF;
  font-size: 15px;
  font-weight: 600;
  padding: 12px 14px;
  border-radius: 14px;
  transition: all 0.2s;
}
.mobile-link svg { color: #A1A1AA; flex-shrink: 0; }
.mobile-link:hover,
.mobile-link.router-link-active {
  background: rgba(225, 29, 72, 0.12);
  color: #E11D48;
}
.mobile-link:hover svg,
.mobile-link.router-link-active svg { color: #E11D48; }

.mobile-footer {
  padding: 16px;
  border-top: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.mobile-theme-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 12px;
  background: color-mix(in srgb, var(--text) 6%, transparent);
  color: var(--text);
  font-size: 14px;
  font-weight: 600;
  border: 1px solid var(--border);
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.2s;
}
.mobile-theme-toggle:hover {
  background: color-mix(in srgb, var(--accent) 12%, transparent);
  color: var(--accent);
  border-color: color-mix(in srgb, var(--accent) 30%, transparent);
}
.mobile-btn-logout {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 12px;
  background: rgba(225, 29, 72, 0.12);
  color: #E11D48;
  font-size: 14px;
  font-weight: 700;
  border: 1px solid rgba(225, 29, 72, 0.3);
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.2s;
}
.mobile-btn-logout:hover { background: rgba(225, 29, 72, 0.2); }

.mobile-btn-ghost {
  display: block;
  text-align: center;
  padding: 12px;
  background: transparent;
  color: #E11D48;
  font-size: 14px;
  font-weight: 700;
  border: 2px solid rgba(225, 29, 72, 0.5);
  border-radius: 14px;
  text-decoration: none;
  transition: all 0.2s;
}
.mobile-btn-ghost:hover { background: rgba(225, 29, 72, 0.12); }

.mobile-btn-primary {
  display: block;
  text-align: center;
  padding: 12px;
  background: #E11D48;
  color: white;
  font-size: 14px;
  font-weight: 700;
  border-radius: 14px;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(225, 29, 72, 0.35);
  transition: all 0.2s;
}
.mobile-btn-primary:hover { background: #BE123C; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(225, 29, 72, 0.45); }

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