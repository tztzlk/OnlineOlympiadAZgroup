<template>
  <div class="admin-page">
    <header class="page-head">
      <div>
        <p class="eyebrow">Заявки</p>
        <h1>Заявки и оплата участников</h1>
        <p class="subtext">
          Здесь администратор вручную проверяет новые заявки, подтверждает оплату и открывает доступ к олимпиаде.
        </p>
      </div>

      <div class="toolbar">
        <label class="search-box">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.7"/>
            <path d="M20 20L17 17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
          </svg>
          <input v-model="search" type="text" placeholder="Поиск по имени, email или предмету" />
        </label>

        <div class="filter-panel">
          <div class="filter-group">
            <span class="filter-label">Статус заявки</span>
            <button
              v-for="filter in requestFilters"
              :key="filter.value"
              type="button"
              class="filter-btn"
              :class="{ active: activeRequestFilter === filter.value }"
              @click="activeRequestFilter = filter.value"
            >
              {{ filter.label }}
            </button>
          </div>

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
        <span>Ждут проверки</span>
        <strong>{{ requestsByStatus.pending }}</strong>
      </div>
      <div class="stat-card approved">
        <span>Одобрены</span>
        <strong>{{ requestsByStatus.approved }}</strong>
      </div>
      <div class="stat-card rejected">
        <span>Отклонены</span>
        <strong>{{ requestsByStatus.rejected }}</strong>
      </div>
      <div class="stat-card paid">
        <span>Оплата подтверждена</span>
        <strong>{{ requestsByPayment.paid }}</strong>
      </div>
    </div>

    <div v-if="loading" class="state-card">Загружаем заявки...</div>
    <div v-else-if="errorMessage" class="state-card error-card">{{ errorMessage }}</div>
    <div v-else-if="!filteredRequests.length" class="state-card">По текущим фильтрам заявок нет.</div>

    <div v-else class="request-grid">
      <article v-for="request in filteredRequests" :key="request.id" class="request-card">
        <div class="request-top">
          <div>
            <p class="request-id">Заявка #{{ request.id }}</p>
            <h2>{{ request.name }}</h2>
            <p class="request-meta">{{ request.subjectName }}</p>
          </div>
          <div class="pill-stack">
            <span class="status-pill" :class="request.status">{{ statusLabel(request.status) }}</span>
            <span class="payment-pill" :class="request.payment_status">{{ paymentLabel(request.payment_status) }}</span>
          </div>
        </div>

        <div class="request-details">
          <div class="detail">
            <span>Email родителя</span>
            <strong>{{ request.email }}</strong>
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
            <span>Дата заявки</span>
            <strong>{{ formatDate(request.created_at) }}</strong>
          </div>
          <div class="detail">
            <span>Подтверждение оплаты</span>
            <strong>{{ request.paid_at ? formatDate(request.paid_at) : 'Ещё не подтверждена' }}</strong>
          </div>
          <div class="detail">
            <span>Ссылка на оплату</span>
            <a :href="request.payment_url" target="_blank" rel="noopener">Открыть Kaspi</a>
          </div>
        </div>

        <p class="request-note">{{ requestActionHint(request) }}</p>

        <div class="actions">
          <button type="button" class="ghost-btn" @click="viewRequest(request)">Подробнее</button>
          <button
            v-if="request.status !== 'approved'"
            type="button"
            class="success-btn"
            @click="updateStatus(request, 'approved')"
          >
            Одобрить заявку
          </button>
          <button
            v-if="request.status !== 'rejected'"
            type="button"
            class="danger-btn"
            @click="updateStatus(request, 'rejected')"
          >
            Отклонить заявку
          </button>
          <button
            v-if="request.status !== 'pending'"
            type="button"
            class="ghost-btn"
            @click="updateStatus(request, 'pending')"
          >
            Вернуть в ожидание
          </button>
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
            Вернуть оплату в ожидание
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
            <span>Статус заявки</span>
            <strong>{{ statusLabel(selectedRequest.status) }}</strong>
          </div>
          <div class="modal-field">
            <span>Статус оплаты</span>
            <strong>{{ paymentLabel(selectedRequest.payment_status) }}</strong>
          </div>
          <div class="modal-field">
            <span>Почта родителя</span>
            <strong>{{ selectedRequest.email }}</strong>
          </div>
          <div class="modal-field">
            <span>Телефон родителя</span>
            <strong>{{ selectedRequest.parent_phone || 'Не указан' }}</strong>
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
            <span>Класс</span>
            <strong>{{ selectedRequest.grade || 'Не указан' }}</strong>
          </div>
          <div class="modal-field">
            <span>Язык</span>
            <strong>{{ languageLabel(selectedRequest.language) }}</strong>
          </div>
          <div class="modal-field">
            <span>Дата заявки</span>
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
const activeRequestFilter = ref('all')
const activePaymentFilter = ref('all')

