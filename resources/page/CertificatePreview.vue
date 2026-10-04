<template>
  <div class="certificate-page">
    <div v-if="loading" class="certificate-shell">
      <StatePanel tone="neutral" loading eyebrow="Сертификат" title="Подготавливаем превью" description="Сейчас загрузим данные участника и проверим, доступен ли сертификат." />
    </div>

    <div v-else-if="!certificate" class="certificate-shell">
      <StatePanel tone="empty" eyebrow="Сертификат" title="Превью пока недоступно" description="Мы не нашли данные сертификата. Откройте результаты позже или проверьте доступ участника.">
        <template #actions>
          <RouterLink to="/results" class="ds-btn ds-btn-ghost">К результатам</RouterLink>
        </template>
      </StatePanel>
    </div>

    <div v-else class="certificate-shell">
      <RouterLink to="/results" class="back-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
        К результатам
      </RouterLink>

      <section class="certificate-hero">
        <div>
          <p class="eyebrow">Сертификат участника</p>
          <h1>{{ certificate.participant_name }}</h1>
          <p class="subtitle">{{ certificate.subject }} · {{ certificate.category }} · {{ certificate.date }}</p>
        </div>
        <StatusBadge :label="certificate.status_meta?.label || 'Результат'" :tone="certificate.status_meta?.tone || 'neutral'" />
      </section>

      <section class="certificate-preview" aria-label="Превью сертификата">
        <div class="paper">
          <span class="paper__corner paper__corner--tl" aria-hidden="true"></span>
          <span class="paper__corner paper__corner--br" aria-hidden="true"></span>
          <div class="paper__inner">
            <div class="paper__brand">
              <span class="paper__mark" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
              </span>
              <p class="preview-brand">EURICA</p>
            </div>
            <h2>СЕРТИФИКАТ</h2>
            <p class="preview-lead">Подтверждает участие и получение результата</p>
            <h3>{{ certificate.participant_name }}</h3>
            <p class="preview-school">{{ certificate.school }}, {{ certificate.city }}</p>
            <dl class="preview-meta">
              <div><dt>Предмет</dt><dd>{{ certificate.subject }}</dd></div>
              <div><dt>Категория</dt><dd>{{ certificate.category }}</dd></div>
              <div><dt>Результат</dt><dd>{{ certificate.score }}/{{ certificate.total }} · {{ certificate.percent }}%</dd></div>
              <div><dt>Дата</dt><dd>{{ certificate.date }}</dd></div>
            </dl>
            <span class="paper__seal" aria-hidden="true">
              <svg width="34" height="34" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
            </span>
          </div>
        </div>
      </section>

      <section class="certificate-actions">
        <StatePanel
          tone="success"
          eyebrow="Готово"
          title="Сертификат можно сохранить"
          description="Скачайте PDF-сертификат на устройство."
        >
          <template #actions>
            <button type="button" class="ds-btn ds-btn-sun ds-btn-lg" :disabled="downloading" @click="downloadCertificate">
              <span v-if="downloading" class="ds-spinner" aria-hidden="true"></span>
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
              Скачать сертификат
            </button>
          </template>
        </StatePanel>
        <p v-if="downloadError" class="ds-msg error" role="alert">{{ downloadError }}</p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../js/api'
import StatePanel from '../components/StatePanel.vue'
import StatusBadge from '../components/StatusBadge.vue'

const route = useRoute()
const loading = ref(true)
const certificate = ref(null)
const downloading = ref(false)
const downloadError = ref('')

const loadPreview = async () => {
  loading.value = true
  try {
    const { data } = await api.get(`/profile/results/${route.params.resultId}/certificate-preview`)
    certificate.value = data
  } catch {
    certificate.value = null
  } finally {
    loading.value = false
  }
}

