<template>
  <section class="news-section">
    <div class="news-header">
      <div class="news-badge">Новости</div>
      <h1 class="news-title">Новости платформы и leaderboard доверия</h1>
      <p class="news-subtitle">
        Следите за обновлениями, а рядом сразу видьте реальные результаты участников и быстрые проверки сертификатов.
      </p>
    </div>

    <div class="content-grid">
      <div class="news-column">
        <div v-if="loading" class="news-grid">
          <div v-for="n in 3" :key="n" class="skeleton-card">
            <div class="skeleton-img"></div>
            <div class="skeleton-body">
              <div class="skeleton-line wide"></div>
              <div class="skeleton-line medium"></div>
            </div>
          </div>
        </div>

        <div v-else-if="!newsList.length" class="empty-state">
          Новости скоро появятся. Пока можно посмотреть рейтинг участников и проверить сертификат по ID.
        </div>

        <div v-else class="news-grid">
          <article class="news-card" v-for="news in newsList" :key="news.id">
            <div class="image-wrapper">
              <img :src="news.image" :alt="news.title" loading="lazy" />
            </div>
            <div class="news-content">
              <div class="news-meta" v-if="news.date">{{ formatDate(news.date) }}</div>
              <h2 class="news-card-title">{{ news.title }}</h2>
              <p class="news-desc">{{ news.description }}</p>
              <a v-if="news.link" :href="news.link" target="_blank" class="read-more">Читать далее</a>
            </div>
          </article>
        </div>
      </div>

      <aside class="leaderboard-card">
        <div class="leaderboard-head">
          <p class="eyebrow">Leaderboard</p>
          <h2>Рейтинг как доказательство активности платформы</h2>
          <p class="leaderboard-copy">
            Это не абстрактные цифры, а лучшие завершённые результаты, которые помогают родителям и школам быстрее доверять платформе.
          </p>
        </div>

        <div v-if="leaderboard.length" class="leaderboard-summary">
          <article class="summary-pill">
            <span>Лучший результат</span>
            <strong>{{ leaderboard[0].percent }}%</strong>
          </article>
          <article class="summary-pill">
            <span>Предметов</span>
            <strong>{{ uniqueSubjects }}</strong>
          </article>
        </div>

        <div v-if="leaderboardLoading" class="leaderboard-loading">Загружаем рейтинг...</div>
        <div v-else-if="!leaderboard.length" class="leaderboard-loading">Пока нет завершённых результатов.</div>

        <div v-else class="leaderboard-list">
          <article v-for="item in leaderboard" :key="`${item.rank}-${item.user_name}`" class="leaderboard-item">
            <div class="rank-badge">{{ item.rank }}</div>
            <div class="leaderboard-body">
              <strong>{{ item.user_name }}</strong>
              <p>{{ item.subject }} · {{ item.category }}</p>
              <span>{{ item.school }}, {{ item.city }}</span>
            </div>
            <div class="leaderboard-score">
              <strong>{{ item.percent }}%</strong>
              <span>{{ item.score }}/{{ item.total }}</span>
            </div>
          </article>
        </div>

        <div class="leaderboard-actions">
          <RouterLink class="action-link" to="/leaderboard">Открыть весь рейтинг</RouterLink>
          <RouterLink class="action-link secondary" to="/certificate-check">Проверить сертификат</RouterLink>
        </div>
      </aside>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../js/api'

const newsList = ref([])
const leaderboard = ref([])
const loading = ref(true)
const leaderboardLoading = ref(true)

const uniqueSubjects = computed(() => new Set(leaderboard.value.map((item) => item.subject)).size)

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

const fetchNews = async () => {
  try {
    const res = await api.get('/news')
    newsList.value = res.data
  } finally {
    loading.value = false
  }
}

const fetchLeaderboard = async () => {
  try {
    const res = await api.get('/leaderboard')
    leaderboard.value = res.data
  } finally {
    leaderboardLoading.value = false
  }
}

onMounted(() => {
  fetchNews()
  fetchLeaderboard()
})
</script>

