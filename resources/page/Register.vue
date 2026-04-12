<template>
  <div class="register-page">
    <div class="register-shell">
      <section class="register-side">
        <p class="eyebrow">Новый участник</p>
        <h1>Создайте родительский аккаунт и откройте доступ к олимпиадам</h1>
        <p class="lead">
          После регистрации вы сможете добавить ребёнка, выбрать предмет, отслеживать статус заявки, оплату, результаты и сертификаты.
        </p>

        <div class="step-list">
          <article v-for="(step, index) in steps" :key="step.title" class="step-card">
            <span class="step-index">{{ index + 1 }}</span>
            <div>
              <strong>{{ step.title }}</strong>
              <p>{{ step.text }}</p>
            </div>
          </article>
        </div>

        <div class="trust-box">
          <strong>Что будет дальше?</strong>
          <p>
            Сразу после входа вы попадёте в личный кабинет, где сможете добавить ребёнка и выбрать подходящую олимпиаду.
          </p>
        </div>
      </section>

      <section class="register-card">
        <div class="form-header">
          <p class="eyebrow">Регистрация</p>
          <h2>Откройте личный кабинет</h2>
          <p>{{ currentStepDescription }}</p>
        </div>

        <div class="progress-box" aria-label="Прогресс регистрации">
          <div class="progress-box__top">
            <strong>Шаг {{ currentStep }} из 2</strong>
            <span>{{ currentStepTitle }}</span>
          </div>
          <div class="progress-track">
            <div class="progress-fill" :style="{ width: progressWidth }"></div>
          </div>
        </div>

        <form @submit.prevent="handleRegister" class="register-form" novalidate>
          <div v-if="currentStep === 1" class="form-grid">
            <label class="field field-wide">
              <span>Email</span>
              <input v-model="email" type="email" placeholder="you@example.com" required />
              <small class="field-hint" :class="{ 'is-error': emailTouched && emailError }">
                {{ emailTouched && emailError ? emailError : 'Используйте email, на который удобно получать уведомления и входить в кабинет.' }}
              </small>
            </label>

            <label class="field">
              <span>Пароль</span>
              <div class="password-wrap">
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Минимум 12 символов"
                  required
                />
                <button type="button" class="toggle-btn" @click="showPassword = !showPassword">
                  {{ showPassword ? 'Скрыть' : 'Показать' }}
                </button>
              </div>
            </label>

            <label class="field">
              <span>Подтвердите пароль</span>
              <div class="password-wrap">
                <input
                  v-model="confirmPassword"
                  :type="showConfirm ? 'text' : 'password'"
                  placeholder="Повторите пароль"
                  required
                />
                <button type="button" class="toggle-btn" @click="showConfirm = !showConfirm">
                  {{ showConfirm ? 'Скрыть' : 'Показать' }}
                </button>
              </div>
            </label>
          </div>

          <div v-else class="form-grid">
            <label class="field field-wide">
              <span>Имя и фамилия родителя</span>
              <input v-model="name" type="text" placeholder="Иван Иванов" required />
              <small class="field-hint" :class="{ 'is-error': nameTouched && nameError }">
                {{ nameTouched && nameError ? nameError : 'Укажите имя взрослого, который оформляет участие и оплачивает заявку.' }}
              </small>
            </label>

            <label class="field">
              <span>Телефон</span>
              <input
                v-model="phone"
                type="tel"
                maxlength="18"
                placeholder="+7 (___) ___-__-__"
                @input="formatPhone"
                required
              />
              <small class="field-hint" :class="{ 'is-error': phoneTouched && phoneError }">
                {{ phoneTouched && phoneError ? phoneError : 'Введите номер в формате +7 (777) 000-00-00.' }}
              </small>
            </label>

            <label class="field">
              <span>Город</span>
              <input v-model="city" type="text" placeholder="Например: Астана" required />
              <small class="field-hint" :class="{ 'is-error': cityTouched && cityError }">
                {{ cityTouched && cityError ? cityError : 'Город нужен для профиля участника и сертификатов.' }}
              </small>
            </label>

            <label class="field field-wide">
              <span>Школа</span>
              <input v-model="school" type="text" placeholder="Например: Лицей №12" required />
              <small class="field-hint" :class="{ 'is-error': schoolTouched && schoolError }">
                {{ schoolTouched && schoolError ? schoolError : 'Школа отобразится в профиле и может использоваться в результатах.' }}
              </small>
            </label>
          </div>

          <div v-if="currentStep === 1 && password" class="strength-box">
            <div class="strength-track">
              <div class="strength-fill" :style="{ width: strengthWidth }" :class="strengthClass"></div>
            </div>
            <span class="strength-label" :class="strengthClass">{{ strengthLabel }}</span>
          </div>

          <div class="notice-box">
            {{ currentStepNotice }}
          </div>

          <label v-if="currentStep === 2" class="agreement-box">
            <input v-model="rulesAccepted" type="checkbox" />
            <span>
              Я ознакомлен с правилами участия и условиями платформы. Подробные правила доступны на странице выбранной олимпиады.
            </span>
          </label>

          <div v-if="error" class="message error">{{ error }}</div>

          <div class="form-actions">
            <button v-if="currentStep === 2" type="button" class="secondary-btn" @click="currentStep = 1">
              Назад
            </button>
            <button v-if="currentStep === 1" type="button" class="submit-btn" @click="goToStepTwo">
              Продолжить
            </button>
            <button v-else type="submit" class="submit-btn" :disabled="loading">
              {{ loading ? 'Создаём аккаунт...' : 'Зарегистрироваться' }}
            </button>
          </div>
        </form>

        <div class="form-footer">
          <p>Уже есть аккаунт? <RouterLink to="/login">Войти</RouterLink></p>
        </div>
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
const showPassword = ref(false)
const showConfirm = ref(false)
const emailTouched = ref(false)
const nameTouched = ref(false)
const phoneTouched = ref(false)
const cityTouched = ref(false)
const schoolTouched = ref(false)
const rulesAccepted = ref(false)

