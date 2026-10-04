<template>
  <div class="quiz-page">
    <StatePanel
      v-if="loading"
      tone="neutral"
      eyebrow="Олимпиада"
      title="Загружаем олимпиаду"
      description="Подготавливаем вопросы, правила и данные участника перед стартом."
    />

    <StatePanel
      v-else-if="loadError"
      tone="warning"
      eyebrow="Доступ к олимпиаде"
      :title="loadErrorTitle"
      :description="loadErrorMessage"
    >
      <template #actions>
        <RouterLink class="ds-btn ds-btn-ghost" to="/profile">Вернуться в кабинет</RouterLink>
        <RouterLink class="ds-btn ds-btn-primary" to="/subject">Открыть выбор олимпиад</RouterLink>
      </template>
    </StatePanel>

    <template v-else-if="result">
      <section class="result-card" :class="result.status === 'passed' ? 'is-passed' : 'is-failed'" aria-labelledby="result-title">
        <div class="result-confetti" aria-hidden="true">
          <i v-for="n in 14" :key="n" :style="{ '--n': n }"></i>
        </div>

        <div class="result-ring" :style="{ '--p': result.percent }" role="img" :aria-label="`${result.percent}% правильных`">
          <div class="result-ring__inner">
            <svg v-if="result.status === 'passed'" width="40" height="40" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 3h10v2h3a1 1 0 0 1 1 1v2a5 5 0 0 1-4.3 4.95A5 5 0 0 1 13 15.9V18h3v3H8v-3h3v-2.1a5 5 0 0 1-3.7-2.95A5 5 0 0 1 3 8V6a1 1 0 0 1 1-1h3zm10 4v3.8A3 3 0 0 0 19 8V7zM5 7v1a3 3 0 0 0 2 2.8V7z"/></svg>
            <svg v-else width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>
            <strong>{{ result.percent }}%</strong>
          </div>
        </div>

        <div class="result-heading">
          <p class="eyebrow">{{ result.status === 'passed' ? 'Поздравляем!' : 'Олимпиада завершена' }}</p>
          <h1 id="result-title">{{ result.status === 'passed' ? 'Вы прошли олимпиаду!' : 'Результат сохранён' }}</h1>
        </div>

        <div class="result-score-row">
          <div class="result-score-chip">
            <span class="result-score-main">{{ result.score }}<span class="result-score-total"> / {{ result.total }}</span></span>
            <span class="result-score-pct">{{ result.percent }}% правильных</span>
          </div>
          <StatusBadge :label="result.status === 'passed' ? 'Пройдено' : 'Не пройдено'" :tone="result.status === 'passed' ? 'success' : 'warning'" />
        </div>

        <div class="result-actions">
          <RouterLink class="ds-btn ds-btn-primary ds-btn-lg" to="/results">Перейти к результатам</RouterLink>
          <button type="button" class="ds-btn ds-btn-sun ds-btn-lg" @click="downloadCertificate">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            Скачать сертификат
          </button>
          <RouterLink v-if="result.mistakes_url" class="ds-btn ds-btn-ghost ds-btn-lg" :to="result.mistakes_url">Работа над ошибками</RouterLink>
        </div>
      </section>
    </template>

    <StatePanel
      v-else-if="violationMessage"
      tone="danger"
      eyebrow="Попытка сброшена"
      title="Тест остановлен"
      :description="violationMessage"
    >
      <template #actions>
        <RouterLink class="ds-btn ds-btn-primary" to="/profile">Вернуться в профиль</RouterLink>
        <RouterLink class="ds-btn ds-btn-ghost" to="/help-desk">Подать апелляцию или написать в поддержку</RouterLink>
      </template>
    </StatePanel>

    <template v-else-if="quiz">
      <!-- ===== Перед стартом ===== -->
      <section v-if="!examStarted" class="intro-card" aria-labelledby="intro-title">
        <div class="intro-head">
          <span class="intro-badge" aria-hidden="true">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2z"/><path d="M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z"/></svg>
          </span>
          <div>
            <p class="eyebrow">{{ quiz.subject?.name || 'Олимпиада' }}</p>
            <h1 id="intro-title">{{ quiz.title }}</h1>
          </div>
        </div>

        <p class="description">{{ quiz.description || 'Перед началом внимательно ознакомьтесь с правилами прохождения олимпиады.' }}</p>
        <p v-if="quiz.child" class="child-chip">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Участник: {{ quiz.child.full_name }} · {{ quiz.child.grade || 'без класса' }}
        </p>

        <dl class="intro-grid">
          <div class="intro-item"><dt>Категория</dt><dd>{{ quiz.category?.label }}</dd></div>
          <div v-if="quiz.category?.display_range && quiz.category.display_range !== quiz.category.label" class="intro-item"><dt>Классы</dt><dd>{{ quiz.category.display_range }}</dd></div>
          <div class="intro-item intro-item--blue"><dt>Вопросов</dt><dd>{{ quiz.questions.length }}</dd></div>
          <div class="intro-item intro-item--sun"><dt>Время</dt><dd>{{ quiz.time_limit }} минут</dd></div>
        </dl>

        <StatePanel
          v-if="!isMobile"
          tone="warning"
          eyebrow="Важно перед стартом"
          :title="quiz.warning"
          description="Перед началом убедитесь, что у участника есть свободное время, стабильный интернет и готовность пройти олимпиаду в одном окне браузера."
        />

        <ul class="rules-list">
          <li v-for="rule in quiz.warning_rules || defaultRules" :key="rule">
            <span class="rules-list__icon" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 8v5M12 16.5h.01"/></svg>
            </span>
            {{ rule }}
          </li>
        </ul>

        <label class="confirm-row" :class="{ 'is-checked': rulesAccepted }">
          <input v-model="rulesAccepted" type="checkbox" class="confirm-row__input" />
          <span class="confirm-row__box" aria-hidden="true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
          </span>
          <span>Я ознакомился с правилами и понимаю, что нарушение приведёт к аннулированию попытки.</span>
        </label>

        <p v-if="fullscreenError" class="ds-msg error" role="alert">{{ fullscreenError }}</p>
        <p v-if="submitError" class="ds-msg error" role="alert">{{ submitError }}</p>

        <div class="intro-actions">
          <RouterLink class="ds-btn ds-btn-ghost ds-btn-lg" to="/profile">Вернуться в кабинет</RouterLink>
          <button type="button" class="ds-btn ds-btn-primary ds-btn-lg intro-start" :disabled="!rulesAccepted" @click="startExam">
            {{ isMobile ? 'Начать тест' : 'Начать тест в полноэкранном режиме' }}
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
        </div>
      </section>

      <!-- ===== Экзамен ===== -->
      <section v-else class="exam-shell">
        <!-- Мобильная верхняя панель -->
        <div v-if="isMobile" class="mobile-topbar">
          <RouterLink to="/profile" class="mobile-back" aria-label="Выйти в кабинет">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
          </RouterLink>
          <div class="mobile-topbar-center">
            <span class="mobile-title">{{ quiz.subject?.name || 'Олимпиада' }}</span>
            <span class="mobile-qcount">Вопрос {{ currentQuestionIndex + 1 }} из {{ quiz.questions.length }}</span>
          </div>
          <span class="mobile-timer" :class="{ warn: timeLeft < 300 }" role="timer" aria-live="off">{{ formatTime(timeLeft) }}</span>
        </div>

        <div class="exam-layout">
          <div class="exam-main">
            <header v-if="!isMobile" class="exam-header">
              <p class="eyebrow">{{ quiz.subject?.name || 'Олимпиада' }}</p>
              <h1>{{ quiz.title }}</h1>
            </header>

            <StatePanel
              v-if="submitError"
              tone="warning"
              eyebrow="Отправка ответов"
              :title="submitError"
              description="Пожалуйста, не закрывайте страницу. Можно попробовать отправить ответы ещё раз."
            />

            <!-- Карта вопросов на мобильных -->
            <section v-if="isMobile" ref="questionNavRef" class="progress-card progress-card--mobile">
              <div class="progress-track"><div class="progress-fill" :style="{ width: `${progressPercent}%` }"></div></div>
              <div ref="questionMapScrollerRef" class="question-map-scroller">
                <div class="question-map question-map--row">
                  <button
                    v-for="(question, index) in quiz.questions"
                    :key="question.id"
                    type="button"
                    class="question-dot"
                    :class="questionState(index)"
                    :aria-label="`Вопрос ${index + 1}`"
                    :aria-current="index === currentQuestionIndex ? 'step' : undefined"
                    @click="goToQuestion(index)"
                  >
                    {{ index + 1 }}
                  </button>
                </div>
              </div>
            </section>

            <article ref="questionCardRef" class="question-card" :key="currentQuestion.id" aria-live="polite">
              <div class="question-header">
                <span class="question-index">{{ currentQuestionIndex + 1 }}</span>
                <div class="question-copy">
                  <p class="question-hint">Выберите один вариант ответа</p>
                  <h2 class="question-title">{{ questionText }}</h2>
                </div>
              </div>

              <div v-if="currentQuestion.image" class="question-image-shell">
                <img :src="currentQuestion.image" :alt="`Иллюстрация к вопросу ${currentQuestionIndex + 1}`" class="question-image" />
              </div>

              <div class="answer-list" role="radiogroup" :aria-label="`Варианты ответа на вопрос ${currentQuestionIndex + 1}`">
                <label
                  v-for="answer in currentQuestion.answers"
                  :key="answer.id"
                  class="answer-option"
                  :class="{ selected: userAnswers[currentQuestion.id] === answer.id }"
                >
                  <input
                    v-model="userAnswers[currentQuestion.id]"
                    type="radio"
                    class="answer-input"
                    :name="`question-${currentQuestion.id}`"
                    :value="answer.id"
                    @change="markVisited(currentQuestionIndex)"
                  />
                  <span class="answer-label">{{ answer.label }}</span>
                  <span class="answer-text">{{ decodeText(answer.answer) }}</span>
                  <span class="answer-check" aria-hidden="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                  </span>
                </label>
              </div>
            </article>

            <footer class="sticky-footer">
              <button type="button" class="ds-btn ds-btn-ghost nav-prev" :disabled="currentQuestionIndex === 0" :aria-label="isMobile ? 'Назад' : undefined" @click="goPrev">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                <span v-if="!isMobile">Назад</span>
              </button>
              <span v-if="!isMobile" class="kbd-hint">Подсказка: цифры 1–{{ Math.min(currentQuestion.answers.length, 9) }} выбирают ответ, стрелки ← → листают вопросы</span>
              <button v-if="!isLastQuestion" type="button" class="ds-btn ds-btn-primary nav-next" @click="goNext">
                Следующий вопрос
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
              </button>
              <button v-else type="button" class="ds-btn ds-btn-sun nav-next" :disabled="submitting" @click="confirmSubmitQuiz">{{ submitting ? 'Отправляем...' : 'Завершить тест' }}</button>
            </footer>
          </div>

          <!-- Боковая панель (компьютер) -->
          <aside v-if="!isMobile" ref="questionNavRef" class="exam-side">
            <div class="side-timer" :class="{ warn: timeLeft < 300 }" role="timer" aria-live="off">
              <span class="side-timer__label">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2M9 2h6"/></svg>
                Время
              </span>
              <strong>{{ formatTime(timeLeft) }}</strong>
            </div>

            <div class="side-progress">
              <div class="progress-ring" :style="{ '--p': progressPercent }">
                <span>{{ progressPercent }}%</span>
              </div>
              <dl class="side-progress__meta">
                <div><dt>Отвечено</dt><dd>{{ answeredCount }}/{{ quiz.questions.length }}</dd></div>
                <div><dt>Текущий</dt><dd>{{ currentQuestionIndex + 1 }}/{{ quiz.questions.length }}</dd></div>
              </dl>
            </div>

            <div class="side-map">
              <p class="side-map__title">Прогресс</p>
              <div ref="questionMapScrollerRef" class="question-map question-map--grid">
                <button
                  v-for="(question, index) in quiz.questions"
                  :key="question.id"
                  type="button"
                  class="question-dot"
                  :class="questionState(index)"
                  :aria-label="`Вопрос ${index + 1}`"
                  :aria-current="index === currentQuestionIndex ? 'step' : undefined"
                  @click="goToQuestion(index)"
                >
                  {{ index + 1 }}
                </button>
              </div>
              <ul class="side-map__legend" aria-hidden="true">
                <li><i class="lg lg--answered"></i>Отвечено</li>
                <li><i class="lg lg--current"></i>Текущий</li>
                <li><i class="lg lg--empty"></i>Без ответа</li>
              </ul>
            </div>

            <button type="button" class="side-fullscreen" @click="requestFullscreen">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
              Режим: {{ isFullscreen ? 'Полный экран' : 'Развернуть' }}
            </button>
          </aside>
        </div>
      </section>
    </template>

    <Teleport to="body">
      <Transition name="dialog">
        <div v-if="showConfirmModal" class="confirm-overlay" @click.self="onConfirmNo" @keydown.esc="onConfirmNo">
          <div class="confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-body">
            <span class="confirm-dialog__icon" aria-hidden="true">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1zM4 22v-7"/></svg>
            </span>
            <p id="confirm-title" class="confirm-dialog__title">Завершить тест?</p>
            <p id="confirm-body" class="confirm-dialog__body">После отправки ответы изменить нельзя.</p>
            <p v-if="quiz" class="confirm-dialog__stat" :class="{ 'is-incomplete': answeredCount < quiz.questions.length }">
              Отвечено {{ answeredCount }} из {{ quiz.questions.length }}
            </p>
            <div class="confirm-dialog__actions">
              <button ref="confirmCancelRef" type="button" class="ds-btn ds-btn-ghost" @click="onConfirmNo">Отмена</button>
              <button type="button" class="ds-btn ds-btn-primary" :disabled="submitting" @click="onConfirmYes">{{ submitting ? 'Отправляем...' : 'Завершить' }}</button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import api from '../js/api'
