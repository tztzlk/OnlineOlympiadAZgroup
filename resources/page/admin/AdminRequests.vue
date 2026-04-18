<template>
  <div class="admin-page">
    <header class="page-head">
      <div>
        <p class="eyebrow">Участники</p>
        <h1>Заявки и подтверждение оплаты</h1>
        <p class="subtext">
          Здесь видно, кто уже оплатил олимпиаду, у кого платёж только на сверке и где всё ещё нужна ручная проверка.
        </p>
      </div>

      <div class="toolbar">
        <label class="search-box">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.7"/>
            <path d="M20 20L17 17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
          </svg>
          <input v-model="search" type="text" placeholder="Поиск по имени, предмету или ID заявки" />
        </label>

        <div class="filter-panel">
          <div class="filter-group">
            <span class="filter-label">Статус оплаты</span>
            <button
              v-for="filter in paymentFilters"
              :key="filter.value"
              type="button"
              class="filter-btn"
              :class="{ active: activePaymentFilter === filter.value }"
              @click="activePaymentFilter = filter.value"
            >
              {{ filter.label }}
            </button>
          </div>
        </div>
      </div>
    </header>

    <div class="stats-row" v-if="!loading">
      <div class="stat-card">
        <span>Всего заявок</span>
        <strong>{{ requests.length }}</strong>
      </div>
      <div class="stat-card pending">
        <span>Ждут оплату</span>
        <strong>{{ requestsByPayment.pending }}</strong>
      </div>
      <div class="stat-card review">
        <span>На сверке / проверке</span>
        <strong>{{ requestsByReconciliation.review }}</strong>
      </div>
      <div class="stat-card paid">
        <span>Оплата подтверждена</span>
        <strong>{{ requestsByPayment.paid }}</strong>
      </div>
    </div>

    <div v-if="loading" class="state-card">Загружаем участников...</div>
    <div v-else-if="errorMessage" class="state-card error-card">{{ errorMessage }}</div>
    <div v-else-if="!filteredRequests.length" class="state-card">По текущим фильтрам участников нет.</div>

    <div v-else class="request-grid">
      <article v-for="request in filteredRequests" :key="request.id" class="request-card">
        <div class="request-top">
          <div>
            <p class="request-id">Участие #{{ request.id }}</p>
            <h2>{{ request.name }}</h2>
            <p class="request-meta">{{ request.subjectName }}</p>
          </div>
          <div class="pill-stack">
            <span class="status-pill" :class="request.status">{{ statusLabel(request.status) }}</span>
            <span class="payment-pill" :class="request.payment_status">{{ paymentLabel(request.payment_status) }}</span>
            <span class="reconciliation-pill" :class="request.reconciliation_status">{{ reconciliationLabel(request.reconciliation_status) }}</span>
          </div>
        </div>

        <div class="request-details">
          <div class="detail">
            <span>Email родителя</span>
            <strong>{{ request.masked_email }}</strong>
          </div>
          <div class="detail">
            <span>Телефон родителя</span>
            <strong>{{ request.masked_phone }}</strong>
          </div>
          <div class="detail">
            <span>Класс</span>
            <strong>{{ request.grade || 'Не указан' }}</strong>
          </div>
          <div class="detail">
            <span>Язык</span>
            <strong>{{ languageLabel(request.language) }}</strong>
          </div>
          <div class="detail">
            <span>Дата оформления</span>
            <strong>{{ formatDate(request.created_at) }}</strong>
          </div>
          <div class="detail">
            <span>Подтверждение оплаты</span>
            <strong>{{ request.paid_at ? formatDate(request.paid_at) : 'Ещё не подтверждена' }}</strong>
          </div>
          <div class="detail">
            <span>Request ID</span>
            <strong>{{ request.payment_reference || 'Не указан' }}</strong>
          </div>
          <div class="detail">
            <span>Комментарий к оплате</span>
            <strong>{{ request.payment_comment || request.payment_reference || 'Не указан' }}</strong>
          </div>
        </div>

        <p class="request-note">{{ requestActionHint(request) }}</p>

        <div class="actions">
          <button type="button" class="ghost-btn" @click="viewRequest(request)">Подробнее</button>
          <a class="ghost-btn link-btn" :href="request.payment_url" target="_blank" rel="noopener">Открыть Kaspi</a>
          <button
            v-if="request.payment_status !== 'paid'"
            type="button"
            class="success-btn"
            @click="updatePaymentStatus(request, 'paid')"
          >
            Подтвердить оплату
          </button>
          <button
            v-if="request.payment_status !== 'failed'"
            type="button"
            class="warning-btn"
            @click="updatePaymentStatus(request, 'failed')"
          >
            Оплата не прошла
          </button>
          <button
            v-if="request.payment_status !== 'pending'"
            type="button"
            class="ghost-btn"
            @click="updatePaymentStatus(request, 'pending')"
          >
            Вернуть в ожидание
          </button>
        </div>
      </article>
    </div>

    <div v-if="selectedRequest" class="modal-backdrop" @click.self="selectedRequest = null">
      <div class="modal-card">
        <div class="modal-head">
          <div>
            <p class="eyebrow">Детали</p>
            <h2>{{ selectedRequest.name }}</h2>
            <p class="subtext">{{ selectedRequest.subjectName }}</p>
          </div>
          <button type="button" class="close-btn" @click="selectedRequest = null">×</button>
        </div>

        <div class="modal-grid">
          <div class="modal-field">
            <span>Статус участия</span>
            <strong>{{ statusLabel(selectedRequest.status) }}</strong>
          </div>
          <div class="modal-field">
            <span>Статус оплаты</span>
            <strong>{{ paymentLabel(selectedRequest.payment_status) }}</strong>
          </div>
          <div class="modal-field">
            <span>Сверка платежа</span>
            <strong>{{ reconciliationLabel(selectedRequest.reconciliation_status) }}</strong>
          </div>
          <div class="modal-field">
            <span>Почта родителя</span>
            <strong>{{ selectedRequest.masked_email }}</strong>
          </div>
          <div class="modal-field">
            <span>Телефон родителя</span>
            <strong>{{ selectedRequest.masked_phone }}</strong>
          </div>
          <div class="modal-field">
            <span>Родитель</span>
            <strong>{{ selectedRequest.parent_name || 'Не указан' }}</strong>
          </div>
          <div class="modal-field">
            <span>Ребёнок</span>
            <strong>{{ selectedRequest.name }}</strong>
          </div>
          <div class="modal-field">
            <span>Дата оформления</span>
            <strong>{{ formatDate(selectedRequest.created_at) }}</strong>
          </div>
          <div class="modal-field">
            <span>Подтверждение оплаты</span>
            <strong>{{ selectedRequest.paid_at ? formatDate(selectedRequest.paid_at) : 'Ещё не подтверждена' }}</strong>
          </div>
        </div>

        <div class="payment-link-card">
          <span>Ссылка Kaspi</span>
          <a :href="selectedRequest.payment_url" target="_blank" rel="noopener">Открыть оплату</a>
        </div>
        <div class="payment-link-card">
          <span>Request ID</span>
          <strong>{{ selectedRequest.payment_reference || 'Не указан' }}</strong>
        </div>
        <div class="payment-link-card">
          <span>Комментарий к оплате</span>
          <strong>{{ selectedRequest.payment_comment || selectedRequest.payment_reference || 'Не указан' }}</strong>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../js/api'

