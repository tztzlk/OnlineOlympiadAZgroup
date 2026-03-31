<template>
  <div class="help-page">
    <div class="help-card">
      <div class="help-head">
        <p class="eyebrow">Help Desk</p>
        <h1>Обратная связь</h1>
        <p>Напишите нам, если нужен доступ, помощь с оплатой, входом или прохождением теста.</p>
      </div>

      <form @submit.prevent="submit" class="help-form">
        <div class="grid">
          <input v-model="form.name" type="text" placeholder="Имя" required />
          <input v-model="form.email" type="email" placeholder="Email" required />
          <input v-model="form.phone" type="text" placeholder="Телефон" />
          <input v-model="form.topic" type="text" placeholder="Тема обращения" required />
        </div>
        <textarea v-model="form.message" rows="6" placeholder="Опишите проблему или вопрос" required></textarea>
        <p v-if="message" class="message success">{{ message }}</p>
        <p v-if="error" class="message error">{{ error }}</p>
        <button class="submit-btn" :disabled="loading">
          {{ loading ? 'Отправляем...' : 'Отправить обращение' }}
        </button>
      </form>

      <div class="contact-strip">
        <a href="mailto:support@olympiad.kz">support@olympiad.kz</a>
        <a href="tel:+77770000000">+7 (777) 000-00-00</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import api from '../js/api'
import { solveProofOfWork } from '../js/pow'

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
    const { data } = await api.post('/support/feedback', {
      ...form,
      ...pow,
    })
    message.value = data.message
    form.name = ''
    form.email = ''
    form.phone = ''
    form.topic = ''
    form.message = ''
  } catch (err) {
    error.value = err.response?.data?.message || 'Не удалось отправить обращение.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.help-page { min-height: 100vh; padding: 40px 20px; background: var(--bg); }
.help-card { width: min(900px, 100%); margin: 0 auto; padding: 32px; border-radius: 28px; background: var(--surface); border: 1px solid var(--surface-border); }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: .08em; color: #e11d48; font-size: 12px; font-weight: 700; }
h1 { margin: 0 0 10px; color: var(--text-on-surface); }
.help-head p { color: var(--text-muted-on-surface); line-height: 1.6; }
.help-form { margin-top: 24px; display: grid; gap: 14px; }
.grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
input, textarea { width: 100%; padding: 14px 16px; border-radius: 14px; border: 1px solid var(--surface-border); background: var(--surface-soft); color: var(--text-on-surface); }
.message { margin: 0; font-size: 14px; }
.success { color: #16a34a; }
.error { color: #dc2626; }
.submit-btn { justify-self: start; border: 0; border-radius: 14px; padding: 14px 20px; background: linear-gradient(90deg, #e11d48, #fb7185); color: #fff; font-weight: 700; cursor: pointer; }
.contact-strip { margin-top: 22px; display: flex; gap: 16px; flex-wrap: wrap; }
.contact-strip a { color: #e11d48; text-decoration: none; font-weight: 600; }
@media (max-width: 640px) { .help-card { padding: 24px 18px; } .grid { grid-template-columns: 1fr; } .submit-btn { width: 100%; } }
</style>
