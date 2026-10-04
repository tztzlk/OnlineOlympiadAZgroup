<template>
  <div class="results-page">
    <div class="results-wrap">
      <header class="page-head">
        <div class="page-head__text">
          <p class="eyebrow">Результаты</p>
          <h1>Итоги участия детей</h1>
          <p class="page-head__copy">Здесь собраны завершённые олимпиады, баллы, статусы и доступ к сертификатам каждого участника.</p>
        </div>

        <label class="ds-field filter-box">
          <span class="ds-label">Показывать результаты</span>
          <select v-model="selectedChildId" class="ds-input" @change="loadResults">
            <option value="">Все дети</option>
            <option v-for="child in userStore.children" :key="child.id" :value="String(child.id)">
              {{ child.full_name }}
            </option>
          </select>
        </label>
      </header>

      <p v-if="downloadError" class="ds-msg error" role="alert">{{ downloadError }}</p>

      <StatePanel
        v-if="loading"
        tone="neutral" loading
        eyebrow="Результаты"
        title="Загружаем результаты"
        description="Собираем баллы, статусы и сертификаты по выбранному участнику."
      />

      <StatePanel
        v-else-if="!results.length"
        tone="empty"
        eyebrow="Результаты"
        title="Пока нет завершённых олимпиад"
        description="Когда участник пройдёт первую олимпиаду, здесь появятся баллы, статус и сертификат."
      >
        <template #actions>
          <RouterLink class="ds-btn ds-btn-primary" to="/subject">Выбрать олимпиаду</RouterLink>
        </template>
      </StatePanel>

      <div v-else class="results-grid">
        <article v-for="(result, index) in results" :key="result.id" v-reveal="index" class="result-card" :class="result.status === 'passed' ? 'is-win' : 'is-participant'">
          <div class="result-card__top">
            <div class="ring" :style="{ '--p': result.percent }" role="img" :aria-label="`${result.percent}%`">
              <span>{{ result.percent }}%</span>
            </div>
            <div class="result-card__title">
              <p class="child">{{ result.child_name }}</p>
              <h2>{{ result.subject }}</h2>
              <p class="quiz-title">{{ result.quiz_title }}</p>
            </div>
            <StatusBadge :label="result.status_meta?.label || result.status" :tone="result.status_meta?.tone || 'neutral'" />
          </div>

          <div class="score-panel">
            <span class="score-panel__label">Баллы</span>
            <strong>{{ result.score }}/{{ result.total }}</strong>
          </div>

          <dl class="detail-grid">
            <div class="detail-item"><dt>Категория</dt><dd>{{ result.category_label }}</dd></div>
            <div class="detail-item"><dt>Дата</dt><dd>{{ result.date }}</dd></div>
            <div class="detail-item"><dt>Школа</dt><dd>{{ result.school }}</dd></div>
            <div class="detail-item"><dt>Город</dt><dd>{{ result.city }}</dd></div>
          </dl>

          <div class="meta-row">
            <p>Откройте превью сертификата, чтобы проверить данные перед скачиванием.</p>
            <div class="meta-actions">
              <RouterLink class="ds-btn ds-btn-ghost" :to="`/profile/results/${result.id}/certificate-preview`">Превью</RouterLink>
              <button type="button" class="ds-btn ds-btn-sun" :disabled="downloadingId === result.id" @click="downloadCertificate(result)">
                <span v-if="downloadingId === result.id" class="ds-spinner" aria-hidden="true"></span>
                <svg v-else width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Скачать сертификат
              </button>
            </div>
          </div>
        </article>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../js/api'
import { useUserStore } from '../stores/user'
import StatePanel from '../components/StatePanel.vue'
import StatusBadge from '../components/StatusBadge.vue'

const userStore = useUserStore()
const loading = ref(true)
const results = ref([])
const selectedChildId = ref('')
const downloadingId = ref(null)
const downloadError = ref('')

const loadResults = async () => {
  loading.value = true
  try {
    await userStore.fetchUser()
    const { data } = await api.get('/profile/results', {
      params: selectedChildId.value ? { child_profile_id: selectedChildId.value } : {},
    })
    results.value = data
  } catch {
    results.value = []
  } finally {
    loading.value = false
  }
}

const downloadCertificate = async (result) => {
  downloadError.value = ''
  downloadingId.value = result.id

  try {
    const { data, headers } = await api.get(result.certificate_url.replace('/api', ''), {
      responseType: 'blob',
    })

    const blob = new Blob([data], { type: headers['content-type'] || 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `certificate-result-${result.id}.pdf`
    link.click()
    window.URL.revokeObjectURL(url)
  } catch {
    downloadError.value = 'Не удалось скачать сертификат. Попробуйте ещё раз через минуту.'
  } finally {
    downloadingId.value = null
  }
}

onMounted(loadResults)
</script>

<style scoped>
.results-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 32px) 20px 72px;
  background:
    radial-gradient(700px 360px at 100% 0%, color-mix(in srgb, var(--sun) 16%, transparent), transparent 70%),
    var(--bg);
}

.results-wrap {
  max-width: 1160px;
  margin: 0 auto;
  display: grid;
  gap: 20px;
}

.page-head {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
}

.page-head__text { max-width: 680px; }
.page-head h1 { font-size: clamp(28px, 3.6vw, 42px); }

.eyebrow {
  margin-bottom: 6px;
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

.page-head__copy {
  margin-top: 10px;
  color: var(--text-secondary);
  font-size: 17px;
}

.filter-box { min-width: 260px; }

.results-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 16px;
}

.result-card {
  display: grid;
  gap: 18px;
  padding: 22px;
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}

.result-card__top {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  gap: 6px 16px;
  align-items: start;
}

.result-card__top .ring { grid-row: span 2; }
.result-card__top :deep(.status-badge) { grid-column: 2; justify-self: start; }

.ring {
  --p: 0;
  width: 72px;
  height: 72px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background:
    radial-gradient(closest-side, var(--card) 76%, transparent 78%),
    conic-gradient(var(--ring-color, var(--success)) calc(var(--p) * 1%), var(--bg-alt) 0);
}

.is-participant .ring { --ring-color: var(--sun-hover); }

.ring span {
  font-size: 16px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.child {
  margin-bottom: 2px;
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

.result-card__title h2 {
  font-family: var(--font-sans);
  font-size: 20px;
  font-weight: 700;
  letter-spacing: -0.015em;
}

.quiz-title {
  margin-top: 2px;
  color: var(--text-secondary);
  font-size: 14.5px;
}

.score-panel {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  padding: 14px 18px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.score-panel__label {
  color: var(--text-secondary);
  font-size: 14px;
}

.score-panel strong {
  font-family: var(--font-display);
  font-size: 28px;
  line-height: 1;
  font-variant-numeric: tabular-nums;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px 16px;
  margin: 0;
}

.detail-item dt {
  color: var(--text-tertiary);
  font-size: 13px;
}

.detail-item dd {
  margin: 2px 0 0;
  font-size: 15px;
  font-weight: 600;
  overflow-wrap: anywhere;
}

.meta-row {
  display: grid;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid var(--border);
}

.meta-row p {
  color: var(--text-secondary);
  font-size: 14px;
}

.meta-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.meta-actions .ds-btn { flex: 1; }

@media (max-width: 720px) {
  .results-page { padding: calc(var(--header-h) + 16px) 14px 96px; }
  .page-head { flex-direction: column; align-items: stretch; }
  .filter-box { min-width: 0; }
  .results-grid { grid-template-columns: 1fr; }
}
</style>
