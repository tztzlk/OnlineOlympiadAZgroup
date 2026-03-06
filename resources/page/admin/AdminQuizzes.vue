<template>
  <div class="admin-page">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar__left">
        <h1 class="page-title">Тесты</h1>
        <span class="page-subtitle">Всего: {{ quizzes.length }}</span>
      </div>
      <button class="btn-create" @click="createQuiz">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
          <path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Создать тест
      </button>
    </header>

    <!-- Loading -->
    <div v-if="loading" class="skeleton-grid">
      <div v-for="i in 6" :key="i" class="skeleton-card"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!quizzes.length" class="empty">
      <div class="empty__icon">
        <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
          <rect x="5" y="5" width="30" height="30" rx="5" stroke="#BFDBFE" stroke-width="2"/>
          <path d="M13 20h14M13 26h9M13 14h14" stroke="#BFDBFE" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <p class="empty__text">Тесты ещё не созданы</p>
      <button class="btn-create" @click="createQuiz">Создать первый тест</button>
    </div>

    <!-- Quiz grid -->
    <div v-else class="quiz-grid">
      <div
        v-for="(quiz, i) in quizzes"
        :key="quiz.id"
        class="quiz-card"
        :style="{ animationDelay: i * 0.05 + 's' }"
      >
        <div class="quiz-card__header">
          <div class="quiz-card__icon" :class="'quiz-card__icon--' + (i % 3 === 0 ? 'blue' : i % 3 === 1 ? 'indigo' : 'sky')">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <rect x="2" y="2" width="14" height="14" rx="2.5" stroke="currentColor" stroke-width="1.6"/>
              <path d="M5.5 9h7M5.5 12h4.5M5.5 6h7" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="quiz-card__menu">
            <button class="menu-btn" @click.stop="toggleMenu(quiz.id)">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <circle cx="8" cy="3.5" r="1.2" fill="currentColor"/>
                <circle cx="8" cy="8" r="1.2" fill="currentColor"/>
                <circle cx="8" cy="12.5" r="1.2" fill="currentColor"/>
              </svg>
            </button>
            <div v-if="openMenu === quiz.id" class="dropdown">
              <button class="dropdown__item" @click="editQuiz(quiz)">
                <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                  <path d="M9 2l2 2-7 7H2v-2l7-7z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                </svg>
                Редактировать
              </button>
              <button class="dropdown__item dropdown__item--danger" @click="deleteQuiz(quiz.id)">
                <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                  <path d="M2 3.5h9M5 3.5V2.5h3v1M4 3.5l.5 7h4l.5-7" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
                Удалить
              </button>
            </div>
          </div>
        </div>

        <div class="quiz-card__body">
          <h3 class="quiz-card__title">{{ quiz.title }}</h3>
          <p v-if="quiz.description" class="quiz-card__desc">{{ quiz.description }}</p>
        </div>

        <div class="quiz-card__footer">
          <span class="quiz-card__meta">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.3"/>
              <path d="M6 3.5v2.5l1.5 1.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>
            </svg>
            {{ quiz.questions_count ?? '—' }} вопросов
          </span>
          <button class="btn-open" @click="openQuiz(quiz)">
            Открыть
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path d="M2.5 6h7M6.5 3l3 3-3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Click outside overlay -->
    <div v-if="openMenu" class="overlay" @click="openMenu = null"></div>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import api from "../../js/api"

const quizzes = ref([])
const loading = ref(true)
const openMenu = ref(null)

onMounted(async () => {
  try {
    const res = await api.get('/admin/quizzes')
    quizzes.value = res.data
  } catch {
    // fallback demo data
    quizzes.value = [
      { id: 1, title: "JavaScript основы", description: "Базовые концепции JS", questions_count: 12 },
      { id: 2, title: "Vue 3 компоненты", description: "Options API и Composition API", questions_count: 8 },
      { id: 3, title: "CSS Grid & Flexbox", description: "Современная вёрстка", questions_count: 15 },
      { id: 4, title: "HTTP и REST API", description: "Протоколы и методы", questions_count: 10 },
      { id: 5, title: "TypeScript введение", description: "Типизация в JS-проектах", questions_count: 9 },
    ]
  } finally {
    loading.value = false
  }
})

const toggleMenu = (id) => {
  openMenu.value = openMenu.value === id ? null : id
}

const createQuiz = () => {
  alert("Страница создания теста будет здесь")
}

const editQuiz = (quiz) => {
  openMenu.value = null
  alert(`Редактирование: ${quiz.title}`)
}

const deleteQuiz = (id) => {
  openMenu.value = null
  quizzes.value = quizzes.value.filter(q => q.id !== id)
}

