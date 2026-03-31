<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Оплаты</p>
        <h1>История оплат и импорт участников</h1>
      </div>
      <button class="primary-btn" @click="downloadExport">Выгрузить оплаты</button>
    </header>

    <section class="setup-card">
      <div class="setup-copy">
        <h2>Настройка оплаты</h2>
        <p>Для всех олимпиад используется ссылка Kaspi. Участники переходят по ней со страницы оформления заявки.</p>
        <code>{{ paymentUrl }}</code>
      </div>
      <div class="setup-actions">
        <a class="primary-btn" :href="paymentUrl" target="_blank" rel="noopener">Открыть ссылку оплаты</a>
        <button class="secondary-btn" @click="copyPaymentLink">Скопировать ссылку</button>
      </div>
      <p v-if="copyMessage" class="message">{{ copyMessage }}</p>
      <p class="helper-text">Подтверждать оплату администратор может на странице «Заявки», меняя статус оплаты на `paid`.</p>
    </section>

    <section class="import-card">
      <div>
        <h2>Импорт родителей и детей</h2>
        <p>Загрузите CSV из Excel с колонками:</p>
        <code>parent_email;parent_name;parent_phone;child_first_name;child_last_name;grade;birth_date;school;city;language_preference</code>
      </div>

      <label class="file-picker">
        <input type="file" accept=".csv,.txt" @change="handleFile" />
        <span>{{ file?.name || 'Выбор файла: файл не выбран' }}</span>
      </label>

      <button class="primary-btn wide-btn" :disabled="!file || importing" @click="upload">
        {{ importing ? 'Импортируем...' : 'Импортировать CSV' }}
      </button>

      <p v-if="importMessage" class="message">{{ importMessage }}</p>
    </section>

    <section class="table-card">
      <table>
        <thead>
          <tr>
            <th>Родитель</th>
            <th>Ребенок</th>
            <th>Предмет</th>
            <th>Сумма</th>
            <th>Статус</th>
            <th>Дата</th>
          </tr>
        </thead>
        <tbody v-if="payments.length">
          <tr v-for="item in payments" :key="item.id">
            <td>{{ item.parent_name || '—' }}</td>
            <td>{{ item.child_name || '—' }}</td>
            <td>{{ item.subject || '—' }}</td>
            <td>{{ item.amount ? `${item.amount} ${item.currency}` : '—' }}</td>
            <td>{{ paymentStatusLabel(item.status) }}</td>
            <td>{{ item.paid_at || item.date || '—' }}</td>
          </tr>
        </tbody>
      </table>

      <p v-if="!payments.length" class="empty-text">Платежей пока нет. После подтверждения оплаты записи появятся здесь.</p>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../js/api'

const payments = ref([])
const file = ref(null)
const importing = ref(false)
const importMessage = ref('')
const copyMessage = ref('')
const paymentUrl = 'https://kaspi.kz/pay/_gate?action=service_with_subservice&service_id=3025&subservice_id=22909&region_id=19'

const paymentStatusLabel = (status) => ({
  pending: 'Ожидает оплаты',
  paid: 'Оплачено',
  failed: 'Ошибка оплаты',
}[status] ?? status ?? '—')

const load = async () => {
  const { data } = await api.get('/admin/payments')
  payments.value = data
}

const handleFile = (event) => {
  file.value = event.target.files?.[0] || null
}

const upload = async () => {
  if (!file.value) return

  importing.value = true
  importMessage.value = ''
  const formData = new FormData()
  formData.append('file', file.value)

  try {
    const { data } = await api.post('/admin/participants/import', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    importMessage.value = `${data.message}. Импортировано: ${data.imported}. Ошибок: ${data.errors.length}`
    await load()
  } catch (error) {
    importMessage.value = error.response?.data?.message || 'Не удалось выполнить импорт.'
  } finally {
    importing.value = false
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

const downloadExport = async () => {
  const { data } = await api.get('/admin/payments/export', { responseType: 'blob' })
  const url = URL.createObjectURL(new Blob([data], { type: 'application/vnd.ms-excel' }))
  const link = document.createElement('a')
  link.href = url
  link.download = 'payments.xls'
  link.click()
  URL.revokeObjectURL(url)
}

onMounted(load)
</script>

<style scoped>
.admin-page {
  min-height: 100vh;
  padding: 28px;
  color: #102347;
}

.header,
.setup-card,
.import-card,
.table-card {
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 24px;
  padding: 22px;
  box-shadow: 0 18px 48px rgba(15, 35, 85, 0.08);
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 12px;
  font-weight: 700;
  color: #e11d48;
}

.setup-card,
.import-card,
.table-card {
  margin-top: 18px;
  display: grid;
  gap: 14px;
}

.setup-copy h2,
.import-card h2 {
  margin: 0 0 10px;
}

.setup-copy p,
.helper-text,
.message,
.empty-text {
  margin: 0;
  color: #475569;
}

.setup-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.primary-btn,
.secondary-btn {
  border: 0;
  border-radius: 14px;
  padding: 12px 16px;
  font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.primary-btn {
  background: linear-gradient(90deg, #2563eb, #1d4ed8);
  color: white;
}

.secondary-btn {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}

.wide-btn {
  width: 100%;
}

.file-picker {
  display: block;
  border: 1px dashed #bfdbfe;
  border-radius: 16px;
  padding: 14px 16px;
  background: #f8fbff;
  cursor: pointer;
  color: #102347;
}

.file-picker input {
  display: none;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  text-align: left;
  padding: 12px 10px;
  border-bottom: 1px solid #dbe6f3;
}

code {
  word-break: break-word;
  color: #e11d48;
}

@media (max-width: 760px) {
  .admin-page {
    padding: 16px;
  }

  .header {
    flex-direction: column;
    align-items: stretch;
  }

  .setup-actions {
    flex-direction: column;
  }

  .table-card {
    overflow-x: auto;
  }
}
</style>
