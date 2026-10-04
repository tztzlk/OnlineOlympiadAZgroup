<template>
  <section class="reviews" aria-labelledby="reviews-title">
    <div class="reviews__inner">
      <header class="reviews__head" v-reveal>
        <div class="reviews__intro">
          <span class="ds-eyebrow sun">Отзывы</span>
          <h2 id="reviews-title">Что говорят родители и участники</h2>
          <p class="reviews__lead">
            Впечатления тех, кто уже прошёл олимпиаду и получил результат с сертификатом.
          </p>
        </div>

        <dl class="reviews__stats">
          <div class="stat" v-for="stat in stats" :key="stat.label">
            <dt>{{ stat.label }}</dt>
            <dd>{{ stat.value }}</dd>
          </div>
        </dl>
      </header>

      <div class="reviews__toolbar" v-reveal>
        <div class="segmented" role="tablist" aria-label="Фильтр отзывов">
          <button
            v-for="f in filters"
            :key="f.key"
            type="button"
            role="tab"
            class="segmented__btn"
            :class="{ 'is-active': activeFilter === f.key }"
            :aria-selected="activeFilter === f.key ? 'true' : 'false'"
            @click="activeFilter = f.key"
          >{{ f.label }}</button>
        </div>

        <div class="reviews__controls">
          <span class="reviews__counter" aria-live="polite">
            <strong>{{ activeDot + 1 }}</strong> / {{ filteredReviews.length }}
          </span>
          <button class="ds-icon-btn" type="button" aria-label="Назад" :disabled="activeDot === 0" @click="scrollLeft">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
          <button class="ds-icon-btn" type="button" aria-label="Вперёд" :disabled="activeDot >= filteredReviews.length - 1" @click="scrollRight">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
          </button>
        </div>
      </div>

      <div class="reviews__track" ref="scrollContainer" tabindex="0" aria-label="Отзывы, листайте горизонтально">
        <article class="review" v-for="(review, index) in filteredReviews" :key="review.name">
          <div class="review__top">
            <div class="review__stars" :aria-label="`Оценка ${review.rating} из 5`" role="img">
              <svg v-for="i in 5" :key="i" width="18" height="18" viewBox="0 0 24 24" :class="i <= review.rating ? 'is-on' : 'is-off'" aria-hidden="true">
                <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z" fill="currentColor"/>
              </svg>
            </div>
            <span class="review__badge" title="Подтверждённый участник">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>
              Участник
            </span>
          </div>

          <blockquote class="review__text">«{{ review.text }}»</blockquote>

          <footer class="review__person">
            <span class="review__avatar" :class="`tone-${index % 4}`" aria-hidden="true">{{ review.name.charAt(0) }}</span>
            <span class="review__meta">
              <strong>{{ review.name }}</strong>
              <span>{{ review.role }}</span>
            </span>
          </footer>
        </article>
      </div>

      <div class="reviews__progress" aria-hidden="true">
        <span :style="{ width: progressWidth }"></span>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const scrollContainer = ref(null)
const activeDot = ref(0)
const activeFilter = ref('all')

const filters = [
  { key: 'all', label: 'Все отзывы' },
  { key: 'parent', label: 'Родители' },
  { key: 'student', label: 'Участники' },
]

const stats = [
  { value: '1 247+', label: 'участников' },
  { value: '97.4%', label: 'рекомендуют' },
  { value: '4.9 / 5', label: 'средняя оценка' },
  { value: '6', label: 'предметов олимпиады' },
]

