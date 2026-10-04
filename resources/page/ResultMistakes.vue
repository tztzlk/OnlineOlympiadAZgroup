<template>
  <div class="mistakes-page">
    <div class="mistakes-wrap">
      <StatePanel
        v-if="loading"
        tone="neutral" loading
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
          <RouterLink class="ds-btn ds-btn-ghost" to="/results">К результатам</RouterLink>
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
          <RouterLink class="ds-btn ds-btn-primary" to="/results">Перейти к результатам</RouterLink>
        </template>
      </StatePanel>

      <template v-else>
        <RouterLink to="/results" class="back-link">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
          К результатам
        </RouterLink>

        <header class="mistakes-header">
          <div class="mistakes-header__text">
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

        <div class="filters" role="tablist" aria-label="Фильтр вопросов">
          <button
            v-for="f in filters"
            :key="f.key"
            type="button"
            role="tab"
            class="filters__btn"
            :class="[`is-${f.key}`, { 'is-active': activeFilter === f.key }]"
            :aria-selected="activeFilter === f.key ? 'true' : 'false'"
            :disabled="!counts[f.key]"
            @click="activeFilter = f.key"
          >
            {{ f.label }}
            <span class="filters__count">{{ counts[f.key] }}</span>
          </button>
        </div>

        <ol class="mistakes-list">
          <li v-for="item in visibleItems" :key="item.question_id" class="mistake-card" :class="`is-${item.status}`">
            <div class="mistake-card__top">
              <span class="mistake-index">{{ item.index }}</span>
              <div class="mistake-card__title">
                <p class="mistake-label">{{ statusLabel(item.status) }}</p>
                <h2>{{ item.question }}</h2>
              </div>
              <span class="mistake-status">{{ statusBadge(item.status) }}</span>
            </div>

            <img v-if="item.image" :src="item.image" :alt="`Иллюстрация к вопросу ${item.index}`" class="mistake-image" />

            <div class="mistake-answers">
              <div class="answer-block" :class="item.status === 'correct' ? 'is-ok' : 'is-yours'">
                <span>Ваш ответ</span>
                <strong>{{ item.selected_answer ? `${item.selected_answer.label}. ${item.selected_answer.answer}` : 'Ответ не выбран' }}</strong>
              </div>
              <div class="answer-block is-ok">
                <span>Правильный ответ</span>
                <strong>{{ item.correct_answer ? `${item.correct_answer.label}. ${item.correct_answer.answer}` : 'Не найден' }}</strong>
              </div>
            </div>

            <div v-if="item.explanation" class="explanation-block">
              <span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/></svg>
                Разбор
              </span>
              <p>{{ item.explanation }}</p>
            </div>
          </li>
        </ol>

        <div class="mistakes-actions">
          <RouterLink class="ds-btn ds-btn-ghost ds-btn-lg" to="/results">К результатам</RouterLink>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../js/api'
import StatePanel from '../components/StatePanel.vue'

const route = useRoute()

const loading = ref(true)
const errorMessage = ref('')
const payload = ref(null)
const activeFilter = ref('all')

const filters = [
  { key: 'all', label: 'Все' },
  { key: 'wrong', label: 'Ошибки' },
  { key: 'skipped', label: 'Пропуски' },
  { key: 'correct', label: 'Верно' },
]

// Номер вопроса сохраняем исходным, чтобы при фильтрации он не «съезжал».
const indexedItems = computed(() => (payload.value?.items || []).map((item, i) => ({
  ...item,
  index: i + 1,
  status: item.status === 'correct' || item.status === 'skipped' ? item.status : 'wrong',
})))

const counts = computed(() => {
  const result = { all: indexedItems.value.length, wrong: 0, skipped: 0, correct: 0 }
  for (const item of indexedItems.value) result[item.status] += 1
  return result
})

const visibleItems = computed(() => activeFilter.value === 'all'
  ? indexedItems.value
  : indexedItems.value.filter((item) => item.status === activeFilter.value))

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
.mistakes-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 28px) 20px 72px;
  background:
    radial-gradient(700px 360px at 0% 0%, color-mix(in srgb, var(--sun) 14%, transparent), transparent 70%),
    var(--bg);
}

.mistakes-wrap {
  max-width: 900px;
  margin: 0 auto;
  display: grid;
  gap: 16px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  justify-self: start;
  padding: 6px 2px;
  color: var(--text-secondary);
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
}

.back-link:hover { color: var(--brand-ink); }

