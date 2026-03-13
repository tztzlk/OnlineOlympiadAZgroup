<template>
  <br>
  <br>
  <div class="profile-page">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>

    <div v-if="loading" class="loader-wrap">
      <div class="loader-ring"></div>
      <p>Загружаем ваш профиль…</p>
    </div>

    <div v-else-if="user" class="profile-wrap">

      <!-- Hero Card -->
      <div class="hero-card">
        <div class="hero-bg-pattern"></div>
        <div class="hero-content">
          <div class="avatar-wrap">
            <img class="avatar" :src="avatarUrl" alt="avatar"/>
            <div class="avatar-ring"></div>
            <div class="online-dot"></div>
          </div>
          <div class="user-meta">
            <div class="badge-row">
              <span class="badge-role">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Участник олимпиады
              </span>
            </div>
            <h1 class="user-name">{{ user.name }}</h1>
            <div class="user-contacts">
              <div class="contact-chip">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                {{ user.email }}
              </div>
              <div class="contact-chip">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.99 15a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.92 4h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 11.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                {{ user.phone }}
              </div>
            </div>
          </div>
          <div class="hero-actions">
            <RouterLink to="/edit-profile" class="btn btn-primary">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Редактировать
            </RouterLink>
            <RouterLink to="/results" class="btn btn-outline">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              Результаты
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Subjects Section -->
      <div class="section">
        <div class="section-header">
          <div class="section-title-wrap">
            <div class="section-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
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
                <img :src="item.subject.image || '/default-subject.png'" :alt="item.subject.name"/>
              </div>
            </div>

            <div class="card-body">
              <div class="card-top">
                <strong class="subject-name">{{ item.subject.name }}</strong>
                <span class="status-pill" :class="'pill-' + item.status">
                  <span class="pill-dot"></span>
                  <span v-if="item.status === 'pending'">Ожидает</span>
                  <span v-else-if="item.status === 'approved'">Одобрено</span>
                  <span v-else-if="item.status === 'rejected'">Отклонено</span>
                </span>
              </div>

              <p class="subject-date">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ formatDate(item.subject.start_date) }}
              </p>

              <div v-if="item.subject.start_date" class="countdown-wrap">
                <div class="countdown-label">До начала</div>
                <div class="countdown-blocks">
                  <div class="cd-block">
                    <span class="cd-num">{{ countdowns[item.subject.id]?.days ?? 0 }}</span>
                    <span class="cd-unit">дней</span>
                  </div>
                  <div class="cd-sep">:</div>
                  <div class="cd-block">
                    <span class="cd-num">{{ String(countdowns[item.subject.id]?.hours ?? 0).padStart(2,'0') }}</span>
                    <span class="cd-unit">часов</span>
                  </div>
                  <div class="cd-sep">:</div>
                  <div class="cd-block">
                    <span class="cd-num">{{ String(countdowns[item.subject.id]?.minutes ?? 0).padStart(2,'0') }}</span>
                    <span class="cd-unit">минут</span>
                  </div>
                  <div class="cd-sep">:</div>
                  <div class="cd-block">
                    <span class="cd-num">{{ String(countdowns[item.subject.id]?.seconds ?? 0).padStart(2,'0') }}</span>
                    <span class="cd-unit">секунд</span>
                  </div>
                </div>
              </div>

              <button
  v-if="item.status === 'approved' && !item.completed"
  @click="startQuiz(item.subject.id)"
  class="btn-start"
>
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Начать олимпиаду
              </button>
              <div v-if="item.completed" class="completed-result">
  ✅ Тест уже пройден
</div>
            </div>
          </div>
        </div>

        <div v-else class="empty-state">
          <div class="empty-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
          </div>
          <p>Вы пока не зарегистрированы ни на одну олимпиаду</p>
          
        </div>
      </div>

    </div>

    <div v-else class="error-state">
      <div class="error-icon">⚠</div>
      <h3>Не удалось загрузить профиль</h3>
      <p v-if="!hasToken">Пожалуйста, войдите в аккаунт.</p>
      <a v-else @click.prevent="reloadProfile" href="#" class="btn btn-primary">Попробовать снова</a>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
import api from '../js/api'

const router = useRouter()
const userStore = useUserStore()

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading = ref(true)
const registeredSubjects = ref([])
const countdowns = ref({})
const hasToken = ref(true)

const user = computed(() => userStore.user)

let intervalId = null

/*
|--------------------------------------------------------------------------
| Utils
|--------------------------------------------------------------------------
*/

