<template>
  <div class="subject-page">
    <div class="container">
      <div class="page-header">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
          <RouterLink to="/">Главная</RouterLink>
          <span>/</span>
          <span>Предметы</span>
          <span>/</span>
          <span>Оформление участия</span>
        </nav>
        <div class="page-badge">Предметы</div>
        <h1 class="page-title">Выберите олимпиаду и сразу ознакомьтесь с условиями участия</h1>
        <p class="page-subtitle">
          Путь стал проще: выберите предмет, укажите участника, сохраните данные и оплатите участие.
          После подтверждения оплаты в админке доступ к олимпиаде откроется автоматически.
        </p>
        <div class="funnel-progress" aria-label="Прогресс оформления">
          <div class="funnel-progress__top">
            <strong>Шаг 1 из 3</strong>
            <span>Выбор предмета и участника</span>
          </div>
          <div class="funnel-progress__track">
            <div class="funnel-progress__fill" style="width: 33.333%;"></div>
          </div>
        </div>
      </div>

      <StatePanel
        v-if="pageLoading"
        tone="info"
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
          <button type="button" class="step-link" @click="initializePage">Повторить загрузку</button>
        </template>
      </StatePanel>

      <template v-else>
        <div class="subjects-grid">
          <div
            v-for="subject in subjects"
            :key="subject.id"
            class="subject-card"
            :class="{ selected: selectedSubject?.id === subject.id }"
            @click="selectSubject(subject)"
          >
            <div class="subject-card__img-wrap">
              <img :src="subject.image" :alt="subject.name" />
            </div>
            <h2 class="subject-card__name">{{ subject.name }}</h2>
            <p class="subject-card__desc">{{ subject.description }}</p>
            <p class="subject-card__price">Участие: {{ formatPrice(subject.price) }}</p>
            <CountdownBadge :target="subject.start_date" label="До старта" />
            <RouterLink
              class="subject-card__link"
              :to="`/subjects/${subject.id}`"
              @click.stop
            >
              Страница предмета
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

        <div v-if="selectedSubject" class="step-box">
          <div class="step-box__header">
            <div>
              <p class="page-badge soft">Шаг 1 из 3</p>
              <h2>Оформление участия</h2>
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
            <span v-if="countdownParts">{{ countdownParts }}</span>
          </div>

          <section class="rules-card">
            <button
              type="button"
              class="rules-card__toggle"
              :aria-expanded="String(rulesExpanded)"
              @click="rulesExpanded = !rulesExpanded"
            >
              <div>
                <p class="rules-card__eyebrow">Правила участия</p>
                <h3>Правила теперь доступны прямо на странице оформления</h3>
                <p class="rules-card__summary">Ознакомьтесь с условиями без перехода на отдельный экран.</p>
              </div>
              <span class="rules-card__action">{{ rulesExpanded ? 'Скрыть' : 'Открыть' }}</span>
            </button>

            <div v-if="rulesExpanded" class="rules-card__body">
              <div class="rules-card__notice">
                Нарушение правил может привести к аннулированию результатов. Участие в олимпиаде означает согласие с условиями платформы.
              </div>

              <div class="rules-list">
                <article v-for="(rule, index) in participationRules" :key="rule.title" class="rule-item">
                  <span class="rule-item__index">{{ String(index + 1).padStart(2, '0') }}</span>
                  <div>
                    <h4>{{ rule.title }}</h4>
                    <p>{{ rule.desc }}</p>
                  </div>
                </article>
              </div>
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
              <RouterLink to="/login" class="step-link">Войти</RouterLink>
              <RouterLink to="/register" class="step-link secondary">Регистрация</RouterLink>
            </template>
          </StatePanel>

          <template v-else>
            <div class="flow-grid">
              <article class="flow-step">
                <span class="flow-step__index">1</span>
                <div>
                  <h3>Выберите участника</h3>
                  <p>Можно использовать уже созданный профиль ребёнка или заполнить форму ниже для нового участника.</p>
                </div>
              </article>
              <article class="flow-step">
                <span class="flow-step__index">2</span>
                <div>
                  <h3>Сохраните данные и оплатите</h3>
                  <p>Укажите язык олимпиады и контакты родителя. После сохранения откроется ссылка на оплату Kaspi.</p>
                </div>
              </article>
              <article class="flow-step">
                <span class="flow-step__index">3</span>
                <div>
                  <h3>Дождитесь подтверждения оплаты</h3>
                  <p>После оплаты нажмите «Я оплатил». Дальше статус автосверки появится прямо на этой странице.</p>
                </div>
              </article>
            </div>

            <div class="field">
              <label>Ребёнок</label>
              <select v-model="selectedChildId" class="step-input" @change="applyChildSelection">
                <option value="">Создать или обновить профиль ребёнка из формы ниже</option>
                <option v-for="child in userStore.children" :key="child.id" :value="String(child.id)">
                  {{ child.full_name }} · {{ child.grade || 'без класса' }}
                </option>
              </select>
              <small v-if="!userStore.children.length" class="helper">
                У вас пока нет профилей детей. Заполните форму ниже, и профиль создастся автоматически вместе с участием.
              </small>
            </div>

            <div class="form-section-label">Данные ребёнка</div>

            <div class="fields-row">
              <div class="field">
                <label>Имя</label>
                <input v-model="form.first_name" placeholder="Введите имя" class="step-input" />
              </div>
              <div class="field">
                <label>Фамилия</label>
                <input v-model="form.last_name" placeholder="Введите фамилию" class="step-input" />
              </div>
            </div>

            <div class="fields-row">
              <div class="field">
                <label>Дата рождения</label>
                <input v-model="form.birth_date" type="date" class="step-input" />
              </div>
              <div class="field">
                <label>Класс</label>
                <select v-model.number="form.grade" class="step-input">
                  <option disabled value="">Выберите класс</option>
                  <option v-for="item in gradeOptions" :key="item" :value="item">{{ item }} класс</option>
                </select>
              </div>
            </div>

            <div class="fields-row">
              <div class="field">
                <label>Школа</label>
                <input v-model="form.school" placeholder="Название школы" class="step-input" />
              </div>
              <div class="field">
                <label>Город</label>
                <input v-model="form.city" placeholder="Город" class="step-input" />
              </div>
            </div>

            <div class="field">
              <label>Язык олимпиады</label>
              <select v-model="form.language" class="step-input">
                <option value="ru">Русский</option>
                <option value="kk">Қазақша</option>
                <option value="en">English</option>
              </select>
            </div>

            <div class="divider"><span>Данные родителя</span></div>

            <div class="fields-row">
              <div class="field">
                <label>ФИО родителя</label>
                <input v-model="form.parent_name" placeholder="Полное имя" class="step-input" />
              </div>
              <div class="field">
                <label>Телефон</label>
                <input v-model="form.parent_phone" placeholder="+7 (777) 777-77-77" class="step-input" />
              </div>
            </div>

            <div class="field">
              <label>Email</label>
              <input v-model="form.parent_email" placeholder="email@mail.ru" class="step-input" />
            </div>

            <div class="single-action">
              <button :disabled="!canProceed || submitting" class="start-btn" @click="startOlympiad">
                {{ submitting ? 'Сохраняем...' : 'Сохранить и перейти к оплате' }}
              </button>
              <p v-if="submitError" class="submit-error">{{ submitError }}</p>
            </div>

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
                <div v-if="showKaspiButton" class="payment-action">
                  <a :href="paymentUrl" target="_blank" rel="noopener" class="step-link">Оплатить через Kaspi</a>
                  <span class="payment-action__hint">Оплата открывается в Kaspi</span>
                </div>
                <button v-if="canStartOlympiad" class="step-link secondary" @click="goToQuiz">Начать олимпиаду</button>
              </template>
            </StatePanel>

            <div
              v-if="requestStatus && (paymentReference || paymentComment || showReportPaymentButton || paymentReportMessage)"
              class="payment-followup"
            >
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
                  <span>Автосверка</span>
                  <strong>{{ reconciliationDescription }}</strong>
                </div>
              </div>

              <div v-if="showReportPaymentButton" class="payment-followup__actions">
                <button
                  type="button"
                  class="step-link secondary"
                  :disabled="reportingPayment"
                  @click="reportPayment"
                >
                  {{ reportingPayment ? 'Отмечаем оплату...' : 'Я оплатил' }}
                </button>
                <span class="payment-action__hint">{{ reconciliationDescription }}</span>
              </div>

              <p v-if="paymentReportMessage" class="payment-feedback">{{ paymentReportMessage }}</p>
            </div>
          </template>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'
