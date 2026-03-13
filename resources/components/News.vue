<template>
  <section class="news-section">

    <div class="news-header">
      <div class="news-badge">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2 2zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
          <path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6z"/>
        </svg>
        Новости
      </div>
      <h1 class="news-title">Новости олимпиады</h1>
      <p class="news-subtitle">Следите за актуальными событиями и обновлениями платформы</p>
    </div>

    <!-- Skeleton -->
    <div v-if="loading" class="news-grid">
      <div v-for="n in 6" :key="n" class="skeleton-card">
        <div class="skeleton-img"></div>
        <div class="skeleton-body">
          <div class="skeleton-line wide"></div>
          <div class="skeleton-line medium"></div>
          <div class="skeleton-line short"></div>
          <div class="skeleton-line short"></div>
          <div class="skeleton-tag"></div>
        </div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="!newsList.length" class="empty-state">
      <div class="empty-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 0-2 2z"/>
          <path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6z"/>
        </svg>
      </div>
      <p>Новости пока не опубликованы</p>
    </div>

    <!-- News grid -->
    <div v-else class="news-grid">
      <article
        class="news-card"
        v-for="(news, index) in newsList"
        :key="news.id"
        :class="{ featured: index === 0 }"
      >
        <div class="image-wrapper">
          <img :src="news.image" :alt="news.title" loading="lazy"/>
          <div class="image-overlay"></div>
          <div class="news-category" v-if="news.category">{{ news.category }}</div>
        </div>

        <div class="news-content">
          <div class="news-meta" v-if="news.date">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            {{ formatDate(news.date) }}
          </div>

          <h2 class="news-card-title">{{ news.title }}</h2>
          <p class="news-desc">{{ news.description }}</p>

          <a :href="news.link" target="_blank" class="read-more">
            Читать далее
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
            </svg>
          </a>
        </div>
      </article>
    </div>

  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../js/api'

const newsList = ref([])
const loading = ref(true)

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' })
}

const fetchNews = async () => {
  try {
    const res = await api.get('/news')
    newsList.value = res.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchNews)
</script>

<style scoped>
* { box-sizing: border-box; }

.news-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 90px 28px;
  
}

/* Header */
.news-header {
  text-align: center;
  margin-bottom: 56px;
}

.news-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #E11D48;
  background: rgba(225, 29, 72, 0.15);
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid rgba(225, 29, 72, 0.3);
  margin-bottom: 18px;
}

.news-title {
  font-size: 30px;
  font-weight: 600;
  color: #FFFFFF;
  margin: 0 0 12px;
  line-height: 1.25;
}

.news-subtitle {
  font-size: 16px;
  font-weight: 400;
  color: #A1A1AA;
  margin: 0;
  line-height: 1.6;
}

/* Grid */
.news-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
}

/* First card — featured (spans 2 columns) */
.news-card.featured {
  grid-column: span 2;
}
.news-card.featured .image-wrapper {
  height: 240px;
}
.news-card.featured .news-card-title {
  font-size: 20px;
}

/* Card */
.news-card {
  border-radius: 24px;
  overflow: hidden;
  background: #1A1A1A;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
  position: relative;
}
.news-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, #E11D48, #E11D48, #BE123C);
  opacity: 0;
  transition: opacity 0.3s;
  z-index: 1;
}
.news-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(225, 29, 72, 0.15);
  border-color: rgba(225, 29, 72, 0.35);
}
.news-card:hover::before { opacity: 1; }

/* Image */
.image-wrapper {
  height: 180px;
  overflow: hidden;
  position: relative;
  flex-shrink: 0;
}
.image-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}
.news-card:hover img { transform: scale(1.08); }

.image-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(15, 14, 26, 0.3), transparent);
}

.news-category {
  position: absolute;
  bottom: 12px;
  left: 12px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: white;
  background: rgba(225, 29, 72, 0.85);
  backdrop-filter: blur(6px);
  padding: 4px 10px;
  border-radius: 20px;
}

/* Content */
.news-content {
  padding: 24px;
  padding-top: 16px;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.news-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #A1A1AA;
  font-weight: 500;
  margin-bottom: 8px;
}

.news-card-title {
  font-size: 18px;
  font-weight: 600;
  color: #FFFFFF;
  margin: 0;
  line-height: 1.35;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.news-desc {
  font-size: 16px;
  font-weight: 400;
  color: #A1A1AA;
  line-height: 1.5;
  margin: 8px 0 0;
  flex: 1;
  display: -webkit-box;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.read-more {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  margin-top: 16px;
  font-size: 14px;
  font-weight: 500;
  color: #E11D48;
  text-decoration: none;
  padding: 8px 16px;
  background: rgba(225, 29, 72, 0.15);
  border-radius: 10px;
  border: 1px solid rgba(225, 29, 72, 0.4);
  align-self: flex-start;
  transition: all 0.2s;
}
.read-more:hover {
  background: #E11D48;
  color: white;
  border-color: #E11D48;
  transform: translateX(3px);
}
.read-more svg { transition: transform 0.2s; }
.read-more:hover svg { transform: translateX(3px); }

/* Skeleton */
.skeleton-card {
  border-radius: 24px;
  overflow: hidden;
  background: #1A1A1A;
  border: 1px solid rgba(255, 255, 255, 0.08);
}
.skeleton-img {
  height: 180px;
  background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
  background-size: 400% 100%;
  animation: shimmer 1.4s infinite;
}
.skeleton-body { padding: 24px; display: flex; flex-direction: column; gap: 10px; }
.skeleton-line {
  height: 12px;
  border-radius: 6px;
  background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
  background-size: 400% 100%;
  animation: shimmer 1.4s infinite;
}
.skeleton-line.wide { width: 85%; height: 16px; }
.skeleton-line.medium { width: 65%; }
.skeleton-line.short { width: 90%; }
.skeleton-tag { width: 80px; height: 28px; border-radius: 10px; background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%); background-size: 400% 100%; animation: shimmer 1.4s infinite; margin-top: 4px; }

@keyframes shimmer {
  0% { background-position: 100% 0; }
  100% { background-position: -100% 0; }
}

/* Empty */
.empty-state {
  text-align: center;
  padding: 64px 20px;
  color: #A1A1AA;
}
.empty-icon {
  width: 80px; height: 80px;
  background: rgba(255, 255, 255, 0.04);
  border-radius: 24px;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 20px;
  color: #cbd5e1;
}
.empty-state p { font-size: 15px; margin: 0; }

/* Responsive */
@media (max-width: 1024px) {
  .news-grid { grid-template-columns: repeat(2, 1fr); }
  .news-card.featured { grid-column: span 2; }
}

@media (max-width: 700px) {
  .news-section { padding: 60px 16px; }
  .news-title { font-size: 28px; }
  .news-grid { grid-template-columns: 1fr; gap: 16px; }
  .news-card.featured { grid-column: span 1; }
  .news-card.featured .image-wrapper { height: 200px; }
  .news-card.featured .news-card-title { font-size: 18px; }
}
</style>