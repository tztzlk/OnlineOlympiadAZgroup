<template>
  <div class="leaderboard-page">
    <div class="lb-wrap">
      <section class="hero-card" aria-labelledby="lb-title">
        <div class="hero-card__text">
          <p class="hero-eyebrow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
            Public leaderboard
          </p>
          <h1 id="lb-title">Рейтинг как доказательство результата, а не просто список имён</h1>
          <p class="hero-copy">
            Здесь родители и школы видят сильнейшие результаты платформы, активные предметы и недавние достижения участников.
          </p>
        </div>
        <div class="hero-actions">
          <RouterLink class="ds-btn ds-btn-sun ds-btn-lg" to="/register">Присоединиться к олимпиаде</RouterLink>
          <RouterLink class="ds-btn ds-btn-lg hero-ghost" to="/certificate-check">Проверить сертификат</RouterLink>
        </div>
      </section>

      <StatePanel
        v-if="loading"
        tone="neutral" loading
        eyebrow="Leaderboard"
        title="Собираем лучшие результаты"
        description="Загружаем актуальный рейтинг, чтобы показать самые сильные выступления участников."
      />

      <StatePanel
        v-else-if="!items.length"
        tone="empty"
        eyebrow="Leaderboard"
        title="Рейтинг появится после первых завершённых олимпиад"
        description="Как только участники завершат олимпиаду, здесь появятся лучшие результаты по предметам и категориям."
      >
        <template #actions>
          <RouterLink class="ds-btn ds-btn-primary" to="/subject">Выбрать олимпиаду</RouterLink>
        </template>
      </StatePanel>

      <template v-else>
        <!-- Пьедестал тройки лидеров -->
        <section v-if="podium.length" class="podium" aria-label="Тройка лидеров">
          <div
            v-for="item in podium"
            :key="`p-${item.rank}`"
            class="podium__place"
            :class="`podium__place--${item.rank}`"
          >
            <span class="podium__medal" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
            </span>
            <strong class="podium__name">{{ item.user_name }}</strong>
            <span class="podium__subject">{{ item.subject }}</span>
            <div class="podium__block">
              <span class="podium__rank">{{ item.rank }}</span>
              <span class="podium__percent">{{ item.percent }}%</span>
            </div>
          </div>
        </section>

        <section class="summary-grid">
          <article class="summary-card">
            <span>Лучший результат</span>
            <strong>{{ topScore?.percent || 0 }}%</strong>
            <small>{{ topScore?.subject || 'Предмет появится позже' }}</small>
          </article>
          <article class="summary-card">
            <span>Предметов в рейтинге</span>
            <strong>{{ topSubjectsCount }}</strong>
            <small>Показываем реальную активность по направлениям</small>
          </article>
          <article class="summary-card">
            <span>Недавних достижений</span>
            <strong>{{ recentCount }}</strong>
            <small>Результаты за последние публикации рейтинга</small>
          </article>
        </section>

        <section class="table-card" aria-labelledby="lb-table-title">
          <div class="table-head">
            <div>
              <p class="eyebrow">Top participants</p>
              <h2 id="lb-table-title">Лучшие результаты платформы</h2>
            </div>
            <p class="table-copy">Каждая запись отражает завершённую олимпиаду и помогает быстро увидеть сильные предметы и уровни подготовки.</p>
          </div>

          <ol class="leaderboard-list">
            <li v-for="item in items" :key="`${item.rank}-${item.user_name}`" class="leaderboard-row" :class="`is-rank-${item.rank}`">
              <span class="leaderboard-rank">{{ item.rank }}</span>
              <div class="leaderboard-main">
                <strong>{{ item.user_name }}</strong>
                <p>{{ item.subject }} · {{ item.category }}</p>
                <span>{{ item.school }}, {{ item.city }}</span>
              </div>
              <div class="leaderboard-bar" aria-hidden="true">
                <span :style="{ width: `${item.percent}%` }"></span>
              </div>
              <div class="leaderboard-meta">
                <strong>{{ item.percent }}%</strong>
                <span>{{ item.score }}/{{ item.total }}</span>
                <small>{{ item.date }}</small>
              </div>
            </li>
          </ol>
        </section>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../js/api'
