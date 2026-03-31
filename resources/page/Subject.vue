<template>
  <div class="subject-page">
    <div class="container">
      <div class="page-header">
        <div class="page-badge">Предметы</div>
        <h1 class="page-title">Выберите олимпиаду и заполните заявку на участие</h1>
        <p class="page-subtitle">
          Олимпиада открывается только после проверки заявки и подтверждения оплаты.
          Тренировка остаётся бесплатной и доступна отдельно.
        </p>
      </div>

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
          <div class="subject-card__date">{{ formatDate(subject.start_date) }}</div>
        </div>
      </div>

      <div v-if="!subjects.length" class="empty-state">Сейчас нет опубликованных олимпиад.</div>

      <div v-if="selectedSubject" class="step-box">
        <div class="step-box__header">
          <div>
            <h2>Оформление участия</h2>
            <p class="chosen">Предмет: <strong>{{ selectedSubject.name }}</strong></p>
          </div>
        </div>

        <div v-if="userStore.isAuthenticated" class="field">
          <label>Ребёнок</label>
          <select v-model="selectedChildId" class="step-input" @change="applyChildSelection">
            <option value="">Создать или обновить профиль ребёнка из формы ниже</option>
            <option v-for="child in userStore.children" :key="child.id" :value="String(child.id)">
              {{ child.full_name }} · {{ child.grade || 'без класса' }}
            </option>
          </select>
          <small v-if="!userStore.children.length" class="helper">
            У вас пока нет профилей детей. Заполните форму ниже, и профиль будет создан автоматически.
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
            {{ submitting ? 'Сохраняем...' : 'Оформить участие' }}
          </button>
        </div>

        <div v-if="requestStatus" class="status-card">
          <div class="status-line">
            <span>Статус заявки</span>
            <strong>{{ requestStatusLabel }}</strong>
          </div>
          <div class="status-line">
            <span>Статус оплаты</span>
            <strong>{{ paymentStatusLabel }}</strong>
          </div>
          <p class="status-hint">{{ requestHint }}</p>

          <a
            v-if="showKaspiButton"
            :href="paymentUrl"
            target="_blank"
            rel="noopener"
            class="kaspi-btn"
          >
            Оплатить через Kaspi
          </a>

          <button v-if="canStartOlympiad" class="start-btn" @click="goToQuiz">Начать олимпиаду</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'

const router = useRouter()
const userStore = useUserStore()

const subjects = ref([])
const selectedSubject = ref(null)
const selectedChildId = ref('')
const requestStatus = ref('')
const paymentStatus = ref('')
const paymentUrl = ref('')
const submitting = ref(false)
const gradeOptions = [3, 4, 5, 6, 7, 8, 9, 10, 11]

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
  pending: 'Заявка отправлена',
  approved: 'Заявка одобрена',
  rejected: 'Заявка отклонена',
}[requestStatus.value] || 'Заявка не оформлена'))

const paymentStatusLabel = computed(() => ({
  pending: 'Оплата ожидается',
  paid: 'Оплата подтверждена',
  failed: 'Оплата не прошла',
}[paymentStatus.value] || 'Оплата ожидается'))

const showKaspiButton = computed(() =>
  Boolean(paymentUrl.value) &&
  requestStatus.value === 'approved' &&
  paymentStatus.value !== 'paid'
)

const canStartOlympiad = computed(() =>
  requestStatus.value === 'approved' && paymentStatus.value === 'paid'
)

const requestHint = computed(() => {
  if (requestStatus.value === 'pending') {
    return 'Заявка отправлена. Администратор должен проверить данные, после чего вы сможете перейти к оплате.'
  }

  if (requestStatus.value === 'approved' && paymentStatus.value === 'pending') {
    return 'Заявка одобрена. Оплатите участие по ссылке Kaspi, затем дождитесь подтверждения оплаты администратором.'
  }

  if (requestStatus.value === 'approved' && paymentStatus.value === 'paid') {
    return 'Оплата подтверждена. Доступ к олимпиаде открыт.'
  }

  if (requestStatus.value === 'rejected') {
    return 'Заявка отклонена. Если это ошибка, свяжитесь с администратором и обновите данные в форме.'
  }

  return 'После оформления заявки вы увидите здесь дальнейшие шаги по оплате и доступу к олимпиаде.'
})

const formatDate = (date) => {
  if (!date) return ''
  const value = new Date(date)
  return Number.isNaN(value.getTime())
    ? ''
    : value.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

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
  subjects.value = data
}

