<template>
  <div class="leaderboard-page">
    <section class="hero-card">
      <div>
        <p class="eyebrow">Public leaderboard</p>
        <h1>Рейтинг как доказательство результата, а не просто список имён</h1>
        <p class="hero-copy">
          Здесь родители и школы видят сильнейшие результаты платформы, активные предметы и недавние достижения участников.
        </p>
      </div>
      <div class="hero-actions">
        <RouterLink class="btn btn-primary" to="/register">Присоединиться к олимпиаде</RouterLink>
        <RouterLink class="btn btn-outline" to="/certificate-check">Проверить сертификат</RouterLink>
      </div>
    </section>

    <StatePanel
      v-if="loading"
      tone="neutral"
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
        <RouterLink class="btn btn-primary" to="/subject">Выбрать олимпиаду</RouterLink>
      </template>
    </StatePanel>

    <template v-else>
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

      <section class="table-card">
        <div class="table-head">
          <div>
            <p class="eyebrow">Top participants</p>
            <h2>Лучшие результаты платформы</h2>
          </div>
          <p class="table-copy">Каждая запись отражает завершённую олимпиаду и помогает быстро увидеть сильные предметы и уровни подготовки.</p>
        </div>

        <div class="leaderboard-list">
          <article v-for="item in items" :key="`${item.rank}-${item.user_name}`" class="leaderboard-row">
            <div class="leaderboard-rank">{{ item.rank }}</div>
            <div class="leaderboard-main">
              <strong>{{ item.user_name }}</strong>
              <p>{{ item.subject }} · {{ item.category }}</p>
              <span>{{ item.school }}, {{ item.city }}</span>
            </div>
            <div class="leaderboard-meta">
              <strong>{{ item.percent }}%</strong>
              <span>{{ item.score }}/{{ item.total }}</span>
              <small>{{ item.date }}</small>
            </div>
          </article>
        </div>
      </section>
    </template>
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

const loadLeaderboard = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/leaderboard')
    items.value = data
  } finally {
    loading.value = false
  }
}

onMounted(loadLeaderboard)
</script>

<style scoped>
* { box-sizing: border-box; }

.leaderboard-page {
  min-height: 100vh;
  padding: 110px 18px 48px;
  background: radial-gradient(circle at top left, rgba(201, 171, 99, 0.14), transparent 24%), var(--bg);
  display: grid;
  gap: 18px;
}

.hero-card,
.summary-card,
.table-card {
  max-width: 1120px;
  width: 100%;
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
  gap: 20px;
  align-items: flex-start;
}

.eyebrow {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .12em;
  color: var(--accent-strong);
}

.hero-card h1,
.table-head h2 {
  margin: 0;
  color: var(--text);
}

.hero-copy,
.table-copy,
.leaderboard-main p,
.leaderboard-main span,
.leaderboard-meta span,
.leaderboard-meta small,
.summary-card span,
.summary-card small {
  color: var(--text-secondary);
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.summary-grid {
  max-width: 1120px;
  margin: 0 auto;
  display: grid;
  gap: 14px;
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.summary-card {
  padding: 22px;
  display: grid;
  gap: 8px;
}

.summary-card strong {
  font-size: 36px;
  line-height: 1;
  color: var(--text);
}

.table-card {
  padding: 24px;
  display: grid;
  gap: 18px;
}

.table-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
}

.leaderboard-list {
  display: grid;
  gap: 12px;
}

.leaderboard-row {
  display: grid;
  grid-template-columns: 56px 1fr auto;
  gap: 14px;
  align-items: center;
  padding: 16px;
  border-radius: var(--radius-md);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 244, 0.82);
}

.leaderboard-rank {
  width: 56px;
  height: 56px;
  border-radius: 18px;
  display: grid;
  place-items: center;
  font-weight: 800;
  color: var(--text);
  background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%);
}

.leaderboard-main strong,
.leaderboard-meta strong {
  color: var(--text);
}

.leaderboard-main p,
.leaderboard-main span,
.leaderboard-meta span,
.leaderboard-meta small {
  display: block;
  margin-top: 4px;
}

.leaderboard-meta {
  text-align: right;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
  padding: 12px 18px;
  border-radius: var(--radius-sm);
  border: 0;
  font-weight: 700;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%);
  color: var(--text);
}

.btn-outline {
  background: rgba(255, 251, 243, 0.86);
  color: var(--accent-strong);
  border: 1px solid rgba(201, 171, 99, 0.28);
}

@media (max-width: 860px) {
  .hero-card,
  .table-head,
  .leaderboard-row {
    grid-template-columns: 1fr;
    flex-direction: column;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .leaderboard-meta {
    text-align: left;
  }
}
</style>
