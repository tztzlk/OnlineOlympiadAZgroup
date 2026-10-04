<template>
  <div class="countdown-badge" :class="{ 'is-open': isOpen }">
    <span class="countdown-badge__icon" aria-hidden="true">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2M9 2h6"/></svg>
    </span>
    <span class="countdown-badge__text">
      <span class="countdown-badge__label">{{ label }}</span>
      <strong class="countdown-badge__value">{{ value }}</strong>
    </span>
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

const isOpen = computed(() => {
  if (!props.target) return false
  const diff = new Date(props.target).getTime() - now.value
  return !Number.isNaN(diff) && diff <= 0
})

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
  align-items: center;
  gap: 10px;
  padding: 8px 12px 8px 8px;
  border-radius: var(--radius-sm);
  background: var(--sun-soft);
}

.countdown-badge__icon {
  width: 30px;
  height: 30px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 9px;
  background: var(--card);
  color: #d99a00;
}

.countdown-badge__text {
  display: grid;
  line-height: 1.25;
}

.countdown-badge__label {
  color: var(--sun-ink);
  font-size: 12.5px;
  font-weight: 500;
}

.countdown-badge__value {
  color: var(--text);
  font-size: 15px;
  font-variant-numeric: tabular-nums;
}

.countdown-badge.is-open { background: var(--success-soft); }
.countdown-badge.is-open .countdown-badge__icon { color: var(--success); }
.countdown-badge.is-open .countdown-badge__label { color: var(--success-ink); }
</style>
