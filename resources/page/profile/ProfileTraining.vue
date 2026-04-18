<template>
  <div class="profile-subpage">
    <div class="profile-subpage__container">
      <section class="profile-section">
        <div class="profile-section__head">
          <div>
            <p class="profile-eyebrow">Тренировки</p>
            <h2>Последние тренировочные попытки</h2>
            <p class="profile-section__copy">Отдельный список тренировок помогает не смешивать подготовку с боевыми результатами олимпиады.</p>
          </div>
        </div>

        <div v-if="trainings.length" class="profile-compact-list">
          <article v-for="item in trainings" :key="item.id" class="profile-compact">
            <strong>{{ item.child_name || 'Без участника' }}</strong>
            <span>{{ item.subject }} · {{ item.percent }}%</span>
            <small>{{ item.date }}</small>
          </article>
        </div>
        <p v-else class="profile-empty">Тренировки появятся после первых пробных попыток.</p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import api from '../../js/api'
import { useUserStore } from '../../stores/user'

const userStore = useUserStore()
const trainings = ref([])

const hydrate = async () => {
  const { data } = await api.get('/profile/trainings', {
    params: userStore.selectedChildId ? { child_profile_id: userStore.selectedChildId } : {},
  })
  trainings.value = data
}

onMounted(hydrate)
watch(() => userStore.selectedChildId, hydrate)
</script>

<style src="../../css/profile-hub.css"></style>
