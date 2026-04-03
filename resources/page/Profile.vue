<template>
  <div class="profile-page">
    <StatePanel
      v-if="loading"
      tone="neutral"
      eyebrow="Кабинет"
      title="Загружаем кабинет"
      description="Собираем участников, заявки, оплаты, уведомления и результаты в одном месте."
    />

    <StatePanel
      v-else-if="!user"
      tone="warning"
      eyebrow="Кабинет"
      title="Нужен вход в аккаунт"
      description="Войдите, чтобы видеть детей, заявки, оплаты, результаты и сертификаты."
    />

    <template v-else>
      <section class="hero-card">
        <div class="hero-main">
          <p class="eyebrow">Родительский кабинет</p>
          <h1>{{ user.name }}</h1>
          <p class="hero-copy">Управляйте участниками, заявками, оплатами, тренировками и сертификатами из одного центра действий.</p>
          <div class="hero-meta">
            <span>{{ user.email }}</span>
            <span>{{ user.phone || 'Телефон не указан' }}</span>
            <span>{{ user.city || 'Город не указан' }}</span>
          </div>
        </div>

        <div class="hero-actions">
          <RouterLink v-if="canReturnToAdminPanel" to="/admin" class="btn btn-ghost">Вернуться в админку</RouterLink>
          <RouterLink to="/edit-profile" class="btn btn-outline">Редактировать профиль</RouterLink>
          <RouterLink to="/subject" class="btn btn-primary">Выбрать олимпиаду</RouterLink>
        </div>
      </section>

      <section class="stats-grid">
        <article class="stat-card">
          <span>Профили детей</span>
          <strong>{{ stats.children || children.length }}</strong>
          <small>Участники, которыми вы управляете</small>
        </article>
        <article class="stat-card">
          <span>Активные заявки</span>
          <strong>{{ stats.olympiads || olympiads.length }}</strong>
          <small>Все олимпиады и запросы в системе</small>
        </article>
        <article class="stat-card">
          <span>Доступно к старту</span>
          <strong>{{ stats.ready_to_start || 0 }}</strong>
          <small>Одобрено и оплачено, можно начинать</small>
        </article>
        <article class="stat-card">
          <span>Уведомления</span>
          <strong>{{ userStore.notificationsUnread }}</strong>
          <small>Непрочитанные статусы и события</small>
        </article>
      </section>

      <section class="dashboard-grid">
        <StatePanel
          class="dashboard-span"
          :tone="currentTask?.tone || 'neutral'"
          eyebrow="Текущий шаг"
          :title="currentTask?.title || 'Кабинет готов к работе'"
          :description="currentTask?.description || 'Выберите олимпиаду или откройте результаты.'"
        >
          <template #actions>
            <RouterLink v-if="currentTask?.action_url" class="btn btn-primary" :to="currentTask.action_url">
              {{ currentTask.action_label || 'Открыть' }}
            </RouterLink>
          </template>
        </StatePanel>

        <article v-if="onboarding" class="panel-card">
          <div class="panel-head">
            <div>
              <p class="eyebrow">Onboarding</p>
              <h2>Как пройти путь участника</h2>
            </div>
            <strong class="progress-value">{{ onboarding.progress_percent }}%</strong>
          </div>

          <div class="progress-track"><div class="progress-fill" :style="{ width: `${onboarding.progress_percent}%` }"></div></div>

          <div class="onboarding-steps">
            <div v-for="step in onboarding.steps" :key="step.key" class="onboarding-step" :class="{ done: step.completed }">
              <div class="onboarding-step__index">{{ step.index }}</div>
              <div>
                <strong>{{ step.label }}</strong>
                <p>{{ step.description }}</p>
              </div>
              <button
                v-if="!step.completed"
                type="button"
                class="step-complete"
                @click="completeOnboarding(step.key)"
              >
                Готово
              </button>
            </div>
          </div>
        </article>

        <article class="panel-card">
          <div class="panel-head">
            <div>
              <p class="eyebrow">Центр уведомлений</p>
              <h2>Последние события</h2>
            </div>
            <button v-if="userStore.hasUnreadNotifications" class="btn btn-outline compact" @click="refreshNotifications">Обновить</button>
          </div>

          <div v-if="userStore.notifications.length" class="notification-list">
            <article v-for="item in userStore.notifications" :key="item.id" class="notification-card" :class="{ unread: !item.read_at }">
              <strong>{{ item.title }}</strong>
              <p>{{ item.body }}</p>
              <div class="notification-meta">
                <span>{{ item.date }}</span>
                <button v-if="!item.read_at" class="link-btn" @click="userStore.markNotificationRead(item.id)">Прочитано</button>
              </div>
            </article>
          </div>
          <p v-else class="empty-text">Пока нет новых событий. Когда изменится статус заявки, оплаты или результата, карточки появятся здесь.</p>
        </article>
      </section>

      <section class="section-card">
        <div class="section-head">
          <div>
            <p class="eyebrow">Шаг 1</p>
            <h2>Участники из вашей семьи</h2>
            <p class="section-copy">Добавьте ребёнка, чтобы выбирать предметы, подавать заявки и получать результаты в одном месте.</p>
          </div>
          <button class="btn btn-primary" @click="startCreateChild">Добавить ребёнка</button>
        </div>

        <div v-if="children.length" class="children-grid">
          <article
            v-for="child in children"
            :key="child.id"
            class="child-card"
            :class="{ selected: child.id === userStore.selectedChildId }"
          >
            <div class="child-top">
              <div>
                <h3>{{ child.full_name }}</h3>
                <p>{{ child.grade ? `${child.grade} класс` : 'Класс не указан' }}</p>
              </div>
              <StatusBadge :label="child.id === userStore.selectedChildId ? 'Выбран' : 'Готов к участию'" :tone="child.id === userStore.selectedChildId ? 'success' : 'neutral'" />
            </div>
            <p class="child-meta">{{ child.school || 'Школа не указана' }} · {{ child.city || 'Город не указан' }}</p>
            <div class="child-actions">
              <button class="btn btn-ghost" @click="selectChild(child.id)">Выбрать</button>
              <button class="btn btn-outline" @click="startEditChild(child)">Изменить</button>
            </div>
          </article>
        </div>
        <p v-else class="empty-text">Пока не добавлен ни один участник. Начните с профиля ребёнка, чтобы перейти к заявке на олимпиаду.</p>

        <form class="child-form" @submit.prevent="saveChild">
          <div class="form-head">
            <div>
              <p class="eyebrow">{{ editingChildId ? 'Редактирование' : 'Новый участник' }}</p>
              <h3>{{ editingChildId ? 'Обновите данные ребёнка' : 'Добавьте нового ребёнка' }}</h3>
            </div>
          </div>

          <div class="form-grid">
            <label class="field"><span>Имя</span><input v-model="childForm.first_name" placeholder="Имя" required /></label>
            <label class="field"><span>Фамилия</span><input v-model="childForm.last_name" placeholder="Фамилия" required /></label>
            <label class="field"><span>Дата рождения</span><input v-model="childForm.birth_date" type="date" /></label>
            <label class="field"><span>Класс</span><input v-model.number="childForm.grade" type="number" min="1" max="11" placeholder="Например: 7" /></label>
            <label class="field"><span>Школа</span><input v-model="childForm.school" placeholder="Школа" /></label>
            <label class="field"><span>Город</span><input v-model="childForm.city" placeholder="Город" /></label>
            <label class="field field-wide">
              <span>Язык интерфейса</span>
              <select v-model="childForm.language_preference">
                <option value="ru">Русский</option>
                <option value="kk">Қазақша</option>
                <option value="en">English</option>
              </select>
            </label>
          </div>

          <div class="form-actions">
            <button class="btn btn-primary" :disabled="savingChild">{{ savingChild ? 'Сохраняем...' : 'Сохранить профиль' }}</button>
            <button v-if="editingChildId" type="button" class="btn btn-outline" @click="resetChildForm">Отмена</button>
          </div>
        </form>
      </section>

      <section class="section-card">
        <div class="section-head">
          <div>
            <p class="eyebrow">Шаг 2</p>
            <h2>Статус участия в олимпиадах</h2>
            <p class="section-copy">Отслеживайте, где заявка ждёт проверки, где нужна оплата и где уже открыт доступ к тесту.</p>
          </div>
          <RouterLink to="/subject" class="btn btn-primary">Перейти к выбору олимпиады</RouterLink>
        </div>

        <div v-if="olympiads.length" class="list-grid">
          <article v-for="item in olympiads" :key="item.id" class="list-card">
            <div class="card-head">
              <div>
                <h3>{{ item.subject.name }}</h3>
                <p>{{ item.child?.full_name || 'Ребёнок не выбран' }}</p>
              </div>
              <div class="badge-stack">
                <StatusBadge :label="item.status_meta.label" :tone="item.status_meta.tone" />
                <StatusBadge :label="item.payment_status_meta.label" :tone="item.payment_status_meta.tone" />
              </div>
            </div>

            <CountdownBadge :target="item.countdown?.target" :label="item.countdown?.label || 'Старт олимпиады'" />
            <p class="card-copy">{{ item.next_action }}</p>

            <div class="actions-row">
              <button
                v-if="item.status === 'approved' && item.payment_status === 'paid' && !item.completed"
                class="btn btn-primary"
                @click="startQuiz(item.subject.id, item.child?.id)"
              >
                Начать олимпиаду
              </button>
              <button class="btn btn-outline" @click="startTraining(item.subject.id, item.child?.id)">Тренировка</button>
            </div>
          </article>
        </div>
        <p v-else class="empty-text">У вас пока нет активных заявок. После выбора предмета они появятся здесь с понятным статусом и следующими шагами.</p>
      </section>

      <section class="section-card two-col">
        <div class="info-column">
          <div class="section-head compact">
            <div>
              <p class="eyebrow">Шаг 3</p>
              <h2>История оплат</h2>
            </div>
          </div>

          <div v-if="payments.length" class="compact-list">
            <article v-for="item in payments" :key="item.id" class="compact-item">
              <div class="compact-head">
                <strong>{{ item.child_name || 'Без участника' }}</strong>
                <StatusBadge :label="item.status_meta?.label || item.status" :tone="item.status_meta?.tone || 'neutral'" />
              </div>
              <span>{{ item.subject || 'Предмет не указан' }}</span>
              <small>{{ item.date }}</small>
            </article>
          </div>
          <p v-else class="empty-text">Когда заявки начнут переходить к оплате, история появится здесь.</p>
        </div>

        <div class="info-column">
          <div class="section-head compact">
            <div>
              <p class="eyebrow">Шаг 4</p>
              <h2>Последние тренировки</h2>
            </div>
          </div>

          <div v-if="trainings.length" class="compact-list">
            <article v-for="item in trainings" :key="item.id" class="compact-item">
              <strong>{{ item.child_name || 'Без участника' }}</strong>
              <span>{{ item.subject }} · {{ item.percent }}%</span>
              <small>{{ item.date }}</small>
            </article>
          </div>
          <p v-else class="empty-text">Тренировки появятся после первых пробных попыток.</p>
        </div>
      </section>

      <section class="section-card">
        <div class="section-head">
          <div>
            <p class="eyebrow">Шаг 5</p>
            <h2>Последние результаты и сертификаты</h2>
            <p class="section-copy">Когда олимпиада завершена, здесь сразу появляется итог, ссылка на страницу результата и превью сертификата.</p>
          </div>
          <RouterLink to="/results" class="btn btn-primary">Открыть все результаты</RouterLink>
        </div>

        <div v-if="latestResults.length" class="list-grid">
          <article v-for="item in latestResults" :key="item.id" class="list-card">
            <div class="card-head">
              <div>
                <h3>{{ item.subject }}</h3>
                <p>{{ item.child_name }} · {{ item.category_label }}</p>
              </div>
              <StatusBadge :label="item.status_meta?.label || item.status" :tone="item.status_meta?.tone || 'neutral'" />
            </div>

            <p class="card-copy">Результат: {{ item.score }}/{{ item.total }} · {{ item.percent }}%. {{ item.date }}</p>

            <div class="actions-row">
              <RouterLink class="btn btn-outline" to="/results">Смотреть результат</RouterLink>
              <RouterLink class="btn btn-primary" :to="item.certificate_preview_url">Превью сертификата</RouterLink>
            </div>
          </article>
        </div>
        <p v-else class="empty-text">После первой завершённой олимпиады здесь появятся результаты участника и доступ к сертификату.</p>
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'
import StatePanel from '../components/StatePanel.vue'
import StatusBadge from '../components/StatusBadge.vue'
import CountdownBadge from '../components/CountdownBadge.vue'

