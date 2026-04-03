<template>
  <div class="training-page">
    <div v-if="loading" class="state-card">Загружаем тренировку...</div>
    <div v-else-if="errorMessage" class="state-card error">{{ errorMessage }}</div>

    <template v-else-if="result">
      <section class="result-card">
        <p class="eyebrow">Результат тренировки</p>
        <h1>{{ result.score }} / {{ result.total }}</h1>
        <p class="description">Правильных ответов: {{ result.percent }}%</p>
        <div class="items">
          <article v-for="item in result.items" :key="item.question_id" class="item-card" :class="{ ok: item.is_correct, bad: !item.is_correct }">
            <strong>{{ item.question }}</strong>
            <p>Правильный ответ: {{ item.correct_answer }}</p>
            <p>{{ item.explanation }}</p>
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
.training-page { min-height: 100vh; padding: 110px 20px 60px; background: radial-gradient(circle at top left, rgba(201,171,99,.14), transparent 24%), var(--bg); color: var(--text-primary); }
.intro-card, .question-list, .footer, .state-card, .result-card { max-width: 980px; margin: 0 auto; }
.intro-card, .state-card, .result-card, .question-card { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 24px; padding: 24px; box-shadow: var(--shadow-card); }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; font-size: 12px; font-weight: 700; color: var(--accent-strong); }
.description { color: var(--text-secondary); line-height: 1.6; }
.question-list { display: grid; gap: 16px; margin-top: 18px; }
.answer-list { display: grid; gap: 10px; margin-top: 16px; }
.answer-option { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-radius: 16px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.8); }
.footer { margin-top: 18px; display: flex; justify-content: flex-end; }
.primary-btn { border: 0; border-radius: 14px; padding: 14px 18px; background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%); color: var(--text); font-weight: 700; cursor: pointer; box-shadow: 0 12px 26px rgba(201,171,99,.2); }
.items { display: grid; gap: 12px; margin: 18px 0; }
.item-card { border-radius: 18px; padding: 16px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.82); }
.item-card.ok { background: rgba(34,197,94,0.08); }
.item-card.bad { background: rgba(198,90,90,0.08); }
.error { color: #8f3b3b; }
</style>
