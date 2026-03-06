<template>
  <div class="quiz-wrap">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
    </div>

    <!-- Loading -->
    <div v-if="!quiz && !loadError" class="loading-state">
      <div class="loader-ring"></div>
      <p>Загружаем тест…</p>
    </div>

    <!-- Error -->
    <div v-else-if="loadError" class="error-state">
      <div class="error-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
      </div>
      <h3>Не удалось загрузить тест</h3>
      <button class="btn-primary" @click="loadQuiz">Попробовать снова</button>
    </div>

    <!-- Quiz -->
    <div v-else-if="quiz && !result" class="quiz-page">

      <!-- Header -->
      <div class="quiz-header">
        <div class="quiz-meta">
          <div class="quiz-badge">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            Олимпиада
          </div>
          <h1 class="quiz-title">{{ quiz.title }}</h1>
        </div>

        <!-- Progress & Timer -->
        <div class="quiz-stats">
          <div class="stat-pill">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
            </svg>
            {{ answeredCount }} / {{ quiz.questions.length }} ответов
          </div>
          <div class="stat-pill" :class="{ 'timer-warn': timeLeft < 60 }" v-if="timeLeft !== null">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            {{ formatTime(timeLeft) }}
          </div>
        </div>
      </div>

      <!-- Progress bar -->
      <div class="progress-bar-wrap">
        <div class="progress-bar" :style="{ width: progressPercent + '%' }"></div>
      </div>

      <!-- Questions -->
      <div class="questions-list">
        <div
          class="question-card"
          v-for="(q, qIndex) in quiz.questions"
          :key="q.id"
          :class="{ answered: userAnswers[q.id] }"
        >
          <div class="question-header">
            <div class="q-num">{{ qIndex + 1 }}</div>
            <div class="q-content">
              <p class="q-text">{{ q.question }}</p>
              <div class="question-media" v-if="q.image || q.audio">
                <img
                  v-if="q.image"
                  :src="q.image"
                  alt="Вопрос"
                  class="question-image"
                  @click="openImage(q.image)"
                />
                <audio v-if="q.audio" class="question-audio" controls preload="none">
                  <source :src="q.audio" type="audio/mpeg" />
                  Ваш браузер не поддерживает аудио.
                </audio>
              </div>
            </div>
          </div>

          <!-- Image modal -->
          <transition name="fade">
            <div
              v-if="showImageModal"
              class="image-modal-overlay"
              @click="showImageModal = false"
            >
              <img :src="modalImageSrc" class="image-modal-content" @click.stop />
            </div>
          </transition>

          <div class="answers-list">
            <label
              v-for="a in q.answers"
              :key="a.id"
              class="answer-option"
              :class="{ selected: userAnswers[q.id] === a.id }"
            >
              <input
                type="radio"
                :name="'q_' + q.id"
                :value="a.id"
                v-model="userAnswers[q.id]"
              />
              <div class="answer-radio">
                <div class="radio-dot"></div>
              </div>
              <span>{{ a.answer }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Submit -->
      <div class="quiz-footer">
        <div class="footer-info">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <span v-if="unansweredCount > 0">Осталось без ответа: <strong>{{ unansweredCount }}</strong></span>
          <span v-else>Все вопросы отвечены!</span>
        </div>
        <button
          @click="confirmSubmit"
          class="btn-submit"
          :disabled="submitting"
        >
          <svg v-if="!submitting" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <div v-else class="btn-loader"></div>
          {{ submitting ? 'Отправка…' : 'Сдать тест' }}
        </button>
      </div>
    </div>

    <!-- Result -->
    <div v-else-if="result" class="result-page">
      <div class="result-card">

        <div class="result-icon" :class="resultClass">
          <svg v-if="isPassed" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <svg v-else width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </div>

        <div class="result-badge" :class="resultClass + '-badge'">
          {{ isPassed ? 'Отличный результат!' : 'Попробуйте ещё раз' }}
        </div>

        <h2 class="result-title">Ваш результат</h2>

        <!-- Score circle -->
        <div class="score-wrap">
          <svg class="score-ring" width="140" height="140" viewBox="0 0 140 140">
            <circle cx="70" cy="70" r="58" fill="none" stroke="#f1f5f9" stroke-width="10"/>
            <circle
              cx="70" cy="70" r="58"
              fill="none"
              :stroke="isPassed ? '#22c55e' : '#ef4444'"
              stroke-width="10"
              stroke-linecap="round"
              :stroke-dasharray="364"
              :stroke-dashoffset="364 - (364 * scorePercent / 100)"
              transform="rotate(-90 70 70)"
              style="transition: stroke-dashoffset 1s ease"
            />
          </svg>
          <div class="score-inner">
            <span class="score-num">{{ result.score }}</span>
            <span class="score-total">/ {{ result.total }}</span>
          </div>
        </div>

        <div class="score-percent-label">{{ scorePercent }}% правильных ответов</div>

        <!-- Stats row -->
        <div class="result-stats">
          <div class="res-stat">
            <span class="res-stat__num correct">{{ result.score }}</span>
            <span class="res-stat__label">Верно</span>
          </div>
          <div class="res-stat-div"></div>
          <div class="res-stat">
            <span class="res-stat__num wrong">{{ result.total - result.score }}</span>
            <span class="res-stat__label">Неверно</span>
          </div>
          <div class="res-stat-div"></div>
          <div class="res-stat">
            <span class="res-stat__num">{{ result.total }}</span>
            <span class="res-stat__label">Всего</span>
          </div>
        </div>

        <button @click="goBack" class="btn-primary">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
          Вернуться в профиль
        </button>
      </div>
    </div>

    <!-- Floating HUD -->
    <transition name="hud-in">
      <div
        v-if="quiz && !result"
        class="floating-hud"
      >
        <div class="hud-row hud-timer" :class="{ 'timer-warn': timeLeft !== null && timeLeft < 60 }">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
          </svg>
          {{ timeLeft !== null ? formatTime(timeLeft) : '60:00' }}
        </div>
        <div class="hud-divider"></div>
        <div class="hud-row">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
          </svg>
          {{ answeredCount }} / {{ quiz.questions.length }}
        </div>
        <div class="hud-divider"></div>
        <div class="hud-row hud-unanswered" v-if="unansweredCount > 0">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          Без ответа: {{ unansweredCount }}
        </div>
        <div class="hud-row hud-done" v-else>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Все отвечены!
        </div>

        <!-- Mini progress ring -->
        <svg class="hud-ring" width="36" height="36" viewBox="0 0 36 36">
          <circle cx="18" cy="18" r="14" fill="none" stroke="#f1f5f9" stroke-width="3"/>
          <circle
            cx="18" cy="18" r="14"
            fill="none" stroke="#6366f1" stroke-width="3"
            stroke-linecap="round"
            :stroke-dasharray="88"
            :stroke-dashoffset="88 - (88 * progressPercent / 100)"
            transform="rotate(-90 18 18)"
            style="transition: stroke-dashoffset 0.4s ease"
          />
          <text x="18" y="22" text-anchor="middle" font-size="9" font-weight="700" fill="#4f46e5" font-family="Manrope,sans-serif">
            {{ Math.round(progressPercent) }}%
          </text>
        </svg>
      </div>
    </transition>

    <!-- Confirm modal -->
    <transition name="fade">
      <div v-if="showConfirm" class="modal-overlay" @click.self="showConfirm = false">
        <div class="modal">
          <div class="modal-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
          </div>
          <h3>Сдать тест?</h3>
          <p v-if="unansweredCount > 0">Вы ответили не на все вопросы. Осталось без ответа: <strong>{{ unansweredCount }}</strong>.</p>
          <p v-else>Вы ответили на все вопросы. Подтвердите отправку.</p>
          <div class="modal-actions">
            <button class="btn-ghost" @click="showConfirm = false">Отмена</button>
            <button class="btn-primary" @click="submitQuiz">Подтвердить</button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../js/api'

const route = useRoute()
const router = useRouter()

const quiz = ref(null)
const userAnswers = ref({})
const result = ref(null)
const submitting = ref(false)
const loadError = ref(false)
const showConfirm = ref(false)
const showImageModal = ref(false)
const modalImageSrc = ref('')
const hasResult = ref(false)
const openImage = (src) => {
  modalImageSrc.value = src
  showImageModal.value = true
}
const handleKey = (e) => { if (e.key === 'Escape') showImageModal.value = false }
const timeLeft = ref(null)
let timer = null

// Floating HUD — fixed bottom-left, no cursor tracking

const answeredCount = computed(() => Object.keys(userAnswers.value).length)
const unansweredCount = computed(() => quiz.value ? quiz.value.questions.length - answeredCount.value : 0)
const progressPercent = computed(() => quiz.value ? (answeredCount.value / quiz.value.questions.length) * 100 : 0)
const scorePercent = computed(() => result.value ? Math.round((result.value.score / result.value.total) * 100) : 0)
const isPassed = computed(() => scorePercent.value >= 60)
const resultClass = computed(() => isPassed.value ? 'success' : 'fail')

const formatTime = (s) => {
  const m = Math.floor(s / 60)
  const sec = s % 60
  return `${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`
}

const loadQuiz = async () => {
  loadError.value = false
  try {
    const res = await api.get(`/quiz/${route.params.subjectId}`)
    quiz.value = res.data
    // Default 60 min, or use server value
    const durationSeconds = (res.data.duration_minutes ?? 60) * 60
    timeLeft.value = durationSeconds
    timer = setInterval(() => {
      if (timeLeft.value > 0) timeLeft.value--
      else { clearInterval(timer); submitQuiz() }
    }, 1000)
  } catch(e) {
    console.error(e)
    loadError.value = true
  }
}

const confirmSubmit = () => { showConfirm.value = true }

const submitQuiz = async () => {
  if (!quiz.value || submitting.value) return
  showConfirm.value = false
  submitting.value = true
  clearInterval(timer)
  try {
    const res = await api.post(`/quiz/${quiz.value.id}/submit`, { answers: userAnswers.value })
    result.value = res.data
  } catch(e) {
    console.error(e)
    alert('Ошибка отправки ответов')
  } finally {
    submitting.value = false
  }
}

const goBack = () => router.push('/profile')

onMounted(() => {
  loadQuiz()
  window.addEventListener('keydown', handleKey)
})

onUnmounted(() => {
  if (timer) { clearInterval(timer); timer = null }
  window.removeEventListener('keydown', handleKey)
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; }

.quiz-wrap {
  min-height: 100vh;
  background: #f7f8fc;
  padding: 60px 28px 100px;
  font-family: 'Manrope', sans-serif;
  position: relative;
  overflow: hidden;
}

/* Orbs */
.bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.3; }
.orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, #c7d2fe, #a5b4fc); top: -150px; right: -100px; }
.orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, #ddd6fe, #c4b5fd); bottom: -100px; left: -80px; }

/* Loading */
.loading-state, .error-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  min-height: 70vh; gap: 16px;
  position: relative; z-index: 1;
}
.loader-ring {
  width: 48px; height: 48px;
  border: 3px solid #e2e8f0;
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 0.9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.loading-state p { font-size: 15px; color: #94a3b8; font-weight: 600; }
.error-icon {
  width: 72px; height: 72px;
  background: #fee2e2; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #dc2626;
}
.error-state h3 { font-size: 20px; color: #1e1b4b; margin: 0; }

/* Quiz page */
.quiz-page {
  max-width: 760px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* Header */
.quiz-header {
  background: white;
  border-radius: 24px 24px 0 0;
  padding: 30px 32px 24px;
  border: 1px solid #e2e8f0;
  border-bottom: none;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.quiz-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6366f1;
  background: #eef2ff;
  padding: 5px 12px;
  border-radius: 20px;
  border: 1px solid #c7d2fe;
  margin-bottom: 10px;
}
.quiz-title {
  font-family: 'Playfair Display', serif;
  font-size: 26px;
  font-weight: 700;
  color: #1e1b4b;
  margin: 0;
}
.quiz-stats { display: flex; gap: 10px; flex-wrap: wrap; flex-shrink: 0; margin-top: 4px; }
.stat-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #64748b;
  background: #f8fafc;
  padding: 8px 14px;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
}
.timer-warn { color: #ef4444; background: #fff5f5; border-color: #fecaca; animation: pulse-timer 1s ease-in-out infinite; }
@keyframes pulse-timer { 0%,100%{opacity:1} 50%{opacity:0.6} }

/* Progress bar */
.progress-bar-wrap {
  height: 4px;
  background: #f1f5f9;
  border-left: 1px solid #e2e8f0;
  border-right: 1px solid #e2e8f0;
}
.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #818cf8, #6366f1);
  transition: width 0.4s ease;
  border-radius: 0 2px 2px 0;
}

/* Questions */
.questions-list {
  background: white;
  border-left: 1px solid #e2e8f0;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  gap: 0;
  padding: 8px 0;
}

.question-card {
  padding: 28px 32px;
  border-bottom: 1px solid #f1f5f9;
  transition: background 0.2s;
}
.question-card:last-child { border-bottom: none; }
.question-card.answered { background: #fafbff; }

.question-header { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 20px; }
.q-num {
  flex-shrink: 0;
  width: 30px; height: 30px;
  background: #eef2ff;
  border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px;
  font-weight: 700;
  color: #6366f1;
  margin-top: 1px;
}
.question-card.answered .q-num {
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
}
.q-text { font-size: 16px; font-weight: 600; color: #1e1b4b; margin: 0; line-height: 1.5; }

/* Answers */
.answers-list { display: flex; flex-direction: column; gap: 10px; padding-left: 44px; }
.answer-option {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 13px 16px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.2s;
  background: white;
  font-size: 15px;
  color: #374151;
  font-weight: 500;
}
.answer-option:hover { border-color: #a5b4fc; background: #fafbff; }
.answer-option.selected { border-color: #6366f1; background: #eef2ff; color: #4338ca; }
.answer-option input { display: none; }

.answer-radio {
  width: 20px; height: 20px;
  border-radius: 50%;
  border: 2px solid #c7d2fe;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: all 0.2s;
  background: white;
}
.answer-option.selected .answer-radio {
  border-color: #6366f1;
  background: #6366f1;
}
.radio-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: white;
  opacity: 0;
  transition: opacity 0.2s;
}
.answer-option.selected .radio-dot { opacity: 1; }

/* Footer */
.quiz-footer {
  background: white;
  border-radius: 0 0 24px 24px;
  border: 1px solid #e2e8f0;
  border-top: none;
  padding: 24px 32px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.footer-info {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  color: #64748b;
}
.footer-info svg { color: #94a3b8; flex-shrink: 0; }
.footer-info strong { color: #ef4444; }

.btn-submit {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 13px 26px;
  border: none;
  border-radius: 14px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
  transition: all 0.25s;
}
.btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(99, 102, 241, 0.45); }
.btn-submit:disabled { background: #c7d2fe; cursor: not-allowed; transform: none; box-shadow: none; }

.btn-loader {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

/* Shared buttons */
.btn-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  padding: 13px 26px;
  border: none;
  border-radius: 14px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
  transition: all 0.25s;
  text-decoration: none;
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(99, 102, 241, 0.45); }
.btn-ghost {
  display: inline-flex; align-items: center; justify-content: center;
  padding: 13px 22px;
  border: 2px solid #c7d2fe;
  border-radius: 14px;
  background: white;
  color: #6366f1;
  font-family: 'Manrope', sans-serif;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-ghost:hover { background: #eef2ff; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(15, 23, 42, 0.4);
  backdrop-filter: blur(6px);
  z-index: 1000;
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.modal {
  background: white;
  border-radius: 24px;
  padding: 36px;
  max-width: 420px;
  width: 100%;
  box-shadow: 0 24px 80px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  text-align: center;
}
.modal-icon {
  width: 56px; height: 56px;
  background: #fffbeb;
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
  color: #d97706;
}
.modal h3 {
  font-family: 'Playfair Display', serif;
  font-size: 22px;
  font-weight: 700;
  color: #1e1b4b;
  margin: 0;
}
.modal p { font-size: 14px; color: #64748b; margin: 0; line-height: 1.6; }
.modal p strong { color: #ef4444; }
.modal-actions { display: flex; gap: 12px; width: 100%; margin-top: 6px; }
.modal-actions .btn-ghost, .modal-actions .btn-primary { flex: 1; }

/* Result */
.result-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 80vh;
  position: relative;
  z-index: 1;
}
.result-card {
  background: white;
  border-radius: 28px;
  padding: 48px 40px;
  max-width: 480px;
  width: 100%;
  text-align: center;
  box-shadow: 0 8px 48px rgba(0,0,0,0.08);
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 18px;
}

.result-icon {
  width: 80px; height: 80px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}
.result-icon.success { background: #dcfce7; color: #16a34a; box-shadow: 0 0 0 10px #f0fdf4; }
.result-icon.fail { background: #fee2e2; color: #dc2626; box-shadow: 0 0 0 10px #fff5f5; }

.result-badge {
  font-size: 12px; font-weight: 700; letter-spacing: 0.08em;
  text-transform: uppercase; padding: 5px 14px; border-radius: 20px;
}
.success-badge { color: #16a34a; background: #dcfce7; border: 1px solid #a7f3d0; }
.fail-badge { color: #dc2626; background: #fee2e2; border: 1px solid #fca5a5; }

.result-title {
  font-family: 'Playfair Display', serif;
  font-size: 28px; font-weight: 700; color: #1e1b4b; margin: 0;
}

.score-wrap {
  position: relative;
  width: 140px; height: 140px;
}
.score-ring { display: block; }
.score-inner {
  position: absolute;
  inset: 0;
  display: flex; align-items: center; justify-content: center;
  flex-direction: row; gap: 2px;
}
.score-num { font-size: 36px; font-weight: 700; color: #1e1b4b; line-height: 1; }
.score-total { font-size: 18px; color: #94a3b8; font-weight: 600; align-self: flex-end; margin-bottom: 4px; }
.score-percent-label { font-size: 14px; color: #94a3b8; font-weight: 600; }

.result-stats {
  display: flex;
  align-items: center;
  gap: 24px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 16px 28px;
}
.res-stat { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.res-stat__num { font-size: 24px; font-weight: 700; color: #1e1b4b; }
.res-stat__num.correct { color: #22c55e; }
.res-stat__num.wrong { color: #ef4444; }
.res-stat__label { font-size: 12px; color: #94a3b8; font-weight: 600; }
.res-stat-div { width: 1px; height: 32px; background: #e2e8f0; }

/* Floating HUD */
.floating-hud {
  position: fixed;
  bottom: 28px;
  left: 28px;
  z-index: 999;
  pointer-events: none;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(99, 102, 241, 0.15);
  border-radius: 20px;
  padding: 14px 16px;
  box-shadow:
    0 8px 32px rgba(79, 70, 229, 0.12),
    0 2px 8px rgba(0,0,0,0.06),
    inset 0 1px 0 rgba(255,255,255,0.8);
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-width: 200px;
  font-family: 'Manrope', sans-serif;
}

.hud-section {
  display: flex;
  align-items: center;
  gap: 10px;
}


.hud-timer-row 
.hud-icon {
  width: 32px; height: 32px;
  background: #eef2ff;
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  color: #6366f1;
  flex-shrink: 0;
  transition: background 0.3s, color 0.3s;
}
.hud-timer-row.timer-warn .hud-icon {
  background: #fff5f5;
  color: #ef4444;
  animation: pulse-timer 1s ease-in-out infinite;
}

.hud-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #94a3b8;
  margin-bottom: 2px;
}
.hud-value {
  font-size: 15px;
  font-weight: 700;
  color: #1e1b4b;
  line-height: 1;
}
.hud-time {
  font-size: 18px;
  letter-spacing: 0.04em;
  font-variant-numeric: tabular-nums;
}
.hud-timer-row.timer-warn .hud-time { color: #ef4444; }

/* Progress row */
.hud-progress-row { align-items: center; }
.hud-ring { flex-shrink: 0; }
.hud-progress-info { flex: 1; }

.hud-sublabel {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 700;
  margin-top: 4px;
}
.hud-sublabel.left { color: #d97706; }
.hud-sublabel.left svg { color: #d97706; }
.hud-sublabel.done { color: #16a34a; }
.hud-sublabel.done svg { color: #16a34a; }

/* Mini bar */
.hud-bar-wrap {
  height: 4px;
  background: #f1f5f9;
  border-radius: 2px;
  overflow: hidden;
}
.hud-bar {
  height: 100%;
  background: linear-gradient(90deg, #818cf8, #6366f1);
  border-radius: 2px;
  transition: width 0.5s ease, background 0.4s ease;
}
.hud-bar.hud-bar-done { background: linear-gradient(90deg, #4ade80, #22c55e); }

.hud-divider { height: 1px; background: #f1f5f9; }

.hud-in-enter-active { transition: opacity 0.4s, transform 0.4s cubic-bezier(0.34,1.56,0.64,1); }
.hud-in-enter-from { opacity: 0; transform: translateY(16px) scale(0.9); }

/* Transitions */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Question media */
.q-content { flex: 1; }
.question-media {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.question-image {
  max-width: 100%;
  max-height: 320px;
  object-fit: contain;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  cursor: zoom-in;
  transition: transform 0.2s ease, box-shadow 0.2s;
}
.question-image:hover {
  transform: scale(1.02);
  box-shadow: 0 8px 28px rgba(79, 70, 229, 0.12);
}
.question-audio {
  width: 100%;
  border-radius: 10px;
  outline: none;
  accent-color: #6366f1;
}

/* Image modal */
.image-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(10, 10, 20, 0.82);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  cursor: zoom-out;
  padding: 24px;
}
.image-modal-content {
  max-width: 92vw;
  max-height: 88vh;
  object-fit: contain;
  border-radius: 16px;
  box-shadow: 0 24px 80px rgba(0,0,0,0.5);
  cursor: default;
}

/* Responsive */
@media (max-width: 600px) {
  .quiz-wrap { padding: 40px 12px 80px; }
  .quiz-header { padding: 22px 20px 18px; }
  .question-card { padding: 22px 20px; }
  .answers-list { padding-left: 0; }
  .quiz-footer { padding: 20px; }
  .result-card { padding: 36px 24px; }
}
</style>