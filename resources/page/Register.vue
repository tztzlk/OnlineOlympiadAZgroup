<template>
  <div class="register-page">
    <div class="register-shell">
      <aside class="register-side">
        <div class="side-copy">
          <p class="eyebrow">Регистрация</p>
          <h1>Личный кабинет для родителя</h1>
          <p class="lead">
            Создайте аккаунт, добавьте ребёнка и переходите к олимпиаде без лишних шагов.
          </p>
        </div>

        <div class="journey-steps" aria-label="Шаги регистрации">
          <article
            v-for="(step, index) in journeySteps"
            :key="step.title"
            class="journey-step"
            :class="{ 'is-active': index === currentStep - 1 }"
          >
            <div class="journey-step__icon">{{ step.icon }}</div>
            <div class="journey-step__body">
              <span class="journey-step__meta">Шаг {{ index + 1 }}</span>
              <strong>{{ step.title }}</strong>
              <p>{{ step.text }}</p>
            </div>
          </article>
        </div>

        <div class="benefit-card">
          <strong>После регистрации вы сможете</strong>
          <ul class="benefit-list">
            <li>Добавить профиль ребёнка</li>
            <li>Выбрать предмет и оплатить участие</li>
            <li>Отслеживать статус, результаты и сертификаты</li>
          </ul>
        </div>
      </aside>

      <section class="register-card">
        <header class="register-header">
          <div>
            <p class="eyebrow">Регистрация</p>
            <h2>Откройте личный кабинет</h2>
            <p class="register-copy">{{ currentStepDescription }}</p>
          </div>

          <div class="step-summary">
            <span class="step-summary__label">Текущий шаг</span>
            <strong>{{ currentStep }} / 2</strong>
          </div>
        </header>

        <div class="progress-card" aria-label="Прогресс регистрации">
          <div class="progress-card__top">
            <strong>{{ currentStepTitle }}</strong>
            <span>{{ progressPercent }}%</span>
          </div>
          <div class="progress-track">
            <div class="progress-fill" :style="{ width: `${progressPercent}%` }"></div>
          </div>
          <div class="progress-steps">
            <div class="progress-step" :class="{ 'is-current': currentStep === 1, 'is-done': currentStep > 1 }">
              <span>1</span>
              <small>Доступ</small>
            </div>
            <div class="progress-step" :class="{ 'is-current': currentStep === 2 }">
              <span>2</span>
              <small>Данные родителя</small>
            </div>
          </div>
        </div>

        <div v-if="success" class="message success">
          <span class="message__icon" aria-hidden="true">✓</span>
          <div>
            <strong>Аккаунт создан</strong>
            <p>Перенаправляем в личный кабинет...</p>
          </div>
        </div>

        <form @submit.prevent="handleRegister" class="register-form" novalidate>
          <div v-if="currentStep === 1" class="form-section">
            <div class="form-section__head">
              <h3>Данные для входа</h3>
              <p>Email и пароль будут использоваться для входа в кабинет.</p>
            </div>

            <div class="form-grid">
              <label class="field field-wide" :class="fieldState(emailTouched, emailError)">
                <span>Email</span>
                <input
                  v-model="email"
                  type="email"
                  placeholder="you@example.com"
                  autocomplete="email"
                  required
                />
                <small class="field-message">
                  {{ emailTouched && emailError ? emailError : 'На этот email придут уведомления и восстановление доступа.' }}
                </small>
              </label>

              <label class="field" :class="fieldState(passwordTouched, passwordError)">
                <span>Пароль</span>
                <div class="password-wrap">
                  <input
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    placeholder="Минимум 12 символов"
                    autocomplete="new-password"
                    required
                  />
                  <button type="button" class="toggle-btn" @click="showPassword = !showPassword">
                    {{ showPassword ? 'Скрыть' : 'Показать' }}
                  </button>
                </div>
                <small class="field-message">
                  {{ passwordTouched && passwordError ? passwordError : 'Используйте уникальный пароль для безопасного входа.' }}
                </small>
              </label>

              <label class="field" :class="fieldState(confirmPasswordTouched, confirmPasswordError)">
                <span>Повторите пароль</span>
                <div class="password-wrap">
                  <input
                    v-model="confirmPassword"
                    :type="showConfirm ? 'text' : 'password'"
                    placeholder="Повторите пароль"
                    autocomplete="new-password"
                    required
                  />
                  <button type="button" class="toggle-btn" @click="showConfirm = !showConfirm">
                    {{ showConfirm ? 'Скрыть' : 'Показать' }}
                  </button>
                </div>
                <small class="field-message">
                  {{ confirmPasswordTouched && confirmPasswordError ? confirmPasswordError : 'Пароли должны совпадать.' }}
                </small>
              </label>
            </div>

            <div class="password-helper">
              <div class="password-helper__head">
                <strong>Надёжный пароль</strong>
                <span :class="['strength-badge', strengthClass]">{{ strengthLabel }}</span>
              </div>
              <ul class="password-checklist">
                <li v-for="item in passwordChecklist" :key="item.label" :class="{ 'is-met': item.met }">
                  <span aria-hidden="true">{{ item.met ? '✓' : '•' }}</span>
                  {{ item.label }}
                </li>
              </ul>
            </div>
          </div>

          <div v-else class="form-section">
            <div class="form-section__head">
              <h3>Контакты родителя</h3>
              <p>Эти данные понадобятся для заявок, оплаты и связи по участию.</p>
            </div>

            <div class="form-grid">
              <label class="field field-wide" :class="fieldState(nameTouched, nameError)">
                <span>Имя и фамилия</span>
                <input v-model="name" type="text" placeholder="Алия Ержанова" autocomplete="name" required />
                <small class="field-message">
                  {{ nameTouched && nameError ? nameError : 'Укажите взрослого, который оформляет участие.' }}
                </small>
              </label>

              <label class="field" :class="fieldState(phoneTouched, phoneError)">
                <span>Телефон</span>
                <input
                  v-model="phone"
                  type="tel"
                  maxlength="18"
                  placeholder="+7 (777) 000-00-00"
                  autocomplete="tel"
                  @input="formatPhone"
                  required
                />
                <small class="field-message">
                  {{ phoneTouched && phoneError ? phoneError : 'Номер нужен для быстрых уведомлений и уточнений.' }}
                </small>
              </label>

              <label class="field" :class="fieldState(cityTouched, cityError)">
                <span>Город</span>
                <input v-model="city" type="text" placeholder="Астана" autocomplete="address-level2" required />
                <small class="field-message">
                  {{ cityTouched && cityError ? cityError : 'Город будет использоваться в профиле и сертификатах.' }}
                </small>
              </label>

              <label class="field field-wide" :class="fieldState(schoolTouched, schoolError)">
                <span>Школа</span>
                <input v-model="school" type="text" placeholder="Лицей №12" autocomplete="organization" required />
                <small class="field-message">
                  {{ schoolTouched && schoolError ? schoolError : 'Название школы поможет заполнить профиль ребёнка быстрее.' }}
                </small>
              </label>
            </div>

            <label class="agreement-box" :class="{ 'is-error': rulesTouched && !rulesAccepted }">
              <input v-model="rulesAccepted" type="checkbox" />
              <span>Подтверждаю, что ознакомлен(а) с правилами участия и условиями платформы.</span>
            </label>
          </div>

          <div v-if="error" class="message error">
            <span class="message__icon" aria-hidden="true">!</span>
            <div>
              <strong>Проверьте форму</strong>
              <p>{{ error }}</p>
            </div>
          </div>

          <div class="form-actions">
            <button v-if="currentStep === 2" type="button" class="secondary-btn" @click="goBack">
              Назад
            </button>
            <button v-if="currentStep === 1" type="button" class="submit-btn" @click="goToStepTwo">
              Продолжить
            </button>
            <button v-else type="submit" class="submit-btn" :disabled="loading || success">
              <span v-if="loading" class="button-loader" aria-hidden="true"></span>
              {{ loading ? 'Создаём аккаунт...' : 'Продолжить' }}
            </button>
          </div>
        </form>

        <footer class="form-footer">
          <p>Уже есть аккаунт? <RouterLink to="/login">Войти</RouterLink></p>
        </footer>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { solveProofOfWork } from '../js/pow'
