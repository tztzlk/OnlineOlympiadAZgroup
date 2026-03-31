<template>
  <div class="register-page">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>

    <div class="register-card">
      <div class="card-art">
        <div class="art-inner">
          <div class="art-logo">OO</div>
          <div class="art-text">
            <h2>Добро пожаловать</h2>
            <p>Создайте аккаунт, укажите школу и город, чтобы получить доступ к олимпиадам, результатам и сертификатам.</p>
          </div>
          <div class="art-steps">
            <div v-for="(step, index) in steps" :key="step" class="step">
              <div class="step-num">{{ index + 1 }}</div>
              <span>{{ step }}</span>
            </div>
          </div>
          <div class="art-footer">Безопасная регистрация участников</div>
        </div>
        <div class="art-grid"></div>
      </div>

      <div class="card-form">
        <div class="form-header">
          <h1>Регистрация</h1>
          <p>Заполните данные участника для создания аккаунта.</p>
        </div>

        <form @submit.prevent="handleRegister" novalidate>
          <div class="fields-grid">
            <div class="field" :class="{ focused: focusedField === 'name', filled: name }">
              <label>Имя и фамилия</label>
              <div class="input-wrap">
                <span class="field-icon">👤</span>
                <input v-model="name" type="text" placeholder="Иван Иванов" @focus="focusedField = 'name'" @blur="focusedField = ''" required />
              </div>
            </div>

            <div class="field" :class="{ focused: focusedField === 'email', filled: email }">
              <label>Email</label>
              <div class="input-wrap">
                <span class="field-icon">@</span>
                <input v-model="email" type="email" placeholder="you@example.com" @focus="focusedField = 'email'" @blur="focusedField = ''" required />
              </div>
            </div>

            <div class="field" :class="{ focused: focusedField === 'school', filled: school }">
              <label>Школа</label>
              <div class="input-wrap">
                <span class="field-icon">🏫</span>
                <input v-model="school" type="text" placeholder="Например: Школа-лицей №12" @focus="focusedField = 'school'" @blur="focusedField = ''" required />
              </div>
            </div>

            <div class="field" :class="{ focused: focusedField === 'city', filled: city }">
              <label>Город</label>
              <div class="input-wrap">
                <span class="field-icon">📍</span>
                <input v-model="city" type="text" placeholder="Например: Кызылорда" @focus="focusedField = 'city'" @blur="focusedField = ''" required />
              </div>
            </div>

            <div class="field full" :class="{ focused: focusedField === 'phone', filled: phone }">
              <label>Номер телефона</label>
              <div class="input-wrap">
                <span class="field-icon">☎</span>
                <input v-model="phone" type="tel" maxlength="18" placeholder="+7 (___) ___-__-__" @input="formatPhone" @focus="focusedField = 'phone'" @blur="focusedField = ''" required />
              </div>
            </div>

            <div class="field" :class="{ focused: focusedField === 'password', filled: password }">
              <label>Пароль</label>
              <div class="input-wrap">
                <span class="field-icon">🔒</span>
                <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Минимум 6 символов" @focus="focusedField = 'password'" @blur="focusedField = ''" required />
                <button type="button" class="eye-btn" tabindex="-1" @click="showPassword = !showPassword">
                  {{ showPassword ? 'Скрыть' : 'Показать' }}
                </button>
              </div>
            </div>

            <div class="field" :class="{ focused: focusedField === 'confirm', filled: confirmPassword, match: confirmPassword && password === confirmPassword }">
              <label>Подтвердите пароль</label>
              <div class="input-wrap">
                <span class="field-icon">✓</span>
                <input v-model="confirmPassword" :type="showConfirm ? 'text' : 'password'" placeholder="Повторите пароль" @focus="focusedField = 'confirm'" @blur="focusedField = ''" required />
                <button type="button" class="eye-btn" tabindex="-1" @click="showConfirm = !showConfirm">
                  {{ showConfirm ? 'Скрыть' : 'Показать' }}
                </button>
              </div>
            </div>
          </div>

          <div v-if="password" class="strength-bar">
            <div class="strength-track">
              <div class="strength-fill" :style="{ width: strengthWidth }" :class="strengthClass"></div>
            </div>
            <span class="strength-label" :class="strengthClass">{{ strengthLabel }}</span>
          </div>

          <transition name="err">
            <div v-if="error" class="error-banner">{{ error }}</div>
          </transition>

          <button type="submit" class="submit-btn" :disabled="loading">
            {{ loading ? 'Создание аккаунта...' : 'Зарегистрироваться' }}
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
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { solveProofOfWork } from '../js/pow'
import { useUserStore } from '../stores/user'

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
const showPassword = ref(false)
const showConfirm = ref(false)
const focusedField = ref('')

const steps = ['Заполните личные данные', 'Укажите школу и город', 'Получите доступ к олимпиадам']

const strengthScore = computed(() => {
  const value = password.value
  if (!value) return 0
  let score = 0
  if (value.length >= 6) score += 1
  if (/[A-ZА-Я]/.test(value)) score += 1
  if (/[0-9]/.test(value)) score += 1
  if (/[^A-Za-zА-Яа-я0-9]/.test(value)) score += 1
  return score
})

const strengthWidth = computed(() => ['0%', '25%', '50%', '75%', '100%'][strengthScore.value])
const strengthClass = computed(() => ['', 'weak', 'fair', 'good', 'strong'][strengthScore.value])
const strengthLabel = computed(() => ['', 'Слабый', 'Средний', 'Хороший', 'Надёжный'][strengthScore.value])

