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
.admin-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(201, 171, 99, 0.12), transparent 24%),
    linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%);
  padding: 28px;
  color: var(--text);
  display: grid;
  gap: 18px;
  align-content: start;
}

.header,
.setup-card,
.table-card,
.stat-card {
  background: var(--surface);
  border: 1px solid var(--surface-border);
  border-radius: var(--radius-lg);
  padding: 22px;
  box-shadow: var(--shadow-card);
}

.header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
}

.header-actions,
.setup-actions,
.import-card {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 12px;
  font-weight: 700;
  color: var(--accent-strong);
}

.subtext,
.setup-copy p,
.message,
.empty-text,
.section-head p {
  margin: 0;
  color: var(--text-secondary);
}

.setup-card,
.table-card {
  display: grid;
  gap: 16px;
}

.import-card {
  align-items: center;
  padding: 18px;
  border-radius: var(--radius-md);
  border: 1px dashed var(--surface-border);
  background: rgba(255,252,244,.82);
}

.import-card h3,
.section-head h2 {
  margin: 0 0 6px;
}

.primary-btn,
.secondary-btn {
  border: 0;
  border-radius: var(--radius-sm);
  padding: 12px 16px;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.primary-btn {
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  color: var(--text);
  box-shadow: 0 12px 26px rgba(201, 171, 99, 0.2);
}

.secondary-btn {
  background: rgba(79, 167, 116, 0.1);
  color: #316a49;
  border: 1px solid rgba(79, 167, 116, 0.16);
}

.primary-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
}

.stat-card span {
  display: block;
  color: var(--text-secondary);
  font-size: 13px;
  margin-bottom: 10px;
}

.stat-card strong {
  color: var(--text);
  font-size: 34px;
}

.stat-card.success { background: linear-gradient(180deg, rgba(79,167,116,.08) 0%, rgba(255,249,238,.95) 100%); }
.stat-card.warning { background: linear-gradient(180deg, rgba(234,179,8,.08) 0%, rgba(255,249,238,.95) 100%); }

.table-wrap {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  text-align: left;
  padding: 12px 10px;
  border-bottom: 1px solid var(--surface-border);
  vertical-align: top;
}

code {
  word-break: break-word;
  color: var(--accent-strong);
}

.comment-cell {
  min-width: 260px;
  color: var(--text-secondary);
}

.pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 12px;
  font-weight: 800;
  white-space: nowrap;
}

.pill--pending,
.pill--awaiting_payment,
.pill--reported,
.pill--new {
  background: rgba(201,171,99,.16);
  color: var(--accent-strong);
}

.pill--paid,
.pill--matched {
  background: rgba(79,167,116,.12);
  color: #316a49;
}

.pill--failed,
.pill--needs_review {
  background: rgba(239,68,68,.1);
  color: #9a3d3d;
}

@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 760px) {
  .admin-page {
    padding: 16px;
  }

  .header {
    flex-direction: column;
  }

  .header-actions,
  .setup-actions,
  .import-card {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
