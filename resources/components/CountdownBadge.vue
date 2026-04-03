<template>
  <div class="countdown-badge">
    <p class="countdown-badge__label">{{ label }}</p>
    <strong class="countdown-badge__value">{{ value }}</strong>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  target: { type: String, default: '' },
  label: { type: String, default: 'До старта' },
})

const now = ref(Date.now())
let timerId = null

const value = computed(() => {
  if (!props.target) {
    return 'Дата появится позже'
  }

  const diff = new Date(props.target).getTime() - now.value

  if (Number.isNaN(diff)) {
    return 'Расписание уточняется'
  }

  if (diff <= 0) {
    return 'Старт уже открыт'
  }

  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor((diff / (1000 * 60 * 60)) % 24)

  return `${days} д ${hours} ч`
})

onMounted(() => {
  timerId = window.setInterval(() => {
    now.value = Date.now()
  }, 60000)
})

onBeforeUnmount(() => {
  if (timerId) {
    window.clearInterval(timerId)
  }
})
</script>

<style scoped>
.countdown-badge {
  display: inline-flex;
  flex-direction: column;
  gap: 4px;
  padding: 12px 14px;
  border-radius: 16px;
  background: rgba(255, 252, 244, 0.88);
  border: 1px solid var(--surface-border);
}

.countdown-badge__label {
  margin: 0;
  font-size: 12px;
  color: var(--text-secondary);
}

.countdown-badge__value {
  color: var(--text);
  font-size: 16px;
}
</style>
