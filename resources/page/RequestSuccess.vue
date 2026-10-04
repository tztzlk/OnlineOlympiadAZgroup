<template>
  <div class="pay-page">
    <div class="pay-card">
      <div class="funnel-progress" aria-label="Прогресс оформления">
        <div class="funnel-progress__top">
          <strong>Шаг 3 из 3</strong>
          <span>Заявка сохранена, дальше остаётся оплата и автосверка</span>
        </div>
        <div class="funnel-progress__track">
          <div class="funnel-progress__fill"></div>
        </div>
      </div>

      <header class="success-head">
        <span class="success-head__icon" aria-hidden="true">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
        </span>
        <div>
          <p class="pay-eyebrow">Заявка принята</p>
          <h1>Что дальше?</h1>
        </div>
      </header>

      <p class="pay-lead">
        Заявка на олимпиаду <strong>{{ subjectName }}</strong> сохранена. После оплаты нажмите
        <strong>«Я оплатил»</strong>, и система начнёт автосверку платежа.
      </p>

      <ol class="steps">
        <li class="step">
          <span class="step__index">1</span>
          <div>
            <h2>Оплата</h2>
            <p>Откройте ссылку Kaspi и оплатите участие по общей ссылке.</p>
          </div>
        </li>
        <li class="step">
          <span class="step__index">2</span>
          <div>
            <h2>Автосверка</h2>
            <p>Нажмите «Я оплатил», и система начнёт искать платёж в импортированной Kaspi-выгрузке.</p>
          </div>
        </li>
        <li class="step">
          <span class="step__index">3</span>
          <div>
            <h2>Доступ</h2>
            <p>Как только платёж будет подтверждён, кнопка старта станет доступной автоматически.</p>
          </div>
        </li>
      </ol>

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
          hint="После оплаты нажмите «Я оплатил», а статус обновится автоматически"
          mobile-cta="Оплатить через Kaspi"
          desktop-cta="Открыть ссылку оплаты"
        />
        <div class="pay-actions__row">
          <button
            v-if="showPayButton && showReportButton"
            type="button"
            class="ds-btn ds-btn-primary ds-btn-lg"
            :disabled="reportingPayment"
            @click="reportPayment"
          >
            <span v-if="reportingPayment" class="ds-spinner" aria-hidden="true"></span>
            {{ reportingPayment ? 'Отмечаем оплату...' : 'Я оплатил' }}
          </button>
          <RouterLink v-if="isPaid" :to="quizLink" class="ds-btn ds-btn-primary ds-btn-lg">Начать тест</RouterLink>
          <RouterLink :to="backLink" class="ds-btn ds-btn-ghost ds-btn-lg">Вернуться к предмету</RouterLink>
          <RouterLink to="/profile" class="ds-btn ds-btn-ghost ds-btn-lg">Открыть кабинет</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../js/api'
import KaspiPaymentAssist from '../components/KaspiPaymentAssist.vue'

const route = useRoute()

const paymentStatus = ref('')
const paymentUrl = ref('')
const paymentReference = ref(String(route.query.request || ''))
const paymentComment = ref('')
const reconciliationStatus = ref('awaiting_payment')
const feedbackMessage = ref('')
const reportingPayment = ref(false)

let pollTimer = null

const subjectId = computed(() => String(route.query.subject || ''))
const childId = computed(() => String(route.query.child || ''))
const subjectName = computed(() => String(route.query.subjectName || 'олимпиаду'))
const backLink = computed(() => `/subject?subject=${subjectId.value}`)
const quizLink = computed(() => ({
  path: `/quiz/${subjectId.value}`,
  query: childId.value ? { childId: childId.value } : {},
}))

const isPaid = computed(() => paymentStatus.value === 'paid')
const showPayButton = computed(() => Boolean(paymentUrl.value) && paymentStatus.value !== 'paid')
const showReportButton = computed(() => Boolean(paymentReference.value) && paymentStatus.value !== 'paid')

const mainStatusLabel = computed(() => {
  if (paymentStatus.value === 'paid') return 'Оплата подтверждена, доступ открыт'
  if (reconciliationStatus.value === 'reported') return 'Платёж отмечен, идёт автосверка'
  if (reconciliationStatus.value === 'needs_review' || paymentStatus.value === 'failed') return 'Автосверка не завершилась, нужна проверка'
  return 'Ожидаем оплату'
})

