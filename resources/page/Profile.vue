<template>
  <div class="profile-page">
    <div v-if="loading" class="state-card">Загружаем профиль...</div>
    <div v-else-if="!user" class="state-card">Пожалуйста, войдите в аккаунт.</div>

    <template v-else>
      <section class="hero-card">
        <div>
          <p class="eyebrow">Parent Account</p>
          <h1>{{ user.name }}</h1>
          <p class="description">{{ user.email }} · {{ user.phone }}</p>
        </div>
        <div class="hero-actions">
          <RouterLink to="/edit-profile" class="btn btn-outline">Редактировать профиль</RouterLink>
          <RouterLink to="/results" class="btn btn-primary">Результаты</RouterLink>
        </div>
      </section>

      <section class="stats-grid">
        <article class="stat-card">
          <span>Детей</span>
          <strong>{{ stats.children || children.length }}</strong>
        </article>
        <article class="stat-card">
          <span>Олимпиад</span>
          <strong>{{ stats.olympiads || olympiads.length }}</strong>
        </article>
        <article class="stat-card">
          <span>Тренировок</span>
          <strong>{{ trainings.length }}</strong>
        </article>
        <article class="stat-card">
          <span>Оплат</span>
          <strong>{{ payments.length }}</strong>
        </article>
      </section>

      <section class="section-card">
        <div class="section-head">
          <div>
            <p class="eyebrow">Children</p>
            <h2>Профили детей</h2>
          </div>
          <button class="btn btn-primary" @click="startCreateChild">Добавить ребенка</button>
        </div>

        <div v-if="children.length" class="children-grid">
          <article
            v-for="child in children"
            :key="child.id"
            class="child-card"
            :class="{ selected: child.id === userStore.selectedChildId }"
          >
            <div>
              <h3>{{ child.full_name }}</h3>
              <p>{{ child.grade ? `${child.grade} класс` : 'Класс не указан' }}</p>
              <small>{{ child.school || 'Школа не указана' }} · {{ child.city || 'Город не указан' }}</small>
            </div>
            <div class="child-actions">
              <button class="btn btn-ghost" @click="selectChild(child.id)">Выбрать</button>
              <button class="btn btn-outline" @click="startEditChild(child)">Изменить</button>
            </div>
          </article>
        </div>
        <p v-else class="empty-text">Добавьте хотя бы один профиль ребенка, чтобы записывать его на олимпиады и тренировки.</p>

        <form class="child-form" @submit.prevent="saveChild">
          <h3>{{ editingChildId ? 'Редактирование ребенка' : 'Новый ребенок' }}</h3>
          <div class="form-grid">
            <input v-model="childForm.first_name" placeholder="Имя" required />
            <input v-model="childForm.last_name" placeholder="Фамилия" required />
            <input v-model="childForm.birth_date" type="date" />
            <input v-model.number="childForm.grade" type="number" min="1" max="11" placeholder="Класс" />
            <input v-model="childForm.school" placeholder="Школа" />
            <input v-model="childForm.city" placeholder="Город" />
            <select v-model="childForm.language_preference">
              <option value="ru">Русский</option>
              <option value="kk">Қазақша</option>
              <option value="en">English</option>
            </select>
          </div>
          <div class="form-actions">
            <button class="btn btn-primary" :disabled="savingChild">{{ savingChild ? 'Сохраняем...' : 'Сохранить' }}</button>
            <button v-if="editingChildId" type="button" class="btn btn-outline" @click="resetChildForm">Отмена</button>
          </div>
        </form>
      </section>

      <section class="section-card">
        <div class="section-head">
          <div>
            <p class="eyebrow">Olympiads</p>
            <h2>История участия</h2>
          </div>
          <RouterLink to="/subject" class="btn btn-primary">Выбрать олимпиаду</RouterLink>
        </div>
        <div v-if="olympiads.length" class="list-grid">
          <article v-for="item in olympiads" :key="item.id" class="list-card">
            <h3>{{ item.subject.name }}</h3>
            <p>{{ item.child?.full_name || 'Без ребенка' }}</p>
            <small>Статус: {{ item.status }} · Оплата: {{ item.payment_status }}</small>
            <div class="actions-row">
              <button v-if="item.status === 'approved' && item.payment_status === 'paid' && !item.completed" class="btn btn-primary" @click="startQuiz(item.subject.id, item.child?.id)">Начать</button>
              <button class="btn btn-outline" @click="startTraining(item.subject.id, item.child?.id)">Тренировка</button>
            </div>
          </article>
        </div>
        <p v-else class="empty-text">Пока нет зарегистрированных олимпиад.</p>
      </section>

      <section class="section-card two-col">
        <div>
          <div class="section-head">
            <div>
              <p class="eyebrow">Payments</p>
              <h2>История оплат</h2>
            </div>
          </div>
          <div v-if="payments.length" class="compact-list">
            <article v-for="item in payments" :key="item.id" class="compact-item">
              <strong>{{ item.child_name || '—' }}</strong>
              <span>{{ item.subject || 'Предмет не указан' }}</span>
              <small>{{ item.status }} · {{ item.date }}</small>
            </article>
          </div>
          <p v-else class="empty-text">История оплат пока пуста.</p>
        </div>

        <div>
          <div class="section-head">
            <div>
              <p class="eyebrow">Training</p>
              <h2>Последние тренировки</h2>
            </div>
          </div>
          <div v-if="trainings.length" class="compact-list">
            <article v-for="item in trainings" :key="item.id" class="compact-item">
              <strong>{{ item.child_name || '—' }}</strong>
              <span>{{ item.subject }} · {{ item.percent }}%</span>
              <small>{{ item.date }}</small>
            </article>
          </div>
          <p v-else class="empty-text">Тренировок пока нет.</p>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
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
.profile-page { min-height: 100vh; padding: 110px 20px 50px; background: var(--bg); color: var(--text-primary); }
.hero-card, .section-card, .state-card, .stat-card { max-width: 1120px; margin: 0 auto; background: var(--surface); border: 1px solid var(--surface-border); border-radius: 24px; padding: 24px; }
.hero-card { display: flex; align-items: center; justify-content: space-between; gap: 18px; }
.hero-actions, .actions-row, .form-actions, .child-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; font-size: 12px; font-weight: 700; color: #e11d48; }
.description { color: var(--text-secondary); }
.stats-grid { max-width: 1120px; margin: 18px auto 0; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.stat-card span { display: block; color: var(--text-secondary); font-size: 13px; }
.stat-card strong { font-size: 30px; }
.section-card { margin-top: 18px; }
.section-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 18px; }
.children-grid, .list-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; }
.child-card, .list-card, .compact-item { border-radius: 20px; border: 1px solid var(--surface-border); padding: 18px; background: color-mix(in srgb, var(--bg) 94%, var(--text) 6%); }
.child-card.selected { outline: 2px solid rgba(225,29,72,0.35); }
.child-card p, .list-card p, .compact-item span, .compact-item small, .list-card small, .empty-text { color: var(--text-secondary); }
.child-form { margin-top: 20px; display: grid; gap: 14px; border-top: 1px solid var(--surface-border); padding-top: 20px; }
.form-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
input, select { width: 100%; border-radius: 14px; border: 1px solid var(--surface-border); padding: 13px 14px; background: color-mix(in srgb, var(--bg) 92%, var(--text) 8%); color: var(--text-on-surface); }
.compact-list { display: grid; gap: 12px; }
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 12px 16px; border-radius: 14px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; }
.btn-primary { background: linear-gradient(90deg, #e11d48, #be123c); color: #fff; }
.btn-outline { background: transparent; color: #e11d48; border: 2px solid rgba(225,29,72,0.35); }
.btn-ghost { background: rgba(225,29,72,0.08); color: #e11d48; }
@media (max-width: 900px) { .stats-grid, .two-col, .form-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 640px) { .profile-page { padding-inline: 16px; } .hero-card, .section-head { flex-direction: column; align-items: stretch; } .stats-grid, .two-col, .form-grid { grid-template-columns: 1fr; } }
</style>
