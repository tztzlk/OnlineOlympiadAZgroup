<template>
  <div class="subject-page">
    <div class="container">
      <header class="page-header">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
          <RouterLink to="/">Главная</RouterLink>
          <span aria-hidden="true">/</span>
          <span>Предметы</span>
          <span aria-hidden="true">/</span>
          <span aria-current="page">Оформление участия</span>
        </nav>
        <span class="ds-eyebrow">Предметы</span>
        <h1 class="page-title">Выберите олимпиаду и ознакомьтесь с условиями участия</h1>
        <p class="page-subtitle">
          Выберите предмет, укажите участника и оформите участие.
        </p>
      </header>

      <StatePanel
        v-if="pageLoading"
        tone="info"
        loading
        eyebrow="Загрузка"
        title="Загружаем предметы"
        description="Подождите немного, собираем доступные олимпиады и статус участия."
      />

      <StatePanel
        v-else-if="pageError"
        tone="error"
        eyebrow="Не удалось открыть страницу"
        title="Предметы пока не загрузились"
        :description="pageError"
      >
        <template #actions>
          <button type="button" class="ds-btn ds-btn-primary" @click="initializePage">Повторить загрузку</button>
        </template>
      </StatePanel>

      <template v-else>
        <div class="subjects-grid" role="list">
          <div
            v-for="(subject, index) in subjects"
            :key="subject.id"
            v-reveal="index"
            class="subject-card"
            :class="[{ selected: selectedSubject?.id === subject.id }, `tone-${index % 4}`]"
            role="button"
            tabindex="0"
            :aria-pressed="selectedSubject?.id === subject.id ? 'true' : 'false'"
            @click="selectSubject(subject)"
            @keydown.enter.prevent="selectSubject(subject)"
            @keydown.space.prevent="selectSubject(subject)"
          >
            <span v-if="selectedSubject?.id === subject.id" class="subject-card__picked" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <div class="subject-card__img-wrap">
              <img :src="subject.image" :alt="subject.name" loading="lazy" />
            </div>
            <div class="subject-card__body">
              <h2 class="subject-card__name">{{ subject.name }}</h2>
              <p class="subject-card__desc">{{ subject.description }}</p>
            </div>
            <div class="subject-card__meta">
              <p class="subject-card__price">Участие: {{ formatPrice(subject.price) }}</p>
              <CountdownBadge :target="subject.start_date" label="До старта" />
            </div>
            <RouterLink
              class="subject-card__link"
              :to="`/subjects/${subject.id}`"
              @click.stop
              @keydown.stop
            >
              Страница предмета
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </RouterLink>
          </div>
        </div>

        <StatePanel
          v-if="!subjects.length"
          tone="empty"
          eyebrow="Каталог"
          title="Сейчас нет опубликованных олимпиад"
          description="Как только организаторы откроют новый набор, карточки предметов появятся здесь."
        />

        <section v-if="selectedSubject" ref="registrationBoxRef" class="step-box" aria-labelledby="step-box-title">
          <div class="step-box__header">
            <div>
              <p class="step-pill">Шаг 1 из 3</p>
              <h2 id="step-box-title">Оформление участия</h2>
              <p class="chosen">Предмет: <strong>{{ selectedSubject.name }}</strong></p>
              <p v-if="registrationDeadlineLabel" class="deadline-copy">
                До закрытия регистрации: <strong>{{ registrationDeadlineLabel }}</strong>
              </p>
            </div>
            <CountdownBadge :target="selectedSubject.start_date" label="Старт олимпиады" />
          </div>

          <div v-if="countdownStatusLabel" class="deadline-banner">
            <div>
              <p class="deadline-banner__eyebrow">Регистрация</p>
              <strong>{{ countdownStatusLabel }}</strong>
            </div>
            <span v-if="countdownParts" class="deadline-banner__value">{{ countdownParts }}</span>
          </div>

          <section v-if="!canStartOlympiad" class="rules-card">
            <button
              type="button"
              class="rules-card__toggle"
              :aria-expanded="String(rulesExpanded)"
              aria-controls="participation-rules"
              @click="rulesExpanded = !rulesExpanded"
            >
              <span class="rules-card__icon" aria-hidden="true">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
              </span>
              <span class="rules-card__text">
                <span class="rules-card__eyebrow">Правила участия</span>
                <span class="rules-card__title">Правила теперь доступны прямо на странице оформления</span>
                <span class="rules-card__summary">Ознакомьтесь с условиями без перехода на отдельный экран.</span>
              </span>
              <span class="rules-card__action">
                {{ rulesExpanded ? 'Скрыть' : 'Открыть' }}
                <svg :class="{ 'is-open': rulesExpanded }" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
              </span>
            </button>

            <div v-if="rulesExpanded" id="participation-rules" class="rules-card__body">
              <p class="ds-msg warning">
                Нарушение правил может привести к аннулированию результатов. Участие в олимпиаде означает согласие с условиями платформы.
              </p>

              <ol class="rules-list">
                <li v-for="(rule, index) in participationRules" :key="rule.title" class="rule-item">
                  <span class="rule-item__index">{{ String(index + 1).padStart(2, '0') }}</span>
                  <div>
                    <h4>{{ rule.title }}</h4>
                    <p>{{ rule.desc }}</p>
                  </div>
                </li>
              </ol>
            </div>
          </section>

          <StatePanel
            v-if="!userStore.isAuthenticated"
            tone="warning"
            eyebrow="Нужен аккаунт"
            title="Сначала войдите в кабинет"
            description="После входа можно сохранить данные ребёнка, сразу перейти к оплате и отслеживать подтверждение платежа."
          >
            <template #actions>
              <RouterLink to="/login" class="ds-btn ds-btn-primary">Войти</RouterLink>
              <RouterLink to="/register" class="ds-btn ds-btn-ghost">Регистрация</RouterLink>
            </template>
          </StatePanel>

          <template v-else>
            <!-- Доступ уже открыт — главная кнопка сразу наверху -->
            <div v-if="canStartOlympiad" class="start-hero">
              <span class="start-hero__badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
                Доступ открыт
              </span>
              <h3 class="start-hero__title">Олимпиада готова к прохождению</h3>
              <p class="start-hero__hint">Данные участника сохранены. Нажмите кнопку, чтобы начать.</p>
              <button type="button" class="start-hero__btn" @click="goToQuiz">Начать олимпиаду →</button>
            </div>

            <template v-if="!canStartOlympiad">
              <ol class="flow-grid">
                <li class="flow-step">
                  <span class="flow-step__index">1</span>
                  <div>
                    <h3>Выберите участника</h3>
                    <p>Можно использовать уже созданный профиль ребёнка или заполнить форму ниже для нового участника.</p>
                  </div>
                </li>
                <li class="flow-step">
                  <span class="flow-step__index">2</span>
                  <div>
                    <h3>Сохраните данные и оплатите</h3>
                    <p>Укажите язык олимпиады и контакты родителя. После сохранения откроется ссылка на оплату Kaspi.</p>
                  </div>
                </li>
                <li class="flow-step">
                  <span class="flow-step__index">3</span>
                  <div>
                    <h3>Дождитесь подтверждения оплаты</h3>
                    <p>После оплаты нажмите «Я оплатил». Дальше статус автосверки появится прямо на этой странице.</p>
                  </div>
                </li>
              </ol>

              <div class="form-card">
                <label class="ds-field">
                  <span class="ds-label">Ребёнок</span>
                  <select v-model="selectedChildId" class="ds-input" @change="applyChildSelection">
                    <option value="">Создать или обновить профиль ребёнка из формы ниже</option>
                    <option v-for="child in userStore.children" :key="child.id" :value="String(child.id)">
                      {{ child.full_name }} · {{ child.grade || 'без класса' }}
                    </option>
                  </select>
                  <small v-if="!userStore.children.length" class="ds-hint">
                    У вас пока нет профилей детей. Заполните форму ниже, и профиль создастся автоматически вместе с участием.
                  </small>
                </label>
              </div>

              <fieldset class="form-card">
                <legend class="form-section-label">
                  <span class="form-section-label__icon tone-child" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M6 21v-1a6 6 0 0 1 12 0v1"/></svg>
                  </span>
                  Данные ребёнка
                </legend>

                <div class="fields-row">
                  <label class="ds-field">
                    <span class="ds-label">Имя</span>
                    <input v-model="form.first_name" placeholder="Введите имя" class="ds-input" autocomplete="off" />
                  </label>
                  <label class="ds-field">
                    <span class="ds-label">Фамилия</span>
                    <input v-model="form.last_name" placeholder="Введите фамилию" class="ds-input" autocomplete="off" />
                  </label>
                </div>

                <div class="fields-row">
                  <div class="ds-field">
                    <span class="ds-label" id="birth-label">Дата рождения</span>
                    <div class="date-selects" role="group" aria-labelledby="birth-label">
                      <select v-model="birthDay" class="ds-input" aria-label="День">
                        <option value="">День</option>
                        <option v-for="d in 31" :key="d" :value="d">{{ d }}</option>
                      </select>
                      <select v-model="birthMonth" class="ds-input" aria-label="Месяц">
                        <option value="">Месяц</option>
                        <option v-for="(m, i) in MONTHS" :key="i" :value="i + 1">{{ m }}</option>
                      </select>
                      <select v-model="birthYear" class="ds-input" aria-label="Год">
                        <option value="">Год</option>
                        <option v-for="y in BIRTH_YEARS" :key="y" :value="y">{{ y }}</option>
                      </select>
                    </div>
                  </div>
                  <label class="ds-field">
                    <span class="ds-label">Класс</span>
                    <select v-model.number="form.grade" class="ds-input">
                      <option disabled value="">Выберите класс</option>
                      <option v-for="item in gradeOptions" :key="item" :value="item">{{ item }} класс</option>
                    </select>
                  </label>
                </div>

                <div class="fields-row">
                  <label class="ds-field">
                    <span class="ds-label">Школа</span>
                    <input v-model="form.school" placeholder="Название школы" class="ds-input" />
                  </label>
                  <label class="ds-field">
                    <span class="ds-label">Город</span>
                    <input v-model="form.city" list="kz-cities-subject" placeholder="Город" class="ds-input" />
                    <datalist id="kz-cities-subject">
                      <option v-for="c in KZ_CITIES" :key="c" :value="c" />
                    </datalist>
                  </label>
                </div>

                <!-- Язык пока выбирается автоматически (поле скрыто), значение уходит в заявку -->
                <select v-model="form.language" class="visually-hidden-select" tabindex="-1" aria-hidden="true">
                  <option value="ru">Русский</option>
                  <option value="kk">Қазақша</option>
                  <option value="en">English</option>
                </select>
              </fieldset>

              <fieldset class="form-card">
                <legend class="form-section-label">
                  <span class="form-section-label__icon tone-parent" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg>
                  </span>
                  Данные родителя
                </legend>

                <div class="fields-row">
                  <label class="ds-field">
                    <span class="ds-label">ФИО родителя</span>
                    <input v-model="form.parent_name" placeholder="Полное имя" class="ds-input" autocomplete="name" />
                  </label>
                  <label class="ds-field">
                    <span class="ds-label">Телефон</span>
                    <input v-model="form.parent_phone" type="tel" inputmode="tel" placeholder="+7 (777) 777-77-77" class="ds-input" autocomplete="tel" />
                  </label>
                </div>

                <label class="ds-field">
                  <span class="ds-label">Email</span>
                  <input v-model="form.parent_email" type="email" inputmode="email" placeholder="email@mail.ru" class="ds-input" autocomplete="email" />
                </label>
              </fieldset>

              <div class="single-action">
                <button type="button" :disabled="!canProceed || submitting" class="ds-btn ds-btn-primary ds-btn-lg start-btn" @click="startOlympiad">
                  <span v-if="submitting" class="ds-spinner" aria-hidden="true"></span>
                  {{ submitting ? 'Сохраняем...' : (isFree ? 'Записаться и начать' : 'Сохранить и перейти к оплате') }}
                </button>
                <p v-if="submitError" class="ds-msg error submit-error" role="alert">{{ submitError }}</p>
              </div>
            </template>

            <StatePanel
              v-if="requestStatus"
              :tone="requestTone"
              eyebrow="Статус участия"
              :title="requestStatusLabel"
              :description="requestHint"
            >
              <template #actions>
                <StatusBadge :label="requestStatusLabel" :tone="requestTone" />
                <KaspiPaymentAssist
                  v-if="showKaspiButton"
                  :payment-url="paymentUrl"
                  hint="Оплата открывается в Kaspi"
                  mobile-cta="Оплатить через Kaspi"
                  desktop-cta="Открыть ссылку оплаты"
                />
              </template>
            </StatePanel>

            <div
              v-if="requestStatus && (paymentReference || paymentComment || showReportPaymentButton || paymentReportMessage)"
              class="payment-followup"
            >
              <dl v-if="paymentReference || paymentComment" class="payment-meta">
                <div class="payment-meta__item">
                  <dt>Request ID</dt>
                  <dd>{{ paymentReference || '—' }}</dd>
                </div>
                <div class="payment-meta__item">
                  <dt>Комментарий к оплате</dt>
                  <dd>{{ paymentComment || paymentReference || '—' }}</dd>
                </div>
                <div class="payment-meta__item">
                  <dt>Автосверка</dt>
                  <dd>{{ reconciliationDescription }}</dd>
                </div>
              </dl>

              <div v-if="showReportPaymentButton" class="payment-followup__actions">
                <button
                  type="button"
                  class="ds-btn ds-btn-secondary ds-btn-lg"
                  :disabled="reportingPayment"
                  @click="reportPayment"
                >
                  <span v-if="reportingPayment" class="ds-spinner" aria-hidden="true"></span>
                  {{ reportingPayment ? 'Отмечаем оплату...' : 'Я оплатил' }}
                </button>
                <span class="payment-action__hint">{{ reconciliationDescription }}</span>
              </div>

              <p v-if="paymentReportMessage" class="ds-msg info" role="status">{{ paymentReportMessage }}</p>
            </div>
          </template>
        </section>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'