const paymentMetaLabel = computed(() => {
  if (paymentStatus.value === 'paid') return 'Оплата подтверждена'
  if (reconciliationStatus.value === 'reported') return 'Идёт автосверка'
  if (reconciliationStatus.value === 'needs_review' || paymentStatus.value === 'failed') return 'Нужна проверка'
  return 'Ожидаем оплату'
})

const statusTone = computed(() => {
  if (paymentStatus.value === 'paid') return 'success'
  if (reconciliationStatus.value === 'needs_review' || paymentStatus.value === 'failed') return 'warning'
  return 'pending'
})

const statusDescription = computed(() => {
  if (paymentStatus.value === 'paid') {
    return 'Доступ к олимпиаде уже открыт. Можно сразу переходить к тесту.'
  }

  if (reconciliationStatus.value === 'reported') {
    return 'Платёж отмечен. Сейчас система автоматически сверяет его с выгрузкой Kaspi.'
  }

  if (reconciliationStatus.value === 'needs_review' || paymentStatus.value === 'failed') {
    return 'Автосверка не смогла однозначно найти платёж. Заявка ждёт проверки.'
  }

  return 'Оплатите участие и после этого нажмите «Я оплатил», чтобы запустить автосверку.'
})

const applyStatusPayload = (data = {}) => {
  paymentStatus.value = data.payment_status || ''
  paymentUrl.value = data.payment_url || ''
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

  if (!subjectId.value || paymentStatus.value === 'paid') {
    return
  }

  pollTimer = window.setInterval(() => {
    fetchStatus()
  }, 15000)
}

const fetchStatus = async () => {
  if (!subjectId.value) return

  try {
    const { data } = await api.get('/olympiad/request/status', {
      params: {
        subject_id: subjectId.value,
        ...(childId.value ? { child_profile_id: childId.value } : {}),
      },
    })

    applyStatusPayload(data)
    syncPolling()
  } catch {
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

    applyStatusPayload({
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

onMounted(async () => {
  await fetchStatus()
})

onBeforeUnmount(() => {
  clearPolling()
})
</script>

<style src="../css/payment-flow.css"></style>

<style scoped>
.funnel-progress {
  display: grid;
  gap: 10px;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.funnel-progress__top {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 6px 12px;
}

.funnel-progress__top strong { font-size: 15px; }

.funnel-progress__top span {
  color: var(--text-secondary);
  font-size: 14px;
}

.funnel-progress__track {
  height: 8px;
  border-radius: var(--radius-pill);
  background: var(--bg-alt);
  overflow: hidden;
}

.funnel-progress__fill {
  width: 100%;
  height: 100%;
  border-radius: inherit;
  background: var(--success);
  animation: funnel-fill 900ms var(--ease-out) both;
}

@keyframes funnel-fill { from { width: 66%; } }

.success-head {
  display: flex;
  align-items: center;
  gap: 16px;
}

.success-head__icon {
  width: 64px;
  height: 64px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 20px;
  background: var(--success);
  color: #ffffff;
  box-shadow: 0 14px 30px rgba(18, 160, 101, 0.3);
  animation: pop 520ms var(--ease-out) both;
}

@keyframes pop {
  from { transform: scale(0.6) rotate(-12deg); opacity: 0; }
  to   { transform: none; opacity: 1; }
}

.steps {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
  list-style: none;
}

.step {
  display: grid;
  align-content: start;
  gap: 10px;
  padding: 16px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.step__index {
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  border-radius: 11px;
  background: var(--brand);
  color: #ffffff;
  font-weight: 700;
}

.step:nth-child(2) .step__index { background: var(--sun); color: #1f1600; }
.step:nth-child(3) .step__index { background: var(--success); }

.step h2 {
  font-family: var(--font-sans);
  font-size: 16px;
  font-weight: 700;
  letter-spacing: 0;
}

.step p {
  margin-top: 4px;
  color: var(--text-secondary);
  font-size: 14px;
  line-height: 1.55;
}

@media (max-width: 720px) {
  .steps { grid-template-columns: 1fr; }
  .step { grid-template-columns: auto 1fr; align-items: start; }
}
</style>