import { useUserStore } from '../stores/user'
import StatePanel from '../components/StatePanel.vue'
import StatusBadge from '../components/StatusBadge.vue'

const route = useRoute()
const userStore = useUserStore()

const loading = ref(true)
const loadError = ref(false)
const loadErrorTitle = ref('Не удалось открыть олимпиаду')
const loadErrorMessage = ref('')
const quiz = ref(null)
const result = ref(null)
const violationMessage = ref('')
const submitting = ref(false)
const submitError = ref('')
const userAnswers = ref({})
const timeLeft = ref(0)
const currentQuestionIndex = ref(0)
const visitedQuestions = ref(new Set())
const skippedQuestions = ref(new Set())
const examStarted = ref(false)
const fullscreenError = ref('')
const isFullscreen = ref(false)
const isMobile = ref(false)
const recomputeIsMobile = () => {
  const ua = navigator.userAgent || ''
  const uaMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(ua)
  const touchTablet = (navigator.maxTouchPoints || 0) > 1 && /Macintosh/.test(ua)
  isMobile.value = uaMobile || touchTablet || window.innerWidth <= 640
}
recomputeIsMobile()
const rulesAccepted = ref(false)
const questionNavRef = ref(null)
const questionCardRef = ref(null)
const questionMapScrollerRef = ref(null)
const defaultRules = [
  'Запрещено переключать вкладку, окно, выходить из полноэкранного режима или сворачивать браузер.',
  'Нельзя использовать подсказки, списывать и обращаться к сторонней помощи.',
  'При нарушении правил попытка аннулируется автоматически.',
]

