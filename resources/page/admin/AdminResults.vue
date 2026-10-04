<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Результаты</p>
        <h1>Результаты участников</h1>
        <p class="subtext">Общий список, фильтры и экспорт с городом, школой и категорией.</p>
      </div>
      <button class="export-btn" @click="downloadExport">Выгрузить Excel</button>
    </header>

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
    </div>

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
.filters {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(160px, 1fr) minmax(160px, 1fr);
  gap: 10px;
  padding: 14px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
}

.results-table td:nth-child(7),
.results-table td:nth-child(8),
.results-table td:nth-child(9) {
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

.results-table td:nth-child(8) { font-weight: 700; color: var(--brand-ink); }
.results-table td:last-child { color: var(--text-tertiary); white-space: nowrap; }

@media (max-width: 760px) {
  .filters { grid-template-columns: 1fr; }
}
</style>
