<template>
  <div class="pay-page">
    <div class="pay-card">
      <header>
        <p class="pay-eyebrow">Статус заявки</p>
        <h1>{{ waitingTitle }}</h1>
      </header>

      <div v-if="status === 'pending'" class="pay-status pending" role="status">
        <span class="pay-status__icon" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M21 12a9 9 0 1 1-6.2-8.6"/></svg>
        </span>
        <p class="pay-lead">
          Заявка ещё проходит модерацию. Как только статус обновится, здесь появятся следующие действия.
        </p>
      </div>

      <template v-else-if="status === 'approved'">
        <div class="pay-status" :class="statusTone" role="status">
          <span class="pay-status__icon" aria-hidden="true">
            <svg v-if="statusTone === 'success'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            <svg v-else-if="statusTone === 'warning'" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/></svg>
            <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M21 12a9 9 0 1 1-6.2-8.6"/></svg>
          </span>
          <div>
            <strong>{{ mainStatusLabel }}</strong>
            <p>{{ statusDescription }}</p>
          </div>
        </div>

        <dl v-if="paymentReference || paymentComment" class="pay-meta">
          <div class="pay-meta__item">
            <dt>Request ID</dt>
            <dd>{{ paymentReference || '—' }}</dd>
          </div>
          <div class="pay-meta__item">
            <dt>Комментарий к оплате</dt>
            <dd>{{ paymentComment || paymentReference || '—' }}</dd>
          </div>
          <div class="pay-meta__item">
            <dt>Статус</dt>
            <dd>{{ paymentMetaLabel }}</dd>
          </div>
        </dl>

        <p v-if="feedbackMessage" class="ds-msg info" role="status">{{ feedbackMessage }}</p>

        <div class="pay-actions">
          <KaspiPaymentAssist
            v-if="showPayButton"
            :payment-url="paymentUrl"
            hint="После оплаты нажмите «Я оплатил», а экран обновится автоматически"
            mobile-cta="Оплатить через Kaspi"
            desktop-cta="Открыть ссылку оплаты"
          />
          <div class="pay-actions__row">
            <button
              v-if="showReportButton"
              type="button"
              class="ds-btn ds-btn-primary ds-btn-lg"
              :disabled="reportingPayment"
              @click="reportPayment"
            >
              <span v-if="reportingPayment" class="ds-spinner" aria-hidden="true"></span>
              {{ reportingPayment ? 'Отмечаем оплату...' : 'Я оплатил' }}
            </button>
            <button v-if="paymentStatus === 'paid'" type="button" class="ds-btn ds-btn-primary ds-btn-lg" @click="goToQuiz">Начать олимпиаду</button>
          </div>
        </div>
      </template>

      <StatePanel v-else-if="status === 'rejected'" tone="danger" title="Заявка отклонена" description="Заявка отклонена. Проверьте данные или свяжитесь с поддержкой.">
        <template #actions>
          <RouterLink to="/help-desk" class="ds-btn ds-btn-ghost">Написать в поддержку</RouterLink>
        </template>
      </StatePanel>

      <StatePanel v-else tone="empty" title="Участие не оформлено" description="Вы ещё не оформили участие.">
        <template #actions>
          <RouterLink to="/subject" class="ds-btn ds-btn-primary">Выбрать олимпиаду</RouterLink>
        </template>
      </StatePanel>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../js/api'
import KaspiPaymentAssist from '../components/KaspiPaymentAssist.vue'
import StatePanel from '../components/StatePanel.vue'

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

const waitingTitle = 'Оплата и доступ к олимпиаде'
const waitingForPaymentLabel = 'Ожидаем оплату'
const paymentReportedLabel = 'Платёж отмечен, идёт автосверка'
const paymentReviewLabel = 'Автосверка не завершилась, нужна проверка'
const paymentConfirmedLabel = 'Оплата подтверждена, доступ открыт'
const paymentConfirmedShortLabel = 'Оплата подтверждена'
const needsReviewShortLabel = 'Нужна проверка'
const autoCheckShortLabel = 'Идёт автосверка'

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

const statusTone = computed(() => {
  if (paymentStatus.value === 'paid') return 'success'
  if (reconciliationStatus.value === 'needs_review' || paymentStatus.value === 'failed') return 'warning'
  return 'pending'
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

<style src="../css/payment-flow.css"></style>
