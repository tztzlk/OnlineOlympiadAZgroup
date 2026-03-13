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
        <p class="page-subtitle">После заполнения формы участник сразу переходит к тесту без промежуточной заявки.</p>
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

      <div v-if="!subjects.length" class="empty-state">
        Сейчас нет опубликованных олимпиад. Как только администратор опубликует тест, он появится здесь.
      </div>

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
              <input v-model="grade" placeholder="Например: 9" class="step-input" />
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
            {{ submitting ? 'Открываем тест...' : 'Перейти к тесту' }}
          </button>
        </div>
      </transition>

      <transition name="slide-up">
        <div v-if="requestStatus === 'approved' && selectedSubject" class="step-box step-box--success">
          <h2>Доступ открыт</h2>
          <p class="chosen">Можно сразу перейти к олимпиаде по предмету <strong>{{ selectedSubject.name }}</strong>.</p>
          <button class="start-btn" @click="router.push(`/quiz/${selectedSubject.id}`)">Начать тест</button>
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
const submitting = ref(false)

const firstName = ref('')
const lastName = ref('')
const birthDate = ref('')
const grade = ref('')
const language = ref('')
const parentName = ref('')
const parentPhone = ref('')
const parentEmail = ref('')

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
const phoneValid = computed(() => {
  const digits = parentPhone.value.replace(/[^\d]/g, '')
  return /^7\d{10}$/.test(digits)
})

const validateFields = () => {
  errors.value.firstName = firstName.value.trim().length > 1 ? '' : 'Введите имя'
  errors.value.lastName = lastName.value.trim().length > 1 ? '' : 'Введите фамилию'
  errors.value.birthDate = birthDate.value ? '' : 'Выберите дату рождения'
  errors.value.grade = grade.value.trim() ? '' : 'Введите класс'
  errors.value.language = language.value ? '' : 'Выберите язык'
  errors.value.parentName = parentName.value.trim().length > 3 ? '' : 'Введите ФИО родителя'
  errors.value.parentPhone = phoneValid.value ? '' : 'Неверный формат телефона'
  errors.value.parentEmail = emailValid.value ? '' : 'Неверный формат email'
}

watch([firstName, lastName, birthDate, grade, language, parentName, parentPhone, parentEmail], validateFields)

const isFormValid = computed(() => Object.values(errors.value).every((value) => value === ''))

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
    await api.post('/olympiad/request', {
      subject_id: selectedSubject.value.id,
      first_name: firstName.value.trim(),
      last_name: lastName.value.trim(),
      birth_date: birthDate.value,
      grade: grade.value.trim(),
      language: language.value,
      parent_name: parentName.value.trim(),
      parent_phone: parentPhone.value.trim(),
      parent_email: parentEmail.value.trim(),
    })

    requestStatus.value = 'approved'
    router.push(`/quiz/${selectedSubject.value.id}`)
  } catch (error) {
    alert(error.response?.data?.message || 'Не удалось открыть тест')
  } finally {
    submitting.value = false
  }
}

onMounted(fetchSubjects)
watch(selectedSubject, fetchRequestStatus)
</script>

<style scoped>
* { box-sizing: border-box; }

.subject-page {
  min-height: 100vh;
  background: #0f0f0f;
  padding: 80px 28px 100px;
  position: relative;
  overflow: hidden;
}

.bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.3; }
.orb-1 { width: 600px; height: 600px; background: radial-gradient(circle, rgba(225, 29, 72, 0.12), transparent); top: -200px; right: -150px; }
.orb-2 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(59, 130, 246, 0.14), transparent); bottom: -150px; left: -100px; }

.container {
  max-width: 1100px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.page-header { text-align: center; margin-bottom: 56px; }
.page-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #e11d48;
  background: rgba(225, 29, 72, 0.15);
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid rgba(225, 29, 72, 0.35);
  margin-bottom: 18px;
}

.page-title {
  font-size: 30px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 12px;
}

.page-subtitle {
  font-size: 16px;
  color: #a1a1aa;
  margin: 0;
  line-height: 1.6;
}

.subjects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 24px;
  margin-bottom: 20px;
}

.subject-card {
  background: #1a1a1a;
  padding: 24px;
  border-radius: 24px;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.3s ease;
  text-align: center;
}

.subject-card:hover,
.subject-card.selected {
  transform: translateY(-4px);
  border-color: rgba(225, 29, 72, 0.35);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
}

.subject-card__img-wrap {
  width: 80px;
  height: 80px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  overflow: hidden;
}

.subject-card__img-wrap img {
  width: 60px;
  height: 60px;
  object-fit: contain;
}

.subject-card__name {
  font-size: 18px;
  font-weight: 600;
  color: #fff;
  margin: 0 0 8px;
}

.subject-card__desc {
  font-size: 14px;
  color: #a1a1aa;
  line-height: 1.5;
  margin: 0 0 12px;
}

.subject-card__date {
  display: inline-flex;
  font-size: 12px;
  font-weight: 600;
  color: #a1a1aa;
  background: rgba(255, 255, 255, 0.04);
  padding: 5px 12px;
  border-radius: 20px;
}

.empty-state {
  max-width: 680px;
  margin: 0 auto 24px;
  padding: 18px 20px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  text-align: center;
  line-height: 1.6;
}

.step-box {
  margin-top: 40px;
  background: #1a1a1a;
  padding: 36px;
  border-radius: 28px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-width: 680px;
  margin-inline: auto;
}

.step-box__header h2 {
  color: #fff;
  margin: 0 0 4px;
}

.chosen {
  color: #94a3b8;
  margin: 0;
}

.chosen strong {
  color: #fff;
}

.form-section-label,
.divider span {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #a1a1aa;
}

.fields-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field label {
  font-size: 13px;
  font-weight: 600;
  color: #cbd5e1;
}

.step-input {
  padding: 13px 16px;
  border-radius: 12px;
  border: 1.5px solid rgba(255, 255, 255, 0.08);
  font-size: 15px;
  color: #fff;
  width: 100%;
  background: rgba(255, 255, 255, 0.04);
}

.step-input:focus {
  outline: none;
  border-color: #e11d48;
  box-shadow: 0 0 0 4px rgba(225, 29, 72, 0.12);
}

.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 4px 0;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(255, 255, 255, 0.06);
}

.error {
  font-size: 12px;
  color: #ef4444;
  font-weight: 600;
}

.start-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  padding: 15px 24px;
  border: none;
  border-radius: 14px;
  background: linear-gradient(135deg, #e11d48, #be123c);
  color: white;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(225, 29, 72, 0.35);
}

.start-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.step-box--success {
  text-align: center;
  align-items: center;
}

.slide-up-enter-active { transition: all 0.35s ease; }
.slide-up-enter-from { opacity: 0; transform: translateY(18px); }

@media (max-width: 768px) {
  .subject-page { padding: 56px 16px 72px; }
  .fields-row { grid-template-columns: 1fr; }
  .step-box { padding: 24px 18px; }
}

@media (max-width: 480px) {
  .subjects-grid { grid-template-columns: 1fr 1fr; gap: 16px; }
}
</style>
