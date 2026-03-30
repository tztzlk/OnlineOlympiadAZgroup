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
            <th>Статус</th>
            <th>Дата</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="result in results" :key="result.id">
            <td>{{ result.user_name }}</td>
            <td>{{ result.school }}</td>
            <td>{{ result.city }}</td>
            <td>{{ result.subject }}</td>
            <td>{{ result.category }}</td>
            <td>{{ result.quiz_title }}</td>
            <td>{{ result.score }}/{{ result.total }}</td>
            <td>{{ result.percent }}%</td>
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
.admin-page { min-height: 100vh; background: radial-gradient(circle at top right, rgba(34,197,94,0.08), transparent 22%), linear-gradient(180deg, #f4f7fb 0%, #e9eef7 100%); padding: 28px; color: #102347; }
.header { display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; margin-bottom: 20px; }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; color: #2563eb; font-size: 12px; font-weight: 700; }
.subtext { margin-top: 8px; color: #55708f; }
h1 { margin: 0; }
.filters { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
input, select, .export-btn { border: 1px solid #cad8ea; border-radius: 16px; padding: 13px 15px; background: rgba(255,255,255,0.94); color: #102347; min-width: 220px; outline: none; }
.export-btn { min-width: auto; cursor: pointer; font-weight: 700; background: linear-gradient(90deg, #2563eb, #1d4ed8); color: #fff; border: none; }
input::placeholder { color: #92a3ba; }
input:focus, select:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.12); }
.loading-card, .empty-card, .table-card { background: rgba(255,255,255,0.94); border: 1px solid rgba(148,163,184,0.2); border-radius: 24px; box-shadow: 0 18px 48px rgba(15,35,85,0.08); }
.loading-card, .empty-card { padding: 28px; }
.table-card { overflow: auto; }
.results-table { width: 100%; border-collapse: collapse; min-width: 1120px; }
.results-table th, .results-table td { padding: 16px 18px; border-bottom: 1px solid #e2e8f0; text-align: left; vertical-align: middle; }
.results-table th { color: #55708f; font-size: 12px; text-transform: uppercase; letter-spacing: 0.04em; }
.status-chip { display: inline-flex; align-items: center; height: fit-content; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.status-chip.passed { background: #e8f8ed; color: #1f7a34; }
.status-chip.failed { background: #ffe7e7; color: #9f1d1d; }
@media (max-width: 860px) { .admin-page { padding: 16px; } .header { flex-direction: column; } .filters, .filters input, .filters select, .export-btn { width: 100%; } }
</style>
