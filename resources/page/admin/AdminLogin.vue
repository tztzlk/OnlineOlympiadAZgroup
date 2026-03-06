<template>
  <div class="admin-login">
    <!-- Background decoration -->
    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>
    <div class="bg-orb bg-orb--3"></div>

    <div class="card">
      <div class="card__header">
        <div class="logo">
          <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
            <rect x="2" y="2" width="11" height="11" rx="2.5" fill="#3B82F6"/>
            <rect x="15" y="2" width="11" height="11" rx="2.5" fill="#3B82F6" opacity="0.5"/>
            <rect x="2" y="15" width="11" height="11" rx="2.5" fill="#3B82F6" opacity="0.5"/>
            <rect x="15" y="15" width="11" height="11" rx="2.5" fill="#3B82F6" opacity="0.3"/>
          </svg>
        </div>
        <h1 class="card__title">Панель управления</h1>
        <p class="card__subtitle">Войдите, чтобы продолжить</p>
      </div>

      <form class="form" @submit.prevent="login">
        <div class="field" :class="{ 'field--focused': focusedField === 'email', 'field--filled': email }">
          <label class="field__label">Электронная почта</label>
          <div class="field__wrapper">
            <span class="field__icon">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M2 4a1 1 0 011-1h10a1 1 0 011 1v8a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" stroke="currentColor" stroke-width="1.2"/>
                <path d="M2 4l6 5 6-5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
              </svg>
            </span>
            <input
              v-model="email"
              type="email"
              placeholder="admin@example.com"
              required
              autocomplete="email"
              @focus="focusedField = 'email'"
              @blur="focusedField = ''"
            />
          </div>
        </div>

        <div class="field" :class="{ 'field--focused': focusedField === 'password', 'field--filled': password }">
          <label class="field__label">Пароль</label>
          <div class="field__wrapper">
            <span class="field__icon">
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <rect x="3" y="7" width="10" height="7" rx="1.5" stroke="currentColor" stroke-width="1.2"/>
                <path d="M5 7V5a3 3 0 016 0v2" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                <circle cx="8" cy="10.5" r="1" fill="currentColor"/>
              </svg>
            </span>
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              autocomplete="current-password"
              @focus="focusedField = 'password'"
              @blur="focusedField = ''"
            />
            <button type="button" class="field__toggle" @click="showPassword = !showPassword">
              <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M1 8s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5z" stroke="currentColor" stroke-width="1.2"/>
                <circle cx="8" cy="8" r="2" stroke="currentColor" stroke-width="1.2"/>
              </svg>
              <svg v-else width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M2 2l12 12M6.5 6.6A2 2 0 0010 9.5M4 4.3C2.4 5.4 1 8 1 8s2.5 5 7 5c1.3 0 2.5-.4 3.5-1M7 3.1C7.3 3 7.7 3 8 3c4.5 0 7 5 7 5s-.6 1.3-1.7 2.5" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
        </div>

        <Transition name="error">
          <div v-if="error" class="error-banner">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.2"/>
              <path d="M7 4v3.5M7 9.5v.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
            </svg>
            {{ error }}
          </div>
        </Transition>

        <button type="submit" class="submit-btn" :class="{ 'submit-btn--loading': loading }">
          <span v-if="!loading">Войти</span>
          <span v-else class="loader"></span>
        </button>
      </form>

      <p class="card__footer">Только для авторизованных сотрудников</p>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue"
import api from "../../js/api"
import { useRouter } from "vue-router"

const email = ref("")
const password = ref("")
const error = ref("")
const loading = ref(false)
const showPassword = ref(false)
const focusedField = ref("")
const router = useRouter()

