<template>
  <div class="waiting-page">
    <div class="waiting-card">
      <p class="eyebrow">Статус заявки</p>
      <h1>Оплата и доступ к олимпиаде</h1>

      <p v-if="status === 'pending'" class="lead">
        Заявка ещё проходит модерацию. Как только статус обновится, здесь появятся следующие действия.
      </p>

      <template v-else-if="status === 'approved'">
        <p class="lead">
          <span v-if="paymentStatus === 'paid'">
            Оплата подтверждена, доступ к олимпиаде уже открыт.
          </span>
          <span v-else>
            Заявка оформлена. После оплаты нажмите «Я оплатил», и система начнёт автосверку платежа.
          </span>
        </p>

        <div v-if="paymentReference || paymentComment" class="payment-meta">
          <div class="payment-meta__item">
            <span>Request ID</span>
            <strong>{{ paymentReference || '—' }}</strong>
          </div>
          <div class="payment-meta__item">
            <span>Комментарий к оплате</span>
            <strong>{{ paymentComment || paymentReference || '—' }}</strong>
          </div>
          <div class="payment-meta__item">
            <span>Сверка</span>
            <strong>{{ reconciliationStatusLabel }}</strong>
          </div>
        </div>

        <p v-if="feedbackMessage" class="feedback">{{ feedbackMessage }}</p>

        <div class="actions">
          <KaspiPaymentAssist
            v-if="showPayButton"
            :payment-url="paymentUrl"
            hint="После оплаты нажмите «Я оплатил», а экран обновится автоматически"
            mobile-cta="Оплатить через Kaspi"
            desktop-cta="Открыть ссылку оплаты"
          />
          <a v-if="showPayButton" :href="paymentUrl" target="_blank" rel="noopener" class="btn btn-primary">Оплатить через Kaspi</a>
          <button
            v-if="showReportButton"
            type="button"
            class="btn btn-secondary"
            :disabled="reportingPayment"
            @click="reportPayment"
          >
            {{ reportingPayment ? 'Отмечаем оплату...' : 'Я оплатил' }}
          </button>
          <button v-if="paymentStatus === 'paid'" type="button" class="btn btn-primary" @click="goToQuiz">Начать олимпиаду</button>
        </div>
      </template>

      <p v-else-if="status === 'rejected'" class="lead">Заявка отклонена. Проверьте данные или свяжитесь с поддержкой.</p>
      <p v-else class="lead">Вы ещё не оформили участие.</p>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../js/api'
import KaspiPaymentAssist from '../components/KaspiPaymentAssist.vue'

const route = useRoute()
const router = useRouter()

const status = ref('')
const paymentStatus = ref('')
const paymentUrl = ref('')
const subjectId = ref(String(route.query.subject || ''))
const childProfileId = ref(String(route.query.child || ''))
const paymentReference = ref(String(route.query.request || ''))
const paymentComment = ref('')
const reconciliationStatus = ref('awaiting_payment')
const feedbackMessage = ref('')
const reportingPayment = ref(false)

let pollTimer = null

const reconciliationStatusLabel = computed(() => ({
  awaiting_payment: 'Ожидаем оплату',
  reported: 'Платёж на сверке',
  matched: 'Сверка завершена',
  needs_review: 'Нужна ручная проверка',
}[reconciliationStatus.value] || 'Ожидаем оплату'))

const showPayButton = computed(() => Boolean(paymentUrl.value) && paymentStatus.value !== 'paid')
const showReportButton = computed(() => Boolean(paymentReference.value) && status.value === 'approved' && paymentStatus.value !== 'paid')

const applyPayload = (data = {}) => {
  status.value = data.status || ''
  paymentStatus.value = data.payment_status || ''
  paymentUrl.value = data.payment_url || ''
  subjectId.value = data.subject_id || subjectId.value || ''
  childProfileId.value = data.child_profile_id || childProfileId.value || ''
  paymentReference.value = data.payment_reference || paymentReference.value || ''
  paymentComment.value = data.payment_comment || ''
  reconciliationStatus.value = data.reconciliation_status || 'awaiting_payment'
}

const clearPolling = () => {
  if (pollTimer) {
    window.clearInterval(pollTimer)
    pollTimer = null
  }
}

const syncPolling = () => {
  clearPolling()

  if (!status.value || paymentStatus.value === 'paid' || status.value === 'rejected') {
    return
  }

  pollTimer = window.setInterval(() => {
    fetchStatus()
  }, 15000)
}

const fetchStatus = async () => {
  try {
    const res = await api.get('/olympiad/request/status', {
      params: {
        ...(subjectId.value ? { subject_id: subjectId.value } : {}),
        ...(childProfileId.value ? { child_profile_id: childProfileId.value } : {}),
      },
    })

    applyPayload(res.data)
    syncPolling()
  } catch (err) {
    console.error(err)
    clearPolling()
  }
}

const reportPayment = async () => {
  if (!paymentReference.value || reportingPayment.value) return

  reportingPayment.value = true
  feedbackMessage.value = ''

  try {
    const { data } = await api.post(`/olympiad/request/${paymentReference.value}/payment-report`, {
      paid_at: new Date().toISOString(),
    })

    applyPayload({
      ...data,
      payment_status: data.payment_status || data.request?.payment_status,
    })
    feedbackMessage.value = data.message || 'Платёж отмечен и отправлен на сверку.'
    syncPolling()
  } catch (error) {
    feedbackMessage.value = error.response?.data?.message || 'Не удалось отметить платёж. Попробуйте ещё раз.'
  } finally {
    reportingPayment.value = false
  }
}

function goToQuiz() {
  if (subjectId.value) {
    router.push({
      path: `/quiz/${subjectId.value}`,
      query: childProfileId.value ? { childId: childProfileId.value } : {},
    })
    return
  }

  router.push('/subject')
}

onMounted(() => {
  fetchStatus()
})

onBeforeUnmount(() => {
  clearPolling()
})
</script>

<style scoped>
.waiting-page {
  min-height: 100vh;
  display: grid;
  place-items: center;
  background: linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%);
  padding: 40px 18px;
}

.waiting-card {
  width: min(760px, 100%);
  display: grid;
  gap: 18px;
  background: var(--surface);
  border: 1px solid var(--surface-border);
  border-radius: 28px;
  box-shadow: var(--shadow-card);
  padding: 28px;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

h1 {
  margin: 0;
  color: var(--text);
}

.lead,
.feedback {
  margin: 0;
  color: var(--text-secondary);
  line-height: 1.7;
}

.payment-meta {
  display: grid;
  gap: 12px;
}

.payment-meta__item {
  padding: 16px;
  border-radius: 18px;
  border: 1px solid var(--surface-border);
  background: rgba(255,252,244,.82);
}

.payment-meta__item span {
  display: block;
  margin-bottom: 6px;
  font-size: 12px;
  font-weight: 700;
  color: var(--text-secondary);
}

.payment-meta__item strong {
  color: var(--text);
  word-break: break-word;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.btn,
.actions > a {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
  padding: 12px 18px;
  border-radius: 14px;
  text-decoration: none;
  border: 0;
  cursor: pointer;
  font-weight: 700;
}

.btn-primary,
.actions > a {
  background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%);
  color: var(--text);
}

.btn-secondary {
  background: rgba(255,252,244,.82);
  color: var(--accent-strong);
  border: 1px solid var(--surface-border);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 640px) {
  .waiting-card {
    padding: 22px 18px;
  }

  .actions {
    flex-direction: column;
  }
}
</style>
