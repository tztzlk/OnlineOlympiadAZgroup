<template>
  <div class="profile-page">
    <div v-if="loading" class="state-card">Загружаем личный кабинет...</div>
    <div v-else-if="!user" class="state-card">Пожалуйста, войдите в аккаунт, чтобы увидеть данные профиля.</div>

    <template v-else>
      <section class="hero-card">
        <div class="hero-main">
          <p class="eyebrow">Личный кабинет</p>
          <h1>{{ user.name }}</h1>
          <p class="hero-copy">
            Управляйте участниками, заявками, оплатами, тренировками и результатами из одного кабинета.
          </p>
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
          <small>Олимпиады и заявки в системе</small>
        </article>
        <article class="stat-card">
          <span>Тренировки</span>
          <strong>{{ trainings.length }}</strong>
          <small>Последние тренировочные попытки</small>
        </article>
        <article class="stat-card">
          <span>Оплаты</span>
          <strong>{{ payments.length }}</strong>
          <small>История подтверждённых и ожидающих оплат</small>
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
              <span class="child-state">{{ child.id === userStore.selectedChildId ? 'Выбран' : 'Готов к участию' }}</span>
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
            <label class="field">
              <span>Имя</span>
              <input v-model="childForm.first_name" placeholder="Имя" required />
            </label>
            <label class="field">
              <span>Фамилия</span>
              <input v-model="childForm.last_name" placeholder="Фамилия" required />
            </label>
            <label class="field">
              <span>Дата рождения</span>
              <input v-model="childForm.birth_date" type="date" />
            </label>
            <label class="field">
              <span>Класс</span>
              <input v-model.number="childForm.grade" type="number" min="1" max="11" placeholder="Например: 7" />
            </label>
            <label class="field">
              <span>Школа</span>
              <input v-model="childForm.school" placeholder="Школа" />
            </label>
            <label class="field">
              <span>Город</span>
              <input v-model="childForm.city" placeholder="Город" />
            </label>
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
            <p class="section-copy">Следите, где заявка ожидает проверки, где нужна оплата и где уже открыт доступ к тесту.</p>
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
                <span class="badge">{{ item.status }}</span>
                <span class="badge badge-soft">{{ item.payment_status }}</span>
              </div>
            </div>

            <p class="card-copy">
              <template v-if="item.status === 'approved' && item.payment_status === 'paid' && !item.completed">
                Всё готово: можно перейти к олимпиаде или пройти тренировку перед стартом.
              </template>
              <template v-else-if="item.status === 'approved' && item.payment_status !== 'paid'">
                Заявка одобрена. Следующий шаг — подтвердить оплату и дождаться доступа.
              </template>
              <template v-else-if="item.status === 'pending'">
                Заявка отправлена и ожидает проверки администратором.
              </template>
              <template v-else>
                Проверьте статус заявки и при необходимости свяжитесь с поддержкой.
              </template>
            </p>

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
        <p v-else class="empty-text">У вас пока нет активных заявок. После выбора предмета они появятся здесь с понятным статусом.</p>
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
              <strong>{{ item.child_name || 'Без участника' }}</strong>
              <span>{{ item.subject || 'Предмет не указан' }}</span>
              <small>{{ item.status }} · {{ item.date }}</small>
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
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'

const router = useRouter()
const userStore = useUserStore()

const loading = ref(true)
const user = ref(null)
const stats = ref({})
const children = ref([])
const olympiads = ref([])
const payments = ref([])
const trainings = ref([])
const editingChildId = ref(null)
const savingChild = ref(false)

const canReturnToAdminPanel = computed(() => {
  return Boolean(user.value?.is_admin && userStore.sessionType === 'admin')
})

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

  if (user.value) {
    const [olympiadsRes, paymentsRes, trainingsRes] = await Promise.all([
      api.get('/profile/olympiads'),
      api.get('/profile/payments'),
      api.get('/profile/trainings'),
    ])

    olympiads.value = olympiadsRes.data
    payments.value = paymentsRes.data
    trainings.value = trainingsRes.data
  }

  resetChildForm()
  loading.value = false
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
  background:
    radial-gradient(circle at top left, rgba(201, 171, 99, 0.14), transparent 24%),
    var(--bg);
  color: var(--text);
}

.hero-card,
.section-card,
.state-card,
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

.hero-main {
  display: grid;
  gap: 12px;
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
.state-card,
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

.stats-grid {
  max-width: 1140px;
  margin: 18px auto 0;
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.stat-card {
  padding: 22px;
  display: grid;
  gap: 8px;
}

.stat-card span {
  color: var(--text-secondary);
  font-size: 13px;
}

.stat-card strong {
  font-size: 34px;
  line-height: 1;
}

.stat-card small {
  color: var(--text-secondary);
  font-size: 13px;
}

.section-card {
  margin-top: 18px;
  padding: 24px;
}

.section-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 18px;
}

.section-head.compact {
  margin-bottom: 14px;
}

.children-grid,
.list-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 14px;
}

.child-card,
.list-card,
.compact-item,
.field input,
.field select,
.child-form {
  border-radius: var(--radius-md);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 244, 0.82);
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

.child-top,
.card-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
}

.child-state,
.badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 7px 12px;
  border-radius: 999px;
  background: var(--success-bg);
  color: #2f6f4b;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

.badge-stack {
  display: grid;
  gap: 8px;
}

.badge-soft {
  background: rgba(201, 171, 99, 0.18);
  color: var(--accent-strong);
}

.child-form {
  margin-top: 22px;
  padding: 20px;
  display: grid;
  gap: 18px;
}

.form-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
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

.compact-list {
  display: grid;
  gap: 12px;
}

.compact-item {
  padding: 16px;
  display: grid;
  gap: 4px;
}

.compact-item strong {
  font-size: 17px;
}

.two-col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.info-column {
  display: grid;
  gap: 8px;
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

@media (max-width: 920px) {
  .stats-grid,
  .two-col,
  .form-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 700px) {
  .profile-page {
    padding-inline: 14px;
  }

  .hero-card,
  .section-head,
  .child-top,
  .card-head {
    flex-direction: column;
    align-items: stretch;
  }

  .stats-grid,
  .two-col,
  .form-grid {
    grid-template-columns: 1fr;
  }

  .field.field-wide {
    grid-column: auto;
  }
}
</style>