import StatePanel from '../components/StatePanel.vue'
import StatusBadge from '../components/StatusBadge.vue'
import CountdownBadge from '../components/CountdownBadge.vue'
import KaspiPaymentAssist from '../components/KaspiPaymentAssist.vue'
import { applySeo, getStaticSeoForPath } from '../js/composables/useSeo'
import { KZ_CITIES } from '../js/kazakhstanData'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const subjects = ref([])
const selectedSubject = ref(null)
const registrationBoxRef = ref(null)
const selectedChildId = ref('')
const requestStatus = ref('')
const paymentStatus = ref('')
const paymentUrl = ref('')
const paymentReference = ref('')
const paymentComment = ref('')
const reconciliationStatus = ref('awaiting_payment')
const paymentReportMessage = ref('')
const reportingPayment = ref(false)
const submitting = ref(false)
const submitError = ref('')
const rulesExpanded = ref(false)
const pageLoading = ref(true)
const pageError = ref('')
const nowTs = ref(Date.now())
const gradeOptions = [3, 4, 5, 6, 7, 8, 9, 10, 11]

const MONTHS = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь']
const subjectCurrentYear = new Date().getFullYear()
const BIRTH_YEARS = Array.from({ length: subjectCurrentYear - 2000 + 1 }, (_, i) => subjectCurrentYear - i)

