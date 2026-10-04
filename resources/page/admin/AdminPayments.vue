<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Оплаты</p>
        <h1>Kaspi-сверка и история платежей</h1>
        <p class="subtext">
          Здесь можно загрузить CSV-выгрузку из Kaspi, посмотреть результат автосверки и найти записи,
          которым нужна ручная проверка.
        </p>
      </div>
      <div class="header-actions">
        <button class="secondary-btn" @click="downloadParticipantsExport">Выгрузить участников</button>
        <button class="primary-btn" @click="downloadPaymentsExport">Выгрузить оплаты</button>
      </div>
    </header>

    <section class="setup-card">
      <div class="setup-copy">
        <h2>Ссылка оплаты</h2>
        <p>Kaspi-ссылка остаётся общей, поэтому автосопоставление строится вокруг Request ID и комментария к оплате.</p>
        <code>{{ paymentUrl }}</code>
      </div>

      <div class="setup-actions">
        <a class="primary-btn" :href="paymentUrl" target="_blank" rel="noopener">Открыть ссылку оплаты</a>
        <button class="secondary-btn" @click="copyPaymentLink">Скопировать ссылку</button>
      </div>

      <form class="import-card" @submit.prevent="uploadImport">
        <div>
          <h3>Импорт Kaspi CSV</h3>
          <p>Поддерживаются `.csv` и `.txt`. После загрузки новые строки сразу проходят автосверку.</p>
        </div>
        <input type="file" accept=".csv,.txt,text/csv,text/plain" @change="handleFileChange" />
        <button type="submit" class="primary-btn" :disabled="!selectedFile || uploading">
          {{ uploading ? 'Загружаем...' : 'Загрузить CSV' }}
        </button>
      </form>

      <p v-if="copyMessage" class="message">{{ copyMessage }}</p>
      <p v-if="importMessage" class="message">{{ importMessage }}</p>
    </section>

    <section class="stats-grid">
      <article class="stat-card">
        <span>Новые строки</span>
        <strong>{{ summary.new }}</strong>
      </article>
      <article class="stat-card success">
        <span>Сматчено</span>
        <strong>{{ summary.matched }}</strong>
      </article>
      <article class="stat-card warning">
        <span>Нужна проверка</span>
        <strong>{{ summary.needs_review }}</strong>
      </article>
    </section>

    <section class="table-card">
      <div class="section-head">
        <div>
          <h2>Платежи</h2>
          <p>Текущие записи `payment_records` с финальным статусом и промежуточной сверкой.</p>
        </div>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Родитель</th>
              <th>Ребёнок</th>
              <th>Предмет</th>
              <th>Request ID</th>
              <th>Сумма</th>
              <th>Оплата</th>
              <th>Сверка</th>
              <th>Внешний ID</th>
              <th>Комментарий</th>
              <th>Дата</th>
            </tr>
          </thead>
          <tbody v-if="payments.length">
            <tr v-for="item in payments" :key="item.id">
              <td>{{ item.parent_name || '—' }}</td>
              <td>{{ item.child_name || '—' }}</td>
              <td>{{ item.subject || '—' }}</td>
              <td><code>{{ item.request_reference || '—' }}</code></td>
              <td>{{ item.amount ? `${item.amount} ${item.currency}` : '—' }}</td>
              <td><span class="pill" :class="`pill--${item.status}`">{{ paymentStatusLabel(item.status) }}</span></td>
              <td><span class="pill" :class="`pill--${item.reconciliation_status}`">{{ reconciliationLabel(item.reconciliation_status) }}</span></td>
              <td><code>{{ item.external_reference || '—' }}</code></td>
              <td class="comment-cell">{{ item.comment || '—' }}</td>
              <td>{{ item.paid_at || item.date || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-if="!payments.length" class="empty-text">Платежей пока нет.</p>
    </section>

    <section class="table-card">
      <div class="section-head">
        <div>
          <h2>Импортированные строки</h2>
          <p>Последние строки из Kaspi-выгрузки и результаты их сопоставления.</p>
        </div>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Статус</th>
              <th>Сумма</th>
              <th>Внешний ID</th>
              <th>Комментарий</th>
              <th>Request ID</th>
              <th>Ребёнок</th>
              <th>Дата платежа</th>
              <th>Дата импорта</th>
            </tr>
          </thead>
          <tbody v-if="imports.length">
            <tr v-for="item in imports" :key="item.id">
              <td><span class="pill" :class="`pill--${item.status}`">{{ importStatusLabel(item.status) }}</span></td>
              <td>{{ item.amount || '—' }}</td>
              <td><code>{{ item.external_reference || '—' }}</code></td>
              <td class="comment-cell">{{ item.comment || '—' }}</td>
              <td><code>{{ item.request_reference || '—' }}</code></td>
              <td>{{ item.child_name || '—' }}</td>
              <td>{{ item.paid_at || '—' }}</td>
              <td>{{ item.date || '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-if="!imports.length" class="empty-text">Импортов пока нет.</p>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../js/api'

const payments = ref([])
const imports = ref([])
const summary = ref({ new: 0, matched: 0, needs_review: 0 })
const copyMessage = ref('')
const importMessage = ref('')
const uploading = ref(false)
const selectedFile = ref(null)

const paymentUrl = import.meta.env.VITE_KASPI_PAYMENT_URL || 'https://kaspi.kz/pay/_gate?action=service_with_subservice&service_id=3025&subservice_id=22909&region_id=19'

const paymentStatusLabel = (status) => ({
  pending: 'Ожидает оплаты',
  paid: 'Оплачено',
  failed: 'Ошибка оплаты',
}[status] ?? status ?? '—')

const reconciliationLabel = (status) => ({
  awaiting_payment: 'Ожидаем оплату',
  reported: 'Платёж отмечен',
  matched: 'Сверка завершена',
  needs_review: 'Нужна проверка',
}[status] ?? status ?? '—')

const importStatusLabel = (status) => ({
  new: 'Новая',
  matched: 'Сматчена',
  needs_review: 'Проверить',
}[status] ?? status ?? '—')

const applyPayload = (data = {}) => {
  payments.value = data.payments || []
  imports.value = data.imports || []
  summary.value = data.summary || { new: 0, matched: 0, needs_review: 0 }
}

const load = async () => {
  const { data } = await api.get('/admin/payments')
  applyPayload(data)
}

const handleFileChange = (event) => {
  selectedFile.value = event.target.files?.[0] || null
}

const uploadImport = async () => {
  if (!selectedFile.value || uploading.value) return

  uploading.value = true
  importMessage.value = ''

  try {
    const formData = new FormData()
    formData.append('file', selectedFile.value)

    const { data } = await api.post('/admin/payments/import', formData)
    applyPayload(data)
    importMessage.value = data.message || 'CSV загружен.'
    selectedFile.value = null
  } catch (error) {
    importMessage.value = error.response?.data?.message || 'Не удалось загрузить CSV.'
  } finally {
    uploading.value = false
  }
}

const copyPaymentLink = async () => {
  try {
    await navigator.clipboard.writeText(paymentUrl)
    copyMessage.value = 'Ссылка оплаты скопирована.'
  } catch {
    copyMessage.value = 'Не удалось скопировать ссылку.'
  }
}

const downloadFile = (data, fileName) => {
  const url = URL.createObjectURL(new Blob([data], { type: 'application/vnd.ms-excel' }))
  const link = document.createElement('a')
  link.href = url
  link.download = fileName
  link.click()
  URL.revokeObjectURL(url)
}

const downloadPaymentsExport = async () => {
  const { data } = await api.get('/admin/payments/export', { responseType: 'blob' })
  downloadFile(data, 'payments.xls')
}

const downloadParticipantsExport = async () => {
  const { data } = await api.get('/admin/participants/export', { responseType: 'blob' })
  downloadFile(data, 'participants.xls')
}

onMounted(load)
</script>

<style scoped>
.setup-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 16px;
  align-items: start;
  padding: 20px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
}

.setup-copy {
  display: grid;
  gap: 6px;
}

.setup-copy p,
.import-card p,
.section-head p {
  color: var(--text-secondary);
  font-size: 14.5px;
}

code {
  padding: 2px 6px;
  border-radius: 6px;
  background: var(--bg-alt);
  color: var(--text);
  font-family: ui-monospace, 'SF Mono', Consolas, monospace;
  font-size: 13px;
  overflow-wrap: anywhere;
}

.setup-copy code {
  justify-self: start;
  padding: 8px 10px;
}

.setup-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.import-card {
  grid-column: 1 / -1;
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto auto;
  gap: 12px;
  align-items: center;
  padding: 16px;
  border-radius: var(--radius-md);
  background: var(--bg);
  border: 1.5px dashed var(--border-strong);
}

.import-card h3 {
  font-family: var(--font-sans);
  font-size: 16px;
  font-weight: 700;
  letter-spacing: 0;
}

.import-card input[type="file"] {
  font-size: 14px;
  max-width: 260px;
}

.message {
  grid-column: 1 / -1;
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  background: var(--brand-soft);
  color: var(--brand-ink);
  font-size: 14px;
}

.stat-card span { color: inherit; opacity: 0.8; font-size: 13.5px; }

.stat-card strong {
  font-family: var(--font-display);
  font-size: 28px;
  font-variant-numeric: tabular-nums;
}

.stat-card.success,
.stat-card.warning { border-color: transparent; }

.section-head {
  margin-bottom: 14px;
}

.comment-cell {
  max-width: 280px;
  color: var(--text-secondary);
  overflow-wrap: anywhere;
}

.empty-text {
  padding: 16px;
  color: var(--text-secondary);
  text-align: center;
}

@media (max-width: 860px) {
  .setup-card { grid-template-columns: 1fr; }
  .import-card { grid-template-columns: 1fr; }
}
</style>
