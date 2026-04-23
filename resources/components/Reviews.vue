<template>
  <section class="reviews-section">
    <div class="reviews-inner">

      <div class="reviews__header">
        <div class="reviews__copy">
          <span class="ds-eyebrow">Отзывы участников</span>
          <h2>Что говорят родители и дети</h2>
          <p>Более 1 200 участников уже прошли олимпиаду — вот несколько историй.</p>
        </div>
        <div class="reviews__controls">
          <button class="scroll-btn" type="button" aria-label="Назад" @click="scrollLeft">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="15 18 9 12 15 6" />
            </svg>
          </button>
          <button class="scroll-btn" type="button" aria-label="Вперёд" @click="scrollRight">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="9 18 15 12 9 6" />
            </svg>
          </button>
        </div>
      </div>
    </div>
        
  <section class="reviews" ref="sectionRef" :class="{ visible: isVisible }">
    <div class="reviews__header">
      <div class="reviews__copy">
        <span class="reviews__eyebrow">Отзывы</span>
        <h2>Что говорят родители и участники</h2>
        <p>Короткие впечатления тех, кто уже прошёл олимпиаду и получил результат.</p>
      </div>

      <div class="reviews__controls">
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

      <div class="trust-bar">
        <div class="trust-stat" v-for="stat in stats" :key="stat.label">
          <strong>{{ stat.value }}</strong>
          <span>{{ stat.label }}</span>
        </div>
      </div>

      <div class="reviews__scroll-container" ref="scrollContainer">
        <article class="review" v-for="(review, index) in reviews" :key="index">
          <div class="review__top">
            <svg class="review__quote-icon" width="32" height="32" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
            </svg>
            <div class="review__stars">
              <svg v-for="i in 5" :key="i" width="14" height="14" viewBox="0 0 24 24" :fill="i <= review.rating ? '#f5c842' : '#e2e8f0'" stroke="none">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
              </svg>
            </div>
          </div>

          <p class="review__text">"{{ review.text }}"</p>

          <div class="review__footer">
            <div class="review__avatar">{{ review.name.charAt(0) }}</div>
            <div class="review__meta">
              <div class="review__name">{{ review.name }}</div>
              <div class="review__role">{{ review.role }}</div>
            </div>
            <div class="review__verified" title="Подтверждённый участник">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M9 12l2 2 4-4" />
                <path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z" />
              </svg>
              Участник
            </div>
          </div>
        </article>
      </div>

      <div class="reviews__dots">
        <button
          v-for="(_, i) in reviews"
          :key="i"
          type="button"
          class="dot"
          :class="{ active: activeDot === i }"
          :aria-label="`Отзыв ${i + 1}`"
          @click="scrollToCard(i)"
        ></button>
      </div>

    </div>
   </section>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const scrollContainer = ref(null)
const sectionRef = ref(null)
const isVisible = ref(false)
const activeDot = ref(0)

const stats = [
  { value: '1 200+', label: 'участников' },
  { value: '98%', label: 'рекомендуют' },
  { value: '4.9 / 5', label: 'средняя оценка' },
  { value: '6 предметов', label: 'олимпиад' },
]

const reviews = ref([
  { name: 'Айгуль С.', role: 'Родитель', text: 'Очень удобная платформа. Ребёнок прошёл олимпиаду с интересом, а результат увидели сразу после завершения.', rating: 5 },
  { name: 'Илья К.', role: '7 класс', text: 'Задания понятные и интересные. Особенно понравилось, что потом можно вернуться к разбору и понять ошибки.', rating: 5 },
  { name: 'Мария П.', role: 'Родитель', text: 'Современный дизайн и хорошая организация. Всё прошло спокойно, без лишних шагов и путаницы.', rating: 5 },
  { name: 'Данияр А.', role: '9 класс', text: 'Интерфейс простой и приятный. Проходить тест было удобно даже с телефона.', rating: 4 },
  { name: 'Алина Т.', role: 'Родитель', text: 'Удобно отслеживать результаты ребёнка в кабинете. После участия всё понятно: результат, сертификат и что делать дальше.', rating: 5 },
  { name: 'Сергей Л.', role: '8 класс', text: 'Интересные задания, хороший темп и нормальная навигация по вопросам. Хочется участвовать ещё.', rating: 4 },
])

const scrollLeft = () => scrollContainer.value?.scrollBy({ left: -380, behavior: 'smooth' })
const scrollRight = () => scrollContainer.value?.scrollBy({ left: 380, behavior: 'smooth' })

const scrollToCard = (index) => {
  const card = scrollContainer.value?.children[index]
  card?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' })
}

const updateDot = () => {
  if (!scrollContainer.value) return
  const cardWidth = (scrollContainer.value.children[0]?.offsetWidth ?? 360) + 20
  activeDot.value = Math.round(scrollContainer.value.scrollLeft / cardWidth)
}