const reviews = [
  {
    name: 'Айгуль М.',
    role: 'Родитель',
    type: 'parent',
    text: 'Очень рада, что узнала о платформе. Дочь прошла олимпиаду по математике, и результат с разбором ошибок получили сразу после теста. Никакой путаницы, всё чётко и понятно.',
    rating: 5,
  },
  {
    name: 'Амина Б.',
    role: 'Родитель',
    type: 'parent',
    text: 'Удобно, что можно выбрать время самостоятельно. Сын проходил олимпиаду вечером после школы — никаких проблем с подключением. Сертификат получили мгновенно.',
    rating: 5,
  },
  {
    name: 'Светлана К.',
    role: 'Родитель',
    type: 'parent',
    text: 'Порекомендую всем родителям. Платформа современная, дизайн приятный, а поддержка ответила на вопросы очень быстро. Ребёнок доволен, планируем участвовать ещё.',
    rating: 5,
  },
  {
    name: 'Данияр А.',
    role: '9 класс',
    type: 'student',
    text: 'Интерфейс простой и удобный. Таймер не давит, задания интересные. После теста сразу увидел свой результат и понял, где допустил ошибки. Буду участвовать снова.',
    rating: 5,
  },
  {
    name: 'Илья К.',
    role: '7 класс',
    type: 'student',
    text: 'Задания были по уровню — не слишком лёгкие и не слишком сложные. Разбор ошибок помог разобраться в темах, которые я не до конца понимал. Хорошая платформа.',
    rating: 5,
  },
  {
    name: 'Сабина Н.',
    role: '8 класс',
    type: 'student',
    text: 'Удобно проходить с телефона. Интерфейс не лагал, вопросы переключались плавно. Получила сертификат — теперь могу добавить в портфолио.',
    rating: 4,
  },
]

const filteredReviews = computed(() => {
  if (activeFilter.value === 'all') return reviews
  return reviews.filter((r) => r.type === activeFilter.value)
})

const progressWidth = computed(() => {
  const total = filteredReviews.value.length || 1
  return `${((activeDot.value + 1) / total) * 100}%`
})

const getCardStep = () => {
  const track = scrollContainer.value
  const firstCard = track?.children[0]
  if (!firstCard) return 360
  const gap = parseFloat(getComputedStyle(track).columnGap) || 20
  return firstCard.getBoundingClientRect().width + gap
}

const clampActiveDot = (value) => {
  activeDot.value = Math.max(0, Math.min(filteredReviews.value.length - 1, value))
}

const updateDot = () => {
  const track = scrollContainer.value
  if (!track) return
  // В конце ленты последний отзыв считается активным, даже если он не доехал до левого края.
  if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 4) {
    clampActiveDot(filteredReviews.value.length - 1)
    return
  }
  clampActiveDot(Math.round(track.scrollLeft / getCardStep()))
}

const scrollLeft = () => {
  scrollContainer.value?.scrollBy({ left: -getCardStep(), behavior: 'smooth' })
}

const scrollRight = () => {
  scrollContainer.value?.scrollBy({ left: getCardStep(), behavior: 'smooth' })
}

watch(activeFilter, () => {
  activeDot.value = 0
  if (scrollContainer.value) scrollContainer.value.scrollLeft = 0
})

onMounted(() => {
  scrollContainer.value?.addEventListener('scroll', updateDot, { passive: true })
  updateDot()
})

onUnmounted(() => {
  scrollContainer.value?.removeEventListener('scroll', updateDot)
})
</script>

<style scoped>
.reviews {
  padding: clamp(64px, 9vw, 112px) 0 clamp(72px, 10vw, 120px);
  background: var(--bg);
  overflow: hidden;
}

.reviews__inner {
  max-width: 1248px;
  margin: 0 auto;
  padding: 0 24px;
}

.reviews__head {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 40px;
  align-items: end;
  margin-bottom: 36px;
}

.reviews__intro {
  display: grid;
  justify-items: start;
  gap: 14px;
}

.reviews__lead {
  max-width: 46ch;
  color: var(--text-secondary);
  font-size: 18px;
}

.reviews__stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin: 0;
}

.stat {
  display: flex;
  flex-direction: column-reverse;
  gap: 2px;
  padding: 16px 18px;
  border-radius: var(--radius-md);
  background: var(--card);
  border: 1px solid var(--border);
}

