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
        </section>

        <div class="exam-body">
          <div class="exam-main">
            <article ref="questionCardRef" class="question-card">
              <div class="question-header">
                <span class="question-index">{{ currentQuestionIndex + 1 }}</span>
                <div>
                  <p class="question-hint">Выберите один вариант ответа</p>
                  <h2>{{ currentQuestion.question }}</h2>
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
                  <span class="answer-text">{{ answer.answer }}</span>
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
          <span>Р’СЂРµРјСЏ</span>
          <strong>{{ formatTime(timeLeft) }}</strong>
        </aside>
      </section>
    </template>
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
const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)
const rulesAccepted = ref(false)
const questionNavRef = ref(null)
const questionCardRef = ref(null)
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

const currentQuestion = computed(() => quiz.value?.questions[currentQuestionIndex.value] || null)
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

const confirmSubmitQuiz = async () => {
  if (submitting.value || violated) return

  const confirmed = window.confirm('Вы уверены, что хотите завершить тест? После отправки ответы изменить нельзя.')
  if (!confirmed) return

  await submitQuiz()
}

const requestFullscreen = async () => {
  fullscreenError.value = ''

  if (isMobile) {
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

const handleVisibilityLoss = () => {
  if (document.hidden) registerViolation('tab_hidden')
}

const handleWindowBlur = () => {
  registerViolation('window_blur')
}

const handleFullscreenChange = () => {
  if (isMobile) return
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
})

onUnmounted(async () => {
  clearTimer()
  clearHeartbeat()
  document.removeEventListener('visibilitychange', handleVisibilityLoss)
  document.removeEventListener('fullscreenchange', handleFullscreenChange)
  window.removeEventListener('blur', handleWindowBlur)

  if (document.fullscreenElement) {
    try {
      await document.exitFullscreen()
    } catch {}
  }
})
</script>

<style scoped>
* { box-sizing: border-box; }
.quiz-page { min-height: 100vh; background: radial-gradient(circle at top center, rgba(201,171,99,0.14), transparent 22%), linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%); color: var(--text); padding: 90px 20px 110px; display: grid; gap: 18px; }
.intro-card, .exam-header, .progress-card, .question-card, .result-card { max-width: 1100px; width: 100%; margin: 0 auto; border-radius: var(--radius-lg); border: 1px solid var(--surface-border); background: var(--surface); backdrop-filter: blur(12px); box-shadow: var(--shadow-card); }
.result-card, .intro-card { padding: 30px; display: grid; gap: 18px; }
.result-panel { margin-top: 8px; }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--accent-strong); font-size: 12px; font-weight: 700; }
h1 { margin: 0; font-size: clamp(30px, 4vw, 44px); }
h2 { margin: 0; font-size: clamp(16px, 3.5vw, 24px); line-height: 1.45; }
.description, .child-chip { margin-top: 12px; color: var(--text-secondary); line-height: 1.6; }
.intro-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-top: 8px; }
.intro-item { padding: 16px; border-radius: var(--radius-md); background: rgba(255,252,244,0.82); border: 1px solid var(--surface-border); }
.intro-item span { display: block; color: var(--text-secondary); font-size: 12px; margin-bottom: 8px; }
.rules-list { margin: 0; padding-left: 20px; color: var(--text-secondary); line-height: 1.7; }
.confirm-row { display: flex; gap: 10px; align-items: flex-start; color: var(--text); }
.error-inline { margin: 0; color: #8f3b3b; font-weight: 700; }
.intro-actions { display: flex; justify-content: flex-end; gap: 12px; flex-wrap: wrap; }
.exam-shell { max-width: 1100px; margin: 0 auto; display: grid; gap: 16px; }
.exam-header { padding: 24px; display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; }
.hero-stats { display: grid; grid-template-columns: repeat(2, minmax(160px, 1fr)); gap: 12px; }
.stat-box { padding: 14px 16px; border-radius: var(--radius-md); background: rgba(255,252,244,0.82); border: 1px solid var(--surface-border); text-align: left; color: var(--text); }
.stat-box span { display: block; font-size: 12px; color: var(--text-secondary); margin-bottom: 6px; }
.stat-box.warn { outline: 2px solid rgba(198,90,90,0.24); }
.fullscreen-btn { cursor: pointer; }
.progress-card { width: 100%; padding: 20px 20px 22px; position: static; }
.progress-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; color: var(--text-secondary); margin-bottom: 12px; }
.progress-track { height: 10px; border-radius: 999px; background: rgba(100,83,41,0.12); overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, var(--success-soft), #56a36f); }
.progress-meta { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; color: var(--text-secondary); font-size: 13px; }
.exam-body { display: grid; grid-template-columns: minmax(0, 1fr); gap: 16px; align-items: start; }
.exam-main { display: grid; gap: 16px; min-width: 0; width: 100%; }
.question-map { display: flex; gap: 10px; margin-top: 18px; overflow-x: auto; padding-bottom: 4px; scrollbar-width: thin; }
.question-map--horizontal { grid-template-columns: none; }
.question-dot { flex: 0 0 44px; width: 44px; height: 44px; border-radius: var(--radius-sm); border: 1px solid var(--surface-border); background: rgba(255,252,244,0.8); color: var(--text); font-weight: 700; cursor: pointer; }
.question-dot.current { background: var(--info-soft); border-color: rgba(26,95,168,0.32); color: var(--info); }
.question-dot.answered { background: rgba(44,122,75,0.14); border-color: rgba(44,122,75,0.34); color: var(--success-soft); }
.question-dot.skipped { background: var(--warning-bg); border-color: rgba(201,171,99,0.3); color: var(--accent-strong); }
.question-dot.visited { background: rgba(26,95,168,0.08); }
.question-card { padding: 28px; min-height: 520px; display: grid; align-content: start; width: 100%; min-width: 0; }
.question-header { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 18px; }
.question-index { width: 38px; height: 38px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; background: var(--info-soft); color: var(--info); }
.question-hint { margin: 0 0 8px; color: var(--text-secondary); font-size: 13px; text-transform: uppercase; letter-spacing: 0.08em; }
.question-image-shell { width: 100%; min-height: 220px; max-height: 360px; display: flex; align-items: center; justify-content: center; border-radius: 18px; margin-bottom: 18px; background: rgba(255,252,244,0.8); border: 1px solid var(--surface-border); padding: 14px; overflow: hidden; }
.question-image { width: 100%; height: 100%; max-height: 330px; object-fit: contain; border-radius: 12px; }
.answer-list { display: grid; gap: 12px; }
.answer-option { display: grid; grid-template-columns: 28px 24px 1fr; gap: 12px; align-items: start; padding: 14px 16px; border-radius: 18px; border: 1px solid var(--surface-border); background: rgba(255,252,244,0.82); cursor: pointer; }
.answer-option.selected { border-color: rgba(26,95,168,0.36); background: rgba(26,95,168,0.08); }
.answer-option input { margin: 0; margin-top: 2px; }
.answer-label { width: 24px; height: 24px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: var(--info-soft); font-size: 12px; font-weight: 700; color: var(--info); margin-top: 1px; flex-shrink: 0; }
.answer-text { line-height: 1.55; padding-top: 2px; }
.sticky-footer { position: sticky; bottom: 16px; z-index: 3; display: flex; justify-content: space-between; gap: 12px; padding: 14px; border-radius: 20px; background: rgba(255,249,238,0.94); border: 1px solid var(--surface-border); backdrop-filter: blur(14px); box-shadow: var(--shadow-card); }
.floating-timer { position: fixed; right: 20px; bottom: 20px; z-index: 7; min-width: 118px; padding: 14px 16px; border-radius: 18px; border: 1px solid var(--surface-border); background: rgba(255,249,238,0.96); box-shadow: var(--shadow-card); backdrop-filter: blur(14px); display: grid; gap: 4px; }
.floating-timer span { font-size: 0; color: transparent; }
.floating-timer span::before { content: "\0412\0440\0435\043C\044F"; font-size: 12px; color: var(--text-secondary); }
.floating-timer strong { font-size: 24px; line-height: 1; font-variant-numeric: tabular-nums; }
.floating-timer.warn { border-color: rgba(198,90,90,0.4); box-shadow: 0 18px 40px rgba(198,90,90,0.18); }
.action-btn { display: inline-flex; align-items: center; justify-content: center; border: 0; border-radius: 14px; padding: 12px 18px; background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%); color: var(--text); font-weight: 700; cursor: pointer; text-decoration: none; box-shadow: 0 12px 26px rgba(201,168,76,0.24); }
.action-btn.secondary { background: var(--info-soft); color: var(--info); border: 1px solid rgba(26,95,168,0.18); box-shadow: none; }
.action-btn:disabled { opacity: 0.6; cursor: not-allowed; }
:deep(.result-panel .state-panel__actions > :nth-child(1)),
:deep(.result-panel .state-panel__actions > :nth-child(2)) { display: none; }
.hero-stats > :nth-child(2),
.progress-meta > :nth-child(2),
.sticky-footer > :nth-child(2) { display: none; }
@media (max-width: 980px) { .question-card { min-height: 0; } }
@media (max-width: 900px) { .exam-header, .sticky-footer { flex-direction: column; align-items: stretch; } .hero-stats { grid-template-columns: 1fr 1fr; } }
@media (max-width: 768px) {
  .quiz-page { padding-top: 30px; padding-bottom: 120px; }
  .question-card { min-height: 0; padding: 22px 20px; }
  .exam-header { padding: 18px 20px; }
  .hero-stats { grid-template-columns: repeat(2, 1fr); gap: 10px; }
  .answer-option { border-radius: 16px; }
}
@media (max-width: 640px) {
  .quiz-page { padding: 0 0 100px; background: var(--bg); overflow-x: hidden; max-width: 100vw; }
  .exam-shell { gap: 0; overflow-x: clip; }
  .floating-timer { display: none; }
  .progress-card { border-radius: 0; border-left: none; border-right: none; border-top: none; padding: 12px 16px 14px; }
  .progress-top { display: none; }
  .progress-track { height: 4px; margin-top: 6px; margin-bottom: 0; }
  .progress-meta { display: none; }
  .question-map { gap: 8px; margin-top: 0; padding: 0; }
  .question-dot { flex: 0 0 38px; width: 38px; height: 38px; font-size: 13px; }
  .question-card { border-radius: 0; border-left: none; border-right: none; padding: 20px 16px; min-height: 0; }
  .question-header { flex-direction: row; align-items: flex-start; gap: 10px; margin-bottom: 14px; }
  .question-hint { font-size: 11px; }
  h2 { font-size: 18px; word-break: break-word; overflow-wrap: break-word; }
  .question-header > div { min-width: 0; }
  .question-image-shell { min-height: 200px; max-height: 240px; border-radius: 14px; }
  .answer-list { gap: 10px; }
  .answer-option { display: flex; align-items: flex-start; gap: 12px; padding: 16px 14px; border-radius: 14px; font-size: 15px; }
  .answer-option input { display: none; }
  .answer-label { width: 28px; height: 28px; font-size: 13px; flex-shrink: 0; margin-top: 1px; }
  .answer-text { flex: 1; min-width: 0; word-break: break-word; }
  .answer-option.selected { border-color: rgba(26,95,168,0.5); background: rgba(26,95,168,0.12); }
  .sticky-footer { position: fixed; bottom: 0; left: 0; right: 0; border-radius: 0; border-left: none; border-right: none; border-bottom: none; padding: 12px 16px; display: flex; gap: 10px; }
  .sticky-footer > :nth-child(2) { display: flex; }
  .mobile-nav-btn { flex: 0 0 48px; width: 48px; padding: 12px 0; font-size: 18px; }
  .mobile-next-btn { flex: 1; font-size: 16px; }
  .question-header { flex-direction: row; }
  .intro-card { border-radius: 0; padding: 20px 16px; gap: 14px; }
  .intro-card h1 { font-size: 22px; }
  .intro-card .description { font-size: 14px; margin-top: 4px; }
  .intro-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
  .intro-item { padding: 12px; }
  .rules-list { font-size: 13px; padding-left: 16px; line-height: 1.5; }
  .intro-actions { flex-direction: column; }
  .intro-actions .action-btn { width: 100%; }
}

