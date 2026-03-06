<template>
  <div class="subject-page">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
    </div>

    <div class="container">

      <!-- Header -->
      <div class="page-header">
        <div class="page-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
          </svg>
          Предметы
        </div>
        <h1 class="page-title">Выберите предмет</h1>
        <p class="page-subtitle">Начните олимпиаду и проверьте свои знания прямо сейчас</p>
      </div>

      

      <!-- Subject Grid -->
      <div class="subjects-grid">
        <div
          v-for="subject in subjects"
          :key="subject.id"
          @click="selectSubject(subject)"
          class="subject-card"
          :class="{ selected: selectedSubject?.id === subject.id }"
        >
          <div class="subject-card__img-wrap">
            <img :src="subject.image" :alt="subject.name"/>
          </div>
          <h2 class="subject-card__name">{{ subject.name }}</h2>
          <p class="subject-card__desc">{{ subject.description }}</p>
          <div class="subject-card__date">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            {{ formatDate(subject.start_date) }}
          </div>
          <div class="subject-card__check">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Registration Form -->
      <transition name="slide-up">
        <div
          v-if="selectedSubject && requestStatus !== 'pending' && requestStatus !== 'approved' && requestStatus !== 'rejected'"
          class="step-box"
        >
          <div class="step-box__header">
            <div class="step-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
            </div>
            <div>
              <h2>Регистрация участника</h2>
              <p class="chosen">Предмет: <strong>{{ selectedSubject.name }}</strong></p>
            </div>
          </div>

          <div class="form-section-label">Данные участника</div>

          <div class="fields-row">
            <div class="field">
              <label>Имя</label>
              <input v-model="firstName" placeholder="Введите имя" class="step-input"/>
              <span class="error" v-if="errors.firstName">{{ errors.firstName }}</span>
            </div>
            <div class="field">
              <label>Фамилия</label>
              <input v-model="lastName" placeholder="Введите фамилию" class="step-input"/>
              <span class="error" v-if="errors.lastName">{{ errors.lastName }}</span>
            </div>
          </div>

          <div class="fields-row">
            <div class="field">
              <label>Дата рождения</label>
              <input v-model="birthDate" type="date" class="step-input"/>
              <span class="error" v-if="errors.birthDate">{{ errors.birthDate }}</span>
            </div>
            <div class="field">
              <label>Класс</label>
              <input v-model="grade" placeholder="Например: 9" class="step-input"/>
              <span class="error" v-if="errors.grade">{{ errors.grade }}</span>
            </div>
          </div>

          <div class="field">
            <label>Язык олимпиады</label>
            <select v-model="language" class="step-input">
              <option disabled value="">Выберите язык</option>
              <option value="ru">🇷🇺 Русский</option>
              <option value="kk">🇰🇿 Казахский</option>
              <option value="en">🇬🇧 Английский</option>
            </select>
            <span class="error" v-if="errors.language">{{ errors.language }}</span>
          </div>

          <div class="divider">
            <span>Данные родителя</span>
          </div>

          <div class="field">
            <label>ФИО родителя</label>
            <input v-model="parentName" placeholder="Полное имя" class="step-input"/>
            <span class="error" v-if="errors.parentName">{{ errors.parentName }}</span>
          </div>

          <div class="fields-row">
            <div class="field">
              <label>Телефон</label>
              <input v-model="parentPhone" placeholder="+7 (777) 777-77-77" class="step-input"/>
              <span class="error" v-if="errors.parentPhone">{{ errors.parentPhone }}</span>
            </div>
            <div class="field">
              <label>Email</label>
              <input v-model="parentEmail" placeholder="email@mail.ru" class="step-input"/>
              <span class="error" v-if="errors.parentEmail">{{ errors.parentEmail }}</span>
            </div>
          </div>

          <button :disabled="!isFormValid" @click="startOlympiad" class="start-btn">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.99 15a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.92 4h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 11.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            Оставить заявку
          </button>
        </div>
      </transition>

      <!-- Payment QR -->
      <transition name="slide-up">
        <div v-if="requestStatus === 'pending'" class="step-box step-box--payment">
          <div class="step-box__header">
            <div class="step-icon step-icon--yellow">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
              </svg>
            </div>
            <div>
              <h2>Оплата участия</h2>
              <p class="chosen">Отсканируйте QR-код или оплатите через Kaspi</p>
            </div>
          </div>

          <div class="qr-wrap">
            <img src="/public/kaspi.png" alt="QR код для оплаты" draggable="false"/>
          </div>

          <a
            href="https://kaspi.kz/pay/_gate?action=service_with_subservice&service_id=3025&subservice_id=22909&region_id=19"
            class="kaspi-btn"
            target="_blank"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
            Оплатить через Kaspi
          </a>

          <button class="start-btn start-btn--outline" @click="router.push('/profile')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            Я оплатил(а)
          </button>
        </div>
      </transition>

      <!-- Approved -->
      <transition name="slide-up">
        <div v-if="requestStatus === 'approved' && selectedSubject" class="step-box step-box--success">
          <div class="success-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <h2>Заявка одобрена!</h2>
          <p class="chosen">Вы можете приступить к олимпиаде по предмету <strong>{{ selectedSubject.name }}</strong></p>
          <button class="start-btn" @click="router.push(`/quiz/${selectedSubject.id}`)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polygon points="5 3 19 12 5 21 5 3"/>
            </svg>
            Начать олимпиаду
          </button>
        </div>
      </transition>

      <!-- Rejected -->
      <transition name="slide-up">
        <div v-if="requestStatus === 'rejected'" class="step-box step-box--rejected">
          <div class="rejected-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
          </div>
          <h2>Заявка отклонена</h2>
          <p class="chosen">Вы можете выбрать другой предмет и отправить новую заявку.</p>
          <button class="start-btn start-btn--outline" @click="requestStatus = null; selectedSubject = null">
            Попробовать снова
          </button>
        </div>
      </transition>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'