const fetchRequestStatus = async () => {
  if (!selectedSubject.value || !userStore.isAuthenticated) return

  const params = {
    subject_id: selectedSubject.value.id,
    ...(selectedChildId.value ? { child_profile_id: selectedChildId.value } : {}),
  }

  const { data } = await api.get('/olympiad/request/status', { params })
  requestStatus.value = data.status || ''
  paymentStatus.value = data.payment_status || ''
  paymentUrl.value = data.payment_url || ''
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
    requestStatus.value = data.request?.status || 'pending'
    paymentStatus.value = data.request?.payment_status || 'pending'
    paymentUrl.value = data.payment_url || ''

    await userStore.fetchUser()

    if (data.request?.child_profile_id) {
      userStore.setSelectedChild(data.request.child_profile_id)
      selectedChildId.value = String(data.request.child_profile_id)
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Не удалось оформить участие.')
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

onMounted(async () => {
  await userStore.fetchUser()
  hydrateParentDefaults()
  selectedChildId.value = userStore.selectedChildId ? String(userStore.selectedChildId) : ''
  if (selectedChildId.value) applyChildSelection()
  await fetchSubjects()
})
</script>

<style scoped>
* { box-sizing: border-box; }
.subject-page { min-height: 100vh; background: var(--bg); padding: 100px 28px 70px; position: relative; }
.container { max-width: 1100px; margin: 0 auto; }
.page-header { text-align: center; margin-bottom: 36px; }
.page-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #e11d48; background: rgba(225,29,72,0.15); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(225,29,72,0.35); margin-bottom: 18px; }
.page-title { font-size: 30px; font-weight: 700; color: var(--text-primary); margin: 0 0 12px; }
.page-subtitle { font-size: 16px; color: var(--text-secondary); margin: 0; line-height: 1.6; }
.subjects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 24px; margin-bottom: 20px; }
.subject-card { background: var(--surface); padding: 24px; border-radius: 24px; cursor: pointer; border: 2px solid transparent; transition: all 0.3s ease; text-align: center; }
.subject-card:hover, .subject-card.selected { transform: translateY(-4px); border-color: rgba(225,29,72,0.35); box-shadow: 0 10px 30px rgba(0,0,0,0.15); }
.subject-card__img-wrap { width: 80px; height: 80px; background: var(--surface-soft); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; overflow: hidden; }
.subject-card__img-wrap img { width: 60px; height: 60px; object-fit: contain; }
.subject-card__name { font-size: 18px; font-weight: 600; color: var(--text-on-surface); margin: 0 0 8px; }
.subject-card__desc { font-size: 14px; color: var(--text-muted-on-surface); line-height: 1.5; margin: 0 0 12px; }
.subject-card__date { display: inline-flex; font-size: 12px; font-weight: 600; color: var(--text-muted-on-surface); background: color-mix(in srgb, var(--text) 4%, transparent); padding: 5px 12px; border-radius: 20px; }
.empty-state { max-width: 680px; margin: 0 auto 24px; padding: 18px 20px; border-radius: 20px; background: color-mix(in srgb, var(--text) 4%, transparent); border: 1px solid var(--surface-border); color: var(--text-secondary); text-align: center; line-height: 1.6; }
.step-box { margin-top: 40px; background: var(--surface); padding: 36px; border-radius: 28px; border: 1px solid var(--surface-border); display: flex; flex-direction: column; gap: 16px; max-width: 760px; margin-inline: auto; }
.step-box__header h2 { color: var(--text-on-surface); margin: 0 0 4px; }
.chosen { color: var(--text-secondary); margin: 0; }
.chosen strong { color: var(--text-on-surface); }
.form-section-label, .divider span, .helper { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; color: var(--text-secondary); }
.fields-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
.step-input { padding: 13px 16px; border-radius: 12px; border: 1.5px solid var(--surface-border); font-size: 15px; color: var(--text-on-surface); width: 100%; background: color-mix(in srgb, var(--bg) 90%, var(--text) 10%); }
.divider { display: flex; align-items: center; gap: 12px; margin: 4px 0; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--surface-border); }
.single-action { display: grid; }
.start-btn, .kaspi-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 15px 24px; border: none; border-radius: 14px; color: white; font-size: 16px; font-weight: 700; cursor: pointer; text-decoration: none; }
.start-btn, .kaspi-btn { background: linear-gradient(135deg, #e11d48, #be123c); }
.start-btn:disabled { opacity: 0.55; cursor: not-allowed; }
.status-card { padding: 18px; border-radius: 18px; background: color-mix(in srgb, var(--text) 4%, transparent); border: 1px solid var(--surface-border); display: grid; gap: 10px; }
.status-line { display: flex; align-items: center; justify-content: space-between; gap: 12px; color: var(--text-secondary); }
.status-line strong { color: var(--text-on-surface); }
.status-hint { margin: 0; color: var(--text-secondary); line-height: 1.6; }
@media (max-width: 768px) { .subject-page { padding: 90px 16px 60px; } .fields-row { grid-template-columns: 1fr; } .step-box { padding: 24px 18px; } .status-line { flex-direction: column; align-items: flex-start; } }
</style>