import { useUserStore } from '../stores/user'

const router = useRouter()
const userStore = useUserStore()

const currentStep = ref(1)
const name = ref('')
const email = ref('')
const school = ref('')
const city = ref('')
const phone = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const loading = ref(false)
const success = ref(false)
const showPassword = ref(false)
const showConfirm = ref(false)
const emailTouched = ref(false)
const passwordTouched = ref(false)
const confirmPasswordTouched = ref(false)
const nameTouched = ref(false)
const phoneTouched = ref(false)
const cityTouched = ref(false)
const schoolTouched = ref(false)
const rulesTouched = ref(false)
const rulesAccepted = ref(false)

const journeySteps = [
  { icon: '01', title: 'Регистрация', text: 'Укажите email и пароль для входа в кабинет.' },
  { icon: '02', title: 'Добавление ребёнка', text: 'После входа заполните профиль участника за минуту.' },
  { icon: '03', title: 'Участие', text: 'Выберите олимпиаду, оплатите и следите за статусом.' },
]

const currentStepTitle = computed(() => (
  currentStep.value === 1 ? 'Шаг 1. Доступ к кабинету' : 'Шаг 2. Данные родителя'
))

const currentStepDescription = computed(() => (
  currentStep.value === 1
    ? 'Сначала создайте безопасный вход в личный кабинет.'
    : 'Осталось указать контакты, чтобы можно было оформить участие.'
))