const router = useRouter()
const userStore = useUserStore()

const loading = ref(true)
const user = ref(null)
const stats = ref({})
const children = ref([])
const olympiads = ref([])
const payments = ref([])
const trainings = ref([])
const latestResults = ref([])
const onboarding = ref(null)
const currentTask = ref(null)
const editingChildId = ref(null)
const savingChild = ref(false)

const canReturnToAdminPanel = computed(() => Boolean(user.value?.is_admin && userStore.sessionType === 'admin'))

const childForm = reactive({
  first_name: '',
  last_name: '',
  birth_date: '',
  grade: '',
  school: '',
  city: '',
  language_preference: 'ru',
})

const resetChildForm = () => {
  editingChildId.value = null
  childForm.first_name = ''
  childForm.last_name = ''
  childForm.birth_date = ''
  childForm.grade = ''
  childForm.school = user.value?.school || ''
  childForm.city = user.value?.city || ''
  childForm.language_preference = 'ru'
}

const hydrate = async () => {
  loading.value = true
  await userStore.fetchUser()
  user.value = userStore.user
  children.value = userStore.children
  stats.value = userStore.stats
  onboarding.value = userStore.onboarding
  currentTask.value = userStore.currentTask

  if (user.value) {
    const [olympiadsRes, paymentsRes, trainingsRes, resultsRes] = await Promise.all([
      api.get('/profile/olympiads'),
      api.get('/profile/payments'),
      api.get('/profile/trainings'),
      api.get('/profile/results'),
    ])

    olympiads.value = olympiadsRes.data
    payments.value = paymentsRes.data
    trainings.value = trainingsRes.data
    latestResults.value = resultsRes.data.slice(0, 3)
  }

  resetChildForm()
  loading.value = false
}

