<template>
  <div class="help-page">
    <div class="help-shell">
      <section class="help-side">
        <p class="eyebrow">Support</p>
        <h1>Поддержка по заявкам, оплатам и входу</h1>
        <p class="lead">
          Напишите нам, если нужна помощь с регистрацией, оплатой, доступом к олимпиаде
          или прохождением теста.
        </p>

        <div class="contact-list">
          <a class="contact-card" :href="`mailto:${supportEmail}`">
            <strong>Email поддержки</strong>
            <span>{{ supportEmail }}</span>
          </a>
          <a v-if="supportPhone" class="contact-card" :href="supportPhoneHref">
            <strong>Телефон</strong>
            <span>{{ supportPhone }}</span>
          </a>
        </div>

        <div class="trust-box">
          <strong>Когда стоит обратиться?</strong>
          <p>Если неясен статус заявки, не проходит оплата, не удаётся войти или открыть тест.</p>
        </div>
      </section>

      <section class="help-card">
        <div class="help-head">
          <p class="eyebrow">Обратная связь</p>
          <h2>Опишите вопрос в свободной форме</h2>
          <p>Чем точнее описание, тем быстрее команда сможет помочь.</p>
        </div>

        <form @submit.prevent="submit" class="help-form">
          <div class="grid">
            <label class="field">
              <span>Имя</span>
              <input v-model="form.name" type="text" placeholder="Ваше имя" required />
            </label>
            <label class="field">
              <span>Email</span>
              <input v-model="form.email" type="email" placeholder="Email" required />
            </label>
            <label class="field">
              <span>Телефон</span>
              <input v-model="form.phone" type="text" placeholder="Телефон" />
            </label>
            <label class="field">
              <span>Тема</span>
              <input v-model="form.topic" type="text" placeholder="Например: оплата или вход" required />
            </label>
          </div>

          <label class="field">
            <span>Сообщение</span>
            <textarea v-model="form.message" rows="6" placeholder="Опишите проблему или вопрос" required></textarea>
          </label>

          <p v-if="message" class="message success">{{ message }}</p>
          <p v-if="error" class="message error">{{ error }}</p>

          <button class="submit-btn" :disabled="loading">
            {{ loading ? 'Отправляем...' : 'Отправить обращение' }}
          </button>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import api from '../js/api'
import { solveProofOfWork } from '../js/pow'

const supportEmail = import.meta.env.VITE_SUPPORT_EMAIL || 'eurica001olimp@gmail.com'
const supportPhone = import.meta.env.VITE_SUPPORT_PHONE || ''
const supportPhoneHref = supportPhone ? `tel:${supportPhone.replace(/[^\d+]/g, '')}` : ''

const form = reactive({
  name: '',
  email: '',
  phone: '',
  topic: '',
  message: '',
})

const message = ref('')
const error = ref('')
const loading = ref(false)

const submit = async () => {
  loading.value = true
  message.value = ''
  error.value = ''

  try {
    const pow = await solveProofOfWork('feedback')
    await api.post('/support/feedback', {
      ...form,
      ...pow,
    })

    message.value = 'Сообщение успешно отправлено'
    form.name = ''
    form.email = ''
    form.phone = ''
    form.topic = ''
    form.message = ''
  } catch (err) {
    error.value = err.response?.status >= 400
      ? (err.response?.data?.message || 'Не удалось отправить обращение.')
      : 'Не удалось отправить обращение.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
* { box-sizing: border-box; }

.help-page {
  min-height: 100vh;
  padding: 110px 20px 48px;
  background:
    radial-gradient(circle at top left, rgba(201, 171, 99, 0.14), transparent 24%),
    var(--bg);
}

.help-shell {
  width: min(1120px, 100%);
  margin: 0 auto;
  display: grid;
  grid-template-columns: 0.95fr 1.05fr;
  gap: 22px;
}

.help-side,
.help-card {
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
}

.help-side {
  padding: 30px;
  display: grid;
  gap: 20px;
}

.help-card {
  padding: 30px;
  display: grid;
  gap: 20px;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

.lead,
.help-head p:last-child,
.trust-box p,
.contact-card span {
  color: var(--text-secondary);
}

.contact-list {
  display: grid;
  gap: 12px;
}

.contact-card {
  padding: 18px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(201, 171, 99, 0.18);
  background: rgba(255, 251, 243, 0.82);
  text-decoration: none;
  display: grid;
  gap: 4px;
}

.trust-box {
  padding: 18px;
  border-radius: var(--radius-md);
  background: rgba(79, 167, 116, 0.08);
  border: 1px solid rgba(79, 167, 116, 0.16);
  display: grid;
  gap: 8px;
}

.help-form {
  display: grid;
  gap: 16px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.field {
  display: grid;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
}

.field input,
.field textarea {
  width: 100%;
  border-radius: var(--radius-sm);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 245, 0.95);
  padding: 14px 16px;
  color: var(--text);
}

.message {
  margin: 0;
  border-radius: var(--radius-sm);
  padding: 13px 14px;
  font-size: 14px;
}

.message.success {
  background: var(--success-bg);
  color: #2f6f4b;
}

.message.error {
  background: var(--danger-bg);
  color: #8f3b3b;
}

.submit-btn {
  justify-self: start;
  min-width: 220px;
  min-height: 52px;
  border: 0;
  border-radius: var(--radius-sm);
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  color: var(--text);
  box-shadow: 0 14px 28px rgba(201, 171, 99, 0.22);
  cursor: pointer;
}

@media (max-width: 860px) {
  .help-shell {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .help-page {
    padding: 96px 14px 30px;
  }

  .help-side,
  .help-card {
    padding: 22px 18px;
  }

  .grid {
    grid-template-columns: 1fr;
  }

  .submit-btn {
    width: 100%;
  }
}
</style>