const birthDay = ref('')
const birthMonth = ref('')
const birthYear = ref('')

watch([birthDay, birthMonth, birthYear], ([d, m, y]) => {
  if (d && m && y) {
    const mm = String(m).padStart(2, '0')
    const dd = String(d).padStart(2, '0')
    form.birth_date = `${y}-${mm}-${dd}`
  } else {
    form.birth_date = ''
  }
})

function parseBirthDate(dateStr) {
  if (!dateStr) { birthYear.value = ''; birthMonth.value = ''; birthDay.value = ''; return }
  const [y, m, d] = dateStr.split('-')
  birthYear.value = parseInt(y, 10) || ''
  birthMonth.value = parseInt(m, 10) || ''
  birthDay.value = parseInt(d, 10) || ''
}

const participationRules = [
  {
    title: 'Индивидуальное участие',
    desc: 'Каждый участник должен проходить олимпиаду самостоятельно. Совместное выполнение не допускается.',
  },
  {
    title: 'Ограничение по времени',
    desc: 'Время на выполнение заданий ограничено и отображается во время прохождения олимпиады.',
  },
  {
    title: 'Академическая честность',
    desc: 'Во время олимпиады нельзя использовать помощь других людей, учебники или сторонние подсказки.',
  },
  {
    title: 'Отправка ответов',
    desc: 'Все ответы должны быть отправлены через платформу до окончания отведённого времени.',
  },
  {
    title: 'Технические проблемы',
    desc: 'Если возникают технические сложности, свяжитесь с поддержкой как можно быстрее.',
  },
  {
    title: 'Аннулирование результата',
    desc: 'При нарушении правил результат может быть аннулирован без повторного прохождения.',
  },
]