const requestFilters = [
  { value: 'all', label: 'Все' },
  { value: 'pending', label: 'Ожидают' },
  { value: 'approved', label: 'Одобрены' },
  { value: 'rejected', label: 'Отклонены' },
]

const paymentFilters = [
  { value: 'all', label: 'Все' },
  { value: 'pending', label: 'Ожидают' },
  { value: 'paid', label: 'Оплачены' },
  { value: 'failed', label: 'Не прошли' },
]

const statusLabel = (status) => ({
  pending: 'Ожидает проверки',
  approved: 'Одобрена',
  rejected: 'Отклонена',
}[status] ?? status)

const paymentLabel = (status) => ({
  pending: 'Оплата ожидается',
  paid: 'Оплата подтверждена',
  failed: 'Оплата не прошла',
}[status] ?? 'Оплата ожидается')

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

const requestsByStatus = computed(() => ({
  pending: requests.value.filter((item) => item.status === 'pending').length,
  approved: requests.value.filter((item) => item.status === 'approved').length,
  rejected: requests.value.filter((item) => item.status === 'rejected').length,
}))

const requestsByPayment = computed(() => ({
  pending: requests.value.filter((item) => item.payment_status === 'pending').length,
  paid: requests.value.filter((item) => item.payment_status === 'paid').length,
  failed: requests.value.filter((item) => item.payment_status === 'failed').length,
}))

const filteredRequests = computed(() => {
  const query = search.value.trim().toLowerCase()

  return requests.value.filter((request) => {
    const requestFilterMatch = activeRequestFilter.value === 'all' || request.status === activeRequestFilter.value
    const paymentFilterMatch = activePaymentFilter.value === 'all' || request.payment_status === activePaymentFilter.value
    const searchMatch =
      !query ||
      request.name.toLowerCase().includes(query) ||
      request.email.toLowerCase().includes(query) ||
      request.subjectName.toLowerCase().includes(query)

    return requestFilterMatch && paymentFilterMatch && searchMatch
  })
})

const mapRequest = (item) => ({
  ...item,
  name: `${item.first_name || ''} ${item.last_name || ''}`.trim() || 'Без имени',
  email: item.parent_email || item.user?.email || 'Не указан',
  subjectName: item.subject?.name || 'Без предмета',
  payment_status: item.payment_status || 'pending',
  payment_url: item.payment_url || fallbackPaymentUrl,
})

const requestActionHint = (request) => {
  if (request.status === 'pending') {
    return 'Сначала проверьте заявку, затем одобрите её. После этого участник сможет оплатить олимпиаду.'
  }

  if (request.status === 'approved' && request.payment_status === 'pending') {
    return 'Заявка одобрена. Ожидается оплата и ручное подтверждение администратора.'
  }

  if (request.status === 'approved' && request.payment_status === 'paid') {
    return 'Все условия выполнены. Участник может начать олимпиаду.'
  }

  if (request.status === 'rejected') {
    return 'Отклонённая заявка не допускается к олимпиаде даже при наличии оплаты.'
  }

  return 'Проверьте данные и подтвердите следующий шаг вручную.'
}

const syncRequest = (target, payload) => {
  Object.assign(target, mapRequest(payload.request ?? payload))

  if (selectedRequest.value?.id === target.id) {
    selectedRequest.value = { ...target }
  }
}

const loadRequests = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const { data } = await api.get('/admin/requests')
    requests.value = (data.data || []).map(mapRequest)
  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'Не удалось загрузить заявки.'
  } finally {
    loading.value = false
  }
}

