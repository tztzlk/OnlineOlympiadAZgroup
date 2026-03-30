<template>
  <div class="subject-page">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
    </div>

    <div class="container">
      <div class="page-header">
        <div class="page-badge">Предметы</div>
        <h1 class="page-title">Выберите олимпиаду и начните тест</h1>
        <p class="page-subtitle">После заявки участник проходит шаг оплаты через Kaspi, а доступ к тесту открывается после подтверждения оплаты.</p>
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

      <transition name="slide-up">
        <div v-if="selectedSubject && requestStatus !== 'approved'" class="step-box">
          <div class="step-box__header">
            <div>
              <h2>Регистрация участника</h2>
              <p class="chosen">Предмет: <strong>{{ selectedSubject.name }}</strong></p>
            </div>
          </div>

          <div class="form-section-label">Данные участника</div>

          <div class="fields-row">
            <div class="field">
              <label>Имя</label>
              <input v-model="firstName" placeholder="Введите имя" class="step-input" />
              <span v-if="errors.firstName" class="error">{{ errors.firstName }}</span>
            </div>
            <div class="field">
              <label>Фамилия</label>
              <input v-model="lastName" placeholder="Введите фамилию" class="step-input" />
              <span v-if="errors.lastName" class="error">{{ errors.lastName }}</span>
            </div>
          </div>

          <div class="fields-row">
            <div class="field">
              <label>Дата рождения</label>
              <input v-model="birthDate" type="date" class="step-input" />
              <span v-if="errors.birthDate" class="error">{{ errors.birthDate }}</span>
            </div>
            <div class="field">
              <label>Класс</label>
              <select v-model.number="grade" class="step-input">
                <option disabled value="">Выберите класс</option>
                <option v-for="item in gradeOptions" :key="item" :value="item">{{ item }} класс</option>
              </select>
              <span v-if="errors.grade" class="error">{{ errors.grade }}</span>
            </div>
          </div>

          <div class="field">
            <label>Язык олимпиады</label>
            <select v-model="language" class="step-input">
              <option disabled value="">Выберите язык</option>
              <option value="ru">Русский</option>
              <option value="kk">Казахский</option>
              <option value="en">Английский</option>
            </select>
            <span v-if="errors.language" class="error">{{ errors.language }}</span>
          </div>

          <div class="divider"><span>Данные родителя</span></div>

          <div class="field">
            <label>ФИО родителя</label>
            <input v-model="parentName" placeholder="Полное имя" class="step-input" />
            <span v-if="errors.parentName" class="error">{{ errors.parentName }}</span>
          </div>

          <div class="fields-row">
            <div class="field">
              <label>Телефон</label>
              <input v-model="parentPhone" placeholder="+7 (777) 777-77-77" class="step-input" />
              <span v-if="errors.parentPhone" class="error">{{ errors.parentPhone }}</span>
            </div>
            <div class="field">
              <label>Email</label>
              <input v-model="parentEmail" placeholder="email@mail.ru" class="step-input" />
              <span v-if="errors.parentEmail" class="error">{{ errors.parentEmail }}</span>
            </div>
          </div>

          <button :disabled="!isFormValid || submitting" class="start-btn" @click="startOlympiad">
            {{ submitting ? 'Сохраняем...' : 'Оформить участие' }}
          </button>
        </div>
      </transition>

      <transition name="slide-up">
        <div v-if="selectedSubject && requestStatus === 'approved'" class="step-box step-box--success">
          <h2>Шаг оплаты</h2>
          <p class="chosen">Статус оплаты: <strong>{{ paymentLabel }}</strong></p>

          <div class="payment-card">
            <p>Для прохождения теста оплатите участие через Kaspi. После подтверждения оплаты доступ к тесту откроется автоматически.</p>
            <a v-if="paymentUrl" :href="paymentUrl" target="_blank" class="kaspi-btn">Оплатить через Kaspi</a>
            <p v-else class="error">Kaspi-ссылка пока не настроена администратором.</p>
          </div>

          <div v-if="paymentStatus === 'paid'" class="payment-card success">
            <p>Оплата подтверждена. Можно переходить к тесту.</p>
            <button class="start-btn" @click="router.push(`/quiz/${selectedSubject.id}`)">Начать тест</button>
          </div>

          <div v-else class="payment-note">
            После оплаты дождитесь подтверждения. Если доступ не открылся, напишите в <RouterLink to="/help-desk">Help Desk</RouterLink>.
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'

const router = useRouter()
const userStore = useUserStore()

const subjects = ref([])
const selectedSubject = ref(null)
const requestStatus = ref(null)
const paymentStatus = ref(null)
const paymentUrl = ref('')
const submitting = ref(false)

const firstName = ref('')
const lastName = ref('')
const birthDate = ref('')
const grade = ref('')
const language = ref('')
const parentName = ref('')
const parentPhone = ref('')
const parentEmail = ref('')
const gradeOptions = [3, 4, 5, 6, 7, 8, 9, 10, 11]

const errors = ref({
  firstName: '',
  lastName: '',
  birthDate: '',
  grade: '',
  language: '',
  parentName: '',
  parentPhone: '',
  parentEmail: '',
})

const emailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(parentEmail.value.trim()))
const phoneValid = computed(() => /^7\d{10}$/.test(parentPhone.value.replace(/[^\d]/g, '')))
const isFormValid = computed(() => Object.values(errors.value).every((value) => value === ''))
const paymentLabel = computed(() => ({
  pending: 'Ожидает подтверждения',
  paid: 'Подтверждена',
  failed: 'Не подтверждена',
}[paymentStatus.value] || 'Не определён'))