const steps = [
  { title: 'Создайте родительский аккаунт', text: 'Введите базовые контакты, чтобы получить доступ к личному кабинету.' },
  { title: 'Добавьте ребёнка в профиль', text: 'Внутри кабинета можно указать данные участника и выбрать язык.' },
  { title: 'Оформите заявку и следите за статусом', text: 'Вы будете видеть проверку, оплату, доступ к тесту и результаты.' },
]

const currentStepTitle = computed(() => (
  currentStep.value === 1 ? 'Доступ к кабинету' : 'Данные родителя'
))

const currentStepDescription = computed(() => (
  currentStep.value === 1
    ? 'Сначала задайте email и пароль, чтобы создать безопасный вход в личный кабинет.'
    : 'Теперь нужны данные родителя. Они понадобятся для заявок, оплаты и связи по участию.'
))

const currentStepNotice = computed(() => (
  currentStep.value === 1
    ? 'Для регистрации нужен надёжный пароль: не менее 12 символов, с буквами в разном регистре, цифрами и символом.'
    : 'Контакты родителя используются в заявках, оплате и уведомлениях по олимпиаде. Эти данные можно будет обновить в профиле.'
))

const progressWidth = computed(() => (currentStep.value === 1 ? '50%' : '100%'))
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

const strengthScore = computed(() => {
  const value = password.value
  if (!value) return 0
  let score = 0
  if (value.length >= 12) score += 1
  if (/[A-ZА-Я]/.test(value)) score += 1
  if (/[0-9]/.test(value)) score += 1
  if (/[^A-Za-zА-Яа-я0-9]/.test(value)) score += 1
  return score
})

const strengthWidth = computed(() => ['0%', '25%', '50%', '75%', '100%'][strengthScore.value])
const strengthClass = computed(() => ['', 'weak', 'fair', 'good', 'strong'][strengthScore.value])
const strengthLabel = computed(() => ['', 'Слабый', 'Средний', 'Хороший', 'Надёжный'][strengthScore.value])

