<template>
  <div class="quiz-page">
    <div v-if="loading" class="state-card">Загружаем олимпиаду...</div>
    <div v-else-if="loadError" class="state-card error">{{ loadErrorMessage }}</div>

    <template v-else-if="quiz && !result">
      <header class="hero">
        <div>
          <p class="eyebrow">{{ quiz.subject?.name || 'Олимпиада' }}</p>
          <h1>{{ quiz.title }}</h1>
          <p class="description">{{ quiz.description || 'Ответьте на все вопросы и отправьте работу на проверку.' }}</p>
        </div>

        <div class="hero-stats">
          <div class="stat-box">
            <span>Ответов</span>
            <strong>{{ answeredCount }}/{{ quiz.questions.length }}</strong>
          </div>
          <div class="stat-box" :class="{ warn: timeLeft < 300 }">
            <span>Время</span>
            <strong>{{ formatTime(timeLeft) }}</strong>
          </div>
        </div>
      </header>

      <div class="progress-track">
        <div class="progress-fill" :style="{ width: `${progressPercent}%` }"></div>
      </div>

      <section class="question-list">
        <article v-for="(question, index) in quiz.questions" :key="question.id" class="question-card">
          <div class="question-header">
            <span class="question-index">{{ index + 1 }}</span>
            <h2>{{ question.question }}</h2>
          </div>

          <div class="answer-list">
            <label
              v-for="answer in question.answers"
              :key="answer.id"
              class="answer-option"
              :class="{ selected: userAnswers[question.id] === answer.id }"
            >
              <input
                v-model="userAnswers[question.id]"
                type="radio"
                :name="`question-${question.id}`"
                :value="answer.id"
              />
              <span class="answer-label">{{ answer.label }}</span>
              <span class="answer-text">{{ answer.answer }}</span>
            </label>
          </div>
        </article>
      </section>

      <footer class="sticky-footer">
        <div class="footer-copy">
          <strong>{{ unansweredCount }}</strong>
          <span>{{ unansweredCount === 1 ? 'вопрос без ответа' : 'вопросов без ответа' }}</span>
        </div>
        <button class="submit-btn" :disabled="submitting" @click="submitQuiz">
          {{ submitting ? 'Отправляем...' : 'Сдать олимпиаду' }}
        </button>
      </footer>
    </template>

    <div v-else-if="result" class="result-card">
      <p class="eyebrow">Результат</p>
      <h1>{{ result.score }} / {{ result.total }}</h1>
      <p class="description">Процент правильных ответов: {{ result.percent }}%</p>
      <span class="status-chip" :class="result.status">
        {{ result.status === 'passed' ? 'Пройдено' : 'Не пройдено' }}
      </span>
      <button class="submit-btn" @click="router.push('/results')">Перейти к результатам</button>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../js/api'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const loadError = ref(false)
const loadErrorMessage = ref('')
const quiz = ref(null)
const result = ref(null)
const submitting = ref(false)
const userAnswers = ref({})
const timeLeft = ref(0)
let timerId = null

const answeredCount = computed(() => Object.keys(userAnswers.value).length)
const unansweredCount = computed(() => Math.max((quiz.value?.questions.length || 0) - answeredCount.value, 0))
const progressPercent = computed(() => {
  if (!quiz.value?.questions.length) return 0
  return Math.round((answeredCount.value / quiz.value.questions.length) * 100)
})

const formatTime = (seconds) => {
  const safeSeconds = Math.max(seconds, 0)
  const minutes = Math.floor(safeSeconds / 60)
  const remainder = safeSeconds % 60
  return `${String(minutes).padStart(2, '0')}:${String(remainder).padStart(2, '0')}`
}

const startTimer = (minutes) => {
  startTimerFromSeconds(minutes * 60)
}

const startTimerFromSeconds = (seconds) => {
  if (timerId) clearInterval(timerId)
  timeLeft.value = seconds
  timerId = window.setInterval(() => {
    if (timeLeft.value <= 0) {
      clearInterval(timerId)
      submitQuiz()
      return
    }

    timeLeft.value -= 1
  }, 1000)
}

const loadQuiz = async () => {
  loading.value = true
  loadError.value = false
  loadErrorMessage.value = ''
  try {
    const { data } = await api.get(`/quiz/${route.params.subjectId}`)
    quiz.value = data
    if (data.already_submitted) {
      router.push('/results')
      return
    }

    startTimer(data.time_limit || 60)
  } catch (error) {
    loadError.value = true
    loadErrorMessage.value = error.response?.status === 404
      ? 'По этому предмету олимпиада пока не опубликована.'
      : (error.response?.data?.message || 'Не удалось загрузить олимпиаду.')
    console.error(error)
  } finally {
    loading.value = false
  }
}

