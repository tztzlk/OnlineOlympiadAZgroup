<template>
  <div class="auth-page">
    <div class="auth-shell">
      <section class="auth-side">
        <p class="eyebrow">Восстановление доступа</p>
        <h1>Вернём вас в кабинет без обращения в поддержку</h1>
        <p class="lead">
          Достаточно указать email, и мы отправим ссылку для смены пароля. После обновления пароля вы снова попадёте в личный кабинет участника.
        </p>

        <div class="hint-list">
          <article class="hint-card">
            <strong>Что понадобится</strong>
            <p>Email, который использовался при регистрации на платформе.</p>
          </article>
          <article class="hint-card">
            <strong>Что будет дальше</strong>
            <p>Вы получите письмо со ссылкой, после чего сможете задать новый пароль и снова войти в кабинет.</p>
          </article>
        </div>
      </section>

      <section class="auth-card">
        <p class="eyebrow">Сброс пароля</p>
        <h2>Получить ссылку для восстановления</h2>
        <p class="subtext">Введите адрес электронной почты, и мы отправим инструкцию для восстановления доступа к кабинету.</p>

        <form @submit.prevent="submit" class="auth-form">
          <label class="field">
            <span>Email</span>
            <input v-model="email" type="email" placeholder="you@example.com" required />
          </label>

          <p class="notice-box">Если аккаунт существует, письмо придёт на указанный адрес. Проверьте также папку “Спам”.</p>

          <p v-if="message" class="message success">{{ message }}</p>
          <p v-if="error" class="message error">{{ error }}</p>

          <button class="submit-btn" :disabled="loading">
            {{ loading ? 'Отправляем...' : 'Отправить ссылку' }}
          </button>
        </form>

        <div class="links">
          <RouterLink to="/login">Вернуться ко входу</RouterLink>
          <RouterLink to="/help-desk">Нужна помощь?</RouterLink>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '../js/api'

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
    error.value = err.response?.data?.message || 'Не удалось отправить ссылку.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
* { box-sizing: border-box; }

.auth-page {
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

.auth-shell {
  width: min(1040px, 100%);
  display: grid;
  grid-template-columns: 1fr 0.95fr;
  gap: 24px;
}

.auth-side,
.auth-card {
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
}

.auth-side,
.auth-card {
  padding: 30px;
}

.auth-side {
  display: grid;
  gap: 18px;
  background: linear-gradient(160deg, rgba(255, 249, 238, 0.95), rgba(240, 232, 214, 0.82)), var(--surface);
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: .12em;
  color: var(--accent-strong);
  font-size: 12px;
  font-weight: 800;
}

.lead,
.subtext,
.hint-card p,
.notice-box {
  color: var(--text-secondary);
}

.hint-list {
  display: grid;
  gap: 12px;
}

.hint-card {
  padding: 16px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(141, 111, 49, 0.14);
  background: rgba(255, 252, 244, 0.76);
}

.hint-card strong {
  display: block;
  color: var(--text);
  margin-bottom: 6px;
}

.auth-card h2 {
  margin: 0;
  color: var(--text);
}

.subtext {
  margin: 10px 0 22px;
  line-height: 1.6;
}

.auth-form {
  display: grid;
  gap: 14px;
}

.field {
  display: grid;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
}

input {
  width: 100%;
  padding: 14px 16px;
  min-height: 54px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 245, 0.95);
  color: var(--text);
}

.notice-box,
.message {
  margin: 0;
  font-size: 14px;
  border-radius: var(--radius-sm);
  padding: 12px 14px;
}

.notice-box {
  background: rgba(201, 171, 99, 0.12);
  border: 1px solid rgba(201, 171, 99, 0.16);
}

.success {
  color: #2f6f4b;
  background: var(--success-bg);
}

.error {
  color: #8f3b3b;
  background: var(--danger-bg);
}

.submit-btn {
  border: 0;
  border-radius: var(--radius-sm);
  padding: 14px 18px;
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  color: var(--text);
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 12px 26px rgba(201,171,99,.2);
}

.links {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-top: 18px;
}

.links a {
  color: var(--accent-strong);
  text-decoration: none;
  font-weight: 700;
}

@media (max-width: 760px) {
  .auth-shell {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 560px) {
  .auth-page {
    padding: 20px 14px;
  }

  .auth-side,
  .auth-card {
    padding: 24px 18px;
  }

  .links {
    flex-direction: column;
  }
}
</style>