import StatePanel from '../components/StatePanel.vue'
import StatusBadge from '../components/StatusBadge.vue'
import CountdownBadge from '../components/CountdownBadge.vue'
import KaspiPaymentAssist from '../components/KaspiPaymentAssist.vue'
import { applySeo, getStaticSeoForPath } from '../js/composables/useSeo'

const router = useRouter()
const route = useRoute()
const userStore = useUserStore()

const subjects = ref([])
const selectedSubject = ref(null)
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
    title: 'Честное выполнение',
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
* { box-sizing: border-box; }

.subject-page { min-height: 100dvh; background: var(--bg); padding: 100px 28px 70px; }
.container { max-width: 1100px; margin: 0 auto; display: grid; gap: 28px; }

/* Left-aligned header — not centered (DESIGN_VARIANCE=8) */
.page-header { display: grid; gap: 12px; justify-items: start; }
.breadcrumbs { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: flex-start; font-size: 13px; color: var(--text-secondary); }
.breadcrumbs a { color: var(--info); text-decoration: none; font-weight: 700; }
.breadcrumbs a:hover { text-decoration: underline; }

.page-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; letter-spacing: 0.09em; text-transform: uppercase; color: #92660a; background: var(--accent-soft); padding: 5px 13px; border-radius: 999px; border: 1px solid rgba(245,200,66,0.3); margin-bottom: 6px; }
.page-badge.soft { margin-bottom: 6px; }

