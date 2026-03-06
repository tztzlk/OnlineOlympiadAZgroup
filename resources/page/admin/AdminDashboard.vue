<template>
  <div class="admin-page">

     
        <div class="topbar__right">
          <div class="search-box">
            <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
              <circle cx="6.5" cy="6.5" r="5" stroke="#93B4D8" stroke-width="1.5"/>
              <path d="M10.5 10.5L13.5 13.5" stroke="#93B4D8" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <input type="text" placeholder="Поиск..." />
          </div>
          <button class="notif-btn">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M9 2a5 5 0 00-5 5v3l-1.5 2.5h13L14 10V7a5 5 0 00-5-5z" stroke="#5B78AA" stroke-width="1.5"/>
              <path d="M7 14.5a2 2 0 004 0" stroke="#5B78AA" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span class="notif-dot"></span>
          </button>
        </div>
     

      <!-- Stats cards -->
      <section class="stats-grid">

        <div class="stat-card stat-card--blue" style="animation-delay: 0s">
          <div class="stat-card__icon">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
              <circle cx="11" cy="8" r="4" stroke="currentColor" stroke-width="1.8"/>
              <path d="M3 20c0-4.418 3.582-8 8-8s8 3.582 8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="stat-card__body">
            <span class="stat-card__label">Пользователи</span>
            <div class="stat-card__value">
              <span class="count" :data-target="stats.users">{{ displayUsers }}</span>
            </div>
            <div class="stat-card__trend">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                <path d="M2 7L5 4L8 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              <span>+12% за месяц</span>
            </div>
          </div>
          <div class="stat-card__bg-shape"></div>
        </div>

        <div class="stat-card stat-card--indigo" style="animation-delay: 0.1s">
          <div class="stat-card__icon">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
              <rect x="3" y="3" width="16" height="16" rx="2.5" stroke="currentColor" stroke-width="1.8"/>
              <path d="M7 11h8M7 15h5M7 7h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="stat-card__body">
            <span class="stat-card__label">Тесты</span>
            <div class="stat-card__value">
              <span class="count">{{ displayQuizzes }}</span>
            </div>
            <div class="stat-card__trend">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                <path d="M2 7L5 4L8 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              <span>+5% за месяц</span>
            </div>
          </div>
          <div class="stat-card__bg-shape"></div>
        </div>

        <div class="stat-card stat-card--sky" style="animation-delay: 0.2s">
          <div class="stat-card__icon">
            <svg width="22" height="22" viewBox="0 0 22 22" fill="none">
              <path d="M4 17V13M8 17V8M12 17V10M16 17V5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
              <path d="M4 13L8 9L12 11L17 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="stat-card__body">
            <span class="stat-card__label">Результаты</span>
            <div class="stat-card__value">
              <span class="count">{{ displayResults }}</span>
            </div>
            <div class="stat-card__trend">
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                <path d="M2 7L5 4L8 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              <span>+18% за месяц</span>
            </div>
          </div>
          <div class="stat-card__bg-shape"></div>
        </div>

      </section>

      <!-- Bottom panels -->
      <section class="panels">

        <div class="panel panel--activity">
          <div class="panel__header">
            <h2 class="panel__title">Активность</h2>
            <div class="panel__tabs">
              <button class="tab tab--active">Неделя</button>
              <button class="tab">Месяц</button>
            </div>
          </div>
          <div class="chart">
            <div class="chart__bars">
              <div v-for="(bar, i) in chartBars" :key="i" class="chart__bar-wrap">
                <div class="chart__bar" :style="{ height: bar.height + '%', animationDelay: i * 0.07 + 's' }"></div>
                <span class="chart__label">{{ bar.label }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="panel panel--recent">
          <div class="panel__header">
            <h2 class="panel__title">Последние события</h2>
            <a href="#" class="panel__link">Все →</a>
          </div>
          <ul class="events">
            <li v-for="(event, i) in recentEvents" :key="i" class="event" :style="{ animationDelay: i * 0.06 + 's' }">
              <div class="event__dot" :class="'event__dot--' + event.type"></div>
              <div class="event__content">
                <span class="event__text">{{ event.text }}</span>
                <span class="event__time">{{ event.time }}</span>
              </div>
            </li>
          </ul>
        </div>

      </section>

  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from "vue"
import api from "../../js/api"

const stats = reactive({ users: 0, quizzes: 0, results: 0 })
const displayUsers = ref(0)
const displayQuizzes = ref(0)
const displayResults = ref(0)

const currentDate = computed(() => {
  return new Date().toLocaleDateString('ru-RU', { weekday: 'long', day: 'numeric', month: 'long' })
})

const chartBars = [
  { height: 45, label: 'Пн' },
  { height: 72, label: 'Вт' },
  { height: 58, label: 'Ср' },
  { height: 90, label: 'Чт' },
  { height: 63, label: 'Пт' },
  { height: 38, label: 'Сб' },
  { height: 55, label: 'Вс' },
]

const recentEvents = [
  { type: 'blue',   text: 'Новый пользователь зарегистрировался', time: '2 мин назад' },
  { type: 'indigo', text: 'Тест "JavaScript основы" создан',      time: '15 мин назад' },
  { type: 'sky',    text: '47 новых результатов за сегодня',       time: '1 час назад' },
  { type: 'blue',   text: 'Пользователь обновил профиль',          time: '2 часа назад' },
  { type: 'indigo', text: 'Тест "Vue 3" обновлён',                 time: '3 часа назад' },
]

function animateCount(ref, target, duration = 1200) {
  const start = performance.now()
  const step = (now) => {
    const progress = Math.min((now - start) / duration, 1)
    const ease = 1 - Math.pow(1 - progress, 3)
    ref.value = Math.round(ease * target)
    if (progress < 1) requestAnimationFrame(step)
  }
  requestAnimationFrame(step)
}

onMounted(async () => {
  try {
    const res = await api.get('/admin/dashboard')
    stats.users = res.data.users
    stats.quizzes = res.data.quizzes
    stats.results = res.data.results
  } catch {
    stats.users = 1284
    stats.quizzes = 56
    stats.results = 8340
  }

  setTimeout(() => {
    animateCount(displayUsers, stats.users)
    animateCount(displayQuizzes, stats.quizzes)
    animateCount(displayResults, stats.results)
  }, 300)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Layout ── */
.admin-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 28px 32px;
  background: #F0F5FF;
  font-family: 'Sora', sans-serif;
  min-height: 100vh;
}

/* ── Topbar ── */
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.topbar__left {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.page-title {
  font-size: 22px;
  font-weight: 600;
  color: #0F2355;
  letter-spacing: -0.4px;
}

.page-date {
  font-size: 12.5px;
  color: #93B4D8;
  font-weight: 300;
  text-transform: capitalize;
}

.topbar__right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FFFFFF;
  border: 1px solid #DBEAFE;
  border-radius: 10px;
  padding: 9px 14px;
}

.search-box input {
  border: none;
  outline: none;
  font-family: 'Sora', sans-serif;
  font-size: 13px;
  color: #0F2355;
  background: transparent;
  width: 160px;
}

.search-box input::placeholder { color: #B0C8E4; }

.notif-btn {
  position: relative;
  width: 38px; height: 38px;
  background: #FFFFFF;
  border: 1px solid #DBEAFE;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  transition: background 0.15s;
}

.notif-btn:hover { background: #EFF6FF; }

.notif-dot {
  position: absolute;
  top: 8px; right: 8px;
  width: 7px; height: 7px;
  background: #3B82F6;
  border-radius: 50%;
  border: 1.5px solid white;
}

/* ── Stat cards ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.stat-card {
  position: relative;
  overflow: hidden;
  border-radius: 18px;
  padding: 24px;
  display: flex;
  gap: 16px;
  align-items: flex-start;
  border: 1px solid transparent;
  animation: fadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

.stat-card--blue {
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
  border-color: #2563EB;
}

.stat-card--indigo {
  background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
  border-color: #4F46E5;
}

.stat-card--sky {
  background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%);
  border-color: #0284C7;
}

.stat-card__icon {
  width: 46px; height: 46px;
  background: rgba(255,255,255,0.18);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: white;
  flex-shrink: 0;
  backdrop-filter: blur(8px);
}

.stat-card__body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.stat-card__label {
  font-size: 12px;
  font-weight: 400;
  color: rgba(255,255,255,0.75);
  letter-spacing: 0.3px;
  text-transform: uppercase;
}

.stat-card__value {
  font-family: 'DM Mono', monospace;
  font-size: 32px;
  font-weight: 500;
  color: #FFFFFF;
  line-height: 1;
  letter-spacing: -1px;
}

.stat-card__trend {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11.5px;
  color: rgba(255,255,255,0.7);
  margin-top: 4px;
}

.stat-card__trend svg { color: rgba(255,255,255,0.9); }

.stat-card__bg-shape {
  position: absolute;
  width: 130px; height: 130px;
  border-radius: 50%;
  background: rgba(255,255,255,0.07);
  right: -30px; bottom: -40px;
  pointer-events: none;
}

/* ── Panels ── */
.panels {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 18px;
}

.panel {
  background: #FFFFFF;
  border-radius: 18px;
  border: 1px solid #E2EAFC;
  padding: 24px;
  animation: fadeUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) 0.25s both;
}

.panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 22px;
}

.panel__title {
  font-size: 15px;
  font-weight: 600;
  color: #0F2355;
  letter-spacing: -0.2px;
}

.panel__tabs {
  display: flex;
  gap: 4px;
  background: #F0F5FF;
  border-radius: 8px;
  padding: 3px;
}

.tab {
  padding: 5px 12px;
  border: none;
  border-radius: 6px;
  font-family: 'Sora', sans-serif;
  font-size: 12px;
  cursor: pointer;
  background: transparent;
  color: #6B84B0;
  transition: all 0.15s;
}

.tab--active {
  background: #FFFFFF;
  color: #2563EB;
  font-weight: 500;
  box-shadow: 0 1px 4px rgba(59,130,246,0.15);
}

.panel__link {
  font-size: 12.5px;
  color: #3B82F6;
  text-decoration: none;
  font-weight: 500;
  transition: opacity 0.15s;
}
.panel__link:hover { opacity: 0.75; }

/* Chart */
.chart {
  height: 160px;
  display: flex;
  align-items: flex-end;
}

.chart__bars {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  width: 100%;
  height: 100%;
}

.chart__bar-wrap {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  height: 100%;
  justify-content: flex-end;
}

.chart__bar {
  width: 100%;
  background: linear-gradient(180deg, #3B82F6 0%, #BFDBFE 100%);
  border-radius: 6px 6px 4px 4px;
  min-height: 8px;
  animation: barGrow 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
  transform-origin: bottom;
}

@keyframes barGrow {
  from { transform: scaleY(0); opacity: 0; }
  to   { transform: scaleY(1); opacity: 1; }
}

.chart__label {
  font-size: 11px;
  color: #93B4D8;
  font-weight: 400;
}

/* Events */
.events {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 0;
}

.event {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid #F0F5FF;
  animation: fadeUp 0.4s cubic-bezier(0.22, 1, 0.36, 1) both;
}

.event:last-child { border-bottom: none; }

.event__dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 5px;
}

.event__dot--blue   { background: #3B82F6; }
.event__dot--indigo { background: #6366F1; }
.event__dot--sky    { background: #0EA5E9; }

.event__content {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.event__text {
  font-size: 13px;
  color: #2C3E6A;
  line-height: 1.4;
}

.event__time {
  font-size: 11px;
  color: #B0C8E4;
}
</style>