<template>
  <div class="waiting-page">
    <div class="waiting-card">
      <p class="eyebrow">Статус заявки</p>
      <h1>{{ waitingTitle }}</h1>

      <p v-if="status === 'pending'" class="lead">
        Заявка ещё проходит модерацию. Как только статус обновится, здесь появятся следующие действия.
      </p>

      <template v-else-if="status === 'approved'">
        <p class="lead"><strong>{{ mainStatusLabel }}</strong></p>
        <p class="lead">{{ statusDescription }}</p>

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
            <span>Статус</span>
            <strong>{{ paymentMetaLabel }}</strong>
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

const waitingTitle = '\u041E\u043F\u043B\u0430\u0442\u0430 \u0438 \u0434\u043E\u0441\u0442\u0443\u043F \u043A \u043E\u043B\u0438\u043C\u043F\u0438\u0430\u0434\u0435'
const waitingForPaymentLabel = '\u041E\u0436\u0438\u0434\u0430\u0435\u043C \u043E\u043F\u043B\u0430\u0442\u0443'
const paymentReportedLabel = '\u041F\u043B\u0430\u0442\u0451\u0436 \u043E\u0442\u043C\u0435\u0447\u0435\u043D, \u0438\u0434\u0451\u0442 \u0430\u0432\u0442\u043E\u0441\u0432\u0435\u0440\u043A\u0430'
const paymentReviewLabel = '\u0410\u0432\u0442\u043E\u0441\u0432\u0435\u0440\u043A\u0430 \u043D\u0435 \u0437\u0430\u0432\u0435\u0440\u0448\u0438\u043B\u0430\u0441\u044C, \u043D\u0443\u0436\u043D\u0430 \u043F\u0440\u043E\u0432\u0435\u0440\u043A\u0430'
const paymentConfirmedLabel = '\u041E\u043F\u043B\u0430\u0442\u0430 \u043F\u043E\u0434\u0442\u0432\u0435\u0440\u0436\u0434\u0435\u043D\u0430, \u0434\u043E\u0441\u0442\u0443\u043F \u043E\u0442\u043A\u0440\u044B\u0442'
const paymentConfirmedShortLabel = '\u041E\u043F\u043B\u0430\u0442\u0430 \u043F\u043E\u0434\u0442\u0432\u0435\u0440\u0436\u0434\u0435\u043D\u0430'
const needsReviewShortLabel = '\u041D\u0443\u0436\u043D\u0430 \u043F\u0440\u043E\u0432\u0435\u0440\u043A\u0430'
const autoCheckShortLabel = '\u0418\u0434\u0451\u0442 \u0430\u0432\u0442\u043E\u0441\u0432\u0435\u0440\u043A\u0430'

const mainStatusLabel = computed(() => {
  if (paymentStatus.value === 'paid') return paymentConfirmedLabel
  if (reconciliationStatus.value === 'reported') return paymentReportedLabel
  if (reconciliationStatus.value === 'needs_review' || paymentStatus.value === 'failed') return paymentReviewLabel
  return waitingForPaymentLabel
})

const paymentMetaLabel = computed(() => {
  if (paymentStatus.value === 'paid') return paymentConfirmedShortLabel
  if (reconciliationStatus.value === 'reported') return autoCheckShortLabel
  if (reconciliationStatus.value === 'needs_review' || paymentStatus.value === 'failed') return needsReviewShortLabel
  return waitingForPaymentLabel
})

const statusDescription = computed(() => {
  if (paymentStatus.value === 'paid') {
    return 'Оплата подтверждена, доступ к олимпиаде уже открыт.'
  }

  if (reconciliationStatus.value === 'reported') {
    return 'Платёж отмечен. Сейчас система автоматически сверяет его с выгрузкой Kaspi.'
  }

  if (reconciliationStatus.value === 'needs_review' || paymentStatus.value === 'failed') {
    return 'Автосверка не смогла однозначно найти платёж. Заявка ждёт проверки.'
  }

  return 'Оплатите участие и после этого нажмите «Я оплатил», чтобы запустить автосверку.'
})

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
  margin-bottom: 4px;
  color: var(--text-secondary);
  font-size: 13px;
}

.payment-meta__item strong {
  color: var(--text);
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.btn {
  border: 1px solid transparent;
  border-radius: 14px;
  padding: 12px 16px;
  font-weight: 700;
  cursor: pointer;
}

.btn-primary {
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  color: var(--text);
}

.btn-secondary {
  background: rgba(255,252,244,.82);
  color: var(--text);
  border-color: var(--surface-border);
}
</style>
