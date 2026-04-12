<template>
  <span class="status-badge" :class="`status-badge--${tone}`">
    <span class="status-badge__dot"></span>
    {{ label }}
  </span>
</template>

<script setup>
defineProps({
  label: { type: String, required: true },
  tone: { type: String, default: 'neutral' },
})
</script>

<style scoped>
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.status-badge__dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
}

.status-badge--success { background: var(--success-bg); color: var(--success-soft); }
.status-badge--warning { background: var(--warning-bg); color: #8d6f31; }
.status-badge--danger,
.status-badge--error { background: var(--danger-bg); color: #8e4343; }
.status-badge--neutral { background: var(--info-soft); color: var(--info); }
</style>
