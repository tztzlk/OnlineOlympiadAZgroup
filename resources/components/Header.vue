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
        <span>Eurica!</span>
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
          <span class="mobile-menu__title">Eurica!</span>
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

    <router-link v-if="showStickyOlympiadCta" to="/subject" class="sticky-cta">
      Выбрать олимпиаду
    </router-link>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
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
const showStickyOlympiadCta = computed(() => {
  if (route.path === '/subject') return false
  if (route.path === '/register') return false
  if (route.path === '/login') return false
  if (route.path === '/admin-login') return false
  if (route.path.startsWith('/admin')) return false
  if (route.path.startsWith('/quiz/')) return false
  return true
})

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20
}

watch(menuOpen, (value) => {
  document.body.style.overflow = value ? 'hidden' : ''
})

watch(() => route.fullPath, () => {
  closeMenu()
})

onMounted(async () => {
  await userStore.fetchUser()
  loading.value = false
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  document.body.style.overflow = ''
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

/* Transparent on homepage before scroll — light hero, so keep dark text */
.header.transparent {
  background: rgba(255, 255, 255, 0.72);
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  box-shadow: none;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}
:global(.dark) .header.transparent {
  background: rgba(17, 24, 39, 0.8);
  border-bottom-color: rgba(255, 255, 255, 0.06);
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
/* transparent header inherits normal dark text — no override needed */
.logo-icon {
  width: 36px;
  height: 36px;
  background: var(--green);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  flex-shrink: 0;
  box-shadow: 0 6px 16px rgba(22, 163, 74, 0.28);
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
  transition: color 0.25s ease, background 0.25s ease;
}
.header__link:hover,
.header__link.router-link-active {
  color: var(--accent);
  background: color-mix(in srgb, var(--accent) 12%, transparent);
}
/* nav links use normal theme colors on transparent header */

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
/* theme toggle: same as scrolled state on light background */

.header__profile {
  display: flex;
  align-items: center;
  gap: 9px;
  text-decoration: none;
  padding: 6px 14px 6px 6px;
  border-radius: 40px;
  border: 1px solid var(--border);
  background: color-mix(in srgb, var(--text) 4%, transparent);
  transition: border-color 0.25s ease, background 0.25s ease;
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
/* profile pill: same as scrolled state on light background */

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
  transition: background 0.25s ease;
  cursor: pointer;
}
.btn-ghost:hover { background: color-mix(in srgb, var(--accent) 12%, transparent); }
/* btn-ghost: same on transparent — inherits normal styles */
.btn-ghost:active { background: color-mix(in srgb, var(--accent) 18%, transparent); }
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
  background: var(--green);
  color: #ffffff;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  border: none;
  cursor: pointer;
  box-shadow: 0 6px 16px rgba(22, 163, 74, 0.28);
  transition: all 0.2s;
}
.btn-primary:hover {
  background: var(--green-hover);
  transform: translateY(-1px);
  box-shadow: 0 10px 22px rgba(22, 163, 74, 0.34);
}

@media (hover: hover) and (pointer: fine) {
  .btn-primary:hover {
    background: var(--green-hover);
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(22, 163, 74, 0.34);
  }
}

.btn-primary:active {
  transform: scale(0.96);
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.22);
  transition-duration: 0.1s;
}

.btn-logout {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 12px;
  background: rgba(73, 168, 107, 0.12);
  color: #2f7f4b;
  font-size: 13px;
  font-weight: 700;
  border: 1px solid rgba(73, 168, 107, 0.26);
  cursor: pointer;
  transition: background 0.25s ease;
}
.btn-logout:hover { background: rgba(73, 168, 107, 0.18); }
/* btn-logout: inherits same green style on light background */
.btn-logout:active { background: rgba(73, 168, 107, 0.24); }
:global(.dark) .btn-logout { color: var(--green-strong); }
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
  background: transparent;
  border: 1px solid var(--border);
  cursor: pointer;
  transition: background 0.2s ease, border-color 0.2s ease;
}
.burger:hover { background: var(--accent-soft); border-color: rgba(245, 200, 66, 0.4); }
.burger span {
  display: block;
  height: 2px;
  background: var(--text);
  border-radius: 2px;
  transition: transform 0.3s cubic-bezier(0.23, 1, 0.32, 1), opacity 0.3s ease;
  transform-origin: center;
}
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
  background: var(--surface);
  border-right: 1px solid var(--surface-border);
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
  border-bottom: 1px solid var(--surface-border);
}
.mobile-menu__title {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-on-surface);
  flex: 1;
}
.mobile-close {
  width: 32px; height: 32px;
  border-radius: 8px;
  background: var(--surface-soft);
  border: 1px solid var(--surface-border);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  color: var(--text-muted-on-surface);
  transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}