import StatePanel from '../components/StatePanel.vue'

const loading = ref(true)
const items = ref([])

const topScore = computed(() => items.value[0] || null)
const topSubjectsCount = computed(() => new Set(items.value.map((item) => item.subject)).size)
const recentCount = computed(() => items.value.filter((item) => item.date).length)

// Порядок на пьедестале: 2-е место слева, 1-е по центру, 3-е справа.
const podium = computed(() => {
  const [first, second, third] = items.value
  return [second, first, third].filter(Boolean)
})

const loadLeaderboard = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/leaderboard')
    items.value = data
  } catch {
    items.value = []
  } finally {
    loading.value = false
  }
}

onMounted(loadLeaderboard)
</script>

<style scoped>
.leaderboard-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 24px) 20px 80px;
  background:
    radial-gradient(700px 360px at 50% 0%, color-mix(in srgb, var(--sun) 18%, transparent), transparent 70%),
    var(--bg);
}

.lb-wrap {
  max-width: 1160px;
  margin: 0 auto;
  display: grid;
  gap: 18px;
}

/* ---- Шапка ---- */
.hero-card {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  padding: clamp(24px, 4vw, 40px);
  border-radius: var(--radius-xl);
  background:
    radial-gradient(500px 260px at 100% 0%, rgba(255, 255, 255, 0.16), transparent 70%),
    linear-gradient(155deg, #3d6cff 0%, #2b5bf5 50%, #2046d4 100%);
  color: #ffffff;
  box-shadow: var(--shadow-brand), var(--shadow-md);
}

.hero-card__text { max-width: 640px; }

.hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 10px;
  color: var(--sun);
  font-size: 14px;
  font-weight: 600;
}

.hero-card h1 { font-size: clamp(26px, 3.6vw, 40px); }

.hero-copy {
  margin-top: 10px;
  color: rgba(255, 255, 255, 0.85);
  font-size: 16.5px;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  flex-shrink: 0;
}

.hero-ghost {
  background: rgba(255, 255, 255, 0.14);
  border-color: rgba(255, 255, 255, 0.3);
  color: #ffffff;
}

.hero-ghost:hover { background: rgba(255, 255, 255, 0.24); }

/* ---- Пьедестал ---- */
.podium {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  align-items: end;
  gap: 12px;
  max-width: 760px;
  width: 100%;
  margin: 8px auto 0;
}

.podium__place {
  display: grid;
  justify-items: center;
  gap: 4px;
  text-align: center;
  min-width: 0;
}

.podium__medal {
  width: 48px;
  height: 48px;
  display: grid;
  place-items: center;
  margin-bottom: 4px;
  border-radius: 50%;
  background: #dfe4ef;
  color: #ffffff;
  box-shadow: var(--shadow-sm);
}

/* Место всегда в своей колонке, даже если участников меньше трёх */
.podium__place--1 { grid-column: 2; grid-row: 1; }
.podium__place--2 { grid-column: 1; grid-row: 1; }
.podium__place--3 { grid-column: 3; grid-row: 1; }