const validatePassword = () => {
  if (password.value.length < 12) {
    return 'Пароль должен содержать минимум 12 символов.'
  }

  if (!/[A-ZА-Я]/.test(password.value) || !/[a-zа-я]/.test(password.value)) {
    return 'Добавьте в пароль буквы в верхнем и нижнем регистре.'
  }

  if (!/[0-9]/.test(password.value)) {
    return 'Добавьте в пароль хотя бы одну цифру.'
  }

  if (!/[^A-Za-zА-Яа-я0-9]/.test(password.value)) {
    return 'Добавьте в пароль хотя бы один специальный символ.'
  }

  return ''
}

const validateStepOne = () => {
  if (emailError.value) {
    return emailError.value
  }

  const passwordError = validatePassword()
  if (passwordError) {
    return passwordError
  }

  if (password.value !== confirmPassword.value) {
    return 'Пароли не совпадают.'
  }

  return ''
}

const validateStepTwo = () => {
  if (nameError.value) {
    return nameError.value
  }

  if (schoolError.value) {
    return schoolError.value
  }

  if (cityError.value) {
    return cityError.value
  }

  if (phoneError.value) {
    return phoneError.value
  }

  if (!rulesAccepted.value) {
    return 'Подтвердите, что ознакомились с правилами участия.'
  }

  return ''
}

function goToStepTwo() {
  error.value = ''
  emailTouched.value = true
  const stepError = validateStepOne()
  if (stepError) {
    error.value = stepError
    return
  }

  currentStep.value = 2
}

async function handleRegister() {
  error.value = ''
  emailTouched.value = true
  nameTouched.value = true
  phoneTouched.value = true
  cityTouched.value = true
  schoolTouched.value = true

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

    userStore.setAuth(response.data.user, response.data.token)
    router.push('/profile')
  } catch (err) {
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)[0][0]
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
  if (error.value) error.value = ''
})

watch(name, () => {
  nameTouched.value = true
  if (error.value) error.value = ''
})

watch(phone, () => {
  phoneTouched.value = true
  if (error.value) error.value = ''
})

watch(city, () => {
  cityTouched.value = true
  if (error.value) error.value = ''
})

watch(school, () => {
  schoolTouched.value = true
  if (error.value) error.value = ''
})
</script>

<style scoped>
* { box-sizing: border-box; }

.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 32px 20px;
  background:
    radial-gradient(circle at top left, rgba(201, 171, 99, 0.16), transparent 28%),
    radial-gradient(circle at bottom right, rgba(79, 167, 116, 0.14), transparent 24%),
    var(--bg);
}

.register-shell {
  width: min(1180px, 100%);
  display: grid;
  grid-template-columns: 1.02fr 1.08fr;
  gap: 24px;
}

.register-side,
.register-card {
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-soft);
}

.register-side {
  padding: 38px;
  display: grid;
  gap: 22px;
  background:
    linear-gradient(160deg, rgba(255, 249, 238, 0.95), rgba(240, 232, 214, 0.82)),
    var(--surface);
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

.lead {
  color: var(--text-secondary);
  font-size: 17px;
  max-width: 58ch;
}

.step-list {
  display: grid;
  gap: 14px;
}

.step-card {
  display: grid;
  grid-template-columns: 42px 1fr;
  gap: 14px;
  padding: 18px 18px 18px 16px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(141, 111, 49, 0.14);
  background: rgba(255, 252, 244, 0.78);
}

.step-index {
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: var(--accent-soft);
  color: var(--accent-strong);
  font-weight: 800;
}

.step-card strong {
  display: block;
  margin-bottom: 6px;
  font-size: 18px;
}

.step-card p,
.trust-box p {
  color: var(--text-secondary);
}

.trust-box {
  padding: 18px 20px;
  border-radius: var(--radius-md);
  background: rgba(79, 167, 116, 0.08);
  border: 1px solid rgba(79, 167, 116, 0.16);
  display: grid;
  gap: 8px;
}

.register-card {
  padding: 34px;
  display: grid;
  gap: 22px;
}

.form-header {
  display: grid;
  gap: 10px;
}

.form-header p:last-child {
  color: var(--text-secondary);
}

.progress-box {
  display: grid;
  gap: 10px;
  padding: 16px 18px;
  border-radius: var(--radius-md);
  background: rgba(255, 252, 244, 0.82);
  border: 1px solid rgba(201, 171, 99, 0.18);
}

.progress-box__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  color: var(--text-secondary);
  font-size: 14px;
}

