<template>
  <header class="header" :class="{ 'is-scrolled': isScrolled, 'is-home': isHome }">
    <div class="header__bar">

      <!-- Логотип -->
      <router-link to="/" class="logo" aria-label="Eurika — на главную">
        <span class="logo__mark" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/>
          </svg>
        </span>
        <span class="logo__text">Eurika!</span>
      </router-link>

      <!-- Навигация -->
      <nav class="nav" aria-label="Основная навигация">
        <router-link to="/" class="nav__link" exact-active-class="is-active">Главная</router-link>
        <router-link to="/subject" class="nav__link" active-class="is-active">Предметы</router-link>
        <router-link to="/rules" class="nav__link" active-class="is-active">Правила</router-link>
      </nav>

      <!-- Тема и пользователь -->
      <div class="actions">
        <button
          type="button"
          class="icon-btn"
          :aria-label="isDark ? 'Светлая тема' : 'Тёмная тема'"
          :title="isDark ? 'Светлая тема' : 'Тёмная тема'"
          @click="toggleTheme"
        >
          <svg v-if="isDark" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <circle cx="12" cy="12" r="4.5"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
          </svg>
          <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>
          </svg>
        </button>

        <div v-if="loading" class="skeleton-user ds-skeleton" aria-hidden="true"></div>

        <template v-else>
          <template v-if="userStore.isAuthenticated">
            <router-link to="/profile" class="profile-pill">
              <span class="avatar" aria-hidden="true">{{ avatarLetter }}</span>
              <span class="profile-pill__name">{{ user?.name || 'Профиль' }}</span>
            </router-link>
            <button type="button" class="logout-btn" @click="logout">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/>
              </svg>
              Выйти
            </button>
          </template>
          <template v-else>
            <router-link to="/login" class="ds-btn ds-btn-ghost ds-btn-sm">Войти</router-link>
            <router-link to="/register" class="ds-btn ds-btn-primary ds-btn-sm">Регистрация</router-link>
          </template>
        </template>
      </div>

      <!-- Бургер -->
      <button
        type="button"
        class="burger"
        :class="{ 'is-open': menuOpen }"
        :aria-expanded="menuOpen ? 'true' : 'false'"
        aria-controls="mobile-menu"
        aria-label="Меню"
        @click="toggleMenu"
      >
        <span></span><span></span><span></span>
      </button>
    </div>

    <!-- Затемнение -->
    <transition name="fade">
      <div v-if="menuOpen" class="overlay" aria-hidden="true" @click="closeMenu"></div>
    </transition>

    <!-- Мобильное меню -->
    <transition name="drawer">
      <aside v-if="menuOpen" id="mobile-menu" class="drawer" aria-label="Меню" @keydown.esc="closeMenu">
        <div class="drawer__head">
          <span class="logo__mark logo__mark--sm" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/>
            </svg>
          </span>
          <span class="drawer__title">Eurika!</span>
          <button ref="closeButton" type="button" class="icon-btn icon-btn--sm" aria-label="Закрыть меню" @click="closeMenu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <div v-if="userStore.isAuthenticated && user" class="drawer__profile">
          <span class="avatar avatar--lg" aria-hidden="true">{{ avatarLetter }}</span>
          <div class="drawer__profile-text">
            <div class="drawer__name">{{ user.name }}</div>
            <div class="drawer__role">Участник олимпиады</div>
          </div>
        </div>

        <nav class="drawer__nav" aria-label="Мобильная навигация">
          <router-link @click="closeMenu" to="/" class="drawer__link" exact-active-class="is-active">
            <span class="drawer__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10l9-7 9 7v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
            </span>
            Главная
          </router-link>
          <router-link @click="closeMenu" to="/subject" class="drawer__link" active-class="is-active">
            <span class="drawer__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/></svg>
            </span>
            Предметы
          </router-link>
          <router-link @click="closeMenu" to="/rules" class="drawer__link" active-class="is-active">
            <span class="drawer__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            </span>
            Правила
          </router-link>
          <router-link @click="closeMenu" to="/results" class="drawer__link" active-class="is-active">
            <span class="drawer__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
            </span>
            Результаты
          </router-link>
          <router-link v-if="userStore.isAuthenticated" @click="closeMenu" to="/profile" class="drawer__link" active-class="is-active">
            <span class="drawer__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            Профиль
          </router-link>
        </nav>

        <div class="drawer__foot">
          <button type="button" class="drawer__theme" :aria-label="isDark ? 'Светлая тема' : 'Тёмная тема'" @click="toggleTheme(); closeMenu();">
            <svg v-if="isDark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4.5"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
            <span>{{ isDark ? 'Светлая тема' : 'Тёмная тема' }}</span>
          </button>
          <button v-if="userStore.isAuthenticated" type="button" @click="logout" class="ds-btn ds-btn-danger ds-btn-block">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/>
            </svg>
            Выйти из аккаунта
          </button>
          <template v-else>
            <router-link @click="closeMenu" to="/login" class="ds-btn ds-btn-ghost ds-btn-block">Войти</router-link>
            <router-link @click="closeMenu" to="/register" class="ds-btn ds-btn-primary ds-btn-block">Регистрация</router-link>
          </template>
        </div>
      </aside>
    </transition>

    <router-link v-if="showStickyOlympiadCta" to="/subject" class="sticky-cta">
      Выбрать олимпиаду
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </router-link>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useUserStore } from '../stores/user'
import { useTheme } from '../js/composables/useTheme'
import api from '../js/api'