<style scoped>
* { box-sizing: border-box; }
.news-section { max-width: 1200px; margin: 0 auto; padding: 90px 28px; }
.news-header { text-align: center; margin-bottom: 40px; }
.news-badge, .eyebrow { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #6c5a2f; background: rgba(208, 179, 107, 0.18); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(208, 179, 107, 0.28); margin-bottom: 18px; }
.news-title { font-size: 30px; font-weight: 600; color: var(--text-primary); margin: 0 0 12px; }
.news-subtitle { font-size: 16px; color: var(--text-secondary); margin: 0; line-height: 1.6; }
.content-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(340px, 1fr); gap: 24px; align-items: start; }
.news-grid { display: grid; gap: 18px; }
.news-card, .leaderboard-card, .skeleton-card { border-radius: 24px; overflow: hidden; background: rgba(255, 249, 239, 0.72); border: 1px solid var(--surface-border); box-shadow: 0 16px 40px rgba(67, 55, 28, 0.09); backdrop-filter: blur(10px); }
.image-wrapper { height: 180px; overflow: hidden; }
.image-wrapper img { width: 100%; height: 100%; object-fit: cover; }
.news-content { padding: 20px; }
.news-meta { font-size: 12px; color: var(--text-muted-on-surface); margin-bottom: 8px; }
.news-card-title { font-size: 18px; font-weight: 600; color: var(--text-on-surface); margin: 0; }
.news-desc { font-size: 15px; color: var(--text-muted-on-surface); line-height: 1.5; margin: 10px 0 0; }
.read-more { display: inline-flex; margin-top: 14px; color: #22663b; text-decoration: none; font-weight: 600; }
.leaderboard-card { padding: 22px; position: sticky; top: 90px; display: grid; gap: 16px; }
.leaderboard-head h2 { margin: 0; color: var(--text-on-surface); }
.leaderboard-copy { margin: 10px 0 0; color: var(--text-muted-on-surface); line-height: 1.55; }
.leaderboard-summary { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.summary-pill { padding: 14px; border-radius: 18px; background: rgba(255, 252, 244, 0.82); border: 1px solid var(--surface-border); display: grid; gap: 6px; }
.summary-pill span { color: var(--text-muted-on-surface); font-size: 12px; }
.summary-pill strong { color: var(--text-on-surface); font-size: 28px; line-height: 1; }
.leaderboard-loading { color: var(--text-secondary); padding: 12px 0; }
.leaderboard-list { display: grid; gap: 12px; }
.leaderboard-item { display: grid; grid-template-columns: 42px 1fr auto; gap: 12px; align-items: center; padding: 14px; border-radius: 18px; background: color-mix(in srgb, var(--text) 4%, transparent); border: 1px solid var(--surface-border); }
.rank-badge { width: 42px; height: 42px; border-radius: 14px; background: linear-gradient(135deg, #49a86b, #7fc894); color: #fff; display: grid; place-items: center; font-weight: 800; }
.leaderboard-body strong { display: block; color: var(--text-on-surface); }
.leaderboard-body p, .leaderboard-body span { margin: 4px 0 0; color: var(--text-muted-on-surface); font-size: 13px; }
.leaderboard-score { text-align: right; }
.leaderboard-score strong { display: block; color: var(--text-on-surface); }
.leaderboard-score span { color: var(--text-muted-on-surface); font-size: 13px; }
.leaderboard-actions { display: grid; gap: 10px; }
.action-link { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; border-radius: 16px; text-decoration: none; font-weight: 700; color: var(--text); background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%); }
.action-link.secondary { background: rgba(255,252,244,0.82); border: 1px solid var(--surface-border); color: var(--accent-strong); }
.empty-state { padding: 32px; text-align: center; color: var(--text-secondary); background: var(--surface); border: 1px solid var(--surface-border); border-radius: 24px; }
.skeleton-img { height: 180px; background: linear-gradient(90deg, color-mix(in srgb, var(--text) 4%, transparent) 25%, color-mix(in srgb, var(--text) 8%, transparent) 50%, color-mix(in srgb, var(--text) 4%, transparent) 75%); background-size: 400% 100%; animation: shimmer 1.4s infinite; }
.skeleton-body { padding: 24px; display: flex; flex-direction: column; gap: 10px; }
.skeleton-line { height: 12px; border-radius: 6px; background: linear-gradient(90deg, color-mix(in srgb, var(--text) 4%, transparent) 25%, color-mix(in srgb, var(--text) 8%, transparent) 50%, color-mix(in srgb, var(--text) 4%, transparent) 75%); background-size: 400% 100%; animation: shimmer 1.4s infinite; }
.skeleton-line.wide { width: 85%; height: 16px; }
.skeleton-line.medium { width: 65%; }
@keyframes shimmer { 0% { background-position: 100% 0; } 100% { background-position: -100% 0; } }
@media (max-width: 980px) { .content-grid { grid-template-columns: 1fr; } .leaderboard-card { position: static; } }
@media (max-width: 700px) { .news-section { padding: 60px 16px; } .news-title { font-size: 28px; } .leaderboard-summary { grid-template-columns: 1fr; } .leaderboard-item { grid-template-columns: 42px 1fr; } .leaderboard-score { grid-column: 2; text-align: left; } }
</style>
