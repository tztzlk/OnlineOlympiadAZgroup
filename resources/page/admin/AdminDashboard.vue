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
      tone="neutral" loading
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
.stats-grid { grid-template-columns: repeat(auto-fit, minmax(128px, 1fr)); }

.stat-label {
  color: var(--text-secondary);
  font-size: 13.5px;
}

.stat-value {
  order: 2;
  font-family: var(--font-display);
  font-size: 28px;
  line-height: 1.1;
  font-variant-numeric: tabular-nums;
}

.stat-card:nth-child(1) { background: var(--brand-soft); border-color: transparent; }
.stat-card:nth-child(2) { background: var(--sun-soft); border-color: transparent; }
.stat-card:nth-child(5) { background: var(--success-soft); border-color: transparent; }

.panels {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
}

.funnel-grid,
.request-grid {
  display: grid;
  gap: 10px;
}

.funnel-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); }
.request-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }

.request-pill {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.request-pill.neutral { background: var(--brand-soft); color: var(--brand-ink); }

.request-pill span {
  font-size: 13px;
  opacity: 0.85;
}

.request-pill strong {
  font-family: var(--font-display);
  font-size: 24px;
  font-variant-numeric: tabular-nums;
}

.queue-list,
.quick-links {
  display: grid;
  gap: 8px;
}

.queue-item,
.quick-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  background: var(--bg);
  color: var(--text);
  font-weight: 600;
  text-decoration: none;
}

.queue-item p {
  margin-top: 2px;
  color: var(--text-secondary);
  font-size: 13.5px;
  font-weight: 400;
}

.queue-item > span {
  color: var(--text-tertiary);
  font-size: 13px;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

a.quick-link { transition: background-color var(--dur) ease, color var(--dur) ease; }
a.quick-link:hover { background: var(--brand-soft); color: var(--brand-ink); }

.quick-link.compact {
  padding: 7px 12px;
  background: var(--brand-soft);
  color: var(--brand-ink);
  font-size: 14px;
}

.quick-link.static { font-weight: 500; color: var(--text-secondary); }
.quick-link.static strong { color: var(--text); font-variant-numeric: tabular-nums; }

.links-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }

.empty-text {
  padding: 16px;
  border-radius: var(--radius-sm);
  background: var(--bg);
  color: var(--text-secondary);
  font-size: 14.5px;
}

@media (max-width: 1100px) {
  .funnel-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

@media (max-width: 900px) {
  .panels { grid-template-columns: 1fr; }
}

@media (max-width: 560px) {
  .funnel-grid,
  .request-grid { grid-template-columns: 1fr 1fr; }
}
</style>