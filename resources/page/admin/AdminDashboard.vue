<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Admin Overview</p>
        <h1>Панель управления олимпиадами</h1>
        <p class="subtext">Реальная статистика по пользователям, заявкам, олимпиадам и результатам.</p>
      </div>
    </header>

    <div v-if="loading" class="loading-card">Загружаем метрики...</div>

    <template v-else>
      <section class="stats-grid">
        <article class="stat-card">
          <span class="stat-label">Пользователи</span>
          <strong class="stat-value">{{ stats.users }}</strong>
        </article>
        <article class="stat-card">
          <span class="stat-label">Олимпиады</span>
          <strong class="stat-value">{{ stats.quizzes }}</strong>
        </article>
        <article class="stat-card">
          <span class="stat-label">Результаты</span>
          <strong class="stat-value">{{ stats.results }}</strong>
        </article>
        <article class="stat-card">
          <span class="stat-label">Заявки</span>
          <strong class="stat-value">{{ stats.requests.total }}</strong>
        </article>
      </section>

      <section class="panels">
        <article class="panel">
          <div class="panel-head">
            <h2>Статусы заявок</h2>
          </div>
          <div class="request-grid">
            <div class="request-pill pending">
              <span>Ожидают</span>
              <strong>{{ stats.requests.pending }}</strong>
            </div>
            <div class="request-pill approved">
              <span>Одобрены</span>
              <strong>{{ stats.requests.approved }}</strong>
            </div>
            <div class="request-pill rejected">
              <span>Отклонены</span>
              <strong>{{ stats.requests.rejected }}</strong>
            </div>
          </div>
        </article>

        <article class="panel">
          <div class="panel-head">
            <h2>Быстрые переходы</h2>
          </div>
          <div class="quick-links">
            <RouterLink to="/admin/quizzes" class="quick-link">Создать олимпиаду</RouterLink>
            <RouterLink to="/admin/requests" class="quick-link">Проверить заявки</RouterLink>
            <RouterLink to="/admin/results" class="quick-link">Посмотреть результаты</RouterLink>
          </div>
        </article>
      </section>
    </template>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '../../js/api'

const loading = ref(true)
const stats = reactive({
  users: 0,
  quizzes: 0,
  results: 0,
  requests: {
    total: 0,
    pending: 0,
    approved: 0,
    rejected: 0,
  },
})

const loadDashboard = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/dashboard')
    stats.users = data.users ?? 0
    stats.quizzes = data.quizzes ?? 0
    stats.results = data.results ?? 0
    stats.requests = {
      total: data.requests?.total ?? 0,
      pending: data.requests?.pending ?? 0,
      approved: data.requests?.approved ?? 0,
      rejected: data.requests?.rejected ?? 0,
    }
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
  background:
    radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 24%),
    linear-gradient(180deg, #f4f7fb 0%, #e9eef7 100%);
  padding: 28px;
  color: #102347;
}

.header {
  margin-bottom: 20px;
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #2563eb;
  font-size: 12px;
  font-weight: 700;
}

h1 {
  margin: 0;
  color: #102347;
  font-size: clamp(28px, 4vw, 40px);
}

.subtext {
  margin: 10px 0 0;
  color: #55708f;
}

.loading-card,
.panel,
.stat-card {
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 24px;
  box-shadow: 0 18px 48px rgba(15, 35, 85, 0.08);
}

.loading-card {
  padding: 28px;
  color: #55708f;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}

.stat-card {
  padding: 22px;
}

.stat-label {
  display: block;
  color: #55708f;
  font-size: 13px;
  margin-bottom: 10px;
}

.stat-value {
  font-size: 34px;
  color: #102347;
}

.panels {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 16px;
  margin-top: 16px;
}

.panel {
  padding: 22px;
}

.panel-head h2 {
  margin: 0 0 16px;
  color: #102347;
  font-size: 20px;
}

.request-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.request-pill {
  border-radius: 18px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  color: #102347;
}

.request-pill span {
  color: #55708f;
  font-size: 13px;
}

.request-pill strong {
  font-size: 28px;
}

.pending { background: #fff7db; }
.approved { background: #e8f8ed; }
.rejected { background: #ffe7e7; }

.quick-links {
  display: grid;
  gap: 12px;
}

.quick-link {
  display: block;
  text-decoration: none;
  padding: 14px 16px;
  border-radius: 16px;
  background: #f4f8ff;
  color: #173a72;
  font-weight: 700;
  border: 1px solid #dbe7f7;
}

@media (max-width: 900px) {
  .stats-grid,
  .request-grid,
  .panels {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 640px) {
  .admin-page {
    padding: 16px;
  }

  .stats-grid,
  .request-grid,
  .panels {
    grid-template-columns: 1fr;
  }

  .stat-value {
    font-size: 28px;
  }
}
</style>