let timerId = null
let heartbeatId = null
let violated = false

const clearHeartbeat = () => {
  if (heartbeatId) { clearInterval(heartbeatId); heartbeatId = null }
}

let heartbeatFailures = 0
const MAX_HEARTBEAT_FAILURES = 3

const startHeartbeat = () => {
  clearHeartbeat()
  heartbeatFailures = 0
  heartbeatId = window.setInterval(async () => {
    if (!quiz.value || !examStarted.value || violated || result.value) return
    try {
      const { data } = await api.post(`/quiz/${quiz.value.id}/start`, {
        child_profile_id: activeChildId.value,
      })
      heartbeatFailures = 0
      const serverRemaining = data.remaining_seconds ?? 0
      if (serverRemaining <= 0) {
        clearHeartbeat()
        await submitQuiz()
        return
      }
      if (Math.abs(timeLeft.value - serverRemaining) > 15) {
        timeLeft.value = serverRemaining
      }
    } catch {
      heartbeatFailures++
      if (heartbeatFailures >= MAX_HEARTBEAT_FAILURES) {
        // Server unreachable for 3 min — client timer keeps running, submit on expire
        clearHeartbeat()
      }
    }
  }, 60000)
}

const decodeText = (() => {
  let el = null
  return (str) => {
    if (str == null) return ''
    const s = String(str)
    if (typeof document === 'undefined') return s
    if (!el) el = document.createElement('textarea')
    el.innerHTML = s
    return el.value
      .replace(/\u00A0/g, ' ')
      .replace(/\u202F/g, ' ')
      .replace(/\u2007/g, ' ')
  }
})()

const currentQuestion = computed(() => quiz.value?.questions[currentQuestionIndex.value] || null)
const questionText = computed(() => decodeText(currentQuestion.value?.question))
const isLastQuestion = computed(() => {
  if (!quiz.value?.questions?.length) return false
  return currentQuestionIndex.value === quiz.value.questions.length - 1
})
const answeredCount = computed(() => Object.keys(userAnswers.value).length)
const skippedCount = computed(() => skippedQuestions.value.size)
const progressPercent = computed(() => {
  if (!quiz.value?.questions.length) return 0
  return Math.round((answeredCount.value / quiz.value.questions.length) * 100)
})

const activeChildId = computed(() => String(route.query.childId || userStore.selectedChildId || '') || null)

const storageKey = computed(() => `quiz_draft_${route.params.subjectId}_${activeChildId.value || 'self'}`)

const saveAnswers = () => {
  if (!examStarted.value || violated || result.value) return
  try { localStorage.setItem(storageKey.value, JSON.stringify(userAnswers.value)) } catch {}
}

const restoreAnswers = () => {
  try {
    const saved = localStorage.getItem(storageKey.value)
    if (saved) userAnswers.value = JSON.parse(saved)
  } catch {}
}

const clearSavedAnswers = () => {
  try { localStorage.removeItem(storageKey.value) } catch {}
}

watch(userAnswers, saveAnswers, { deep: true })

const formatTime = (seconds) => {
  const safeSeconds = Math.max(seconds, 0)
  const minutes = Math.floor(safeSeconds / 60)
  const remainder = safeSeconds % 60
  return `${String(minutes).padStart(2, '0')}:${String(remainder).padStart(2, '0')}`
}

const clearTimer = () => {
  if (timerId) {
    clearInterval(timerId)
    timerId = null
  }
}

