<template>
  <AuthShell wide>
    <div>
      <div v-if="success" class="success-screen" role="status">
        <div class="success-icon" aria-hidden="true">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
        </div>
        <p class="auth-eyebrow">Готово!</p>
        <h1>Вы успешно зарегистрировались</h1>
        <p class="auth-sub">Переходим в личный кабинет...</p>
        <span class="success-bar" aria-hidden="true"><span></span></span>
      </div>

      <template v-else>
        <header class="auth-head">
          <p class="auth-eyebrow">Регистрация</p>
          <h1>Создайте аккаунт</h1>
        </header>

        <form @submit.prevent="handleRegister" class="auth-form" novalidate>
          <div class="form-grid">
            <label class="ds-field field-wide">
              <span>Имя и фамилия</span>
              <input v-model="name" class="ds-input" :class="fieldState(nameTouched, nameError)" type="text" placeholder="Алия Ержанова" autocomplete="name" required />
              <small v-if="nameTouched && nameError" class="ds-error-text">{{ nameError }}</small>
            </label>

            <label class="ds-field field-wide">
              <span>Email</span>
              <input v-model="email" class="ds-input" :class="fieldState(emailTouched, emailError)" type="email" inputmode="email" placeholder="you@example.com" autocomplete="email" required />
              <small v-if="emailTouched && emailError" class="ds-error-text">{{ emailError }}</small>
            </label>

            <label class="ds-field">
              <span>Телефон</span>
              <input
                v-model="phone"
                class="ds-input"
                :class="fieldState(phoneTouched, phoneError)"
                type="tel"
                inputmode="tel"
                maxlength="18"
                placeholder="+7 (777) 000-00-00"
                autocomplete="tel"
                @keydown="handlePhoneKeydown"
                @input="formatPhone"
                required
              />
              <small v-if="phoneTouched && phoneError" class="ds-error-text">{{ phoneError }}</small>
            </label>

            <label class="ds-field">
              <span>Город</span>
              <input v-model="city" class="ds-input" :class="fieldState(cityTouched, cityError)" type="text" list="kz-cities-register" placeholder="Астана" autocomplete="address-level2" required />
              <datalist id="kz-cities-register">
                <option v-for="c in KZ_CITIES" :key="c" :value="c" />
              </datalist>
              <small v-if="cityTouched && cityError" class="ds-error-text">{{ cityError }}</small>
            </label>

            <label class="ds-field field-wide">
              <span>Школа</span>
              <input v-model="school" class="ds-input" :class="fieldState(schoolTouched, schoolError)" type="text" placeholder="Лицей №12" autocomplete="organization" required />
              <small v-if="schoolTouched && schoolError" class="ds-error-text">{{ schoolError }}</small>
            </label>

            <label class="ds-field">
              <span>Пароль</span>
              <div class="password-wrap">
                <input
                  v-model="password"
                  class="ds-input"
                  :class="fieldState(passwordTouched, passwordError)"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Минимум 12 символов"
                  autocomplete="new-password"
                  aria-describedby="password-rules"
                  required
                />
                <button type="button" class="password-toggle" :aria-label="showPassword ? 'Скрыть' : 'Показать'" @click="showPassword = !showPassword">
                  <svg v-if="showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.9 17.9A10.1 10.1 0 0 1 12 20c-7 0-11-8-11-8a18.5 18.5 0 0 1 5.1-5.9M9.9 4.2A9.1 9.1 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.2 3.2M14.1 14.1a3 3 0 1 1-4.2-4.2M1 1l22 22"/></svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </label>

            <label class="ds-field">
              <span>Повторите пароль</span>
              <div class="password-wrap">
                <input
                  v-model="confirmPassword"
                  class="ds-input"
                  :class="fieldState(confirmPasswordTouched, confirmPasswordError)"
                  :type="showConfirm ? 'text' : 'password'"
                  placeholder="Повторите пароль"
                  autocomplete="new-password"
                  required
                />
                <button type="button" class="password-toggle" :aria-label="showConfirm ? 'Скрыть' : 'Показать'" @click="showConfirm = !showConfirm">
                  <svg v-if="showConfirm" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.9 17.9A10.1 10.1 0 0 1 12 20c-7 0-11-8-11-8a18.5 18.5 0 0 1 5.1-5.9M9.9 4.2A9.1 9.1 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.2 3.2M14.1 14.1a3 3 0 1 1-4.2-4.2M1 1l22 22"/></svg>
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <small v-if="confirmPasswordTouched && confirmPasswordError" class="ds-error-text">{{ confirmPasswordError }}</small>
            </label>

            <ul id="password-rules" class="password-rules field-wide" aria-label="Требования к паролю">
              <li :class="{ 'is-ok': passwordChecks.length }">Минимум 12 символов</li>
              <li :class="{ 'is-ok': passwordChecks.mixedCase }">Заглавные и строчные буквы</li>
              <li :class="{ 'is-ok': passwordChecks.number }">Хотя бы одна цифра</li>
              <li :class="{ 'is-ok': passwordChecks.symbol }">Хотя бы один спецсимвол</li>
            </ul>
          </div>

          <label class="agreement-box" :class="{ 'is-error': rulesTouched && !rulesAccepted, 'is-checked': rulesAccepted }">
            <input v-model="rulesAccepted" class="agreement-box__input" type="checkbox" />
            <span class="agreement-box__check" aria-hidden="true">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <span>Подтверждаю <RouterLink to="/rules" target="_blank" rel="noopener">правила участия</RouterLink> и условия платформы.</span>
          </label>

          <div v-if="error" class="ds-msg error" role="alert">
            <p><strong>Проверьте форму:</strong> {{ error }}</p>
          </div>

          <button type="submit" class="ds-btn ds-btn-primary ds-btn-lg ds-btn-block" :disabled="loading || success">
            <span v-if="loading" class="ds-spinner" aria-hidden="true"></span>
            {{ loading ? 'Создаём аккаунт...' : 'Создать аккаунт' }}
          </button>
        </form>

        <footer class="auth-foot">
          <p>Уже есть аккаунт? <RouterLink to="/login">Войти</RouterLink></p>
        </footer>
      </template>
    </div>
  </AuthShell>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { solveProofOfWork } from '../js/pow'
