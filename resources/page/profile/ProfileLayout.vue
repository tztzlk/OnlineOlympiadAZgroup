<template>
  <div class="profile-shell">
    <StatePanel
      v-if="loading"
      tone="neutral"
      eyebrow="Кабинет"
      title="Загружаем кабинет"
      description="Подготавливаем профиль, участников и быстрые действия."
    />

    <StatePanel
      v-else-if="!userStore.user"
      tone="warning"
      eyebrow="Кабинет"
      title="Нужен вход в аккаунт"
      description="Войдите, чтобы видеть участников, заявки, оплаты, уведомления и результаты."
    />

    <template v-else>
      <section class="profile-hero profile-shell__container">
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
          <span>Детей: {{ userStore.stats.children || userStore.children.length }}</span>
          <span>Активных заявок: {{ userStore.stats.olympiads || 0 }}</span>
          <span>Готово к старту: {{ userStore.stats.ready_to_start || 0 }}</span>
          <span>Непрочитанных уведомлений: {{ userStore.notificationsUnread }}</span>
        </div>

        <nav class="profile-nav" aria-label="Разделы кабинета">
          <RouterLink
            v-for="section in profileSections"
            :key="section.name"
            :to="section.to"
            class="profile-nav__link"
          >
            {{ section.label }}
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
