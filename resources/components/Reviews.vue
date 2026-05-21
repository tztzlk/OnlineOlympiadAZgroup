<template>
  <section ref="sectionRef" class="reviews-section" :class="{ visible: isVisible }">
    <div class="reviews-inner">
      <div class="reviews-layout">
        <aside class="reviews-sidebar">
          <span class="reviews-eyebrow">Отзывы</span>
          <h2>Что говорят родители и участники</h2>
          <p class="reviews-lead">
            Впечатления тех, кто уже прошёл олимпиаду и получил результат с сертификатом.
          </p>

          <div class="reviews-filter">
            <button
              v-for="f in filters"
              :key="f.key"
              type="button"
              class="filter-btn"
              :class="{ active: activeFilter === f.key }"
              @click="activeFilter = f.key"
            >{{ f.label }}</button>
          </div>

          <div class="reviews-stats">
            <div class="stat-row" v-for="stat in stats" :key="stat.label">
              <strong>{{ stat.value }}</strong>
              <span>{{ stat.label }}</span>
            </div>
          </div>

          <div class="reviews-controls">
            <button class="scroll-btn" type="button" aria-label="Назад" @click="scrollLeft">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="15 18 9 12 15 6" />
              </svg>
            </button>
            <button class="scroll-btn" type="button" aria-label="Вперёд" @click="scrollRight">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </button>
          </div>
        </aside>

        <div class="reviews-content">
          <div class="reviews-track" ref="scrollContainer">
            <article class="review-card" v-for="(review, index) in filteredReviews" :key="index">
              <div class="review-card__top">
                <svg class="review-card__quote" width="34" height="34" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
                </svg>

                <div class="review-card__stars">
                  <svg v-for="i in 5" :key="i" width="14" height="14" viewBox="0 0 24 24" :fill="i <= review.rating ? '#f5c842' : '#d9dee8'" stroke="none">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
                  </svg>
                </div>
              </div>

              <p class="review-card__text">"{{ review.text }}"</p>

              <footer class="review-card__footer">
                <div class="review-card__person">
                  <div class="review-card__avatar">{{ review.name.charAt(0) }}</div>
                  <div class="review-card__meta">
                    <strong>{{ review.name }}</strong>
                    <span>{{ review.role }}</span>
                  </div>
                </div>

                <div class="review-card__badge" title="Подтверждённый участник">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M9 12l2 2 4-4" />
                    <path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z" />
                  </svg>
                  <span>Участник</span>
                </div>
              </footer>
            </article>
          </div>

          <div class="reviews-dots">
            <button
              v-for="(_, i) in filteredReviews"
              :key="i"
              type="button"
              class="dot"
              :class="{ active: activeDot === i }"
              :aria-label="`Отзыв ${i + 1}`"
              @click="scrollToCard(i)"
            ></button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'

const scrollContainer = ref(null)
const sectionRef = ref(null)
const isVisible = ref(false)
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

const getCardStep = () => {
  const firstCard = scrollContainer.value?.children[0]
  if (!firstCard) return 360
  const gap = 24
  return firstCard.getBoundingClientRect().width + gap
}

const clampActiveDot = (value) => {
  activeDot.value = Math.max(0, Math.min(filteredReviews.value.length - 1, value))
}

const updateDot = () => {
  if (!scrollContainer.value) return
  const step = getCardStep()
  clampActiveDot(Math.round(scrollContainer.value.scrollLeft / step))
}

const scrollLeft = () => {
  scrollContainer.value?.scrollBy({ left: -getCardStep(), behavior: 'smooth' })
}

const scrollRight = () => {
  scrollContainer.value?.scrollBy({ left: getCardStep(), behavior: 'smooth' })
}

const scrollToCard = (index) => {
  const card = scrollContainer.value?.children[index]
  card?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' })
  clampActiveDot(index)
}

let observer = null

watch(activeFilter, () => {
  activeDot.value = 0
  if (scrollContainer.value) scrollContainer.value.scrollLeft = 0
})

onMounted(() => {
  scrollContainer.value?.addEventListener('scroll', updateDot, { passive: true })

  observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        isVisible.value = true
        observer?.disconnect()
      }
    },
    { threshold: 0.18, rootMargin: '0px 0px -40px 0px' }
  )

  if (sectionRef.value) {
    observer.observe(sectionRef.value)
  }

  updateDot()
})

onUnmounted(() => {
  scrollContainer.value?.removeEventListener('scroll', updateDot)
  observer?.disconnect()
})
</script>

<style scoped>
* { box-sizing: border-box; }

.reviews-section {
  background: var(--bg);
  padding: 84px 0 96px;
  overflow: hidden;
}

.reviews-inner {
  max-width: 1240px;
  margin: 0 auto;
  padding: 0 28px;
}

.reviews-layout {
  display: grid;
  grid-template-columns: minmax(260px, 360px) minmax(0, 1fr);
  gap: 40px;
  align-items: center;
  opacity: 0;
  transform: translateY(18px);
  transition: opacity 0s, transform 0s;
}

.reviews-section.visible .reviews-layout {
  opacity: 1;
  transform: translateY(0);
  transition: opacity 0.55s cubic-bezier(0.23, 1, 0.32, 1), transform 0.55s cubic-bezier(0.23, 1, 0.32, 1);
}

