<template>
  <div class="edit-page">
    <div class="edit-card">

      <h1>Редактирование профиля</h1>

      <form @submit.prevent="updateProfile">

        <div class="input-group">
          <label>Имя</label>
          <input v-model="form.name" required />
        </div>

        <div class="input-group">
          <label>Email</label>
          <input v-model="form.email" type="email" required />
        </div>

        <div class="input-group">
          <label>Номер телефона</label>
          <input v-model="form.phone" required />
        </div>

        <p v-if="message" class="success">{{ message }}</p>
        <p v-if="error" class="error">{{ error }}</p>

        <div class="buttons">
          <button class="save-btn" :disabled="loading">
            {{ loading ? 'Сохранение...' : 'Сохранить' }}
          </button>

          <button type="button" class="cancel-btn" @click="router.back()">
            Отмена
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import api from "../js/api"
import { useRouter } from "vue-router"

const router = useRouter()

const form = ref({
  name: '',
  email: '',
  phone: ''
})

const loading = ref(false)
const message = ref('')
const error = ref('')



onMounted(async () => {
  try {
    const res = await api.get('/profile')
    form.value = res.data
  } catch {
    router.push('/login')
  }
})



const updateProfile = async () => {

  loading.value = true
  error.value = ''
  message.value = ''

  try {

    await api.put('/update-profile', form.value)

    message.value = 'Профиль успешно обновлён ✅'

    setTimeout(() => {
      router.push('/profile')
    }, 1000)

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

.edit-page {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px; 
}

.edit-card {
  width: 100%;
  max-width: 500px;
  background: white;
  padding: 35px;
  border-radius: 22px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.08);
  box-sizing: border-box;
}

h1 {
  margin-bottom: 25px;
  font-size: 1.5rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 18px;
}

input {
  padding: 13px;
  border-radius: 12px;
  border: 2px solid #e5e7eb;
  font-size: 1rem;
}

input:focus {
  outline: none;
  border-color: #4f46e5;
}

.buttons {
  display: flex;
  gap: 10px;
  margin-top: 20px;
  flex-wrap: wrap; 
}

.save-btn, .cancel-btn {
  flex: 1 1 48%;
  padding: 14px;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  cursor: pointer;
}

.save-btn {
  background: #4f46e5;
  color: white;
  font-weight: 600;
}

.cancel-btn {
  background: #e5e7eb;
}

.success {
  color: #16a34a;
  margin-top: 10px;
}

.error {
  color: #dc2626;
  margin-top: 10px;
}


@media (max-width: 480px) {
  .edit-card {
    padding: 25px;
    border-radius: 16px;
  }

  h1 {
    font-size: 1.3rem;
    margin-bottom: 20px;
  }

  input {
    padding: 12px;
    font-size: 0.95rem;
  }

  .buttons {
    flex-direction: column;
  }

  .save-btn, .cancel-btn {
    flex: 1 1 100%;
  }
}

</style>

