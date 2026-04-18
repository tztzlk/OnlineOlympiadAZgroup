<template>
  <div class="profile-subpage">
    <div class="profile-subpage__container profile-grid-two">
      <StatePanel
        class="profile-span-two"
        :tone="userStore.currentTask?.tone || 'neutral'"
        eyebrow="Текущий шаг"
        :title="userStore.currentTask?.title || 'Кабинет готов к работе'"
        :description="userStore.currentTask?.description || 'Выберите раздел ниже и продолжите работу.'"
      >
        <template #actions>
          <RouterLink
            v-if="userStore.currentTask?.action_url"
            class="profile-btn primary"
            :to="userStore.currentTask.action_url"
          >
            {{ userStore.currentTask.action_label || 'Открыть' }}
          </RouterLink>
        </template>
      </StatePanel>

      <section class="profile-panel">
        <div class="profile-panel__head">
          <div>
            <p class="profile-eyebrow">Сводка</p>
            <h2>Что важно сейчас</h2>
          </div>
        </div>

        <div class="profile-stats">
          <article class="profile-stat">
            <span>Профили детей</span>
            <strong>{{ userStore.stats.children || userStore.children.length }}</strong>
          </article>
          <article class="profile-stat">
            <span>Активные заявки</span>
            <strong>{{ olympiads.length }}</strong>
          </article>
          <article class="profile-stat">
            <span>Готово к старту</span>
            <strong>{{ readyToStart }}</strong>
          </article>
          <article class="profile-stat">
            <span>Результаты</span>
            <strong>{{ userStore.stats.results || 0 }}</strong>
          </article>
        </div>
      </section>

      <section v-if="userStore.onboarding" class="profile-panel">
        <div class="profile-panel__head">
          <div>
            <p class="profile-eyebrow">Onboarding</p>
            <h2>Путь участника</h2>
          </div>
          <strong class="profile-progress-value">{{ userStore.onboarding.progress_percent }}%</strong>
        </div>

        <div class="profile-progress-track">
          <div class="profile-progress-fill" :style="{ width: `${userStore.onboarding.progress_percent}%` }"></div>
        </div>

        <div class="profile-step-list">
          <div
            v-for="step in visibleSteps"
            :key="step.key"
            class="profile-step"
            :class="{ done: step.completed }"
          >
            <div class="profile-step__index">{{ step.index }}</div>
            <div>
              <strong>{{ step.label }}</strong>
              <p>{{ step.description }}</p>
            </div>
            <button
              v-if="!step.completed"
              type="button"
              class="profile-step-btn"
              @click="completeOnboarding(step.key)"
            >
              Готово
            </button>
          </div>
        </div>
      </section>

      <section class="profile-panel">
        <div class="profile-panel__head">
          <div>
            <p class="profile-eyebrow">Быстрые действия</p>
            <h2>Куда перейти дальше</h2>
          </div>
        </div>

        <div class="profile-preview-grid">
          <article class="profile-preview-card">
            <strong>Участники</strong>
            <p>Добавляйте детей и обновляйте их данные без поиска по длинной странице.</p>
            <RouterLink class="profile-btn outline" to="/profile/children">Открыть участников</RouterLink>
          </article>
          <article class="profile-preview-card">
            <strong>Олимпиады</strong>
            <p>Смотрите статусы заявок, оплату и быстрый переход к старту олимпиады.</p>
            <RouterLink class="profile-btn outline" to="/profile/olympiads">Открыть олимпиады</RouterLink>
          </article>
          <article class="profile-preview-card">
            <strong>Результаты</strong>
            <p>Итоги участия и сертификаты остаются на отдельной странице без дублирования.</p>
            <RouterLink class="profile-btn outline" to="/results">Открыть результаты</RouterLink>
          </article>
        </div>
      </section>

      <section class="profile-panel">
        <div class="profile-panel__head">
          <div>
            <p class="profile-eyebrow">Уведомления</p>
            <h2>Последние события</h2>
          </div>
          <RouterLink class="profile-btn outline compact" to="/profile/notifications">Все уведомления</RouterLink>
        </div>

        <div v-if="userStore.notifications.length" class="profile-notification-list">
          <article
            v-for="item in userStore.notifications.slice(0, 3)"
            :key="item.id"
            class="profile-notification"
            :class="{ unread: !item.read_at }"
          >
            <strong>{{ item.title }}</strong>
            <p>{{ item.body }}</p>
            <div class="profile-notification__meta">
              <span>{{ item.date }}</span>
              <button
                v-if="!item.read_at"
                class="profile-link-btn"
                @click="userStore.markNotificationRead(item.id)"
              >
                Прочитано
              </button>
            </div>
          </article>
        </div>
        <p v-else class="profile-empty">Новых уведомлений пока нет.</p>
      </section>

      <section class="profile-panel">
        <div class="profile-panel__head">
          <div>
            <p class="profile-eyebrow">Последние результаты</p>
            <h2>Короткий preview</h2>
          </div>
          <RouterLink class="profile-btn outline compact" to="/results">Все результаты</RouterLink>
        </div>

        <div v-if="latestResults.length" class="profile-list-grid">
          <article v-for="item in latestResults" :key="item.id" class="profile-list-card">
            <div class="profile-card-head">
              <div>
                <h3>{{ item.subject }}</h3>
                <p>{{ item.child_name }} · {{ item.category_label }}</p>
              </div>
              <StatusBadge :label="item.status_meta?.label || item.status" :tone="item.status_meta?.tone || 'neutral'" />
            </div>
            <p>Результат: {{ item.score }}/{{ item.total }} · {{ item.percent }}%. {{ item.date }}</p>
            <div class="profile-actions-row">
              <RouterLink class="profile-btn outline" to="/results">Открыть список</RouterLink>
              <RouterLink class="profile-btn primary" :to="item.certificate_preview_url">Превью сертификата</RouterLink>
            </div>
          </article>
        </div>
        <p v-else class="profile-empty">После первой завершённой олимпиады здесь появится краткий preview результатов.</p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import api from '../../js/api'
import StatePanel from '../../components/StatePanel.vue'
import StatusBadge from '../../components/StatusBadge.vue'
import { useUserStore } from '../../stores/user'

const userStore = useUserStore()
const olympiads = ref([])
const latestResults = ref([])

const selectedChildParams = computed(() => userStore.selectedChildId ? { child_profile_id: userStore.selectedChildId } : {})
const readyToStart = computed(() => olympiads.value.filter((item) => item.status === 'approved' && item.payment_status === 'paid' && !item.completed).length)
const visibleSteps = computed(() => (userStore.onboarding?.steps || []).slice(0, 3))

const hydrate = async () => {
  await userStore.fetchUser()
  const [olympiadsRes, resultsRes] = await Promise.all([
    api.get('/profile/olympiads', { params: selectedChildParams.value }),
    api.get('/profile/results', { params: selectedChildParams.value }),
  ])
  olympiads.value = olympiadsRes.data
  latestResults.value = resultsRes.data.slice(0, 3)
}

const completeOnboarding = async (step) => {
  await userStore.syncOnboarding({ step })
}

onMounted(hydrate)
watch(() => userStore.selectedChildId, hydrate)
</script>

<style src="../../css/profile-hub.css"></style>
