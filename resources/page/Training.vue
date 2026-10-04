<template>
  <div class="training-page">
    <div class="training-wrap">
      <StatePanel v-if="loading" tone="neutral" loading eyebrow="Тренировка" title="Загружаем тренировку..." description="Подбираем задания по классу участника." />

      <StatePanel v-else-if="errorMessage" tone="warning" eyebrow="Тренировка" :title="errorMessage" description="Проверьте, что выбран профиль ребёнка, и попробуйте ещё раз.">
        <template #actions>
          <RouterLink class="ds-btn ds-btn-primary" to="/profile">Вернуться в профиль</RouterLink>
        </template>
      </StatePanel>

      <!-- ===== Результат ===== -->
      <template v-else-if="result">
        <section class="result-hero" aria-labelledby="training-result-title">
          <div class="score-ring" :style="{ '--p': result.percent }" role="img" :aria-label="`${correctAnswersLabel}: ${result.percent}%`">
            <span>{{ result.percent }}%</span>
          </div>
          <div class="result-hero__text">
            <p class="eyebrow">Результат тренировки</p>
            <h1 id="training-result-title">{{ result.score }} / {{ result.total }}</h1>
            <p class="description">{{ correctAnswersLabel }}: {{ result.percent }}%</p>
          </div>
          <button type="button" class="ds-btn ds-btn-primary ds-btn-lg" @click="router.push('/profile')">Вернуться в профиль</button>
        </section>

        <ol class="items">
          <li v-for="(item, index) in result.items" :key="item.question_id" class="item-card" :class="item.is_correct ? 'is-ok' : 'is-bad'">
            <div class="item-card__header">
              <span class="item-card__num">{{ index + 1 }}</span>
              <strong class="item-card__question">{{ item.question }}</strong>
              <span class="result-chip">
                <svg v-if="item.is_correct" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
                {{ item.is_correct ? 'Верно' : 'Есть ошибка' }}
              </span>
            </div>

            <div v-if="item.image" class="question-image-shell">
              <img :src="item.image" :alt="`Иллюстрация к вопросу ${index + 1}`" class="question-image" />
            </div>

            <div class="result-meta">
              <p class="result-meta__row" :class="item.is_correct ? 'is-ok' : 'is-bad'">
                <span>{{ yourAnswerLabel }}</span>
                <strong>{{ formatAnswer(item.selected_answer, answerNotSelectedLabel) }}</strong>
              </p>
              <p v-if="!item.is_correct" class="result-meta__row is-ok">
                <span>{{ correctAnswerLabel }}</span>
                <strong>{{ formatAnswer(item.correct_answer, notFoundLabel) }}</strong>
              </p>
            </div>

            <div v-if="item.explanation" class="explanation-card">
              <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/></svg>
                {{ explanationLabel }}
              </span>
              <p>{{ item.explanation }}</p>
            </div>
          </li>
        </ol>
      </template>

      <!-- ===== Задания ===== -->
      <template v-else-if="quiz">
        <section class="intro-card">
          <span class="intro-card__badge" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5l11 11M21 21l-1-1M3 3l1 1M18 22l4-4M2 6l4-4M3 10l7-7M14 21l7-7"/></svg>
          </span>
          <div>
            <p class="eyebrow">Тренировка</p>
            <h1>{{ quiz.title }}</h1>
            <p class="description">
              Бесплатный тренировочный режим для {{ quiz.child?.full_name }}.
              После завершения вы сразу увидите правильные ответы и разбор.
            </p>
          </div>
        </section>

        <ol class="question-list">
          <li v-for="(question, index) in quiz.questions" :key="question.id" class="question-card" :class="{ 'is-answered': answers[question.id] }">
            <div class="question-card__head">
              <span class="question-card__num">{{ index + 1 }}</span>
              <h2>{{ question.question }}</h2>
            </div>

            <div v-if="question.image" class="question-image-shell">
              <img :src="question.image" :alt="`Иллюстрация к вопросу ${index + 1}`" class="question-image" />
            </div>

            <div class="answer-list" role="radiogroup" :aria-label="`Варианты ответа на вопрос ${index + 1}`">
              <label v-for="answer in question.answers" :key="answer.id" class="answer-option" :class="{ selected: answers[question.id] === answer.id }">
                <input v-model="answers[question.id]" class="answer-input" type="radio" :name="`q-${question.id}`" :value="answer.id" />
                <span class="answer-label">{{ answer.label }}</span>
                <span class="answer-text">{{ answer.answer }}</span>
              </label>
            </div>
          </li>
        </ol>

        <div class="footer">
          <div class="footer__progress">
            <div class="ds-progress-track"><div class="ds-progress-fill" :style="{ width: `${answeredPercent}%` }"></div></div>
            <span>{{ answeredCount }} / {{ quiz.questions.length }}</span>
          </div>
          <button type="button" class="ds-btn ds-btn-primary ds-btn-lg" :disabled="submitting" @click="submit">
            {{ submitting ? 'Проверяем...' : 'Проверить ответы' }}
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../js/api'
import { useUserStore } from '../stores/user'
import StatePanel from '../components/StatePanel.vue'

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

