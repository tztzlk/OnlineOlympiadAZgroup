<template>
  <div
    class="state-panel"
    :class="`state-panel--${tone}`"
    :role="tone === 'danger' || tone === 'error' ? 'alert' : 'status'"
    :aria-busy="loading ? 'true' : undefined"
  >
    <div class="state-panel__icon" aria-hidden="true">
      <span v-if="loading" class="state-panel__spinner"></span>
      <span v-else-if="icon" class="state-panel__glyph">{{ icon }}</span>
      <svg v-else-if="tone === 'success'" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
      <svg v-else-if="tone === 'warning'" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/></svg>
      <svg v-else-if="tone === 'danger' || tone === 'error'" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9.5"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
      <svg v-else-if="tone === 'empty'" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.5 5.1L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.5-6.9A2 2 0 0 0 16.8 4H7.2a2 2 0 0 0-1.7 1.1z"/></svg>
      <svg v-else width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="12" cy="12" r="9.5"/><path d="M12 16v-4M12 8h.01"/></svg>
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
defineProps({
  tone: { type: String, default: 'neutral' },
  title: { type: String, required: true },
  description: { type: String, required: true },
  eyebrow: { type: String, default: '' },
  icon: { type: String, default: '' },
  loading: { type: Boolean, default: false },
})
</script>

<style scoped>
.state-panel {
  display: grid;
  grid-template-columns: 56px minmax(0, 1fr);
  gap: 18px;
  padding: 22px 24px;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  background: var(--card);
  box-shadow: var(--shadow-xs);
}

.state-panel__icon {
  width: 56px;
  height: 56px;
  display: grid;
  place-items: center;
  border-radius: 18px;
  background: var(--brand-soft);
  color: var(--brand);
}

.state-panel__glyph {
  font-size: 22px;
  font-weight: 800;
}

.state-panel__spinner {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  border: 3px solid color-mix(in srgb, var(--brand) 22%, transparent);
  border-top-color: var(--brand);
  animation: sp-spin 0.8s linear infinite;
}

@keyframes sp-spin { to { transform: rotate(360deg); } }

.state-panel__eyebrow {
  margin-bottom: 4px;
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

.state-panel__title {
  margin-bottom: 6px;
  font-size: 19px;
  line-height: 1.35;
}

.state-panel__body {
  color: var(--text-secondary);
  font-size: 15.5px;
  line-height: 1.6;
}

.state-panel__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 16px;
}

/* Мягкий фон панели в цвет тона: состояние считывается сразу */
.state-panel--success { background: color-mix(in srgb, var(--success-soft) 45%, var(--card)); border-color: color-mix(in srgb, var(--success) 22%, var(--border)); }
.state-panel--success .state-panel__icon { background: var(--success); color: #ffffff; }
.state-panel--success .state-panel__eyebrow { color: var(--success-ink); }

.state-panel--warning { background: color-mix(in srgb, var(--warning-soft) 55%, var(--card)); border-color: color-mix(in srgb, var(--warning) 26%, var(--border)); }
.state-panel--warning .state-panel__icon { background: var(--warning-soft); color: #c27800; }
.state-panel--warning .state-panel__eyebrow { color: var(--warning-ink); }

.state-panel--danger,
.state-panel--error { background: color-mix(in srgb, var(--danger-soft) 55%, var(--card)); border-color: color-mix(in srgb, var(--danger) 24%, var(--border)); }
.state-panel--danger .state-panel__icon,
.state-panel--error .state-panel__icon { background: var(--danger); color: #ffffff; }
.state-panel--danger .state-panel__eyebrow,
.state-panel--error .state-panel__eyebrow { color: var(--danger-ink); }

.state-panel--empty .state-panel__icon { background: var(--bg-alt); color: var(--text-secondary); }
.state-panel--empty { border-style: dashed; border-color: var(--border-strong); }

@media (max-width: 640px) {
  .state-panel {
    grid-template-columns: 1fr;
    gap: 14px;
    padding: 20px;
  }
  .state-panel__actions :deep(.ds-btn),
  .state-panel__actions :deep(a),
  .state-panel__actions :deep(button) { flex: 1 1 100%; }
}
</style>
