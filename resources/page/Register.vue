<template>
  <div class="register-page">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>

    <div class="register-card">
      <!-- Left art panel -->
      <div class="card-art">
        <div class="art-inner">
          <div class="art-logo">
            <svg width="42" height="42" viewBox="0 0 42 42" fill="none">
              <rect width="42" height="42" rx="12" fill="white" fill-opacity="0.15"/>
              <path d="M12 21L21 12L30 21L21 30L12 21Z" stroke="white" stroke-width="2" fill="none"/>
              <circle cx="21" cy="21" r="4" fill="white"/>
            </svg>
          </div>
          <div class="art-text">
            <h2>Добро пожаловать</h2>
            <p>Создайте аккаунт и начните пользоваться всеми возможностями платформы</p>
          </div>
          <div class="art-steps">
            <div class="step" v-for="(s, i) in steps" :key="i">
              <div class="step-num">{{ i + 1 }}</div>
              <span>{{ s }}</span>
            </div>
          </div>
          <div class="art-footer">Версия 2.0 · Безопасно</div>
        </div>
        <div class="art-grid"></div>
      </div>

      <!-- Right form panel -->
      <div class="card-form">
        <div class="form-header">
          <h1>Регистрация</h1>
          <p>Заполните данные для создания аккаунта</p>
        </div>

        <form @submit.prevent="handleRegister" novalidate>
          <div class="fields-grid">

            <div class="field" :class="{ focused: focusedField === 'name', filled: name }">
              <label>Имя</label>
              <div class="input-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="none">
                  <circle cx="10" cy="7" r="3.5" stroke="currentColor" stroke-width="1.5"/>
                  <path d="M3 17c0-3.3 3.1-6 7-6s7 2.7 7 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <input
                  type="text"
                  v-model="name"
                  placeholder="Иван Иванов"
                  @focus="focusedField = 'name'"
                  @blur="focusedField = ''"
                  required
                />
              </div>
            </div>

            <div class="field" :class="{ focused: focusedField === 'email', filled: email }">
              <label>Email</label>
              <div class="input-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="none">
                  <path d="M3 4h14a1 1 0 011 1v10a1 1 0 01-1 1H3a1 1 0 01-1-1V5a1 1 0 011-1z" stroke="currentColor" stroke-width="1.5"/>
                  <path d="M2 6l8 5 8-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
                <input
                  type="email"
                  v-model="email"
                  placeholder="you@example.com"
                  @focus="focusedField = 'email'"
                  @blur="focusedField = ''"
                  required
                />
              </div>
            </div>

            <div class="field full" :class="{ focused: focusedField === 'phone', filled: phone }">
              <label>Номер телефона</label>
              <div class="input-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="none">
                  <path d="M4 2h4l1.5 4-2 1.5a10 10 0 004 4L13 9.5l4 1.5v4a1 1 0 01-1 1C7 16 4 9 4 3a1 1 0 011-1z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <input
                  type="tel"
                  v-model="phone"
                  @input="formatPhone"
                  placeholder="+7 (___) ___-__-__"
                  maxlength="18"
                  @focus="focusedField = 'phone'"
                  @blur="focusedField = ''"
                  required
                />
              </div>
            </div>

            <div class="field" :class="{ focused: focusedField === 'password', filled: password }">
              <label>Пароль</label>
              <div class="input-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="none">
                  <rect x="3" y="8" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
                  <path d="M7 8V6a3 3 0 116 0v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  <circle cx="10" cy="13" r="1.5" fill="currentColor"/>
                </svg>
                <input
                  :type="showPassword ? 'text' : 'password'"
                  v-model="password"
                  placeholder="••••••••"
                  @focus="focusedField = 'password'"
                  @blur="focusedField = ''"
                  required
                />
                <button type="button" class="eye-btn" @click="showPassword = !showPassword" tabindex="-1">
                  <svg v-if="!showPassword" viewBox="0 0 20 20" fill="none">
                    <path d="M1 10s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6z" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                  </svg>
                  <svg v-else viewBox="0 0 20 20" fill="none">
                    <path d="M3 3l14 14M8.5 8.5A2.5 2.5 0 0112.5 12M6 5.5C3.5 7 2 10 2 10s3.5 5 8 5c1.5 0 3-.4 4.2-1M14 13.5C15.8 12 18 10 18 10s-3.5-6-8-6c-.8 0-1.5.1-2.2.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="field" :class="{ focused: focusedField === 'confirm', filled: confirmPassword, match: confirmPassword && password === confirmPassword }">
              <label>Подтвердите пароль</label>
              <div class="input-wrap">
                <svg class="field-icon" viewBox="0 0 20 20" fill="none">
                  <path d="M5 10l4 4 6-7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
                </svg>
                <input
                  :type="showConfirm ? 'text' : 'password'"
                  v-model="confirmPassword"
                  placeholder="••••••••"
                  @focus="focusedField = 'confirm'"
                  @blur="focusedField = ''"
                  required
                />
                <button type="button" class="eye-btn" @click="showConfirm = !showConfirm" tabindex="-1">
                  <svg v-if="!showConfirm" viewBox="0 0 20 20" fill="none">
                    <path d="M1 10s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6z" stroke="currentColor" stroke-width="1.5"/>
                    <circle cx="10" cy="10" r="2.5" stroke="currentColor" stroke-width="1.5"/>
                  </svg>
                  <svg v-else viewBox="0 0 20 20" fill="none">
                    <path d="M3 3l14 14M8.5 8.5A2.5 2.5 0 0112.5 12M6 5.5C3.5 7 2 10 2 10s3.5 5 8 5c1.5 0 3-.4 4.2-1M14 13.5C15.8 12 18 10 18 10s-3.5-6-8-6c-.8 0-1.5.1-2.2.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>
            </div>

          </div>

          <!-- Password strength -->
          <div class="strength-bar" v-if="password">
            <div class="strength-track">
              <div class="strength-fill" :style="{ width: strengthWidth }" :class="strengthClass"></div>
            </div>
            <span class="strength-label" :class="strengthClass">{{ strengthLabel }}</span>
          </div>

          <transition name="err">
            <div v-if="error" class="error-banner">
              <svg viewBox="0 0 20 20" fill="none">
                <circle cx="10" cy="10" r="8" stroke="currentColor" stroke-width="1.5"/>
                <path d="M10 6v5M10 14h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
              {{ error }}
            </div>
          </transition>

          <button type="submit" class="submit-btn" :class="{ loading }" :disabled="loading">
            <span class="btn-text">{{ loading ? 'Создание аккаунта...' : 'Зарегистрироваться' }}</span>
            <div class="btn-spinner" v-if="loading"></div>
            <svg v-else class="btn-arrow" viewBox="0 0 20 20" fill="none">
              <path d="M4 10h12M11 5l5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        </form>

        <div class="form-footer">
          <p>Уже есть аккаунт? <router-link to="/login">Войти</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { useUserStore } from '../stores/user'