@media (max-width: 380px) {
  .mobile-topbar { padding: 10px 12px; gap: 8px; }
  .mobile-title { font-size: 10px; }
  .mobile-qcount { font-size: 12px; }
  .mobile-timer { font-size: 18px; }
  .progress-card { padding: 8px 12px 10px; }
  .question-dot { flex: 0 0 32px; width: 32px; height: 32px; font-size: 12px; }
  .question-card { padding: 14px 12px; }
  .question-hint { font-size: 10px; }
  .question-image-shell { min-height: 160px; max-height: 200px; }
  .answer-list { gap: 8px; }
  .answer-option { padding: 12px 10px; gap: 10px; border-radius: 12px; font-size: 14px; }
  .answer-label { width: 26px; height: 26px; font-size: 11px; }
  .sticky-footer { padding: 10px 12px; }
  .mobile-nav-btn { flex: 0 0 44px; width: 44px; font-size: 16px; }
  .mobile-next-btn { font-size: 14px; }
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
  }
  .mobile-back {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(255,252,244,0.9);
    border: 1px solid var(--surface-border);
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700;
    color: var(--text);
    text-decoration: none;
    flex-shrink: 0;
  }
  .mobile-topbar-center { flex: 1; min-width: 0; }
  .mobile-title { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--accent-strong); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .mobile-qcount { display: block; font-size: 13px; color: var(--text-secondary); }
  .mobile-timer {
    font-size: 20px; font-weight: 700; font-variant-numeric: tabular-nums;
    color: var(--text); flex-shrink: 0;
  }
  .mobile-timer.warn { color: #c65a5a; }
}
</style>
