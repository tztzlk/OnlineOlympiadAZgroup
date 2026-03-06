<template>
  <div class="waiting-page">
    <h1>Ожидаем одобрения заявки</h1>
    <p v-if="status==='pending'">Ваша заявка на олимпиаду на рассмотрении.</p>
    <p v-else-if="status==='approved'">
      Ваша заявка одобрена! 
      <button @click="goToQuiz">Начать олимпиаду</button>
    </p>
    <p v-else-if="status==='rejected'">Ваша заявка отклонена.</p>
    <p v-else>Вы ещё не отправили заявку.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../js/api'
import { useRouter } from 'vue-router'

const router = useRouter()
const status = ref('pending')

async function fetchStatus() {
  try {
    const res = await api.get('/olympiad-request-status')
    status.value = res.data.status
  } catch (err) {
    console.error(err)
  }
}

function goToQuiz() {
  router.push('/quiz')
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
}
button {
  padding: 12px 20px;
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}
</style>