.stat:nth-child(1) { background: var(--brand); border-color: transparent; }
.stat:nth-child(1) dd,
.stat:nth-child(1) dt { color: #ffffff; }
.stat:nth-child(1) dt { opacity: 0.82; }
.stat:nth-child(3) { background: var(--sun-soft); border-color: transparent; }

.stat dd {
  margin: 0;
  font-family: var(--font-display);
  font-size: clamp(20px, 2.2vw, 26px);
  font-weight: 700;
  letter-spacing: -0.03em;
  font-variant-numeric: tabular-nums;
}

.stat dt {
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 500;
}

/* ---- Панель управления ---- */
.reviews__toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
}

.segmented {
  display: inline-flex;
  padding: 4px;
  gap: 4px;
  border-radius: var(--radius-sm);
  background: var(--bg-alt);
}

.segmented__btn {
  min-height: 40px;
  padding: 8px 16px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

.segmented__btn:hover { color: var(--text); }

.segmented__btn.is-active {
  background: var(--card);
  color: var(--text);
  box-shadow: var(--shadow-sm);
}

.reviews__controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.reviews__counter {
  margin-right: 6px;
  color: var(--text-secondary);
  font-size: 15px;
  font-variant-numeric: tabular-nums;
}

.reviews__counter strong { color: var(--text); }

.ds-icon-btn:disabled {
  opacity: 0.4;
  cursor: default;
}

/* ---- Лента ---- */
.reviews__track {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: minmax(300px, calc((100% - 40px) / 3));
  gap: 20px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scroll-padding: 0 4px;
  padding: 4px 4px 12px;
  margin: 0 -4px;
  scrollbar-width: none;
  border-radius: var(--radius-lg);
}

.reviews__track::-webkit-scrollbar { display: none; }

.review {
  scroll-snap-align: start;
  display: flex;
  flex-direction: column;
  gap: 18px;
  padding: 26px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-xs);
  transition: transform var(--dur-slow) var(--ease-out), box-shadow var(--dur-slow) ease;
}

@media (hover: hover) and (pointer: fine) {
  .review:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
}

.review__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.review__stars {
  display: inline-flex;
  gap: 2px;
}

.review__stars .is-on { color: #ffb800; }
.review__stars .is-off { color: var(--bg-alt); }

.review__badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 9px;
  border-radius: var(--radius-xs);
  background: var(--success-soft);
  color: var(--success-ink);
  font-size: 12.5px;
  font-weight: 600;
}

.review__text {
  flex: 1;
  color: var(--text);
  font-size: 16px;
  line-height: 1.65;
}

.review__person {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid var(--border);
}

.review__avatar {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  font-size: 17px;
  font-weight: 700;
  flex-shrink: 0;
}

.tone-0 { background: var(--brand-soft); color: var(--brand-ink); }
.tone-1 { background: var(--sun-soft); color: var(--sun-ink); }
.tone-2 { background: var(--tone-violet-soft); color: var(--tone-violet); }
.tone-3 { background: var(--tone-teal-soft); color: #0d8a7c; }

.review__meta {
  display: grid;
  line-height: 1.3;
}

.review__meta strong { font-size: 15px; }
.review__meta span { color: var(--text-secondary); font-size: 14px; }

.reviews__progress {
  height: 4px;
  margin-top: 12px;
  border-radius: var(--radius-pill);
  background: var(--bg-alt);
  overflow: hidden;
}

.reviews__progress span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: var(--brand);
  transition: width var(--dur-slow) var(--ease-out);
}

@media (max-width: 960px) {
  .reviews__head { grid-template-columns: 1fr; gap: 28px; align-items: start; }
  .reviews__stats { grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .reviews__track { grid-auto-columns: minmax(280px, calc((100% - 20px) / 2)); }
}

@media (max-width: 640px) {
  .reviews__inner { padding: 0 16px; }
  .reviews__stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .reviews__toolbar { flex-direction: column; align-items: stretch; }
  .segmented { display: flex; }
  .segmented__btn { flex: 1; padding: 8px 10px; }
  .reviews__controls { justify-content: flex-end; }
  .reviews__track { grid-auto-columns: 86%; gap: 12px; }
  .review { padding: 22px; }
}
</style>
