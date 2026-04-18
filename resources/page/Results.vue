<template>
  <div class="results-page">
    <header class="header-card">
      <div>
        <p class="eyebrow">Результаты</p>
        <h1>Итоги участия детей</h1>
        <p class="header-copy">Здесь собраны завершённые олимпиады, баллы, статусы и доступ к сертификатам каждого участника.</p>
      </div>

      <label class="filter-box">
        <span>Показывать результаты</span>
        <select v-model="selectedChildId" @change="loadResults">
          <option value="">Все дети</option>
          <option v-for="child in userStore.children" :key="child.id" :value="String(child.id)">
            {{ child.full_name }}
          </option>
        </select>
      </label>
    </header>

    <StatePanel
      v-if="loading"
      tone="neutral"
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
    />

    <div v-else class="results-grid">
      <article v-for="result in results" :key="result.id" class="result-card">
        <div class="result-head">
          <div>
            <p class="child">{{ result.child_name }}</p>
            <h2>{{ result.subject }}</h2>
            <p class="quiz-title">{{ result.quiz_title }}</p>
          </div>
          <StatusBadge :label="result.status_meta?.label || result.status" :tone="result.status_meta?.tone || 'neutral'" />
        </div>

        <div class="score-panel">
          <strong>{{ result.score }}/{{ result.total }}</strong>
          <span>{{ result.percent }}%</span>
        </div>

        <div class="progress-track">
          <div
            class="progress-fill"
            :class="result.status === 'passed' ? 'win' : 'participant'"
            :style="{ width: `${result.percent}%` }"
          ></div>
        </div>

        <div class="detail-grid">
          <div class="detail-item">
            <span>Категория</span>
            <strong>{{ result.category_label }}</strong>
          </div>
          <div class="detail-item">
            <span>Дата</span>
            <strong>{{ result.date }}</strong>
          </div>
          <div class="detail-item">
            <span>Школа</span>
            <strong>{{ result.school }}</strong>
          </div>
          <div class="detail-item">
            <span>Город</span>
            <strong>{{ result.city }}</strong>
          </div>
        </div>

        <div class="meta-row">
          <span>Откройте превью сертификата, чтобы проверить данные перед скачиванием.</span>
          <div class="meta-actions">
            <RouterLink class="certificate-btn preview" :to="`/profile/results/${result.id}/certificate-preview`">Превью</RouterLink>
            <button class="certificate-btn" @click="downloadCertificate(result)">Скачать сертификат</button>
          </div>
        </div>
      </article>
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

const loadResults = async () => {
  loading.value = true
  try {
    await userStore.fetchUser()
    const { data } = await api.get('/profile/results', {
      params: selectedChildId.value ? { child_profile_id: selectedChildId.value } : {},
    })
    results.value = data
  } finally {
    loading.value = false
  }
}

const downloadCertificate = async (result) => {
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
}

onMounted(loadResults)
</script>

<style scoped>
* { box-sizing: border-box; }

.results-page {
  min-height: 100vh;
  padding: 110px 20px 48px;
  background: radial-gradient(circle at top left, rgba(201, 171, 99, 0.14), transparent 24%), var(--bg);
  color: var(--text);
}

.header-card,
.result-card {
  max-width: 1120px;
  margin: 0 auto;
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
}

.header-card {
  padding: 26px;
  display: flex;
  justify-content: space-between;
  gap: 18px;
  align-items: flex-start;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

.header-copy,
.quiz-title,
.meta-row {
  color: var(--text-secondary);
}

.filter-box {
  min-width: 260px;
  display: grid;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
}

.filter-box select {
  min-height: 50px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 245, 0.95);
  padding: 12px 14px;
  color: var(--text);
}

.results-grid {
  max-width: 1120px;
  margin: 20px auto 0;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 16px;
}

.result-card {
  padding: 22px;
  display: grid;
  gap: 16px;
}

.result-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
}

.child {
  margin-bottom: 8px;
  color: var(--accent-strong);
  font-weight: 700;
}

.score-panel {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 12px;
}

.score-panel strong {
  font-size: 34px;
  line-height: 1;
}

.score-panel span {
  color: var(--text-secondary);
  font-weight: 700;
}

.progress-track {
  height: 10px;
  border-radius: 999px;
  overflow: hidden;
  background: rgba(100, 83, 41, 0.1);
}

.progress-fill {
  height: 100%;
}

.progress-fill.win {
  background: linear-gradient(90deg, var(--success-soft), #78c293);
}

.progress-fill.participant {
  background: linear-gradient(90deg, var(--accent), #dfc27f);
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.detail-item {
  padding: 14px;
  border-radius: var(--radius-md);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 244, 0.82);
}

.detail-item span {
  display: block;
  margin-bottom: 6px;
  color: var(--text-secondary);
  font-size: 12px;
}

.meta-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
  font-size: 14px;
}

.meta-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.certificate-btn {
  border: 0;
  background: rgba(201, 171, 99, 0.16);
  color: var(--accent-strong);
  border-radius: var(--radius-sm);
  padding: 11px 14px;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
}

.certificate-btn.preview {
  background: rgba(79, 167, 116, 0.12);
  color: #2f6f4b;
}

@media (max-width: 720px) {
  .results-page {
    padding: 98px 14px 30px;
  }

  .header-card,
  .result-head,
  .meta-row {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-box {
    min-width: 0;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>
