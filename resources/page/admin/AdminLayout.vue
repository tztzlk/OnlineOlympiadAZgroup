<template>
  <div class="admin-layout">
    <aside class="sidebar" aria-label="Админ-панель">
      <div class="brand">
        <div class="brand-mark" aria-hidden="true">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
        </div>
        <div class="brand-copy">
          <strong>Online Olympiad</strong>
          <span>Операционная панель команды</span>
        </div>
      </div>

      <div class="sidebar-intro">
        <p class="sidebar-eyebrow">Admin</p>
        <p>{{ introText }}</p>
      </div>

      <nav class="nav" aria-label="Разделы админ-панели">
        <router-link
          v-for="section in allowedSections"
          :key="section.key"
          :to="section.to"
          class="nav-link"
          exact-active-class="nav-link-exact-active"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="section.icon"/></svg>
          {{ section.label }}
        </router-link>
      </nav>

      <div class="notification-panel">
        <div class="notification-head">
          <div>
            <p class="sidebar-eyebrow">Events</p>
            <strong>Что требует внимания сейчас</strong>
          </div>
          <span class="notification-badge" :class="{ 'is-zero': !unreadCount }">{{ unreadCount }}</span>
        </div>

        <div v-if="notifications.length" class="notification-list">
          <button
            v-for="item in notifications"
            :key="item.id"
            type="button"
            class="notification-item"
            :class="{ unread: !item.read_at }"
            @click="handleNotification(item)"
          >
            <strong>{{ item.title }}</strong>
            <p>{{ item.body }}</p>
            <span>{{ item.date }}</span>
          </button>
        </div>
        <p v-else class="notification-empty">Новых событий нет. Когда появятся заявки, оплаты или результаты, они будут видны здесь.</p>
      </div>

      <div class="sidebar-footer">
        <div class="support-card">
          <span class="support-label">Роль</span>
          <strong>{{ roleLabel }}</strong>
        </div>
        <div class="footer-actions">
          <router-link to="/" class="back-link">Вернуться на сайт</router-link>
          <button type="button" class="logout-link" @click="logout">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
            Выйти
          </button>
        </div>
      </div>
    </aside>

    <main class="content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../js/api'
import { useUserStore } from '../../stores/user'
import { adminSections, firstAdminRoute, hasAdminCapability } from '../../js/adminAccess'

const router = useRouter()
const userStore = useUserStore()

const notifications = computed(() => userStore.notifications.slice(0, 6))
const unreadCount = computed(() => userStore.notificationsUnread)
const allowedSections = computed(() => adminSections.filter((section) => hasAdminCapability(userStore.user, section.key)))
const roleLabel = computed(() => ({
  admin: 'Полный доступ ко всем разделам',
  operator: 'Оператор: заявки и оплаты',
  content: 'Контент: только конструктор олимпиад',
  analyst: 'Аналитик: только результаты',
}[userStore.user?.admin_role || 'admin'] ?? 'Ограниченный доступ'))

const introText = computed(() => {
  if (hasAdminCapability(userStore.user, 'dashboard')) {
    return 'Проверка заявок, оплат, результатов и обратной связи в одном рабочем пространстве.'
  }

  return 'В боковом меню показаны только те разделы, которые доступны для вашей роли.'
})

const normalizeActionUrl = (value) => {
  if (!value) return firstAdminRoute(userStore.user)

  if (value.startsWith('http')) {
    try {
      return new URL(value).pathname || firstAdminRoute(userStore.user)
    } catch {
      return firstAdminRoute(userStore.user)
    }
  }

  return value
}

const canOpenTarget = (target) => allowedSections.value.some((section) => target === section.to || target.startsWith(`${section.to}/`))

const handleNotification = async (item) => {
  if (!item.read_at) {
    await userStore.markNotificationRead(item.id)
  }

  const target = normalizeActionUrl(item.action_url)
  router.push(canOpenTarget(target) ? target : firstAdminRoute(userStore.user))
}

// Выход отзывает токен администратора на сервере, а не только очищает браузер.
const logout = async () => {
  try {
    await api.post('/auth/logout')
  } catch {}
  userStore.logout()
  router.push('/admin-login')
}

onMounted(async () => {
  await userStore.fetchNotifications(12)
})
</script>

<style src="../../css/admin.css"></style>

<style scoped>
.admin-layout {
  min-height: 100dvh;
  display: grid;
  grid-template-columns: 300px minmax(0, 1fr);
  background: var(--bg);
}

