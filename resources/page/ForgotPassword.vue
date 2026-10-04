<template>
  <AuthShell>
    <div>
      <header class="auth-head">
        <p class="auth-eyebrow">Восстановление доступа</p>
        <h1>Вернём вас в кабинет без обращения в поддержку</h1>
        <p class="auth-sub">
          Достаточно указать email, и мы отправим ссылку для смены пароля. После обновления пароля вы снова попадёте в личный кабинет участника.
        </p>
      </header>

      <form @submit.prevent="submit" class="auth-form">
        <h2 class="form-title">Получить ссылку для восстановления</h2>
        <p class="auth-sub form-copy">Введите адрес электронной почты, и мы отправим инструкцию для восстановления доступа к кабинету.</p>

        <label class="ds-field">
          <span>Email</span>
          <input v-model="email" class="ds-input" type="email" inputmode="email" autocomplete="email" placeholder="you@example.com" required />
        </label>

        <p class="ds-msg info">Если аккаунт существует, письмо придёт на указанный адрес. Проверьте также папку “Спам”.</p>

        <p v-if="message" class="ds-msg success" role="status">{{ message }}</p>
        <p v-if="error" class="ds-msg error" role="alert">{{ error }}</p>

        <button class="ds-btn ds-btn-primary ds-btn-lg ds-btn-block" :disabled="loading">
          <span v-if="loading" class="ds-spinner" aria-hidden="true"></span>
          {{ loading ? 'Отправляем...' : 'Отправить ссылку' }}
        </button>
      </form>

      <dl class="hint-list">
        <div class="hint-card">
          <dt>Что понадобится</dt>
          <dd>Email, который использовался при регистрации на платформе.</dd>
        </div>
        <div class="hint-card">
          <dt>Что будет дальше</dt>
          <dd>Вы получите письмо со ссылкой, после чего сможете задать новый пароль и снова войти в кабинет.</dd>
        </div>
      </dl>

      <div class="auth-foot auth-links">
        <RouterLink to="/login">Вернуться ко входу</RouterLink>
        <RouterLink to="/help-desk">Нужна помощь?</RouterLink>
      </div>
    </div>
  </AuthShell>
</template>

<script setup>
import { ref } from 'vue'
import api from '../js/api'
import AuthShell from '../components/AuthShell.vue'
import '../css/auth.css'

const email = ref('')
const message = ref('')
const error = ref('')
const loading = ref(false)

const submit = async () => {
  loading.value = true
  message.value = ''
  error.value = ''

  try {
    const { data } = await api.post(
      '/auth/forgot-password',
      { email: email.value.trim() },
      { timeout: 15000 }
    )
    message.value = data.message
  } catch (err) {
    if (err.code === 'ECONNABORTED') {
      error.value = 'Сервер отвечает слишком долго. Попробуйте ещё раз через несколько секунд.'
      return
    }

    if (!err.response) {
      error.value = 'Не удалось связаться с сервером. Проверьте, что локальный сервер запущен.'
      return
    }
    if (err.response.status === 429) {
      error.value = 'Слишком много запросов. Подождите минуту и попробуйте снова.'
      return
    }
    error.value = err.response?.data?.message || 'Не удалось отправить ссылку.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-head h1 { font-size: clamp(24px, 2.8vw, 30px); }

.form-title {
  font-family: var(--font-sans);
  font-size: 19px;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.form-copy {
  margin-top: -8px;
  font-size: 15px;
}

.hint-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin: 24px 0 0;
}

.hint-card {
  padding: 14px 16px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.hint-card dt {
  margin-bottom: 4px;
  font-size: 14.5px;
  font-weight: 700;
}

.hint-card dd {
  margin: 0;
  color: var(--text-secondary);
  font-size: 14px;
  line-height: 1.5;
}

@media (max-width: 560px) {
  .hint-list { grid-template-columns: 1fr; }
}
</style>
