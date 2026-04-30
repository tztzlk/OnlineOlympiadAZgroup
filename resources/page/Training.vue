<template>
  <div class="training-page">
    <div v-if="loading" class="state-card">Загружаем тренировку...</div>
    <div v-else-if="errorMessage" class="state-card error">{{ errorMessage }}</div>

    <template v-else-if="result">
      <section class="result-card">
        <p class="eyebrow">Результат тренировки</p>
        <h1>{{ result.score }} / {{ result.total }}</h1>
        <p class="description">{{ correctAnswersLabel }}: {{ result.percent }}%</p>

        <div class="items">
          <article v-for="item in result.items" :key="item.question_id" class="item-card" :class="{ ok: item.is_correct, bad: !item.is_correct }">
            <div class="item-card__header">
              <strong>{{ item.question }}</strong>
              <span class="result-chip" :class="{ ok: item.is_correct, bad: !item.is_correct }">
                {{ item.is_correct ? 'Верно' : 'Есть ошибка' }}
              </span>
            </div>

            <div v-if="item.image" class="question-image-shell">
              <img :src="item.image" alt="question" class="question-image" />
            </div>

            <div class="result-meta">
              <p>
                <span>{{ yourAnswerLabel }}</span>
                <strong>{{ formatAnswer(item.selected_answer, answerNotSelectedLabel) }}</strong>
              </p>
              <p>
                <span>{{ correctAnswerLabel }}</span>
                <strong>{{ formatAnswer(item.correct_answer, notFoundLabel) }}</strong>
              </p>
            </div>

            <div v-if="item.explanation" class="explanation-card">
              <span>{{ explanationLabel }}</span>
              <p>{{ item.explanation }}</p>
            </div>
          </article>
        </div>

        <button class="primary-btn" @click="router.push('/profile')">Вернуться в профиль</button>
      </section>
    </template>

    <template v-else-if="quiz">
      <section class="intro-card">
        <p class="eyebrow">Тренировка</p>
        <h1>{{ quiz.title }}</h1>
        <p class="description">
          Бесплатный тренировочный режим для {{ quiz.child?.full_name }}.
          После завершения вы сразу увидите правильные ответы и разбор.
        </p>
      </section>

      <section class="question-list">
        <article v-for="question in quiz.questions" :key="question.id" class="question-card">
          <h2>{{ question.question }}</h2>

          <div v-if="question.image" class="question-image-shell">
            <img :src="question.image" alt="question" class="question-image" />
          </div>

          <div class="answer-list">
            <label v-for="answer in question.answers" :key="answer.id" class="answer-option">
              <input v-model="answers[question.id]" type="radio" :name="`q-${question.id}`" :value="answer.id" />
              <span>{{ answer.label }}. {{ answer.answer }}</span>
            </label>
          </div>
        </article>
      </section>

      <div class="footer">
        <button class="primary-btn" :disabled="submitting" @click="submit">
          {{ submitting ? 'Проверяем...' : 'Проверить ответы' }}
        </button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../js/api'
import { useUserStore } from '../stores/user'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const loading = ref(true)
const submitting = ref(false)
const errorMessage = ref('')
const quiz = ref(null)
const result = ref(null)
const answers = ref({})
const activeChildId = ref(null)

const correctAnswersLabel = '\u041F\u0440\u0430\u0432\u0438\u043B\u044C\u043D\u044B\u0445 \u043E\u0442\u0432\u0435\u0442\u043E\u0432'
const yourAnswerLabel = '\u0412\u0430\u0448 \u043E\u0442\u0432\u0435\u0442'
const answerNotSelectedLabel = '\u041E\u0442\u0432\u0435\u0442 \u043D\u0435 \u0432\u044B\u0431\u0440\u0430\u043D'
const correctAnswerLabel = '\u041F\u0440\u0430\u0432\u0438\u043B\u044C\u043D\u044B\u0439 \u043E\u0442\u0432\u0435\u0442'
const notFoundLabel = '\u041D\u0435 \u043D\u0430\u0439\u0434\u0435\u043D'
const explanationLabel = '\u0420\u0430\u0437\u0431\u043E\u0440'

const formatAnswer = (answer, fallback) => {
  if (!answer) return fallback

  return answer.label ? `${answer.label}. ${answer.answer}` : answer.answer
}