const updateStatus = async (request, status) => {
  try {
    const { data } = await api.patch(`/admin/requests/${request.id}/status`, { status })
    syncRequest(request, data)
  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'Не удалось обновить статус заявки.'
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
    radial-gradient(circle at top right, rgba(14, 165, 233, 0.12), transparent 24%),
    linear-gradient(180deg, #f4f7fb 0%, #e9eef7 100%);
  padding: 28px;
  color: #102347;
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
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
}

h1,
h2 {
  margin: 0;
  color: #102347;
}

h1 {
  font-size: clamp(30px, 5vw, 44px);
}

.subtext {
  margin: 10px 0 0;
  max-width: 760px;
  color: #5a6e8c;
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
  background: rgba(255, 255, 255, 0.92);
  border: 1px solid rgba(148, 163, 184, 0.2);
  box-shadow: 0 18px 48px rgba(15, 35, 85, 0.08);
}

.search-box {
  min-width: min(100%, 420px);
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 0 16px;
  border-radius: 18px;
  color: #64748b;
}

.search-box input {
  width: 100%;
  border: 0;
  background: transparent;
  color: #102347;
  padding: 16px 0;
  outline: none;
  font-size: 15px;
}

.search-box input::placeholder {
  color: #8ea1bc;
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
  border-radius: 18px;
  align-items: center;
}

.filter-label {
  color: #58708f;
  font-size: 12px;
  font-weight: 800;
  padding: 0 6px;
}

.filter-btn {
  border: 0;
  background: transparent;
  color: #58708f;
  border-radius: 14px;
  padding: 12px 14px;
  font-weight: 700;
  cursor: pointer;
}

.filter-btn.active {
  background: #102347;
  color: #fff;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 14px;
  margin-bottom: 18px;
}

.stat-card {
  border-radius: 22px;
  padding: 18px 20px;
}

.stat-card span {
  display: block;
  color: #60758f;
  font-size: 13px;
  margin-bottom: 10px;
}

.stat-card strong {
  color: #102347;
  font-size: 32px;
}

.stat-card.pending { background: linear-gradient(180deg, #fff9e8 0%, #fff 100%); }
.stat-card.approved { background: linear-gradient(180deg, #ecfdf3 0%, #fff 100%); }
.stat-card.rejected { background: linear-gradient(180deg, #fff1f2 0%, #fff 100%); }
.stat-card.paid { background: linear-gradient(180deg, #eef6ff 0%, #fff 100%); }

.state-card {
  border-radius: 24px;
  padding: 28px;
  color: #546a86;
}

.error-card {
  color: #b42318;
  background: #fff5f5;
}

.request-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 16px;
}

.request-card {
  border-radius: 24px;
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
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.request-meta {
  margin: 8px 0 0;
  color: #60758f;
}

.pill-stack {
  display: grid;
  gap: 8px;
  justify-items: end;
}

.status-pill,
.payment-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 999px;
  padding: 8px 12px;
  font-size: 12px;
  font-weight: 800;
  white-space: nowrap;
}

.status-pill.pending { background: #fff3c4; color: #9a6700; }
.status-pill.approved { background: #ddf7e7; color: #146c43; }
.status-pill.rejected { background: #ffe1e3; color: #b42318; }
.payment-pill.pending { background: #eef2ff; color: #3730a3; }
.payment-pill.paid { background: #dcfce7; color: #166534; }
.payment-pill.failed { background: #fff7db; color: #9c6b05; }

.request-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.detail,
.modal-field,
.payment-link-card {
  background: #f8fbff;
  border: 1px solid #e2eaf6;
  border-radius: 16px;
  padding: 14px;
}

.detail span,
.modal-field span,
.payment-link-card span {
  display: block;
  color: #64748b;
  font-size: 12px;
  margin-bottom: 6px;
}

.detail strong,
.modal-field strong {
  color: #102347;
  word-break: break-word;
}

.detail a,
.payment-link-card a {
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

.request-note {
  margin: 16px 0 0;
  color: #546a86;
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
.danger-btn,
.warning-btn,
.close-btn {
  border: 0;
  border-radius: 14px;
  padding: 12px 16px;
  font-weight: 800;
  cursor: pointer;
}

.ghost-btn {
  background: #edf4ff;
  color: #173a72;
}

.success-btn {
  background: #dcfce7;
  color: #166534;
}

.danger-btn {
  background: #ffe4e6;
  color: #be123c;
}

.warning-btn {
  background: #fff7db;
  color: #9c6b05;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.45);
  display: grid;
  place-items: center;
  padding: 20px;
}

.modal-card {
  width: min(760px, 100%);
  border-radius: 28px;
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
  background: #f3f6fb;
  color: #102347;
}

.modal-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.payment-link-card {
  margin-top: 14px;
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