const router = useRouter()
const { isDark, toggle: toggleTheme } = useTheme()
const menuOpen = ref(false)
const loading = ref(true)
const closeButton = ref(null)
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
    // Отзываем токен на сервере, чтобы он перестал работать сразу после выхода.
    await api.post('/auth/logout')
  } catch {}
  userStore.logout()
  router.push('/')
  closeMenu()
}

const isScrolled = ref(false)
const route = useRoute()
const isHome = computed(() => route.path === '/')
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
  isScrolled.value = window.scrollY > 12
}

const handleKeydown = (event) => {
  if (event.key === 'Escape' && menuOpen.value) closeMenu()
}

watch(menuOpen, async (value) => {
  document.body.style.overflow = value ? 'hidden' : ''
  if (value) {
    await nextTick()
    closeButton.value?.focus()
  }
})

watch(() => route.fullPath, () => {
  closeMenu()
})

onMounted(async () => {
  window.addEventListener('scroll', handleScroll, { passive: true })
  window.addEventListener('keydown', handleKeydown)
  handleScroll()
  await userStore.fetchUser()
  loading.value = false
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})
</script>

<style scoped>
.header {
  position: fixed;
  inset: 0 0 auto;
  z-index: var(--z-header);
  padding: 10px 16px 0;
  pointer-events: none;
}

.header__bar {
  pointer-events: auto;
  max-width: 1200px;
  height: 60px;
  margin: 0 auto;
  padding: 0 10px 0 14px;
  display: flex;
  align-items: center;
  gap: 20px;
  border-radius: var(--radius-lg);
  background: color-mix(in srgb, var(--card) 88%, transparent);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  backdrop-filter: saturate(160%) blur(16px);
  -webkit-backdrop-filter: saturate(160%) blur(16px);
  transition: box-shadow var(--dur-slow) ease, background-color var(--dur-slow) ease;
}

.header.is-scrolled .header__bar {
  background: color-mix(in srgb, var(--card) 96%, transparent);
  box-shadow: var(--shadow-md);
}

/* ---- Логотип ---- */
.logo {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: var(--text);
  flex-shrink: 0;
  border-radius: var(--radius-sm);
}

.logo__mark {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  background: var(--brand);
  color: var(--sun);
  box-shadow: var(--shadow-brand);
  transform: rotate(-6deg);
  transition: transform var(--dur-slow) var(--ease-out);
}

.logo__mark--sm {
  width: 32px;
  height: 32px;
  border-radius: 10px;
}

@media (hover: hover) and (pointer: fine) {
  .logo:hover .logo__mark { transform: rotate(6deg) scale(1.04); }
}

.logo__text {
  font-family: var(--font-display);
  font-size: 18px;
  font-weight: 700;
  letter-spacing: -0.02em;
  white-space: nowrap;
}

/* ---- Навигация ---- */
.nav {
  display: flex;
  align-items: center;
  gap: 2px;
  margin-right: auto;
}

.nav__link {
  position: relative;
  padding: 9px 14px;
  border-radius: var(--radius-sm);
  color: var(--text-secondary);
  font-size: 15px;
  font-weight: 500;
  text-decoration: none;
  transition: color var(--dur) ease, background-color var(--dur) ease;
}

.nav__link:hover {
  color: var(--text);
  background: var(--surface-soft);
}

.nav__link.is-active {
  color: var(--brand-ink);
  background: var(--brand-soft);
  font-weight: 600;
}

/* ---- Действия ---- */
.actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.icon-btn {
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--card);
  color: var(--text-secondary);
}

.icon-btn:hover {
  color: var(--brand-ink);
  border-color: color-mix(in srgb, var(--brand) 35%, var(--border));
  background: var(--brand-softer);
}

.icon-btn--sm {
  width: 38px;
  height: 38px;
  margin-left: auto;
}

.profile-pill {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  max-width: 220px;
  padding: 4px 14px 4px 4px;
  border-radius: var(--radius-pill);
  border: 1px solid var(--border);
  background: var(--card);
  text-decoration: none;
  color: var(--text);
  transition: border-color var(--dur) ease, background-color var(--dur) ease;
}

.profile-pill:hover {
  border-color: color-mix(in srgb, var(--brand) 35%, var(--border));
  background: var(--brand-softer);
}

.profile-pill__name {
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.avatar {
  width: 34px;
  height: 34px;
  border-radius: 11px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  background: var(--sun);
  color: #1f1600;
  font-size: 14px;
  font-weight: 700;
}

.avatar--lg {
  width: 48px;
  height: 48px;
  border-radius: 15px;
  font-size: 18px;
}

.logout-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  height: 42px;
  padding: 0 14px;
  border-radius: var(--radius-sm);
  border: 1px solid transparent;
  background: transparent;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 600;
}

