<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Обзор панели</p>
        <h1>Операционный центр олимпиад</h1>
        <p class="subtext">Здесь видны очереди, воронка заявок, платежи, weekly-динамика и предметы, на которые сейчас приходится основная нагрузка.</p>
      </div>
      <StatusBadge :label="`${stats.notifications.unread} непрочитанных`" tone="warning" />
    </header>

    <StatePanel
      v-if="loading"
      tone="neutral"
      eyebrow="Аналитика"
      title="Загружаем метрики"
      description="Собираем очереди заявок, платежные статусы и weekly summary."
    />

    <template v-else>
      <section class="stats-grid">
        <article class="stat-card"><span class="stat-label">Пользователи</span><strong class="stat-value">{{ stats.users }}</strong></article>
        <article class="stat-card"><span class="stat-label">Дети</span><strong class="stat-value">{{ stats.children }}</strong></article>
        <article class="stat-card"><span class="stat-label">Олимпиады</span><strong class="stat-value">{{ stats.quizzes }}</strong></article>
        <article class="stat-card"><span class="stat-label">Результаты</span><strong class="stat-value">{{ stats.results }}</strong></article>
        <article class="stat-card"><span class="stat-label">Заявки</span><strong class="stat-value">{{ stats.requests.total }}</strong></article>
        <article class="stat-card"><span class="stat-label">Оплаты</span><strong class="stat-value">{{ stats.payments }}</strong></article>
        <article class="stat-card"><span class="stat-label">Обратные звонки</span><strong class="stat-value">{{ stats.callbacks }}</strong></article>
      </section>

      <section class="panels">
        <article class="panel">
          <div class="panel-head">
            <h2>Воронка участия</h2>
          </div>
          <div class="funnel-grid">
            <div class="request-pill neutral"><span>Создано</span><strong>{{ stats.funnel.created }}</strong></div>
            <div class="request-pill approved"><span>Одобрено</span><strong>{{ stats.funnel.approved }}</strong></div>
            <div class="request-pill rejected"><span>Отклонено</span><strong>{{ stats.funnel.rejected }}</strong></div>
            <div class="request-pill approved"><span>Оплачено</span><strong>{{ stats.funnel.paid }}</strong></div>
            <div class="request-pill success"><span>Завершено</span><strong>{{ stats.funnel.completed }}</strong></div>
          </div>
        </article>

        <article class="panel">
          <div class="panel-head">
            <h2>Платёжные статусы</h2>
          </div>
          <div class="request-grid">
            <div class="request-pill pending"><span>Ожидают</span><strong>{{ stats.payment_distribution.pending }}</strong></div>
            <div class="request-pill approved"><span>Подтверждены</span><strong>{{ stats.payment_distribution.paid }}</strong></div>
            <div class="request-pill rejected"><span>С ошибкой</span><strong>{{ stats.payment_distribution.failed }}</strong></div>
          </div>
        </article>
      </section>

      <section class="panels">
        <article class="panel">
          <div class="panel-head">
            <h2>Очередь заявок</h2>
            <RouterLink to="/admin/requests" class="quick-link compact">Открыть всё</RouterLink>
          </div>
          <div v-if="stats.queues.pending_requests.length" class="queue-list">
            <article v-for="item in stats.queues.pending_requests" :key="item.id" class="queue-item">
              <div>
                <strong>{{ item.parent_name }}</strong>
                <p>{{ item.child_name }} · {{ item.subject }}</p>
              </div>
              <span>{{ item.created_at }}</span>
            </article>
          </div>
          <p v-else class="empty-text">Сейчас нет заявок, которые ждут проверки.</p>
        </article>

        <article class="panel">
          <div class="panel-head">
            <h2>Проверка оплат</h2>
            <RouterLink to="/admin/payments" class="quick-link compact">Оплаты</RouterLink>
          </div>
          <div v-if="stats.queues.payment_review.length" class="queue-list">
            <article v-for="item in stats.queues.payment_review" :key="item.id" class="queue-item">
              <div>
                <strong>{{ item.parent_name }}</strong>
                <p>{{ item.child_name }} · {{ item.subject }}</p>
              </div>
              <StatusBadge :label="item.status" :tone="item.status === 'failed' ? 'danger' : 'warning'" />
            </article>
          </div>
          <p v-else class="empty-text">Все платежи обработаны.</p>
        </article>
      </section>

      <section class="panels">
        <article class="panel">
          <div class="panel-head">
            <h2>Популярные предметы</h2>
          </div>
          <div v-if="stats.top_subjects.length" class="queue-list">
            <article v-for="item in stats.top_subjects" :key="item.subject" class="queue-item">
              <div>
                <strong>{{ item.subject }}</strong>
                <p>{{ item.results_count }} результатов</p>
              </div>
              <span>{{ item.average_percent }}%</span>
            </article>
          </div>
          <p v-else class="empty-text">Когда появятся результаты, здесь соберётся предметная аналитика.</p>
        </article>

        <article class="panel">
          <div class="panel-head">
            <h2>Weekly summary</h2>
          </div>
          <div class="quick-links">
            <div class="quick-link static">Новых пользователей: <strong>{{ stats.weekly.users }}</strong></div>
            <div class="quick-link static">Новых заявок: <strong>{{ stats.weekly.requests }}</strong></div>
            <div class="quick-link static">Платежей: <strong>{{ stats.weekly.payments }}</strong></div>
            <div class="quick-link static">Обращений: <strong>{{ stats.weekly.callbacks }}</strong></div>
            <div class="quick-link static">Результатов: <strong>{{ stats.weekly.results }}</strong></div>
          </div>
        </article>
      </section>

      <section class="panel full">
        <div class="panel-head">
          <h2>Быстрые переходы</h2>
        </div>
        <div class="quick-links links-grid">
          <RouterLink to="/admin/quizzes" class="quick-link">Создать олимпиаду</RouterLink>
          <RouterLink to="/admin/requests" class="quick-link">Проверить заявки</RouterLink>
          <RouterLink to="/admin/results" class="quick-link">Посмотреть результаты</RouterLink>
          <RouterLink to="/admin/payments" class="quick-link">Оплаты и импорт</RouterLink>
          <RouterLink to="/admin/callbacks" class="quick-link">Обращения</RouterLink>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../../js/api'