const fallbackPaymentUrl = import.meta.env.VITE_KASPI_PAYMENT_URL || 'https://kaspi.kz/pay/_gate?action=service_with_subservice&service_id=3025&subservice_id=22909&region_id=19'

const loading = ref(true)
const errorMessage = ref('')
const requests = ref([])
const selectedRequest = ref(null)
const search = ref('')
const activePaymentFilter = ref('all')

const paymentFilters = [
  { value: 'all', label: 'Все' },
  { value: 'pending', label: 'Ожидают' },
  { value: 'paid', label: 'Оплачены' },
  { value: 'failed', label: 'Не прошли' },
]

const statusLabel = (status) => ({
  pending: 'Требует проверки',
  approved: 'Оформлено',
  rejected: 'Отклонено',
}[status] ?? status)

const paymentLabel = (status) => ({
  pending: 'Оплата ожидается',
  paid: 'Оплата подтверждена',
  failed: 'Оплата не прошла',
}[status] ?? 'Оплата ожидается')

const reconciliationLabel = (status) => ({
  awaiting_payment: 'Ожидаем оплату',
  reported: 'Платёж на сверке',
  matched: 'Сверка завершена',
  needs_review: 'Нужна проверка',
}[status] ?? 'Ожидаем оплату')

const languageLabel = (language) => ({
  ru: 'Русский',
  kk: 'Қазақша',
  en: 'English',
}[language] ?? (language || 'Не указан'))