const startTimerFromSeconds = (seconds) => {
  clearTimer()
  timeLeft.value = seconds
  timerId = window.setInterval(() => {
    if (timeLeft.value <= 0) {
      clearTimer()
      submitQuiz()
      return
    }
    timeLeft.value -= 1
  }, 1000)
}

const markVisited = (index) => {
  visitedQuestions.value.add(index)
  const questionId = quiz.value?.questions[index]?.id
  if (questionId && userAnswers.value[questionId]) {
    skippedQuestions.value.delete(index)
  }
}

const questionState = (index) => {
  if (index === currentQuestionIndex.value) return 'current'
  const question = quiz.value?.questions[index]
  if (!question) return 'unvisited'
  if (userAnswers.value[question.id]) return 'answered'
  if (skippedQuestions.value.has(index)) return 'skipped'
  if (visitedQuestions.value.has(index)) return 'visited'
  return 'unvisited'
}

const scrollActiveDotIntoView = async (index) => {
  await nextTick()
  const scroller = questionMapScrollerRef.value
  if (!scroller) return
  const dots = scroller.querySelectorAll('.question-dot')
  const dot = dots[index]
  if (!dot) return
  const targetLeft = dot.offsetLeft - scroller.offsetWidth / 2 + dot.offsetWidth / 2
  scroller.scrollTo({ left: Math.max(0, targetLeft), behavior: 'smooth' })
}

watch(currentQuestionIndex, (index) => scrollActiveDotIntoView(index))

const scrollToQuestionTop = async (behavior = 'smooth') => {
  await nextTick()

  // На мобильных прокручиваем к карте вопросов, на компьютере — к самому вопросу.
  const target = (isMobile.value ? questionNavRef.value : questionCardRef.value) || questionCardRef.value
  if (!target) {
    window.scrollTo({ top: 0, behavior })
    return
  }

  const headerOffset = document.querySelector('.header')?.offsetHeight ?? 72
  const top = target.getBoundingClientRect().top + window.scrollY - headerOffset - 12
  window.scrollTo({ top: Math.max(top, 0), behavior })
}

const goToQuestion = (index) => {
  currentQuestionIndex.value = index
  markVisited(index)
  scrollToQuestionTop()
}

const goPrev = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value -= 1
    markVisited(currentQuestionIndex.value)
    scrollToQuestionTop()
  }
}

const goNext = () => {
  if (currentQuestionIndex.value < quiz.value.questions.length - 1) {
    currentQuestionIndex.value += 1
    markVisited(currentQuestionIndex.value)
    scrollToQuestionTop()
  }
}

const skipQuestion = () => {
  skippedQuestions.value.add(currentQuestionIndex.value)
  visitedQuestions.value.add(currentQuestionIndex.value)
  if (currentQuestionIndex.value < quiz.value.questions.length - 1) {
    currentQuestionIndex.value += 1
    markVisited(currentQuestionIndex.value)
    scrollToQuestionTop()
  }
}

const showConfirmModal = ref(false)
const confirmCancelRef = ref(null)

const confirmSubmitQuiz = async () => {
  if (submitting.value || violated) return
  showConfirmModal.value = true
  await nextTick()
  confirmCancelRef.value?.focus()
}

