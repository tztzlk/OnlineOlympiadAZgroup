<template>
  <section class="reviews">

    <div class="reviews__header">
      <div class="reviews__badge">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        Отзывы
      </div>
      <h2 class="reviews__title">Что говорят участники<br/>и родители</h2>
      <p class="reviews__subtitle">Реальные впечатления от олимпиады</p>

      <div class="reviews__stats">
        <div class="stat">
          <span class="stat__num">4.9</span>
          <div class="stat__stars">
            <svg v-for="i in 5" :key="i" width="14" height="14" viewBox="0 0 24 24" fill="#6366f1" stroke="none">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
          </div>
          <span class="stat__label">средняя оценка</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
          <span class="stat__num">1 200+</span>
          <span class="stat__label">участников</span>
        </div>
        <div class="stat-divider"></div>
        <div class="stat">
          <span class="stat__num">98%</span>
          <span class="stat__label">рекомендуют</span>
        </div>
      </div>
    </div>

    <div class="reviews__wrapper">
      <button class="scroll-btn left" @click="scrollLeft" aria-label="Назад">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </button>

      <div class="reviews__scroll-container" ref="scrollContainer">
        <div
          class="review"
          v-for="(review, index) in reviews"
          :key="index"
        >
          <div class="review__quote">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
              <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
            </svg>
          </div>

          <p class="review__text">{{ review.text }}</p>

          <div class="review__stars">
            <svg v-for="i in (review.rating || 5)" :key="i" width="13" height="13" viewBox="0 0 24 24" fill="#6366f1" stroke="none">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
          </div>

          <div class="review__footer">
            <div class="review__avatar">{{ review.name.charAt(0) }}</div>
            <div>
              <div class="review__name">{{ review.name }}</div>
              <div class="review__role">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path v-if="review.role.includes('родитель')" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle v-if="review.role.includes('родитель')" cx="12" cy="7" r="4"/>
                  <path v-else d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path v-if="!review.role.includes('родитель')" d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
                {{ review.role }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <button class="scroll-btn right" @click="scrollRight" aria-label="Вперёд">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </button>
    </div>

    <!-- Scroll indicator dots -->
    <div class="reviews__dots">
      <div
        v-for="(_, i) in reviews"
        :key="i"
        class="dot"
        :class="{ active: activeDot === i }"
        @click="scrollToCard(i)"
      ></div>
    </div>

  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const scrollContainer = ref(null)
const activeDot = ref(0)

const reviews = ref([
  { name: "Айгуль С.", role: "родитель", text: "Очень удобная платформа. Ребёнок прошёл олимпиаду с интересом — задания разнообразные и не утомляют. Однозначно рекомендую!", rating: 5 },
  { name: "Илья К.", role: "7 класс", text: "Задания понятные и интересные. Хочу ещё такие олимпиады! Удобно, что можно видеть результаты сразу после завершения.", rating: 5 },
  { name: "Мария П.", role: "родитель", text: "Современный дизайн и хорошая организация. Приятно, что всё работает без лагов. Дочь в восторге от участия.", rating: 5 },
  { name: "Данияр А.", role: "9 класс", text: "Интерфейс простой и приятный. Проходить тест было легко и комфортно. Хотелось бы больше предметов!", rating: 4 },
  { name: "Алина Т.", role: "родитель", text: "Удобно отслеживать результаты и прогресс ребёнка в личном кабинете. Спасибо за такой продукт.", rating: 5 },
  { name: "Сергей Л.", role: "8 класс", text: "Интересные задания, уже советую друзьям. Буду участвовать снова в следующем году!", rating: 4 },
])

const scrollLeft = () => {
  scrollContainer.value.scrollBy({ left: -380, behavior: 'smooth' })
}
const scrollRight = () => {
  scrollContainer.value.scrollBy({ left: 380, behavior: 'smooth' })
}
const scrollToCard = (i) => {
  const card = scrollContainer.value.children[i]
  card?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' })
}

const updateDot = () => {
  if (!scrollContainer.value) return
  const { scrollLeft, offsetWidth } = scrollContainer.value
  const cardWidth = scrollContainer.value.children[0]?.offsetWidth + 24 || 380
  activeDot.value = Math.round(scrollLeft / cardWidth)
}

onMounted(() => scrollContainer.value?.addEventListener('scroll', updateDot))
onUnmounted(() => scrollContainer.value?.removeEventListener('scroll', updateDot))
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; }

