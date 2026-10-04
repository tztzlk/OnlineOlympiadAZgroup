<template>
  <section class="news" aria-labelledby="news-title">
    <header class="news__head" v-reveal>
      <span class="ds-eyebrow">Новости</span>
      <h2 id="news-title" class="news__title">Новости платформы и рейтинг участников</h2>
      <p class="news__subtitle">
        Следите за обновлениями и просматривайте реальные результаты участников, быстрые проверки сертификатов.
      </p>
    </header>

    <div class="news__grid">
      <div class="news__column">
        <div v-if="loading" class="news__list" aria-busy="true" aria-label="Загрузка новостей">
          <div v-for="n in 3" :key="n" class="news-card news-card--skeleton">
            <span class="ds-skeleton news-card__media"></span>
            <div class="news-card__body">
              <span class="ds-skeleton" style="height: 14px; width: 30%"></span>
              <span class="ds-skeleton" style="height: 20px; width: 85%"></span>
              <span class="ds-skeleton" style="height: 14px; width: 65%"></span>
            </div>
          </div>
        </div>

        <div v-else-if="!newsList.length" class="empty-state">
          <span class="empty-state__icon" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M3 15h6M3 18h6M3 12h4"/></svg>
          </span>
          <p>Новости скоро появятся. Пока можно посмотреть рейтинг участников и проверить сертификат по ID.</p>
        </div>

        <div v-else class="news__list">
          <article class="news-card" v-for="(news, index) in newsList" :key="news.id" v-reveal="index">
            <div class="news-card__media">
              <img :src="news.image" :alt="news.title" loading="lazy" />
            </div>
            <div class="news-card__body">
              <time class="news-card__date" v-if="news.date" :datetime="news.date">{{ formatDate(news.date) }}</time>
              <h3 class="news-card__title">{{ news.title }}</h3>
              <p class="news-card__desc">{{ news.description }}</p>
              <a v-if="news.link" :href="news.link" target="_blank" rel="noopener noreferrer" class="news-card__more">
                Читать далее
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg>
              </a>
            </div>
          </article>
        </div>
      </div>

      <aside class="board" aria-labelledby="board-title" v-reveal>
        <div class="board__head">
          <p class="board__eyebrow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
            Leaderboard
          </p>
          <h2 id="board-title" class="board__title">Рейтинг участников</h2>
          <p class="board__copy">
            Лучшие завершённые результаты, которые помогают родителям и школам убедиться в качестве платформы.
          </p>
        </div>

        <div v-if="leaderboard.length" class="board__summary">
          <div class="summary-pill">
            <span>Лучший результат</span>
            <strong>{{ leaderboard[0].percent }}%</strong>
          </div>
          <div class="summary-pill">
            <span>Предметов</span>
            <strong>{{ uniqueSubjects }}</strong>
          </div>
        </div>

        <div v-if="leaderboardLoading" class="board__list" aria-busy="true">
          <span v-for="n in 3" :key="n" class="ds-skeleton" style="height: 64px; border-radius: 16px"></span>
          <span class="sr-only">Загружаем рейтинг...</span>
        </div>
        <p v-else-if="!leaderboard.length" class="board__empty">Пока нет завершённых результатов.</p>

        <ol v-else class="board__list">
          <li v-for="item in leaderboard" :key="`${item.rank}-${item.user_name}`" class="board-item" :class="`board-item--${item.rank}`">
            <span class="board-item__rank">{{ item.rank }}</span>
            <div class="board-item__body">
              <strong>{{ item.user_name }}</strong>
              <p>{{ item.subject }} · {{ item.category }}</p>
              <span>{{ item.school }}, {{ item.city }}</span>
            </div>
            <div class="board-item__score">
              <strong>{{ item.percent }}%</strong>
              <span>{{ item.score }}/{{ item.total }}</span>
            </div>
          </li>
        </ol>

        <div class="board__actions">
          <RouterLink class="ds-btn ds-btn-primary ds-btn-block" to="/leaderboard">Открыть весь рейтинг</RouterLink>
          <RouterLink class="ds-btn ds-btn-ghost ds-btn-block" to="/certificate-check">Проверить сертификат</RouterLink>
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
  } catch {
    newsList.value = []
  } finally {
    loading.value = false
  }
}

