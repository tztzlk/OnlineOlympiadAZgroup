<template>
  <div class="profile-subpage">
    <div class="profile-subpage__container">
      <section class="profile-section">
        <div class="profile-section__head">
          <div>
            <p class="profile-eyebrow">Оплаты</p>
            <h2>История оплат</h2>
            <p class="profile-section__copy">Все платежи по участникам собраны отдельно, без смешивания с заявками и результатами.</p>
          </div>
        </div>

        <div v-if="payments.length" class="profile-compact-list">
          <article v-for="item in payments" :key="item.id" class="profile-compact">
            <div class="profile-compact-head">
              <strong>{{ item.child_name || 'Без участника' }}</strong>
              <StatusBadge :label="item.status_meta?.label || item.status" :tone="item.status_meta?.tone || 'neutral'" />
            </div>
            <span>{{ item.subject || 'Предмет не указан' }} · {{ item.amount }} {{ item.currency }}</span>
            <small>{{ item.date }}</small>
          </article>
        </div>
        <p v-else class="profile-empty">Когда заявки начнут переходить к оплате, история появится здесь.</p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import api from '../../js/api'
import StatusBadge from '../../components/StatusBadge.vue'
import { useUserStore } from '../../stores/user'

const userStore = useUserStore()
const payments = ref([])

const hydrate = async () => {
  const { data } = await api.get('/profile/payments', {
    params: userStore.selectedChildId ? { child_profile_id: userStore.selectedChildId } : {},
  })
  payments.value = data
}

onMounted(hydrate)
watch(() => userStore.selectedChildId, hydrate)
</script>

<style src="../../css/profile-hub.css"></style>
