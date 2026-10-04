<template>
  <div class="help-page">
    <div class="help-shell">
      <section class="help-side" aria-labelledby="help-title">
        <span class="help-side__icon" aria-hidden="true">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M8 9h8M8 13h5"/></svg>
        </span>
        <p class="eyebrow eyebrow--light">Support</p>
        <h1 id="help-title">Поддержка по заявкам, оплатам и входу</h1>
        <p class="lead">
          Напишите нам, если нужна помощь с регистрацией, оплатой, доступом к олимпиаде
          или прохождением теста.
        </p>

        <div class="contact-list">
          <a class="contact-card" :href="`mailto:${supportEmail}`">
            <span class="contact-card__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="M22 7l-10 6L2 7"/></svg>
            </span>
            <span>
              <strong>Email поддержки</strong>
              <span>{{ supportEmail }}</span>
            </span>
          </a>
          <a v-if="supportPhone" class="contact-card" :href="supportPhoneHref">
            <span class="contact-card__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>
            </span>
            <span>
              <strong>Телефон</strong>
              <span>{{ supportPhone }}</span>
            </span>
          </a>
        </div>

        <div class="trust-box">
          <strong>Когда стоит обратиться?</strong>
          <p>Если неясен статус заявки, не проходит оплата, не удаётся войти или открыть тест.</p>
        </div>
      </section>

      <section class="help-card" aria-labelledby="help-form-title">
        <div class="help-head">
          <p class="eyebrow">Обратная связь</p>
          <h2 id="help-form-title">Опишите вопрос в свободной форме</h2>
          <p>Чем точнее описание, тем быстрее команда сможет помочь.</p>
        </div>

        <form @submit.prevent="submit" class="help-form">
          <div class="grid">
            <label class="ds-field">
              <span class="ds-label">Имя</span>
              <input v-model="form.name" class="ds-input" type="text" placeholder="Ваше имя" autocomplete="name" required />
            </label>
            <label class="ds-field">
              <span class="ds-label">Email</span>
              <input v-model="form.email" class="ds-input" type="email" inputmode="email" placeholder="Email" autocomplete="email" required />
            </label>
            <label class="ds-field">
              <span class="ds-label">Телефон</span>
              <input v-model="form.phone" class="ds-input" type="tel" inputmode="tel" placeholder="Телефон" autocomplete="tel" />
            </label>
            <label class="ds-field">
              <span class="ds-label">Тема</span>
              <input v-model="form.topic" class="ds-input" type="text" placeholder="Например: оплата или вход" list="help-topics" required />
              <datalist id="help-topics">
                <option value="Оплата" />
                <option value="Вход в аккаунт" />
                <option value="Доступ к олимпиаде" />
                <option value="Апелляция" />
              </datalist>
            </label>
          </div>

          <label class="ds-field">
            <span class="ds-label">Сообщение</span>
            <textarea v-model="form.message" class="ds-input" rows="6" placeholder="Опишите проблему или вопрос" required></textarea>
          </label>

          <p v-if="message" class="ds-msg success" role="status">{{ message }}</p>
          <p v-if="error" class="ds-msg error" role="alert">{{ error }}</p>

          <button class="ds-btn ds-btn-primary ds-btn-lg submit-btn" :disabled="loading">
            <span v-if="loading" class="ds-spinner" aria-hidden="true"></span>
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
    const errors = err.response?.data?.errors
    error.value = errors
      ? Object.values(errors)[0][0]
      : (err.response?.status >= 400
        ? (err.response?.data?.message || 'Не удалось отправить обращение.')
        : 'Не удалось отправить обращение.')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.help-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 28px) 20px 80px;
  background: var(--bg);
}

.help-shell {
  max-width: 1160px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
  gap: 18px;
  align-items: start;
}

.help-side {
  display: grid;
  gap: 16px;
  padding: clamp(24px, 3vw, 36px);
  border-radius: var(--radius-xl);
  background:
    radial-gradient(400px 240px at 100% 0%, rgba(255, 255, 255, 0.16), transparent 70%),
    linear-gradient(155deg, #3d6cff 0%, #2b5bf5 50%, #2046d4 100%);
  color: #ffffff;
  box-shadow: var(--shadow-brand);
}

.help-side__icon {
  width: 58px;
  height: 58px;
  display: grid;
  place-items: center;
  border-radius: 18px;
  background: var(--sun);
  color: #1f1600;
  transform: rotate(-5deg);
}

.eyebrow {
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

.eyebrow--light { color: var(--sun); }

.help-side h1 { font-size: clamp(24px, 3vw, 34px); }

.lead {
  color: rgba(255, 255, 255, 0.86);
  font-size: 16px;
  line-height: 1.6;
}

.contact-list {
  display: grid;
  gap: 10px;
}

.contact-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
  text-decoration: none;
  transition: background-color var(--dur) ease;
}

.contact-card:hover { background: rgba(255, 255, 255, 0.2); }

.contact-card__icon {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 12px;
  background: #ffffff;
  color: var(--brand);
}

.contact-card strong {
  display: block;
  font-size: 14px;
  opacity: 0.85;
}

.contact-card span span {
  font-size: 15.5px;
  font-weight: 600;
  word-break: break-word;
}

.trust-box {
  display: grid;
  gap: 4px;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.12);
}

.trust-box p {
  color: rgba(255, 255, 255, 0.82);
  font-size: 14.5px;
}

.help-card {
  display: grid;
  gap: 20px;
  padding: clamp(22px, 3vw, 34px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}

.help-head h2 {
  margin-top: 4px;
  font-size: clamp(20px, 2.4vw, 26px);
}

.help-head p:last-child {
  margin-top: 6px;
  color: var(--text-secondary);
}

.help-form {
  display: grid;
  gap: 14px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

textarea.ds-input {
  min-height: 140px;
  resize: vertical;
  line-height: 1.5;
}

.submit-btn { justify-self: start; min-width: 240px; }

@media (max-width: 860px) {
  .help-shell { grid-template-columns: 1fr; }
}

@media (max-width: 600px) {
  .help-page { padding: calc(var(--header-h) + 14px) 12px 96px; }
  .grid { grid-template-columns: 1fr; }
  .submit-btn { width: 100%; min-width: 0; }
}
</style>