const refreshNotifications = async () => {
  await userStore.fetchNotifications(10)
}

const completeOnboarding = async (step) => {
  await userStore.syncOnboarding({ step })
  onboarding.value = userStore.onboarding
}

const selectChild = (childId) => {
  userStore.setSelectedChild(childId)
}

const startCreateChild = () => {
  resetChildForm()
}

const startEditChild = (child) => {
  editingChildId.value = child.id
  childForm.first_name = child.first_name
  childForm.last_name = child.last_name
  childForm.birth_date = child.birth_date || ''
  childForm.grade = child.grade || ''
  childForm.school = child.school || ''
  childForm.city = child.city || ''
  childForm.language_preference = child.language_preference || 'ru'
}

const saveChild = async () => {
  savingChild.value = true
  try {
    if (editingChildId.value) {
      await api.put(`/profile/children/${editingChildId.value}`, childForm)
    } else {
      await api.post('/profile/children', childForm)
    }

    await userStore.fetchUser()
    children.value = userStore.children
    stats.value = userStore.stats
    onboarding.value = userStore.onboarding
    currentTask.value = userStore.currentTask
    resetChildForm()
  } finally {
    savingChild.value = false
  }
}

const startQuiz = (subjectId, childId) => {
  if (childId) userStore.setSelectedChild(childId)
  router.push({ path: `/quiz/${subjectId}`, query: childId ? { childId } : {} })
}

