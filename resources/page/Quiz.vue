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
        <RouterLink class="action-btn secondary" to="/profile">Вернуться в кабинет</RouterLink>
        <RouterLink class="action-btn" to="/subject">Открыть выбор олимпиад</RouterLink>
      </template>
    </StatePanel>

    <template v-else-if="result">
      <div class="result-card">
        <p class="eyebrow">Результат</p>
        <h1>{{ result.score }} / {{ result.total }}</h1>
        <p class="description">Правильных ответов: {{ result.percent }}%</p>
        <StatusBadge :label="result.status === 'passed' ? 'Пройдено' : 'Не пройдено'" :tone="result.status === 'passed' ? 'success' : 'warning'" />

        <StatePanel
          class="result-panel"
          :tone="result.status === 'passed' ? 'success' : 'warning'"
          eyebrow="Следующий шаг"
          :title="result.status === 'passed' ? 'Результат уже сохранён' : 'Попытка завершена и сохранена'"
          description="Откройте страницу результатов, чтобы увидеть историю участия, и при необходимости проверьте сертификат участника."
        >
          <template #actions>
            <RouterLink v-if="result.id" class="action-btn secondary" :to="`/profile/results/${result.id}/certificate-preview`">Превью сертификата</RouterLink>
            <button class="action-btn secondary" @click="downloadCertificate">Скачать сертификат</button>
            <RouterLink v-if="result.mistakes_url" class="action-btn secondary" :to="result.mistakes_url">Работа над ошибками</RouterLink>
            <RouterLink class="action-btn" to="/results">Перейти к результатам</RouterLink>
          </template>
        </StatePanel>
      </div>
    </template>

    <StatePanel
      v-else-if="violationMessage"
      tone="danger"
      eyebrow="Попытка сброшена"
      title="Тест остановлен"
      :description="violationMessage"
    >
      <template #actions>
        <RouterLink class="action-btn" to="/profile">Вернуться в профиль</RouterLink>
        <RouterLink class="action-btn secondary" to="/help-desk">Подать апелляцию или написать в поддержку</RouterLink>
      </template>
    </StatePanel>

    <template v-else-if="quiz">
      <section v-if="!examStarted" class="intro-card">
        <p class="eyebrow">{{ quiz.subject?.name || 'Олимпиада' }}</p>
        <h1>{{ quiz.title }}</h1>
        <p class="description">{{ quiz.description || 'Перед началом внимательно ознакомьтесь с правилами прохождения олимпиады.' }}</p>
        <p v-if="quiz.child" class="child-chip">Участник: {{ quiz.child.full_name }} · {{ quiz.child.grade || 'без класса' }}</p>

        <div class="intro-grid">
          <div class="intro-item"><span>Категория</span><strong>{{ quiz.category?.label }}</strong></div>
          <div v-if="quiz.category?.display_range && quiz.category.display_range !== quiz.category.label" class="intro-item"><span>Классы</span><strong>{{ quiz.category.display_range }}</strong></div>
          <div class="intro-item"><span>Вопросов</span><strong>{{ quiz.questions.length }}</strong></div>
          <div class="intro-item"><span>Время</span><strong>{{ quiz.time_limit }} минут</strong></div>
        </div>

        <StatePanel
          v-if="!isMobile"
          tone="warning"
          eyebrow="Важно перед стартом"
          :title="quiz.warning"
          description="Перед началом убедитесь, что у участника есть свободное время, стабильный интернет и готовность пройти олимпиаду в одном окне браузера."
        />

        <ul class="rules-list">
          <li v-for="rule in quiz.warning_rules || defaultRules" :key="rule">{{ rule }}</li>
        </ul>

        <label class="confirm-row">
          <input v-model="rulesAccepted" type="checkbox" />
          <span>Я ознакомился с правилами и понимаю, что нарушение приведёт к аннулированию попытки.</span>
        </label>

        <p v-if="fullscreenError" class="error-inline">{{ fullscreenError }}</p>

        <div class="intro-actions">
          <RouterLink class="action-btn secondary" to="/profile">Вернуться в кабинет</RouterLink>
          <button class="action-btn" :disabled="!rulesAccepted" @click="startExam">{{ isMobile ? 'Начать тест' : 'Начать тест в полноэкранном режиме' }}</button>
        </div>
      </section>

      <section v-else class="exam-shell">
        <!-- Mobile top bar -->
        <div v-if="isMobile" class="mobile-topbar">
          <RouterLink to="/profile" class="mobile-back">✕</RouterLink>
          <div class="mobile-topbar-center">
            <span class="mobile-title">{{ quiz.subject?.name || 'Олимпиада' }}</span>
            <span class="mobile-qcount">Вопрос {{ currentQuestionIndex + 1 }} из {{ quiz.questions.length }}</span>
          </div>
          <span class="mobile-timer" :class="{ warn: timeLeft < 300 }">{{ formatTime(timeLeft) }}</span>
        </div>

        <header v-if="!isMobile" class="exam-header">
          <div>
            <p class="eyebrow">{{ quiz.subject?.name || 'Олимпиада' }}</p>
            <h1>{{ quiz.title }}</h1>
            <p class="description">Вопрос {{ currentQuestionIndex + 1 }} из {{ quiz.questions.length }}</p>
          </div>

          <div class="hero-stats">
            <div class="stat-box"><span>Отвечено</span><strong>{{ answeredCount }}/{{ quiz.questions.length }}</strong></div>
            <div class="stat-box"><span>Пропущено</span><strong>{{ skippedCount }}</strong></div>
            <div class="stat-box" :class="{ warn: timeLeft < 300 }"><span>Время</span><strong>{{ formatTime(timeLeft) }}</strong></div>
            <button class="stat-box fullscreen-btn" @click="requestFullscreen"><span>Режим</span><strong>{{ isFullscreen ? 'Полный экран' : 'Развернуть' }}</strong></button>
          </div>
        </header>

        <StatePanel
          v-if="submitError"
          tone="warning"
          eyebrow="Отправка ответов"
          :title="submitError"
          description="Пожалуйста, не закрывайте страницу. Можно попробовать отправить ответы ещё раз."
        />

        <section ref="questionNavRef" class="progress-card">
          <div class="progress-top"><span>Прогресс</span><strong>{{ progressPercent }}%</strong></div>
          <div class="progress-track"><div class="progress-fill" :style="{ width: `${progressPercent}%` }"></div></div>
          <div class="progress-meta">
            <span>Отвечено: {{ answeredCount }}</span>
            <span>Пропущено: {{ skippedCount }}</span>
            <span>Текущий: {{ currentQuestionIndex + 1 }}/{{ quiz.questions.length }}</span>
          </div>
          <div ref="questionMapScrollerRef" class="question-map-scroller">
            <div class="question-map question-map--horizontal">
              <button
                v-for="(question, index) in quiz.questions"
                :key="question.id"
                type="button"
                class="question-dot"
                :class="questionState(index)"
                @click="goToQuestion(index)"
              >
                {{ index + 1 }}
              </button>
            </div>
          </div>
        </section>

        <div class="exam-body">
          <div class="exam-main">
            <article ref="questionCardRef" class="question-card">
              <div class="question-header">
                <span class="question-index">{{ currentQuestionIndex + 1 }}</span>
                <div class="question-copy">
                  <p class="question-hint">Выберите один вариант ответа</p>
                  <h2 class="question-title">{{ questionText }}</h2>
                </div>
              </div>

              <div v-if="currentQuestion.image" class="question-image-shell">
                <img :src="currentQuestion.image" alt="question" class="question-image" />
              </div>

              <div class="answer-list">
                <label
                  v-for="answer in currentQuestion.answers"
                  :key="answer.id"
                  class="answer-option"
                  :class="{ selected: userAnswers[currentQuestion.id] === answer.id }"
                >
                  <input
                    v-model="userAnswers[currentQuestion.id]"
                    type="radio"
                    :name="`question-${currentQuestion.id}`"
                    :value="answer.id"
                    @change="markVisited(currentQuestionIndex)"
                  />
                  <span class="answer-label">{{ answer.label }}</span>
                  <span class="answer-text">{{ decodeText(answer.answer) }}</span>
                </label>
              </div>
            </article>

            <footer class="sticky-footer">
              <button v-if="!isMobile" class="action-btn secondary" :disabled="currentQuestionIndex === 0" @click="goPrev">Назад</button>
              <button v-if="!isMobile" class="action-btn secondary" @click="skipQuestion">Пропустить</button>
              <template v-if="isMobile">
                <button class="action-btn secondary mobile-nav-btn" :disabled="currentQuestionIndex === 0" @click="goPrev">←</button>
              </template>
              <button v-if="!isLastQuestion" class="action-btn" :class="{ 'mobile-next-btn': isMobile }" @click="goNext">Следующий вопрос</button>
              <button v-else class="action-btn" :class="{ 'mobile-next-btn': isMobile }" :disabled="submitting" @click="confirmSubmitQuiz">{{ submitting ? 'Отправляем...' : 'Завершить тест' }}</button>
            </footer>
          </div>
        </div>

        <aside class="floating-timer" :class="{ warn: timeLeft < 300 }" aria-live="polite">
          <span>Время</span>
          <strong>{{ formatTime(timeLeft) }}</strong>
        </aside>
      </section>
    </template>

    <Teleport to="body">
      <div v-if="showConfirmModal" class="confirm-overlay" @click.self="onConfirmNo">
        <div class="confirm-dialog" role="dialog" aria-modal="true">
          <p class="confirm-dialog__title">Завершить тест?</p>
          <p class="confirm-dialog__body">После отправки ответы изменить нельзя.</p>
          <div class="confirm-dialog__actions">
            <button class="action-btn secondary" @click="onConfirmNo">Отмена</button>
            <button class="action-btn" :disabled="submitting" @click="onConfirmYes">{{ submitting ? 'Отправляем...' : 'Завершить' }}</button>
          </div>
        </div>
      </div>
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

  const target = questionNavRef.value || questionCardRef.value
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