const router = useRouter()
const userStore = useUserStore()

const name = ref('')
const email = ref('')
const phone = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)
const showConfirm = ref(false)
const focusedField = ref('')

const steps = ['Заполните данные формы', 'Подтвердите email', 'Начните работу']

// Password strength
const strengthScore = computed(() => {
  const p = password.value
  if (!p) return 0
  let s = 0
  if (p.length >= 8) s++
  if (/[A-Z]/.test(p)) s++
  if (/[0-9]/.test(p)) s++
  if (/[^A-Za-z0-9]/.test(p)) s++
  return s
})

const strengthWidth = computed(() => ['0%', '25%', '50%', '75%', '100%'][strengthScore.value])
const strengthClass = computed(() => ['', 'weak', 'fair', 'good', 'strong'][strengthScore.value])
const strengthLabel = computed(() => ['', 'Слабый', 'Средний', 'Хороший', 'Надёжный'][strengthScore.value])

async function handleRegister() {
  error.value = ''

  const cleanPhone = phone.value.replace(/\D/g, '')
  if (cleanPhone.length < 11) {
    error.value = 'Введите корректный номер телефона'
    return
  }

  if (password.value !== confirmPassword.value) {
    error.value = 'Пароли не совпадают'
    return
  }

  try {
    loading.value = true

    const response = await api.post('/auth/register', {
      name: name.value,
      email: email.value,
      phone: phone.value,
      password: password.value,
      password_confirmation: confirmPassword.value
    })

    userStore.setAuth(response.data.user, response.data.token)
    router.push('/')

  } catch (err) {
    console.log(err)
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)[0][0]
    } else {
      error.value = 'Ошибка сервера'
    }
  } finally {
    loading.value = false
  }
}

