<template>
  <div class="profile-shell">
    <StatePanel
      v-if="loading"
      class="profile-shell__container"
      tone="neutral" loading
      eyebrow="Кабинет"
      title="Загружаем кабинет"
      description="Подготавливаем профиль, участников и быстрые действия."
    />

    <StatePanel
      v-else-if="!userStore.user"
      class="profile-shell__container"
      tone="warning"
      eyebrow="Кабинет"
      title="Нужен вход в аккаунт"
      description="Войдите, чтобы видеть участников, заявки, оплаты, уведомления и результаты."
    >
      <template #actions>
        <RouterLink to="/login" class="ds-btn ds-btn-primary">Войти</RouterLink>
        <RouterLink to="/register" class="ds-btn ds-btn-ghost">Регистрация</RouterLink>
      </template>
    </StatePanel>

    <template v-else>
      <section class="profile-hero profile-shell__container">
        <div class="profile-hero__main">
          <span class="profile-hero__avatar" aria-hidden="true">{{ avatarLetter }}</span>
          <div>
            <p class="profile-eyebrow">Родительский кабинет</p>
            <h1>{{ userStore.user.name }}</h1>
            <p class="profile-hero__copy">Один обзор для первого визита и отдельные разделы для работы без перегруза.</p>
            <div class="profile-hero__meta">
              <span>{{ userStore.user.email }}</span>
              <span>{{ userStore.user.phone || 'Телефон не указан' }}</span>
              <span>{{ userStore.user.city || 'Город не указан' }}</span>
            </div>
          </div>
        </div>

        <div class="profile-actions">
          <RouterLink v-if="canReturnToAdminPanel" to="/admin" class="profile-btn ghost">Вернуться в админку</RouterLink>
          <RouterLink to="/edit-profile" class="profile-btn outline">Редактировать профиль</RouterLink>
          <RouterLink to="/subject" class="profile-btn primary highlight-subtle">Выбрать олимпиаду</RouterLink>
        </div>
      </section>

      <section class="profile-toolbar profile-shell__container">
        <div class="profile-toolbar__row">
          <div>
            <p class="profile-eyebrow">Контекст участника</p>
            <h2>{{ selectedChild ? selectedChild.full_name : 'Все участники' }}</h2>
            <p class="profile-toolbar__hint">
              Выбор участника применяется к заявкам, оплатам, тренировкам и результатам.
            </p>
          </div>

          <div class="profile-toolbar__actions">
            <label class="profile-field">
              <span>Активный участник</span>
              <select v-model="selectedChildId">
                <option value="">Все участники</option>
                <option v-for="child in userStore.children" :key="child.id" :value="String(child.id)">
                  {{ child.full_name }}
                </option>
              </select>
            </label>
            <RouterLink to="/profile/children#participant-form" class="profile-btn outline">Управлять участниками</RouterLink>
          </div>
        </div>

        <div class="profile-toolbar__meta">
          <span>Детей: <strong>{{ userStore.stats.children || userStore.children.length }}</strong></span>
          <span>Активных заявок: <strong>{{ userStore.stats.olympiads || 0 }}</strong></span>
          <span>Готово к старту: <strong>{{ userStore.stats.ready_to_start || 0 }}</strong></span>
          <span>Непрочитанных уведомлений: <strong>{{ userStore.notificationsUnread }}</strong></span>
        </div>

        <nav class="profile-nav" aria-label="Разделы кабинета">
          <RouterLink
            v-for="section in profileSections"
            :key="section.name"
            :to="section.to"
            class="profile-nav__link"
            active-class=""
            exact-active-class="is-active"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="section.icon"/></svg>
            {{ section.label }}
            <span v-if="section.name === 'ProfileNotifications' && userStore.notificationsUnread" class="profile-nav__badge">{{ userStore.notificationsUnread }}</span>
          </RouterLink>
        </nav>
      </section>

      <RouterView />
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, RouterView } from 'vue-router'
import StatePanel from '../../components/StatePanel.vue'
import { useUserStore } from '../../stores/user'
import { hasAdminAccess } from '../../js/adminAccess'
import { profileSections } from '../../js/profileSections'

const userStore = useUserStore()
const loading = ref(true)

const canReturnToAdminPanel = computed(() => Boolean(hasAdminAccess(userStore.user) && userStore.sessionType === 'admin'))
const selectedChild = computed(() => userStore.selectedChild)
const avatarLetter = computed(() => (userStore.user?.name || '?').charAt(0).toUpperCase())

const selectedChildId = computed({
  get: () => userStore.selectedChildId || '',
  set: (value) => {
    userStore.setSelectedChild(value || null)
  },
})

onMounted(async () => {
  loading.value = true
  await userStore.fetchUser()
  loading.value = false
})

watch(
  () => userStore.children,
  (children) => {
    if (!children.length && userStore.selectedChildId) {
      userStore.setSelectedChild(null)
    }
  }
)
</script>

<style src="../../css/profile-hub.css"></style>
