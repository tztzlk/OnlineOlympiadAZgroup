<template>
  <span class="status-badge" :class="`status-badge--${tone}`">
    <span class="status-badge__dot" aria-hidden="true"></span>
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
  gap: 7px;
  padding: 5px 11px;
  border-radius: var(--radius-xs);
  font-size: 13px;
  font-weight: 600;
  line-height: 1.4;
  white-space: nowrap;
}

.status-badge__dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
}

.status-badge--success { background: var(--success-soft); color: var(--success-ink); }
.status-badge--warning { background: var(--warning-soft); color: var(--warning-ink); }
.status-badge--danger,
.status-badge--error { background: var(--danger-soft); color: var(--danger-ink); }
.status-badge--info,
.status-badge--neutral { background: var(--brand-soft); color: var(--brand-ink); }
.status-badge--muted { background: var(--bg-alt); color: var(--text-secondary); }
</style>