function formatPhone(e) {
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
</script>

<style scoped>
* { box-sizing: border-box; margin: 0; padding: 0; }

.register-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0F0F0F;
  padding: 24px;
  position: relative;
  overflow: hidden;
}

/* --- Orbs --- */
.bg-orbs { position: absolute; inset: 0; pointer-events: none; }

.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.25;
  animation: drift 12s ease-in-out infinite alternate;
}
.orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(225,29,72,0.12), transparent 70%); top: -120px; left: -100px; animation-duration: 14s; }
.orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(225,29,72,0.08), transparent 70%); bottom: -80px; right: -60px; animation-duration: 10s; animation-delay: -4s; }
.orb-3 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(225,29,72,0.06), transparent 70%); top: 40%; left: 50%; opacity: 0.35; animation-duration: 16s; animation-delay: -7s; }

@keyframes drift {
  from { transform: translate(0, 0) scale(1); }
  to { transform: translate(30px, 40px) scale(1.1); }
}

/* --- Card --- */
.register-card {
  display: flex;
  width: 100%;
  max-width: 960px;
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.08);
  position: relative;
  z-index: 1;
  animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes cardIn {
  from { opacity: 0; transform: translateY(30px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/* --- Art panel --- */
.card-art {
  width: 38%;
  background: linear-gradient(145deg, #1A1A1A 0%, #0F0F0F 50%, #1A1A1A 100%);
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  padding: 48px 36px;
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
}

.art-grid {
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px);
  background-size: 40px 40px;
  mask-image: linear-gradient(to bottom right, transparent 20%, black 60%);
}

.art-inner {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  height: 100%;
  gap: 24px;
}

.art-text h2 { font-size: 24px; font-weight: 700; color: white; margin-bottom: 10px; line-height: 1.25; }
.art-text p { font-size: 13px; color: rgba(255,255,255,0.65); line-height: 1.6; }

.art-steps {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-top: auto;
}

.step {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 13px;
  color: rgba(255,255,255,0.8);
}

.step-num {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(255,255,255,0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 600;
  color: white;
  flex-shrink: 0;
}

.art-footer { font-size: 11px; color: rgba(255,255,255,0.35); }

/* --- Form panel --- */
.card-form {
  flex: 1;
  background: #1A1A1A;
  padding: 44px 44px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow-y: auto;
}

.form-header { margin-bottom: 28px; }
.form-header h1 { font-size: 30px; font-weight: 700; color: #FFFFFF; margin-bottom: 6px; letter-spacing: -0.5px; }
.form-header p { font-size: 14px; color: #A1A1AA; }

/* --- Fields grid --- */
.fields-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.field { display: flex; flex-direction: column; gap: 7px; }
.field.full { grid-column: 1 / -1; }

label { font-size: 12.5px; font-weight: 500; color: #6b7280; transition: color 0.2s; }
.field.focused label { color: #E11D48; }
.field.match label { color: #10b981; }

.input-wrap { position: relative; display: flex; align-items: center; }

.field-icon {
  position: absolute;
  left: 12px;
  width: 16px;
  height: 16px;
  color: #d1d5db;
  transition: color 0.2s;
  pointer-events: none;
}

.field.focused .field-icon { color: #E11D48; }
.field.match .field-icon { color: #10b981; }

input {
  width: 100%;
  background: rgba(255, 255, 255, 0.04);
  border: 1.5px solid rgba(255, 255, 255, 0.08);
  border-radius: 11px;
  padding: 11px 38px 11px 36px;
  font-size: 14px;
  color: #FFFFFF;
  caret-color: #E11D48;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  outline: none;
}

input::placeholder { color: #c4c9d4; }

input:focus {
  border-color: #E11D48;
  background: #ffffff;
  color: #111827;
  box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.15);
}

.field.focused .eye-btn,
.field.focused .field-icon {
  color: #6b7280;
}

input:-webkit-autofill,
input:-webkit-autofill:hover,
input:-webkit-autofill:focus {
  -webkit-text-fill-color: #111827;
  -webkit-box-shadow: 0 0 0 1000px #ffffff inset;
  transition: background-color 9999s ease-in-out 0s;
}

.field.match input {
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.08);
}

.eye-btn {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  color: #d1d5db;
  display: flex;
  align-items: center;
  transition: color 0.2s;
}
.eye-btn:hover { color: #6b7280; }
.eye-btn svg { width: 16px; height: 16px; }

/* --- Strength bar --- */
.strength-bar {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 14px;
}

.strength-track {
  flex: 1;
  height: 4px;
  background: #f3f4f6;
  border-radius: 99px;
  overflow: hidden;
}

.strength-fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.4s ease, background 0.3s;
}

.strength-fill.weak { background: #f87171; }
.strength-fill.fair { background: #fb923c; }
.strength-fill.good { background: #facc15; }
.strength-fill.strong { background: #10b981; }

.strength-label {
  font-size: 11px;
  font-weight: 500;
  min-width: 56px;
  text-align: right;
}
.strength-label.weak { color: #f87171; }
.strength-label.fair { color: #fb923c; }
.strength-label.good { color: #ca8a04; }
.strength-label.strong { color: #10b981; }

/* --- Error banner --- */
.error-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 10px;
  padding: 11px 13px;
  font-size: 13px;
  color: #dc2626;
  margin-bottom: 4px;
}
.error-banner svg { width: 15px; height: 15px; flex-shrink: 0; }

.err-enter-active, .err-leave-active { transition: all 0.3s ease; }
.err-enter-from, .err-leave-to { opacity: 0; transform: translateY(-6px); }

/* --- Submit --- */
.submit-btn {
  width: 100%;
  margin-top: 20px;
  padding: 14px 24px;
  border-radius: 12px;
  border: none;
  background: #E11D48;
  color: white;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
  box-shadow: 0 4px 20px rgba(225, 29, 72, 0.35);
}

.submit-btn:hover:not(:disabled) { background: #BE123C; transform: translateY(-1px); box-shadow: 0 8px 28px rgba(225, 29, 72, 0.45); }
.submit-btn:active:not(:disabled) { transform: translateY(0); }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-arrow { width: 17px; height: 17px; flex-shrink: 0; }

.btn-spinner {
  width: 17px;
  height: 17px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* --- Footer --- */
.form-footer { margin-top: 20px; text-align: center; }
.form-footer p { font-size: 14px; color: #A1A1AA; }
.form-footer a { color: #E11D48; text-decoration: none; font-weight: 500; transition: color 0.2s; }
.form-footer a:hover { color: #BE123C; }

/* --- Responsive --- */
@media (max-width: 820px) {
  .card-art { display: none; }
  .card-form { padding: 40px 32px; }
}

@media (max-width: 540px) {
  .fields-grid { grid-template-columns: 1fr; }
  .field.full { grid-column: 1; }
  .card-form { padding: 32px 22px; }
  .form-header h1 { font-size: 26px; }
}
</style>