const confirmSubmitQuiz = () => {
  if (submitting.value || violated) return
  showConfirmModal.value = true
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
  window.addEventListener('resize', recomputeIsMobile)
  window.addEventListener('orientationchange', recomputeIsMobile)
})

onUnmounted(async () => {
  clearTimer()
  clearHeartbeat()
  document.removeEventListener('visibilitychange', handleVisibilityLoss)
  document.removeEventListener('fullscreenchange', handleFullscreenChange)
  window.removeEventListener('blur', handleWindowBlur)
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
* { box-sizing: border-box; }
.quiz-page { min-height: 100vh; background: radial-gradient(circle at top center, rgba(201,171,99,0.14), transparent 22%), linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%); color: var(--text); padding: 90px 20px 110px; display: block; width: 100%; max-width: 100%; overflow-x: hidden; touch-action: pan-y; }
.intro-card, .exam-header, .progress-card, .question-card, .result-card { max-width: 1100px; width: 100%; min-width: 0; margin: 0 auto; border-radius: var(--radius-lg); border: 1px solid var(--surface-border); background: var(--surface); backdrop-filter: blur(12px); box-shadow: var(--shadow-card); }
.result-card, .intro-card { padding: 30px; display: grid; gap: 18px; }
.result-panel { margin-top: 8px; }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--accent-strong); font-size: 0.75rem; font-weight: 700; }
h1 { margin: 0; font-size: clamp(1.875rem, 4vw, 2.75rem); }
h2 { margin: 0; font-size: clamp(1rem, 3.5vw, 1.5rem); line-height: 1.45; word-break: break-word; overflow-wrap: anywhere; hyphens: auto; min-width: 0; }
.description, .child-chip { margin-top: 12px; color: var(--text-secondary); line-height: 1.6; }
.intro-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-top: 8px; }
.intro-item { padding: 16px; border-radius: var(--radius-md); background: rgba(255,252,244,0.82); border: 1px solid var(--surface-border); }
.intro-item span { display: block; color: var(--text-secondary); font-size: 0.75rem; margin-bottom: 8px; }
.rules-list { margin: 0; padding-left: 20px; color: var(--text-secondary); line-height: 1.7; }
.confirm-row { display: flex; gap: 10px; align-items: flex-start; color: var(--text); }
.error-inline { margin: 0; color: #8f3b3b; font-weight: 700; }
.intro-actions { display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap; }
.exam-shell { max-width: 1100px; width: 100%; min-width: 0; margin: 0 auto; display: block; overflow-x: hidden; }
.exam-header { padding: 24px; display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; }
.hero-stats { display: grid; grid-template-columns: repeat(2, minmax(160px, 1fr)); gap: 12px; }
.stat-box { padding: 14px 16px; border-radius: var(--radius-md); background: rgba(255,252,244,0.82); border: 1px solid var(--surface-border); text-align: left; color: var(--text); }
.stat-box span { display: block; font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 6px; }
.stat-box.warn { outline: 2px solid rgba(198,90,90,0.24); }
.fullscreen-btn { cursor: pointer; }
.progress-card { width: 100%; max-width: 100%; min-width: 0; padding: 20px 20px 22px; position: static; overflow: hidden; }
.progress-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; color: var(--text-secondary); margin-bottom: 12px; }
.progress-track { height: 10px; border-radius: 999px; background: rgba(100,83,41,0.12); overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, var(--success-soft), #56a36f); }
.progress-meta { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; color: var(--text-secondary); font-size: 0.8125rem; }
.exam-body { display: block; width: 100%; max-width: 100%; min-width: 0; margin-top: 16px; }
.exam-main { display: block; width: 100%; max-width: 100%; min-width: 0; }
.question-map-scroller { width: 100%; max-width: 100%; min-width: 0; overflow-x: auto; overflow-y: hidden; padding-bottom: 4px; -webkit-overflow-scrolling: touch; scrollbar-width: thin; }
.question-map { display: flex; gap: 10px; margin-top: 18px; width: max-content; min-width: 100%; overflow: visible; }
.question-map--horizontal { grid-template-columns: none; }
.question-dot { flex: 0 0 auto; width: 44px; height: 44px; border-radius: var(--radius-sm); border: 1px solid var(--surface-border); background: rgba(255,252,244,0.8); color: var(--text); font-weight: 700; cursor: pointer; }
.question-dot.current { background: var(--info-soft); border-color: rgba(26,95,168,0.32); color: var(--info); }
.question-dot.answered { background: rgba(44,122,75,0.14); border-color: rgba(44,122,75,0.34); color: var(--success-soft); }
.question-dot.skipped { background: var(--warning-bg); border-color: rgba(201,171,99,0.3); color: var(--accent-strong); }
.question-dot.visited { background: rgba(26,95,168,0.08); }
.question-card { padding: 28px; min-height: 520px; display: block; width: 100%; max-width: 100%; min-width: 0; overflow: hidden; overflow-wrap: anywhere; }
.question-card > *, .question-header > div, .answer-option { min-width: 0; }
.question-header { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 18px; width: 100%; min-width: 0; }
.question-index { width: 38px; height: 38px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; background: var(--info-soft); color: var(--info); }
.question-copy { flex: 1 1 0; min-width: 0; max-width: 100%; }
.question-hint { margin: 0 0 8px; color: var(--text-secondary); font-size: 0.8125rem; text-transform: uppercase; letter-spacing: 0.08em; }
.question-title { display: block; width: 100%; max-width: 100%; min-width: 0; white-space: normal; word-break: break-word; overflow-wrap: anywhere; }
.question-image-shell { width: 100%; min-height: 220px; max-height: 360px; display: flex; align-items: center; justify-content: center; border-radius: 18px; margin-bottom: 18px; background: rgba(255,252,244,0.8); border: 1px solid var(--surface-border); padding: 14px; overflow: hidden; }
.question-image { width: 100%; height: 100%; max-height: 330px; object-fit: contain; border-radius: 12px; }
.answer-list { display: grid; gap: 12px; width: 100%; min-width: 0; }
.answer-option { display: grid; grid-template-columns: 28px 24px minmax(0, 1fr); gap: 12px; align-items: start; width: 100%; min-width: 0; padding: 14px 16px; border-radius: 18px; border: 1px solid var(--surface-border); background: rgba(255,252,244,0.82); cursor: pointer; }
.answer-option.selected { border-color: rgba(26,95,168,0.36); background: rgba(26,95,168,0.08); }
.answer-option input { margin: 0; margin-top: 2px; }
.answer-label { width: 1.5rem; height: 1.5rem; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: var(--info-soft); font-size: 0.75rem; font-weight: 700; color: var(--info); margin-top: 1px; flex-shrink: 0; }
.answer-text { line-height: 1.55; padding-top: 2px; min-width: 0; max-width: 100%; word-break: break-word; overflow-wrap: anywhere; }
.sticky-footer { position: sticky; bottom: 16px; z-index: 3; display: flex; justify-content: space-between; gap: 12px; width: 100%; max-width: 100%; min-width: 0; margin-top: 16px; padding: 14px; border-radius: 20px; background: rgba(255,249,238,0.94); border: 1px solid var(--surface-border); backdrop-filter: blur(14px); box-shadow: var(--shadow-card); }
.floating-timer { position: fixed; right: 20px; bottom: 20px; z-index: 7; min-width: 118px; padding: 14px 16px; border-radius: 18px; border: 1px solid var(--surface-border); background: rgba(255,249,238,0.96); box-shadow: var(--shadow-card); backdrop-filter: blur(14px); display: grid; gap: 4px; }
.floating-timer span { font-size: 0.75rem; color: var(--text-secondary); }
.floating-timer strong { font-size: 1.5rem; line-height: 1; font-variant-numeric: tabular-nums; }
.floating-timer.warn { border-color: rgba(198,90,90,0.4); box-shadow: 0 18px 40px rgba(198,90,90,0.18); }
.action-btn { display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 14px; padding: 12px 18px; min-height: 2.75rem; background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%); color: var(--text); font-weight: 700; cursor: pointer; text-decoration: none; box-shadow: 0 12px 26px rgba(201,168,76,0.24); }
.action-btn.secondary { background: var(--info-soft); color: var(--info); border: 1px solid rgba(26,95,168,0.18); box-shadow: none; }
.action-btn:disabled { opacity: 0.6; cursor: not-allowed; }
:deep(.result-panel .state-panel__actions > :nth-child(1)),
:deep(.result-panel .state-panel__actions > :nth-child(2)) { display: none; }
.hero-stats > :nth-child(2),
.progress-meta > :nth-child(2),
.sticky-footer > :nth-child(2) { display: none; }
@media (max-width: 980px) { .question-card { min-height: 0; } }
@media (max-width: 1024px) and (min-width: 641px) {
  .quiz-page { padding: 70px 16px 100px; }
  .question-card { padding: 24px 22px; min-height: 0; }
  .exam-header { padding: 20px; gap: 14px; flex-wrap: wrap; }
  .hero-stats { grid-template-columns: repeat(2, minmax(140px, 1fr)); }
  .answer-option { padding: 14px; }
  .question-image-shell { min-height: 180px; max-height: 300px; }
  .floating-timer { right: 14px; bottom: 14px; min-width: 100px; padding: 10px 12px; }
}
@media (max-width: 900px) { .exam-header, .sticky-footer { flex-direction: column; align-items: stretch; } .hero-stats { grid-template-columns: 1fr 1fr; } }
@media (max-width: 768px) {
  .quiz-page { padding-top: 30px; padding-bottom: 120px; }
  .exam-shell > * + * { margin-top: 16px; }
  .question-card { min-height: 0; padding: 22px 20px; }
  .exam-header { padding: 18px 20px; }
  .hero-stats { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .answer-option { border-radius: 16px; margin-bottom: 0.5rem; }
  .question-hint { font-size: 0.75rem; }
  .intro-actions { flex-direction: column; }
  .intro-actions .action-btn, .sticky-footer .action-btn { width: 100%; }
}
@media (max-width: 640px) {
  .quiz-page { padding: 0 0 112px; background: var(--bg); overflow-x: hidden; width: 100%; max-width: 100%; }
  .exam-shell { display: block; width: 100%; max-width: 100%; min-width: 0; overflow-x: hidden; }
  .exam-shell > * + * { margin-top: 0; }
  .floating-timer { display: none; }
  .intro-card, .progress-card, .question-card, .sticky-footer { max-width: 100%; }
  .progress-card { border-radius: 0; border-left: none; border-right: none; border-top: none; padding: 12px 16px 14px; backdrop-filter: none; }
  .progress-top { display: none; }
  .progress-track { height: 4px; margin-top: 6px; margin-bottom: 0; }
  .progress-meta { display: none; }
  .question-map-scroller { width: calc(100% + 16px); margin-right: -16px; padding-right: 16px; padding-bottom: 2px; overflow-x: auto; }
  .question-map { gap: 8px; margin-top: 0; min-width: max-content; padding-right: 2px; }
  .question-dot { flex: 0 0 38px; width: 38px; height: 38px; font-size: 13px; }
  .exam-body { margin-top: 0; }
  .exam-main { display: block; width: 100%; max-width: 100%; min-width: 0; }
  .question-card { border-radius: 0; border-left: none; border-right: none; padding: 20px 16px; min-height: 0; }
  .question-header { display: grid; grid-template-columns: 2.375rem minmax(0, 1fr); align-items: flex-start; gap: 10px; margin-bottom: 14px; width: 100%; }
  .question-hint { font-size: 0.6875rem; }
  h2 { font-size: 1.125rem; line-height: 1.5; word-break: break-word; overflow-wrap: anywhere; }
  .question-header > div { min-width: 0; }
  .question-copy { min-width: 0; max-width: 100%; }
  .question-title { font-size: 1.125rem; line-height: 1.5; }
  .question-image-shell { min-height: 200px; max-height: 240px; border-radius: 14px; }
  .answer-list { gap: 10px; }
  .answer-option { display: flex; align-items: flex-start; gap: 12px; padding: 16px 14px; border-radius: 14px; font-size: 0.9375rem; min-height: 2.75rem; }
  .answer-option input { display: none; }
  .answer-label { width: 1.75rem; height: 1.75rem; font-size: 0.8125rem; flex-shrink: 0; margin-top: 1px; }
  .answer-text { flex: 1; min-width: 0; max-width: 100%; word-break: break-word; overflow-wrap: anywhere; }
  .answer-option.selected { border-color: rgba(26,95,168,0.5); background: rgba(26,95,168,0.12); }
  .sticky-footer { position: sticky; bottom: 0; left: auto; right: auto; border-radius: 0; border-left: none; border-right: none; border-bottom: none; padding: 12px 16px; display: flex; gap: 10px; backdrop-filter: none; box-shadow: none; }
  .sticky-footer > :nth-child(2) { display: flex; }
  .mobile-nav-btn { flex: 0 0 3rem; width: 3rem; padding: 12px 0; font-size: 1.125rem; min-height: 2.75rem; }
  .mobile-next-btn { flex: 1; font-size: 1rem; min-height: 2.75rem; }
  .intro-card { border-radius: 0; padding: 20px 16px; gap: 14px; backdrop-filter: none; }
  .intro-card h1 { font-size: 1.375rem; }
  .intro-card .description { font-size: 0.875rem; margin-top: 4px; }
  .intro-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
  .intro-item { padding: 12px; }
  .rules-list { font-size: 0.8125rem; padding-left: 16px; line-height: 1.5; }
  .intro-actions { flex-direction: column; }
  .intro-actions .action-btn { width: 100%; }
}

@media (max-width: 380px) {
  .mobile-topbar { padding: 10px 12px; gap: 8px; }
  .mobile-title { font-size: 0.625rem; }
  .mobile-qcount { font-size: 0.75rem; }
  .mobile-timer { font-size: 1.125rem; }
  .progress-card { padding: 8px 12px 10px; }
  .question-map-scroller { width: calc(100% + 12px); margin-right: -12px; padding-right: 12px; }
  .question-dot { flex: 0 0 2rem; width: 2rem; height: 2rem; font-size: 0.75rem; }
  .question-card { padding: 14px 12px; }
  .question-hint { font-size: 0.625rem; }
  .question-title { font-size: 1rem; line-height: 1.45; }
  .question-image-shell { min-height: 160px; max-height: 200px; }
  .answer-list { gap: 8px; }
  .answer-option { padding: 12px 10px; gap: 10px; border-radius: 12px; font-size: 0.875rem; min-height: 2.75rem; }
  .answer-label { width: 1.625rem; height: 1.625rem; font-size: 0.6875rem; }
  .sticky-footer { padding: 10px 12px; }
  .mobile-nav-btn { flex: 0 0 2.75rem; width: 2.75rem; font-size: 1rem; min-height: 2.75rem; }
  .mobile-next-btn { font-size: 0.875rem; min-height: 2.75rem; }
}

/* Mobile top bar */
.mobile-topbar { display: none; }
@media (max-width: 640px) {
  .mobile-topbar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: var(--surface);
    border-bottom: 1px solid var(--surface-border);
    position: sticky;
    top: 0;
    z-index: 10;
    backdrop-filter: none;
  }
  .mobile-back {
    width: 2.75rem; height: 2.75rem; min-height: 2.75rem;
    border-radius: 50%;
    background: rgba(255,252,244,0.9);
    border: 1px solid var(--surface-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; font-weight: 700;
    color: var(--text);
    text-decoration: none;
    flex-shrink: 0;
  }
  .mobile-topbar-center { flex: 1; min-width: 0; }
  .mobile-title { display: block; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--accent-strong); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .mobile-qcount { display: block; font-size: 0.8125rem; color: var(--text-secondary); }
  .mobile-timer {
    font-size: 1.25rem; font-weight: 700; font-variant-numeric: tabular-nums;
    color: var(--text); flex-shrink: 0;
  }
  .mobile-timer.warn { color: #c65a5a; }
}
</style>

<style>
.confirm-overlay {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
}

.confirm-dialog {
  background: var(--card, #fff);
  border-radius: 1rem;
  padding: 1.5rem;
  width: 100%;
  max-width: 360px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
}

.confirm-dialog__title {
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0 0 0.5rem;
  color: var(--text);
}

.confirm-dialog__body {
  font-size: 0.9375rem;
  color: var(--text-muted, #666);
  margin: 0 0 1.25rem;
}

.confirm-dialog__actions {
  display: flex;
  gap: 0.75rem;
  justify-content: flex-end;
}
</style>
