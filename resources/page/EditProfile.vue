<template>
  <div class="edit-page">
    <div class="edit-card">
      <form @submit.prevent="updateProfile">
        <div class="input-group">
          <input v-model="form.name" type="text" required placeholder="Имя" aria-label="Имя" />
        </div>
        <div class="input-group">
          <input v-model="form.email" type="email" required placeholder="Email" aria-label="Email" />
        </div>
        <div class="input-group">
          <input v-model="form.school" type="text" required placeholder="Школа" aria-label="Школа" />
        </div>
        <div class="input-group">
          <input v-model="form.city" type="text" required placeholder="Город" aria-label="Город" />
        </div>
        <div class="input-group">
          <input v-model="form.phone" type="tel" required placeholder="Номер телефона" aria-label="Номер телефона" />
        </div>

        <p v-if="message" class="message success">{{ message }}</p>
        <p v-if="error" class="message error">{{ error }}</p>

        <div class="buttons">
          <button type="submit" class="save-btn" :disabled="loading">
            {{ loading ? 'Сохранение...' : 'Сохранить' }}
          </button>
          <button type="button" class="cancel-btn" @click="router.back()">Отмена</button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../js/api'
import { useUserStore } from '../stores/user'

const router = useRouter()
const userStore = useUserStore()

const form = ref({
  name: '',
  email: '',
  school: '',
  city: '',
  phone: '',
})

const loading = ref(false)
const message = ref('')
const error = ref('')

onMounted(async () => {
  try {
    const res = await api.get('/profile')
    form.value = {
      name: res.data.name || '',
      email: res.data.email || '',
      school: res.data.school || '',
      city: res.data.city || '',
      phone: res.data.phone || '',
    }
  } catch {
    router.push('/login')
  }
})

const updateProfile = async () => {
  loading.value = true
  error.value = ''
  message.value = ''

  try {
    const { data } = await api.put('/profile', form.value)
    userStore.user = data.user
    message.value = 'Профиль успешно обновлён'
    setTimeout(() => router.push('/profile'), 900)
  } catch (err) {
    if (err.response?.data?.errors) {
      error.value = Object.values(err.response.data.errors)[0][0]
    } else {
      error.value = 'Ошибка обновления'
    }
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.edit-page { min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; background: var(--bg-primary, #0f0f0f); }
.edit-card { width: 100%; max-width: 460px; background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 20px 50px rgba(0,0,0,0.35); box-sizing: border-box; }
.input-group { margin-bottom: 16px; }
.input-group:last-of-type { margin-bottom: 24px; }
input { width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #e0e0e0; font-size: 1rem; background: #fff; color: #1a1a1a; box-sizing: border-box; }
input::placeholder { color: #9ca3af; }
input:focus { outline: none; border-color: #e11d48; }
.buttons { display: flex; gap: 12px; margin-top: 8px; flex-wrap: wrap; }
.save-btn, .cancel-btn { flex: 1 1 0; min-width: 0; padding: 14px 20px; border: none; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; }
.save-btn { background: #e11d48; color: #fff; }
.save-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.cancel-btn { background: #e5e7eb; color: #4b5563; }
.message { margin: 0 0 12px; font-size: 0.95rem; }
.message.success { color: #16a34a; }
.message.error { color: #dc2626; }
@media (max-width: 480px) { .edit-card { padding: 28px; border-radius: 16px; } }
</style>
