<template>
  <div class="notification-shell" ref="shellRef">
    <button type="button" class="notification-trigger" @click="toggleOpen">
      <span class="notification-trigger__icon">◌</span>
      <span class="notification-trigger__label">Уведомления</span>
      <span v-if="unreadCount" class="notification-trigger__count">{{ unreadCount }}</span>
    </button>

    <transition name="fade">
      <div v-if="open" class="notification-popover">
        <div class="notification-popover__head">
          <div>
            <p class="notification-popover__eyebrow">Центр событий</p>
            <h3>Что изменилось</h3>
          </div>
          <button type="button" class="notification-close" @click="open = false">×</button>
        </div>

        <div v-if="!items.length" class="notification-empty">
          Пока нет новых уведомлений. Когда появятся статусы по заявкам, оплатам и результатам, они будут здесь.
        </div>

        <div v-else class="notification-list">
          <article
            v-for="item in items"
            :key="item.id"
            class="notification-item"
            :class="{ unread: !item.read_at }"
          >
            <div class="notification-item__meta">
              <strong>{{ item.title }}</strong>
              <span>{{ item.date }}</span>
            </div>
            <p>{{ item.body }}</p>
            <div class="notification-item__actions">
              <RouterLink
                v-if="item.action_url"
                class="notification-link"
                :to="item.action_url"
                @click="open = false"
              >
                Открыть
              </RouterLink>
              <button
                v-if="!item.read_at"
                type="button"
                class="notification-mark"
                @click="$emit('mark-read', item.id)"
              >
                Прочитано
              </button>
            </div>
          </article>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

defineProps({
  items: { type: Array, default: () => [] },
  unreadCount: { type: Number, default: 0 },
})

defineEmits(['mark-read'])

const open = ref(false)
const shellRef = ref(null)

const toggleOpen = () => {
  open.value = !open.value
}

const handleClickOutside = (event) => {
  if (!shellRef.value?.contains(event.target)) {
    open.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))
</script>

<style scoped>
.notification-shell { position: relative; }

.notification-trigger {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  min-height: 42px;
  padding: 10px 14px;
  border-radius: 14px;
  border: 1px solid var(--border);
  background: var(--card);
  color: var(--text);
  font-weight: 700;
  cursor: pointer;
}

.notification-trigger__icon {
  width: 20px;
  height: 20px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(201, 171, 99, 0.16);
  color: var(--accent-strong);
}

.notification-trigger__count {
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: var(--success-bg);
  color: #2f6f4b;
  font-size: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.notification-popover {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: min(380px, calc(100vw - 24px));
  padding: 18px;
  border-radius: 22px;
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
  z-index: 1300;
}

.notification-popover__head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 12px;
}

.notification-popover__eyebrow {
  margin: 0 0 4px;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--accent-strong);
}

.notification-popover__head h3 {
  margin: 0;
  color: var(--text);
}

.notification-close {
  border: 0;
  background: rgba(201, 171, 99, 0.12);
  color: var(--accent-strong);
  width: 32px;
  height: 32px;
  border-radius: 10px;
  cursor: pointer;
}

.notification-empty {
  padding: 16px;
  border-radius: 18px;
  background: rgba(255, 252, 244, 0.82);
  color: var(--text-secondary);
  line-height: 1.6;
}

.notification-list {
  display: grid;
  gap: 10px;
  max-height: 420px;
  overflow: auto;
}

.notification-item {
  padding: 14px;
  border-radius: 18px;
  background: rgba(255, 252, 244, 0.82);
  border: 1px solid var(--surface-border);
  display: grid;
  gap: 8px;
}

.notification-item.unread {
  border-color: rgba(201, 171, 99, 0.34);
  box-shadow: 0 0 0 3px rgba(201, 171, 99, 0.09);
}

.notification-item__meta {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
}

.notification-item__meta strong { color: var(--text); }
.notification-item__meta span,
.notification-item p { color: var(--text-secondary); }
.notification-item p { margin: 0; line-height: 1.55; }

.notification-item__actions {
  display: flex;
  justify-content: space-between;
  gap: 10px;
  align-items: center;
}

.notification-link,
.notification-mark {
  border: 0;
  background: transparent;
  color: var(--accent-strong);
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  padding: 0;
}

.fade-enter-active, .fade-leave-active { transition: opacity .18s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 900px) {
  .notification-trigger__label { display: none; }
}
</style>