async function handleRegister() {
  error.value = ''

  if (!school.value.trim()) {
    error.value = 'Укажите школу.'
    return
  }

  if (!city.value.trim()) {
    error.value = 'Укажите город.'
    return
  }

  const cleanPhone = phone.value.replace(/\D/g, '')
  if (cleanPhone.length < 11) {
    error.value = 'Введите корректный номер телефона.'
    return
  }

  if (password.value !== confirmPassword.value) {
    error.value = 'Пароли не совпадают.'
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
    router.push('/')
  } catch (err) {
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)[0][0]
    } else {
      error.value = 'Ошибка сервера.'
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
.register-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at top left, rgba(225,29,72,0.14), transparent 26%), radial-gradient(circle at bottom right, rgba(245,158,11,0.14), transparent 24%), var(--bg); padding: 24px; position: relative; overflow: hidden; }
.bg-orbs { position: absolute; inset: 0; pointer-events: none; }
.orb { position: absolute; border-radius: 50%; filter: blur(70px); opacity: 0.3; }
.orb-1 { width: 420px; height: 420px; background: rgba(225,29,72,0.18); top: -80px; left: -80px; }
.orb-2 { width: 320px; height: 320px; background: rgba(245,158,11,0.14); bottom: -60px; right: -40px; }
.orb-3 { width: 260px; height: 260px; background: rgba(244,63,94,0.12); top: 40%; left: 48%; }
.register-card { display: flex; width: 100%; max-width: 1080px; border-radius: 28px; overflow: hidden; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 24px 60px rgba(0,0,0,0.32); position: relative; z-index: 1; }
.card-art { width: 40%; padding: 44px 36px; position: relative; overflow: hidden; background: linear-gradient(160deg, #111827 0%, #3f0f21 100%); color: #fff; }
.art-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px); background-size: 42px 42px; }
.art-inner { position: relative; z-index: 1; display: flex; flex-direction: column; gap: 24px; height: 100%; }
.art-logo { width: 54px; height: 54px; border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.18); }
.art-text h2 { font-size: 28px; margin-bottom: 10px; color: #fff; }
.art-text p { color: rgba(255,255,255,0.84); line-height: 1.6; }
.art-steps { display: grid; gap: 14px; margin-top: auto; }
.step { display: flex; align-items: center; gap: 12px; color: rgba(255,255,255,0.92); }
.step-num { width: 28px; height: 28px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.14); font-size: 12px; font-weight: 700; }
.art-footer { color: rgba(255,255,255,0.7); font-size: 13px; }
.card-form { flex: 1; background: rgba(10,14,24,0.94); padding: 42px; }
.form-header { margin-bottom: 26px; }
.form-header h1 { font-size: 32px; color: #f9fafb; margin-bottom: 8px; }
.form-header p { color: #d1d5db; }
.fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { display: flex; flex-direction: column; gap: 8px; }
.field.full { grid-column: 1 / -1; }
label { color: #f3f4f6; font-size: 13px; font-weight: 600; }
.field.focused label { color: #fb7185; }
.field.match label { color: #34d399; }
.input-wrap { position: relative; }
.field-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #d1d5db; }
input { width: 100%; padding: 13px 90px 13px 42px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.16); background: rgba(255,255,255,0.1); color: #fff; font-size: 14px; outline: none; transition: 0.2s ease; }
input::placeholder { color: #dbe3ef; opacity: 0.95; }
input:focus { border-color: #fb7185; background: rgba(255,255,255,0.14); box-shadow: 0 0 0 4px rgba(251,113,133,0.14); }
.eye-btn { position: absolute; top: 50%; right: 12px; transform: translateY(-50%); border: 0; background: transparent; color: #fca5a5; cursor: pointer; font-size: 12px; font-weight: 700; }
.strength-bar { display: flex; align-items: center; gap: 12px; margin-top: 16px; }
.strength-track { flex: 1; height: 6px; border-radius: 999px; background: rgba(255,255,255,0.12); overflow: hidden; }
.strength-fill { height: 100%; }
.strength-fill.weak { background: #f87171; }
.strength-fill.fair { background: #fb923c; }
.strength-fill.good { background: #facc15; }
.strength-fill.strong { background: #34d399; }
.strength-label { font-size: 12px; font-weight: 700; }
.strength-label.weak { color: #f87171; }
.strength-label.fair { color: #fb923c; }
.strength-label.good { color: #facc15; }
.strength-label.strong { color: #34d399; }
.error-banner { margin-top: 16px; border-radius: 14px; padding: 12px 14px; color: #fecaca; background: rgba(127,29,29,0.34); border: 1px solid rgba(248,113,113,0.32); }
.submit-btn { width: 100%; margin-top: 20px; border: 0; border-radius: 14px; padding: 14px 18px; background: linear-gradient(90deg, #e11d48, #fb7185); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; box-shadow: 0 12px 28px rgba(225,29,72,0.24); }
.submit-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.form-footer { margin-top: 18px; text-align: center; color: #d1d5db; }
.form-footer a { color: #fb7185; font-weight: 700; text-decoration: none; }
.err-enter-active, .err-leave-active { transition: all 0.25s ease; }
.err-enter-from, .err-leave-to { opacity: 0; transform: translateY(-6px); }
@media (max-width: 860px) { .card-art { display: none; } .card-form { padding: 30px 22px; } }
@media (max-width: 560px) { .fields-grid { grid-template-columns: 1fr; } .field.full { grid-column: auto; } }
</style>