const formatPrice = (price) => `${new Intl.NumberFormat('ru-RU').format(Number(price) || 0)} ₸`

const form = reactive({
  first_name: '',
  last_name: '',
  birth_date: '',
  grade: '',
  school: '',
  city: '',
  language: 'ru',
  parent_name: '',
  parent_phone: '',
  parent_email: '',
})

let clockTimer = null
let paymentPollTimer = null

const canProceed = computed(() =>
  selectedSubject.value &&
  form.parent_name.trim() &&
  form.parent_phone.trim() &&
  form.parent_email.trim() &&
  (
    selectedChildId.value ||
    (form.first_name.trim() && form.last_name.trim() && Number(form.grade) >= 3)
  )
)

const requestStatusLabel = computed(() => {
  if (requestStatus.value === 'pending') return 'Участие требует проверки'
  if (requestStatus.value === 'rejected') return 'Участие отклонено'

  if (requestStatus.value === 'approved' && paymentStatus.value === 'paid') {
    return 'Оплата подтверждена, доступ открыт'
  }

  if (requestStatus.value === 'approved' && reconciliationStatus.value === 'reported') {
    return 'Платёж отмечен, идёт автосверка'
  }

  if (requestStatus.value === 'approved' && reconciliationStatus.value === 'needs_review') {
    return 'Автосверка не завершилась, нужна проверка'
  }

  if (requestStatus.value === 'approved') {
    return 'Ожидаем оплату'
  }

  return 'Участие ещё не оформлено'
})

const requestTone = computed(() => {
  if (requestStatus.value === 'rejected') return 'danger'
  if (requestStatus.value === 'pending') return 'warning'
  if (requestStatus.value === 'approved' && reconciliationStatus.value === 'needs_review') return 'warning'
  if (requestStatus.value === 'approved' && paymentStatus.value === 'paid') return 'success'
  if (requestStatus.value === 'approved' && reconciliationStatus.value === 'reported') return 'info'
  if (requestStatus.value === 'approved') return 'warning'
  return 'neutral'
})

const showKaspiButton = computed(() =>
  Boolean(paymentUrl.value) &&
  requestStatus.value !== 'rejected' &&
  paymentStatus.value !== 'paid'
)

const showReportPaymentButton = computed(() =>
  Boolean(paymentReference.value) &&
  requestStatus.value === 'approved' &&
  paymentStatus.value !== 'paid'
)

const canStartOlympiad = computed(() =>
  requestStatus.value === 'approved' && paymentStatus.value === 'paid'
)

const isFree = computed(() => Number(selectedSubject.value?.price) === 0)

const reconciliationDescription = computed(() => {
  if (paymentStatus.value === 'paid') {
    return 'Оплата подтверждена, доступ к олимпиаде уже открыт.'
  }

  if (reconciliationStatus.value === 'reported') {
    return 'Платёж отмечен, идёт автосверка. Обычно подтверждение появляется автоматически.'
  }

  if (reconciliationStatus.value === 'needs_review') {
    return 'Автосверка не завершилась, нужна проверка администратора.'
  }

  return 'Ожидаем оплату. После перевода нажмите «Я оплатил», чтобы запустить автосверку.'
})

