<template>
  <div class="certificate-page">
    <div v-if="loading" class="certificate-shell">
      <StatePanel tone="neutral" eyebrow="Сертификат" title="Подготавливаем превью" description="Сейчас загрузим данные участника и проверим, доступен ли сертификат." />
    </div>

    <div v-else-if="!certificate" class="certificate-shell">
      <StatePanel tone="empty" eyebrow="Сертификат" title="Превью пока недоступно" description="Мы не нашли данные сертификата. Откройте результаты позже или проверьте доступ участника." />
    </div>

    <div v-else class="certificate-shell">
      <section class="certificate-hero">
        <div>
          <p class="eyebrow">Сертификат участника</p>
          <h1>{{ certificate.participant_name }}</h1>
          <p class="subtitle">{{ certificate.subject }} · {{ certificate.category }} · {{ certificate.date }}</p>
        </div>
        <StatusBadge :label="certificate.status_meta?.label || 'Результат'" :tone="certificate.status_meta?.tone || 'neutral'" />
      </section>

      <section class="certificate-preview">
        <div class="certificate-preview__frame">
          <div class="certificate-preview__inner">
            <p class="preview-brand">EURICA</p>
            <h2>СЕРТИФИКАТ</h2>
            <p class="preview-lead">Подтверждает участие и получение результата</p>
            <h3>{{ certificate.participant_name }}</h3>
            <p>{{ certificate.school }}, {{ certificate.city }}</p>
            <dl class="preview-meta">
              <div><dt>Предмет</dt><dd>{{ certificate.subject }}</dd></div>
              <div><dt>Категория</dt><dd>{{ certificate.category }}</dd></div>
              <div><dt>Результат</dt><dd>{{ certificate.score }}/{{ certificate.total }} · {{ certificate.percent }}%</dd></div>
              <div><dt>Дата</dt><dd>{{ certificate.date }}</dd></div>
            </dl>
          </div>
        </div>
      </section>

      <section class="certificate-actions">
        <StatePanel
          tone="success"
          eyebrow="Готово"
          title="Сертификат можно сохранить"
          description="Откройте PDF-сертификат в новом окне или скачайте его на устройство."
        >
          <template #actions>
            <a class="btn btn-primary" :href="certificate.download_url" target="_blank" rel="noopener">Открыть PDF</a>
            <button class="btn btn-outline" @click="downloadCertificate">Скачать файл</button>
          </template>
        </StatePanel>
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
}

onMounted(loadPreview)
</script>

<style scoped>
.certificate-page {
  min-height: 100vh;
  padding: 110px 18px 42px;
  background: var(--bg);
}

.certificate-shell {
  max-width: 980px;
  margin: 0 auto;
  display: grid;
  gap: 18px;
}

.certificate-hero,
.certificate-preview,
.certificate-actions {
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
}

.certificate-hero {
  padding: 24px;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.eyebrow {
  margin: 0 0 8px;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .12em;
  color: var(--accent-strong);
  font-weight: 800;
}

.certificate-hero h1 { margin: 0 0 8px; color: var(--text); }
.subtitle { margin: 0; color: var(--text-secondary); }

.certificate-preview { padding: 24px; }

.certificate-preview__frame {
  padding: 18px;
  border-radius: 28px;
  background: #faf7ef;
  border: 1px solid rgba(201, 171, 99, 0.24);
}

.certificate-preview__inner {
  padding: 42px 28px;
  border-radius: 22px;
  border: 2px solid rgba(201, 171, 99, 0.42);
  text-align: center;
}

.preview-brand {
  margin: 0 0 12px;
  color: #9b7a10;
  letter-spacing: .18em;
  font-weight: 700;
}

.certificate-preview__inner h2 {
  margin: 0;
  font-size: clamp(34px, 6vw, 58px);
  color: var(--text);
}

.preview-lead {
  margin: 18px 0 22px;
  color: #6f5c3a;
}

.certificate-preview__inner h3 {
  margin: 0 0 10px;
  font-size: clamp(28px, 5vw, 44px);
  color: var(--text);
}

.preview-meta {
  margin: 28px 0 0;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
  text-align: left;
}

.preview-meta div {
  padding: 14px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.54);
}

.preview-meta dt {
  color: var(--text-secondary);
  font-size: 12px;
  margin-bottom: 6px;
}

.preview-meta dd {
  margin: 0;
  color: var(--text);
  font-weight: 700;
}

.certificate-actions { padding: 18px; }

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
  cursor: pointer;
}

.btn-primary {
  background: var(--green);
  color: #ffffff;
}

.btn-outline {
  background: var(--card);
  color: var(--green);
  border: 1px solid var(--surface-border);
}

@media (max-width: 720px) {
  .certificate-page { padding-inline: 14px; }
  .certificate-hero { flex-direction: column; }
  .preview-meta { grid-template-columns: 1fr; }
}
</style>
