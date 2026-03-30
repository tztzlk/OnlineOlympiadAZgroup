<template>
  <div class="admin-page">
    <header class="page-head">
      <div>
        <p class="eyebrow">Admissions</p>
        <h1>Заявки</h1>
        <p class="subtext">
          Проверяйте участников, подтверждайте доступ и контролируйте оплату перед прохождением теста.
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

        <div class="filter-group">
          <button
            v-for="filter in filters"
            :key="filter.value"
            type="button"
            class="filter-btn"
            :class="{ active: activeFilter === filter.value }"
            @click="activeFilter = filter.value"
          >
            {{ filter.label }}
          </button>
        </div>
      </div>
    </header>

    <div class="stats-row" v-if="!loading">
      <div class="stat-card">
        <span>Всего</span>
        <strong>{{ requests.length }}</strong>
      </div>
      <div class="stat-card pending">
        <span>Ожидают</span>
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
          <span class="status-pill" :class="request.status">{{ statusLabel(request.status) }}</span>
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
            <strong>{{ request.language || 'Не указан' }}</strong>
          </div>
          <div class="detail">
            <span>Дата заявки</span>
            <strong>{{ formatDate(request.created_at) }}</strong>
          </div>
          <div class="detail payment-detail">
            <span>Оплата</span>
            <strong>{{ paymentLabel(request.payment_status) }}</strong>
            <small v-if="request.paid_at">Подтверждено: {{ formatDate(request.paid_at) }}</small>
          </div>
        </div>

        <div class="actions">
          <button type="button" class="ghost-btn" @click="viewRequest(request)">Подробнее</button>
          <button
            v-if="request.status !== 'approved'"
            type="button"
            class="success-btn"
            @click="updateStatus(request, 'approved')"
          >
            Одобрить
          </button>
          <button
            v-if="request.status !== 'rejected'"
            type="button"
            class="danger-btn"
            @click="updateStatus(request, 'rejected')"
          >
            Отклонить
          </button>
          <button
            v-if="request.payment_status !== 'paid'"
            type="button"
            class="success-btn"
            @click="updatePaymentStatus(request, 'paid')"
          >
            Оплата подтверждена
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
            <p class="eyebrow">Request Details</p>
            <h2>{{ selectedRequest.name }}</h2>
            <p class="subtext">{{ selectedRequest.subjectName }}</p>
          </div>
          <button type="button" class="close-btn" @click="selectedRequest = null">×</button>
        </div>

        <div class="modal-grid">
          <div class="modal-field">
            <span>Статус</span>
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
            <span>Класс</span>
            <strong>{{ selectedRequest.grade || 'Не указан' }}</strong>
          </div>
          <div class="modal-field">
            <span>Язык</span>
            <strong>{{ selectedRequest.language || 'Не указан' }}</strong>
          </div>
          <div class="modal-field">
            <span>Оплата подтверждена</span>
            <strong>{{ selectedRequest.paid_at ? formatDate(selectedRequest.paid_at) : 'Еще нет' }}</strong>
          </div>
        </div>

        <div v-if="selectedRequest.payment_url" class="payment-link-card">
          <span>Kaspi ссылка</span>
          <a :href="selectedRequest.payment_url" target="_blank" rel="noopener">Открыть оплату</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../../js/api'

const loading = ref(true)
const errorMessage = ref('')
const requests = ref([])
const selectedRequest = ref(null)
const search = ref('')
const activeFilter = ref('all')

const filters = [
  { value: 'all', label: 'Все' },
  { value: 'pending', label: 'Ожидают' },
  { value: 'approved', label: 'Одобрены' },
  { value: 'rejected', label: 'Отклонены' },
]

const statusLabel = (status) => ({
  pending: 'Ожидает',
  approved: 'Одобрена',
  rejected: 'Отклонена',
}[status] ?? status)

const paymentLabel = (status) => ({
  pending: 'Ожидает оплаты',
  paid: 'Оплачено',
  failed: 'Не оплачено',
}[status] ?? 'Ожидает оплаты')

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

const filteredRequests = computed(() => {
  const query = search.value.trim().toLowerCase()

  return requests.value.filter((request) => {
    const filterMatch = activeFilter.value === 'all' || request.status === activeFilter.value
    const searchMatch =
      !query ||
      request.name.toLowerCase().includes(query) ||
      request.email.toLowerCase().includes(query) ||
      request.subjectName.toLowerCase().includes(query)

    return filterMatch && searchMatch
  })
})

const mapRequest = (item) => ({
  ...item,
  name: `${item.first_name || ''} ${item.last_name || ''}`.trim() || 'Без имени',
  email: item.parent_email || item.user?.email || 'Не указан',
  subjectName: item.subject?.name || 'Без предмета',
  payment_status: item.payment_status || 'pending',
})

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
    await api.patch(`/admin/requests/${request.id}/status`, { status })
    request.status = status
    if (selectedRequest.value?.id === request.id) {
      selectedRequest.value = { ...selectedRequest.value, status }
    }
  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'Не удалось обновить статус.'
  }
}

const updatePaymentStatus = async (request, paymentStatus) => {
  try {
    const { data } = await api.patch(`/admin/requests/${request.id}/payment`, {
      payment_status: paymentStatus,
    })

    request.payment_status = data.payment_status
    request.paid_at = data.paid_at

    if (selectedRequest.value?.id === request.id) {
      selectedRequest.value = {
        ...selectedRequest.value,
        payment_status: data.payment_status,
        paid_at: data.paid_at,
      }
    }
  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'Не удалось обновить статус оплаты.'
  }
}

const viewRequest = (request) => {
  selectedRequest.value = request
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
  align-items: center;
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

.filter-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding: 8px;
  border-radius: 18px;
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
  grid-template-columns: repeat(4, minmax(0, 1fr));
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
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
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

.status-pill {
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

.detail small {
  display: block;
  margin-top: 8px;
  color: #64748b;
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

.payment-link-card a {
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: none;
}

@media (max-width: 980px) {
  .stats-row {
    grid-template-columns: 1fr 1fr;
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
}
</style>
