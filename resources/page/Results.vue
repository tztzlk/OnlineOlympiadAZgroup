<template>
  <div class="results-page">
    <header class="header">
      <p class="eyebrow">My Results</p>
      <h1>Мои результаты</h1>
      <p class="subtext">Здесь показаны баллы, категории и сертификаты по завершённым олимпиадам.</p>
    </header>

    <div v-if="loading" class="state-card">Загружаем результаты...</div>
    <div v-else-if="!results.length" class="state-card">Вы ещё не завершали олимпиады.</div>

    <div v-else class="results-grid">
      <article v-for="result in results" :key="result.id" class="result-card">
        <div class="result-head">
          <div>
            <h2>{{ result.subject }}</h2>
            <p>{{ result.quiz_title }}</p>
            <small>{{ result.school }} · {{ result.city }}</small>
          </div>
          <span class="status-chip" :class="result.statusClass">{{ result.status }}</span>
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
        </div>

        <div class="score-line">
          <strong>{{ result.score }}/{{ result.total }}</strong>
          <span>{{ result.percent }}%</span>
        </div>

        <div class="progress-track">
          <div class="progress-fill" :class="result.statusClass" :style="{ width: `${result.percent}%` }"></div>
        </div>

        <div class="meta-row">
          <span>Сертификат доступен сразу после результата</span>
          <button class="certificate-btn" @click="downloadCertificate(result)">Скачать сертификат</button>
        </div>
      </article>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../js/api'

const loading = ref(true)
const results = ref([])

const loadResults = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/profile/results')
    results.value = data
  } finally {
    loading.value = false
  }
}

const downloadCertificate = async (result) => {
  const { data, headers } = await api.get(result.certificate_url.replace('/api', ''), {
    responseType: 'blob',
  })

  const blob = new Blob([data], { type: headers['content-type'] || 'image/svg+xml' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `certificate-result-${result.id}.svg`
  link.click()
  window.URL.revokeObjectURL(url)
}

onMounted(loadResults)
</script>

<style scoped>
* { box-sizing: border-box; }
.results-page { min-height: 100vh; background: var(--bg); color: var(--text-primary); padding: 24px; }
.header, .state-card, .result-card { max-width: 1100px; margin: 0 auto; }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; color: #f43f5e; font-size: 12px; font-weight: 700; }
h1, h2 { margin: 0; }
.subtext { margin-top: 10px; color: var(--text-secondary); }
.state-card, .result-card { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 24px; padding: 22px; backdrop-filter: blur(10px); }
.state-card { margin-top: 20px; }
.results-grid { max-width: 1100px; margin: 20px auto 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 16px; }
.result-head { display: flex; justify-content: space-between; gap: 12px; }
.result-head p, .meta-row, .result-head small { color: var(--text-muted-on-surface); }
.status-chip { display: inline-flex; height: fit-content; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.status-chip.win { background: #e8f8ed; color: #1f7a34; }
.status-chip.participant { background: #ffe7e7; color: #9f1d1d; }
.detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 16px; }
.detail-item { padding: 12px 14px; border-radius: 16px; background: rgba(255,255,255,0.05); border: 1px solid var(--surface-border); }
.detail-item span { display: block; color: var(--text-muted-on-surface); font-size: 12px; margin-bottom: 6px; }
.score-line { display: flex; justify-content: space-between; align-items: baseline; margin: 18px 0 10px; }
.score-line strong { font-size: 28px; }
.score-line span { color: var(--text-muted-on-surface); font-weight: 700; }
.progress-track { height: 10px; border-radius: 999px; overflow: hidden; background: rgba(255,255,255,0.08); }
.progress-fill { height: 100%; }
.progress-fill.win { background: linear-gradient(90deg, #34d399, #15803d); }
.progress-fill.participant { background: linear-gradient(90deg, #fb7185, #be123c); }
.meta-row { margin-top: 14px; font-size: 14px; display: flex; justify-content: space-between; gap: 12px; align-items: center; }
.certificate-btn { color: #f43f5e; font-weight: 700; background: none; border: 0; cursor: pointer; padding: 0; }
@media (max-width: 640px) { .results-page { padding: 16px; } .result-head, .meta-row { flex-direction: column; align-items: flex-start; } .detail-grid { grid-template-columns: 1fr; } }
</style>