.mistakes-header {
  display: flex;
  align-items: stretch;
  justify-content: space-between;
  gap: 20px;
  padding: clamp(22px, 3vw, 32px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}

.mistakes-header__text { min-width: 0; }

.eyebrow {
  margin-bottom: 4px;
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

h1 { font-size: clamp(24px, 3.4vw, 34px); }

.description {
  margin-top: 8px;
  color: var(--text-secondary);
  font-size: 16px;
}

.summary-card {
  display: grid;
  align-content: center;
  gap: 2px;
  min-width: 170px;
  padding: 16px 20px;
  border-radius: var(--radius-lg);
  background: var(--sun-soft);
  text-align: center;
}

.summary-card span,
.summary-card small {
  color: var(--sun-ink);
  font-size: 14px;
}

.summary-card strong {
  font-family: var(--font-display);
  font-size: 40px;
  line-height: 1.1;
}

/* ---- Фильтры ---- */
.filters {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.filters__btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  min-height: 42px;
  padding: 8px 14px;
  border-radius: var(--radius-sm);
  border: 1.5px solid var(--border);
  background: var(--card);
  color: var(--text-secondary);
  font-size: 14.5px;
  font-weight: 600;
}

.filters__btn:hover:not(:disabled) { border-color: var(--brand); color: var(--brand-ink); }
.filters__btn:disabled { opacity: 0.45; }

.filters__count {
  min-width: 24px;
  padding: 1px 7px;
  border-radius: var(--radius-pill);
  background: var(--bg-alt);
  font-size: 13px;
  font-variant-numeric: tabular-nums;
}

.filters__btn.is-active {
  border-color: var(--brand);
  background: var(--brand);
  color: #ffffff;
}

.filters__btn.is-active .filters__count { background: rgba(255, 255, 255, 0.22); }

/* ---- Карточки ---- */
.mistakes-list {
  display: grid;
  gap: 14px;
  list-style: none;
}

.mistake-card {
  display: grid;
  gap: 16px;
  padding: clamp(20px, 3vw, 26px);
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
  border-left-width: 1px;
  box-shadow: var(--shadow-xs);
}

.mistake-card__top {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  gap: 14px;
  align-items: start;
}

.mistake-index {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  border-radius: 12px;
  background: var(--danger);
  color: #ffffff;
  font-weight: 700;
}

.is-skipped .mistake-index { background: var(--warning); }
.is-correct .mistake-index { background: var(--success); }

.mistake-label {
  margin-bottom: 4px;
  color: var(--text-secondary);
  font-size: 13.5px;
  font-weight: 500;
}

.mistake-card h2 {
  font-family: var(--font-sans);
  font-size: 19px;
  font-weight: 600;
  line-height: 1.45;
  letter-spacing: -0.01em;
  overflow-wrap: anywhere;
}

.mistake-status {
  padding: 5px 10px;
  border-radius: var(--radius-xs);
  background: var(--danger-soft);
  color: var(--danger-ink);
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
}

.is-skipped .mistake-status { background: var(--warning-soft); color: var(--warning-ink); }
.is-correct .mistake-status { background: var(--success-soft); color: var(--success-ink); }

.mistake-image {
  width: 100%;
  max-height: 320px;
  object-fit: contain;
  padding: 10px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.mistake-answers {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.answer-block {
  display: grid;
  gap: 4px;
  padding: 14px 16px;
  border-radius: var(--radius-md);
}

.answer-block span {
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 500;
}

.answer-block strong {
  font-size: 16px;
  overflow-wrap: anywhere;
}

.answer-block.is-yours { background: var(--danger-soft); }
.is-skipped .answer-block.is-yours { background: var(--bg-alt); }
.answer-block.is-ok { background: var(--success-soft); }

.explanation-block {
  display: grid;
  gap: 6px;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  background: var(--sun-soft);
}

.explanation-block span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--sun-ink);
  font-size: 13.5px;
  font-weight: 700;
}

.explanation-block p {
  font-size: 15.5px;
  line-height: 1.65;
}

.mistakes-actions { display: flex; }

@media (max-width: 760px) {
  .mistakes-page { padding: calc(var(--header-h) + 16px) 14px 72px; }
  .mistakes-header { flex-direction: column; }
  .summary-card { min-width: 0; grid-template-columns: auto auto 1fr; align-items: baseline; gap: 8px; text-align: left; }
  .summary-card strong { order: -1; font-size: 30px; }
  .mistake-card__top { grid-template-columns: auto minmax(0, 1fr); }
  .mistake-status { grid-column: 1 / -1; justify-self: start; }
  .mistake-answers { grid-template-columns: 1fr; }
  .mistakes-actions .ds-btn { width: 100%; }
}
</style>