const startTraining = (subjectId, childId) => {
  if (childId) userStore.setSelectedChild(childId)
  router.push({ path: `/training/${subjectId}`, query: childId ? { childId } : {} })
}

onMounted(hydrate)
</script>

<style scoped>
* { box-sizing: border-box; }

.profile-page {
  min-height: 100vh;
  padding: 110px 20px 56px;
  background: radial-gradient(circle at top left, rgba(201, 171, 99, 0.14), transparent 24%), var(--bg);
  color: var(--text);
  display: grid;
  gap: 18px;
}

.hero-card,
.section-card,
.panel-card,
.stat-card {
  max-width: 1140px;
  margin: 0 auto;
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
}

.hero-card {
  padding: 28px;
  display: flex;
  justify-content: space-between;
  gap: 22px;
  align-items: flex-start;
}

.eyebrow {
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

.hero-copy,
.section-copy,
.empty-text,
.card-copy,
.child-meta,
.compact-item span,
.compact-item small {
  color: var(--text-secondary);
}

.hero-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 18px;
  color: var(--text-secondary);
  font-size: 14px;
}

.hero-actions,
.actions-row,
.form-actions,
.child-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.stats-grid,
.dashboard-grid {
  max-width: 1140px;
  margin: 0 auto;
  display: grid;
  gap: 14px;
}

.stats-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr));
}

