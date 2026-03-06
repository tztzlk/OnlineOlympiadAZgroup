<template>
  <div class="admin-page">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar__left">
        <h1 class="page-title">Результаты</h1>
        <span class="page-subtitle">Всего: {{ filteredResults.length }}</span>
      </div>
      <div class="topbar__right">
        <div class="search-box">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <circle cx="6" cy="6" r="4.5" stroke="#93B4D8" stroke-width="1.5"/>
            <path d="M9.5 9.5L12 12" stroke="#93B4D8" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <input v-model="search" type="text" placeholder="Поиск по имени или тесту..." />
        </div>
        <div class="filter-tabs">
          <button
            v-for="f in scoreFilters"
            :key="f.value"
            class="filter-tab"
            :class="{ 'filter-tab--active': activeFilter === f.value }"
            @click="activeFilter = f.value"
          >{{ f.label }}</button>
        </div>
      </div>
    </header>

    <!-- Summary cards -->
    <div class="summary-row">
      <div class="summary-card summary-card--blue">
        <span class="summary-card__label">Средний балл</span>
        <span class="summary-card__value">{{ avgScore }}%</span>
      </div>
      <div class="summary-card summary-card--green">
        <span class="summary-card__label">Выше 80%</span>
        <span class="summary-card__value">{{ highCount }}</span>
      </div>
      <div class="summary-card summary-card--yellow">
        <span class="summary-card__label">50–80%</span>
        <span class="summary-card__value">{{ midCount }}</span>
      </div>
      <div class="summary-card summary-card--red">
        <span class="summary-card__label">Ниже 50%</span>
        <span class="summary-card__value">{{ lowCount }}</span>
      </div>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="table-wrap">
      <div v-for="i in 5" :key="i" class="skeleton-row" :style="{ animationDelay: i * 0.07 + 's' }"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!filteredResults.length" class="empty">
      <div class="empty__icon">
        <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
          <path d="M5 28V20M11 28V12M17 28V16M23 28V8M29 28V14" stroke="#BFDBFE" stroke-width="2.2" stroke-linecap="round"/>
        </svg>
      </div>
      <p class="empty__text">Результатов не найдено</p>
    </div>

    <!-- Table -->
    <div v-else class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th class="th-sortable" @click="sortBy('user_name')">
              Пользователь
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                :class="{ 'sort-active': sortKey === 'user_name', 'sort-desc': sortKey === 'user_name' && sortDesc }">
                <path d="M5 2v6M2.5 5.5L5 8l2.5-2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </th>
            <th class="th-sortable" @click="sortBy('quiz_title')">
              Тест
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                :class="{ 'sort-active': sortKey === 'quiz_title', 'sort-desc': sortKey === 'quiz_title' && sortDesc }">
                <path d="M5 2v6M2.5 5.5L5 8l2.5-2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </th>
            <th class="th-sortable" @click="sortBy('score')">
              Баллы
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                :class="{ 'sort-active': sortKey === 'score', 'sort-desc': sortKey === 'score' && sortDesc }">
                <path d="M5 2v6M2.5 5.5L5 8l2.5-2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </th>
            <th>Прогресс</th>
            <th>Дата</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(result, i) in filteredResults"
            :key="result.id"
            class="table-row"
            :style="{ animationDelay: i * 0.04 + 's' }"
          >
            <td class="td-name">
              <div class="user-cell">
                <div class="user-avatar" :style="{ background: avatarColor(result.user_name) }">
                  {{ result.user_name?.charAt(0).toUpperCase() }}
                </div>
                <span>{{ result.user_name }}</span>
              </div>
            </td>
            <td class="td-quiz">
              <div class="quiz-cell">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <rect x="1.5" y="1.5" width="11" height="11" rx="2" stroke="#93B4D8" stroke-width="1.3"/>
                  <path d="M4 7h6M4 9.5h4M4 4.5h6" stroke="#93B4D8" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
                {{ result.quiz_title }}
              </div>
            </td>
            <td>
              <span class="score-badge" :class="scoreTier(result.score)">
                {{ result.score }}%
              </span>
            </td>
            <td class="td-progress">
              <div class="progress-bar">
                <div
                  class="progress-bar__fill"
                  :class="'progress-bar__fill--' + scoreTier(result.score)"
                  :style="{ width: result.score + '%' }"
                ></div>
              </div>
            </td>
            <td class="td-date">{{ result.date ?? formatDate(result.id) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import api from "../../js/api"

const results = ref([])
const loading = ref(true)

const search = ref("")
const searchFocused = ref(false)

const activeFilter = ref("all")
const sortKey = ref("score")
const sortDesc = ref(true)

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const scoreFilters = [
  { value: "all", label: "Все" },
  { value: "high", label: "Высокие" },
  { value: "mid", label: "Средние" },
  { value: "low", label: "Низкие" }
]

const scoreTier = (s) => {
  if (s >= 80) return "high"
  if (s >= 50) return "mid"
  return "low"
}

/*
|--------------------------------------------------------------------------
| UI helpers
|--------------------------------------------------------------------------
*/

const avatarColors = [
  "#3B82F6",
  "#6366F1",
  "#0EA5E9",
  "#8B5CF6",
  "#06B6D4",
  "#2563EB"
]

const avatarColor = (name) => {
  if (!name) return avatarColors[0]
  return avatarColors[name.charCodeAt(0) % avatarColors.length]
}

const formatDate = (id) => {
  const d = new Date(Date.now() - id * 86400000 * 2)

  return d.toLocaleDateString("ru-RU", {
    day: "numeric",
    month: "short"
  })
}

/*
|--------------------------------------------------------------------------
| Computed
|--------------------------------------------------------------------------
*/

const filteredResults = computed(() => {
  let list = [...results.value]

  // filter by score tier
  if (activeFilter.value !== "all") {
    list = list.filter(r =>
      scoreTier(r.score) === activeFilter.value
    )
  }

  // search
  if (search.value.trim()) {
    const q = search.value.toLowerCase()

    list = list.filter(r =>
      r.subject?.toLowerCase().includes(q) ||
      r.user_name?.toLowerCase().includes(q) ||
      r.quiz_title?.toLowerCase().includes(q)
    )
  }

  // sort
  list.sort((a, b) => {
    const va = a[sortKey.value]
    const vb = b[sortKey.value]

    if (sortDesc.value) return va < vb ? 1 : -1
    return va > vb ? 1 : -1
  })

  return list
})

const avgScore = computed(() => {
  if (!results.value.length) return 0

  return Math.round(
    results.value.reduce((s, r) => s + r.score, 0) /
    results.value.length
  )
})

const highCount = computed(() =>
  results.value.filter(r => r.score >= 80).length
)

const midCount = computed(() =>
  results.value.filter(r => r.score >= 50 && r.score < 80).length
)

const lowCount = computed(() =>
  results.value.filter(r => r.score < 50).length
)

/*
|--------------------------------------------------------------------------
| Sorting
|--------------------------------------------------------------------------
*/

const sortBy = (key) => {
  if (sortKey.value === key) {
    sortDesc.value = !sortDesc.value
  } else {
    sortKey.value = key
    sortDesc.value = true
  }
}

/*
|--------------------------------------------------------------------------
| Load data
|--------------------------------------------------------------------------
*/

const loadResults = async () => {
  try {
    loading.value = true

    const res = await api.get("/admin/users-results")

    results.value = res.data
  } catch (e) {
    console.error("Load results error:", e)

    // demo fallback
    results.value = [
      { id: 1, user_name: "Алексей Смирнов", quiz_title: "JavaScript", score: 92 },
      { id: 2, user_name: "Мария Иванова", quiz_title: "Vue 3", score: 78 }
    ]
  } finally {
    loading.value = false
  }
}

onMounted(loadResults)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.admin-page {
  padding: 28px 32px;
  background: #F0F5FF;
  min-height: 100vh;
  font-family: 'Sora', sans-serif;
  display: flex;
  flex-direction: column;
  gap: 22px;
}

/* ── Topbar ── */
.topbar {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.topbar__left { display: flex; flex-direction: column; gap: 3px; }

.page-title {
  font-size: 22px;
  font-weight: 600;
  color: #0F2355;
  letter-spacing: -0.4px;
}

.page-subtitle { font-size: 12.5px; color: #93B4D8; font-weight: 300; }

.topbar__right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #fff;
  border: 1px solid #DBEAFE;
  border-radius: 10px;
  padding: 9px 14px;
}

.search-box input {
  border: none; outline: none;
  font-family: 'Sora', sans-serif;
  font-size: 13px; color: #0F2355; background: transparent; width: 210px;
}

.search-box input::placeholder { color: #B0C8E4; }

.filter-tabs {
  display: flex; gap: 4px;
  background: #fff;
  border: 1px solid #DBEAFE;
  border-radius: 10px;
  padding: 4px;
}

.filter-tab {
  padding: 6px 13px; border: none; border-radius: 7px;
  font-family: 'Sora', sans-serif; font-size: 12.5px; cursor: pointer;
  background: transparent; color: #6B84B0; transition: all 0.15s;
}

.filter-tab--active {
  background: linear-gradient(135deg, #EFF6FF, #DBEAFE);
  color: #2563EB; font-weight: 500;
}

/* ── Summary cards ── */
.summary-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
}

.summary-card {
  background: #fff;
  border-radius: 14px;
  border: 1px solid #E2EAFC;
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 6px;
  animation: fadeUp 0.4s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

.summary-card__label {
  font-size: 11.5px;
  color: #93B4D8;
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.summary-card__value {
  font-family: 'DM Mono', monospace;
  font-size: 26px;
  font-weight: 500;
  letter-spacing: -0.5px;
}

.summary-card--blue  .summary-card__value { color: #2563EB; }
.summary-card--green .summary-card__value { color: #16A34A; }
.summary-card--yellow .summary-card__value { color: #CA8A04; }
.summary-card--red   .summary-card__value { color: #DC2626; }

/* ── Skeleton ── */
.skeleton-row {
  height: 52px;
  background: linear-gradient(90deg, #E8EFFC 25%, #F3F7FF 50%, #E8EFFC 75%);
  background-size: 200% 100%;
  border-radius: 10px;
  margin-bottom: 8px;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  from { background-position: 200% 0; }
  to   { background-position: -200% 0; }
}

/* ── Empty ── */
.empty {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: 12px; padding: 64px 24px;
  background: #fff; border-radius: 18px; border: 1px dashed #BFDBFE;
}

.empty__icon {
  width: 68px; height: 68px; background: #EFF6FF;
  border-radius: 16px; display: flex; align-items: center; justify-content: center;
}

.empty__text { font-size: 14px; color: #93B4D8; font-weight: 300; }

/* ── Table ── */
.table-wrap {
  background: #fff;
  border-radius: 18px;
  border: 1px solid #E2EAFC;
  overflow: hidden;
}

.table {
  width: 100%;
  border-collapse: collapse;
}

.table thead tr { border-bottom: 1px solid #EEF2FF; }

.table th {
  padding: 14px 18px;
  font-size: 11.5px; font-weight: 500; color: #93B4D8;
  text-align: left; text-transform: uppercase; letter-spacing: 0.5px;
  background: #F8FAFF; user-select: none;
}

.th-sortable { cursor: pointer; }
.th-sortable:hover { color: #3B82F6; }
.th-sortable svg { margin-left: 4px; vertical-align: middle; color: #C8D9F0; transition: transform 0.2s, color 0.2s; }
.th-sortable svg.sort-active { color: #3B82F6; }
.th-sortable svg.sort-desc { transform: rotate(180deg); }

.table-row {
  border-bottom: 1px solid #F0F5FF;
  animation: fadeIn 0.3s ease both;
  transition: background 0.12s;
}

.table-row:last-child { border-bottom: none; }
.table-row:hover { background: #F8FAFF; }

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to   { opacity: 1; transform: translateY(0); }
}

.table td { padding: 13px 18px; font-size: 13.5px; color: #2C3E6A; vertical-align: middle; }

/* Cells */
.user-cell { display: flex; align-items: center; gap: 10px; }

.user-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white; font-size: 12px; font-weight: 600; flex-shrink: 0;
}

.td-name { font-weight: 500; }

.quiz-cell {
  display: flex; align-items: center; gap: 7px;
  font-size: 13px; color: #5B78AA;
}

/* Score badge */
.score-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-family: 'DM Mono', monospace;
  font-size: 12.5px;
  font-weight: 500;
}

.score-badge.high   { background: #DCFCE7; color: #16A34A; }
.score-badge.mid    { background: #FEF9C3; color: #CA8A04; }
.score-badge.low    { background: #FEE2E2; color: #DC2626; }

/* Progress bar */
.td-progress { width: 140px; }

.progress-bar {
  height: 6px;
  background: #EEF2FF;
  border-radius: 99px;
  overflow: hidden;
}

.progress-bar__fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.8s cubic-bezier(0.22, 1, 0.36, 1);
}

.progress-bar__fill--high   { background: linear-gradient(90deg, #4ADE80, #16A34A); }
.progress-bar__fill--mid    { background: linear-gradient(90deg, #FDE047, #CA8A04); }
.progress-bar__fill--low    { background: linear-gradient(90deg, #FCA5A5, #DC2626); }

.td-date {
  font-size: 12.5px;
  color: #B0C8E4;
  white-space: nowrap;
}
</style>