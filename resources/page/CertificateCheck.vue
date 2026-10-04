<template>
  <div class="certificate-check-page">
    <div class="cc-wrap">
      <section class="hero-card" aria-labelledby="cc-title">
        <span class="hero-card__icon" aria-hidden="true">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.5 13.5L17 22l-5-3-5 3 1.5-8.5"/><path d="M9.5 8l1.8 1.8L14.5 6.5"/></svg>
        </span>
        <p class="eyebrow">Certificate check</p>
        <h1 id="cc-title">Проверка сертификата по ID результата</h1>
        <p class="hero-copy">
          Введите идентификатор сертификата, чтобы подтвердить, что результат существует в системе и относится к завершённой олимпиаде.
        </p>

        <form class="lookup-form" role="search" @submit.prevent="lookupCertificate">
          <label class="sr-only" for="certificate-id">ID сертификата или результата</label>
          <div class="lookup-input">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            <input id="certificate-id" v-model="certificateId" type="text" autocomplete="off" spellcheck="false" placeholder="Например: 9d4d7b2a-..." />
          </div>
          <button class="ds-btn ds-btn-primary ds-btn-lg" :disabled="loading || !certificateId.trim()">
            <span v-if="loading" class="ds-spinner" aria-hidden="true"></span>
            {{ loading ? 'Проверяем...' : 'Проверить сертификат' }}
          </button>
        </form>

        <RouterLink class="hero-link" to="/leaderboard">Открыть leaderboard</RouterLink>
      </section>

      <StatePanel
        v-if="loading"
        tone="neutral" loading
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

      <section v-else-if="certificate" class="result-card" aria-labelledby="cc-result-title">
        <div class="result-head">
          <span class="result-head__check" aria-hidden="true">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
          </span>
          <div class="result-head__text">
            <p class="eyebrow eyebrow--ok">Проверка пройдена</p>
            <h2 id="cc-result-title">{{ certificate.participant_name }}</h2>
            <p class="result-note">{{ certificate.verification_note }}</p>
          </div>
          <StatusBadge :label="certificate.status_meta?.label || 'Результат'" :tone="certificate.status_meta?.tone || 'neutral'" />
        </div>

        <dl class="result-grid">
          <div class="result-item"><dt>Предмет</dt><dd>{{ certificate.subject }}</dd></div>
          <div class="result-item"><dt>Категория</dt><dd>{{ certificate.category }}</dd></div>
          <div class="result-item"><dt>Результат</dt><dd>{{ certificate.score }}/{{ certificate.total }} · {{ certificate.percent }}%</dd></div>
          <div class="result-item"><dt>Дата</dt><dd>{{ certificate.date }}</dd></div>
          <div class="result-item"><dt>Школа</dt><dd>{{ certificate.school }}</dd></div>
          <div class="result-item"><dt>Город</dt><dd>{{ certificate.city }}</dd></div>
        </dl>
      </section>

      <StatePanel
        v-else
        tone="empty"
        eyebrow="Certificate check"
        title="Введите ID, чтобы проверить сертификат"
        description="Этот экран нужен школам, родителям и партнёрам, когда нужно быстро подтвердить реальность результата."
      />
    </div>
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
    const { data } = await api.get(`/certificate-check/${encodeURIComponent(certificateId.value.trim())}`)
    certificate.value = data
  } catch (err) {
    error.value = err.response?.status === 429
      ? 'Слишком много проверок подряд. Подождите минуту и попробуйте снова.'
      : 'Мы не нашли сертификат с таким ID. Проверьте идентификатор ещё раз или запросите его в личном кабинете участника.'
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
.certificate-check-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 24px) 20px 80px;
  background:
    radial-gradient(700px 360px at 50% 0%, color-mix(in srgb, var(--success) 10%, transparent), transparent 70%),
    var(--bg);
}

.cc-wrap {
  max-width: 860px;
  margin: 0 auto;
  display: grid;
  gap: 16px;
}

.hero-card {
  display: grid;
  justify-items: center;
  gap: 12px;
  padding: clamp(26px, 5vw, 48px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-md);
  text-align: center;
}

.hero-card__icon {
  width: 64px;
  height: 64px;
  display: grid;
  place-items: center;
  margin-bottom: 4px;
  border-radius: 20px;
  background: var(--success);
  color: #ffffff;
  transform: rotate(-5deg);
}

.eyebrow {
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

.eyebrow--ok { color: var(--success-ink); }

.hero-card h1 { font-size: clamp(24px, 3.4vw, 36px); }

.hero-copy {
  max-width: 56ch;
  color: var(--text-secondary);
  font-size: 16px;
}

.lookup-form {
  width: 100%;
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 10px;
  margin-top: 10px;
}

.lookup-input {
  position: relative;
  display: flex;
  align-items: center;
}

.lookup-input svg {
  position: absolute;
  left: 16px;
  color: var(--text-tertiary);
  pointer-events: none;
}

.lookup-input input {
  width: 100%;
  min-height: 56px;
  padding: 12px 16px 12px 48px;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--border-strong);
  background: var(--input-bg);
  color: var(--text);
  font-size: 16px;
  font-family: ui-monospace, 'SF Mono', Consolas, monospace;
}

.hero-link {
  color: var(--brand-ink);
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
}

.hero-link:hover { text-decoration: underline; text-underline-offset: 3px; }

.result-card {
  display: grid;
  gap: 18px;
  padding: clamp(20px, 3vw, 30px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 2px solid color-mix(in srgb, var(--success) 40%, var(--border));
  box-shadow: var(--shadow-sm);
}

.result-head {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  gap: 16px;
  align-items: start;
}

.result-head__check {
  width: 52px;
  height: 52px;
  display: grid;
  place-items: center;
  border-radius: 16px;
  background: var(--success);
  color: #ffffff;
}

.result-head h2 {
  margin-top: 2px;
  font-size: clamp(22px, 2.8vw, 28px);
  overflow-wrap: anywhere;
}

.result-note {
  margin-top: 6px;
  color: var(--text-secondary);
  font-size: 15px;
}

.result-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
  margin: 0;
}

.result-item {
  padding: 14px 16px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.result-item dt {
  color: var(--text-tertiary);
  font-size: 13px;
}

.result-item dd {
  margin: 2px 0 0;
  font-size: 15.5px;
  font-weight: 600;
  overflow-wrap: anywhere;
}

@media (max-width: 720px) {
  .certificate-check-page { padding: calc(var(--header-h) + 14px) 12px 96px; }
  .lookup-form { grid-template-columns: 1fr; }
  .result-head { grid-template-columns: auto minmax(0, 1fr); }
  .result-head :deep(.status-badge) { grid-column: 1 / -1; justify-self: start; }
  .result-grid { grid-template-columns: 1fr 1fr; }
}
</style>