import { useUserStore } from '../stores/user'
import { KZ_CITIES } from '../js/kazakhstanData'
import AuthShell from '../components/AuthShell.vue'
import '../css/auth.css'

const router = useRouter()
const userStore = useUserStore()

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

const emailError = computed(() => {
  const value = email.value.trim()
  if (!value) return 'Введите email.'
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) ? '' : 'Проверьте формат email, например name@example.com.'
})

const nameError = computed(() => (name.value.trim() ? '' : 'Укажите имя и фамилию.'))
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

async function handleRegister() {
  error.value = ''

  emailTouched.value = true
  passwordTouched.value = true
  confirmPasswordTouched.value = true
  nameTouched.value = true
  phoneTouched.value = true
  cityTouched.value = true
  schoolTouched.value = true
  rulesTouched.value = true

  const firstError =
    emailError.value ||
    passwordError.value ||
    confirmPasswordError.value ||
    nameError.value ||
    phoneError.value ||
    cityError.value ||
    schoolError.value ||
    (!rulesAccepted.value ? 'Подтвердите согласие с правилами участия.' : '')

  if (firstError) {
    error.value = firstError
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
    window.scrollTo({ top: 0, behavior: 'smooth' })
    setTimeout(() => router.push('/profile'), 2000)
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
  if (!value) {
    phone.value = ''
    return
  }
  if (!value.startsWith('7')) value = '7' + value
  value = value.substring(0, 11)
  let formatted = '+7'
  if (value.length > 1) formatted += ' (' + value.substring(1, 4)
  if (value.length >= 4) formatted += ') ' + value.substring(4, 7)
  if (value.length >= 7) formatted += '-' + value.substring(7, 9)
  if (value.length >= 9) formatted += '-' + value.substring(9, 11)
  phone.value = formatted
}

function handlePhoneKeydown(e) {
  if (e.key !== 'Backspace') return
  const input = e.target
  const pos = input.selectionStart
  if (pos !== input.selectionEnd) return
  const val = input.value
  if (pos > 0 && /\D/.test(val[pos - 1])) {
    e.preventDefault()
    let p = pos - 1
    while (p > 0 && /\D/.test(val[p - 1])) p--
    if (p === 0) return
    const digits = (val.slice(0, p - 1) + val.slice(pos)).replace(/\D/g, '')
    if (!digits) {
      phone.value = ''
      return
    }
    let d = digits.startsWith('7') ? digits : '7' + digits
    d = d.substring(0, 11)
    let formatted = '+7'
    if (d.length > 1) formatted += ' (' + d.substring(1, 4)
    if (d.length >= 4) formatted += ') ' + d.substring(4, 7)
    if (d.length >= 7) formatted += '-' + d.substring(7, 9)
    if (d.length >= 9) formatted += '-' + d.substring(9, 11)
    phone.value = formatted
    nextTick(() => {
      const newPos = Math.max(0, p - 1)
      input.setSelectionRange(newPos, newPos)
    })
  }
}

watch(email, () => { emailTouched.value = true; error.value = '' })
watch(password, () => { passwordTouched.value = true; error.value = '' })
watch(confirmPassword, () => { confirmPasswordTouched.value = true; error.value = '' })
watch(name, () => { nameTouched.value = true; error.value = '' })
watch(phone, () => { phoneTouched.value = true; error.value = '' })
watch(city, () => { cityTouched.value = true; error.value = '' })
watch(school, () => { schoolTouched.value = true; error.value = '' })
watch(rulesAccepted, () => { rulesTouched.value = true; error.value = '' })
</script>

<style scoped>
.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.field-wide { grid-column: 1 / -1; }

.ds-input.is-active:not(:focus) {
  border-color: color-mix(in srgb, var(--success) 55%, var(--border-strong));
}

/* Живой чек-лист требований к паролю */
.password-rules {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 6px 16px;
  margin: -2px 0 0;
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  background: var(--bg);
  list-style: none;
}

.password-rules li {
  position: relative;
  padding-left: 24px;
  color: var(--text-secondary);
  font-size: 13.5px;
  transition: color var(--dur) ease;
}

.password-rules li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid var(--border-strong);
  transform: translateY(-50%);
  transition: background-color var(--dur) ease, border-color var(--dur) ease;
}

