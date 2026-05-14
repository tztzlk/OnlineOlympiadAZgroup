<template>
  <div class="register-page">
    <div class="register-shell">
      <section class="register-card">
        <header class="register-header">
          <p class="eyebrow">Регистрация</p>
          <h2>Создайте аккаунт</h2>
        </header>

        <div v-if="success" class="success-screen">
          <div class="success-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div class="success-body">
            <p class="eyebrow">Готово!</p>
            <h2>Вы успешно зарегистрировались</h2>
            <p class="success-hint">Переходим в личный кабинет...</p>
          </div>
        </div>

        <form v-if="!success" @submit.prevent="handleRegister" class="register-form" novalidate>
          <div class="form-grid">
            <label class="field field-wide" :class="fieldState(emailTouched, emailError)">
              <span>Email</span>
              <input v-model="email" type="email" placeholder="you@example.com" autocomplete="email" required />
              <small v-if="emailTouched && emailError" class="field-error">{{ emailError }}</small>
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
              <small v-if="passwordTouched && passwordError" class="field-error">{{ passwordError }}</small>
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
              <small v-if="confirmPasswordTouched && confirmPasswordError" class="field-error">{{ confirmPasswordError }}</small>
            </label>

            <label class="field field-wide" :class="fieldState(nameTouched, nameError)">
              <span>Имя и фамилия</span>
              <input v-model="name" type="text" placeholder="Алия Ержанова" autocomplete="name" required />
              <small v-if="nameTouched && nameError" class="field-error">{{ nameError }}</small>
            </label>

            <label class="field" :class="fieldState(phoneTouched, phoneError)">
              <span>Телефон</span>
              <input
                v-model="phone"
                type="tel"
                maxlength="18"
                placeholder="+7 (777) 000-00-00"
                autocomplete="tel"
                @keydown="handlePhoneKeydown"
                @input="formatPhone"
                required
              />
              <small v-if="phoneTouched && phoneError" class="field-error">{{ phoneError }}</small>
            </label>

            <label class="field" :class="fieldState(cityTouched, cityError)">
              <span>Город</span>
              <input v-model="city" type="text" list="kz-cities-register" placeholder="Астана" autocomplete="address-level2" required />
              <datalist id="kz-cities-register">
                <option v-for="c in KZ_CITIES" :key="c" :value="c" />
              </datalist>
              <small v-if="cityTouched && cityError" class="field-error">{{ cityError }}</small>
            </label>

            <label class="field field-wide" :class="fieldState(schoolTouched, schoolError)">
              <span>Школа</span>
              <input v-model="school" type="text" placeholder="Лицей №12" autocomplete="organization" required />
              <small v-if="schoolTouched && schoolError" class="field-error">{{ schoolError }}</small>
            </label>
          </div>

          <label class="agreement-box" :class="{ 'is-error': rulesTouched && !rulesAccepted }">
            <input v-model="rulesAccepted" type="checkbox" />
            <span>Подтверждаю правила участия и условия платформы.</span>
          </label>

          <div v-if="error" class="message error">
            <span class="message__icon" aria-hidden="true">!</span>
            <div>
              <p style="margin:0"><strong>Проверьте форму:</strong> {{ error }}</p>
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="submit-btn" :disabled="loading || success">
              <span v-if="loading" class="button-loader" aria-hidden="true"></span>
              {{ loading ? 'Создаём аккаунт...' : 'Создать аккаунт' }}
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
import { computed, nextTick, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { solveProofOfWork } from '../js/pow'
import { useUserStore } from '../stores/user'
import { KZ_CITIES } from '../js/kazakhstanData'

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
* { box-sizing: border-box; }

.register-page {
  min-height: 100dvh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 32px 20px;
  padding-top: 100px;
  background: var(--bg);
}

.register-shell {
  width: min(560px, 100%);
}

.eyebrow {
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  font-size: 12px;
  font-weight: 800;
  color: var(--green);
}

.register-card {
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-soft);
  padding: 32px;
  display: grid;
  gap: 20px;
}

.register-header {
  display: grid;
  gap: 4px;
}

.register-form {
  display: grid;
  gap: 16px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.field {
  display: grid;
  gap: 6px;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--border);
  background: var(--card);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.field:hover {
  border-color: rgba(17, 24, 39, 0.16);
}

.field.is-active {
  border-color: rgba(22, 163, 74, 0.35);
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.08);
}

.field.is-error {
  border-color: rgba(220, 38, 38, 0.4);
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.07);
}

.field-wide {
  grid-column: 1 / -1;
}

.field span {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-secondary);
}

.field input {
  width: 100%;
  min-height: 44px;
  border: 1.5px solid var(--border);
  border-radius: 6px;
  background: var(--bg-alt);
  padding: 0 14px;
  color: var(--text);
  font-size: 15px;
  transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.field input:focus {
  outline: none;
  border-color: rgba(22, 163, 74, 0.34);
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.08);
}

.field-error {
  font-size: 12px;
  color: #b45309;
  line-height: 1.4;
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
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

.agreement-box {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--border);
  background: var(--card);
  color: var(--text-secondary);
  font-size: 14px;
  cursor: pointer;
}

.agreement-box.is-error {
  border-color: rgba(198, 90, 90, 0.34);
}

.agreement-box input {
  margin-top: 2px;
  width: 16px;
  height: 16px;
  flex-shrink: 0;
  accent-color: var(--green);
}

.success-screen {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 20px;
  padding: 20px 0 8px;
}

.success-icon {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #dcfce7;
  border: 2px solid rgba(34, 197, 94, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #15803d;
  animation: pop-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.success-body h2 {
  margin: 8px 0 0;
}

.success-hint {
  margin: 8px 0 0;
  color: var(--text-secondary);
  font-size: 15px;
}

@keyframes pop-in {
  from { transform: scale(0.5); opacity: 0; }
  to   { transform: scale(1);   opacity: 1; }
}

.message {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  padding: 14px 16px;
  border-radius: 8px;
}

.message__icon {
  width: 24px;
  height: 24px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  font-weight: 800;
  flex-shrink: 0;
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
}

.submit-btn {
  min-height: 52px;
  min-width: 180px;
  border-radius: 8px;
  padding: 0 24px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  border: none;
  background: var(--green);
  color: #ffffff;
  box-shadow: 0 4px 16px rgba(22, 163, 74, 0.22);
  transition: background 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.submit-btn:hover:not(:disabled) {
  background: var(--green-hover);
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.28);
}

.submit-btn:active:not(:disabled) {
  transform: scale(0.98);
  transition-duration: 0.08s;
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

.button-loader {
  width: 16px;
  height: 16px;
  display: inline-block;
  margin-right: 8px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  vertical-align: -3px;
}

.form-footer {
  text-align: center;
  color: var(--text-secondary);
  font-size: 14px;
}

.form-footer a {
  color: var(--green);
  font-weight: 700;
  text-decoration: none;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 560px) {
  .register-page {
    padding: 16px 12px 28px;
    padding-top: 88px;
  }

  .register-card {
    padding: 22px 18px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .field-wide {
    grid-column: auto;
  }

  .submit-btn {
    width: 100%;
  }
}
</style>
