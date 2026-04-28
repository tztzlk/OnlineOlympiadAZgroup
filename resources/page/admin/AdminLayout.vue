<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-mark">OO</div>
        <div class="brand-copy">
          <strong>Online Olympiad</strong>
          <span>Операционная панель команды</span>
        </div>
      </div>

      <div class="sidebar-intro">
        <p class="sidebar-eyebrow">Admin</p>
        <p>{{ introText }}</p>
      </div>

      <nav class="nav">
        <router-link
          v-for="section in allowedSections"
          :key="section.key"
          :to="section.to"
          class="nav-link"
          exact-active-class="nav-link-exact-active"
        >
          {{ section.label }}
        </router-link>
      </nav>

      <div class="notification-panel">
        <div class="notification-head">
          <div>
            <p class="sidebar-eyebrow">Events</p>
            <strong>Что требует внимания сейчас</strong>
          </div>
          <span class="notification-badge">{{ unreadCount }}</span>
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
        <router-link to="/" class="back-link">Вернуться на сайт</router-link>
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

onMounted(async () => {
  await userStore.fetchNotifications(12)
})
</script>

<style scoped>
* { box-sizing: border-box; }

.admin-layout {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 328px minmax(0, 1fr);
  background: linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%);
}

.sidebar {
  position: sticky;
  top: 0;
  height: 100vh;
  padding: 22px 18px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  overflow: hidden;
  background: linear-gradient(180deg, rgba(255, 249, 238, 0.98) 0%, rgba(245, 236, 212, 0.96) 100%);
  border-right: 1px solid var(--surface-border);
  box-shadow: inset -1px 0 0 rgba(109, 89, 42, 0.04);
}

.brand,
.support-card,
.notification-panel {
  border-radius: var(--radius-md);
  border: 1px solid rgba(201, 171, 99, 0.18);
  background: rgba(255, 252, 244, 0.82);
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px;
}

.brand-mark {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  font-size: 18px;
  font-weight: 800;
  color: var(--text);
  background: linear-gradient(135deg, var(--accent) 0%, #e1c06f 100%);
  box-shadow: 0 12px 24px rgba(201, 171, 99, 0.22);
}

.brand-copy {
  display: grid;
  gap: 4px;
}

.brand-copy strong {
  font-size: 22px;
  line-height: 1.05;
  color: var(--text);
}

.brand-copy span,
.sidebar-intro p,
.support-label,
.notification-item p,
.notification-item span,
.notification-empty {
  color: var(--text-secondary);
}

.sidebar-intro {
  display: grid;
  gap: 8px;
  padding: 6px 4px 2px;
}

.sidebar-eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

.sidebar-intro p {
  margin: 0;
}

.nav {
  display: grid;
  gap: 8px;
}

.nav-link,
.back-link {
  display: flex;
  align-items: center;
  min-height: 48px;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  text-decoration: none;
  color: var(--text-secondary);
  font-weight: 700;
}

.nav-link:hover,
.back-link:hover {
  background: rgba(201, 171, 99, 0.1);
  color: var(--text);
}

.nav-link.nav-link-exact-active {
  background: linear-gradient(135deg, rgba(201, 171, 99, 0.22) 0%, rgba(201, 171, 99, 0.12) 100%);
  color: var(--text);
  box-shadow: inset 0 0 0 1px rgba(201, 171, 99, 0.18);
}

.notification-panel {
  padding: 16px;
  display: grid;
  gap: 12px;
  flex: 1 1 0;
  min-height: 0;
  overflow-y: auto;
}

.notification-head {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: flex-start;
}

.notification-head strong,
.support-card strong,
.notification-item strong {
  color: var(--text);
}

.notification-badge {
  min-width: 34px;
  min-height: 34px;
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(79, 167, 116, 0.14);
  color: #316a49;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
}

.notification-list {
  display: grid;
  gap: 10px;
}

.notification-item {
  width: 100%;
  text-align: left;
  border: 1px solid var(--surface-border);
  background: rgba(255, 255, 255, 0.46);
  border-radius: 16px;
  padding: 14px;
  cursor: pointer;
  display: grid;
  gap: 6px;
}

.notification-item.unread {
  box-shadow: 0 0 0 3px rgba(201, 171, 99, 0.1);
  border-color: rgba(201, 171, 99, 0.3);
}

.sidebar-footer {
  margin-top: auto;
  display: grid;
  gap: 12px;
}

.support-card {
  padding: 16px;
  display: grid;
  gap: 8px;
}

.support-label {
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.1em;
}

.support-card strong {
  line-height: 1.45;
}

.back-link {
  justify-content: center;
  background: rgba(79, 167, 116, 0.08);
  color: #316a49;
}

.content {
  min-width: 0;
}

@media (max-width: 1080px) {
  .admin-layout {
    grid-template-columns: 1fr;
  }

  .sidebar {
    position: static;
    height: auto;
  }
}

@media (max-width: 640px) {
  .sidebar {
    padding: 16px 14px;
  }

  .brand-copy strong {
    font-size: 20px;
  }

  .nav {
    grid-template-columns: 1fr 1fr;
  }
}
</style>