.password-rules li.is-ok { color: var(--success-ink); }

.password-rules li.is-ok::before {
  border-color: var(--success);
  background: var(--success) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E") center / 10px no-repeat;
}

/* Согласие */
.agreement-box {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--border-strong);
  background: var(--card);
  color: var(--text);
  font-size: 15px;
  line-height: 1.5;
  cursor: pointer;
  transition: border-color var(--dur) ease, background-color var(--dur) ease;
}

.agreement-box a {
  color: var(--brand-ink);
  font-weight: 600;
}

.agreement-box.is-checked { border-color: var(--brand); background: var(--brand-softer); }
.agreement-box.is-error { border-color: var(--danger); }
.agreement-box:has(.agreement-box__input:focus-visible) { box-shadow: var(--focus-ring); }

.agreement-box__input {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
}

.agreement-box__check {
  width: 22px;
  height: 22px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  margin-top: 1px;
  border-radius: 7px;
  border: 2px solid var(--border-strong);
  background: var(--card);
  color: transparent;
  transition: background-color var(--dur) ease, border-color var(--dur) ease, color var(--dur) ease;
}

.agreement-box.is-checked .agreement-box__check {
  border-color: var(--brand);
  background: var(--brand);
  color: #ffffff;
}

/* Экран успеха */
.success-screen {
  display: grid;
  justify-items: center;
  gap: 10px;
  padding: 24px 0;
  text-align: center;
}

.success-icon {
  width: 84px;
  height: 84px;
  display: grid;
  place-items: center;
  margin-bottom: 8px;
  border-radius: 26px;
  background: var(--success);
  color: #ffffff;
  box-shadow: 0 16px 36px rgba(18, 160, 101, 0.32);
  animation: pop-in 520ms var(--ease-out) both;
}

.success-bar {
  width: 180px;
  height: 6px;
  margin-top: 10px;
  border-radius: var(--radius-pill);
  background: var(--bg-alt);
  overflow: hidden;
}

.success-bar span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: var(--brand);
  animation: fill-bar 2s linear forwards;
}

@keyframes pop-in {
  from { transform: scale(0.6) rotate(-12deg); opacity: 0; }
  to   { transform: none; opacity: 1; }
}

@keyframes fill-bar {
  from { width: 0; }
  to   { width: 100%; }
}

@media (max-width: 560px) {
  .form-grid { grid-template-columns: 1fr; }
  .field-wide { grid-column: auto; }
  .password-rules { grid-template-columns: 1fr; }
}
</style>