const login = async () => {
  loading.value = true
  error.value = ""

  try {

    const res = await api.post('/auth/admin/login', {
      email: email.value,
      password: password.value
    })

    const user = res.data.user

    // 🔥 защита — проверяем admin роль
    if (user.is_admin !== 1) {
      throw new Error("Доступ только для администратора")
    }

    localStorage.setItem("token", res.data.token)
    localStorage.setItem("user", JSON.stringify(user))

    router.push("/admin")

  } catch (err) {
    error.value =
      err.response?.data?.message ||
      err.message ||
      "Ошибка входа"
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=DM+Sans:wght@300;400;500&display=swap');

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

.admin-login {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #EEF4FF;
  font-family: 'DM Sans', sans-serif;
  position: relative;
  overflow: hidden;
  padding: 24px;
}

/* Soft background orbs */
.bg-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
}
.bg-orb--1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, #BFDBFE 0%, transparent 70%);
  top: -100px; right: -100px;
  opacity: 0.7;
}
.bg-orb--2 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, #C7D2FE 0%, transparent 70%);
  bottom: -80px; left: -80px;
  opacity: 0.6;
}
.bg-orb--3 {
  width: 300px; height: 300px;
  background: radial-gradient(circle, #BAE6FD 0%, transparent 70%);
  top: 50%; left: 30%;
  transform: translate(-50%, -50%);
  opacity: 0.4;
}

/* Card */
.card {
  background: rgba(255, 255, 255, 0.75);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.9);
  border-radius: 24px;
  padding: 48px 44px;
  width: 100%;
  max-width: 420px;
  box-shadow:
    0 4px 6px rgba(0,0,0,0.03),
    0 20px 60px rgba(0,0,0,0.07),
    inset 0 1px 0 rgba(255,255,255,0.8);
  animation: cardIn 0.6s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes cardIn {
  from { opacity: 0; transform: translateY(24px) scale(0.98); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* Header */
.card__header {
  text-align: center;
  margin-bottom: 36px;
}

.logo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 52px; height: 52px;
  background: linear-gradient(135deg, #1531a1 0%, #5d76f3 100%);
  border-radius: 14px;
  margin-bottom: 20px;
  border: 1px solid rgba(200, 169, 110, 0.2);
  box-shadow: 0 2px 12px rgba(200, 169, 110, 0.15);
}

.card__title {
  font-family: 'Cormorant Garamond', serif;
  font-size: 28px;
  font-weight: 600;
  color: #2C2417;
  letter-spacing: -0.3px;
  line-height: 1.2;
  margin-bottom: 6px;
}

.card__subtitle {
  font-size: 13.5px;
  color: #6672cf;
  font-weight: 300;
  letter-spacing: 0.1px;
}

/* Form */
.form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.field__label {
  font-size: 12px;
  font-weight: 500;
  color: #7A6E63;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  transition: color 0.2s;
}

.field--focused .field__label {
  color: #5a65b8;
}

.field__wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.field__icon {
  position: absolute;
  left: 14px;
  color: #B8A898;
  display: flex;
  align-items: center;
  pointer-events: none;
  transition: color 0.2s;
}

.field--focused .field__icon {
  color: #C8A96E;
}

.field__wrapper input {
  width: 100%;
  padding: 13px 14px 13px 42px;
  background: rgba(247, 244, 239, 0.8);
  border: 1.5px solid #E8E0D5;
  border-radius: 12px;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  color: #2C2417;
  outline: none;
  transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
}

.field__wrapper input::placeholder {
  color: #C4B8AC;
}

.field__wrapper input:focus {
  border-color: #C8A96E;
  background: #FFFFFF;
  box-shadow: 0 0 0 3px rgba(200, 169, 110, 0.1);
}

.field__toggle {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  color: #B8A898;
  display: flex;
  align-items: center;
  padding: 4px;
  border-radius: 6px;
  transition: color 0.2s, background 0.2s;
}

.field__toggle:hover {
  color: #8A7A6A;
  background: rgba(0,0,0,0.04);
}

/* Error */
.error-banner {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 11px 14px;
  background: #FEF1EF;
  border: 1px solid #F5C9C1;
  border-radius: 10px;
  font-size: 13px;
  color: #C0442A;
}

.error-enter-active, .error-leave-active {
  transition: all 0.25s ease;
}
.error-enter-from, .error-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* Submit */
.submit-btn {
  margin-top: 6px;
  padding: 14px;
  background: linear-gradient(135deg, #7d5ad4 0%, #4b4fcd 100%);
  color: #FFFFFF;
  border: none;
  border-radius: 12px;
  font-family: 'DM Sans', sans-serif;
  font-size: 14.5px;
  font-weight: 500;
  letter-spacing: 0.3px;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.2s, filter 0.2s;
  box-shadow: 0 4px 16px rgba(0, 38, 255, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
}

.submit-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(90, 121, 200, 0.45);
  filter: brightness(1.05);
}

.submit-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(122, 105, 236, 0.3);
}

.submit-btn--loading {
  pointer-events: none;
  filter: brightness(0.95);
}

.loader {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #FFFFFF;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Footer */
.card__footer {
  text-align: center;
  margin-top: 28px;
  font-size: 12px;
  color: #c1b9f3;
  letter-spacing: 0.2px;
}
</style>