const formatDate = (date) => {
  if (!date) return 'Не указана'

  return new Date(date).toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const requestsByPayment = computed(() => ({
  pending: requests.value.filter((item) => item.payment_status === 'pending').length,
  paid: requests.value.filter((item) => item.payment_status === 'paid').length,
  failed: requests.value.filter((item) => item.payment_status === 'failed').length,
}))

const requestsByReconciliation = computed(() => ({
  review: requests.value.filter((item) => ['reported', 'needs_review'].includes(item.reconciliation_status)).length,
}))

const filteredRequests = computed(() => {
  const query = search.value.trim().toLowerCase()

  return requests.value.filter((request) => {
    const paymentFilterMatch = activePaymentFilter.value === 'all' || request.payment_status === activePaymentFilter.value
    const searchMatch =
      !query ||
      request.name.toLowerCase().includes(query) ||
      request.subjectName.toLowerCase().includes(query) ||
      (request.payment_reference || '').toLowerCase().includes(query)

    return paymentFilterMatch && searchMatch
  })
})

const maskEmail = (value) => {
  const normalized = String(value || '').trim()
  if (!normalized.includes('@')) return 'Не указан'

  const [local, domain] = normalized.split('@')
  const [domainName, ...zoneParts] = domain.split('.')
  const zone = zoneParts.length ? `.${zoneParts.join('.')}` : ''
  return `${local.slice(0, 1)}${'*'.repeat(Math.max(local.length - 1, 2))}@${domainName.slice(0, 1)}${'*'.repeat(Math.max(domainName.length - 1, 1))}${zone}`
}

const maskPhone = (value) => {
  const digits = String(value || '').replace(/\D+/g, '')
  if (!digits) return 'Не указан'

  return `+${digits.slice(0, 2)}${'*'.repeat(Math.max(digits.length - 4, 2))}${digits.slice(-2)}`
}

const mapRequest = (item) => ({
  ...item,
  name: `${item.first_name || ''} ${item.last_name || ''}`.trim() || 'Без имени',
  email: item.parent_email || item.user?.email || 'Не указан',
  subjectName: item.subject?.name || 'Без предмета',
  payment_status: item.payment_status || 'pending',
  reconciliation_status: item.reconciliation_status || 'awaiting_payment',
  payment_url: item.payment_url || fallbackPaymentUrl,
})

const requestActionHint = (request) => {
  if (request.payment_status === 'paid') {
    return 'Оплата подтверждена. Участник уже может начать олимпиаду.'
  }

  if (request.reconciliation_status === 'reported') {
    return 'Пользователь отметил оплату. Заявка ждёт автоматической сверки с импортом Kaspi.'
  }

  if (request.reconciliation_status === 'needs_review') {
    return 'Автосверка не нашла однозначного совпадения. Здесь может понадобиться ручное подтверждение.'
  }

  if (request.payment_status === 'failed') {
    return 'Платёж не был подтверждён. После повторной оплаты запись можно вернуть в ожидание.'
  }

  return 'Участие оформлено. Осталось дождаться оплаты или подтвердить её вручную после проверки.'
}

const syncRequest = (target, payload) => {
  const mapped = mapRequest(payload.request ?? payload)
  Object.assign(target, {
    ...mapped,
    masked_email: maskEmail(mapped.email),
    masked_phone: maskPhone(mapped.parent_phone),
  })

  if (selectedRequest.value?.id === target.id) {
    selectedRequest.value = { ...target }
  }
}

const loadRequests = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const { data } = await api.get('/admin/requests')
    requests.value = (data.data || []).map((item) => {
      const mapped = mapRequest(item)
      return {
        ...mapped,
        masked_email: maskEmail(mapped.email),
        masked_phone: maskPhone(mapped.parent_phone),
      }
    })
  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'Не удалось загрузить участников.'
  } finally {
    loading.value = false
  }
}

const updatePaymentStatus = async (request, paymentStatus) => {
  try {
    const { data } = await api.patch(`/admin/requests/${request.id}/payment`, {
      payment_status: paymentStatus,
    })
    syncRequest(request, data)
  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'Не удалось обновить статус оплаты.'
  }
}

const viewRequest = (request) => {
  selectedRequest.value = { ...request }
}

onMounted(loadRequests)
</script>

<style scoped>
* { box-sizing: border-box; }

.admin-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(201, 171, 99, 0.12), transparent 24%),
    linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%);
  padding: 28px;
  color: var(--text);
}

.page-head {
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 20px;
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--accent-strong);
  font-size: 12px;
  font-weight: 800;
}

h1,
h2 {
  margin: 0;
  color: var(--text);
}

h1 {
  font-size: clamp(30px, 5vw, 44px);
}

.subtext {
  margin: 10px 0 0;
  max-width: 760px;
  color: var(--text-secondary);
}

.toolbar {
  display: flex;
  gap: 14px;
  align-items: flex-start;
  justify-content: space-between;
  flex-wrap: wrap;
}

.search-box,
.filter-group,
.state-card,
.stat-card,
.request-card,
.modal-card {
  background: var(--surface);
  border: 1px solid var(--surface-border);
  box-shadow: var(--shadow-card);
}

.search-box {
  min-width: min(100%, 420px);
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 16px;
  border-radius: var(--radius-md);
  color: var(--text-secondary);
}

.search-box input {
  width: 100%;
  border: 0;
  background: transparent;
  color: var(--text);
  padding: 16px 0;
  outline: none;
  font-size: 15px;
}