// Клавиатура: цифры выбирают вариант, стрелки листают вопросы, Esc закрывает окно подтверждения.
const handleExamKeydown = (event) => {
  if (!examStarted.value || result.value || violated || !currentQuestion.value) return
  if (event.altKey || event.ctrlKey || event.metaKey) return

  if (showConfirmModal.value) {
    if (event.key === 'Escape') onConfirmNo()
    return
  }

  // Стрелки на сфокусированной радиокнопке уже переключают вариант — не перехватываем их.
  const tag = event.target?.tagName
  if ((tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') && !/^[1-9]$/.test(event.key)) return

  if (event.key === 'ArrowRight' && !isLastQuestion.value) {
    event.preventDefault()
    goNext()
  } else if (event.key === 'ArrowLeft' && currentQuestionIndex.value > 0) {
    event.preventDefault()
    goPrev()
  } else if (/^[1-9]$/.test(event.key)) {
    const answer = currentQuestion.value.answers[Number(event.key) - 1]
    if (answer) {
      userAnswers.value[currentQuestion.value.id] = answer.id
      markVisited(currentQuestionIndex.value)
    }
  }
}

const onConfirmYes = async () => {
  showConfirmModal.value = false
  await submitQuiz()
}

const onConfirmNo = () => {
  showConfirmModal.value = false
}

const requestFullscreen = async () => {
  fullscreenError.value = ''

  if (isMobile.value) {
    isFullscreen.value = true
    return true
  }

  const element = document.documentElement

  if (!element.requestFullscreen) {
    fullscreenError.value = 'Браузер не поддерживает полноэкранный режим.'
    return false
  }

  try {
    if (!document.fullscreenElement) {
      await element.requestFullscreen()
    }
    isFullscreen.value = !!document.fullscreenElement
    return isFullscreen.value
  } catch {
    fullscreenError.value = 'Не удалось включить полноэкранный режим. Разрешите его в браузере и попробуйте снова.'
    return false
  }
}

const registerViolation = async (reason = 'window_focus_lost') => {
  if (violated || !quiz.value || result.value || !examStarted.value) return

  violated = true
  clearTimer()
  clearHeartbeat()
  clearSavedAnswers()
  userAnswers.value = {}
  violationMessage.value = 'Вы переключились на другое окно, вкладку или вышли из полноэкранного режима. По правилам олимпиады попытка аннулирована. Если это сработало ошибочно, можно сразу подать апелляцию через поддержку.'

  try {
    await api.post(`/quiz/${quiz.value.id}/violate`, {
      reason,
      child_profile_id: activeChildId.value,
    })
  } catch (error) {
    console.error('Violation save error:', error)
  }
}

// Mobile browsers fire visibilitychange/blur during normal use (notifications,
// soft keyboard, app switcher), so we exempt them entirely. Desktop gets a small
// grace period to absorb transient blurs (devtools focus, alt-tab to copy).
let hiddenSince = 0
const HIDDEN_GRACE_MS = 8000

const handleVisibilityLoss = () => {
  if (isMobile.value) return
  if (document.hidden) {
    hiddenSince = Date.now()
    setTimeout(() => {
      if (document.hidden && hiddenSince && Date.now() - hiddenSince >= HIDDEN_GRACE_MS) {
        registerViolation('tab_hidden')
      }
    }, HIDDEN_GRACE_MS + 100)
  } else {
    hiddenSince = 0
  }
}

const handleWindowBlur = () => {
  if (isMobile.value) return
  setTimeout(() => {
    if (!document.hasFocus() && examStarted.value && !violated && !result.value) {
      registerViolation('window_blur')
    }
  }, 1500)
}

const handleFullscreenChange = () => {
  if (isMobile.value) return
  isFullscreen.value = !!document.fullscreenElement
  if (examStarted.value && !isFullscreen.value) {
    registerViolation('fullscreen_exit')
  }
}

const loadQuiz = async () => {
  loading.value = true
  loadError.value = false
  loadErrorTitle.value = 'Не удалось открыть олимпиаду'
  loadErrorMessage.value = ''

  try {
    await userStore.fetchUser()
    const { data } = await api.get(`/quiz/${route.params.subjectId}`, {
      params: activeChildId.value ? { child_profile_id: activeChildId.value } : {},
    })
    quiz.value = data

    if (data.child_profile_id) {
      userStore.setSelectedChild(data.child_profile_id)
    }

    if (data.already_submitted) {
      loadError.value = true
      loadErrorTitle.value = 'Олимпиада уже завершена'
      loadErrorMessage.value = 'Для этого участника результат уже сохранён. Откройте страницу результатов, чтобы увидеть итог и сертификат.'
      return
    }

    visitedQuestions.value = new Set([0])

    if (data.attempt_started_at) {
      examStarted.value = true
      rulesAccepted.value = true
      restoreAnswers()
      startTimerFromSeconds(data.remaining_seconds || 0)
      startHeartbeat()
      await scrollToQuestionTop('auto')
    }
  } catch (error) {
    loadError.value = true
    loadErrorMessage.value = error.response?.data?.message || 'Не удалось загрузить олимпиаду.'
  } finally {
    loading.value = false
  }
}

const startExam = async () => {
  if (!rulesAccepted.value) return
  const fullscreenOk = await requestFullscreen()
  if (!fullscreenOk) return
  submitError.value = ''

  try {
    const { data } = await api.post(`/quiz/${quiz.value.id}/start`, {
      child_profile_id: activeChildId.value,
    })

    examStarted.value = true
    markVisited(0)
    startTimerFromSeconds(data.remaining_seconds || (quiz.value.time_limit || 60) * 60)
    startHeartbeat()
    await scrollToQuestionTop('auto')
  } catch (error) {
    submitError.value = error.response?.data?.message || 'Не удалось запустить олимпиаду.'
  }
}

const submitQuiz = async () => {
  if (!quiz.value || submitting.value || violated) return
  submitting.value = true
  submitError.value = ''
  clearTimer()

  try {
    const { data } = await api.post(`/quiz/${quiz.value.id}/submit`, {
      child_profile_id: activeChildId.value,
      answers: userAnswers.value,
    })
    result.value = data
    clearHeartbeat()
    clearSavedAnswers()
    if (document.fullscreenElement) {
      await document.exitFullscreen().catch(() => {})
    }
  } catch (error) {
    submitError.value = error.response?.data?.message || 'Не удалось отправить ответы.'
    startTimerFromSeconds(timeLeft.value || 60)
  } finally {
    submitting.value = false
  }
}

const downloadCertificate = async () => {
  if (!result.value?.certificate_url) return

  const { data, headers } = await api.get(result.value.certificate_url.replace('/api', ''), {
    responseType: 'blob',
  })

  const blob = new Blob([data], { type: headers['content-type'] || 'application/pdf' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `certificate-result-${result.value.id || 'latest'}.pdf`
  link.click()
  window.URL.revokeObjectURL(url)
}

onMounted(async () => {
  await loadQuiz()
  document.addEventListener('visibilitychange', handleVisibilityLoss)
  document.addEventListener('fullscreenchange', handleFullscreenChange)
  window.addEventListener('blur', handleWindowBlur)
  window.addEventListener('keydown', handleExamKeydown)
  window.addEventListener('resize', recomputeIsMobile)
  window.addEventListener('orientationchange', recomputeIsMobile)
})

onUnmounted(async () => {
  clearTimer()
  clearHeartbeat()
  document.removeEventListener('visibilitychange', handleVisibilityLoss)
  document.removeEventListener('fullscreenchange', handleFullscreenChange)
  window.removeEventListener('blur', handleWindowBlur)
  window.removeEventListener('keydown', handleExamKeydown)
  window.removeEventListener('resize', recomputeIsMobile)
  window.removeEventListener('orientationchange', recomputeIsMobile)

  if (document.fullscreenElement) {
    try {
      await document.exitFullscreen()
    } catch {}
  }
})
</script>

<style scoped>
.quiz-page {
  min-height: 100dvh;
  width: 100%;
  overflow-x: hidden;
  padding: 32px 20px 64px;
  background:
    radial-gradient(800px 400px at 50% -120px, color-mix(in srgb, var(--brand) 10%, transparent), transparent 70%),
    var(--bg);
  color: var(--text);
  touch-action: pan-y;
}

.eyebrow {
  margin: 0 0 6px;
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

h1 {
  font-size: clamp(24px, 3.4vw, 36px);
}

/* ================= Перед стартом ================= */
.intro-card {
  max-width: 820px;
  margin: 0 auto;
  display: grid;
  gap: 22px;
  padding: clamp(24px, 4vw, 40px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-md);
}

.intro-head {
  display: flex;
  align-items: center;
  gap: 18px;
}

.intro-badge {
  width: 64px;
  height: 64px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 20px;
  background: var(--brand);
  color: var(--sun);
  box-shadow: var(--shadow-brand);
  transform: rotate(-5deg);
}

.description {
  color: var(--text-secondary);
  font-size: 17px;
  line-height: 1.6;
}

.child-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  justify-self: start;
  padding: 8px 14px;
  border-radius: var(--radius-sm);
  background: var(--brand-soft);
  color: var(--brand-ink);
  font-size: 15px;
  font-weight: 600;
}

.intro-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 12px;
  margin: 0;
}

.intro-item {
  padding: 16px 18px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.intro-item dt {
  margin-bottom: 4px;
  color: var(--text-secondary);
  font-size: 14px;
}

.intro-item dd {
  margin: 0;
  font-size: 18px;
  font-weight: 700;
}

.intro-item--blue { background: var(--brand-soft); }
.intro-item--blue dd { color: var(--brand-ink); font-family: var(--font-display); font-size: 22px; }
.intro-item--sun { background: var(--sun-soft); }
.intro-item--sun dd { color: var(--sun-ink); }

.rules-list {
  display: grid;
  gap: 10px;
  list-style: none;
}

.rules-list li {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  color: var(--text);
  font-size: 16px;
  line-height: 1.55;
}

.rules-list__icon {
  width: 24px;
  height: 24px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  margin-top: 1px;
  border-radius: 8px;
  background: var(--warning-soft);
  color: var(--warning-ink);
}

/* Крупный понятный чекбокс */
.confirm-row {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 16px 18px;
  border-radius: var(--radius-md);
  border: 2px solid var(--border-strong);
  background: var(--card);
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
  line-height: 1.5;
  transition: border-color var(--dur) ease, background-color var(--dur) ease;
}

.confirm-row:hover { border-color: color-mix(in srgb, var(--brand) 50%, var(--border-strong)); }

.confirm-row.is-checked {
  border-color: var(--brand);
  background: var(--brand-softer);
}

.confirm-row__input {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
}

.confirm-row__box {
  width: 26px;
  height: 26px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 8px;
  border: 2px solid var(--border-strong);
  background: var(--card);
  color: transparent;
  transition: background-color var(--dur) ease, border-color var(--dur) ease, color var(--dur) ease, transform var(--dur) var(--ease-out);
}

.confirm-row.is-checked .confirm-row__box {
  border-color: var(--brand);
  background: var(--brand);
  color: #ffffff;
  transform: scale(1.05);
}

.confirm-row:has(.confirm-row__input:focus-visible) {
  box-shadow: var(--focus-ring);
}

.intro-actions {
  display: flex;
  justify-content: flex-end;
  flex-wrap: wrap;
  gap: 12px;
}

.intro-start svg { transition: transform var(--dur) var(--ease-out); }
@media (hover: hover) and (pointer: fine) {
  .intro-start:not(:disabled):hover svg { transform: translateX(4px); }
}

/* ================= Экзамен ================= */
.exam-shell {
  max-width: 1200px;
  margin: 0 auto;
}

.exam-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 300px;
  gap: 20px;
  align-items: start;
}

.exam-main {
  display: grid;
  grid-template-columns: minmax(0, 1fr);
  gap: 16px;
  min-width: 0;
}

.exam-header h1 {
  font-size: clamp(20px, 2.4vw, 26px);
}

.question-card {
  min-width: 0;
  padding: clamp(22px, 3vw, 36px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  overflow-wrap: anywhere;
  animation: questionIn 360ms var(--ease-out) both;
}

@keyframes questionIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: none; }
}

.question-header {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 22px;
}

.question-index {
  width: 48px;
  height: 48px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 15px;
  background: var(--brand);
  color: #ffffff;
  font-family: var(--font-display);
  font-size: 18px;
  font-weight: 700;
}

.question-copy { flex: 1; min-width: 0; }

.question-hint {
  margin-bottom: 6px;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 500;
}

.question-title {
  font-family: var(--font-sans);
  font-size: clamp(19px, 2.2vw, 24px);
  font-weight: 600;
  line-height: 1.45;
  letter-spacing: -0.01em;
  word-break: break-word;
}

.question-image-shell {
  display: flex;
  align-items: center;
  justify-content: center;
  max-height: 380px;
  margin-bottom: 22px;
  padding: 14px;
  border-radius: var(--radius-lg);
  background: var(--bg);
  overflow: hidden;
}

.question-image {
  max-width: 100%;
  max-height: 350px;
  object-fit: contain;
  border-radius: var(--radius-sm);
}

/* Варианты ответа: крупные карточки */
.answer-list {
  display: grid;
  gap: 12px;
}

.answer-option {
  position: relative;
  display: grid;
  grid-template-columns: 40px minmax(0, 1fr) 28px;
  align-items: center;
  gap: 14px;
  min-height: 64px;
  padding: 12px 16px 12px 12px;
  border-radius: var(--radius-md);
  border: 2px solid var(--border);
  background: var(--card);
  cursor: pointer;
  transition: border-color var(--dur) ease, background-color var(--dur) ease, transform var(--dur-fast) var(--ease-out), box-shadow var(--dur) ease;
}

@media (hover: hover) and (pointer: fine) {
  .answer-option:hover:not(.selected) {
    border-color: color-mix(in srgb, var(--brand) 45%, var(--border));
    background: var(--brand-softer);
  }
}

.answer-option:active { transform: scale(0.99); }

.answer-option.selected {
  border-color: var(--brand);
  background: var(--brand-soft);
  box-shadow: 0 6px 18px rgba(43, 91, 245, 0.14);
}

.answer-option:has(.answer-input:focus-visible) {
  box-shadow: var(--focus-ring);
}

.answer-input {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
  pointer-events: none;
}

.answer-label {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  border-radius: 12px;
  background: var(--bg-alt);
  color: var(--text-secondary);
  font-size: 16px;
  font-weight: 700;
  transition: background-color var(--dur) ease, color var(--dur) ease;
}

.answer-option.selected .answer-label {
  background: var(--brand);
  color: #ffffff;
}

.answer-text {
  min-width: 0;
  font-size: 17px;
  line-height: 1.5;
  word-break: break-word;
}

.answer-check {
  width: 28px;
  height: 28px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  border: 2px solid var(--border-strong);
  color: transparent;
  transition: background-color var(--dur) ease, border-color var(--dur) ease, color var(--dur) ease, transform var(--dur) var(--ease-out);
}

.answer-option.selected .answer-check {
  border-color: var(--brand);
  background: var(--brand);
  color: #ffffff;
  transform: scale(1.08);
}

/* Нижняя панель навигации */
.sticky-footer {
  position: sticky;
  bottom: 16px;
  z-index: 3;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: var(--radius-lg);
  background: color-mix(in srgb, var(--card) 94%, transparent);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-md);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.kbd-hint {
  flex: 1;
  color: var(--text-tertiary);
  font-size: 13px;
  text-align: center;
}

.nav-next { margin-left: auto; }

/* Боковая панель */
.exam-side {
  position: sticky;
  top: 20px;
  display: grid;
  gap: 14px;
}

.side-timer,
.side-progress,
.side-map {
  padding: 18px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-xs);
}

.side-timer {
  display: grid;
  gap: 4px;
  background: var(--brand);
  border-color: transparent;
  color: #ffffff;
  box-shadow: var(--shadow-brand);
  transition: background-color var(--dur-slow) ease;
}

.side-timer__label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 500;
  opacity: 0.85;
}