const downloadCertificate = async () => {
  if (!certificate.value?.download_url) return

  downloading.value = true
  downloadError.value = ''

  try {
    const { data, headers } = await api.get(certificate.value.download_url.replace('/api', ''), {
      responseType: 'blob',
    })

    const blob = new Blob([data], { type: headers['content-type'] || 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `certificate-result-${route.params.resultId}.pdf`
    link.click()
    window.URL.revokeObjectURL(url)
  } catch {
    downloadError.value = 'Не удалось скачать сертификат. Попробуйте ещё раз через минуту.'
  } finally {
    downloading.value = false
  }
}

onMounted(loadPreview)
</script>

<style scoped>
.certificate-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 24px) 20px 80px;
  background:
    radial-gradient(700px 360px at 50% 0%, color-mix(in srgb, var(--sun) 16%, transparent), transparent 70%),
    var(--bg);
}

.certificate-shell {
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
  color: var(--text-secondary);
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
}

.back-link:hover { color: var(--brand-ink); }

.certificate-hero {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
}

.eyebrow {
  margin-bottom: 4px;
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

.certificate-hero h1 { font-size: clamp(24px, 3.4vw, 34px); }

.subtitle {
  margin-top: 6px;
  color: var(--text-secondary);
  font-size: 15.5px;
}

/* ---- «Бумага» сертификата ---- */
.paper {
  position: relative;
  overflow: hidden;
  padding: clamp(14px, 2vw, 22px);
  border-radius: var(--radius-xl);
  background: #ffffff;
  box-shadow: var(--shadow-lg);
}

.paper__corner {
  position: absolute;
  width: 180px;
  height: 180px;
  border-radius: 50%;
  pointer-events: none;
}

.paper__corner--tl { top: -90px; left: -90px; background: color-mix(in srgb, #2b5bf5 18%, transparent); }
.paper__corner--br { bottom: -90px; right: -90px; background: color-mix(in srgb, #ffc933 30%, transparent); }

.paper__inner {
  position: relative;
  padding: clamp(28px, 5vw, 52px) clamp(18px, 4vw, 40px);
  border-radius: var(--radius-lg);
  border: 2px solid #dbe3ff;
  text-align: center;
  color: #111a33;
}

.paper__brand {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
}

.paper__mark {
  width: 30px;
  height: 30px;
  display: grid;
  place-items: center;
  border-radius: 9px;
  background: #2b5bf5;
  color: #ffc933;
}

.preview-brand {
  color: #2b5bf5;
  font-family: var(--font-display);
  font-weight: 700;
  letter-spacing: 0.16em;
}

.paper__inner h2 {
  font-size: clamp(32px, 6vw, 56px);
  letter-spacing: 0.04em;
  color: #111a33;
}

.preview-lead {
  margin: 12px 0 22px;
  color: #5b6585;
  font-size: 16px;
}

.paper__inner h3 {
  margin-bottom: 6px;
  font-family: var(--font-display);
  font-size: clamp(24px, 4.4vw, 40px);
  color: #2b5bf5;
  overflow-wrap: anywhere;
}

.preview-school {
  color: #5b6585;
  font-size: 15px;
}

.preview-meta {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
  margin: 28px 0 0;
  text-align: left;
}

.preview-meta div {
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  background: #f4f6fb;
}

.preview-meta dt {
  margin-bottom: 2px;
  color: #8a93b0;
  font-size: 12.5px;
}

.preview-meta dd {
  margin: 0;
  font-size: 14.5px;
  font-weight: 700;
  overflow-wrap: anywhere;
}

.paper__seal {
  position: absolute;
  top: 18px;
  right: 18px;
  width: 64px;
  height: 64px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: #ffc933;
  color: #ffffff;
  box-shadow: 0 0 0 6px #fff5d6;
  transform: rotate(12deg);
}

.certificate-actions {
  display: grid;
  gap: 10px;
}

@media (max-width: 720px) {
  .certificate-page { padding: calc(var(--header-h) + 14px) 12px 96px; }
  .preview-meta { grid-template-columns: 1fr 1fr; }
  .paper__seal { width: 48px; height: 48px; top: 12px; right: 12px; }
  .paper__seal svg { width: 24px; height: 24px; }
}
</style>
