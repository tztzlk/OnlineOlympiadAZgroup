<template>
  <div class="state-panel" :class="`state-panel--${tone}`">
    <div class="state-panel__icon">
      <span>{{ iconGlyph }}</span>
    </div>
    <div class="state-panel__content">
      <p v-if="eyebrow" class="state-panel__eyebrow">{{ eyebrow }}</p>
      <h3 class="state-panel__title">{{ title }}</h3>
      <p class="state-panel__body">{{ description }}</p>
      <div v-if="$slots.actions" class="state-panel__actions">
        <slot name="actions" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  tone: { type: String, default: 'neutral' },
  title: { type: String, required: true },
  description: { type: String, required: true },
  eyebrow: { type: String, default: '' },
  icon: { type: String, default: '' },
})

const iconGlyph = computed(() => {
  const map = {
    success: '✓',
    warning: '!',
    danger: '×',
    error: '×',
    empty: '○',
    neutral: '•',
  }

  return props.icon || map[props.tone] || '•'
})
</script>

<style scoped>
.state-panel {
  display: grid;
  grid-template-columns: 56px 1fr;
  gap: 16px;
  padding: 22px;
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
}

.state-panel__icon {
  width: 56px;
  height: 56px;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  font-weight: 800;
}

.state-panel__eyebrow {
  margin: 0 0 6px;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--accent-strong);
}

.state-panel__title {
  margin: 0 0 6px;
  font-size: 20px;
  color: var(--text);
}

.state-panel__body {
  margin: 0;
  color: var(--text-secondary);
  line-height: 1.65;
}

.state-panel__actions {
  margin-top: 14px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.state-panel--success .state-panel__icon {
  background: var(--success-bg);
  color: #2f6f4b;
}

.state-panel--warning .state-panel__icon {
  background: var(--warning-bg);
  color: #8d6f31;
}

.state-panel--danger .state-panel__icon,
.state-panel--error .state-panel__icon {
  background: var(--danger-bg);
  color: #8e4343;
}

.state-panel--empty .state-panel__icon,
.state-panel--neutral .state-panel__icon {
  background: rgba(201, 171, 99, 0.14);
  color: var(--accent-strong);
}

@media (max-width: 640px) {
  .state-panel {
    grid-template-columns: 1fr;
  }
}
</style>
