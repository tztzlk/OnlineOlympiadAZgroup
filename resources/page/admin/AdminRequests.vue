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

        <p v-if="request.disqualified_at" class="disqualified-note">
          Попытка аннулирована {{ formatDate(request.disqualified_at) }} — {{ disqualificationLabel(request.disqualification_reason) }}
        </p>
        <p v-else class="request-note">{{ requestActionHint(request) }}</p>

        <div class="actions">
          <button type="button" class="ghost-btn" @click="viewRequest(request)">Подробнее</button>
          <a class="ghost-btn link-btn" :href="request.payment_url" target="_blank" rel="noopener">Открыть Kaspi</a>
          <button
            v-if="request.disqualified_at"
            type="button"
            class="reset-btn"
            @click="resetAttempt(request)"
          >
            Снять блокировку
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

const disqualificationLabel = (reason) => ({
  tab_hidden: 'скрытие вкладки',
  window_blur: 'потеря фокуса окна',
  fullscreen_exit: 'выход из полноэкранного режима',
  window_focus_lost: 'потеря фокуса',
  tab_switch: 'переключение вкладки',
  copy_paste: 'копирование/вставка',
  devtools_open: 'открытие DevTools',
  idle_timeout: 'таймаут бездействия',
  time_limit_exceeded: 'превышение времени',
}[reason] ?? reason ?? 'нарушение правил')

const mapRequest = (item) => ({
  ...item,
  name: `${item.first_name || ''} ${item.last_name || ''}`.trim() || 'Без имени',
  email: item.parent_email || item.user?.email || 'Не указан',
  subjectName: item.subject?.name || 'Без предмета',
  payment_status: item.payment_status || 'pending',
  reconciliation_status: item.reconciliation_status || 'awaiting_payment',
  payment_url: item.payment_url || fallbackPaymentUrl,
  disqualified_at: item.disqualified_at || null,
  disqualification_reason: item.disqualification_reason || null,
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

const resetAttempt = async (request) => {
  try {
    const { data } = await api.post(`/admin/requests/${request.id}/reset-attempt`)
    syncRequest(request, data)
  } catch (error) {
    console.error(error)
    errorMessage.value = error.response?.data?.message || 'Не удалось сбросить попытку.'
  }
}

const viewRequest = (request) => {
  selectedRequest.value = { ...request }
}

onMounted(loadRequests)
</script>

<style scoped>
.toolbar {
  display: grid;
  gap: 10px;
  width: min(100%, 520px);
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-box svg {
  position: absolute;
  left: 12px;
  color: var(--text-tertiary);
  pointer-events: none;
}

.search-box input { padding-left: 40px; }

.filter-group {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
}

.filter-label {
  margin-right: 4px;
  color: var(--text-secondary);
  font-size: 13.5px;
  font-weight: 600;
}

.filter-btn {
  min-height: 34px;
  padding: 5px 12px;
  border-radius: var(--radius-pill);
  border: 1.5px solid var(--border-strong);
  background: var(--card);
  color: var(--text-secondary);
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
}

.filter-btn:hover { border-color: var(--brand); color: var(--brand-ink); }

.filter-btn.active {
  border-color: var(--brand);
  background: var(--brand);
  color: var(--on-brand);
}

.stat-card span { font-size: 13.5px; opacity: 0.85; }

.stat-card strong {
  font-family: var(--font-display);
  font-size: 28px;
  font-variant-numeric: tabular-nums;
}

.stat-card.pending,
.stat-card.paid,
.stat-card.review { border-color: transparent; }
.stat-card.review { background: var(--brand-soft); color: var(--brand-ink); }

.request-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(440px, 1fr));
  gap: 14px;
}

.request-card {
  display: grid;
  align-content: start;
  gap: 14px;
  padding: 18px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
}

.request-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.request-id {
  color: var(--text-tertiary);
  font-size: 12.5px;
  font-family: ui-monospace, 'SF Mono', Consolas, monospace;
  overflow-wrap: anywhere;
}

.request-top h2 { margin-top: 2px; font-size: 17px; }

.request-meta {
  color: var(--text-secondary);
  font-size: 14px;
}

.pill-stack {
  display: grid;
  justify-items: end;
  gap: 4px;
}

.reconciliation-pill.matched { background: var(--success-soft); color: var(--success-ink); }
.reconciliation-pill.reported { background: var(--brand-soft); color: var(--brand-ink); }
.reconciliation-pill.needs_review { background: var(--danger-soft); color: var(--danger-ink); }
.reconciliation-pill.awaiting_payment { background: var(--warning-soft); color: var(--warning-ink); }

.request-details {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px 14px;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.detail span {
  display: block;
  color: var(--text-tertiary);
  font-size: 12.5px;
}

.detail strong {
  font-size: 14px;
  font-weight: 600;
  overflow-wrap: anywhere;
}

.request-note {
  color: var(--text-secondary);
  font-size: 14px;
}

.disqualified-note {
  padding: 10px 12px;
  border-radius: var(--radius-sm);
  background: var(--danger-soft);
  color: var(--danger-ink);
  font-size: 14px;
  font-weight: 500;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding-top: 12px;
  border-top: 1px solid var(--border);
}

/* Ссылка «Открыть Kaspi» выглядит как остальные кнопки ряда */
.actions .ghost-btn.link-btn {
  min-height: 42px;
  padding: 8px 16px;
  border: 1.5px solid var(--border-strong);
  border-radius: var(--radius-sm);
  background: var(--card);
  color: var(--text);
  text-decoration: none;
}

.modal-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 8px;
}

.modal-field,
.payment-link-card {
  display: grid;
  gap: 2px;
  padding: 10px 12px;
  border-radius: var(--radius-sm);
  background: var(--bg);
}

.modal-field span,
.payment-link-card span {
  color: var(--text-tertiary);
  font-size: 12.5px;
}

.modal-field strong,
.payment-link-card strong {
  font-size: 14.5px;
  overflow-wrap: anywhere;
}

.payment-link-card a {
  color: var(--brand-ink);
  font-weight: 600;
}

@media (max-width: 1000px) {
  .request-grid { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
  .request-top { flex-direction: column; }
  .pill-stack { justify-items: start; display: flex; flex-wrap: wrap; }
  .request-details,
  .modal-grid { grid-template-columns: 1fr; }
}
</style>