const syncSelectedChild = () => {
  const queryChildId = route.query.childId ? String(route.query.childId) : null
  activeChildId.value = queryChildId || userStore.selectedChildId || null

  if (activeChildId.value) {
    userStore.setSelectedChild(activeChildId.value)
  }
}

const load = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    await userStore.fetchUser()
    syncSelectedChild()

    const { data } = await api.get(`/training/${route.params.subjectId}`, {
      params: activeChildId.value ? { child_profile_id: activeChildId.value } : {},
    })

    quiz.value = data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Не удалось загрузить тренировку.'
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  if (!quiz.value) return

  submitting.value = true

  try {
    const { data } = await api.post(`/training/${quiz.value.id}/submit`, {
      child_profile_id: activeChildId.value,
      answers: answers.value,
    })

    result.value = data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Не удалось отправить тренировку.'
  } finally {
    submitting.value = false
  }
}

onMounted(load)
</script>

<style scoped>
* { box-sizing: border-box; }
.training-page { min-height: 100vh; padding: 110px 20px 60px; background: radial-gradient(circle at top left, rgba(201,171,99,.14), transparent 24%), var(--bg); color: var(--text-primary); width: 100%; max-width: 100%; overflow-x: hidden; }
.intro-card, .question-list, .footer, .state-card, .result-card { max-width: 980px; margin: 0 auto; }
.intro-card, .state-card, .result-card, .question-card { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 24px; padding: 24px; box-shadow: var(--shadow-card); width: 100%; min-width: 0; overflow: hidden; }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; font-size: 12px; font-weight: 700; color: var(--accent-strong); }
.description { color: var(--text-secondary); line-height: 1.6; }
.question-list { display: grid; gap: 16px; margin-top: 18px; }
.question-image-shell { margin-top: 16px; border-radius: 18px; overflow: hidden; border: 1px solid var(--surface-border); background: rgba(255,252,244,.92); }
.question-image { display: block; width: 100%; max-height: 360px; object-fit: contain; background: #fff; }
.answer-list { display: grid; gap: 10px; margin-top: 16px; }
.answer-option { display: flex; gap: 10px; align-items: flex-start; width: 100%; min-width: 0; padding: 12px 14px; border-radius: 16px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.8); }
.question-card h2,
.answer-option span,
.item-card__header strong,
.explanation-card p { min-width: 0; overflow-wrap: anywhere; word-break: break-word; }
.answer-option span { flex: 1; }
.footer { margin-top: 18px; display: flex; justify-content: flex-end; }
.primary-btn { border: 0; border-radius: 14px; padding: 14px 18px; min-height: 2.75rem; background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%); color: var(--text); font-weight: 700; cursor: pointer; box-shadow: 0 12px 26px rgba(201,171,99,.2); }
.items { display: grid; gap: 12px; margin: 18px 0; }
.item-card { border-radius: 18px; padding: 16px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.82); }
.item-card.ok { background: rgba(34,197,94,0.08); }
.item-card.bad { background: rgba(198,90,90,0.08); }
.item-card__header { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
.result-chip { display: inline-flex; align-items: center; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; background: rgba(26,95,168,.12); color: var(--info); }
.result-chip.ok { background: rgba(34,197,94,0.14); color: #2f6f4b; }
.result-chip.bad { background: rgba(198,90,90,0.14); color: #8f3b3b; }
.result-meta { display: grid; gap: 10px; margin-top: 16px; }
.result-meta p, .explanation-card p { margin: 0; }
.result-meta span, .explanation-card span { display: block; margin-bottom: 4px; color: var(--text-secondary); font-size: 13px; }
.explanation-card { margin-top: 16px; padding: 14px 16px; border-radius: 16px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.72); }
.error { color: #8f3b3b; }
@media (max-width: 768px) {
  .training-page { padding: 88px 16px 40px; }
  .intro-card, .state-card, .result-card, .question-card { padding: 20px 16px; border-radius: 20px; }
  .footer { justify-content: stretch; }
  .primary-btn { width: 100%; }
}
@media (max-width: 640px) {
  .training-page { padding: calc(72px + env(safe-area-inset-top, 0px)) max(12px, env(safe-area-inset-right, 0px)) calc(32px + env(safe-area-inset-bottom, 0px)) max(12px, env(safe-area-inset-left, 0px)); }
  .question-list { gap: 12px; }
  .question-image { max-height: 240px; }
  .answer-list { gap: 8px; }
  .answer-option { padding: 14px 12px; }
}
</style>