const registrationDeadline = computed(() => {
  const raw = selectedSubject.value?.start_date
  if (!raw) return null
  const parsed = new Date(raw)
  return Number.isNaN(parsed.getTime()) ? null : parsed
})

const registrationDeadlineLabel = computed(() =>
  registrationDeadline.value
    ? registrationDeadline.value.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' })
    : ''
)

const countdownMs = computed(() => {
  if (!registrationDeadline.value) return null
  return registrationDeadline.value.getTime() - nowTs.value
})

const countdownStatusLabel = computed(() => {
  if (!registrationDeadline.value) return ''
  if ((countdownMs.value ?? 0) <= 0) return 'Регистрация обновляется'
  return 'До закрытия регистрации'
})

const countdownParts = computed(() => {
  if ((countdownMs.value ?? 0) <= 0) return ''
  const totalHours = Math.floor((countdownMs.value || 0) / 3600000)
  const days = Math.floor(totalHours / 24)
  const hours = totalHours % 24
  if (days > 0) return `${days} дн. ${hours} ч.`
  return `${Math.max(hours, 1)} ч.`
})

const requestHint = computed(() => {
  if (requestStatus.value === 'pending') {
    return 'Данные сохранены, но участие временно требует дополнительной проверки администратором.'
  }

  if (requestStatus.value === 'approved' && paymentStatus.value === 'paid') {
    return 'Оплата подтверждена. Доступ к олимпиаде открыт, можно начинать.'
  }

  if (requestStatus.value === 'approved' && reconciliationStatus.value === 'reported') {
    return 'Платёж отмечен пользователем. Сейчас система выполняет автосверку.'
  }

  if (requestStatus.value === 'approved' && reconciliationStatus.value === 'needs_review') {
    return 'Автосверка не смогла подтвердить перевод автоматически. Заявка ждёт ручной проверки.'
  }

  if (requestStatus.value === 'approved') {
    return 'Участие оформлено. Перейдите к оплате, затем нажмите «Я оплатил».'
  }

  if (requestStatus.value === 'rejected') {
    return 'Участие отклонено. Проверьте данные участника или свяжитесь с поддержкой.'
  }

  return 'После сохранения данных вы сразу увидите ссылку на оплату и текущий статус участия.'
})

const hydrateParentDefaults = () => {
  form.parent_name = userStore.user?.name || ''
  form.parent_phone = userStore.user?.phone || ''
  form.parent_email = userStore.user?.email || ''
  form.school = userStore.user?.school || ''
  form.city = userStore.user?.city || ''
}

const scrollToRegistration = async () => {
  await nextTick()

  const target = registrationBoxRef.value
  if (!target) return

  const headerOffset = document.querySelector('.header')?.offsetHeight ?? 72
  const top = target.getBoundingClientRect().top + window.scrollY - headerOffset - 16
  window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' })
}

const applyChildSelection = () => {
  const child = userStore.children.find((item) => String(item.id) === selectedChildId.value)
  if (!child) return

  userStore.setSelectedChild(child.id)
  form.first_name = child.first_name
  form.last_name = child.last_name
  form.birth_date = child.birth_date || ''
  form.grade = child.grade || ''
  form.school = child.school || userStore.user?.school || ''
  form.city = child.city || userStore.user?.city || ''
  form.language = child.language_preference || 'ru'
  parseBirthDate(child.birth_date || '')
  scrollToRegistration()
}

const fetchSubjects = async () => {
  const { data } = await api.get('/subjects')
  subjects.value = Array.isArray(data) ? data : []
}

const syncSubjectFromQuery = async () => {
  const subjectId = route.query.subject ? String(route.query.subject) : ''
  if (!subjectId || !subjects.value.length) return

  const matched = subjects.value.find((item) => item.id === subjectId)
  if (!matched) return

  await selectSubject(matched)
}

const applyRequestStatusPayload = (data = {}) => {
  requestStatus.value = data.status || ''
  paymentStatus.value = data.payment_status || ''
  paymentUrl.value = data.payment_url || ''
  paymentReference.value = data.payment_reference || data.request?.id || ''
  paymentComment.value = data.payment_comment || ''
  reconciliationStatus.value = data.reconciliation_status || 'awaiting_payment'
}

const clearPaymentPolling = () => {
  if (paymentPollTimer) {
    window.clearInterval(paymentPollTimer)
    paymentPollTimer = null
  }
}

const syncPaymentPolling = () => {
  clearPaymentPolling()

  if (
    !userStore.isAuthenticated ||
    !selectedSubject.value ||
    !requestStatus.value ||
    paymentStatus.value === 'paid' ||
    requestStatus.value === 'rejected'
  ) {
    return
  }

  paymentPollTimer = window.setInterval(() => {
    fetchRequestStatus()
  }, 15000)
}

