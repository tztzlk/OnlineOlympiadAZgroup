<template>
  <div class="waiting-page">
    <h1>Статус участия и оплаты</h1>

    <p v-if="status === 'pending'">
      Участие требует дополнительной проверки. Пожалуйста, дождитесь обновления статуса.
    </p>

    <template v-else-if="status === 'approved'">
      <p v-if="paymentStatus === 'paid'">
        Оплата подтверждена, доступ к олимпиаде открыт.
      </p>
      <p v-else>
        Участие оформлено. Оплатите олимпиаду и дождитесь подтверждения платежа.
      </p>

      <div class="actions">
        <a v-if="paymentStatus !== 'paid' && paymentUrl" :href="paymentUrl" target="_blank" rel="noopener">Оплатить через Kaspi</a>
        <button v-if="paymentStatus === 'paid'" @click="goToQuiz">Начать олимпиаду</button>
      </div>
    </template>

    <p v-else-if="status === 'rejected'">Участие отклонено.</p>
    <p v-else>Вы ещё не оформили участие.</p>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../js/api'
import { useRouter } from 'vue-router'

const router = useRouter()
const status = ref('')
const paymentStatus = ref('')
const paymentUrl = ref('')

async function fetchStatus() {
  try {
    const res = await api.get('/olympiad/request/status')
    status.value = res.data.status || ''
    paymentStatus.value = res.data.payment_status || ''
    paymentUrl.value = res.data.payment_url || ''
  } catch (err) {
    console.error(err)
  }
}

function goToQuiz() {
  router.push('/subject')
}

onMounted(() => {
  fetchStatus()
  setInterval(fetchStatus, 5000)
})
</script>

<style scoped>
.waiting-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  background: #f3f4f6;
  padding: 40px;
  gap: 16px;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 12px;
}

button,
a {
  padding: 12px 20px;
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  text-decoration: none;
}
</style>