const fetchLeaderboard = async () => {
  try {
    const res = await api.get('/leaderboard')
    leaderboard.value = res.data
  } catch {
    leaderboard.value = []
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
.news {
  max-width: 1248px;
  margin: 0 auto;
  padding: clamp(64px, 9vw, 112px) 24px clamp(72px, 10vw, 120px);
}

.news__head {
  display: grid;
  justify-items: start;
  gap: 14px;
  max-width: 680px;
  margin-bottom: 40px;
}

.news__subtitle {
  color: var(--text-secondary);
  font-size: 18px;
}

.news__grid {
  display: grid;
  grid-template-columns: minmax(0, 1.65fr) minmax(340px, 1fr);
  gap: 24px;
  align-items: start;
}

.news__list {
  display: grid;
  gap: 16px;
}

/* ---- Новость ---- */
.news-card {
  display: grid;
  grid-template-columns: 240px minmax(0, 1fr);
  overflow: hidden;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-xs);
  transition: transform var(--dur-slow) var(--ease-out), box-shadow var(--dur-slow) ease;
}

@media (hover: hover) and (pointer: fine) {
  .news-card:not(.news-card--skeleton):hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
  .news-card:hover img { transform: scale(1.04); }
}

.news-card__media {
  display: block;
  min-height: 180px;
  height: 100%;
  overflow: hidden;
  background: var(--bg-alt);
  border-radius: 0;
}

.news-card__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 600ms var(--ease-out);
}

.news-card__body {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 22px 24px;
}

.news-card__date {
  color: var(--text-tertiary);
  font-size: 13px;
  font-weight: 500;
}

.news-card__title {
  font-size: 19px;
  line-height: 1.3;
}

.news-card__desc {
  color: var(--text-secondary);
  font-size: 15px;
  line-height: 1.6;
}

.news-card__more {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: auto;
  padding-top: 8px;
  color: var(--brand-ink);
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
}

.news-card__more:hover { text-decoration: underline; text-underline-offset: 3px; }

.empty-state {
  display: grid;
  justify-items: center;
  gap: 14px;
  padding: 48px 32px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1.5px dashed var(--border-strong);
  color: var(--text-secondary);
  text-align: center;
}

.empty-state p { max-width: 42ch; }

.empty-state__icon {
  width: 60px;
  height: 60px;
  display: grid;
  place-items: center;
  border-radius: 18px;
  background: var(--brand-soft);
  color: var(--brand);
}

/* ---- Рейтинг ---- */
.board {
  position: sticky;
  top: calc(var(--header-h) + 20px);
  display: grid;
  gap: 18px;
  padding: 24px;
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-md);
}

.board__eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 8px;
  color: #d99a00;
  font-size: 13px;
  font-weight: 700;
}

.board__title {
  font-size: clamp(22px, 2.2vw, 26px);
}

.board__copy {
  margin-top: 8px;
  color: var(--text-secondary);
  font-size: 15px;
  line-height: 1.55;
}

.board__summary {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.summary-pill {
  display: grid;
  gap: 4px;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.summary-pill:first-child { background: var(--sun-soft); }

.summary-pill span {
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 500;
}

.summary-pill strong {
  font-family: var(--font-display);
  font-size: 26px;
  line-height: 1.1;
  letter-spacing: -0.03em;
}

.board__empty {
  padding: 16px;
  border-radius: var(--radius-md);
  background: var(--bg);
  color: var(--text-secondary);
  text-align: center;
}

.board__list {
  display: grid;
  gap: 8px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.board-item {
  display: grid;
  grid-template-columns: 40px minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
  padding: 12px;
  border-radius: var(--radius-md);
  transition: background-color var(--dur) ease;
}

.board-item:hover { background: var(--bg); }

.board-item__rank {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  border-radius: 13px;
  background: var(--bg-alt);
  color: var(--text-secondary);
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.board-item--1 .board-item__rank { background: var(--sun); color: #1f1600; }
.board-item--2 .board-item__rank { background: #dfe4ef; color: #3a4466; }
.board-item--3 .board-item__rank { background: #ffdcc6; color: #8a3d12; }

.board-item__body { min-width: 0; }

.board-item__body strong {
  display: block;
  font-size: 15px;
}

.board-item__body p,
.board-item__body span {
  display: block;
  margin-top: 2px;
  overflow: hidden;
  color: var(--text-secondary);
  font-size: 13px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.board-item__score { text-align: right; }

.board-item__score strong {
  display: block;
  color: var(--brand-ink);
  font-size: 17px;
  font-variant-numeric: tabular-nums;
}

.board-item__score span {
  color: var(--text-tertiary);
  font-size: 13px;
  font-variant-numeric: tabular-nums;
}

.board__actions {
  display: grid;
  gap: 10px;
}

@media (max-width: 1000px) {
  .news__grid { grid-template-columns: 1fr; }
  .board { position: static; }
}

@media (max-width: 680px) {
  .news { padding-left: 16px; padding-right: 16px; }
  .news-card { grid-template-columns: 1fr; }
  .news-card__media { min-height: 0; height: 180px; }
  .board { padding: 20px; border-radius: var(--radius-lg); }
}

:global(.dark) .board-item--2 .board-item__rank { background: #2b3456; color: #d6dcf0; }
:global(.dark) .board-item--3 .board-item__rank { background: #4a2a18; color: #ffcfb0; }
</style>
