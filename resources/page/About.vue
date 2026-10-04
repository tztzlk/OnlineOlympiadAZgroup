<template>
  <div class="about-page">
    <div class="about-wrap">
      <header class="hero">
        <span class="ds-eyebrow">О платформе</span>
        <h1>О платформе Online Olympiad</h1>
        <p class="lead">
          Мы создаем безопасную и удобную платформу для школьников, родителей и образовательных
          учреждений, где можно готовиться к олимпиадам, участвовать в тестированиях и отслеживать прогресс.
        </p>
      </header>

      <section class="grid">
        <article class="card card--blue" v-reveal="0">
          <span class="card__icon" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
          </span>
          <h2>Что уже доступно</h2>
          <ul>
            <li>Онлайн-олимпиады по школьным предметам</li>
            <li>Режим тренировки с мгновенной проверкой</li>
            <li>Профили детей внутри одного родительского аккаунта</li>
            <li>Результаты, сертификаты и история оплат</li>
          </ul>
        </article>

        <article class="card" v-reveal="1">
          <span class="card__icon card__icon--sun" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg>
          </span>
          <h2>Для кого платформа</h2>
          <ul>
            <li>Для родителей, которые управляют участием детей</li>
            <li>Для школьников, которым нужен удобный тренировочный режим</li>
            <li>Для школ и образовательных центров, которым важна отчетность</li>
          </ul>
        </article>
      </section>

      <section class="callback-card" aria-labelledby="callback-title">
        <div class="callback-card__text">
          <p class="eyebrow">Callback</p>
          <h2 id="callback-title">Оставить заявку на обратный звонок</h2>
          <p>Мы свяжемся с вами, если нужна помощь по участию, оплате или подключению школы.</p>
        </div>

        <form class="callback-form" @submit.prevent="submit">
          <label class="ds-field">
            <span class="sr-only">Ваше имя</span>
            <input v-model="form.name" class="ds-input" placeholder="Ваше имя" autocomplete="name" required />
          </label>
          <label class="ds-field">
            <span class="sr-only">Телефон</span>
            <input v-model="form.phone" class="ds-input" type="tel" inputmode="tel" placeholder="+7 700 000 00 00" autocomplete="tel" required />
          </label>
          <label class="ds-field">
            <span class="sr-only">Email</span>
            <input v-model="form.email" class="ds-input" type="email" inputmode="email" placeholder="email@example.com" autocomplete="email" />
          </label>
          <label class="ds-field">
            <span class="sr-only">Вопрос</span>
            <textarea v-model="form.message" class="ds-input" rows="4" placeholder="Коротко опишите вопрос"></textarea>
          </label>
          <button :disabled="loading" class="ds-btn ds-btn-sun ds-btn-lg">
            <span v-if="loading" class="ds-spinner" aria-hidden="true"></span>
            {{ loading ? 'Отправляем...' : 'Отправить заявку' }}
          </button>
          <p v-if="message" class="ds-msg info" role="status">{{ message }}</p>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import api from '../js/api'
import { solveProofOfWork } from '../js/pow'

const form = reactive({
  name: '',
  phone: '',
  email: '',
  message: '',
})

const loading = ref(false)
const message = ref('')

const submit = async () => {
  loading.value = true
  message.value = ''

  try {
    const pow = await solveProofOfWork('callback')
    const { data } = await api.post('/support/callback', {
      ...form,
      ...pow,
    })
    message.value = data.message
    form.name = ''
    form.phone = ''
    form.email = ''
    form.message = ''
  } catch (error) {
    message.value = error.response?.data?.message || 'Не удалось отправить заявку.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.about-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 36px) 20px 80px;
  background:
    radial-gradient(700px 360px at 100% 0%, color-mix(in srgb, var(--brand) 9%, transparent), transparent 70%),
    var(--bg);
}

.about-wrap {
  max-width: 1160px;
  margin: 0 auto;
  display: grid;
  gap: 20px;
}

.hero {
  display: grid;
  justify-items: start;
  gap: 14px;
  max-width: 760px;
  margin-bottom: 8px;
}

.hero h1 { font-size: clamp(30px, 4.4vw, 50px); }

.lead {
  color: var(--text-secondary);
  font-size: 18px;
  line-height: 1.65;
}

.grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.card {
  display: grid;
  align-content: start;
  gap: 14px;
  padding: clamp(22px, 3vw, 32px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
}

.card--blue {
  background: linear-gradient(155deg, #3d6cff 0%, #2b5bf5 50%, #2046d4 100%);
  border-color: transparent;
  color: #ffffff;
}

.card__icon {
  width: 52px;
  height: 52px;
  display: grid;
  place-items: center;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.16);
  color: var(--sun);
}

.card__icon--sun { background: var(--sun-soft); color: #c98a00; }

.card h2 {
  font-family: var(--font-sans);
  font-size: 22px;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.card ul {
  display: grid;
  gap: 10px;
  list-style: none;
}

.card li {
  position: relative;
  padding-left: 30px;
  color: var(--text-secondary);
  font-size: 16px;
  line-height: 1.5;
}

.card--blue li { color: rgba(255, 255, 255, 0.9); }

.card li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 2px;
  width: 20px;
  height: 20px;
  border-radius: 7px;
  background: var(--success-soft) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2312a065' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E") center / 12px no-repeat;
}

.card--blue li::before {
  background-color: rgba(255, 255, 255, 0.18);
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffc933' stroke-width='3.4' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 6L9 17l-5-5'/%3E%3C/svg%3E");
}

.callback-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(300px, 440px);
  gap: 28px;
  align-items: start;
  padding: clamp(22px, 3vw, 36px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
}

.eyebrow {
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

.callback-card__text h2 {
  margin: 4px 0 10px;
  font-size: clamp(22px, 2.6vw, 30px);
}

.callback-card__text p:last-child {
  color: var(--text-secondary);
  font-size: 16px;
}

.callback-form {
  display: grid;
  gap: 10px;
}

textarea.ds-input {
  resize: vertical;
  line-height: 1.5;
}

@media (max-width: 860px) {
  .grid,
  .callback-card { grid-template-columns: 1fr; }
}

@media (max-width: 600px) {
  .about-page { padding: calc(var(--header-h) + 18px) 12px 96px; }
}
</style>