const correctAnswersLabel = 'Правильных ответов'
const yourAnswerLabel = 'Ваш ответ'
const answerNotSelectedLabel = 'Ответ не выбран'
const correctAnswerLabel = 'Правильный ответ'
const notFoundLabel = 'Не найден'
const explanationLabel = 'Разбор'

const answeredCount = computed(() => Object.values(answers.value).filter(Boolean).length)
const answeredPercent = computed(() => {
  const total = quiz.value?.questions?.length || 0
  return total ? Math.round((answeredCount.value / total) * 100) : 0
})

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
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Не удалось отправить тренировку.'
  } finally {
    submitting.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.training-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 32px) 20px 72px;
  background:
    radial-gradient(700px 360px at 0% 0%, color-mix(in srgb, var(--tone-violet) 10%, transparent), transparent 70%),
    var(--bg);
  overflow-x: hidden;
}

.training-wrap {
  max-width: 860px;
  margin: 0 auto;
  display: grid;
  gap: 16px;
}

.eyebrow {
  margin-bottom: 4px;
  color: var(--tone-violet);
  font-size: 14px;
  font-weight: 600;
}

h1 { font-size: clamp(24px, 3.4vw, 34px); }

.description {
  margin-top: 8px;
  color: var(--text-secondary);
  font-size: 16.5px;
  line-height: 1.6;
}

