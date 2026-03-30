<template>
  <div class="profile-page">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>

    <div v-if="loading" class="loader-wrap">
      <div class="loader-ring"></div>
      <p>Загружаем ваш профиль...</p>
    </div>

    <div v-else-if="user" class="profile-wrap">
      <div class="hero-card">
        <div class="hero-bg-pattern"></div>
        <div class="hero-content">
          <div class="avatar-wrap">
            <img class="avatar" :src="avatarUrl" alt="avatar" />
            <div class="avatar-ring"></div>
            <div class="online-dot"></div>
          </div>

          <div class="user-meta">
            <div class="badge-row">
              <span class="badge-role">Участник олимпиады</span>
            </div>
            <h1 class="user-name">{{ user.name }}</h1>
            <div class="user-contacts">
              <div class="contact-chip">{{ user.email }}</div>
              <div class="contact-chip">{{ user.school || 'Школа не указана' }}</div>
              <div class="contact-chip">{{ user.city || 'Город не указан' }}</div>
              <div class="contact-chip">{{ user.phone }}</div>
            </div>
          </div>

          <div class="hero-actions">
            <RouterLink to="/edit-profile" class="btn btn-primary">Редактировать</RouterLink>
            <RouterLink to="/results" class="btn btn-outline">Результаты</RouterLink>
          </div>
        </div>
      </div>

      <div class="section">
        <div class="section-header">
          <div class="section-title-wrap">
            <div class="section-icon">📚</div>
            <h2>Мои олимпиады</h2>
          </div>
          <span v-if="registeredSubjects.length" class="count-badge">{{ registeredSubjects.length }}</span>
        </div>

        <div v-if="registeredSubjects.length" class="subjects-grid">
          <div
            v-for="item in registeredSubjects"
            :key="item.id"
            class="subject-card"
            :class="'status-' + item.status"
          >
            <div class="card-left">
              <div class="subject-img-wrap">
                <img :src="item.subject.image || '/default-subject.png'" :alt="item.subject.name" />
              </div>
            </div>

            <div class="card-body">
              <div class="card-top">
                <strong class="subject-name">{{ item.subject.name }}</strong>
                <span class="status-pill" :class="'pill-' + item.status">
                  <span v-if="item.status === 'pending'">Ожидает</span>
                  <span v-else-if="item.status === 'approved'">Одобрено</span>
                  <span v-else-if="item.status === 'rejected'">Отклонено</span>
                </span>
              </div>

              <p class="subject-date">{{ formatDate(item.subject.start_date) }}</p>

              <div v-if="item.subject.start_date" class="countdown-wrap">
                <div class="countdown-label">До начала</div>
                <div class="countdown-blocks">
                  <div class="cd-block">
                    <span class="cd-num">{{ countdowns[item.subject.id]?.days ?? 0 }}</span>
                    <span class="cd-unit">дней</span>
                  </div>
                  <div class="cd-block">
                    <span class="cd-num">{{ String(countdowns[item.subject.id]?.hours ?? 0).padStart(2, '0') }}</span>
                    <span class="cd-unit">часов</span>
                  </div>
                  <div class="cd-block">
                    <span class="cd-num">{{ String(countdowns[item.subject.id]?.minutes ?? 0).padStart(2, '0') }}</span>
                    <span class="cd-unit">минут</span>
                  </div>
                  <div class="cd-block">
                    <span class="cd-num">{{ String(countdowns[item.subject.id]?.seconds ?? 0).padStart(2, '0') }}</span>
                    <span class="cd-unit">секунд</span>
                  </div>
                </div>
              </div>

              <button v-if="item.status === 'approved' && !item.completed && !item.disqualified" class="btn-start" @click="startQuiz(item.subject.id)">
                Начать олимпиаду
              </button>

              <div v-if="item.disqualified" class="completed-result">
                Попытка аннулирована из-за нарушения правил тестирования
              </div>

              <div v-if="item.completed" class="completed-result success">
                Тест уже пройден
              </div>
            </div>
          </div>
        </div>

        <div v-else class="empty-state">
          Вы пока не зарегистрированы ни на одну олимпиаду.
        </div>
      </div>
    </div>

    <div v-else class="error-state">
      <h3>Не удалось загрузить профиль</h3>
      <p v-if="!hasToken">Пожалуйста, войдите в аккаунт.</p>
      <a v-else href="#" class="btn btn-primary" @click.prevent="reloadProfile">Попробовать снова</a>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'