const progressPercent = computed(() => (currentStep.value === 1 ? 50 : 100))

const emailError = computed(() => {
  const value = email.value.trim()
  if (!value) return 'Введите email.'
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? '' : 'Проверьте формат email, например name@example.com.'
})

const nameError = computed(() => (name.value.trim() ? '' : 'Укажите имя и фамилию родителя.'))
const cityError = computed(() => (city.value.trim() ? '' : 'Укажите город.'))
const schoolError = computed(() => (school.value.trim() ? '' : 'Укажите школу.'))

const phoneError = computed(() => {
  const cleanPhone = phone.value.replace(/\D/g, '')
  if (!cleanPhone.length) return 'Введите номер в формате +7 (777) 000-00-00.'
  return cleanPhone.length < 11 ? 'Номер должен быть полным: +7 (777) 000-00-00.' : ''
})

const passwordChecks = computed(() => {
  const value = password.value
  return {
    length: value.length >= 12,
    mixedCase: /[A-ZА-Я]/.test(value) && /[a-zа-я]/.test(value),
    number: /[0-9]/.test(value),
    symbol: /[^A-Za-zА-Яа-я0-9]/.test(value),
  }
})

const passwordChecklist = computed(() => [
  { label: '12+ символов', met: passwordChecks.value.length },
  { label: 'Буквы в разном регистре', met: passwordChecks.value.mixedCase },
  { label: 'Хотя бы одна цифра', met: passwordChecks.value.number },
  { label: 'Хотя бы один спецсимвол', met: passwordChecks.value.symbol },
])

const strengthScore = computed(() => Object.values(passwordChecks.value).filter(Boolean).length)
const strengthClass = computed(() => ['is-empty', 'is-weak', 'is-fair', 'is-good', 'is-strong'][strengthScore.value])
const strengthLabel = computed(() => ['Заполните пароль', 'Слабый', 'Базовый', 'Хороший', 'Надёжный'][strengthScore.value])

const passwordError = computed(() => {
  if (!passwordTouched.value && !password.value) return ''
  if (!passwordChecks.value.length) return 'Пароль должен содержать минимум 12 символов.'
  if (!passwordChecks.value.mixedCase) return 'Добавьте буквы в верхнем и нижнем регистре.'
  if (!passwordChecks.value.number) return 'Добавьте хотя бы одну цифру.'
  if (!passwordChecks.value.symbol) return 'Добавьте хотя бы один спецсимвол.'
  return ''
})