.side-timer strong {
  font-family: var(--font-display);
  font-size: 40px;
  line-height: 1.05;
  letter-spacing: -0.02em;
  font-variant-numeric: tabular-nums;
}

.side-timer.warn {
  background: var(--danger);
  box-shadow: 0 8px 22px rgba(229, 72, 77, 0.35);
  animation: timerPulse 1.6s ease-in-out infinite;
}

@keyframes timerPulse {
  0%, 100% { transform: scale(1); }
  50%      { transform: scale(1.02); }
}

.side-progress {
  display: flex;
  align-items: center;
  gap: 16px;
}

.progress-ring {
  --p: 0;
  width: 76px;
  height: 76px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background:
    radial-gradient(closest-side, var(--card) 76%, transparent 78%),
    conic-gradient(var(--success) calc(var(--p) * 1%), var(--bg-alt) 0);
  transition: --p var(--dur-slow) var(--ease-out);
}

.progress-ring span {
  font-size: 17px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.side-progress__meta {
  display: grid;
  gap: 8px;
  margin: 0;
}

.side-progress__meta dt {
  color: var(--text-secondary);
  font-size: 13px;
}

.side-progress__meta dd {
  margin: 0;
  font-size: 17px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.side-map__title {
  margin-bottom: 12px;
  font-size: 15px;
  font-weight: 700;
}

.question-map--grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 8px;
  max-height: 320px;
  overflow-y: auto;
  padding: 2px;
}

.question-dot {
  aspect-ratio: 1;
  min-width: 0;
  display: grid;
  place-items: center;
  border-radius: 11px;
  border: 1.5px solid var(--border);
  background: var(--card);
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  cursor: pointer;
}

.question-dot:hover { border-color: var(--brand); color: var(--brand-ink); }
.question-dot.visited { background: var(--bg); }
.question-dot.skipped { background: var(--warning-soft); border-color: transparent; color: var(--warning-ink); }
.question-dot.answered { background: var(--success-soft); border-color: transparent; color: var(--success-ink); }
.question-dot.current {
  background: var(--brand);
  border-color: var(--brand);
  color: #ffffff;
  box-shadow: var(--shadow-brand);
}

.side-map__legend {
  display: flex;
  flex-wrap: wrap;
  gap: 6px 14px;
  margin-top: 12px;
  list-style: none;
  color: var(--text-secondary);
  font-size: 12.5px;
}

.side-map__legend li {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.lg {
  width: 12px;
  height: 12px;
  border-radius: 4px;
}
.lg--answered { background: var(--success-soft); box-shadow: inset 0 0 0 1.5px var(--success); }
.lg--current { background: var(--brand); }
.lg--empty { background: var(--card); box-shadow: inset 0 0 0 1.5px var(--border-strong); }

.side-fullscreen {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 44px;
  border-radius: var(--radius-sm);
  border: 1px dashed var(--border-strong);
  background: transparent;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 600;
}

.side-fullscreen:hover { color: var(--brand-ink); border-color: var(--brand); }

/* ================= Результат ================= */
.result-card {
  position: relative;
  overflow: hidden;
  max-width: 720px;
  margin: 24px auto 0;
  display: grid;
  justify-items: center;
  gap: 22px;
  padding: clamp(28px, 5vw, 48px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-lg);
  text-align: center;
}

.result-confetti {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.result-card.is-failed .result-confetti { display: none; }

.result-confetti i {
  position: absolute;
  top: -12px;
  left: calc(var(--n) * 7%);
  width: 9px;
  height: 14px;
  border-radius: 3px;
  background: var(--brand);
  opacity: 0;
  animation: confetti 2.6s var(--ease-out) calc(var(--n) * 70ms) forwards;
}
.result-confetti i:nth-child(3n) { background: var(--sun); }
.result-confetti i:nth-child(3n + 1) { background: var(--tone-coral); width: 7px; height: 7px; border-radius: 50%; }
.result-confetti i:nth-child(4n) { background: var(--success); }

@keyframes confetti {
  0%   { opacity: 0; transform: translateY(0) rotate(0); }
  10%  { opacity: 1; }
  100% { opacity: 0; transform: translateY(320px) rotate(540deg); }
}

.result-ring {
  --p: 0;
  width: 156px;
  height: 156px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: conic-gradient(var(--success) calc(var(--p) * 1%), var(--bg-alt) 0);
  animation: ringIn 700ms var(--ease-out) both;
}

.result-card.is-failed .result-ring {
  background: conic-gradient(var(--sun) calc(var(--p) * 1%), var(--bg-alt) 0);
}

@keyframes ringIn {
  from { transform: scale(0.7) rotate(-30deg); opacity: 0; }
  to   { transform: none; opacity: 1; }
}

.result-ring__inner {
  width: 128px;
  height: 128px;
  display: grid;
  place-items: center;
  align-content: center;
  gap: 2px;
  border-radius: 50%;
  background: var(--card);
  color: #e3a400;
}

.result-card.is-failed .result-ring__inner { color: var(--text-secondary); }

.result-ring__inner strong {
  color: var(--text);
  font-family: var(--font-display);
  font-size: 26px;
  line-height: 1;
}

.result-heading h1 { margin-top: 4px; }

.result-score-row {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  padding: 18px 22px;
  border-radius: var(--radius-lg);
  background: var(--bg);
  text-align: left;
}

.result-score-chip {
  display: grid;
  gap: 4px;
}

.result-score-main {
  font-family: var(--font-display);
  font-size: clamp(30px, 6vw, 42px);
  font-weight: 700;
  line-height: 1;
  letter-spacing: -0.03em;
  font-variant-numeric: tabular-nums;
}

.result-score-total {
  color: var(--text-tertiary);
  font-size: 0.55em;
}

.result-score-pct {
  color: var(--text-secondary);
  font-size: 15px;
}

.result-actions {
  width: 100%;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
}

/* ================= Адаптив ================= */
@media (max-width: 1024px) {
  .exam-layout { grid-template-columns: minmax(0, 1fr) 260px; }
  .side-timer strong { font-size: 34px; }
  .question-map--grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .kbd-hint { display: none; }
}

@media (max-width: 820px) {
  .exam-layout { grid-template-columns: 1fr; }
  .exam-side { position: static; grid-template-columns: 1fr 1fr; }
  .side-map, .side-fullscreen { grid-column: 1 / -1; }
  .question-map--grid { grid-template-columns: repeat(auto-fill, minmax(44px, 1fr)); max-height: none; }
}

/* Мобильная раскладка (isMobile) */
.mobile-topbar { display: none; }

@media (max-width: 640px) {
  .quiz-page { padding: 0 0 24px; background: var(--bg); }
  .intro-card { border-radius: 0; border: 0; box-shadow: none; padding: 24px 16px; }
  .intro-head { gap: 14px; }
  .intro-badge { width: 52px; height: 52px; border-radius: 16px; }
  .intro-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
  .intro-actions { flex-direction: column-reverse; }
  .intro-actions .ds-btn { width: 100%; }
  .result-card { margin: 16px; padding: 28px 18px; }
  .result-actions .ds-btn { width: 100%; }
}

.mobile-topbar {
  position: sticky;
  top: 0;
  z-index: 10;
  align-items: center;
  gap: 12px;
  padding: 10px 16px;
  background: var(--card);
  border-bottom: 1px solid var(--border);
}

.exam-shell:has(.mobile-topbar) .mobile-topbar { display: flex; }
.exam-shell:has(.mobile-topbar) .exam-layout { display: block; }
.exam-shell:has(.mobile-topbar) .exam-main { gap: 0; }

.mobile-back {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: var(--radius-sm);
  background: var(--bg-alt);
  color: var(--text);
}

.mobile-topbar-center { flex: 1; min-width: 0; }

.mobile-title {
  display: block;
  overflow: hidden;
  color: var(--brand-ink);
  font-size: 13px;
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.mobile-qcount {
  display: block;
  font-size: 15px;
  font-weight: 700;
}

.mobile-timer {
  flex-shrink: 0;
  padding: 8px 12px;
  border-radius: var(--radius-sm);
  background: var(--brand);
  color: #ffffff;
  font-family: var(--font-display);
  font-size: 18px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.mobile-timer.warn {
  background: var(--danger);
  animation: timerPulse 1.6s ease-in-out infinite;
}

.progress-card--mobile {
  padding: 10px 16px 12px;
  background: var(--card);
  border-bottom: 1px solid var(--border);
}

.progress-card--mobile .progress-track {
  height: 6px;
  margin-bottom: 10px;
  border-radius: var(--radius-pill);
  background: var(--bg-alt);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: inherit;
  background: var(--success);
  transition: width var(--dur-slow) var(--ease-out);
}

.question-map-scroller {
  margin-right: -16px;
  padding-right: 16px;
  overflow-x: auto;
  scrollbar-width: none;
}

.question-map-scroller::-webkit-scrollbar { display: none; }

.question-map--row {
  display: flex;
  gap: 8px;
  width: max-content;
}

.question-map--row .question-dot {
  width: 40px;
  height: 40px;
  flex: 0 0 40px;
}

.exam-shell:has(.mobile-topbar) .question-card {
  border-radius: 0;
  border-left: 0;
  border-right: 0;
  box-shadow: none;
  padding: 20px 16px 24px;
}

.exam-shell:has(.mobile-topbar) .question-index {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  font-size: 16px;
}

.exam-shell:has(.mobile-topbar) .answer-option {
  grid-template-columns: 36px minmax(0, 1fr) 24px;
  min-height: 60px;
  padding: 10px 12px 10px 10px;
}

.exam-shell:has(.mobile-topbar) .answer-label { width: 36px; height: 36px; }
.exam-shell:has(.mobile-topbar) .answer-check { width: 24px; height: 24px; }
.exam-shell:has(.mobile-topbar) .answer-text { font-size: 16px; }

.exam-shell:has(.mobile-topbar) .sticky-footer {
  bottom: 0;
  border-radius: 0;
  border-left: 0;
  border-right: 0;
  border-bottom: 0;
  padding: 12px 16px max(12px, env(safe-area-inset-bottom));
  box-shadow: 0 -6px 20px rgba(17, 26, 51, 0.06);
}

.exam-shell:has(.mobile-topbar) .nav-prev { width: 52px; padding: 0; flex: 0 0 52px; }
.exam-shell:has(.mobile-topbar) .nav-next { flex: 1; margin-left: 0; }
</style>

<style>
.confirm-overlay {
  position: fixed;
  inset: 0;
  z-index: var(--z-modal);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: var(--overlay);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

.confirm-dialog {
  width: 100%;
  max-width: 400px;
  display: grid;
  justify-items: center;
  gap: 8px;
  padding: 28px 24px 24px;
  border-radius: var(--radius-xl);
  background: var(--card);
  box-shadow: var(--shadow-lg);
  text-align: center;
}

.confirm-dialog__icon {
  width: 60px;
  height: 60px;
  display: grid;
  place-items: center;
  margin-bottom: 6px;
  border-radius: 18px;
  background: var(--sun-soft);
  color: #d99a00;
}

.confirm-dialog__title {
  font-family: var(--font-display);
  font-size: 22px;
  font-weight: 700;
  color: var(--text);
}

.confirm-dialog__body {
  color: var(--text-secondary);
  font-size: 16px;
}

.confirm-dialog__stat {
  margin-top: 4px;
  padding: 6px 12px;
  border-radius: var(--radius-sm);
  background: var(--success-soft);
  color: var(--success-ink);
  font-size: 14px;
  font-weight: 600;
}

.confirm-dialog__stat.is-incomplete {
  background: var(--warning-soft);
  color: var(--warning-ink);
}

.confirm-dialog__actions {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 14px;
}

.dialog-enter-active,
.dialog-leave-active { transition: opacity var(--dur) ease; }
.dialog-enter-active .confirm-dialog,
.dialog-leave-active .confirm-dialog { transition: transform var(--dur-slow) var(--ease-out); }
.dialog-enter-from,
.dialog-leave-to { opacity: 0; }
.dialog-enter-from .confirm-dialog,
.dialog-leave-to .confirm-dialog { transform: translateY(16px) scale(0.96); }
</style>