const avatarUrl = computed(() => {
  if (!user.value) return ''

  return user.value.avatar
    ? user.value.avatar
    : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.value.name)}&background=E11D48&color=fff&size=128`
})

const formatDate = (date) => {
  if (!date) return ''

  const d = new Date(date)
  if (isNaN(d)) return ''

  return d.toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

/*
|--------------------------------------------------------------------------
| Data Loaders
|--------------------------------------------------------------------------
*/

const loadProfileData = async () => {
  try {
    await userStore.fetchUser()

    if (!userStore.user) {
      hasToken.value = false
      return
    }

    const [olympiadsRes] = await Promise.all([
      api.get('/profile/olympiads')
    ])

    registeredSubjects.value = olympiadsRes.data || []

    updateCountdowns()

  } catch (e) {
    console.error('Profile load error:', e)
    hasToken.value = false
  }
}

const updateCountdowns = () => {
  const now = Date.now()
  const map = {}

  registeredSubjects.value.forEach(item => {

    if (!item.subject?.start_date) return

    const start = new Date(item.subject.start_date).getTime()
    let diff = start - now

    if (diff < 0) diff = 0

    map[item.subject.id] = {
      days: Math.floor(diff / 86400000),
      hours: Math.floor((diff % 86400000) / 3600000),
      minutes: Math.floor((diff % 3600000) / 60000),
      seconds: Math.floor((diff % 60000) / 1000)
    }
  })

  countdowns.value = map
}

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

const reloadProfile = async () => {
  loading.value = true
  await loadProfileData()
  loading.value = false
}

const startQuiz = (subjectId) => {
  router.push(`/quiz/${subjectId}`)
}

/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(async () => {

  const token = localStorage.getItem('token')

  if (!token) {
    hasToken.value = false
    loading.value = false
    return
  }

  await loadProfileData()

  intervalId = setInterval(updateCountdowns, 1000)

  loading.value = false
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
})
</script>

<style scoped>
* { box-sizing: border-box; }

.profile-page {
  min-height: 100vh;
  background: #0F0F0F;
  padding: 48px 20px;
  position: relative;
  overflow: hidden;
}

/* Decorative background orbs */
.bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.2;
}
.orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(225,29,72,0.15), transparent); top: -150px; right: -100px; }
.orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(225,29,72,0.1), transparent); bottom: -100px; left: -80px; }
.orb-3 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(225,29,72,0.08), transparent); top: 40%; left: 40%; }

.profile-wrap {
  position: relative;
  z-index: 1;
  max-width: 900px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* ── Hero Card ── */
.hero-card {
  background: #1A1A1A;
  border-radius: 28px;
  box-shadow: 0 4px 40px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  position: relative;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.hero-bg-pattern {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 6px;
  background: linear-gradient(90deg, #E11D48, #BE123C, #E11D48);
  background-size: 200% 100%;
  animation: shimmer 4s linear infinite;
}

@keyframes shimmer {
  0% { background-position: 0% 0; }
  100% { background-position: 200% 0; }
}

.hero-content {
  display: flex;
  align-items: center;
  gap: 28px;
  padding: 40px 40px 36px;
  flex-wrap: wrap;
}

/* Avatar */
.avatar-wrap {
  position: relative;
  flex-shrink: 0;
}
.avatar {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  object-fit: cover;
  display: block;
  position: relative;
  z-index: 1;
  border: 3px solid #1A1A1A;
  box-shadow: 0 8px 24px rgba(225, 29, 72, 0.25);
}
.avatar-ring {
  position: absolute;
  inset: -5px;
  border-radius: 50%;
  background: conic-gradient(#E11D48, #BE123C, #f43f5e, #E11D48);
  z-index: 0;
  animation: spin 6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.online-dot {
  position: absolute;
  bottom: 6px; right: 6px;
  width: 16px; height: 16px;
  background: #22c55e;
  border-radius: 50%;
  border: 3px solid white;
  z-index: 2;
}

/* User meta */
.user-meta { flex: 1; min-width: 200px; }

.badge-row { margin-bottom: 8px; }
.badge-role {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #E11D48;
  background: rgba(225, 29, 72, 0.15);
  padding: 5px 12px;
  border-radius: 20px;
  border: 1px solid rgba(225, 29, 72, 0.3);
}

.user-name {
  font-size: 28px;
  font-weight: 700;
  color: #FFFFFF;
  margin: 4px 0 12px;
  line-height: 1.2;
}

.user-contacts { display: flex; flex-wrap: wrap; gap: 8px; }
.contact-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #A1A1AA;
  background: rgba(255, 255, 255, 0.04);
  padding: 6px 12px;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

/* Actions */
.hero-actions { display: flex; flex-direction: column; gap: 10px; }

.btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 11px 22px;
  border-radius: 14px;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-primary {
  background: #E11D48;
  color: white;
  box-shadow: 0 4px 14px rgba(225, 29, 72, 0.35);
}
.btn-primary:hover {
  background: #BE123C;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(225, 29, 72, 0.45);
}

.btn-outline {
  background: transparent;
  color: #E11D48;
  border: 2px solid rgba(225, 29, 72, 0.5);
}
.btn-outline:hover {
  background: rgba(225, 29, 72, 0.12);
  border-color: #E11D48;
}

/* ── Section ── */
.section {
  background: #1A1A1A;
  border-radius: 28px;
  padding: 36px 40px;
  box-shadow: 0 4px 40px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 28px;
}
.section-title-wrap { display: flex; align-items: center; gap: 12px; }
.section-icon {
  width: 40px; height: 40px;
  background: rgba(225, 29, 72, 0.15);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: #E11D48;
}
.section-title-wrap h2 {
  font-size: 22px;
  font-weight: 700;
  color: #FFFFFF;
  margin: 0;
}
.count-badge {
  background: rgba(225, 29, 72, 0.15);
  color: #E11D48;
  font-size: 13px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 20px;
  border: 1px solid rgba(225, 29, 72, 0.3);
}

/* Subjects Grid */
.subjects-grid { display: flex; flex-direction: column; gap: 16px; }

.subject-card {
  display: flex;
  gap: 0;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  overflow: hidden;
  transition: box-shadow 0.2s, transform 0.2s;
  background: rgba(255, 255, 255, 0.02);
}
.subject-card:hover {
  box-shadow: 0 8px 30px rgba(225, 29, 72, 0.1);
  transform: translateY(-2px);
}
.subject-card.status-approved { border-color: rgba(34, 197, 94, 0.4); background: rgba(34, 197, 94, 0.08); }
.subject-card.status-rejected { border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.08); }
.subject-card.status-pending  { border-color: rgba(245, 158, 11, 0.4); background: rgba(245, 158, 11, 0.08); }

.card-left {
  background: rgba(255, 255, 255, 0.03);
  padding: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  flex-shrink: 0;
}
.subject-img-wrap {
  width: 72px; height: 72px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.04);
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
}
.subject-img-wrap img {
  width: 72px; height: 72px;
  object-fit: contain;
}

.card-body { padding: 22px 26px; flex: 1; }

.card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 10px; flex-wrap: wrap; }
.subject-name { font-size: 17px; font-weight: 700; color: #FFFFFF; }

/* Status pill */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 20px;
  white-space: nowrap;
}
.pill-dot { width: 6px; height: 6px; border-radius: 50%; }
.pill-approved { background: #dcfce7; color: #16a34a; }
.pill-approved .pill-dot { background: #22c55e; }
.pill-rejected { background: #fee2e2; color: #dc2626; }
.pill-rejected .pill-dot { background: #ef4444; }
.pill-pending  { background: #fef3c7; color: #d97706; }
.pill-pending .pill-dot { background: #f59e0b; box-shadow: 0 0 0 3px #fde68a; animation: pulse 1.5s ease-in-out infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

.subject-date {
  display: flex; align-items: center; gap: 6px;
  font-size: 13px; color: #A1A1AA; margin: 0 0 16px;
}

/* Countdown */
.countdown-wrap { margin-bottom: 18px; }
.countdown-label { font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #A1A1AA; margin-bottom: 8px; }
.countdown-blocks { display: flex; align-items: center; gap: 6px; }
.cd-block {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  padding: 8px 12px;
  text-align: center;
  min-width: 52px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.cd-num {
  display: block;
  font-size: 20px;
  font-weight: 700;
  color: #E11D48;
  line-height: 1;
}
.cd-unit {
  display: block;
  font-size: 10px;
  font-weight: 600;
  color: #A1A1AA;
  margin-top: 2px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.cd-sep { font-size: 18px; font-weight: 700; color: #52525b; margin-bottom: 12px; }

/* Start button */
.btn-start {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 11px 22px;
  background: #E11D48;
  color: white;
  border: none;
  border-radius: 14px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  box-shadow: 0 4px 14px rgba(225, 29, 72, 0.35);
  transition: all 0.2s;
}
.btn-start:hover {
  background: #BE123C;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(225, 29, 72, 0.45);
}

/* Empty state */
.empty-state {
  text-align: center;
  padding: 48px 20px;
  color: #A1A1AA;
}
.empty-icon {
  width: 80px; height: 80px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 24px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 20px;
  color: #52525b;
}
.empty-state p { font-size: 15px; margin: 0; }

/* Loader */
.loader-wrap {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  min-height: 100vh; gap: 16px;
  position: relative; z-index: 1;
}
.loader-ring {
  width: 50px; height: 50px;
  border: 3px solid rgba(255, 255, 255, 0.08);
  border-top-color: #E11D48;
  border-radius: 50%;
  animation: spin 0.9s linear infinite;
}
.loader-wrap p { color: #A1A1AA; font-size: 14px; font-weight: 600; }

/* Error */
.error-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  min-height: 100vh; text-align: center;
  position: relative; z-index: 1;
}
.error-icon { font-size: 40px; margin-bottom: 16px; }
.error-state h3 { font-size: 22px; color: #FFFFFF; margin: 0 0 8px; }
.error-state p { color: #A1A1AA; margin: 0 0 20px; }

/* Responsive */
@media (max-width: 640px) {
  .hero-content { padding: 30px 24px; flex-direction: column; align-items: flex-start; }
  .hero-actions { flex-direction: row; }
  .section { padding: 28px 24px; }
  .card-body { padding: 18px 20px; }
  .card-left { padding: 20px 16px; }
  .user-name { font-size: 22px; }
  .countdown-blocks { flex-wrap: wrap; gap: 6px; }
}
</style>