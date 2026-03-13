<template>
  <div class="results-page">
    <header class="header">
      <p class="eyebrow">My Results</p>
      <h1>Мои результаты</h1>
      <p class="subtext">Здесь показаны баллы по всем завершённым олимпиадам.</p>
    </header>

    <div v-if="loading" class="state-card">Загружаем результаты...</div>
    <div v-else-if="!results.length" class="state-card">Вы ещё не завершали олимпиады.</div>

    <div v-else class="results-grid">
      <article v-for="result in results" :key="result.id" class="result-card">
        <div class="result-head">
          <div>
            <h2>{{ result.subject }}</h2>
            <p>{{ result.quiz_title }}</p>
          </div>
          <span class="status-chip" :class="result.statusClass">{{ result.status }}</span>
        </div>

        <div class="score-line">
          <strong>{{ result.score }}/{{ result.total }}</strong>
          <span>{{ result.percent }}%</span>
        </div>

        <div class="progress-track">
          <div
            class="progress-fill"
            :class="result.statusClass"
            :style="{ width: `${result.percent}%` }"
          ></div>
        </div>

        <div class="meta-row">
          <span>{{ result.date }}</span>
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

onMounted(loadResults)
</script>

<style scoped>
* { box-sizing: border-box; }

.results-page {
  min-height: 100vh;
  background: linear-gradient(180deg, #10131d 0%, #181c28 100%);
  color: #fff;
  padding: 24px;
}

.header,
.state-card,
.result-card {
  max-width: 1100px;
  margin: 0 auto;
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #f43f5e;
  font-size: 12px;
  font-weight: 700;
}

h1,
h2 {
  margin: 0;
}

.subtext {
  margin: 10px 0 0;
  color: #c4cad8;
}

.state-card,
.result-card {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 22px;
  backdrop-filter: blur(10px);
}

.state-card {
  margin-top: 20px;
}

.results-grid {
  max-width: 1100px;
  margin: 20px auto 0;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.result-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.result-head p,
.meta-row {
  color: #c4cad8;
}

.status-chip {
  display: inline-flex;
  height: fit-content;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.status-chip.win { background: #e8f8ed; color: #1f7a34; }
.status-chip.participant { background: #ffe7e7; color: #9f1d1d; }

.score-line {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin: 18px 0 10px;
}

.score-line strong {
  font-size: 28px;
}

.score-line span {
  color: #c4cad8;
  font-weight: 700;
}

.progress-track {
  height: 10px;
  border-radius: 999px;
  overflow: hidden;
  background: rgba(255, 255, 255, 0.08);
}

.progress-fill {
  height: 100%;
}

.progress-fill.win { background: linear-gradient(90deg, #34d399, #15803d); }
.progress-fill.participant { background: linear-gradient(90deg, #fb7185, #be123c); }

.meta-row {
  margin-top: 14px;
  font-size: 14px;
}

@media (max-width: 640px) {
  .results-page {
    padding: 16px;
  }

  .result-head {
    flex-direction: column;
  }
}
</style>