onMounted(() => {
  scrollContainer.value?.addEventListener('scroll', updateDot, { passive: true })

  const observer = new IntersectionObserver(
    ([entry]) => { if (entry.isIntersecting) { isVisible.value = true; observer.disconnect() } },
    { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
  )
  if (sectionRef.value) observer.observe(sectionRef.value)
})

onUnmounted(() => {
  scrollContainer.value?.removeEventListener('scroll', updateDot)
})
</script>

<style scoped>
* { box-sizing: border-box; }

.reviews-section {
  background: var(--bg);
  padding: 80px 0 96px;
}

.reviews-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 28px;
}

/* Header */
.reviews__header {
  opacity: 0;
  transform: translateY(16px);
  transition: opacity 0s, transform 0s;
}

.reviews.visible .reviews__header {
  opacity: 1;
  transform: translateY(0);
  transition: opacity 0.5s cubic-bezier(0.23, 1, 0.32, 1), transform 0.5s cubic-bezier(0.23, 1, 0.32, 1);
}

.reviews__header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 28px;
}

.reviews__copy {
  max-width: 560px;
  display: grid;
  gap: 12px;
}

.reviews__copy h2 {
  color: var(--text);
  font-size: clamp(28px, 3.5vw, 40px);
  line-height: 1.1;
}

.reviews__copy p {
  color: var(--text-secondary);
  font-size: 16px;
  line-height: 1.65;
}

.reviews__controls {
  display: flex;
  gap: 10px;
  flex-shrink: 0;
}

.scroll-btn {
  width: 44px;
  height: 44px;
  border: 1.5px solid var(--border);
  border-radius: 50%;
  background: var(--card);
  color: var(--text);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 10px 24px rgba(76, 61, 24, 0.08);
  transition: transform 0.2s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.2s ease, border-color 0.2s ease;
}

@media (hover: hover) and (pointer: fine) {
  .scroll-btn:hover {
    transform: translateY(-2px);
    border-color: rgba(73, 168, 107, 0.26);
    box-shadow: 0 16px 32px rgba(73, 168, 107, 0.14);
  }
}

.scroll-btn:active {
  transform: scale(0.93);
  box-shadow: 0 4px 12px rgba(76, 61, 24, 0.08);
  transition-duration: 0.1s;
}

.trust-stat strong {
  font-size: 20px;
  font-weight: 800;
  color: var(--green);
  line-height: 1;
}

.trust-stat span {
  font-size: 12px;
  color: var(--text-secondary);
  font-weight: 500;
  text-align: center;
}

/* Carousel */
.reviews__scroll-container {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  padding: 8px 4px 16px;
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.reviews__scroll-container::-webkit-scrollbar {
  display: none;
}

/* Card */
.review {
  flex: 0 0 340px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 28px;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--border);
  background: var(--card);
  box-shadow: var(--shadow-card);
  scroll-snap-align: start;
  transition: transform 0.22s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.22s cubic-bezier(0.23, 1, 0.32, 1);
}

@media (hover: hover) and (pointer: fine) {
  .review:hover {
    transform: translateY(-4px);
    box-shadow: 0 32px 64px rgba(67, 55, 28, 0.14);
  }
}

.review__quote-icon {
  color: var(--accent);
  opacity: 0.7;
}

.review__stars {
  display: flex;
  gap: 2px;
}

.review__text {
  margin: 0;
  flex: 1;
  font-size: 15px;
  line-height: 1.7;
  color: var(--text);
}

.review__footer {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-top: 16px;
  border-top: 1.5px solid var(--border);
}

.review__avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  flex-shrink: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--green);
  color: #ffffff;
  font-size: 16px;
  font-weight: 800;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.28);
}

.review__meta {
  flex: 1;
  min-width: 0;
}

.review__name {
  font-size: 14px;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.review__role {
  font-size: 12px;
  color: var(--text-secondary);
  margin-top: 2px;
}

.review__verified {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 9px;
  border-radius: 999px;
  background: var(--green-soft);
  color: var(--green-strong);
  border: 1px solid rgba(22, 163, 74, 0.18);
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
  flex-shrink: 0;
}

/* Dots */
.reviews__dots {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 24px;
}

.dot {
  width: 8px;
  height: 8px;
  border: 0;
  border-radius: 999px;
  background: var(--border);
  cursor: pointer;
  transition: width 0.2s, background 0.2s;
}

.dot.active {
  width: 24px;
  background: var(--green);
}

/* Responsive */
@media (max-width: 820px) {
  .reviews-section {
    padding: 60px 0 72px;
  }

  .reviews__header {
    align-items: flex-start;
    flex-direction: column;
  }

  .trust-bar {
    flex-wrap: wrap;
  }

  .trust-stat {
    flex: 1 1 calc(50% - 1px);
  }

  .trust-stat:nth-child(2) {
    border-right: none;
  }

  .trust-stat:nth-child(3) {
    border-top: 1.5px solid var(--border);
    border-right: 1.5px solid var(--border);
  }

  .trust-stat:nth-child(4) {
    border-top: 1.5px solid var(--border);
  }

  .review {
    flex-basis: min(82vw, 340px);
  }
}

@media (max-width: 560px) {
  .reviews-inner {
    padding: 0 16px;
  }

  .reviews__controls {
    display: none;
  }

  .review {
    padding: 22px 20px;
  }

  .trust-stat strong {
    font-size: 18px;
  }
}
</style>