.logout-btn:hover {
  color: var(--danger-ink);
  background: var(--danger-soft);
}

.skeleton-user {
  width: 150px;
  height: 42px;
  border-radius: var(--radius-pill);
}

/* ---- Бургер ---- */
.burger {
  display: none;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
  width: 44px;
  height: 44px;
  padding: 0 12px;
  margin-left: auto;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--card);
}

.burger span {
  display: block;
  height: 2px;
  border-radius: 2px;
  background: var(--text);
  transition: transform var(--dur-slow) var(--ease-out), opacity var(--dur) ease;
}

.burger.is-open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
.burger.is-open span:nth-child(2) { opacity: 0; transform: scaleX(0); }
.burger.is-open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

/* ---- Мобильное меню ---- */
.overlay {
  position: fixed;
  inset: 0;
  z-index: var(--z-overlay);
  background: var(--overlay);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  pointer-events: auto;
}

.drawer {
  position: fixed;
  top: 0;
  right: 0;
  z-index: var(--z-drawer);
  width: min(340px, 88vw);
  height: 100dvh;
  display: flex;
  flex-direction: column;
  background: var(--card);
  border-left: 1px solid var(--border);
  box-shadow: var(--shadow-lg);
  overflow-y: auto;
  pointer-events: auto;
  overscroll-behavior: contain;
}

.drawer__head {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 16px 16px 20px;
}

.drawer__title {
  font-family: var(--font-display);
  font-size: 17px;
  font-weight: 700;
}

.drawer__profile {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 4px 16px 8px;
  padding: 14px;
  border-radius: var(--radius-md);
  background: var(--brand-softer);
}

.drawer__profile-text { min-width: 0; }

.drawer__name {
  font-size: 16px;
  font-weight: 700;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.drawer__role {
  display: inline-block;
  margin-top: 4px;
  padding: 2px 8px;
  border-radius: var(--radius-xs);
  background: var(--brand-soft);
  color: var(--brand-ink);
  font-size: 12px;
  font-weight: 600;
}

.drawer__nav {
  display: grid;
  gap: 4px;
  padding: 8px 12px;
  flex: 1;
  align-content: start;
}

.drawer__link {
  display: flex;
  align-items: center;
  gap: 14px;
  min-height: 52px;
  padding: 8px 12px;
  border-radius: var(--radius-md);
  color: var(--text);
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  transition: background-color var(--dur) ease, color var(--dur) ease;
}

.drawer__icon {
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  border-radius: 11px;
  background: var(--bg-alt);
  color: var(--text-secondary);
  transition: background-color var(--dur) ease, color var(--dur) ease;
}

.drawer__link:hover { background: var(--surface-soft); }

.drawer__link.is-active {
  background: var(--brand-soft);
  color: var(--brand-ink);
}

.drawer__link.is-active .drawer__icon {
  background: var(--brand);
  color: var(--on-brand);
}

.drawer__foot {
  display: grid;
  gap: 10px;
  padding: 16px 16px max(16px, env(safe-area-inset-bottom));
  border-top: 1px solid var(--border);
}

.drawer__theme {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 48px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--bg-alt);
  color: var(--text);
  font-size: 15px;
  font-weight: 600;
}

.drawer__theme:hover { border-color: var(--brand); color: var(--brand-ink); }

/* ---- Плавающая кнопка на мобильных ---- */
.sticky-cta {
  position: fixed;
  left: 16px;
  right: 16px;
  bottom: max(16px, env(safe-area-inset-bottom));
  z-index: var(--z-sticky);
  min-height: 54px;
  display: none;
  align-items: center;
  justify-content: center;
  gap: 8px;
  border-radius: var(--radius-md);
  background: var(--brand);
  color: var(--on-brand);
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  box-shadow: var(--shadow-brand), var(--shadow-md);
  pointer-events: auto;
  transition: transform var(--dur) var(--ease-out), background-color var(--dur) ease;
}

.sticky-cta:active { transform: scale(0.98); }

/* ---- Переходы ---- */
.drawer-enter-active,
.drawer-leave-active { transition: transform var(--dur-slow) var(--ease-out); }
.drawer-enter-from,
.drawer-leave-to { transform: translateX(100%); }

.fade-enter-active,
.fade-leave-active { transition: opacity var(--dur-slow) ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

/* ---- Адаптив ---- */
@media (max-width: 900px) {
  .nav__link { padding: 9px 10px; }
  .profile-pill__name { display: none; }
  .profile-pill { padding: 4px; }
}

@media (max-width: 767px) {
  .header { padding: 8px 10px 0; }
  .header__bar { height: 56px; padding: 0 6px 0 10px; border-radius: var(--radius-md); }
  .nav,
  .actions { display: none; }
  .burger { display: flex; }
  .sticky-cta { display: inline-flex; }
  .logo__mark { width: 34px; height: 34px; }
  .logo__text { font-size: 17px; }
}

@media (min-width: 768px) {
  .burger,
  .drawer,
  .overlay,
  .sticky-cta { display: none !important; }
}
</style>