const reportPayment = async () => {
  if (!paymentReference.value || reportingPayment.value) return

  reportingPayment.value = true
  paymentReportMessage.value = ''

  try {
    const { data } = await api.post(`/olympiad/request/${paymentReference.value}/payment-report`, {
      paid_at: new Date().toISOString(),
    })

    applyRequestStatusPayload({
      ...data,
      payment_status: data.payment_status || data.request?.payment_status,
      payment_reference: paymentReference.value,
      payment_comment: paymentComment.value,
    })
    paymentReportMessage.value = data.message || 'Платёж отмечен, идёт автосверка.'
    syncPaymentPolling()
  } catch (error) {
    paymentReportMessage.value = getErrorMessage(error, 'Не удалось отметить платёж. Попробуйте ещё раз.')
  } finally {
    reportingPayment.value = false
  }
}

const fetchRequestStatus = async () => {
  if (!selectedSubject.value || !userStore.isAuthenticated) {
    applyRequestStatusPayload()
    clearPaymentPolling()
    return
  }

  const params = {
    subject_id: selectedSubject.value.id,
    ...(selectedChildId.value ? { child_profile_id: selectedChildId.value } : {}),
  }

  try {
    const { data } = await api.get('/olympiad/request/status', { params })
    applyRequestStatusPayload(data)
    syncPaymentPolling()
  } catch (error) {
    applyRequestStatusPayload()
    clearPaymentPolling()
    console.warn('Unable to fetch olympiad request status', error)
  }
}

const selectSubject = async (subject) => {
  selectedSubject.value = subject
  paymentReportMessage.value = ''
  applyRequestStatusPayload()
  await fetchRequestStatus()
  await scrollToRegistration()
}

const startOlympiad = async () => {
  if (!userStore.isAuthenticated) {
    router.push('/login')
    return
  }

  submitting.value = true
  submitError.value = ''

  try {
    const payload = {
      subject_id: selectedSubject.value.id,
      child_profile_id: selectedChildId.value || undefined,
      first_name: form.first_name.trim(),
      last_name: form.last_name.trim(),
      birth_date: form.birth_date || undefined,
      grade: Number(form.grade) || undefined,
      language: form.language,
      parent_name: form.parent_name.trim(),
      parent_phone: form.parent_phone.trim(),
      parent_email: form.parent_email.trim(),
    }

    const { data } = await api.post('/olympiad/request', payload)
    applyRequestStatusPayload({
      ...data,
      status: data.request?.status || 'approved',
      payment_status: data.request?.payment_status || 'pending',
      payment_reference: data.payment_reference || data.request?.id,
      payment_comment: data.payment_comment,
      reconciliation_status: data.request?.reconciliation_status || 'awaiting_payment',
    })
    paymentReportMessage.value = ''
    syncPaymentPolling()

    await userStore.fetchUser()
    await userStore.fetchNotifications(10)

    if (data.request?.child_profile_id) {
      userStore.setSelectedChild(data.request.child_profile_id)
      selectedChildId.value = String(data.request.child_profile_id)
    }

    if (data.redirect_to_quiz) {
      router.push({
        path: `/quiz/${selectedSubject.value.id}`,
        query: selectedChildId.value ? { childId: selectedChildId.value } : {},
      })
      return
    }

    router.push({
      path: '/request-success',
      query: {
        subject: selectedSubject.value.id,
        subjectName: selectedSubject.value.name,
        request: data.payment_reference || data.request?.id || '',
        ...(selectedChildId.value ? { child: selectedChildId.value } : {}),
      },
    })
  } catch (error) {
    submitError.value = getErrorMessage(error, 'Не удалось оформить участие.')
  } finally {
    submitting.value = false
  }
}

const goToQuiz = () => {
  if (selectedChildId.value) {
    userStore.setSelectedChild(selectedChildId.value)
  }

  router.push({
    path: `/quiz/${selectedSubject.value.id}`,
    query: selectedChildId.value ? { childId: selectedChildId.value } : {},
  })
}

const getErrorMessage = (error, fallback) =>
  error?.response?.data?.message || error?.message || fallback

const initializePage = async () => {
  pageLoading.value = true
  pageError.value = ''

  try {
    await userStore.fetchUser()
    hydrateParentDefaults()
    applySeo(getStaticSeoForPath('/subject'))
    rulesExpanded.value = route.query.openRules === '1'
    selectedChildId.value = userStore.selectedChildId ? String(userStore.selectedChildId) : ''

    if (selectedChildId.value) {
      applyChildSelection()
    }

    await fetchSubjects()
    await syncSubjectFromQuery()
  } catch (error) {
    pageError.value = getErrorMessage(error, 'Попробуйте обновить страницу или зайти снова чуть позже.')
  } finally {
    pageLoading.value = false
  }
}

onMounted(async () => {
  clockTimer = window.setInterval(() => {
    nowTs.value = Date.now()
  }, 60000)

  await initializePage()
})

onBeforeUnmount(() => {
  if (clockTimer) {
    window.clearInterval(clockTimer)
    clockTimer = null
  }

  clearPaymentPolling()
})

watch(() => route.query.subject, async () => {
  await syncSubjectFromQuery()
})

watch(() => route.query.openRules, (value) => {
  rulesExpanded.value = value === '1'
})

watch(selectedChildId, async () => {
  if (!selectedSubject.value) return
  await fetchRequestStatus()
})
</script>