.filter-panel {
  display: grid;
  gap: 10px;
}

.filter-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 8px;
  border-radius: var(--radius-md);
  align-items: center;
}

.filter-label {
  color: var(--text-secondary);
  font-size: 12px;
  font-weight: 800;
  padding: 0 6px;
}

.filter-btn {
  border: 0;
  background: transparent;
  color: var(--text-secondary);
  border-radius: var(--radius-sm);
  padding: 12px 14px;
  font-weight: 700;
  cursor: pointer;
}

.filter-btn.active {
  background: rgba(201, 171, 99, 0.18);
  color: var(--text);
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
  margin-bottom: 18px;
}

.stat-card {
  border-radius: var(--radius-md);
  padding: 18px 20px;
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

.stat-card.pending { background: linear-gradient(180deg, var(--warning-bg) 0%, rgba(255, 249, 238, 0.95) 100%); }
.stat-card.review { background: linear-gradient(180deg, rgba(234,179,8,.08) 0%, rgba(255, 249, 238, 0.95) 100%); }
.stat-card.paid { background: linear-gradient(180deg, rgba(201, 171, 99, 0.14) 0%, rgba(255, 249, 238, 0.95) 100%); }

.state-card {
  border-radius: var(--radius-lg);
  padding: 28px;
  color: var(--text-secondary);
}

.error-card {
  color: #8f3b3b;
  background: var(--danger-bg);
}

.request-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 16px;
}

.request-card {
  border-radius: var(--radius-lg);
  padding: 22px;
}

.request-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 18px;
}

.request-id {
  margin: 0 0 8px;
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 700;
}

.request-meta {
  margin: 8px 0 0;
  color: var(--text-secondary);
}

.pill-stack {
  display: grid;
  gap: 8px;
  justify-items: end;
}

.status-pill,
.payment-pill,
.reconciliation-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 12px;
  font-weight: 800;
  white-space: nowrap;
}

.status-pill.pending { background: var(--warning-bg); color: var(--accent-strong); }
.status-pill.approved { background: var(--success-bg); color: #2f6f4b; }
.status-pill.rejected { background: var(--danger-bg); color: #8f3b3b; }
.payment-pill.pending,
.reconciliation-pill.awaiting_payment,
.reconciliation-pill.reported { background: rgba(201, 171, 99, 0.14); color: var(--accent-strong); }
.payment-pill.paid,
.reconciliation-pill.matched { background: var(--success-bg); color: #2f6f4b; }
.payment-pill.failed,
.reconciliation-pill.needs_review { background: var(--danger-bg); color: #8f3b3b; }

.request-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.detail,
.modal-field,
.payment-link-card {
  background: rgba(255, 252, 244, 0.86);
  border: 1px solid var(--surface-border);
  border-radius: var(--radius-sm);
  padding: 14px;
}

.detail span,
.modal-field span,
.payment-link-card span {
  display: block;
  color: var(--text-secondary);
  font-size: 12px;
  margin-bottom: 6px;
}

.detail strong,
.modal-field strong {
  color: var(--text);
  word-break: break-word;
}

.request-note {
  margin: 16px 0 0;
  color: var(--text-secondary);
  line-height: 1.6;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 18px;
}

.ghost-btn,
.success-btn,
.warning-btn,
.close-btn,
.link-btn {
  border: 0;
  border-radius: var(--radius-sm);
  padding: 12px 16px;
  font-weight: 800;
  cursor: pointer;
  text-decoration: none;
}

.ghost-btn,
.link-btn {
  background: rgba(201, 171, 99, 0.12);
  color: var(--accent-strong);
}

.success-btn {
  background: var(--success-bg);
  color: #2f6f4b;
}

.warning-btn {
  background: var(--warning-bg);
  color: var(--accent-strong);
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(39, 30, 12, 0.28);
  display: grid;
  place-items: center;
  padding: 20px;
}

.modal-card {
  width: min(760px, 100%);
  border-radius: var(--radius-lg);
  padding: 26px;
}

.modal-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 18px;
}

.close-btn {
  width: 42px;
  height: 42px;
  padding: 0;
  background: rgba(201, 171, 99, 0.14);
  color: var(--accent-strong);
}

.modal-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.payment-link-card {
  margin-top: 14px;
}

.payment-link-card a {
  color: var(--accent-strong);
  font-weight: 700;
  text-decoration: none;
}

@media (max-width: 1100px) {
  .stats-row {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 720px) {
  .admin-page {
    padding: 18px;
  }

  .request-details,
  .modal-grid,
  .stats-row {
    grid-template-columns: 1fr;
  }

  .toolbar,
  .request-top,
  .modal-head {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    width: 100%;
    min-width: 0;
  }

  .pill-stack {
    justify-items: start;
  }
}
</style>
