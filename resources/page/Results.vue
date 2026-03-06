<template>
  <br>
<br>
<br>
  <div class="results-page">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>


    <div class="results-card">
      <!-- Header -->
      <div class="header">
        <div class="header-left">
          <h1>Результаты олимпиад</h1>
          <p>{{ results.length }} записей · обновлено сегодня</p>
        </div>

        <div class="header-right">
          <div class="search-wrap" :class="{ focused: searchFocused }">
            <svg class="search-icon" viewBox="0 0 20 20" fill="none">
              <circle cx="9" cy="9" r="6" stroke="currentColor" stroke-width="1.5"/>
              <path d="M14 14l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <input
              v-model="search"
              type="text"
              placeholder="Поиск по предмету..."
              @focus="searchFocused = true"
              @blur="searchFocused = false"
            />
            <button v-if="search" class="clear-btn" @click="search = ''">
              <svg viewBox="0 0 20 20" fill="none">
                <path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Stats row -->
      <div class="stats-row">
        <div class="stat-card win-card">
          <div class="stat-icon">🏆</div>
          <div>
            <div class="stat-num">{{ winCount }}</div>
            <div class="stat-label">Победы</div>
          </div>
        </div>
        <div class="stat-card prize-card">
          <div class="stat-icon">🥉</div>
          <div>
            <div class="stat-num">{{ prizeCount }}</div>
            <div class="stat-label">Призовых</div>
          </div>
        </div>
        <div class="stat-card score-card">
          <div class="stat-icon">⭐</div>
          <div>
            <div class="stat-num">{{ avgScore }}</div>
            <div class="stat-label">Средний балл</div>
          </div>
        </div>
        <div class="stat-card total-card">
          <div class="stat-icon">📋</div>
          <div>
            <div class="stat-num">{{ results.length }}</div>
            <div class="stat-label">Всего олимпиад</div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="table-wrapper">
        <table class="results-table">
          <thead>
            <tr>
              <th>Предмет</th>
              <th>Дата</th>
              <th>Баллы</th>
              <th>Место</th>
              <th>Статус</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="result in filteredResults"
              :key="result.id"
              class="table-row"
            >
              <td>
                <div class="subject-cell">
                  <div class="subject-dot" :class="result.statusClass"></div>
                  {{ result.subject }}
                </div>
              </td>
              <td class="date-cell">{{ result.date }}</td>
              <td>
                <div class="score-cell">
                  <div class="score-bar-track">
                    <div
                      class="score-bar-fill"
                      :class="result.statusClass"
                      :style="{ width: result.score + '%' }"
                    ></div>
                  </div>
                  <span class="score-num">{{ result.score }}/100</span>
                </div>
              </td>
              <td>
                <span class="place-badge" :class="result.statusClass === 'win' ? 'place-win' : ''">
                  {{ result.place }}
                </span>
              </td>
              <td>
                <span :class="['status-badge', result.statusClass]">
                  <span class="status-dot"></span>
                  {{ result.status }}
                </span>
              </td>
            </tr>

            <tr v-if="filteredResults.length === 0">
              <td colspan="5" class="empty-row">
                <div class="empty-state">
                  <svg viewBox="0 0 48 48" fill="none">
                    <circle cx="24" cy="24" r="20" stroke="#e5e7eb" stroke-width="2"/>
                    <path d="M16 24h16M24 16v16" stroke="#d1d5db" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                  <p>Ничего не найдено</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import api from "../js/api"
import { ref, onMounted, computed } from "vue"

const results = ref([])
const loading = ref(false)
const search = ref("")
const searchFocused = ref(false)

const loadResults = async () => {
  try {
    loading.value = true

    const res = await api.get("/profile/results")

    results.value = res.data

  } catch (e) {
    console.error("Results load error:", e)
  } finally {
    loading.value = false
  }
}

/*
| Computed
*/

const filteredResults = computed(() =>
  results.value.filter(r =>
    r.subject.toLowerCase().includes(search.value.toLowerCase())
  )
)

const winCount = computed(() =>
  results.value.filter(r => r.statusClass === "win").length
)

const prizeCount = computed(() =>
  results.value.filter(r => r.statusClass === "prize").length
)

const avgScore = computed(() => {
  if (!results.value.length) return 0
  return Math.round(
    results.value.reduce((s, r) => s + r.score, 0) /
    results.value.length
  )
})

onMounted(() => {
  loadResults()
})
</script>
<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap');

* { box-sizing: border-box; margin: 0; padding: 0; }

.results-page {
  font-family: 'Sora', sans-serif;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  padding: 32px 24px;
  background: #f1f4f9;
  position: relative;
  overflow: hidden;
}