<style scoped>
.subject-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 28px) 0 96px;
  background:
    radial-gradient(800px 400px at 100% 0%, color-mix(in srgb, var(--brand) 9%, transparent), transparent 70%),
    var(--bg);
}

.container {
  display: grid;
  gap: 24px;
}

/* ---- Шапка ---- */
.page-header {
  display: grid;
  justify-items: start;
  gap: 12px;
  max-width: 760px;
}

.breadcrumbs {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
  color: var(--text-tertiary);
  font-size: 14px;
}

.breadcrumbs a {
  color: var(--text-secondary);
  text-decoration: none;
}

.breadcrumbs a:hover { color: var(--brand-ink); }
.breadcrumbs [aria-current] { color: var(--text); font-weight: 500; }

.page-title { font-size: clamp(26px, 3.6vw, 42px); }

.page-subtitle {
  color: var(--text-secondary);
  font-size: 18px;
}

/* ---- Карточки предметов ---- */
.subjects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
  gap: 16px;
}

.subject-card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 14px 14px 18px;
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 2px solid var(--border);
  cursor: pointer;
  transition: transform var(--dur-slow) var(--ease-out), box-shadow var(--dur-slow) ease, border-color var(--dur) ease;
}

@media (hover: hover) and (pointer: fine) {
  .subject-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
    border-color: color-mix(in srgb, var(--brand) 30%, var(--border));
  }
  .subject-card:hover .subject-card__img-wrap img { transform: scale(1.05) rotate(-1deg); }
}

.subject-card:focus-visible {
  outline: none;
  box-shadow: var(--focus-ring);
}

.subject-card.selected {
  border-color: var(--brand);
  box-shadow: 0 12px 30px rgba(43, 91, 245, 0.18);
}

.subject-card__picked {
  position: absolute;
  top: 22px;
  right: 22px;
  z-index: 1;
  width: 32px;
  height: 32px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: var(--brand);
  color: #ffffff;
  box-shadow: var(--shadow-brand);
}

.subject-card__img-wrap {
  height: 168px;
  display: grid;
  place-items: center;
  overflow: hidden;
  border-radius: var(--radius-lg);
  background: var(--brand-soft);
}

.tone-1 .subject-card__img-wrap { background: var(--sun-soft); }
.tone-2 .subject-card__img-wrap { background: var(--tone-violet-soft); }
.tone-3 .subject-card__img-wrap { background: var(--tone-teal-soft); }

.subject-card__img-wrap img {
  max-width: 78%;
  max-height: 140px;
  object-fit: contain;
  transition: transform 500ms var(--ease-out);
}

.subject-card__body {
  display: grid;
  gap: 6px;
  padding: 0 6px;
}

.subject-card__name {
  font-family: var(--font-sans);
  font-size: 20px;
  font-weight: 700;
  letter-spacing: -0.015em;
}

.subject-card__desc {
  color: var(--text-secondary);
  font-size: 15px;
  line-height: 1.55;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.subject-card__meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-top: auto;
  padding: 0 6px;
}

.subject-card__price {
  font-size: 15px;
  font-weight: 700;
}

.subject-card__link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  align-self: flex-start;
  margin: 0 6px;
  color: var(--brand-ink);
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
}

.subject-card__link:hover { text-decoration: underline; text-underline-offset: 3px; }

/* ---- Блок оформления ---- */
.step-box {
  display: grid;
  gap: 18px;
  padding: clamp(20px, 3vw, 32px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-md);
  scroll-margin-top: calc(var(--header-h) + 16px);
}

.step-box__header {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.step-pill {
  display: inline-block;
  margin-bottom: 8px;
  padding: 4px 10px;
  border-radius: var(--radius-xs);
  background: var(--brand-soft);
  color: var(--brand-ink);
  font-size: 13px;
  font-weight: 600;
}

.step-box__header h2 { font-size: clamp(22px, 2.6vw, 30px); }

.chosen,
.deadline-copy {
  margin-top: 6px;
  color: var(--text-secondary);
  font-size: 16px;
}

.chosen strong { color: var(--text); }

.deadline-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px 18px;
  border-radius: var(--radius-md);
  background: var(--sun-soft);
}

.deadline-banner__eyebrow {
  color: var(--sun-ink);
  font-size: 13px;
  font-weight: 600;
}

.deadline-banner strong { font-size: 16px; }

.deadline-banner__value {
  font-family: var(--font-display);
  font-size: 20px;
  font-weight: 700;
  white-space: nowrap;
}

/* ---- Правила ---- */
.rules-card {
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  overflow: hidden;
}

.rules-card__toggle {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  border: 0;
  background: var(--bg);
  text-align: left;
}

.rules-card__toggle:hover { background: var(--brand-softer); }

.rules-card__icon {
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 13px;
  background: var(--card);
  color: var(--brand);
}

.rules-card__text {
  flex: 1;
  display: grid;
  gap: 2px;
}

.rules-card__eyebrow {
  color: var(--brand-ink);
  font-size: 13px;
  font-weight: 600;
}

.rules-card__title {
  font-size: 16px;
  font-weight: 700;
}

.rules-card__summary {
  color: var(--text-secondary);
  font-size: 14px;
}

