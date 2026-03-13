<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Results</p>
        <h1>Результаты участников</h1>
      </div>
      <div class="filters">
        <input v-model="search" type="text" placeholder="Поиск по ученику или олимпиаде" />
        <select v-model="statusFilter">
          <option value="all">Все статусы</option>
          <option value="passed">Пройден</option>
          <option value="failed">Не пройден</option>
        </select>
      </div>
    </header>

    <div v-if="loading" class="loading-card">Загружаем результаты...</div>

    <div v-else-if="!filteredResults.length" class="empty-card">Результатов пока нет.</div>

    <div v-else class="result-list">
      <article v-for="result in filteredResults" :key="result.id" class="result-card">
        <div class="result-head">
          <div>
            <h2>{{ result.user_name }}</h2>
            <p>{{ result.subject }} · {{ result.quiz_title }}</p>
          </div>
          <span class="status-chip" :class="result.status">
            {{ result.status === 'passed' ? 'Пройден' : 'Не пройден' }}
          </span>
        </div>

        <div class="score-row">
          <strong>{{ result.score }}/{{ result.total }}</strong>
          <span>{{ result.percent }}%</span>
        </div>

        <div class="progress-track">
          <div class="progress-fill" :class="result.status" :style="{ width: `${result.percent}%` }"></div>
        </div>

        <div class="meta-row">
          <span>{{ result.date }}</span>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../js/api'

const loading = ref(true)
const results = ref([])
const search = ref('')
const statusFilter = ref('all')

const loadResults = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/users-results')
    results.value = data
  } finally {
    loading.value = false
  }
}

const filteredResults = computed(() => {
  const query = search.value.trim().toLowerCase()

  return results.value.filter((result) => {
    const statusMatch = statusFilter.value === 'all' || result.status === statusFilter.value
    const queryMatch =
      !query ||
      result.user_name?.toLowerCase().includes(query) ||
      result.quiz_title?.toLowerCase().includes(query) ||
      result.subject?.toLowerCase().includes(query)

    return statusMatch && queryMatch
  })
})

onMounted(loadResults)
</script>

<style scoped>
* { box-sizing: border-box; }

.admin-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(34, 197, 94, 0.08), transparent 22%),
    linear-gradient(180deg, #f4f7fb 0%, #e9eef7 100%);
  padding: 28px;
  color: #102347;
}

.header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
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

h1,
h2 {
  margin: 0;
  color: #102347;
}

.filters {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

input,
select {
  border: 1px solid #cad8ea;
  border-radius: 16px;
  padding: 13px 15px;
  background: rgba(255, 255, 255, 0.94);
  color: #102347;
  min-width: 220px;
  outline: none;
}

input::placeholder {
  color: #92a3ba;
}

input:focus,
select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.loading-card,
.empty-card,
.result-card {
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 24px;
  box-shadow: 0 18px 48px rgba(15, 35, 85, 0.08);
}

.loading-card,
.empty-card {
  padding: 28px;
}

.result-list {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.result-card {
  padding: 22px;
}

.result-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.result-head p,
.meta-row {
  color: #55708f;
}

.status-chip {
  display: inline-flex;
  align-items: center;
  height: fit-content;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.status-chip.passed { background: #e8f8ed; color: #1f7a34; }
.status-chip.failed { background: #ffe7e7; color: #9f1d1d; }

.score-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin: 18px 0 10px;
}

.score-row strong {
  color: #102347;
  font-size: 28px;
}

.score-row span {
  color: #55708f;
  font-weight: 700;
}

.progress-track {
  height: 10px;
  border-radius: 999px;
  overflow: hidden;
  background: #edf4ff;
}

.progress-fill {
  height: 100%;
}

.progress-fill.passed { background: linear-gradient(90deg, #34d399, #1f7a34); }
.progress-fill.failed { background: linear-gradient(90deg, #fb7185, #be123c); }

.meta-row {
  margin-top: 14px;
  font-size: 14px;
}

@media (max-width: 640px) {
  .admin-page {
    padding: 16px;
  }

  .header {
    flex-direction: column;
  }

  .filters,
  .filters input,
  .filters select {
    width: 100%;
  }

  .result-head {
    flex-direction: column;
  }
}
</style>