.reviews-sidebar {
  display: grid;
  gap: 20px;
  align-content: center;
}

.reviews-eyebrow {
  display: inline-flex;
  width: fit-content;
  padding: 7px 14px;
  border-radius: 999px;
  background: color-mix(in srgb, var(--accent) 14%, transparent);
  color: #8b6c11;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.reviews-sidebar h2 {
  margin: 0;
  color: var(--text);
  font-size: clamp(34px, 4vw, 52px);
  line-height: 0.98;
  text-wrap: balance;
}

.reviews-lead {
  margin: 0;
  color: var(--text-secondary);
  font-size: 17px;
  line-height: 1.7;
  max-width: 34ch;
}

.reviews-filter {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.filter-btn {
  padding: 7px 16px;
  border-radius: 999px;
  border: 1.5px solid var(--border);
  background: var(--card);
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.filter-btn.active,
.filter-btn:hover {
  background: var(--green);
  border-color: var(--green);
  color: #ffffff;
}

.reviews-stats {
  display: grid;
  gap: 10px;
}

.stat-row {
  display: flex;
  align-items: baseline;
  gap: 8px;
  flex-wrap: wrap;
}

.stat-row strong {
  color: var(--green);
  font-size: clamp(28px, 3vw, 42px);
  line-height: 1;
  font-weight: 900;
}

.stat-row span {
  color: var(--text-secondary);
  font-size: 15px;
  line-height: 1.4;
}

.reviews-controls {
  display: flex;
  gap: 12px;
  padding-top: 6px;
}

.scroll-btn {
  width: 54px;
  height: 54px;
  border: 1px solid var(--border);
  border-radius: 50%;
  background: var(--card);
  color: var(--text);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.2s ease, border-color 0.2s ease;
}

@media (hover: hover) and (pointer: fine) {
  .scroll-btn:hover {
    transform: translateY(-2px);
    border-color: rgba(22, 163, 74, 0.22);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  }
}

.scroll-btn:active {
  transform: scale(0.95);
  transition-duration: 0.1s;
}

.reviews-content {
  min-width: 0;
}

.reviews-track {
  display: flex;
  gap: 24px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  padding: 10px 6px 18px;
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.reviews-track::-webkit-scrollbar {
  display: none;
}

.review-card {
  flex: 0 0 min(420px, calc(100vw - 96px));
  min-height: 340px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  padding: 34px;
  border-radius: 28px;
  border: 1px solid var(--border);
  background: var(--card);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  scroll-snap-align: start;
  transition: transform 0.24s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.24s ease, border-color 0.24s ease;
}

@media (hover: hover) and (pointer: fine) {
  .review-card:hover {
    transform: translateY(-6px);
    border-color: color-mix(in srgb, var(--accent) 22%, var(--border));
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  }
}

.review-card__top {
  display: grid;
  gap: 8px;
}

.review-card__quote {
  color: var(--accent);
  opacity: 0.82;
}

.review-card__stars {
  display: flex;
  gap: 3px;
}

.review-card__text {
  margin: 0;
  color: var(--text);
  font-size: 17px;
  line-height: 1.75;
  flex: 1;
}

.review-card__footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding-top: 20px;
  border-top: 1px solid var(--border);
}

.review-card__person {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.review-card__avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--green);
  color: #ffffff;
  font-size: 22px;
  font-weight: 800;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.review-card__meta {
  min-width: 0;
  display: grid;
  gap: 4px;
}

.review-card__meta strong {
  color: var(--text);
  font-size: 16px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.review-card__meta span {
  color: var(--text-secondary);
  font-size: 14px;
}

.review-card__badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 999px;
  background: var(--green-soft);
  color: var(--green-strong);
  border: 1px solid rgba(22, 163, 74, 0.18);
  font-size: 13px;
  font-weight: 700;
  white-space: nowrap;
  flex-shrink: 0;
}

.reviews-dots {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
  padding-right: 8px;
}

.dot {
  width: 10px;
  height: 10px;
  border: 0;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.12);
  cursor: pointer;
  transition: width 0.22s ease, background 0.22s ease;
}

.dot.active {
  width: 30px;
  background: var(--green);
}

.dark .dot {
  background: rgba(241, 245, 249, 0.16);
}

@media (max-width: 1080px) {
  .reviews-layout {
    grid-template-columns: 1fr;
    gap: 28px;
  }

  .reviews-sidebar {
    max-width: 760px;
  }

  .reviews-sidebar h2 {
    max-width: 12ch;
  }

  .reviews-controls {
    display: none;
  }

  .reviews-dots {
    justify-content: center;
    padding-right: 0;
  }
}

@media (max-width: 680px) {
  .reviews-section {
    padding: 68px 0 78px;
  }

  .reviews-inner {
    padding: 0 16px;
  }

  .reviews-sidebar h2 {
    font-size: clamp(30px, 9vw, 42px);
  }

  .reviews-lead {
    font-size: 16px;
  }

  .reviews-stats {
    grid-template-columns: 1fr 1fr;
    gap: 14px 20px;
  }

  .stat-row {
    display: grid;
    gap: 2px;
  }

  .stat-row strong {
    font-size: 28px;
  }

  .review-card {
    flex-basis: min(88vw, 360px);
    min-height: 320px;
    padding: 26px 22px;
  }

  .review-card__text {
    font-size: 16px;
  }

  .review-card__footer {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
