<template>
  <div class="success-page">
    <div class="success-card">
      <div class="funnel-progress" aria-label="Прогресс оформления">
        <div class="funnel-progress__top">
          <strong>Шаг 3 из 3</strong>
          <span>Заявка сохранена, дальше остаётся оплата и автосверка</span>
        </div>
        <div class="funnel-progress__track">
          <div class="funnel-progress__fill" style="width: 100%;"></div>
        </div>
      </div>

      <p class="eyebrow">Заявка принята</p>
      <h1>Что дальше?</h1>
      <p class="lead">
        Заявка на олимпиаду <strong>{{ subjectName }}</strong> сохранена. После оплаты нажмите
        <strong>«Я оплатил»</strong>, и система начнёт автосверку платежа.
      </p>

      <div class="steps">
        <article class="step">
          <span class="step__index">1</span>
          <div>
            <h2>Оплата</h2>
            <p>Откройте ссылку Kaspi и оплатите участие по общей ссылке.</p>
          </div>
        </article>
        <article class="step">
          <span class="step__index">2</span>
          <div>
            <h2>Автосверка</h2>
            <p>Нажмите «Я оплатил», и система начнёт искать платёж в импортированной Kaspi-выгрузке.</p>
          </div>
        </article>
        <article class="step">
          <span class="step__index">3</span>
          <div>
            <h2>Доступ</h2>
            <p>Как только платёж будет подтверждён, кнопка старта станет доступной автоматически.</p>
          </div>
        </article>
      </div>

      <div class="status-box" :class="statusTone">
        <strong>{{ mainStatusLabel }}</strong>
        <p>{{ statusDescription }}</p>
      </div>

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
          hint="После оплаты нажмите «Я оплатил», а статус обновится автоматически"
          mobile-cta="Оплатить через Kaspi"
          desktop-cta="Открыть ссылку оплаты"
        />
        <div v-if="showPayButton" class="payment-cta">
          <a :href="paymentUrl" target="_blank" rel="noopener" class="btn btn-primary">Оплатить через Kaspi</a>
          <button
            v-if="showReportButton"
            type="button"
            class="btn btn-secondary"
            :disabled="reportingPayment"
            @click="reportPayment"
          >
            {{ reportingPayment ? 'Отмечаем оплату...' : 'Я оплатил' }}
          </button>
        </div>
        <RouterLink v-if="isPaid" :to="quizLink" class="btn btn-primary">Начать тест</RouterLink>
        <RouterLink :to="backLink" class="btn btn-secondary">Вернуться к предмету</RouterLink>
        <RouterLink to="/profile" class="btn btn-secondary">Открыть кабинет</RouterLink>
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

<style scoped>
* { box-sizing: border-box; }
.success-page { min-height: 100vh; padding: 110px 18px 48px; background: radial-gradient(circle at top left, rgba(201,171,99,.14), transparent 24%), var(--bg); }
.success-card { max-width: 920px; margin: 0 auto; background: var(--surface); border: 1px solid var(--surface-border); border-radius: 28px; box-shadow: var(--shadow-card); padding: 32px; display: grid; gap: 22px; }
.funnel-progress { padding: 16px 18px; border-radius: 20px; background: rgba(232,223,200,.44); border: 1px solid rgba(26,95,168,.12); }
.funnel-progress__top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.funnel-progress__top strong { color: var(--text); font-size: 15px; }
.funnel-progress__top span { color: var(--text-secondary); font-size: 13px; }
.funnel-progress__track { width: 100%; height: 8px; border-radius: 999px; overflow: hidden; background: rgba(26,95,168,.12); }
.funnel-progress__fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--info) 0%, #4d8bc9 100%); }
.eyebrow { margin: 0; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; color: var(--accent-strong); }
h1 { margin: 0; color: var(--text); font-size: 38px; }
.lead, .feedback { margin: 0; color: var(--text-secondary); line-height: 1.7; }
.steps { display: grid; gap: 14px; }
.step { display: grid; grid-template-columns: 40px 1fr; gap: 14px; padding: 18px; border-radius: 18px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.82); }
.step__index { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: rgba(201,171,99,.16); color: var(--accent-strong); font-weight: 800; }
.step h2, .payment-meta__item strong { margin: 0; color: var(--text); }
.step p { margin: 6px 0 0; color: var(--text-secondary); line-height: 1.6; }
.status-box { border-radius: 22px; padding: 18px; border: 1px solid var(--surface-border); }
.status-box strong { display: block; margin-bottom: 6px; color: var(--text); }
.status-box p { margin: 0; color: var(--text-secondary); }
.status-box.success { background: var(--success-bg); }
.status-box.warning { background: var(--warning-bg); }
.status-box.pending { background: rgba(232,223,200,.44); }
.payment-meta { display: grid; gap: 12px; }
.payment-meta__item { padding: 16px; border-radius: 18px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.82); }
.payment-meta__item span { display: block; margin-bottom: 4px; color: var(--text-secondary); font-size: 13px; }
.actions, .payment-cta { display: flex; flex-wrap: wrap; gap: 12px; }
.btn { display: inline-flex; align-items: center; justify-content: center; border-radius: 14px; padding: 12px 16px; text-decoration: none; border: 1px solid transparent; cursor: pointer; font-weight: 700; }
.btn-primary { background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%); color: var(--text); }
.btn-secondary { background: rgba(255,252,244,.82); color: var(--text); border-color: var(--surface-border); }
</style>
