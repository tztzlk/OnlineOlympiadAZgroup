<template>
  <div class="login-page">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>

    <div class="login-card">
      <div class="card-art">
        <div class="art-inner">
          <div class="art-logo">OO</div>
          <div class="art-text">
            <h2>С возвращением</h2>
            <p>Войдите, чтобы продолжить работу с платформой, результатами и заявками.</p>
          </div>
          <div class="art-features">
            <div class="feat" v-for="f in features" :key="f">
              <div class="feat-dot"></div>
              <span>{{ f }}</span>
            </div>
          </div>
          <div class="art-footer">Безопасный вход в систему</div>
        </div>
      </div>

      <div class="card-form">
        <div class="form-header">
          <h1>Вход</h1>
          <p>Введите email и пароль для входа в аккаунт</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form" novalidate>
          <div class="field-group">
            <div class="field" :class="{ focused: focusedField === 'email', filled: email }">
              <label>Email</label>
              <div class="input-wrap">
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

            <div class="field" :class="{ focused: focusedField === 'password', filled: password }">
              <label>Пароль</label>
              <div class="input-wrap">
                <input
                  :type="showPassword ? 'text' : 'password'"
                  v-model="password"
                  placeholder="Введите пароль"
                  @focus="focusedField = 'password'"
                  @blur="focusedField = ''"
                  required
                />
                <button type="button" class="eye-btn" @click="showPassword = !showPassword" tabindex="-1">
                  {{ showPassword ? 'Скрыть' : 'Показать' }}
                </button>
              </div>
            </div>
          </div>

          <div class="helper-row">
            <RouterLink to="/forgot-password" class="helper-link">Забыли пароль?</RouterLink>
            <RouterLink to="/help-desk" class="helper-link">Нужна помощь?</RouterLink>
          </div>

          <transition name="err">
            <div v-if="error" class="error-banner">{{ error }}</div>
          </transition>

          <button type="submit" class="submit-btn" :class="{ loading }" :disabled="loading">
            {{ loading ? 'Подождите...' : 'Войти в аккаунт' }}
          </button>
        </form>

        <div class="form-footer">
          <p>Нет аккаунта? <router-link to="/register">Зарегистрироваться</router-link></p>
          <p class="admin-link">Вход для администратора: <router-link to="/admin-login">Панель управления</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { useUserStore } from '../stores/user'

const router = useRouter()
const userStore = useUserStore()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)
const focusedField = ref('')

const features = ['Безопасный вход', 'Восстановление пароля', 'Поддержка через Help Desk']

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    const response = await api.post('/auth/login', {
      email: email.value,
      password: password.value,
    })

    userStore.setAuth(response.data.user, response.data.token)
    localStorage.setItem('session_type', 'user')
    router.push('/')
  } catch (err) {
    if (err.response?.status === 401) {
      error.value = 'Неверный email или пароль'
    } else if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)[0][0]
    } else {
      error.value = err.response?.data?.message || 'Ошибка сервера. Попробуйте позже.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
* { box-sizing: border-box; margin: 0; padding: 0; }
.login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: var(--bg); padding: 24px; position: relative; overflow: hidden; }
.bg-orbs { position: absolute; inset: 0; pointer-events: none; }
.orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.25; }
.orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(225,29,72,0.15), transparent 70%); top: -120px; left: -100px; }
.orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(225,29,72,0.1), transparent 70%); bottom: -80px; right: -60px; }
.orb-3 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(225,29,72,0.08), transparent 70%); top: 40%; left: 50%; }
.login-card { display: flex; width: 100%; max-width: 900px; min-height: 560px; border-radius: 28px; overflow: hidden; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4); border: 1px solid rgba(255, 255, 255, 0.08); position: relative; z-index: 1; }
.card-art { width: 42%; background: linear-gradient(145deg, var(--surface-strong) 0%, color-mix(in srgb, var(--bg) 82%, #000 18%) 50%, var(--surface-strong) 100%); border-right: 1px solid var(--surface-border); padding: 48px 40px; display: flex; flex-direction: column; }
.art-inner { display: flex; flex-direction: column; height: 100%; gap: 28px; }
.art-logo { width: 54px; height: 54px; border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; background: rgba(255,255,255,0.12); color: #fff; }
.art-text h2 { font-size: 26px; font-weight: 700; color: white; margin-bottom: 10px; line-height: 1.2; }
.art-text p { font-size: 14px; color: rgba(255,255,255,0.65); line-height: 1.6; }
.art-features { display: flex; flex-direction: column; gap: 12px; margin-top: auto; }
.feat { display: flex; align-items: center; gap: 10px; font-size: 13px; color: rgba(255,255,255,0.75); }
.feat-dot { width: 6px; height: 6px; border-radius: 50%; background: rgba(255,255,255,0.6); }
.art-footer { font-size: 11px; color: rgba(255,255,255,0.35); }
.card-form { flex: 1; background: var(--surface); padding: 52px 48px; display: flex; flex-direction: column; justify-content: center; }
.form-header { margin-bottom: 36px; }
.form-header h1 { font-size: 32px; font-weight: 700; color: var(--text-on-surface); margin-bottom: 8px; }
.form-header p { font-size: 14px; color: var(--text-muted-on-surface); }
.field-group { display: flex; flex-direction: column; gap: 20px; margin-bottom: 16px; }
.field { display: flex; flex-direction: column; gap: 8px; }
label { font-size: 13px; font-weight: 500; color: var(--text-muted-on-surface); }
.input-wrap { position: relative; display: flex; align-items: center; }
input { width: 100%; background: var(--surface-soft); border: 1.5px solid var(--surface-border); border-radius: 12px; padding: 13px 44px 13px 16px; font-size: 15px; color: var(--text-on-surface); outline: none; }
input:focus { border-color: #e11d48; background: #ffffff; color: #111827; box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.15); }
.eye-btn { position: absolute; right: 12px; background: none; border: none; cursor: pointer; color: var(--text-muted-on-surface); }
.helper-row { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.helper-link { color: #e11d48; text-decoration: none; font-weight: 600; font-size: 14px; }
.error-banner { display: flex; align-items: center; gap: 10px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.25); border-radius: 10px; padding: 12px 14px; font-size: 13px; color: #fca5a5; margin-bottom: 4px; }
.err-enter-active, .err-leave-active { transition: all 0.3s ease; }
.err-enter-from, .err-leave-to { opacity: 0; transform: translateY(-6px); }
.submit-btn { width: 100%; padding: 15px 24px; border-radius: 12px; border: none; background: #e11d48; color: white; font-size: 15px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 20px rgba(225, 29, 72, 0.35); margin-top: 8px; }
.submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.form-footer { margin-top: 24px; text-align: center; }
.form-footer p { font-size: 14px; color: var(--text-muted-on-surface); }
.form-footer .admin-link { margin-top: 12px; }
.form-footer a { color: #e11d48; text-decoration: none; font-weight: 500; }
@media (max-width: 768px) { .card-art { display: none; } .card-form { padding: 40px 32px; } .form-header h1 { font-size: 26px; } }
@media (max-width: 480px) { .card-form { padding: 32px 24px; } .helper-row { flex-direction: column; } }
</style>