const confirmPasswordError = computed(() => {
  if (!confirmPasswordTouched.value && !confirmPassword.value) return ''
  if (!confirmPassword.value) return 'Повторите пароль.'
  return password.value === confirmPassword.value ? '' : 'Пароли не совпадают.'
})

function fieldState(touched, fieldError) {
  return {
    'is-active': touched && !fieldError,
    'is-error': touched && !!fieldError,
  }
}

function validateStepOne() {
  emailTouched.value = true
  passwordTouched.value = true
  confirmPasswordTouched.value = true

  if (emailError.value) return emailError.value
  if (passwordError.value) return passwordError.value
  if (confirmPasswordError.value) return confirmPasswordError.value
  return ''
}

function validateStepTwo() {
  nameTouched.value = true
  phoneTouched.value = true
  cityTouched.value = true
  schoolTouched.value = true
  rulesTouched.value = true

  if (nameError.value) return nameError.value
  if (phoneError.value) return phoneError.value
  if (cityError.value) return cityError.value
  if (schoolError.value) return schoolError.value
  if (!rulesAccepted.value) return 'Подтвердите согласие с правилами участия.'
  return ''
}

function goToStepTwo() {
  error.value = ''
  const stepError = validateStepOne()
  if (stepError) {
    error.value = stepError
    return
  }

  currentStep.value = 2
}

function goBack() {
  error.value = ''
  currentStep.value = 1
}

async function handleRegister() {
  error.value = ''

  const stepOneError = validateStepOne()
  if (stepOneError) {
    currentStep.value = 1
    error.value = stepOneError
    return
  }

  const stepTwoError = validateStepTwo()
  if (stepTwoError) {
    error.value = stepTwoError
    return
  }

  try {
    loading.value = true
    const pow = await solveProofOfWork('register')
    const response = await api.post('/auth/register', {
      name: name.value.trim(),
      email: email.value.trim(),
      school: school.value.trim(),
      city: city.value.trim(),
      phone: phone.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
      ...pow,
    })

    success.value = true
    userStore.setAuth(response.data.user, response.data.token)
    setTimeout(() => router.push('/profile'), 500)
  } catch (err) {
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)[0][0]
    } else if (err.response?.status >= 400) {
      error.value = err.response?.data?.message || 'Не удалось завершить регистрацию. Попробуйте ещё раз.'
    } else {
      error.value = 'Ошибка сервера. Попробуйте ещё раз.'
    }
  } finally {
    loading.value = false
  }
}

function formatPhone(e) {
  phoneTouched.value = true
  let value = e.target.value.replace(/\D/g, '')
  if (!value.startsWith('7')) value = '7' + value
  value = value.substring(0, 11)
  let formatted = '+7'
  if (value.length > 1) formatted += ' (' + value.substring(1, 4)
  if (value.length >= 4) formatted += ') ' + value.substring(4, 7)
  if (value.length >= 7) formatted += '-' + value.substring(7, 9)
  if (value.length >= 9) formatted += '-' + value.substring(9, 11)
  phone.value = formatted
}

watch(email, () => {
  emailTouched.value = true
  error.value = ''
})

watch(password, () => {
  passwordTouched.value = true
  error.value = ''
})

watch(confirmPassword, () => {
  confirmPasswordTouched.value = true
  error.value = ''
})

watch(name, () => {
  nameTouched.value = true
  error.value = ''
})

watch(phone, () => {
  phoneTouched.value = true
  error.value = ''
})

watch(city, () => {
  cityTouched.value = true
  error.value = ''
})

watch(school, () => {
  schoolTouched.value = true
  error.value = ''
})

watch(rulesAccepted, () => {
  rulesTouched.value = true
  error.value = ''
})
</script>

<style scoped>
* { box-sizing: border-box; }

.register-page {
  min-height: 100vh;
  padding: 32px 20px;
  background:
    radial-gradient(circle at top left, rgba(201, 168, 76, 0.14), transparent 24%),
    radial-gradient(circle at bottom right, rgba(79, 167, 116, 0.08), transparent 24%),
    var(--bg);
}