const router = useRouter()
const userStore = useUserStore()

const subjects = ref([])
const selectedSubject = ref(null)
const requestStatus = ref(null)

const firstName = ref('')
const lastName = ref('')
const birthDate = ref('')
const grade = ref('')
const language = ref('')
const parentName = ref('')
const parentPhone = ref('')
const parentEmail = ref('')

const errors = ref({
  firstName:'', lastName:'', birthDate:'', grade:'', language:'',
  parentName:'', parentPhone:'', parentEmail:''
})

const emailValid = computed(() => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(parentEmail.value.trim()))
const phoneValid = computed(() => {
  const digits = parentPhone.value.replace(/[^\d]/g,'')
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

watch([firstName,lastName,birthDate,grade,language,parentName,parentPhone,parentEmail], validateFields)
const isFormValid = computed(() => Object.values(errors.value).every(e => e === ''))

const selectSubject = (subject) => {
  selectedSubject.value = subject
}

const fetchSubjects = async () => {
  try {
    const res = await api.get('/subjects')
    subjects.value = res.data
    if (userStore.user) {
      for (const subject of subjects.value) {
        const statusRes = await api.get(`/quiz/status/${subject.id}`)
        if (statusRes.data.status) {
          selectedSubject.value = subject
          requestStatus.value = statusRes.data.status
          break
        }
      }
    }
  } catch(e) { console.log(e) }
}

const fetchRequestStatus = async () => {
  if (!userStore.user || !selectedSubject.value) return
  try {
    const res = await api.get(`/quiz/status/${selectedSubject.value.id}`)
    requestStatus.value = res.data.status
  } catch(e) { console.log(e) }
}

const startOlympiad = async () => {
  if (!userStore.user) { router.push('/login'); return }
  if (!selectedSubject.value) return
  validateFields()
  if (!isFormValid.value) return
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
      parent_email: parentEmail.value.trim()
    })
    requestStatus.value = 'pending'
  } catch(err) {
    alert(err.response?.data?.message || 'Ошибка отправки заявки')
  }
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  if (isNaN(d)) return ''
  return d.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

