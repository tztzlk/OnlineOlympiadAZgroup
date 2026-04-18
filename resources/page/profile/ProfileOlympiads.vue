<template>
  <div class="profile-subpage">
    <div class="profile-subpage__container">
      <section class="profile-section">
        <div class="profile-section__head">
          <div>
            <p class="profile-eyebrow">Олимпиады</p>
            <h2>Статус участия</h2>
            <p class="profile-section__copy">Следите, где заявка ждёт проверки, где нужна оплата и где уже открыт доступ к старту.</p>
          </div>
          <RouterLink to="/subject" class="profile-btn primary">Выбрать олимпиаду</RouterLink>
        </div>

        <div v-if="olympiads.length" class="profile-list-grid">
          <article v-for="item in olympiads" :key="item.id" class="profile-list-card">
            <div class="profile-card-head">
              <div>
                <h3>{{ item.subject.name }}</h3>
                <p>{{ item.child?.full_name || 'Ребёнок не выбран' }}</p>
              </div>
              <div class="profile-badge-stack">
                <StatusBadge :label="item.status_meta.label" :tone="item.status_meta.tone" />
                <StatusBadge :label="item.payment_status_meta.label" :tone="item.payment_status_meta.tone" />
              </div>
            </div>

            <CountdownBadge :target="item.countdown?.target" :label="item.countdown?.label || 'Старт олимпиады'" />
            <p>{{ item.next_action }}</p>

            <div class="profile-actions-row">
              <button
                v-if="item.status === 'approved' && item.payment_status === 'paid' && !item.completed"
                class="profile-btn primary"
                @click="startQuiz(item.subject.id, item.child?.id)"
              >
                Начать олимпиаду
              </button>
              <button class="profile-btn outline" @click="startTraining(item.subject.id, item.child?.id)">Тренировка</button>
            </div>
          </article>
        </div>
        <p v-else class="profile-empty">По текущему участнику активных заявок пока нет.</p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import api from '../../js/api'
import CountdownBadge from '../../components/CountdownBadge.vue'
import StatusBadge from '../../components/StatusBadge.vue'
import { useUserStore } from '../../stores/user'

const router = useRouter()
const userStore = useUserStore()
const olympiads = ref([])

const hydrate = async () => {
  const { data } = await api.get('/profile/olympiads', {
    params: userStore.selectedChildId ? { child_profile_id: userStore.selectedChildId } : {},
  })
  olympiads.value = data
}

const startQuiz = (subjectId, childId) => {
  if (childId) userStore.setSelectedChild(childId)
  router.push({ path: `/quiz/${subjectId}`, query: childId ? { childId } : {} })
}

const startTraining = (subjectId, childId) => {
  if (childId) userStore.setSelectedChild(childId)
  router.push({ path: `/training/${subjectId}`, query: childId ? { childId } : {} })
}

onMounted(hydrate)
watch(() => userStore.selectedChildId, hydrate)
</script>

<style src="../../css/profile-hub.css"></style>