const openQuiz = (quiz) => {
  alert(`Открыть тест: ${quiz.title}`)
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.admin-page {
  padding: 28px 32px;
  background: #F0F5FF;
  min-height: 100vh;
  font-family: 'Sora', sans-serif;
  display: flex;
  flex-direction: column;
  gap: 24px;
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

.page-subtitle {
  font-size: 12.5px;
  color: #93B4D8;
  font-weight: 300;
}

/* ── Button ── */
.btn-create {
  display: flex;
  align-items: center;
  gap: 7px;
  padding: 10px 18px;
  background: linear-gradient(135deg, #3B82F6, #1D4ED8);
  color: #fff;
  border: none;
  border-radius: 10px;
  font-family: 'Sora', sans-serif;
  font-size: 13.5px;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
  transition: transform 0.15s, box-shadow 0.15s, filter 0.15s;
}

.btn-create:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(59, 130, 246, 0.45);
  filter: brightness(1.05);
}

.btn-create:active { transform: translateY(0); }

/* ── Skeleton ── */
.skeleton-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.skeleton-card {
  height: 160px;
  background: linear-gradient(90deg, #E8EFFC 25%, #F3F7FF 50%, #E8EFFC 75%);
  background-size: 200% 100%;
  border-radius: 16px;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  from { background-position: 200% 0; }
  to   { background-position: -200% 0; }
}

/* ── Empty ── */
.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  padding: 64px 24px;
  background: #FFFFFF;
  border-radius: 18px;
  border: 1px dashed #BFDBFE;
}

.empty__icon {
  width: 72px; height: 72px;
  background: #EFF6FF;
  border-radius: 18px;
  display: flex; align-items: center; justify-content: center;
}

.empty__text {
  font-size: 14px;
  color: #93B4D8;
  font-weight: 300;
}

/* ── Grid ── */
.quiz-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

/* ── Card ── */
.quiz-card {
  background: #FFFFFF;
  border-radius: 16px;
  border: 1px solid #E2EAFC;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  cursor: default;
  transition: transform 0.2s, box-shadow 0.2s;
  animation: fadeUp 0.4s cubic-bezier(0.22, 1, 0.36, 1) both;
  position: relative;
}

.quiz-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 28px rgba(59, 130, 246, 0.1);
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}

.quiz-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.quiz-card__icon {
  width: 40px; height: 40px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
}

.quiz-card__icon--blue   { background: #EFF6FF; color: #3B82F6; }
.quiz-card__icon--indigo { background: #EEF2FF; color: #6366F1; }
.quiz-card__icon--sky    { background: #F0F9FF; color: #0EA5E9; }

.quiz-card__menu {
  position: relative;
}

.menu-btn {
  width: 30px; height: 30px;
  background: none;
  border: none;
  border-radius: 7px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: #B0C8E4;
  transition: background 0.15s, color 0.15s;
}

.menu-btn:hover {
  background: #F0F5FF;
  color: #3B82F6;
}

.dropdown {
  position: absolute;
  right: 0; top: 34px;
  background: #FFFFFF;
  border: 1px solid #E2EAFC;
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.12);
  padding: 5px;
  min-width: 160px;
  z-index: 10;
}

.dropdown__item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 8px 10px;
  background: none;
  border: none;
  border-radius: 7px;
  font-family: 'Sora', sans-serif;
  font-size: 12.5px;
  color: #2C3E6A;
  cursor: pointer;
  transition: background 0.12s;
}

.dropdown__item:hover { background: #F0F5FF; }

.dropdown__item--danger { color: #EF4444; }
.dropdown__item--danger:hover { background: #FEF2F2; }

.quiz-card__body {
  display: flex;
  flex-direction: column;
  gap: 5px;
  flex: 1;
}

.quiz-card__title {
  font-size: 14.5px;
  font-weight: 600;
  color: #0F2355;
  letter-spacing: -0.2px;
  line-height: 1.3;
}

.quiz-card__desc {
  font-size: 12.5px;
  color: #93B4D8;
  font-weight: 300;
  line-height: 1.5;
}

.quiz-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 12px;
  border-top: 1px solid #F0F5FF;
}

.quiz-card__meta {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 11.5px;
  color: #B0C8E4;
}

.btn-open {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 12px;
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  border-radius: 7px;
  font-family: 'Sora', sans-serif;
  font-size: 12px;
  font-weight: 500;
  color: #2563EB;
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s;
}

.btn-open:hover {
  background: #DBEAFE;
  border-color: #93C5FD;
}

/* Overlay */
.overlay {
  position: fixed;
  inset: 0;
  z-index: 5;
}
</style>