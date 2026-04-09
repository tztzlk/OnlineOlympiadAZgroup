<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Оплаты</p>
        <h1>История оплат и экспорт данных участников</h1>
      </div>
      <div class="header-actions">
        <button class="secondary-btn" @click="downloadParticipantsExport">Выгрузить участников</button>
        <button class="primary-btn" @click="downloadPaymentsExport">Выгрузить оплаты</button>
      </div>
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
const copyMessage = ref('')
const paymentUrl = import.meta.env.VITE_KASPI_PAYMENT_URL || 'https://kaspi.kz/pay/_gate?action=service_with_subservice&service_id=3025&subservice_id=22909&region_id=19'

const paymentStatusLabel = (status) => ({
  pending: 'Ожидает оплаты',
  paid: 'Оплачено',
  failed: 'Ошибка оплаты',
}[status] ?? status ?? '—')

const load = async () => {
  const { data } = await api.get('/admin/payments')
  payments.value = data
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
  padding: 28px;
  color: var(--text);
}

.header,
.setup-card,
.table-card {
  background: var(--surface);
  border: 1px solid var(--surface-border);
  border-radius: var(--radius-lg);
  padding: 22px;
  box-shadow: var(--shadow-card);
}

.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.header-actions {
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

.setup-card,
.table-card {
  margin-top: 18px;
  display: grid;
  gap: 14px;
}

.setup-copy h2 {
  margin: 0 0 10px;
}

.setup-copy p,
.helper-text,
.message,
.empty-text {
  margin: 0;
  color: var(--text-secondary);
}

.setup-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
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

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  text-align: left;
  padding: 12px 10px;
  border-bottom: 1px solid var(--surface-border);
}

code {
  word-break: break-word;
  color: var(--accent-strong);
}

@media (max-width: 760px) {
  .admin-page {
    padding: 16px;
  }

  .header {
    flex-direction: column;
    align-items: stretch;
  }

  .header-actions,
  .setup-actions {
    flex-direction: column;
  }

  .table-card {
    overflow-x: auto;
  }
}
</style>