onMounted(fetchSubjects)
watch(selectedSubject, fetchRequestStatus)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; }

.subject-page {
  min-height: 100vh;
  background: #f7f8fc;
  padding: 80px 28px 100px;
  font-family: 'Manrope', sans-serif;
  position: relative;
  overflow: hidden;
}

/* Orbs */
.bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.3; }
.orb-1 { width: 600px; height: 600px; background: radial-gradient(circle, #c7d2fe, #a5b4fc); top: -200px; right: -150px; }
.orb-2 { width: 500px; height: 500px; background: radial-gradient(circle, #ddd6fe, #c4b5fd); bottom: -150px; left: -100px; }

.container {
  max-width: 1100px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

/* Header */
.page-header { text-align: center; margin-bottom: 56px; }
.page-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6366f1;
  background: #eef2ff;
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid #c7d2fe;
  margin-bottom: 18px;
}
.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 40px;
  font-weight: 700;
  color: #1e1b4b;
  margin: 0 0 12px;
}
.page-subtitle { font-size: 16px; color: #94a3b8; margin: 0; }

/* Subject grid */
.subjects-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 20px;
  margin-bottom: 16px;
}

.subject-card {
  background: white;
  padding: 30px 24px;
  border-radius: 24px;
  cursor: pointer;
  border: 2px solid transparent;
  box-shadow: 0 4px 24px rgba(0,0,0,0.05);
  transition: all 0.3s ease;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.subject-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, #818cf8, #6366f1, #a78bfa);
  opacity: 0;
  transition: opacity 0.3s;
}
.subject-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(79, 70, 229, 0.12);
  border-color: #c7d2fe;
}
.subject-card:hover::before { opacity: 1; }

.subject-card.selected {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
  border-color: #4338ca;
  box-shadow: 0 16px 48px rgba(79, 70, 229, 0.3);
  transform: translateY(-6px);
}
.subject-card.selected::before { opacity: 1; background: rgba(255,255,255,0.3); }
.subject-card.selected .subject-card__name { color: white; }
.subject-card.selected .subject-card__desc { color: #c7d2fe; }
.subject-card.selected .subject-card__date { color: #a5b4fc; border-color: rgba(255,255,255,0.2); background: rgba(255,255,255,0.1); }

.subject-card__check {
  position: absolute;
  top: 14px; right: 14px;
  width: 28px; height: 28px;
  background: rgba(255,255,255,0.2);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white;
  opacity: 0;
  transition: opacity 0.3s;
}
.subject-card.selected .subject-card__check { opacity: 1; }

.subject-card__img-wrap {
  width: 80px; height: 80px;
  background: #f1f5f9;
  border-radius: 20px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 18px;
  overflow: hidden;
  transition: background 0.3s;
}
.subject-card.selected .subject-card__img-wrap { background: rgba(255,255,255,0.15); }
.subject-card__img-wrap img { width: 60px; height: 60px; object-fit: contain; }

.subject-card__name {
  font-family: 'Playfair Display', serif;
  font-size: 18px;
  font-weight: 700;
  color: #1e1b4b;
  margin: 0 0 8px;
}
.subject-card__desc {
  font-size: 13px;
  color: #64748b;
  line-height: 1.5;
  margin: 0 0 14px;
  display: -webkit-box;
  
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.subject-card__date {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
  color: #94a3b8;
  background: #f8fafc;
  padding: 5px 12px;
  border-radius: 20px;
  border: 1px solid #e2e8f0;
}

/* Step box */
.step-box {
  margin-top: 48px;
  background: white;
  padding: 40px;
  border-radius: 28px;
  box-shadow: 0 8px 48px rgba(79, 70, 229, 0.1);
  border: 1px solid rgba(99, 102, 241, 0.1);
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-width: 560px;
  margin-inline: auto;
}

.step-box__header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding-bottom: 20px;
  border-bottom: 1px solid #f1f5f9;
}
.step-box__header h2 {
  font-family: 'Playfair Display', serif;
  font-size: 22px;
  font-weight: 700;
  color: #1e1b4b;
  margin: 0 0 4px;
}
.step-icon {
  width: 48px; height: 48px;
  background: #eef2ff;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  color: #6366f1;
  flex-shrink: 0;
}
.step-icon--yellow { background: #fffbeb; color: #d97706; }

.form-section-label {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #94a3b8;
  margin-top: 4px;
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
  color: #374151;
}

.step-input {
  padding: 13px 16px;
  border-radius: 12px;
  border: 1.5px solid #e2e8f0;
  font-family: 'Manrope', sans-serif;
  font-size: 15px;
  color: #1e1b4b;
  transition: all 0.2s;
  width: 100%;
  background: #fafbff;
}
.step-input:focus {
  outline: none;
  border-color: #6366f1;
  background: white;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
}
select.step-input { cursor: pointer; }

.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 4px 0;
}
.divider::before, .divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #f1f5f9;
}
.divider span {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #94a3b8;
  white-space: nowrap;
}

.error {
  font-size: 12px;
  color: #ef4444;
  font-weight: 600;
}
.chosen { font-size: 14px; color: #64748b; margin: 0; }
.chosen strong { color: #1e1b4b; }

/* Buttons */
.start-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  padding: 15px 24px;
  border: none;
  border-radius: 14px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.25s;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
  margin-top: 4px;
}
.start-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99, 102, 241, 0.45); }
.start-btn:disabled { background: #c7d2fe; cursor: not-allowed; transform: none; box-shadow: none; }

.start-btn--outline {
  background: white;
  color: #6366f1;
  border: 2px solid #c7d2fe;
  box-shadow: none;
}
.start-btn--outline:hover { background: #eef2ff; border-color: #a5b4fc; box-shadow: none; }

/* Payment */
.step-box--payment { text-align: center; }
.qr-wrap {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 20px;
  display: flex; align-items: center; justify-content: center;
}
.qr-wrap img {
  width: 260px;
  max-width: 100%;
  user-select: none;
  pointer-events: none;
  border-radius: 12px;
}
.kaspi-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 14px 26px;
  border-radius: 14px;
  text-decoration: none;
  font-family: 'Manrope', sans-serif;
  font-weight: 700;
  font-size: 15px;
  color: white;
  background: linear-gradient(135deg, #ff0033, #ff5a5f);
  box-shadow: 0 6px 20px rgba(255, 0, 51, 0.3);
  transition: all 0.25s;
}
.kaspi-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(255, 0, 51, 0.4); }

/* Success */
.step-box--success { text-align: center; align-items: center; }
.success-icon {
  width: 72px; height: 72px;
  background: #dcfce7;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #16a34a;
  box-shadow: 0 0 0 8px #f0fdf4;
}
.step-box--success h2 {
  font-family: 'Playfair Display', serif;
  font-size: 26px;
  font-weight: 700;
  color: #1e1b4b;
  margin: 8px 0 0;
}

/* Rejected */
.step-box--rejected { text-align: center; align-items: center; }
.rejected-icon {
  width: 72px; height: 72px;
  background: #fee2e2;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: #dc2626;
  box-shadow: 0 0 0 8px #fff5f5;
}
.step-box--rejected h2 {
  font-family: 'Playfair Display', serif;
  font-size: 24px;
  font-weight: 700;
  color: #1e1b4b;
  margin: 8px 0 0;
}

/* Transition */
.slide-up-enter-active { transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.slide-up-enter-from { opacity: 0; transform: translateY(30px); }

/* Responsive */
@media (max-width: 768px) {
  .subject-page { padding: 60px 16px 80px; }
  .page-title { font-size: 28px; }
  .step-box { padding: 28px 20px; }
  .fields-row { grid-template-columns: 1fr; }
}
@media (max-width: 480px) {
  .subjects-grid { grid-template-columns: 1fr 1fr; gap: 14px; }
  .subject-card { padding: 20px 16px; }
}
</style>