/* --- Orbs --- */
.bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.45; animation: drift 12s ease-in-out infinite alternate; }
.orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, #c7d2fe, transparent 70%); top: -120px; left: -100px; animation-duration: 14s; }
.orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, #bfdbfe, transparent 70%); bottom: -80px; right: -60px; animation-duration: 10s; animation-delay: -4s; }
.orb-3 { width: 300px; height: 300px; background: radial-gradient(circle, #ddd6fe, transparent 70%); top: 40%; left: 50%; opacity: 0.35; animation-duration: 16s; animation-delay: -7s; }

@keyframes drift {
  from { transform: translate(0,0) scale(1); }
  to { transform: translate(30px, 40px) scale(1.1); }
}

/* --- Card --- */
.results-card {
  width: 100%;
  max-width: 1100px;
  background: white;
  border-radius: 28px;
  padding: 40px;
  box-shadow: 0 20px 60px rgba(99, 102, 241, 0.1), 0 0 0 1px rgba(0,0,0,0.06);
  position: relative;
  z-index: 1;
  animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
  align-self: flex-start;
}

@keyframes cardIn {
  from { opacity: 0; transform: translateY(24px); }
  to { opacity: 1; transform: translateY(0); }
}

/* --- Header --- */
.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 28px;
  gap: 16px;
  flex-wrap: wrap;
}

.header-left h1 {
  font-size: 26px;
  font-weight: 700;
  color: #111827;
  letter-spacing: -0.5px;
  margin-bottom: 4px;
}

.header-left p {
  font-size: 13px;
  color: #9ca3af;
}

/* Search */
.search-wrap {
  display: flex;
  align-items: center;
  background: #f9fafb;
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  padding: 0 12px;
  gap: 8px;
  width: 280px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.search-wrap.focused {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  background: white;
}

.search-icon {
  width: 16px;
  height: 16px;
  color: #9ca3af;
  flex-shrink: 0;
}

.search-wrap input {
  flex: 1;
  border: none;
  background: transparent;
  padding: 11px 0;
  font-size: 14px;
  font-family: 'Sora', sans-serif;
  color: #111827;
  outline: none;
}

.search-wrap input::placeholder { color: #c4c9d4; }

.clear-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 2px;
  color: #9ca3af;
  display: flex;
  align-items: center;
  transition: color 0.2s;
}
.clear-btn:hover { color: #6366f1; }
.clear-btn svg { width: 14px; height: 14px; }

/* --- Stats row --- */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 28px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  border-radius: 16px;
  border: 1px solid transparent;
  transition: transform 0.2s;
}

.stat-card:hover { transform: translateY(-2px); }

.win-card { background: #f0fdf4; border-color: #bbf7d0; }
.prize-card { background: #eef2ff; border-color: #c7d2fe; }
.score-card { background: #fffbeb; border-color: #fde68a; }
.total-card { background: #f8fafc; border-color: #e2e8f0; }

.stat-icon { font-size: 22px; line-height: 1; }

.stat-num {
  font-size: 22px;
  font-weight: 700;
  color: #111827;
  line-height: 1;
  margin-bottom: 2px;
}

.stat-label {
  font-size: 12px;
  color: #9ca3af;
}

/* --- Table --- */
.table-wrapper { overflow-x: auto; border-radius: 16px; border: 1px solid #f1f5f9; }

.results-table {
  width: 100%;
  min-width: 620px;
  border-collapse: collapse;
}

.results-table thead tr {
  background: #f8fafc;
}

.results-table th {
  text-align: left;
  padding: 13px 16px;
  font-size: 12px;
  font-weight: 600;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #f1f5f9;
}

.results-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f8fafc;
  font-size: 14px;
  color: #374151;
}

.table-row {
  transition: background 0.15s;
}

.table-row:hover { background: #fafbff; }
.table-row:last-child td { border-bottom: none; }

/* Subject cell */
.subject-cell {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 500;
  color: #111827;
}

.subject-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}
.subject-dot.win { background: #22c55e; }
.subject-dot.prize { background: #6366f1; }
.subject-dot.participant { background: #94a3b8; }

/* Date cell */
.date-cell { color: #9ca3af; font-size: 13px; }

/* Score cell */
.score-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.score-bar-track {
  width: 80px;
  height: 5px;
  background: #f1f5f9;
  border-radius: 99px;
  overflow: hidden;
  flex-shrink: 0;
}

.score-bar-fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.6s ease;
}
.score-bar-fill.win { background: #22c55e; }
.score-bar-fill.prize { background: #6366f1; }
.score-bar-fill.participant { background: #94a3b8; }

.score-num { font-size: 13px; font-weight: 500; color: #374151; white-space: nowrap; }

/* Place badge */
.place-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #f1f5f9;
  font-size: 13px;
  font-weight: 600;
  color: #64748b;
}

.place-badge.place-win {
  background: linear-gradient(135deg, #fef08a, #fbbf24);
  color: #78350f;
  box-shadow: 0 2px 8px rgba(251, 191, 36, 0.35);
}

/* Status badge */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 11px;
  border-radius: 99px;
  font-size: 12.5px;
  font-weight: 600;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

.status-badge.win { background: #dcfce7; color: #16a34a; }
.status-badge.prize { background: #e0e7ff; color: #4f46e5; }
.status-badge.participant { background: #f1f5f9; color: #64748b; }

/* Empty state */
.empty-row { padding: 40px !important; }
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 20px;
}
.empty-state svg { width: 48px; height: 48px; }
.empty-state p { font-size: 14px; color: #9ca3af; }

/* --- Responsive --- */
@media (max-width: 768px) {
  .results-card { padding: 24px; }
  .header { flex-direction: column; }
  .search-wrap { width: 100%; }
  .stats-row { grid-template-columns: 1fr 1fr; }
  .score-bar-track { width: 50px; }
}

@media (max-width: 480px) {
  .results-page { padding: 16px; }
  .results-card { padding: 18px; }
  .stats-row { grid-template-columns: 1fr 1fr; gap: 10px; }
  .stat-card { padding: 12px 14px; }
  .stat-num { font-size: 18px; }
  .header-left h1 { font-size: 22px; }
}
</style>