.dashboard-grid {
  grid-template-columns: 1.2fr 1fr;
}

.dashboard-span {
  grid-column: span 2;
}

.stat-card,
.panel-card {
  padding: 22px;
}

.stat-card {
  display: grid;
  gap: 8px;
}

.stat-card span,
.progress-value {
  color: var(--text-secondary);
  font-size: 13px;
}

.stat-card strong {
  font-size: 34px;
  line-height: 1;
}

.panel-head,
.section-head,
.form-head,
.child-top,
.card-head,
.compact-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.panel-head h2,
.section-head h2,
.form-head h3 {
  margin: 0;
  color: var(--text);
}

.progress-track {
  margin: 14px 0 0;
  height: 10px;
  border-radius: 999px;
  background: rgba(100, 83, 41, 0.1);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--accent), #dfc27f);
}

.onboarding-steps,
.notification-list,
.children-grid,
.list-grid,
.compact-list {
  display: grid;
  gap: 12px;
}

.onboarding-step,
.notification-card,
.child-card,
.list-card,
.compact-item,
.child-form,
.field input,
.field select {
  border-radius: var(--radius-md);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 244, 0.82);
}

.onboarding-step {
  padding: 14px;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 14px;
  align-items: start;
}

.onboarding-step.done {
  border-color: rgba(79, 167, 116, 0.28);
}

.onboarding-step__index {
  width: 34px;
  height: 34px;
  border-radius: 12px;
  background: rgba(201, 171, 99, 0.16);
  color: var(--accent-strong);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
}

.onboarding-step p,
.notification-card p { margin: 6px 0 0; color: var(--text-secondary); line-height: 1.55; }

.step-complete,
.link-btn {
  border: 0;
  background: transparent;
  color: var(--accent-strong);
  font-weight: 700;
  cursor: pointer;
}

.notification-card {
  padding: 14px;
}

.notification-card.unread {
  border-color: rgba(201, 171, 99, 0.34);
  box-shadow: 0 0 0 3px rgba(201, 171, 99, 0.09);
}

.notification-meta {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
  color: var(--text-secondary);
  font-size: 13px;
}

.section-card {
  padding: 24px;
}

.children-grid,
.list-grid {
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}

.child-card,
.list-card {
  padding: 18px;
  display: grid;
  gap: 14px;
}

.child-card.selected {
  border-color: rgba(201, 171, 99, 0.34);
  box-shadow: 0 0 0 4px rgba(201, 171, 99, 0.12);
}

.badge-stack {
  display: grid;
  gap: 8px;
}

.child-form {
  margin-top: 22px;
  padding: 20px;
  display: grid;
  gap: 18px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
}

.field {
  display: grid;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
}

.field.field-wide {
  grid-column: 1 / -1;
}

.field input,
.field select {
  min-height: 52px;
  padding: 13px 14px;
  color: var(--text);
}

.two-col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.compact-item {
  padding: 16px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 48px;
  padding: 12px 18px;
  border-radius: var(--radius-sm);
  font-weight: 700;
  text-decoration: none;
  border: 0;
  cursor: pointer;
}

.btn.compact { min-height: 40px; padding: 10px 14px; }

.btn-primary {
  background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%);
  color: var(--text);
  box-shadow: 0 14px 30px rgba(201, 171, 99, 0.22);
}

.btn-outline {
  background: rgba(255, 251, 243, 0.86);
  color: var(--accent-strong);
  border: 1px solid rgba(201, 171, 99, 0.28);
}

.btn-ghost {
  background: rgba(79, 167, 116, 0.1);
  color: #316a49;
}

@media (max-width: 980px) {
  .stats-grid,
  .dashboard-grid,
  .two-col,
  .form-grid {
    grid-template-columns: 1fr 1fr;
  }

  .dashboard-span {
    grid-column: auto;
  }
}

@media (max-width: 700px) {
  .profile-page {
    padding-inline: 14px;
  }

  .hero-card,
  .panel-head,
  .section-head,
  .child-top,
  .card-head,
  .notification-meta,
  .onboarding-step {
    grid-template-columns: 1fr;
    flex-direction: column;
    align-items: stretch;
  }

  .stats-grid,
  .dashboard-grid,
  .two-col,
  .form-grid {
    grid-template-columns: 1fr;
  }

  .field.field-wide {
    grid-column: auto;
  }
}
</style>