const submitQuiz = async () => {
  if (!quiz.value || submitting.value) return

  submitting.value = true
  clearInterval(timerId)

  try {
    const { data } = await api.post(`/quiz/${quiz.value.id}/submit`, {
      answers: userAnswers.value,
    })
    result.value = data
  } catch (error) {
    console.error(error)
    alert(error.response?.data?.message || 'Не удалось отправить ответы.')
    startTimerFromSeconds(timeLeft.value || 60)
  } finally {
    submitting.value = false
  }
}

onMounted(loadQuiz)

onUnmounted(() => {
  if (timerId) clearInterval(timerId)
})
</script>

<style scoped>
* { box-sizing: border-box; }

.quiz-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #10131d 0%, #181c28 100%);
  color: #fff;
  padding: 20px 20px 110px;
}

.state-card,
.hero,
.question-card,
.result-card {
  max-width: 960px;
  margin: 0 auto;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  backdrop-filter: blur(10px);
}

.state-card,
.result-card {
  padding: 28px;
  margin-top: 40px;
}

.error {
  color: #fecaca;
}

.hero {
  padding: 24px;
  display: flex;
  justify-content: space-between;
  gap: 20px;
  align-items: flex-start;
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #f43f5e;
  font-size: 12px;
  font-weight: 700;
}

h1 {
  margin: 0;
  font-size: clamp(28px, 4vw, 42px);
}

.description {
  margin: 12px 0 0;
  color: #c4cad8;
  line-height: 1.6;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(120px, 1fr));
  gap: 12px;
}

.stat-box {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 18px;
  padding: 14px 16px;
}

.stat-box span {
  display: block;
  font-size: 12px;
  color: #c4cad8;
  margin-bottom: 6px;
}

.stat-box strong {
  font-size: 22px;
}

.warn {
  outline: 2px solid rgba(244, 63, 94, 0.5);
}

.progress-track {
  max-width: 960px;
  margin: 16px auto;
  height: 8px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.08);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #f43f5e, #fb7185);
}

.question-list {
  max-width: 960px;
  margin: 0 auto;
  display: grid;
  gap: 16px;
}

.question-card {
  padding: 20px;
}

.question-header {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  margin-bottom: 16px;
}

.question-header h2 {
  margin: 0;
  font-size: 20px;
  line-height: 1.5;
}

.question-index {
  width: 34px;
  height: 34px;
  border-radius: 12px;
  background: rgba(244, 63, 94, 0.2);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  flex-shrink: 0;
}

.answer-list {
  display: grid;
  gap: 10px;
}

.answer-option {
  display: grid;
  grid-template-columns: 28px 24px 1fr;
  gap: 12px;
  align-items: center;
  padding: 14px 16px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(255, 255, 255, 0.03);
  cursor: pointer;
}

.answer-option input {
  margin: 0;
}

.answer-option.selected {
  border-color: rgba(244, 63, 94, 0.7);
  background: rgba(244, 63, 94, 0.12);
}

.answer-label {
  width: 24px;
  height: 24px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.08);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 700;
}

.sticky-footer {
  position: fixed;
  left: 16px;
  right: 16px;
  bottom: 16px;
  max-width: 960px;
  margin: 0 auto;
  background: rgba(16, 19, 29, 0.92);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 20px;
  padding: 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 14px;
  backdrop-filter: blur(10px);
}

.footer-copy {
  display: flex;
  gap: 8px;
  align-items: baseline;
  flex-wrap: wrap;
}

.submit-btn {
  border: 0;
  border-radius: 14px;
  padding: 12px 18px;
  background: linear-gradient(90deg, #f43f5e, #be123c);
  color: #fff;
  font-weight: 700;
  cursor: pointer;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: wait;
}

.result-card {
  text-align: center;
}

.status-chip {
  display: inline-flex;
  padding: 6px 10px;
  border-radius: 999px;
  margin: 16px 0;
  font-weight: 700;
}

.status-chip.passed { background: #e8f8ed; color: #1f7a34; }
.status-chip.failed { background: #ffe7e7; color: #9f1d1d; }

@media (max-width: 768px) {
  .hero,
  .sticky-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .hero-stats {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .quiz-page {
    padding: 14px 14px 120px;
  }

  .answer-option {
    grid-template-columns: 24px 24px 1fr;
    padding: 12px;
  }

  .question-header h2 {
    font-size: 18px;
  }
}
</style>