import StatePanel from '../../components/StatePanel.vue'
import StatusBadge from '../../components/StatusBadge.vue'

const loading = ref(true)
const stats = reactive({
  users: 0,
  children: 0,
  quizzes: 0,
  results: 0,
  payments: 0,
  callbacks: 0,
  requests: { total: 0, pending: 0, approved: 0, rejected: 0 },
  funnel: { created: 0, approved: 0, rejected: 0, paid: 0, completed: 0 },
  payment_distribution: { pending: 0, paid: 0, failed: 0 },
  queues: { pending_requests: [], payment_review: [], callbacks: [] },
  top_subjects: [],
  weekly: { users: 0, requests: 0, payments: 0, callbacks: 0, results: 0 },
  notifications: { unread: 0 },
})

const loadDashboard = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/dashboard')
    Object.assign(stats, data)
  } finally {
    loading.value = false
  }
}

onMounted(loadDashboard)
</script>

<style scoped>
* { box-sizing: border-box; }

.admin-page {
  min-height: 100vh;
  background: radial-gradient(circle at top right, rgba(201, 171, 99, 0.12), transparent 24%), linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%);
  padding: 28px;
  color: var(--text);
  display: grid;
  gap: 16px;
}

.header, .panel, .stat-card {
  background: var(--surface);
  border: 1px solid var(--surface-border);
  border-radius: 24px;
  box-shadow: var(--shadow-card);
}

.header {
  padding: 24px;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--accent-strong); font-size: 12px; font-weight: 700; }
h1 { margin: 0; color: var(--text); font-size: clamp(28px, 4vw, 40px); }
.subtext, .empty-text, .queue-item p { margin: 10px 0 0; color: var(--text-secondary); }

.stats-grid, .panels {
  display: grid;
  gap: 16px;
}

.stats-grid { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
.panels { grid-template-columns: 1fr 1fr; }

.stat-card, .panel { padding: 22px; }
.stat-label { display: block; color: var(--text-secondary); font-size: 13px; margin-bottom: 10px; }
.stat-value { font-size: 34px; color: var(--text); }

.panel-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 16px; }
.panel-head h2 { margin: 0; color: var(--text); font-size: 20px; }

.request-grid, .funnel-grid {
  display: grid;
  gap: 12px;
}

.request-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
.funnel-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }

.request-pill {
  border-radius: 18px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  color: #102347;
}

.request-pill span { color: var(--text-secondary); font-size: 13px; }
.request-pill strong { font-size: 28px; }

.pending { background: var(--warning-bg); }
.approved, .success { background: var(--success-bg); }
.rejected { background: var(--danger-bg); }
.neutral { background: rgba(201, 171, 99, 0.16); }

.queue-list, .quick-links {
  display: grid;
  gap: 12px;
}

.queue-item, .quick-link {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
  padding: 14px 16px;
  border-radius: 16px;
  background: rgba(255,252,244,.82);
  color: var(--text);
  text-decoration: none;
  font-weight: 700;
  border: 1px solid var(--surface-border);
}

.queue-item p { margin: 4px 0 0; }
.queue-item span { color: var(--text-secondary); font-size: 13px; }

.quick-link.compact { padding: 10px 14px; }
.quick-link.static { font-weight: 600; }
.links-grid { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
.full { max-width: none; }

@media (max-width: 960px) {
  .panels, .request-grid, .funnel-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 640px) {
  .admin-page { padding: 16px; }
  .header, .panels, .request-grid, .funnel-grid { grid-template-columns: 1fr; }
}
</style>