/* ---- Шапка ---- */
.intro-card {
  display: flex;
  gap: 18px;
  align-items: flex-start;
  padding: clamp(22px, 3vw, 32px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}

.intro-card__badge {
  width: 60px;
  height: 60px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 18px;
  background: var(--tone-violet);
  color: #ffffff;
  transform: rotate(-5deg);
}

/* ---- Вопросы ---- */
.question-list {
  display: grid;
  gap: 14px;
  list-style: none;
}

.question-card {
  padding: clamp(20px, 3vw, 28px);
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
  overflow-wrap: anywhere;
  transition: border-color var(--dur) ease;
}

.question-card.is-answered { border-color: color-mix(in srgb, var(--brand) 30%, var(--border)); }

.question-card__head {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}

.question-card__num {
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 12px;
  background: var(--bg-alt);
  color: var(--text-secondary);
  font-weight: 700;
  transition: background-color var(--dur) ease, color var(--dur) ease;
}

.question-card.is-answered .question-card__num { background: var(--brand); color: #ffffff; }

.question-card h2 {
  padding-top: 6px;
  font-family: var(--font-sans);
  font-size: 18px;
  font-weight: 600;
  line-height: 1.45;
  letter-spacing: -0.01em;
}

.question-image-shell {
  margin-top: 16px;
  padding: 12px;
  border-radius: var(--radius-md);
  background: var(--bg);
  overflow: hidden;
}

.question-image {
  display: block;
  width: 100%;
  max-height: 340px;
  object-fit: contain;
  border-radius: var(--radius-xs);
}

.answer-list {
  display: grid;
  gap: 10px;
  margin-top: 16px;
}

.answer-option {
  display: flex;
  align-items: center;
  gap: 12px;
  min-height: 56px;
  padding: 10px 14px 10px 10px;
  border-radius: var(--radius-md);
  border: 2px solid var(--border);
  background: var(--card);
  cursor: pointer;
  transition: border-color var(--dur) ease, background-color var(--dur) ease;
}

@media (hover: hover) and (pointer: fine) {
  .answer-option:hover:not(.selected) { border-color: color-mix(in srgb, var(--brand) 45%, var(--border)); background: var(--brand-softer); }
}

.answer-option.selected { border-color: var(--brand); background: var(--brand-soft); }
.answer-option:has(.answer-input:focus-visible) { box-shadow: var(--focus-ring); }

.answer-input {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
}

.answer-label {
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 11px;
  background: var(--bg-alt);
  color: var(--text-secondary);
  font-weight: 700;
}

.answer-option.selected .answer-label { background: var(--brand); color: #ffffff; }

.answer-text {
  flex: 1;
  min-width: 0;
  font-size: 16px;
  line-height: 1.5;
}

/* ---- Нижняя панель ---- */
.footer {
  position: sticky;
  bottom: 16px;
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 12px 12px 20px;
  border-radius: var(--radius-lg);
  background: color-mix(in srgb, var(--card) 94%, transparent);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-md);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

.footer__progress {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

/* ---- Результат ---- */
.result-hero {
  display: flex;
  align-items: center;
  gap: 24px;
  padding: clamp(22px, 3vw, 32px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-md);
}

.result-hero__text { flex: 1; min-width: 0; }

.score-ring {
  --p: 0;
  width: 108px;
  height: 108px;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background:
    radial-gradient(closest-side, var(--card) 78%, transparent 80%),
    conic-gradient(var(--success) calc(var(--p) * 1%), var(--bg-alt) 0);
}

.score-ring span {
  font-family: var(--font-display);
  font-size: 24px;
  font-weight: 700;
}

.items {
  display: grid;
  gap: 12px;
  list-style: none;
}

.item-card {
  padding: 20px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
  border-top: 4px solid var(--success);
}

.item-card.is-bad { border-top-color: var(--danger); }

.item-card__header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.item-card__num {
  width: 32px;
  height: 32px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 10px;
  background: var(--bg-alt);
  font-size: 14px;
  font-weight: 700;
}

.item-card__question {
  flex: 1;
  min-width: 0;
  padding-top: 4px;
  font-size: 16.5px;
  line-height: 1.45;
  overflow-wrap: anywhere;
}

.result-chip {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  flex-shrink: 0;
  padding: 5px 10px;
  border-radius: var(--radius-xs);
  font-size: 13px;
  font-weight: 600;
  background: var(--success-soft);
  color: var(--success-ink);
}

.item-card.is-bad .result-chip { background: var(--danger-soft); color: var(--danger-ink); }

.result-meta {
  display: grid;
  gap: 8px;
  margin-top: 14px;
}

.result-meta__row {
  display: grid;
  gap: 2px;
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  background: var(--success-soft);
}

.result-meta__row.is-bad { background: var(--danger-soft); }

.result-meta__row span {
  color: var(--text-secondary);
  font-size: 13px;
}

.result-meta__row strong {
  font-size: 15.5px;
  overflow-wrap: anywhere;
}

.explanation-card {
  margin-top: 10px;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  background: var(--sun-soft);
}

.explanation-card span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 4px;
  color: var(--sun-ink);
  font-size: 13px;
  font-weight: 700;
}

.explanation-card p {
  font-size: 15px;
  line-height: 1.6;
  overflow-wrap: anywhere;
}

@media (max-width: 640px) {
  .training-page { padding: calc(var(--header-h) + 16px) 12px 96px; }
  .intro-card { flex-direction: column; }
  .result-hero { flex-direction: column; text-align: center; }
  .result-hero .ds-btn { width: 100%; }
  .footer { flex-direction: column; align-items: stretch; padding: 12px; }
  .item-card__header { flex-wrap: wrap; }
}
</style>