.register-shell {
  width: min(1180px, 100%);
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(320px, 410px) minmax(0, 1fr);
  gap: 24px;
  align-items: start;
}

.register-side,
.register-card {
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-soft);
}

.register-side {
  padding: 32px;
  display: grid;
  gap: 20px;
  background:
    linear-gradient(160deg, rgba(255, 249, 238, 0.96), rgba(240, 232, 214, 0.84)),
    var(--surface);
}

.side-copy {
  display: grid;
  gap: 12px;
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

.lead,
.register-copy,
.field-message,
.form-footer,
.journey-step__body p,
.benefit-list,
.step-summary__label,
.summary-copy,
.form-section__head p {
  color: var(--text-secondary);
}

.journey-steps {
  display: grid;
  gap: 14px;
}

.journey-step {
  display: grid;
  grid-template-columns: 52px 1fr;
  gap: 14px;
  padding: 16px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(141, 111, 49, 0.14);
  background: rgba(255, 252, 244, 0.78);
}

.journey-step.is-active {
  border-color: rgba(201, 171, 99, 0.26);
  box-shadow: 0 16px 38px rgba(141, 107, 25, 0.08);
}

.journey-step__icon {
  width: 52px;
  height: 52px;
  display: grid;
  place-items: center;
  border-radius: 18px;
  background: var(--accent-soft);
  color: var(--accent-strong);
  font-size: 14px;
  font-weight: 800;
}

.journey-step__body {
  display: grid;
  gap: 4px;
}

.journey-step__meta {
  font-size: 12px;
  font-weight: 700;
  color: var(--text-secondary);
}

.benefit-card {
  padding: 18px 20px;
  border-radius: var(--radius-md);
  background: rgba(79, 167, 116, 0.08);
  border: 1px solid rgba(79, 167, 116, 0.16);
  display: grid;
  gap: 10px;
}

.benefit-list {
  margin: 0;
  padding-left: 18px;
  display: grid;
  gap: 8px;
}

.register-card {
  padding: 32px;
  display: grid;
  gap: 22px;
}

.register-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 16px;
}

.step-summary {
  min-width: 120px;
  padding: 14px 16px;
  border-radius: 18px;
  background: rgba(255, 252, 244, 0.82);
  border: 1px solid rgba(201, 171, 99, 0.18);
  display: grid;
  gap: 4px;
}

.progress-card {
  padding: 18px 20px;
  border-radius: var(--radius-md);
  background: rgba(255, 252, 244, 0.82);
  border: 1px solid rgba(201, 171, 99, 0.18);
  display: grid;
  gap: 14px;
}

