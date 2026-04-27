<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Results</p>
        <h1>Результаты участников</h1>
        <p class="subtext">Общий список, фильтры и экспорт с городом, школой и категорией.</p>
      </div>

      <div class="filters">
        <input v-model="search" type="text" placeholder="Поиск по участнику, школе, городу, предмету или категории" />
        <select v-model="statusFilter">
          <option value="all">Все статусы</option>
          <option value="passed">Пройден</option>
          <option value="failed">Не пройден</option>
        </select>
        <select v-model="subjectFilter">
          <option value="all">Все предметы</option>
          <option v-for="subject in subjects" :key="subject" :value="subject">{{ subject }}</option>
        </select>
        <button class="export-btn" @click="downloadExport">Выгрузить Excel</button>
      </div>
    </header>

    <div v-if="loading" class="loading-card">Загружаем результаты...</div>
    <div v-else-if="!results.length" class="empty-card">По выбранным фильтрам результатов нет.</div>

    <div v-else class="table-card">
      <table class="results-table">
        <thead>
          <tr>
            <th>Участник</th>
            <th>Школа</th>
            <th>Город</th>
            <th>Предмет</th>
            <th>Категория</th>
            <th>Олимпиада</th>
            <th>Балл</th>
            <th>Процент</th>
            <th>Время</th>
            <th>Проверка</th>
            <th>Статус</th>
            <th>Дата</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="result in results" :key="result.id">
            <td>{{ result.child_name }}</td>
            <td>{{ result.school }}</td>
            <td>{{ result.city }}</td>
            <td>{{ result.subject }}</td>
            <td>{{ result.category }}</td>
            <td>{{ result.quiz_title }}</td>
            <td>{{ result.score }}/{{ result.total }}</td>
            <td>{{ result.percent }}%</td>
            <td>{{ formatElapsed(result.elapsed_seconds) }}</td>
            <td>
              <span class="review-chip" :class="{ flagged: result.requires_review }">
                {{ result.requires_review ? 'Нужна проверка' : 'OK' }}
              </span>
            </td>
            <td>
              <span class="status-chip" :class="result.status">
                {{ result.status === 'passed' ? 'Пройден' : 'Не пройден' }}
              </span>
            </td>
            <td>{{ result.date }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import api from '../../js/api'

const loading = ref(true)
const results = ref([])
const search = ref('')
const statusFilter = ref('all')
const subjectFilter = ref('all')

const subjects = computed(() => [...new Set(results.value.map((item) => item.subject).filter(Boolean))].sort())

const params = computed(() => ({
  search: search.value,
  status: statusFilter.value,
  subject: subjectFilter.value,
}))

const formatElapsed = (seconds) => {
  if (seconds === null || seconds === undefined || Number.isNaN(Number(seconds))) return 'n/a'
  const totalSeconds = Math.max(0, Number(seconds))
  const minutes = Math.floor(totalSeconds / 60)
  const remainder = totalSeconds % 60
  return `${String(minutes).padStart(2, '0')}:${String(remainder).padStart(2, '0')}`
}

const loadResults = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/users-results', { params: params.value })
    results.value = data
  } finally {
    loading.value = false
  }
}

const debounce = (() => {
  let timeoutId = null
  return (fn) => {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(fn, 250)
  }
})()

watch([search, statusFilter, subjectFilter], () => {
  debounce(loadResults)
})

const downloadExport = async () => {
  const { data } = await api.get('/admin/users-results/export', {
    params: params.value,
    responseType: 'blob',
  })

  const blob = new Blob([data], { type: 'application/vnd.ms-excel' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = 'results.xls'
  link.click()
  window.URL.revokeObjectURL(url)
}

onMounted(loadResults)
</script>

<style scoped>
* { box-sizing: border-box; }

.admin-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(201, 171, 99, 0.12), transparent 22%),
    linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%);
  padding: 28px;
  color: var(--text);
  display: grid;
  gap: 18px;
  align-content: start;
}

.header {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  align-items: flex-start;
  background: var(--surface);
  border: 1px solid var(--surface-border);
  border-radius: var(--radius-lg);
  padding: 22px;
  box-shadow: var(--shadow-card);
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--accent-strong);
  font-size: 12px;
  font-weight: 700;
}

h1 {
  margin: 0;
  font-size: clamp(26px, 4vw, 38px);
  color: var(--text);
}

.subtext {
  margin-top: 8px;
  color: var(--text-secondary);
}

.filters {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: flex-end;
  align-items: flex-start;
}

input,
select {
  border: 1px solid var(--surface-border);
  border-radius: var(--radius-sm);
  padding: 12px 15px;
  background: rgba(255, 252, 245, 0.95);
  color: var(--text);
  min-width: 200px;
  outline: none;
  font: inherit;
}

input::placeholder {
  color: color-mix(in srgb, var(--text-secondary) 78%, white 22%);
}

input:focus,
select:focus {
  border-color: color-mix(in srgb, var(--accent) 75%, white 25%);
  box-shadow: 0 0 0 4px rgba(201, 171, 99, 0.16);
}

.export-btn {
  flex-shrink: 0;
  cursor: pointer;
  font-weight: 700;
  font: inherit;
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  color: var(--text);
  border: none;
  border-radius: var(--radius-sm);
  padding: 12px 16px;
  box-shadow: 0 12px 26px rgba(201, 171, 99, 0.2);
}

.loading-card,
.empty-card,
.table-card {
  background: var(--surface);
  border: 1px solid var(--surface-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-card);
}

.loading-card,
.empty-card {
  padding: 28px;
  color: var(--text-secondary);
}

.table-card {
  overflow: auto;
}

.results-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1280px;
}

.results-table th,
.results-table td {
  padding: 16px 18px;
  border-bottom: 1px solid var(--surface-border);
  text-align: left;
  vertical-align: middle;
}

.results-table th {
  color: var(--text-secondary);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-weight: 700;
}

.results-table tbody tr:last-child td {
  border-bottom: 0;
}

.results-table tbody tr:hover td {
  background: rgba(201, 171, 99, 0.04);
}

.status-chip {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.status-chip.passed { background: var(--success-bg); color: #2f6f4b; }
.status-chip.failed { background: var(--danger-bg); color: #8f3b3b; }

.review-chip {
  display: inline-flex;
  align-items: center;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  background: rgba(44, 122, 75, 0.12);
  color: #2f6f4b;
}

.review-chip.flagged {
  background: rgba(201, 171, 99, 0.22);
  color: #7b5d15;
}

@media (max-width: 860px) {
  .admin-page { padding: 16px; }
  .header { flex-direction: column; }
  .filters,
  .filters input,
  .filters select,
  .export-btn { width: 100%; }
}
</style>