.progress-box__top strong {
  color: var(--text);
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

.register-form {
  display: grid;
  gap: 18px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.field {
  display: grid;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
}

.field-hint {
  font-size: 12px;
  font-weight: 500;
  color: var(--text-secondary);
  line-height: 1.5;
}

.field-hint.is-error {
  color: #b45309;
}

.field-wide {
  grid-column: 1 / -1;
}

.field input {
  width: 100%;
  min-height: 54px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 245, 0.95);
  padding: 14px 16px;
  color: var(--text);
}

.password-wrap {
  position: relative;
}

.password-wrap input {
  padding-right: 100px;
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
  cursor: pointer;
}

.strength-box {
  display: flex;
  align-items: center;
  gap: 12px;
}

.strength-track {
  flex: 1;
  height: 8px;
  border-radius: 999px;
  background: rgba(100, 83, 41, 0.12);
  overflow: hidden;
}

.strength-fill {
  height: 100%;
}

.strength-fill.weak,
.strength-label.weak { background: #d9826a; color: #a85745; }
.strength-fill.fair,
.strength-label.fair { background: #d2aa62; color: #a47926; }
.strength-fill.good,
.strength-label.good { background: #91b66a; color: #5e7f3a; }
.strength-fill.strong,
.strength-label.strong { background: var(--success-soft); color: var(--success-soft); }

.strength-label {
  min-width: 92px;
  font-size: 13px;
  font-weight: 700;
  text-align: right;
  background: transparent;
}

.notice-box,
.message {
  border-radius: var(--radius-sm);
  padding: 14px 16px;
  font-size: 14px;
}

.agreement-box {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  padding: 14px 16px;
  border-radius: var(--radius-sm);
  background: rgba(255, 252, 245, 0.92);
  border: 1px solid var(--surface-border);
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 500;
  line-height: 1.55;
}

.agreement-box input {
  margin-top: 2px;
  width: 16px;
  height: 16px;
  accent-color: var(--success-soft);
}

.notice-box {
  background: rgba(201, 171, 99, 0.12);
  color: var(--text-secondary);
  border: 1px solid rgba(201, 171, 99, 0.16);
}

.message.error {
  background: var(--danger-bg);
  color: #8f3b3b;
  border: 1px solid rgba(198, 90, 90, 0.18);
}

.form-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
}

.submit-btn,
.secondary-btn {
  min-height: 56px;
  border-radius: var(--radius-sm);
  font-size: 16px;
  cursor: pointer;
}

.submit-btn {
  border: 0;
  padding: 0 24px;
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  color: var(--text);
  box-shadow: 0 14px 30px rgba(201, 171, 99, 0.24);
}

.secondary-btn {
  border: 1px solid var(--surface-border);
  padding: 0 20px;
  background: rgba(255, 252, 245, 0.95);
  color: var(--accent-strong);
}

.submit-btn:hover:not(:disabled),
.secondary-btn:hover {
  transform: translateY(-1px);
}

.submit-btn:disabled {
  opacity: 0.72;
  cursor: not-allowed;
}

.form-footer {
  color: var(--text-secondary);
  font-size: 14px;
}

.form-footer a {
  color: var(--accent-strong);
  font-weight: 700;
  text-decoration: none;
}

@media (max-width: 920px) {
  .register-shell {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .register-page {
    padding: 20px 14px;
  }

  .register-side,
  .register-card {
    padding: 24px 20px;
  }

  .progress-box__top,
  .form-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .field-wide {
    grid-column: auto;
  }
}
</style>