.podium__place--1 .podium__medal { width: 60px; height: 60px; background: var(--sun); }
.podium__place--2 .podium__medal { background: #aab4cc; }
.podium__place--3 .podium__medal { background: #e59a6a; }

.podium__name {
  max-width: 100%;
  overflow: hidden;
  font-size: 16px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.podium__subject {
  color: var(--text-secondary);
  font-size: 13.5px;
}

.podium__block {
  width: 100%;
  display: grid;
  align-content: start;
  justify-items: center;
  gap: 2px;
  margin-top: 8px;
  padding-top: 14px;
  border-radius: var(--radius-lg) var(--radius-lg) 0 0;
  background: var(--card);
  border: 1px solid var(--border);
  border-bottom: 0;
}

.podium__place--1 .podium__block { height: 150px; background: var(--brand); border-color: transparent; color: #ffffff; }
.podium__place--2 .podium__block { height: 116px; }
.podium__place--3 .podium__block { height: 92px; }

.podium__rank {
  font-family: var(--font-display);
  font-size: 30px;
  font-weight: 700;
  line-height: 1;
}

.podium__percent {
  font-size: 15px;
  font-weight: 600;
  opacity: 0.85;
}

/* ---- Сводка ---- */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.summary-card {
  display: grid;
  gap: 4px;
  padding: 20px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
}

.summary-card span {
  color: var(--text-secondary);
  font-size: 14px;
}

.summary-card strong {
  font-family: var(--font-display);
  font-size: 32px;
  line-height: 1.1;
}

.summary-card small {
  color: var(--text-tertiary);
  font-size: 13.5px;
}

/* ---- Таблица ---- */
.table-card {
  display: grid;
  gap: 18px;
  padding: clamp(18px, 3vw, 28px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
}

.table-head {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 12px 24px;
}

.eyebrow {
  margin-bottom: 4px;
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
}

.table-head h2 { font-size: clamp(20px, 2.4vw, 26px); }

.table-copy {
  max-width: 46ch;
  color: var(--text-secondary);
  font-size: 14.5px;
}

.leaderboard-list {
  display: grid;
  gap: 6px;
  list-style: none;
}

.leaderboard-row {
  display: grid;
  grid-template-columns: 44px minmax(0, 1.4fr) minmax(80px, 1fr) auto;
  align-items: center;
  gap: 16px;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  transition: background-color var(--dur) ease;
}

.leaderboard-row:hover { background: var(--bg); }

.leaderboard-rank {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: var(--bg-alt);
  color: var(--text-secondary);
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.is-rank-1 .leaderboard-rank { background: var(--sun); color: #1f1600; }
.is-rank-2 .leaderboard-rank { background: #dfe4ef; color: #3a4466; }
.is-rank-3 .leaderboard-rank { background: #ffdcc6; color: #8a3d12; }

.leaderboard-main { min-width: 0; }
.leaderboard-main strong { font-size: 16px; }

.leaderboard-main p,
.leaderboard-main span {
  display: block;
  margin-top: 2px;
  overflow: hidden;
  color: var(--text-secondary);
  font-size: 13.5px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.leaderboard-bar {
  height: 8px;
  border-radius: var(--radius-pill);
  background: var(--bg-alt);
  overflow: hidden;
}

.leaderboard-bar span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: var(--brand);
}

.leaderboard-meta {
  display: grid;
  justify-items: end;
  text-align: right;
}

.leaderboard-meta strong {
  color: var(--brand-ink);
  font-size: 18px;
  font-variant-numeric: tabular-nums;
}

.leaderboard-meta span,
.leaderboard-meta small {
  color: var(--text-tertiary);
  font-size: 13px;
  font-variant-numeric: tabular-nums;
}

:global(.dark) .is-rank-2 .leaderboard-rank { background: #2b3456; color: #d6dcf0; }
:global(.dark) .is-rank-3 .leaderboard-rank { background: #4a2a18; color: #ffcfb0; }

@media (max-width: 860px) {
  .hero-card { flex-direction: column; align-items: stretch; }
  .summary-grid { grid-template-columns: 1fr; }
  .leaderboard-row { grid-template-columns: 44px minmax(0, 1fr) auto; }
  .leaderboard-bar { display: none; }
}

@media (max-width: 560px) {
  .leaderboard-page { padding: calc(var(--header-h) + 14px) 12px 96px; }
  .hero-actions .ds-btn { flex: 1 1 100%; }
  .podium__place--1 .podium__block { height: 120px; }
  .podium__place--2 .podium__block { height: 96px; }
  .podium__place--3 .podium__block { height: 78px; }
  .podium__name { font-size: 14px; }
}
</style>