.mobile-close:hover { background: rgba(208, 179, 107, 0.14); color: var(--accent); border-color: rgba(208, 179, 107, 0.28); }

.mobile-profile {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 18px 20px;
  background: var(--surface-soft);
  border-bottom: 1px solid var(--surface-border);
}
.mobile-avatar {
  width: 44px; height: 44px;
  border-radius: 50%;
  background: var(--success-soft);
  color: white;
  font-size: 16px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 8px 18px rgba(73, 168, 107, 0.22);
}
.mobile-name { font-size: 15px; font-weight: 700; color: var(--text-on-surface); }
.mobile-role {
  font-size: 11px;
  font-weight: 600;
  color: #22663b;
  background: rgba(73, 168, 107, 0.16);
  padding: 2px 8px;
  border-radius: 10px;
  display: inline-block;
  margin-top: 4px;
}
:global(.dark) .mobile-role { color: var(--green-strong); }

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
  color: var(--text-on-surface);
  font-size: 15px;
  font-weight: 600;
  padding: 12px 14px;
  border-radius: 14px;
  transition: background 0.2s ease, color 0.2s ease;
}
.mobile-link svg { color: var(--text-muted-on-surface); flex-shrink: 0; }
.mobile-link:hover,
.mobile-link.router-link-active {
  background: rgba(208, 179, 107, 0.14);
  color: var(--accent);
}
.mobile-link:hover svg,
.mobile-link.router-link-active svg { color: var(--accent); }

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
  transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
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
  background: rgba(73, 168, 107, 0.12);
  color: #2f7f4b;
  font-size: 14px;
  font-weight: 700;
  border: 1px solid rgba(73, 168, 107, 0.26);
  border-radius: 14px;
  cursor: pointer;
  transition: background 0.2s ease;
}
.mobile-btn-logout:hover { background: rgba(73, 168, 107, 0.18); }
.mobile-btn-logout:active { background: rgba(73, 168, 107, 0.26); }
:global(.dark) .mobile-btn-logout { color: var(--green-strong); }

.mobile-btn-ghost {
  display: block;
  text-align: center;
  padding: 12px;
  background: transparent;
  color: var(--accent);
  font-size: 14px;
  font-weight: 700;
  border: 2px solid rgba(208, 179, 107, 0.45);
  border-radius: 14px;
  text-decoration: none;
  transition: background 0.2s ease;
}
.mobile-btn-ghost:hover { background: rgba(208, 179, 107, 0.14); }
.mobile-btn-ghost:active { background: rgba(208, 179, 107, 0.22); }

.mobile-btn-primary {
  display: block;
  text-align: center;
  padding: 12px;
  background: var(--green);
  color: #ffffff;
  font-size: 14px;
  font-weight: 700;
  border-radius: 14px;
  text-decoration: none;
  box-shadow: 0 8px 18px rgba(22, 163, 74, 0.28);
  transition: all 0.2s;
}
.mobile-btn-primary:hover { background: var(--green-hover); transform: translateY(-1px); box-shadow: 0 12px 24px rgba(22, 163, 74, 0.34); }
.mobile-btn-primary:active { transform: scale(0.97); box-shadow: 0 4px 12px rgba(22, 163, 74, 0.22); transition-duration: 0.1s; }

/* Transitions */
.slide-enter-active, .slide-leave-active { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }

.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Responsive */
@media (max-width: 767px) {
  .header__nav, .header__user { display: none; }
  .burger { display: flex; }
  .sticky-cta { display: inline-flex; }
}

.sticky-cta {
  position: fixed;
  left: 16px;
  right: 16px;
  bottom: max(16px, env(safe-area-inset-bottom));
  min-height: 52px;
  display: none;
  align-items: center;
  justify-content: center;
  border-radius: 16px;
  background: var(--green);
  color: #ffffff;
  font-size: 15px;
  font-weight: 800;
  text-decoration: none;
  box-shadow: 0 12px 28px rgba(22, 163, 74, 0.32);
  z-index: 1400;
}

.sticky-cta:hover {
  transform: translateY(-1px);
}

@media (min-width: 768px) {
  .burger,
  .mobile-menu,
  .overlay,
  .sticky-cta {
    display: none !important;
  }
}

@media (max-width: 767px) {
  .header__container {
    height: 64px;
    padding: 0 16px;
  }

  .header__logo span {
    font-size: 16px;
  }

  .mobile-menu {
    width: min(320px, 86vw);
  }
}
</style>
