<template>
  <div class="certificate-check-page">
    <section class="hero-card">
      <div>
        <p class="eyebrow">Certificate check</p>
        <h1>Проверка сертификата по ID результата</h1>
        <p class="hero-copy">
          Введите идентификатор сертификата, чтобы подтвердить, что результат существует в системе и относится к завершённой олимпиаде.
        </p>
      </div>
      <RouterLink class="btn btn-outline" to="/leaderboard">Открыть leaderboard</RouterLink>
    </section>

    <section class="lookup-card">
      <form class="lookup-form" @submit.prevent="lookupCertificate">
        <label class="field">
          <span>ID сертификата или результата</span>
          <input v-model="certificateId" type="text" placeholder="Например: 9d4d7b2a-..." />
        </label>
        <button class="btn btn-primary" :disabled="loading || !certificateId.trim()">
          {{ loading ? 'Проверяем...' : 'Проверить сертификат' }}
        </button>
      </form>
    </section>

    <StatePanel
      v-if="loading"
      tone="neutral"
      eyebrow="Certificate check"
      title="Проверяем результат"
      description="Сейчас сверим ID с системой Online Olympiad и покажем сведения по сертификату."
    />

    <StatePanel
      v-else-if="error"
      tone="warning"
      eyebrow="Certificate check"
      title="Сертификат не найден"
      :description="error"
    />

    <section v-else-if="certificate" class="result-card">
      <div class="result-head">
        <div>
          <p class="eyebrow">Проверка пройдена</p>
          <h2>{{ certificate.participant_name }}</h2>
          <p class="hero-copy">{{ certificate.verification_note }}</p>
        </div>
        <StatusBadge :label="certificate.status_meta?.label || 'Результат'" :tone="certificate.status_meta?.tone || 'neutral'" />
      </div>

      <div class="result-grid">
        <article class="result-item"><span>Предмет</span><strong>{{ certificate.subject }}</strong></article>
        <article class="result-item"><span>Категория</span><strong>{{ certificate.category }}</strong></article>
        <article class="result-item"><span>Результат</span><strong>{{ certificate.score }}/{{ certificate.total }} · {{ certificate.percent }}%</strong></article>
        <article class="result-item"><span>Дата</span><strong>{{ certificate.date }}</strong></article>
        <article class="result-item"><span>Школа</span><strong>{{ certificate.school }}</strong></article>
        <article class="result-item"><span>Город</span><strong>{{ certificate.city }}</strong></article>
      </div>
    </section>

    <StatePanel
      v-else
      tone="empty"
      eyebrow="Certificate check"
      title="Введите ID, чтобы проверить сертификат"
      description="Этот экран нужен школам, родителям и партнёрам, когда нужно быстро подтвердить реальность результата."
    />
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../js/api'
import StatePanel from '../components/StatePanel.vue'
import StatusBadge from '../components/StatusBadge.vue'

const route = useRoute()
const router = useRouter()

const certificateId = ref(route.query.id ? String(route.query.id) : '')
const loading = ref(false)
const certificate = ref(null)
const error = ref('')

const lookupCertificate = async () => {
  if (!certificateId.value.trim()) return

  loading.value = true
  error.value = ''
  certificate.value = null

  try {
    router.replace({ query: { id: certificateId.value.trim() } })
    const { data } = await api.get(`/certificate-check/${certificateId.value.trim()}`)
    certificate.value = data
  } catch {
    error.value = 'Мы не нашли сертификат с таким ID. Проверьте идентификатор ещё раз или запросите его в личном кабинете участника.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (certificateId.value) {
    lookupCertificate()
  }
})
</script>

<style scoped>
* { box-sizing: border-box; }

.certificate-check-page {
  min-height: 100vh;
  padding: 110px 18px 48px;
  background: var(--bg);
  display: grid;
  gap: 18px;
}

.hero-card,
.lookup-card,
.result-card {
  max-width: 980px;
  width: 100%;
  margin: 0 auto;
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
}

.hero-card,
.lookup-card,
.result-card {
  padding: 24px;
}

.hero-card,
.result-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.eyebrow {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .12em;
  color: #9b7a10;
}

.hero-card h1,
.result-head h2 {
  margin: 0;
  color: var(--text);
}

.hero-copy,
.result-item span {
  color: var(--text-secondary);
}

.lookup-form {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 12px;
  align-items: end;
}

.field {
  display: grid;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
}

.field input {
  min-height: 54px;
  padding: 14px 16px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--surface-border);
  background: var(--card);
  color: var(--text);
}

.result-grid {
  margin-top: 18px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.result-item {
  padding: 16px;
  border-radius: var(--radius-md);
  border: 1px solid var(--surface-border);
  background: var(--bg-alt);
  display: grid;
  gap: 8px;
}

.result-item strong {
  color: var(--text);
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
  padding: 12px 18px;
  border-radius: var(--radius-sm);
  border: 0;
  font-weight: 700;
  text-decoration: none;
}

.btn-primary {
  background: var(--accent);
  color: var(--text);
  box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.btn-outline {
  background: var(--card);
  color: var(--green);
  border: 1px solid var(--surface-border);
}

@media (max-width: 720px) {
  .hero-card,
  .result-head,
  .lookup-form {
    grid-template-columns: 1fr;
    flex-direction: column;
  }

  .result-grid {
    grid-template-columns: 1fr;
  }
}
</style>
