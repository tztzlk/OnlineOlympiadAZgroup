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
        <p>Проверка заявок, оплаты, результатов и обратной связи в одном рабочем пространстве.</p>
      </div>

      <nav class="nav">
        <router-link to="/admin" class="nav-link" exact-active-class="nav-link-exact-active">Панель</router-link>
        <router-link to="/admin/requests" class="nav-link" exact-active-class="nav-link-exact-active">Заявки</router-link>
        <router-link to="/admin/quizzes" class="nav-link" exact-active-class="nav-link-exact-active">Олимпиады</router-link>
        <router-link to="/admin/results" class="nav-link" exact-active-class="nav-link-exact-active">Результаты</router-link>
        <router-link to="/admin/payments" class="nav-link" exact-active-class="nav-link-exact-active">Оплаты</router-link>
        <router-link to="/admin/callbacks" class="nav-link" exact-active-class="nav-link-exact-active">Обращения</router-link>
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
        <p v-else class="notification-empty">Новых событий нет. Когда появятся заявки, оплаты или обращения, они будут видны здесь.</p>
      </div>

      <div class="sidebar-footer">
        <div class="support-card">
          <span class="support-label">Приоритет</span>
          <strong>Сначала проверяйте новые заявки и оплаты, затем выдавайте доступ к олимпиадам и закрывайте обращения.</strong>
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

const router = useRouter()
const userStore = useUserStore()

const notifications = computed(() => userStore.notifications.slice(0, 6))
const unreadCount = computed(() => userStore.notificationsUnread)

const normalizeActionUrl = (value) => {
  if (!value) return '/admin'

  if (value.startsWith('http')) {
    try {
      return new URL(value).pathname || '/admin'
    } catch {
      return '/admin'
    }
  }

  return value
}

const handleNotification = async (item) => {
  if (!item.read_at) {
    await userStore.markNotificationRead(item.id)
  }

  router.push(normalizeActionUrl(item.action_url))
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
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
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
