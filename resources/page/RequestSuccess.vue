<template>
  <div class="success-page">
    <div class="success-card">
      <div class="funnel-progress" aria-label="Прогресс оформления">
        <div class="funnel-progress__top">
          <strong>Шаг 3 из 3</strong>
          <span>Заявка принята и ожидает следующий шаг</span>
        </div>
        <div class="funnel-progress__track">
          <div class="funnel-progress__fill" style="width: 100%;"></div>
        </div>
      </div>
      <p class="eyebrow">Заявка принята</p>
      <h1>Что дальше?</h1>
      <p class="lead">
        Заявка на олимпиаду <strong>{{ subjectName }}</strong> сохранена. Дальше осталось пройти три понятных шага.
      </p>

      <div class="steps">
        <article class="step">
          <span class="step__index">1</span>
          <div>
            <h2>Оплата</h2>
            <p>Оплатите участие через Kaspi, чтобы заявка перешла в обработку и не потерялась.</p>
          </div>
        </article>
        <article class="step">
          <span class="step__index">2</span>
          <div>
            <h2>Доступ к тесту</h2>
            <p>После подтверждения оплаты в кабинете появится кнопка для старта олимпиады.</p>
          </div>
        </article>
        <article class="step">
          <span class="step__index">3</span>
          <div>
            <h2>Результат</h2>
            <p>После прохождения вы увидите результат и сможете вернуться к нему в личном кабинете.</p>
          </div>
        </article>
      </div>

      <div class="status-box">
        <strong>{{ statusTitle }}</strong>
        <p>{{ statusDescription }}</p>
      </div>

      <div class="actions">
        <div v-if="showPayButton" class="payment-cta">
          <a :href="paymentUrl" target="_blank" rel="noopener" class="btn btn-primary">Оплатить через Kaspi</a>
          <span class="payment-cta__hint">Результат сразу после теста</span>
        </div>
        <RouterLink v-else-if="isPaid" :to="`/quiz/${subjectId}`" class="btn btn-primary">Начать тест</RouterLink>
        <RouterLink :to="backLink" class="btn btn-secondary">Вернуться к предмету</RouterLink>
        <RouterLink to="/profile" class="btn btn-secondary">Открыть кабинет</RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../js/api'

const route = useRoute()
const paymentStatus = ref('')
const paymentUrl = ref('')

const subjectId = computed(() => String(route.query.subject || ''))
const childId = computed(() => String(route.query.child || ''))
const subjectName = computed(() => String(route.query.subjectName || 'олимпиаду'))
const backLink = computed(() => `/subject?subject=${subjectId.value}`)
const isPaid = computed(() => paymentStatus.value === 'paid')
const showPayButton = computed(() => Boolean(paymentUrl.value) && paymentStatus.value !== 'paid')

const statusTitle = computed(() => {
  if (paymentStatus.value === 'paid') return 'Оплата подтверждена'
  if (paymentStatus.value === 'failed') return 'Оплата требует повторной проверки'
  return 'Ожидается оплата'
})

const statusDescription = computed(() => {
  if (paymentStatus.value === 'paid') return 'Доступ к олимпиаде скоро появится в вашем кабинете или уже доступен.'
  if (paymentStatus.value === 'failed') return 'Если платёж не прошёл, попробуйте открыть оплату ещё раз или свяжитесь с поддержкой.'
  return 'Как только вы оплатите участие, администратор подтвердит платёж и откроет следующий шаг.'
})

const fetchStatus = async () => {
  if (!subjectId.value) return

  try {
    const { data } = await api.get('/olympiad/request/status', {
      params: {
        subject_id: subjectId.value,
        ...(childId.value ? { child_profile_id: childId.value } : {}),
      },
    })
    paymentStatus.value = data.payment_status || ''
    paymentUrl.value = data.payment_url || ''
  } catch {}
}

onMounted(fetchStatus)
</script>

<style scoped>
* { box-sizing: border-box; }
.success-page { min-height: 100vh; padding: 110px 18px 48px; background: radial-gradient(circle at top left, rgba(201,171,99,.14), transparent 24%), var(--bg); }
.success-card { max-width: 860px; margin: 0 auto; background: var(--surface); border: 1px solid var(--surface-border); border-radius: 28px; box-shadow: var(--shadow-card); padding: 32px; display: grid; gap: 22px; }
.funnel-progress { padding: 16px 18px; border-radius: 20px; background: rgba(232,223,200,.44); border: 1px solid rgba(26,95,168,.12); }
.funnel-progress__top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.funnel-progress__top strong { color: var(--text); font-size: 15px; }
.funnel-progress__top span { color: var(--text-secondary); font-size: 13px; }
.funnel-progress__track { width: 100%; height: 8px; border-radius: 999px; overflow: hidden; background: rgba(26,95,168,.12); }
.funnel-progress__fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--info) 0%, #4d8bc9 100%); }
.eyebrow { margin: 0; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; color: var(--accent-strong); }
h1 { margin: 0; color: var(--text); font-size: 38px; }
.lead { margin: 0; color: var(--text-secondary); line-height: 1.7; }
.steps { display: grid; gap: 14px; }
.step { display: grid; grid-template-columns: 40px 1fr; gap: 14px; padding: 18px; border-radius: 18px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.82); }
.step__index { width: 40px; height: 40px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: rgba(201,171,99,.16); color: var(--accent-strong); font-weight: 800; }
.step h2 { margin: 0 0 6px; color: var(--text); font-size: 18px; }
.step p { margin: 0; color: var(--text-secondary); line-height: 1.6; }
.status-box { padding: 18px; border-radius: 18px; background: rgba(79,167,116,.08); border: 1px solid rgba(79,167,116,.16); }
.status-box strong { display: block; margin-bottom: 6px; color: var(--text); }
.status-box p { margin: 0; color: var(--text-secondary); line-height: 1.6; }
.actions { display: flex; flex-wrap: wrap; gap: 12px; }
.payment-cta { display: grid; gap: 6px; }
.payment-cta__hint { font-size: 12px; font-weight: 600; color: var(--text-secondary); }
.btn { display: inline-flex; align-items: center; justify-content: center; min-height: 48px; padding: 12px 18px; border-radius: 14px; text-decoration: none; font-weight: 700; }
.btn-primary { background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%); color: var(--text); }
.btn-secondary { background: rgba(255,252,244,.82); color: var(--accent-strong); border: 1px solid var(--surface-border); }
@media (max-width: 640px) { .success-card { padding: 24px 18px; } .funnel-progress__top { flex-direction: column; align-items: flex-start; } h1 { font-size: 30px; } .actions { flex-direction: column; } }
</style>
