<template>
  <div class="profile-subpage">
    <div class="profile-subpage__container">
      <section class="profile-section">
        <div class="profile-section__head">
          <div>
            <p class="profile-eyebrow">Уведомления</p>
            <h2>Центр уведомлений</h2>
            <p class="profile-section__copy">Здесь собраны все статусы заявок, оплаты и результаты без отвлекающих блоков вокруг.</p>
          </div>
          <button v-if="userStore.hasUnreadNotifications" class="profile-btn outline" @click="hydrate">Обновить</button>
        </div>

        <div v-if="notifications.length" class="profile-notification-list">
          <article
            v-for="item in notifications"
            :key="item.id"
            class="profile-notification"
            :class="{ unread: !item.read_at }"
          >
            <strong>{{ item.title }}</strong>
            <p>{{ item.body }}</p>
            <div class="profile-notification__meta">
              <span>{{ item.date }}</span>
              <div class="profile-actions-row">
                <RouterLink v-if="item.action_url" class="profile-btn outline compact" :to="item.action_url">Открыть</RouterLink>
                <button v-if="!item.read_at" class="profile-link-btn" @click="markRead(item.id)">Прочитано</button>
              </div>
            </div>
          </article>
        </div>
        <p v-else class="profile-empty">Уведомлений пока нет.</p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useUserStore } from '../../stores/user'

const userStore = useUserStore()
const notifications = ref([])

const hydrate = async () => {
  await userStore.fetchNotifications(30)
  notifications.value = userStore.notifications
}

const markRead = async (notificationId) => {
  await userStore.markNotificationRead(notificationId)
  notifications.value = userStore.notifications
}

onMounted(hydrate)
</script>

<style src="../../css/profile-hub.css"></style>
