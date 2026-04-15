<template>
  <div class="subject-page">
    <div class="container">
      <div class="page-header">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
          <RouterLink to="/">Главная</RouterLink>
          <span>/</span>
          <span>Предметы</span>
          <span>/</span>
          <span>Регистрация</span>
        </nav>
        <div class="page-badge">Предметы</div>
        <h1 class="page-title">Выберите олимпиаду и сразу переходите к оплате</h1>
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
            <p v-if="registrationDeadlineLabel" class="deadline-copy">До закрытия регистрации: <strong>{{ registrationDeadlineLabel }}</strong></p>
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
                <p>Как только администратор отметит платёж, здесь появится кнопка для старта олимпиады.</p>
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
              <StatusBadge :label="paymentStatusLabel" :tone="paymentTone" />
              <KaspiPaymentAssist
                v-if="showKaspiButton"
                :payment-url="paymentUrl"
                hint="Результат сразу после теста"
                mobile-cta="Оплатить через Kaspi"
                desktop-cta="Открыть ссылку оплаты"
              />
              <div v-if="showKaspiButton" class="payment-action">
                <a :href="paymentUrl" target="_blank" rel="noopener" class="step-link">Оплатить через Kaspi</a>
                <span class="payment-action__hint">Результат сразу после теста</span>
              </div>
              <button v-if="canStartOlympiad" class="step-link secondary" @click="goToQuiz">Начать олимпиаду</button>
            </template>
          </StatePanel>
        </template>
      </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
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
const submitting = ref(false)
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

const requestStatusLabel = computed(() => ({
  pending: 'Участие требует проверки',
  approved: 'Участие оформлено',
  rejected: 'Участие отклонено',
}[requestStatus.value] || 'Участие ещё не оформлено'))

const paymentStatusLabel = computed(() => ({
  pending: 'Оплата ожидается',
  paid: 'Оплата подтверждена',
  failed: 'Оплата не прошла',
}[paymentStatus.value] || 'Оплата ожидается'))

const requestTone = computed(() => ({
  pending: 'warning',
  approved: 'success',
  rejected: 'danger',
}[requestStatus.value] || 'neutral'))

const paymentTone = computed(() => ({
  pending: 'warning',
  paid: 'success',
  failed: 'danger',
}[paymentStatus.value] || 'neutral'))

const showKaspiButton = computed(() =>
  Boolean(paymentUrl.value) &&
  requestStatus.value !== 'rejected' &&
  paymentStatus.value !== 'paid'
)

