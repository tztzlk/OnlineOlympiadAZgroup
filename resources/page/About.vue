<template>
  <div class="about-page">
    <section class="hero">
      <p class="eyebrow">About Us</p>
      <h1>О платформе Online Olympiad</h1>
      <p class="lead">
        Мы создаем безопасную и удобную платформу для школьников, родителей и образовательных
        учреждений, где можно готовиться к олимпиадам, участвовать в тестированиях и отслеживать прогресс.
      </p>
    </section>

    <section class="grid">
      <article class="card">
        <h2>Что уже доступно</h2>
        <ul>
          <li>Онлайн-олимпиады по школьным предметам</li>
          <li>Режим тренировки с мгновенной проверкой</li>
          <li>Профили детей внутри одного родительского аккаунта</li>
          <li>Результаты, сертификаты и история оплат</li>
        </ul>
      </article>

      <article class="card">
        <h2>Для кого платформа</h2>
        <ul>
          <li>Для родителей, которые управляют участием детей</li>
          <li>Для школьников, которым нужен удобный тренировочный режим</li>
          <li>Для школ и образовательных центров, которым важна отчетность</li>
        </ul>
      </article>
    </section>

    <section class="callback-card">
      <div>
        <p class="eyebrow">Callback</p>
        <h2>Оставить заявку на обратный звонок</h2>
        <p>Мы свяжемся с вами, если нужна помощь по участию, оплате или подключению школы.</p>
      </div>

      <form class="callback-form" @submit.prevent="submit">
        <input v-model="form.name" placeholder="Ваше имя" required />
        <input v-model="form.phone" placeholder="+7 700 000 00 00" required />
        <input v-model="form.email" type="email" placeholder="email@example.com" />
        <textarea v-model="form.message" rows="4" placeholder="Коротко опишите вопрос"></textarea>
        <button :disabled="loading" class="primary-btn">
          {{ loading ? 'Отправляем...' : 'Отправить заявку' }}
        </button>
        <p v-if="message" class="message">{{ message }}</p>
      </form>
    </section>
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
* { box-sizing: border-box; }
.about-page { min-height: 100vh; padding: 110px 20px 60px; background: var(--bg); color: var(--text-primary); }
.hero, .grid, .callback-card { max-width: 1100px; margin: 0 auto; }
.hero { margin-bottom: 28px; }
.eyebrow { margin: 0 0 10px; text-transform: uppercase; letter-spacing: 0.08em; font-size: 12px; font-weight: 700; color: var(--accent-strong); }
h1, h2 { margin: 0; }
.lead { max-width: 760px; line-height: 1.7; color: var(--text-secondary); margin-top: 14px; }
.grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 24px; }
.card, .callback-card { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 24px; padding: 24px; box-shadow: var(--shadow-card); }
.card ul { margin: 14px 0 0; padding-left: 18px; line-height: 1.7; color: var(--text-secondary); }
.callback-card { display: grid; grid-template-columns: 1fr minmax(280px, 420px); gap: 20px; align-items: start; }
.callback-form { display: grid; gap: 12px; }
input, textarea { width: 100%; border-radius: 14px; border: 1px solid var(--surface-border); padding: 13px 14px; background: rgba(255,252,245,.95); color: var(--text-on-surface); }
.primary-btn { border: 0; border-radius: 14px; padding: 14px 16px; background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%); color: var(--text); font-weight: 700; cursor: pointer; box-shadow: 0 12px 26px rgba(201,171,99,.2); }
.message { margin: 0; color: var(--text-secondary); }
@media (max-width: 760px) { .callback-card { grid-template-columns: 1fr; } }
</style>