.sidebar {
  position: sticky;
  top: 0;
  height: 100dvh;
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px 16px;
  overflow: hidden;
  background: linear-gradient(180deg, #121a36 0%, #0d1328 100%);
  color: #e8ecf8;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 6px 6px 4px;
}

.brand-mark {
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 13px;
  background: var(--brand);
  color: var(--sun);
  transform: rotate(-6deg);
}

.brand-copy {
  display: grid;
  gap: 2px;
}

.brand-copy strong {
  font-family: var(--font-display);
  font-size: 16px;
  line-height: 1.15;
  color: #ffffff;
}

.brand-copy span,
.sidebar-intro p {
  color: #9aa6c8;
  font-size: 13px;
}

.sidebar-intro {
  display: grid;
  gap: 4px;
  padding: 0 6px;
}

.sidebar-eyebrow {
  color: #ffd25c;
  font-size: 12px;
  font-weight: 600;
}

.nav {
  display: grid;
  gap: 2px;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 44px;
  padding: 10px 12px;
  border-radius: var(--radius-sm);
  color: #b4bfdc;
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
  transition: background-color var(--dur) ease, color var(--dur) ease;
}

.nav-link svg { opacity: 0.8; }

.nav-link:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #ffffff;
}

.nav-link.nav-link-exact-active {
  background: var(--brand);
  color: #ffffff;
  box-shadow: 0 8px 20px rgba(43, 91, 245, 0.35);
}

.nav-link.nav-link-exact-active svg { opacity: 1; }

.notification-panel {
  flex: 1 1 0;
  min-height: 0;
  display: grid;
  align-content: start;
  gap: 10px;
  padding: 14px;
  overflow-y: auto;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.04);
  scrollbar-width: thin;
  scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
}

.notification-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}

.notification-head strong {
  display: block;
  margin-top: 2px;
  color: #ffffff;
  font-size: 14px;
}

.notification-badge {
  min-width: 28px;
  height: 28px;
  padding: 0 8px;
  display: inline-grid;
  place-items: center;
  border-radius: var(--radius-pill);
  background: var(--danger);
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
}

.notification-badge.is-zero { background: rgba(255, 255, 255, 0.1); color: #9aa6c8; }

.notification-list {
  display: grid;
  gap: 8px;
}

.notification-item {
  display: grid;
  gap: 4px;
  width: 100%;
  padding: 10px 12px;
  border: 1px solid transparent;
  border-radius: var(--radius-sm);
  background: rgba(255, 255, 255, 0.04);
  color: #e8ecf8;
  text-align: left;
  cursor: pointer;
  transition: background-color var(--dur) ease;
}

.notification-item:hover { background: rgba(255, 255, 255, 0.08); }

.notification-item.unread {
  border-color: rgba(91, 131, 255, 0.4);
  background: rgba(91, 131, 255, 0.12);
}

.notification-item strong { font-size: 13.5px; }

.notification-item p {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  color: #9aa6c8;
  font-size: 12.5px;
  line-height: 1.45;
}

.notification-item span {
  color: #6f7ba0;
  font-size: 12px;
}

.notification-empty {
  color: #9aa6c8;
  font-size: 13px;
  line-height: 1.5;
}

.sidebar-footer {
  display: grid;
  gap: 10px;
  margin-top: auto;
}

.support-card {
  display: grid;
  gap: 2px;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  background: rgba(255, 255, 255, 0.04);
}

.support-label {
  color: #6f7ba0;
  font-size: 12px;
}

.support-card strong {
  color: #e8ecf8;
  font-size: 13.5px;
  line-height: 1.4;
}

.footer-actions {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 8px;
}

.back-link,
.logout-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-height: 42px;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
}

.back-link {
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
}

.back-link:hover { background: rgba(255, 255, 255, 0.14); }

.logout-link {
  border: 1px solid rgba(255, 107, 112, 0.35);
  background: transparent;
  color: #ff9a9e;
}

.logout-link:hover {
  background: var(--danger);
  border-color: var(--danger);
  color: #ffffff;
}

.content {
  min-width: 0;
}

@media (max-width: 1080px) {
  .admin-layout { grid-template-columns: 1fr; }
  .sidebar { position: static; height: auto; }
  .nav { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  .notification-panel { max-height: 260px; }
}

@media (max-width: 640px) {
  .sidebar { padding: 14px 12px; }
  .nav { grid-template-columns: 1fr 1fr; }
}
</style>