.reviews {
  padding: 90px 28px;
  max-width: 1200px;
  margin: 0 auto;
  font-family: 'Manrope', sans-serif;
}

/* Header */
.reviews__header {
  text-align: center;
  margin-bottom: 52px;
}

.reviews__badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #6366f1;
  background: #eef2ff;
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid #c7d2fe;
  margin-bottom: 18px;
}

.reviews__title {
  font-family: 'Playfair Display', serif;
  font-size: 38px;
  font-weight: 700;
  color: #1e1b4b;
  margin: 0 0 12px;
  line-height: 1.2;
}

.reviews__subtitle {
  font-size: 16px;
  color: #94a3b8;
  margin: 0 0 32px;
}

/* Stats */
.reviews__stats {
  display: inline-flex;
  align-items: center;
  gap: 28px;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 16px 32px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}
.stat { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.stat__num {
  font-size: 22px;
  font-weight: 700;
  color: #1e1b4b;
  line-height: 1;
}
.stat__stars { display: flex; gap: 2px; }
.stat__label { font-size: 12px; color: #94a3b8; font-weight: 600; }
.stat-divider { width: 1px; height: 36px; background: #e2e8f0; }

/* Scroll wrapper */
.reviews__wrapper {
  position: relative;
}

.reviews__scroll-container {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  scroll-behavior: smooth;
  padding: 12px 4px 20px;
  scroll-snap-type: x mandatory;
  -ms-overflow-style: none;
  scrollbar-width: none;
}
.reviews__scroll-container::-webkit-scrollbar { display: none; }

/* Review card */
.review {
  flex: 0 0 340px;
  background: white;
  border-radius: 24px;
  padding: 30px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
  scroll-snap-align: start;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: relative;
  overflow: hidden;
}
.review::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, #818cf8, #6366f1, #a78bfa);
  opacity: 0;
  transition: opacity 0.3s;
}
.review:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(79, 70, 229, 0.12);
  border-color: #c7d2fe;
}
.review:hover::before { opacity: 1; }

.review__quote {
  color: #e0e7ff;
  line-height: 0;
}

.review__text {
  font-size: 15px;
  color: #475569;
  line-height: 1.7;
  margin: 0;
  flex: 1;
}

.review__stars { display: flex; gap: 3px; }

.review__footer {
  display: flex;
  align-items: center;
  gap: 12px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
}

.review__avatar {
  width: 40px; height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  font-size: 15px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 10px rgba(99, 102, 241, 0.25);
}

.review__name {
  font-size: 14px;
  font-weight: 700;
  color: #1e1b4b;
  margin-bottom: 3px;
}

.review__role {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  color: #6366f1;
  background: #eef2ff;
  padding: 3px 9px;
  border-radius: 10px;
  font-weight: 600;
}

/* Scroll buttons */
.scroll-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 44px; height: 44px;
  border-radius: 50%;
  border: 1px solid #e2e8f0;
  background: white;
  color: #4f46e5;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
  transition: all 0.2s;
  z-index: 2;
}
.scroll-btn:hover {
  background: #eef2ff;
  border-color: #c7d2fe;
  transform: translateY(-50%) scale(1.08);
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.2);
}
.left { left: -22px; }
.right { right: -22px; }

/* Dots */
.reviews__dots {
  display: flex;
  justify-content: center;
  gap: 6px;
  margin-top: 8px;
}
.dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: #e2e8f0;
  cursor: pointer;
  transition: all 0.3s;
}
.dot.active {
  width: 24px;
  border-radius: 4px;
  background: linear-gradient(90deg, #6366f1, #a78bfa);
}

/* Responsive */
@media (max-width: 900px) {
  .reviews { padding: 60px 20px; }
  .reviews__title { font-size: 28px; }
  .review { flex: 0 0 300px; }
  .scroll-btn { display: none; }
  .reviews__stats { gap: 18px; padding: 14px 22px; flex-wrap: wrap; justify-content: center; }
}

@media (max-width: 600px) {
  .reviews { padding: 48px 16px; }
  .reviews__title { font-size: 24px; }
  .review { flex: 0 0 82vw; padding: 24px; }
  .reviews__stats { gap: 14px; }
  .stat-divider { display: none; }
}
</style>