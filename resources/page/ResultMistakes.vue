<template>
  <div class="mistakes-page">
    <StatePanel
      v-if="loading"
      tone="neutral"
      eyebrow="Работа над ошибками"
      title="Готовим разбор"
      description="Собираем вопросы, ответы и объяснения по каждому заданию."
    />

    <StatePanel
      v-else-if="errorMessage"
      tone="warning"
      eyebrow="Работа над ошибками"
      title="Разбор пока недоступен"
      :description="errorMessage"
    >
      <template #actions>
        <RouterLink class="action-btn secondary" to="/results">К результатам</RouterLink>
      </template>
    </StatePanel>

    <StatePanel
      v-else-if="!payload?.items?.length"
      tone="success"
      eyebrow="Работа над ошибками"
      title="Разбор пока пуст"
      description="В этой попытке нет сохранённых вопросов для отображения."
    >
      <template #actions>
        <RouterLink class="action-btn" to="/results">Перейти к результатам</RouterLink>
      </template>
    </StatePanel>

    <template v-else>
      <header class="mistakes-header">
        <div>
          <p class="eyebrow">Разбор олимпиады</p>
          <h1>{{ payload.quiz_title }}</h1>
          <p class="description">{{ payload.subject }} · {{ payload.child_name }}</p>
        </div>

        <div class="summary-card">
          <span>Нужно разобрать</span>
          <strong>{{ payload.mistakes_count }}</strong>
          <small>ошибок или пропусков</small>
        </div>
      </header>

      <div class="mistakes-list">
        <article v-for="(item, index) in payload.items" :key="item.question_id" class="mistake-card">
          <div class="mistake-card__top">
            <div class="mistake-card__title">
              <span class="mistake-index">{{ index + 1 }}</span>
              <div>
                <p class="mistake-label">{{ statusLabel(item.status) }}</p>
                <h2>{{ item.question }}</h2>
              </div>
            </div>
            <span class="mistake-status" :class="item.status">{{ statusBadge(item.status) }}</span>
          </div>

          <img v-if="item.image" :src="item.image" alt="question" class="mistake-image" />

          <div class="mistake-answers">
            <div class="answer-block muted">
              <span>Ваш ответ</span>
              <strong>{{ item.selected_answer ? `${item.selected_answer.label}. ${item.selected_answer.answer}` : 'Ответ не выбран' }}</strong>
            </div>
            <div class="answer-block correct">
              <span>Правильный ответ</span>
              <strong>{{ item.correct_answer ? `${item.correct_answer.label}. ${item.correct_answer.answer}` : 'Не найден' }}</strong>
            </div>
          </div>

          <div v-if="item.explanation" class="explanation-block">
            <span>Разбор</span>
            <p>{{ item.explanation }}</p>
          </div>
        </article>
      </div>

      <div class="mistakes-actions">
        <RouterLink class="action-btn secondary" to="/results">К результатам</RouterLink>
      </div>
    </template>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../js/api'
import StatePanel from '../components/StatePanel.vue'

const route = useRoute()

const loading = ref(true)
const errorMessage = ref('')
const payload = ref(null)

const loadMistakes = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const { data } = await api.get(`/profile/results/${route.params.resultId}/mistakes`)
    payload.value = data
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Не удалось открыть работу над ошибками.'
  } finally {
    loading.value = false
  }
}

const statusLabel = (status) => {
  if (status === 'correct') return 'Верный ответ'
  if (status === 'skipped') return 'Пропущенный вопрос'
  return 'Неверный ответ'
}

const statusBadge = (status) => {
  if (status === 'correct') return 'Верно'
  if (status === 'skipped') return 'Пропуск'
  return 'Ошибка'
}

onMounted(loadMistakes)
</script>

<style scoped>
* { box-sizing: border-box; }

.mistakes-page {
  min-height: 100vh;
  padding: 110px 20px 48px;
  background: radial-gradient(circle at top left, rgba(201, 171, 99, 0.14), transparent 24%), var(--bg);
  color: var(--text);
}

.mistakes-header,
.mistake-card,
.mistakes-actions {
  max-width: 1100px;
  margin: 0 auto;
}

.mistakes-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 18px;
  padding: 28px;
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--accent-strong);
  font-size: 12px;
  font-weight: 700;
}

h1 {
  margin: 0;
  font-size: clamp(30px, 4vw, 42px);
}

h2 {
  margin: 0;
  font-size: 22px;
  line-height: 1.45;
}

.description {
  margin-top: 12px;
  color: var(--text-secondary);
}

.summary-card {
  min-width: 180px;
  padding: 18px 20px;
  border-radius: 22px;
  background: rgba(255, 252, 244, 0.82);
  border: 1px solid var(--surface-border);
  display: grid;
  gap: 6px;
}

.summary-card span,
.summary-card small {
  color: var(--text-secondary);
}

.summary-card strong {
  font-size: 38px;
  line-height: 1;
}

.mistakes-list {
  display: grid;
  gap: 16px;
  margin-top: 18px;
}

.mistake-card {
  padding: 24px;
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
  display: grid;
  gap: 18px;
}

.mistake-card__top {
  display: flex;
  justify-content: space-between;
  gap: 14px;
  align-items: flex-start;
}

.mistake-card__title {
  display: flex;
  gap: 14px;
  align-items: flex-start;
}

.mistake-index {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  background: var(--info-soft);
  color: var(--info);
}

.mistake-label {
  margin: 0 0 8px;
  color: var(--text-secondary);
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.mistake-status {
  padding: 8px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.mistake-status.wrong {
  background: rgba(198, 90, 90, 0.12);
  color: #a54646;
}

.mistake-status.skipped {
  background: rgba(201, 171, 99, 0.16);
  color: #8d6a14;
}

.mistake-status.correct {
  background: rgba(44, 122, 75, 0.12);
  color: #2f6f4b;
}

.mistake-image {
  width: 100%;
  max-height: 320px;
  object-fit: contain;
  border-radius: 18px;
  background: rgba(255, 252, 244, 0.8);
  border: 1px solid var(--surface-border);
}

.mistake-answers {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.answer-block {
  padding: 16px;
  border-radius: 18px;
  border: 1px solid var(--surface-border);
  display: grid;
  gap: 8px;
}

.answer-block span,
.explanation-block span {
  color: var(--text-secondary);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.answer-block.correct {
  background: rgba(44, 122, 75, 0.08);
}

.answer-block.muted,
.explanation-block {
  background: rgba(255, 252, 244, 0.82);
}

.explanation-block {
  padding: 16px;
  border-radius: 18px;
  border: 1px solid var(--surface-border);
  display: grid;
  gap: 8px;
}

.explanation-block p {
  margin: 0;
  line-height: 1.65;
}

.mistakes-actions {
  margin-top: 18px;
}

.action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 0;
  border-radius: 14px;
  padding: 12px 18px;
  background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
  color: var(--text);
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  box-shadow: 0 12px 26px rgba(201, 168, 76, 0.24);
}

.action-btn.secondary {
  background: var(--info-soft);
  color: var(--info);
  border: 1px solid rgba(26, 95, 168, 0.18);
  box-shadow: none;
}

@media (max-width: 760px) {
  .mistakes-header,
  .mistake-card__top,
  .mistake-answers {
    grid-template-columns: 1fr;
    display: grid;
  }

  .mistakes-header {
    padding: 22px;
  }

  .summary-card {
    min-width: 0;
  }
}
</style>