.rules-card__action {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

.rules-card__action svg { transition: transform var(--dur) var(--ease-out); }
.rules-card__action svg.is-open { transform: rotate(180deg); }

.rules-card__body {
  display: grid;
  gap: 14px;
  padding: 18px;
  border-top: 1px solid var(--border);
}

.rules-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  list-style: none;
}

.rule-item {
  display: flex;
  gap: 12px;
  padding: 14px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.rule-item__index {
  font-family: var(--font-display);
  color: var(--brand);
  font-size: 15px;
  font-weight: 700;
}

.rule-item h4 {
  margin-bottom: 4px;
  font-size: 15.5px;
}

.rule-item p {
  color: var(--text-secondary);
  font-size: 14px;
  line-height: 1.55;
}

/* ---- Доступ открыт ---- */
.start-hero {
  display: grid;
  justify-items: center;
  gap: 10px;
  padding: clamp(24px, 4vw, 40px);
  border-radius: var(--radius-xl);
  background:
    radial-gradient(400px 220px at 50% 0%, rgba(255, 255, 255, 0.2), transparent 70%),
    linear-gradient(160deg, #18b874 0%, #0f9a5f 100%);
  color: #ffffff;
  text-align: center;
}

.start-hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: var(--radius-pill);
  background: rgba(255, 255, 255, 0.2);
  font-size: 14px;
  font-weight: 600;
}

.start-hero__title {
  font-family: var(--font-display);
  font-size: clamp(22px, 3vw, 30px);
}

.start-hero__hint {
  max-width: 44ch;
  color: rgba(255, 255, 255, 0.86);
  font-size: 16px;
}

.start-hero__btn {
  margin-top: 8px;
  min-height: 58px;
  padding: 14px 32px;
  border: 0;
  border-radius: var(--radius-md);
  background: #ffffff;
  color: #0a6e44;
  font-size: 18px;
  font-weight: 700;
  box-shadow: 0 12px 28px rgba(5, 60, 35, 0.3);
}

.start-hero__btn:hover { transform: translateY(-2px); }

/* ---- Шаги ---- */
.flow-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  list-style: none;
  counter-reset: step;
}

.flow-step {
  display: flex;
  gap: 12px;
  padding: 16px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.flow-step__index {
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 11px;
  background: var(--brand);
  color: #ffffff;
  font-weight: 700;
}

.flow-step:nth-child(2) .flow-step__index { background: var(--sun); color: #1f1600; }
.flow-step:nth-child(3) .flow-step__index { background: var(--success); }

.flow-step h3 {
  margin-bottom: 4px;
  font-size: 15.5px;
}

.flow-step p {
  color: var(--text-secondary);
  font-size: 14px;
  line-height: 1.55;
}

/* ---- Форма ---- */
.form-card {
  display: grid;
  gap: 14px;
  margin: 0;
  padding: 20px;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  min-width: 0;
}

.form-section-label {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 0 6px;
  margin-left: -6px;
  font-size: 16px;
  font-weight: 700;
}

.form-section-label__icon {
  width: 30px;
  height: 30px;
  display: grid;
  place-items: center;
  border-radius: 10px;
}

.tone-child { background: var(--brand-soft); color: var(--brand); }
.tone-parent { background: var(--sun-soft); color: #c98a00; }

.fields-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.date-selects {
  display: grid;
  grid-template-columns: 0.8fr 1.4fr 1fr;
  gap: 8px;
}

.date-selects .ds-input { padding-left: 12px; padding-right: 32px; background-position: right 12px center; }

.visually-hidden-select {
  position: absolute;
  width: 1px;
  height: 1px;
  opacity: 0;
  pointer-events: none;
}

.single-action {
  display: grid;
  justify-items: start;
  gap: 10px;
}

.start-btn { min-width: 280px; }

/* ---- Оплата ---- */
.payment-followup {
  display: grid;
  gap: 14px;
  padding: 18px;
  border-radius: var(--radius-lg);
  background: var(--bg);
}

.payment-meta {
  display: grid;
  grid-template-columns: 1fr 1fr 1.4fr;
  gap: 10px;
  margin: 0;
}

.payment-meta__item {
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  background: var(--card);
}

.payment-meta__item dt {
  margin-bottom: 2px;
  color: var(--text-tertiary);
  font-size: 13px;
}

.payment-meta__item dd {
  margin: 0;
  font-size: 14.5px;
  font-weight: 600;
  overflow-wrap: anywhere;
}

.payment-followup__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
}

.payment-action__hint {
  color: var(--text-secondary);
  font-size: 14px;
}

@media (max-width: 900px) {
  .flow-grid { grid-template-columns: 1fr; }
  .rules-list { grid-template-columns: 1fr; }
  .payment-meta { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
  .subject-page { padding-top: calc(var(--header-h) + 16px); }
  .fields-row { grid-template-columns: 1fr; }
  .form-card { padding: 16px; }
  .rules-card__toggle { flex-wrap: wrap; }
  .rules-card__action { margin-left: 56px; }
  .single-action { justify-items: stretch; }
  .start-btn { min-width: 0; width: 100%; }
  .deadline-banner { flex-direction: column; align-items: flex-start; }
}
</style>