.page-title { font-size: clamp(26px, 3.5vw, 36px); font-weight: 800; color: var(--text); margin: 0; letter-spacing: -0.025em; line-height: 1.15; max-width: 680px; }
.page-subtitle { font-size: 15px; color: var(--text-secondary); margin: 0; line-height: 1.7; max-width: 620px; }

.funnel-progress { width: min(100%, 480px); padding: 14px 18px; border-radius: 18px; border: 1.5px solid var(--border); background: var(--card); box-shadow: var(--shadow-card); }
.funnel-progress__top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.funnel-progress__top strong { color: var(--text); font-size: 14px; font-weight: 700; }
.funnel-progress__top span { color: var(--text-secondary); font-size: 13px; }
.funnel-progress__track { width: 100%; height: 7px; border-radius: 999px; background: var(--bg-alt); overflow: hidden; }
.funnel-progress__fill { height: 100%; border-radius: inherit; background: var(--green); transition: width 0.4s ease; }

/* Subject cards */
.subjects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 20px; }
.subject-card { background: var(--card); padding: 22px; border-radius: 22px; cursor: pointer; border: 1.5px solid var(--border); transition: transform 0.22s cubic-bezier(0.23, 1, 0.32, 1), border-color 0.22s ease, box-shadow 0.22s ease; text-align: center; box-shadow: var(--shadow-card); display: grid; gap: 12px; justify-items: center; }
.subject-card:hover { transform: translateY(-3px); border-color: rgba(245,200,66,0.45); box-shadow: 0 12px 32px rgba(0,0,0,0.09); }
.subject-card.selected { border-color: rgba(245,200,66,0.6); box-shadow: 0 0 0 3px rgba(245,200,66,0.15), 0 12px 32px rgba(0,0,0,0.09); transform: translateY(-3px); }
.subject-card:active { transform: scale(0.98) translateY(0); transition-duration: 0.08s; }
.subject-card__img-wrap { width: 76px; height: 76px; background: var(--surface-soft); border-radius: 18px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.subject-card__img-wrap img { width: 56px; height: 56px; object-fit: contain; }
.subject-card__name { font-size: 17px; font-weight: 700; color: var(--text-on-surface); margin: 0; letter-spacing: -0.01em; }
.subject-card__desc { font-size: 13px; color: var(--text-muted-on-surface); line-height: 1.55; margin: 0; }
.subject-card__price { margin: 0; padding: 6px 13px; border-radius: 999px; background: var(--green-soft); color: var(--green-strong); font-size: 12px; font-weight: 800; letter-spacing: 0.02em; border: 1px solid rgba(22,163,74,0.2); }
.subject-card__link { color: var(--accent-strong); font-weight: 700; font-size: 13px; text-decoration: none; }
.subject-card__link:hover { text-decoration: underline; }

/* Registration form box */
.step-box { background: var(--surface); padding: 36px; border-radius: 28px; border: 1px solid var(--surface-border); display: grid; gap: 18px; }
.step-box__header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; }
.step-box__header h2 { color: var(--text-on-surface); margin: 0 0 4px; font-size: 22px; letter-spacing: -0.02em; }
.deadline-copy { margin: 8px 0 0; color: var(--text-secondary); font-size: 14px; }
.deadline-copy strong { color: var(--accent-strong); }
.deadline-banner { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 14px 18px; border-radius: 16px; background: var(--accent-soft); border: 1.5px solid rgba(245,200,66,0.35); color: var(--text); }
.deadline-banner__eyebrow { margin: 0 0 3px; font-size: 10px; font-weight: 800; letter-spacing: 0.09em; text-transform: uppercase; color: #92660a; }
.deadline-banner strong { display: block; font-size: 17px; font-weight: 700; }
.deadline-banner span { display: inline-flex; align-items: center; justify-content: center; min-width: 96px; padding: 9px 14px; border-radius: 999px; background: var(--card); color: #92660a; font-weight: 800; border: 1px solid rgba(245,200,66,0.3); font-size: 14px; }

.rules-card { border: 1px solid var(--surface-border); border-radius: 20px; background: rgba(255,252,244,.86); overflow: hidden; }
.rules-card__toggle { width: 100%; border: 0; background: transparent; padding: 20px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; text-align: left; cursor: pointer; transition: background 0.18s ease; }
.rules-card__toggle:hover { background: rgba(245,200,66,0.05); }
.rules-card__toggle h3 { margin: 4px 0 5px; color: var(--text-on-surface); font-size: 18px; letter-spacing: -0.01em; }
.rules-card__eyebrow { margin: 0; font-size: 10px; font-weight: 800; letter-spacing: 0.09em; text-transform: uppercase; color: var(--accent-strong); }
.rules-card__summary { margin: 0; color: var(--text-secondary); line-height: 1.55; font-size: 14px; }
.rules-card__action { display: inline-flex; align-items: center; justify-content: center; min-width: 88px; padding: 9px 14px; border-radius: 999px; background: rgba(201,171,99,.16); color: var(--accent-strong); font-size: 12px; font-weight: 800; flex-shrink: 0; }
.rules-card__body { padding: 0 22px 22px; display: grid; gap: 14px; }
.rules-card__notice { padding: 13px 15px; border-radius: 14px; background: var(--warning-bg); border: 1px solid rgba(245,158,11,0.25); color: #92400e; line-height: 1.6; font-size: 13px; font-weight: 500; }
.rules-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
.rule-item { display: grid; grid-template-columns: auto 1fr; gap: 12px; align-items: flex-start; padding: 14px; border-radius: 16px; border: 1px solid var(--surface-border); background: rgba(255,255,255,.7); }
.rule-item__index { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 11px; background: var(--accent-soft); color: #92660a; font-size: 12px; font-weight: 800; border: 1px solid rgba(245,200,66,0.28); flex-shrink: 0; }
.rule-item h4 { margin: 0 0 5px; color: var(--text-on-surface); font-size: 14px; font-weight: 700; }
.rule-item p { margin: 0; color: var(--text-secondary); line-height: 1.55; font-size: 13px; }

.chosen { color: var(--text-secondary); margin: 0; font-size: 14px; }
.chosen strong { color: var(--text-on-surface); }

/* Flow steps — asymmetric: first step wider */
.flow-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px; }
.flow-step { padding: 16px; border-radius: 16px; border: 1.5px solid var(--border); background: var(--bg-alt); display: grid; gap: 8px; }
.flow-step__index { width: 32px; height: 32px; border-radius: 10px; background: var(--accent-soft); color: #92660a; display:flex; align-items:center; justify-content:center; font-weight:800; font-size: 13px; border: 1px solid rgba(245,200,66,0.3); }
.flow-step h3 { margin: 0 0 4px; font-size: 15px; letter-spacing: -0.01em; }
.flow-step p { margin: 0; color: var(--text-secondary); line-height: 1.55; font-size: 13px; }

.form-section-label { font-size: 10px; font-weight: 700; letter-spacing: 0.09em; text-transform: uppercase; color: var(--text-secondary); }
.divider span { font-size: 11px; font-weight: 600; color: var(--text-secondary); }
.helper { font-size: 12px; color: var(--text-secondary); }

.fields-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { display: flex; flex-direction: column; gap: 5px; }
.field label { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
.step-input { padding: 12px 15px; border-radius: 11px; border: 1.5px solid var(--border); font-size: 15px; color: var(--text); width: 100%; background: var(--card); transition: border-color 0.18s ease, box-shadow 0.18s ease; }
.step-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(245,200,66,0.14); }

.divider { display: flex; align-items: center; gap: 12px; margin: 2px 0; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--surface-border); }

.single-action { display: grid; }
.start-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 15px 24px; border: none; border-radius: 14px; color: #ffffff; font-size: 15px; font-weight: 700; cursor: pointer; background: var(--green); box-shadow: 0 8px 24px rgba(22,163,74,0.28); transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease; }
.start-btn:hover:not(:disabled) { background: var(--green-hover); transform: translateY(-2px); box-shadow: 0 14px 32px rgba(22,163,74,0.34); }
.start-btn:active:not(:disabled) { transform: scale(0.98); box-shadow: 0 4px 14px rgba(22,163,74,0.2); transition-duration: 0.08s; }
.start-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.submit-error { margin: 8px 0 0; color: #8f3b3b; font-weight: 600; font-size: 14px; }

.payment-followup { display: grid; gap: 12px; padding: 18px; border-radius: 18px; border: 1.5px solid var(--border); background: var(--bg-alt); }
.payment-followup__actions { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }
.payment-meta { display: grid; gap: 9px; }
.payment-meta__item { padding: 13px 15px; border-radius: 14px; border: 1px solid var(--surface-border); background: rgba(255,255,255,.78); }
.payment-meta__item span { display: block; margin-bottom: 5px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-secondary); }
.payment-meta__item strong { color: var(--text-on-surface); word-break: break-word; font-size: 14px; }
.payment-feedback { margin: 0; font-size: 14px; color: var(--text-secondary); }
.payment-action { display: none; }
.payment-action__hint { font-size: 12px; font-weight: 600; color: var(--text-secondary); }

.step-link { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:11px 20px; border-radius:13px; text-decoration:none; border:none; font-weight:700; font-size: 14px; cursor:pointer; background: var(--green); color:#ffffff; box-shadow: 0 6px 18px rgba(22,163,74,0.24); transition: background 0.2s, transform 0.18s; }
.step-link:hover:not(:disabled) { background: var(--green-hover); transform: translateY(-1px); }
.step-link:active:not(:disabled) { transform: scale(0.98); transition-duration: 0.08s; }
.step-link.secondary { background: var(--card); color: var(--text-secondary); border: 1.5px solid var(--border); box-shadow: none; }
.step-link.secondary:hover:not(:disabled) { border-color: var(--accent); background: var(--accent-soft); color: #92660a; transform: none; }

@media (max-width: 900px) {
  .flow-grid { grid-template-columns: 1fr; }
  .step-box__header { flex-direction: column; }
  .rules-list { grid-template-columns: 1fr; }
  .deadline-banner { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 768px) {
  .subject-page { padding: 88px 16px 56px; }
  .breadcrumbs { font-size: 12px; gap: 6px; }
  .funnel-progress__top { flex-direction: column; align-items: flex-start; gap: 4px; }
  .fields-row { grid-template-columns: 1fr; }
  .step-box { padding: 22px 18px; }
  .rules-card__toggle { padding: 16px 18px; flex-direction: column; align-items: flex-start; gap: 12px; }
  .rules-card__body { padding: 0 18px 18px; }
  .page-title { font-size: 26px; }
}

:global(.dark) .rules-card { background: var(--surface); }
:global(.dark) .rule-item { background: var(--surface-soft); }
:global(.dark) .payment-meta__item { background: var(--surface-soft); }
:global(.dark) .deadline-banner__eyebrow { color: var(--accent-strong); }
:global(.dark) .deadline-banner span { color: var(--accent-strong); }
:global(.dark) .rules-card__notice { color: #fcd34d; }
:global(.dark) .page-badge { color: var(--accent-strong); }
:global(.dark) .flow-step__index { color: var(--accent-strong); }
:global(.dark) .rule-item__index { color: var(--accent-strong); }
:global(.dark) .rules-card__action { color: var(--accent-strong); }
:global(.dark) .rules-card__eyebrow { color: var(--accent-strong); }
:global(.dark) .submit-error { color: var(--danger-soft); }
</style>
