<template>
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
    </div>

    <div class="reviews__scroll-container" ref="scrollContainer">
      <article class="review" v-for="(review, index) in reviews" :key="index">
        <div class="review__quote">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z" />
          </svg>
        </div>

        <p class="review__text">{{ review.text }}</p>

        <div class="review__stars">
          <svg v-for="i in review.rating" :key="i" width="13" height="13" viewBox="0 0 24 24" fill="#49a86b" stroke="none">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" />
          </svg>
        </div>

        <div class="review__footer">
          <div class="review__avatar">{{ review.name.charAt(0) }}</div>
          <div>
            <div class="review__name">{{ review.name }}</div>
            <div class="review__role">{{ review.role }}</div>
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
        :aria-label="`Перейти к отзыву ${i + 1}`"
        @click="scrollToCard(i)"
      ></button>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const scrollContainer = ref(null)
const sectionRef = ref(null)
const isVisible = ref(false)
const activeDot = ref(0)

const reviews = ref([
  { name: 'Айгуль С.', role: 'родитель', text: 'Очень удобная платформа. Ребёнок прошёл олимпиаду с интересом, а результат увидели сразу после завершения.', rating: 5 },
  { name: 'Илья К.', role: '7 класс', text: 'Задания понятные и интересные. Особенно понравилось, что потом можно вернуться к разбору и понять ошибки.', rating: 5 },
  { name: 'Мария П.', role: 'родитель', text: 'Современный дизайн и хорошая организация. Всё прошло спокойно, без лишних шагов и путаницы.', rating: 5 },
  { name: 'Данияр А.', role: '9 класс', text: 'Интерфейс простой и приятный. Проходить тест было удобно даже с телефона.', rating: 4 },
  { name: 'Алина Т.', role: 'родитель', text: 'Удобно отслеживать результаты ребёнка в кабинете. После участия всё понятно: результат, сертификат и что делать дальше.', rating: 5 },
  { name: 'Сергей Л.', role: '8 класс', text: 'Интересные задания, хороший темп и нормальная навигация по вопросам. Хочется участвовать ещё.', rating: 4 },
])

const scrollLeft = () => {
  scrollContainer.value?.scrollBy({ left: -360, behavior: 'smooth' })
}

const scrollRight = () => {
  scrollContainer.value?.scrollBy({ left: 360, behavior: 'smooth' })
}

const scrollToCard = (index) => {
  const card = scrollContainer.value?.children[index]
  card?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' })
}

const updateDot = () => {
  if (!scrollContainer.value) return
  const cardWidth = scrollContainer.value.children[0]?.offsetWidth + 20 || 360
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

.reviews {
  max-width: 1200px;
  margin: 0 auto;
  padding: 28px 28px 92px;
}

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
  align-items: end;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 22px;
}

.reviews__copy {
  max-width: 640px;
}

.reviews__eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 14px;
  border-radius: 999px;
  background: rgba(208, 179, 107, 0.14);
  border: 1px solid rgba(208, 179, 107, 0.22);
  color: #6c5a2f;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  margin-bottom: 14px;
}

.reviews__copy h2 {
  margin: 0 0 10px;
  font-size: clamp(28px, 4vw, 42px);
  line-height: 1.08;
  color: var(--text-primary);
}

.reviews__copy p {
  margin: 0;
  color: var(--text-secondary);
  font-size: 16px;
  line-height: 1.7;
}

.reviews__controls {
  display: flex;
  gap: 10px;
}

.scroll-btn {
  width: 46px;
  height: 46px;
  border: 1px solid rgba(84, 68, 30, 0.14);
  border-radius: 50%;
  background: rgba(255, 249, 239, 0.9);
  color: var(--text-primary);
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

.reviews__scroll-container {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  scroll-behavior: smooth;
  scroll-snap-type: x mandatory;
  padding: 8px 4px 18px;
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.reviews__scroll-container::-webkit-scrollbar {
  display: none;
}

.review {
  position: relative;
  flex: 0 0 340px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-height: 280px;
  padding: 30px;
  border-radius: 28px;
  border: 1px solid rgba(122, 100, 52, 0.12);
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.58), rgba(255, 248, 236, 0.78)),
    radial-gradient(circle at top right, rgba(208, 179, 107, 0.12), transparent 34%);
  box-shadow: 0 20px 50px rgba(67, 55, 28, 0.09);
  backdrop-filter: blur(12px);
  scroll-snap-align: start;
  transition: transform 0.22s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.22s cubic-bezier(0.23, 1, 0.32, 1);
}

@media (hover: hover) and (pointer: fine) {
  .review:hover {
    transform: translateY(-4px);
    box-shadow: 0 32px 64px rgba(67, 55, 28, 0.14);
  }
}

.review::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  pointer-events: none;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.16), transparent 48%);
}

.review__quote {
  color: rgba(73, 168, 107, 0.38);
  line-height: 0;
}

.review__text {
  margin: 0;
  flex: 1;
  font-size: 15px;
  line-height: 1.7;
  color: rgba(19, 16, 12, 0.86);
}

.review__stars {
  display: flex;
  gap: 3px;
}

.review__footer {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid rgba(122, 100, 52, 0.12);
}

.review__avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--success-soft), #74c58d);
  color: #fff;
  font-weight: 800;
  box-shadow: 0 8px 18px rgba(73, 168, 107, 0.24);
}

.review__name {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-primary);
}

.review__role {
  display: inline-flex;
  margin-top: 4px;
  padding: 5px 10px;
  border-radius: 999px;
  background: rgba(73, 168, 107, 0.12);
  color: #3e8059;
  font-size: 12px;
  font-weight: 700;
}

.reviews__dots {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.dot {
  width: 9px;
  height: 9px;
  border: 0;
  border-radius: 999px;
  background: rgba(128, 112, 81, 0.22);
  cursor: pointer;
  transition: width 0.2s ease, background 0.2s ease;
}

.dot.active {
  width: 24px;
  background: linear-gradient(90deg, #4ca36a, #d0b36b);
}

@media (max-width: 820px) {
  .reviews {
    padding: 20px 20px 76px;
  }

  .reviews__header {
    align-items: start;
    flex-direction: column;
  }

  .review {
    flex-basis: min(84vw, 340px);
  }
}

@media (max-width: 560px) {
  .reviews__copy p {
    font-size: 15px;
  }

  .reviews__controls {
    display: none;
  }

  .review {
    min-height: 260px;
    padding: 24px;
  }
}
</style>