const canStartOlympiad = computed(() =>
  requestStatus.value === 'approved' && paymentStatus.value === 'paid'
)

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

  if (requestStatus.value === 'approved' && paymentStatus.value === 'pending') {
    return 'Участие оформлено. Оплатите олимпиаду и дождитесь подтверждения платежа администратором.'
  }

  if (requestStatus.value === 'approved' && paymentStatus.value === 'paid') {
    return 'Оплата подтверждена. Доступ к олимпиаде открыт, можно начинать.'
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

const fetchRequestStatus = async () => {
  if (!selectedSubject.value || !userStore.isAuthenticated) return

  const params = {
    subject_id: selectedSubject.value.id,
    ...(selectedChildId.value ? { child_profile_id: selectedChildId.value } : {}),
  }

  try {
    const { data } = await api.get('/olympiad/request/status', { params })
    requestStatus.value = data.status || ''
    paymentStatus.value = data.payment_status || ''
    paymentUrl.value = data.payment_url || ''
  } catch (error) {
    requestStatus.value = ''
    paymentStatus.value = ''
    paymentUrl.value = ''
    console.warn('Unable to fetch olympiad request status', error)
  }
}

const selectSubject = async (subject) => {
  selectedSubject.value = subject
  requestStatus.value = ''
  paymentStatus.value = ''
  paymentUrl.value = ''
  await fetchRequestStatus()
}

const startOlympiad = async () => {
  if (!userStore.isAuthenticated) {
    router.push('/login')
    return
  }

  submitting.value = true

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
    requestStatus.value = data.request?.status || 'approved'
    paymentStatus.value = data.request?.payment_status || 'pending'
    paymentUrl.value = data.payment_url || ''

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
        ...(selectedChildId.value ? { child: selectedChildId.value } : {}),
      },
    })
  } catch (error) {
    window.alert(error.response?.data?.message || 'Не удалось оформить участие.')
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

watch(() => route.query.subject, async () => {
  await syncSubjectFromQuery()
})

watch(() => route.query.openRules, (value) => {
  rulesExpanded.value = value === '1'
})
</script>

<style scoped>
* { box-sizing: border-box; }
.subject-page { min-height: 100vh; background: var(--bg); padding: 100px 28px 70px; position: relative; }
.container { max-width: 1100px; margin: 0 auto; display: grid; gap: 24px; }
.page-header { text-align: center; display: grid; gap: 14px; justify-items: center; }
.breadcrumbs { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: center; font-size: 13px; color: var(--text-secondary); }
.breadcrumbs a { color: var(--info); text-decoration: none; font-weight: 700; }
.breadcrumbs a:hover { color: var(--info-hover); }
.page-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #6c5a2f; background: rgba(208,179,107,0.18); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(208,179,107,0.3); margin-bottom: 18px; }
.page-badge.soft { margin-bottom: 10px; }
.page-title { font-size: 34px; font-weight: 700; color: var(--text-primary); margin: 0 0 12px; }
.page-subtitle { font-size: 16px; color: var(--text-secondary); margin: 0 auto; line-height: 1.7; max-width: 760px; }
.funnel-progress { width: min(100%, 520px); padding: 16px 18px; border-radius: 20px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.82); box-shadow: var(--shadow-soft); text-align: left; }
.funnel-progress__top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 10px; }
.funnel-progress__top strong { color: var(--text); font-size: 15px; }
.funnel-progress__top span { color: var(--text-secondary); font-size: 13px; }
.funnel-progress__track { width: 100%; height: 8px; border-radius: 999px; background: rgba(26,95,168,.12); overflow: hidden; }
.funnel-progress__fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, var(--info) 0%, #4d8bc9 100%); }
.subjects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 24px; }
.subject-card { background: rgba(255,249,239,0.72); padding: 24px; border-radius: 24px; cursor: pointer; border: 2px solid transparent; transition: all 0.3s ease; text-align: center; box-shadow: 0 16px 40px rgba(67, 55, 28, 0.09); backdrop-filter: blur(10px); display: grid; gap: 14px; justify-items: center; }
.subject-card:hover, .subject-card.selected { transform: translateY(-4px); border-color: rgba(208,179,107,0.34); box-shadow: 0 18px 42px rgba(104,79,28,0.14); }
.subject-card__img-wrap { width: 80px; height: 80px; background: var(--surface-soft); border-radius: 20px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.subject-card__img-wrap img { width: 60px; height: 60px; object-fit: contain; }
.subject-card__name { font-size: 18px; font-weight: 600; color: var(--text-on-surface); margin: 0; }
.subject-card__desc { font-size: 14px; color: var(--text-muted-on-surface); line-height: 1.5; margin: 0; }
.subject-card__price { margin: 0; padding: 8px 14px; border-radius: 999px; background: rgba(201,171,99,0.16); color: var(--accent-strong); font-size: 13px; font-weight: 800; letter-spacing: 0.02em; }
.subject-card__link { color: var(--accent-strong); font-weight: 700; text-decoration: none; }
.step-box { background: var(--surface); padding: 36px; border-radius: 28px; border: 1px solid var(--surface-border); display: grid; gap: 16px; }
.step-box__header { display: flex; justify-content: space-between; gap: 16px; align-items: flex-start; }
.step-box__header h2 { color: var(--text-on-surface); margin: 0 0 4px; }
.deadline-copy { margin: 10px 0 0; color: var(--text-secondary); }
.deadline-copy strong { color: var(--accent-strong); }
.deadline-banner { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 18px; border-radius: 18px; background: linear-gradient(135deg, rgba(210,178,97,.14), rgba(255,248,232,.86)); border: 1px solid rgba(201,171,99,.24); color: var(--text-on-surface); }
.deadline-banner__eyebrow { margin: 0 0 4px; font-size: 11px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--accent-strong); }
.deadline-banner strong { display: block; font-size: 18px; }
.deadline-banner span { display: inline-flex; align-items: center; justify-content: center; min-width: 100px; padding: 10px 14px; border-radius: 999px; background: rgba(255,255,255,.72); color: var(--accent-strong); font-weight: 800; }
.rules-card { border: 1px solid var(--surface-border); border-radius: 22px; background: rgba(255,252,244,.86); overflow: hidden; }
.rules-card__toggle { width: 100%; border: 0; background: transparent; padding: 20px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; text-align: left; cursor: pointer; }
.rules-card__toggle h3 { margin: 4px 0 6px; color: var(--text-on-surface); font-size: 20px; }
.rules-card__eyebrow { margin: 0; font-size: 11px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--accent-strong); }
.rules-card__summary { margin: 0; color: var(--text-secondary); line-height: 1.55; }
.rules-card__action { display: inline-flex; align-items: center; justify-content: center; min-width: 96px; padding: 10px 14px; border-radius: 999px; background: rgba(201,171,99,.16); color: var(--accent-strong); font-size: 13px; font-weight: 800; }
.rules-card__body { padding: 0 22px 22px; display: grid; gap: 16px; }
.rules-card__notice { padding: 14px 16px; border-radius: 16px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; line-height: 1.6; font-size: 14px; font-weight: 500; }
.rules-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.rule-item { display: grid; grid-template-columns: auto 1fr; gap: 12px; align-items: flex-start; padding: 16px; border-radius: 18px; border: 1px solid var(--surface-border); background: rgba(255,255,255,.7); }
.rule-item__index { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; border-radius: 12px; background: rgba(201,171,99,.16); color: var(--accent-strong); font-size: 13px; font-weight: 800; }
.rule-item h4 { margin: 0 0 6px; color: var(--text-on-surface); font-size: 15px; }
.rule-item p { margin: 0; color: var(--text-secondary); line-height: 1.55; font-size: 14px; }
.chosen { color: var(--text-secondary); margin: 0; }
.chosen strong { color: var(--text-on-surface); }
.flow-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
.flow-step { padding: 16px; border-radius: 18px; border: 1px solid var(--surface-border); background: rgba(255,252,244,.82); display: grid; gap: 10px; }
.flow-step__index { width: 34px; height: 34px; border-radius: 12px; background: rgba(201,171,99,.16); color: var(--accent-strong); display:flex; align-items:center; justify-content:center; font-weight:800; }
.flow-step h3 { margin: 0 0 6px; }
.flow-step p { margin: 0; color: var(--text-secondary); line-height: 1.55; }
.form-section-label, .divider span, .helper { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; color: var(--text-secondary); }
.fields-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
.step-input { padding: 13px 16px; border-radius: 12px; border: 1.5px solid var(--surface-border); font-size: 15px; color: var(--text-on-surface); width: 100%; background: color-mix(in srgb, var(--bg) 90%, var(--text) 10%); }
.divider { display: flex; align-items: center; gap: 12px; margin: 4px 0; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--surface-border); }
.single-action { display: grid; }
.start-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 15px 24px; border: none; border-radius: 14px; color: #18120a; font-size: 16px; font-weight: 700; cursor: pointer; text-decoration: none; background: linear-gradient(135deg, #d2b261, #bea25a); box-shadow: 0 10px 28px rgba(104,79,28,0.22); }
.start-btn:disabled { opacity: 0.55; cursor: not-allowed; }
.payment-action { display: none; }
.payment-action__hint { font-size: 12px; font-weight: 600; color: var(--text-secondary); }
.step-link { display:inline-flex; align-items:center; justify-content:center; min-height:44px; padding:12px 16px; border-radius:14px; text-decoration:none; border:0; font-weight:700; cursor:pointer; background: linear-gradient(135deg, #d2b261, #bea25a); color:#18120a; }
.step-link.secondary { background: rgba(255,251,243,.86); color: var(--accent-strong); border: 1px solid rgba(201,171,99,.28); }
@media (max-width: 900px) { .flow-grid { grid-template-columns: 1fr; } .step-box__header { flex-direction: column; } .rules-list { grid-template-columns: 1fr; } .deadline-banner { flex-direction: column; align-items: flex-start; } }
@media (max-width: 768px) { .subject-page { padding: 90px 16px 60px; } .breadcrumbs { font-size: 12px; gap: 6px; } .funnel-progress__top { flex-direction: column; align-items: flex-start; } .fields-row { grid-template-columns: 1fr; } .step-box { padding: 24px 18px; } .rules-card__toggle { padding: 18px; flex-direction: column; align-items: flex-start; } .rules-card__body { padding: 0 18px 18px; } }
</style>