const router = useRouter()
const userStore = useUserStore()

const loading = ref(true)
const registeredSubjects = ref([])
const countdowns = ref({})
const hasToken = ref(true)
const user = computed(() => userStore.user)

let intervalId = null

const avatarUrl = computed(() => {
  if (!user.value) return ''
  return user.value.avatar
    ? user.value.avatar
    : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.value.name)}&background=E11D48&color=fff&size=128`
})

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  if (Number.isNaN(d.getTime())) return ''
  return d.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

const loadProfileData = async () => {
  try {
    await userStore.fetchUser()

    if (!userStore.user) {
      hasToken.value = false
      return
    }

    const { data } = await api.get('/profile/olympiads')
    registeredSubjects.value = data || []
    updateCountdowns()
  } catch (e) {
    console.error('Profile load error:', e)
    hasToken.value = false
  }
}

const updateCountdowns = () => {
  const now = Date.now()
  const map = {}

  registeredSubjects.value.forEach((item) => {
    if (!item.subject?.start_date) return
    const start = new Date(item.subject.start_date).getTime()
    let diff = start - now
    if (diff < 0) diff = 0

    map[item.subject.id] = {
      days: Math.floor(diff / 86400000),
      hours: Math.floor((diff % 86400000) / 3600000),
      minutes: Math.floor((diff % 3600000) / 60000),
      seconds: Math.floor((diff % 60000) / 1000),
    }
  })

  countdowns.value = map
}

const reloadProfile = async () => {
  loading.value = true
  await loadProfileData()
  loading.value = false
}

const startQuiz = (subjectId) => {
  router.push(`/quiz/${subjectId}`)
}

onMounted(async () => {
  const token = localStorage.getItem('token')
  if (!token) {
    hasToken.value = false
    loading.value = false
    return
  }

  await loadProfileData()
  intervalId = window.setInterval(updateCountdowns, 1000)
  loading.value = false
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
})
</script>

<style scoped>
* { box-sizing: border-box; }
.profile-page { min-height: 100vh; background: var(--bg); padding: 48px 20px; position: relative; overflow: hidden; }
.bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.2; }
.orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(225,29,72,0.15), transparent); top: -150px; right: -100px; }
.orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(225,29,72,0.1), transparent); bottom: -100px; left: -80px; }
.orb-3 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(225,29,72,0.08), transparent); top: 40%; left: 40%; }
.profile-wrap { position: relative; z-index: 1; max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px; }
.hero-card, .section { background: var(--surface); border-radius: 28px; box-shadow: 0 4px 40px rgba(0, 0, 0, 0.2); border: 1px solid var(--surface-border); overflow: hidden; position: relative; }
.hero-bg-pattern { position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #e11d48, #be123c, #e11d48); }
.hero-content { display: flex; align-items: center; gap: 28px; padding: 40px 40px 36px; flex-wrap: wrap; }
.avatar-wrap { position: relative; flex-shrink: 0; }
.avatar { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 3px solid #1a1a1a; position: relative; z-index: 1; }
.avatar-ring { position: absolute; inset: -5px; border-radius: 50%; background: conic-gradient(#e11d48, #be123c, #f43f5e, #e11d48); animation: spin 6s linear infinite; }
.online-dot { position: absolute; bottom: 6px; right: 6px; width: 16px; height: 16px; background: #22c55e; border-radius: 50%; border: 3px solid white; z-index: 2; }
.user-meta { flex: 1; min-width: 220px; }
.badge-row { margin-bottom: 8px; }
.badge-role { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: #e11d48; background: rgba(225,29,72,0.15); padding: 5px 12px; border-radius: 20px; border: 1px solid rgba(225,29,72,0.3); }
.user-name { font-size: 28px; font-weight: 700; color: var(--text-on-surface); margin: 4px 0 12px; line-height: 1.2; }
.user-contacts { display: flex; flex-wrap: wrap; gap: 8px; }
.contact-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--text-muted-on-surface); background: color-mix(in srgb, var(--text) 4%, transparent); padding: 6px 12px; border-radius: 20px; border: 1px solid var(--surface-border); }
.hero-actions { display: flex; flex-direction: column; gap: 10px; }
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 11px 22px; border-radius: 14px; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; border: none; }
.btn-primary { background: #e11d48; color: white; box-shadow: 0 4px 14px rgba(225,29,72,0.35); }
.btn-outline { background: transparent; color: #e11d48; border: 2px solid rgba(225,29,72,0.5); }
.section { padding: 36px 40px; }
.section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
.section-title-wrap { display: flex; align-items: center; gap: 12px; }
.section-icon { width: 40px; height: 40px; background: rgba(225,29,72,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #e11d48; }
.count-badge { background: rgba(225,29,72,0.15); color: #e11d48; font-size: 13px; font-weight: 700; padding: 4px 12px; border-radius: 20px; border: 1px solid rgba(225,29,72,0.3); }
.subjects-grid { display: flex; flex-direction: column; gap: 16px; }
.subject-card { display: flex; border-radius: 20px; border: 1px solid rgba(255,255,255,0.08); overflow: hidden; background: rgba(255,255,255,0.02); }
.subject-card.status-approved { border-color: rgba(34,197,94,0.4); background: rgba(34,197,94,0.08); }
.subject-card.status-rejected { border-color: rgba(239,68,68,0.4); background: rgba(239,68,68,0.08); }
.subject-card.status-pending { border-color: rgba(245,158,11,0.4); background: rgba(245,158,11,0.08); }
.card-left { padding: 24px; display: flex; align-items: center; justify-content: center; border-right: 1px solid rgba(255,255,255,0.08); }
.subject-img-wrap { width: 72px; height: 72px; border-radius: 16px; background: rgba(255,255,255,0.04); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.subject-img-wrap img { width: 72px; height: 72px; object-fit: contain; }
.card-body { padding: 22px 26px; flex: 1; }
.card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 10px; flex-wrap: wrap; }
.subject-name { font-size: 17px; font-weight: 700; color: #fff; }
.status-pill { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
.pill-approved { background: #dcfce7; color: #16a34a; }
.pill-rejected { background: #fee2e2; color: #dc2626; }
.pill-pending { background: #fef3c7; color: #d97706; }
.subject-date { font-size: 13px; color: #a1a1aa; margin: 0 0 16px; }
.countdown-wrap { margin-bottom: 18px; }
.countdown-label { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #a1a1aa; margin-bottom: 8px; }
.countdown-blocks { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.cd-block { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 8px 12px; text-align: center; min-width: 52px; }
.cd-num { display: block; font-size: 20px; font-weight: 700; color: #e11d48; line-height: 1; }
.cd-unit { display: block; font-size: 10px; font-weight: 600; color: #a1a1aa; margin-top: 2px; text-transform: uppercase; }
.btn-start { display: inline-flex; align-items: center; gap: 8px; padding: 11px 22px; background: #e11d48; color: white; border: none; border-radius: 14px; cursor: pointer; font-size: 14px; font-weight: 700; }
.completed-result { color: #fecaca; font-size: 14px; font-weight: 600; }
.completed-result.success { color: #bbf7d0; }
.empty-state, .loader-wrap, .error-state { position: relative; z-index: 1; text-align: center; color: var(--text-secondary); }
.loader-wrap, .error-state { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px; }
.loader-ring { width: 50px; height: 50px; border: 3px solid rgba(255,255,255,0.08); border-top-color: #e11d48; border-radius: 50%; animation: spin 0.9s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
@media (max-width: 640px) { .hero-content { padding: 30px 24px; flex-direction: column; align-items: flex-start; } .hero-actions { flex-direction: row; width: 100%; } .hero-actions .btn { flex: 1; } .section { padding: 28px 24px; } .card-body { padding: 18px 20px; } .card-left { padding: 20px 16px; } .user-name { font-size: 22px; } }
</style>