.progress-card__top {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.progress-track {
  width: 100%;
  height: 10px;
  border-radius: 999px;
  background: rgba(100, 83, 41, 0.12);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  transition: width 0.25s ease;
}

.progress-steps {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.progress-step {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--text-secondary);
}

.progress-step span {
  width: 28px;
  height: 28px;
  display: grid;
  place-items: center;
  border-radius: 999px;
  background: rgba(141, 111, 49, 0.12);
  font-size: 13px;
  font-weight: 700;
}

.progress-step.is-current,
.progress-step.is-done {
  color: var(--text);
}

.progress-step.is-current span,
.progress-step.is-done span {
  background: var(--accent-soft);
  color: var(--accent-strong);
}

.register-form,
.form-section {
  display: grid;
  gap: 18px;
}

.form-section__head {
  display: grid;
  gap: 6px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.field {
  display: grid;
  gap: 8px;
  padding: 16px;
  border-radius: var(--radius-md);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 245, 0.95);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
}

.field:hover {
  transform: translateY(-1px);
}

.field.is-active {
  border-color: rgba(201, 171, 99, 0.3);
  box-shadow: 0 14px 36px rgba(201, 171, 99, 0.1);
}

.field.is-error {
  border-color: rgba(198, 90, 90, 0.34);
  box-shadow: 0 12px 28px rgba(198, 90, 90, 0.08);
}

.field-wide {
  grid-column: 1 / -1;
}

.field input {
  width: 100%;
  min-height: 52px;
  border: 1px solid var(--surface-border);
  border-radius: 16px;
  background: rgba(255, 252, 245, 0.95);
  padding: 0 16px;
  color: var(--text);
}

.field-message {
  font-size: 13px;
  line-height: 1.45;
}

.field.is-error .field-message {
  color: #b45309;
}

.password-wrap {
  position: relative;
}

.password-wrap input {
  padding-right: 96px;
}

.toggle-btn {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  border: 0;
  background: transparent;
  color: var(--accent-strong);
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.password-helper {
  padding: 18px 20px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(201, 171, 99, 0.18);
  background: rgba(255, 252, 244, 0.82);
  display: grid;
  gap: 14px;
}

.password-helper__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.strength-badge {
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
  background: rgba(100, 83, 41, 0.12);
  color: var(--text-secondary);
}

.strength-badge.is-weak {
  background: #fee2e2;
  color: #b91c1c;
}

.strength-badge.is-fair {
  background: #fef3c7;
  color: #b45309;
}

.strength-badge.is-good {
  background: rgba(201, 171, 99, 0.16);
  color: var(--accent-strong);
}

.strength-badge.is-strong {
  background: #dcfce7;
  color: #15803d;
}

.password-checklist {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 10px;
}

.password-checklist li {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--text-secondary);
  font-size: 14px;
}

.password-checklist li span {
  width: 18px;
  text-align: center;
}

.password-checklist li.is-met {
  color: #15803d;
}

.agreement-box {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  padding: 16px 18px;
  border-radius: 20px;
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 245, 0.95);
  color: var(--text-secondary);
}

.agreement-box.is-error {
  border-color: rgba(198, 90, 90, 0.34);
}

.agreement-box input {
  margin-top: 3px;
  width: 16px;
  height: 16px;
  accent-color: var(--success-soft);
}

.message {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  padding: 16px 18px;
  border-radius: 20px;
}

.message__icon {
  width: 24px;
  height: 24px;
  display: grid;
  place-items: center;
  border-radius: 999px;
  font-weight: 800;
}

.message.success {
  background: #ecfdf3;
  color: #166534;
  border: 1px solid rgba(34, 197, 94, 0.18);
}

.message.success .message__icon {
  background: rgba(34, 197, 94, 0.14);
}

.message.error {
  background: #fff7ed;
  color: #9a3412;
  border: 1px solid rgba(249, 115, 22, 0.18);
}

.message.error .message__icon {
  background: rgba(249, 115, 22, 0.12);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.submit-btn,
.secondary-btn {
  min-height: 56px;
  border-radius: 18px;
  padding: 0 22px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
}

.submit-btn {
  min-width: 180px;
  border: 0;
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  color: var(--text);
  box-shadow: 0 18px 40px rgba(201, 171, 99, 0.24);
}

.submit-btn:hover:not(:disabled),
.secondary-btn:hover {
  transform: translateY(-1px);
}

.submit-btn:disabled {
  opacity: 0.72;
  cursor: not-allowed;
  box-shadow: none;
}

.secondary-btn {
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 245, 0.95);
  color: var(--accent-strong);
}

.button-loader {
  width: 16px;
  height: 16px;
  display: inline-block;
  margin-right: 8px;
  border: 2px solid rgba(44, 44, 42, 0.2);
  border-top-color: var(--text);
  border-radius: 999px;
  animation: spin 0.8s linear infinite;
  vertical-align: -3px;
}

.form-footer a {
  color: var(--accent-strong);
  font-weight: 700;
  text-decoration: none;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 980px) {
  .register-shell {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .register-page {
    padding: 16px 12px 28px;
  }

  .register-side,
  .register-card {
    padding: 22px 18px;
  }

  .register-header,
  .progress-card__top,
  .password-helper__head,
  .form-actions {
    grid-template-columns: 1fr;
    display: grid;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .field-wide {
    grid-column: auto;
  }

  .submit-btn,
  .secondary-btn {
    width: 100%;
  }
}
</style>