const validateFields = () => {
  errors.value.firstName = firstName.value.trim().length > 1 ? '' : 'Введите имя'
  errors.value.lastName = lastName.value.trim().length > 1 ? '' : 'Введите фамилию'
  errors.value.birthDate = birthDate.value ? '' : 'Выберите дату рождения'
  errors.value.grade = Number(grade.value) >= 3 && Number(grade.value) <= 11 ? '' : 'Выберите класс'
  errors.value.language = language.value ? '' : 'Выберите язык'
  errors.value.parentName = parentName.value.trim().length > 3 ? '' : 'Введите ФИО родителя'
  errors.value.parentPhone = phoneValid.value ? '' : 'Неверный формат телефона'
  errors.value.parentEmail = emailValid.value ? '' : 'Неверный формат email'
}

watch([firstName, lastName, birthDate, grade, language, parentName, parentPhone, parentEmail], validateFields)

const formatDate = (date) => {
  if (!date) return ''
  const value = new Date(date)
  if (Number.isNaN(value.getTime())) return ''
  return value.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

const fetchSubjects = async () => {
  const { data } = await api.get('/subjects')
  subjects.value = data
}

const fetchRequestStatus = async () => {
  if (!userStore.user || !selectedSubject.value) return

  try {
    const { data } = await api.get(`/quiz/status/${selectedSubject.value.id}`)
    requestStatus.value = data.status
    paymentStatus.value = data.payment_status
    paymentUrl.value = data.payment_url || ''
  } catch (error) {
    console.error(error)
  }
}

const selectSubject = (subject) => {
  selectedSubject.value = subject
}

const startOlympiad = async () => {
  if (!userStore.user) {
    router.push('/login')
    return
  }

  if (!selectedSubject.value) return
  validateFields()
  if (!isFormValid.value) return

  submitting.value = true
  try {
    const { data } = await api.post('/olympiad/request', {
      subject_id: selectedSubject.value.id,
      first_name: firstName.value.trim(),
      last_name: lastName.value.trim(),
      birth_date: birthDate.value,
      grade: Number(grade.value),
      language: language.value,
      parent_name: parentName.value.trim(),
      parent_phone: parentPhone.value.trim(),
      parent_email: parentEmail.value.trim(),
    })

    requestStatus.value = data.request?.status || 'approved'
    paymentStatus.value = data.request?.payment_status || 'pending'
    paymentUrl.value = data.payment_url || ''
  } catch (error) {
    alert(error.response?.data?.message || 'Не удалось оформить участие')
  } finally {
    submitting.value = false
  }
}

onMounted(fetchSubjects)
watch(selectedSubject, fetchRequestStatus)
</script>

<style scoped>
* { box-sizing: border-box; }
.subject-page { min-height: 100vh; background: var(--bg); padding: 80px 28px 100px; position: relative; overflow: hidden; }
.bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.3; }
.orb-1 { width: 600px; height: 600px; background: radial-gradient(circle, rgba(225,29,72,0.12), transparent); top: -200px; right: -150px; }
.orb-2 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(59,130,246,0.14), transparent); bottom: -150px; left: -100px; }
.container { max-width: 1100px; margin: 0 auto; position: relative; z-index: 1; }
.page-header { text-align: center; margin-bottom: 56px; }
.page-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #e11d48; background: rgba(225,29,72,0.15); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(225,29,72,0.35); margin-bottom: 18px; }
.page-title { font-size: 30px; font-weight: 700; color: var(--text-primary); margin: 0 0 12px; }
.page-subtitle { font-size: 16px; color: var(--text-secondary); margin: 0; line-height: 1.6; }
.subjects-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 24px; margin-bottom: 20px; }
.subject-card { background: var(--surface); padding: 24px; border-radius: 24px; cursor: pointer; border: 2px solid transparent; transition: all 0.3s ease; text-align: center; }
.subject-card:hover, .subject-card.selected { transform: translateY(-4px); border-color: rgba(225,29,72,0.35); box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
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
.form-section-label, .divider span { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-secondary); }
.fields-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field label { font-size: 13px; font-weight: 600; color: var(--text-secondary); }
.step-input { padding: 13px 16px; border-radius: 12px; border: 1.5px solid var(--surface-border); font-size: 15px; color: var(--text-on-surface); width: 100%; background: color-mix(in srgb, var(--bg) 90%, var(--text) 10%); }
.divider { display: flex; align-items: center; gap: 12px; margin: 4px 0; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--surface-border); }
.error { font-size: 12px; color: #ef4444; font-weight: 600; }
.start-btn, .kaspi-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 15px 24px; border: none; border-radius: 14px; background: linear-gradient(135deg, #e11d48, #be123c); color: white; font-size: 16px; font-weight: 700; cursor: pointer; text-decoration: none; }
.start-btn:disabled { opacity: 0.55; cursor: not-allowed; }
.step-box--success { text-align: center; align-items: center; }
.payment-card { width: 100%; padding: 18px; border-radius: 18px; background: color-mix(in srgb, var(--text) 4%, transparent); border: 1px solid var(--surface-border); }
.payment-card.success { border-color: rgba(34,197,94,0.35); background: rgba(34,197,94,0.08); }
.payment-card p { margin: 0 0 14px; color: var(--text-muted-on-surface); }
.payment-note { color: var(--text-secondary); }
.payment-note a { color: #e11d48; text-decoration: none; font-weight: 700; }
.slide-up-enter-active { transition: all 0.35s ease; }
.slide-up-enter-from { opacity: 0; transform: translateY(18px); }
@media (max-width: 768px) { .subject-page { padding: 56px 16px 72px; } .fields-row { grid-template-columns: 1fr; } .step-box { padding: 24px 18px; } }
@media (max-width: 480px) { .subjects-grid { grid-template-columns: 1fr; gap: 16px; } .start-btn, .kaspi-btn { width: 100%; } }
</style>
