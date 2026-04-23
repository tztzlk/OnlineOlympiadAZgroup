<template>
  <section class="hiw" id="how-it-works">
    <div class="hiw__inner">
      <!-- Section header -->
      <div class="hiw__head">
        <span class="hiw__eyebrow">Как это работает</span>
        <h2 class="hiw__title">Три шага до результата</h2>
        <p class="hiw__sub">Простой процесс — от регистрации до сертификата.</p>
      </div>

      <!-- Steps grid -->
      <div class="hiw__grid">
        <article v-for="step in steps" :key="step.id" class="step-card">
          <div class="step-card__num">{{ String(step.id).padStart(2, '0') }}</div>

          <div class="step-card__icon-wrap">
            <!-- static SVG bound once — no user/API data -->
            <div class="step-card__icon" v-html="step.icon"></div>
          </div>

          <div class="step-card__body">
            <h3 class="step-card__title">{{ step.title }}</h3>
            <p class="step-card__text">{{ step.text }}</p>
          </div>

          <div class="step-card__tags">
            <span v-for="tag in step.tags" :key="tag" class="tag">{{ tag }}</span>
          </div>
        </article>
      </div>

      <!-- Bottom CTA -->
      <div class="hiw__cta">
        <router-link to="/register" class="hiw__btn">
          Зарегистрироваться бесплатно
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
          </svg>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const sectionRef = ref(null)
const isVisible = ref(false)

onMounted(() => {
  const observer = new IntersectionObserver(
    ([entry]) => { if (entry.isIntersecting) { isVisible.value = true; observer.disconnect() } },
    { threshold: 0.1, rootMargin: '0px 0px -60px 0px' }
  )
  if (sectionRef.value) observer.observe(sectionRef.value)
})

const steps = [
  {
    id: 1,
    title: 'Зарегистрируйся',
    text: 'Создай аккаунт за 2 минуты. Выбери предмет и подходящую олимпиаду.',
    tags: ['Бесплатно', 'Онлайн', '2 мин'],
    icon: `
      <svg viewBox="0 0 56 56" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="28" cy="20" r="9"/>
        <path d="M10 46c0-9.94 8.06-18 18-18s18 8.06 18 18"/>
        <path d="M36 14l3 3-3 3"/>
      </svg>
    `,
  },
  {
    id: 2,
    title: 'Выполни задания',
    text: 'Отвечай на вопросы в удобном интерфейсе с таймером. Без лишних шагов.',
    tags: ['Тест', 'Таймер', 'Онлайн'],
    icon: `
      <svg viewBox="0 0 56 56" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="28" cy="28" r="20"/>
        <path d="M28 16v14l8 5"/>
      </svg>
    `,
  },
  {
    id: 3,
    title: 'Получи результат',
    text: 'Сразу после теста — итог, сертификат и разбор ошибок. Всё онлайн.',
    tags: ['Сертификат', 'Сразу', 'Разбор'],
    icon: `
      <svg viewBox="0 0 56 56" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 14h28v20H14z"/>
        <path d="M20 28l6 6 12-14"/>
        <path d="M20 40h16"/>
        <path d="M28 34v8"/>
      </svg>
    `,
  },
]
</script>

<style scoped>
* { box-sizing: border-box; }

.hiw {
  background: var(--bg-alt);
  padding: 96px 28px;
}

.hiw__inner {
  max-width: 1100px;
  margin: 0 auto;
}

/* ---- Section header ---- */
.hiw__head {
  text-align: center;
  margin-bottom: 56px;
}

.hiw__eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 16px;
  border-radius: 999px;
  background: var(--accent-soft);
  border: 1.5px solid rgba(245, 200, 66, 0.35);
  color: #92660a;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 20px;
}

.hiw__title {
  font-size: clamp(32px, 4vw, 48px);
  font-weight: 800;
  color: var(--text);
  letter-spacing: -0.025em;
  margin-bottom: 12px;
}

.hiw__sub {
  font-size: 17px;
  color: var(--text-secondary);
  max-width: 440px;
  margin: 0 auto;
  line-height: 1.65;
}

/* ---- Steps grid ---- */
.hiw__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-bottom: 56px;
}

/* ---- Step card ---- */
.how-it-works__grid .step-card {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0s, transform 0s;
}

.how-it-works__grid.visible .step-card {
  opacity: 1;
  transform: translateY(0);
  transition: opacity 0.5s cubic-bezier(0.23, 1, 0.32, 1), transform 0.5s cubic-bezier(0.23, 1, 0.32, 1);
  transition-delay: calc(var(--i) * 80ms);
}

.step-card {
  position: relative;
  background: var(--card);
  border: 1.5px solid var(--border);
  border-radius: 24px;
  padding: 32px 28px;
  box-shadow: var(--shadow-card);
  transition: transform 0.22s ease, box-shadow 0.22s ease;
  overflow: hidden;
  min-height: 360px;
  padding: 22px;
  border-radius: 30px;
  border: 1px solid rgba(117, 93, 41, 0.12);
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.56), rgba(255, 248, 236, 0.82)),
    radial-gradient(circle at top right, rgba(208, 179, 107, 0.16), transparent 35%);
  box-shadow: 0 22px 52px rgba(77, 61, 24, 0.08);
  backdrop-filter: blur(12px);
  transition: transform 0.22s cubic-bezier(0.23, 1, 0.32, 1), box-shadow 0.22s cubic-bezier(0.23, 1, 0.32, 1);
}

@media (hover: hover) and (pointer: fine) {
  .step-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 32px 64px rgba(77, 61, 24, 0.13);
  }
}
.step-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  border-radius: 24px 24px 0 0;
  background: linear-gradient(90deg, var(--accent), var(--green));
  opacity: 0;
  transition: opacity 0.22s ease;
}
.step-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.12);
}
.step-card:hover::before { opacity: 1; }

.step-card__num {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.14em;
  color: var(--text-secondary);
  margin-bottom: 20px;
  text-transform: uppercase;
}

.step-card__icon-wrap {
  margin-bottom: 24px;
}

.step-card__icon {
  width: 64px;
  height: 64px;
  border-radius: 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--accent-soft);
  color: #92660a;
  border: 1.5px solid rgba(245, 200, 66, 0.3);
}
.step-card__icon :deep(svg) {
  width: 32px;
  height: 32px;
}

.step-card__body {
  margin-bottom: 20px;
}
.step-card__title {
  font-size: 22px;
  font-weight: 700;
  color: var(--text);
  letter-spacing: -0.02em;
  margin-bottom: 10px;
  line-height: 1.2;
}
.step-card__text {
  font-size: 14px;
  line-height: 1.7;
  color: var(--text-secondary);
}

/* ---- Tags ---- */
.step-card__tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
.tag {
  padding: 6px 12px;
  border-radius: 999px;
  background: var(--bg-alt);
  border: 1.5px solid var(--border);
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
}

/* ---- Bottom CTA ---- */
.hiw__cta {
  text-align: center;
}

.hiw__btn {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 16px 32px;
  border-radius: 14px;
  background: var(--green);
  color: #ffffff;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.28);
  transition: all 0.22s ease;
}
.hiw__btn:hover {
  background: var(--green-hover);
  transform: translateY(-2px);
  box-shadow: 0 14px 32px rgba(22, 163, 74, 0.36);
}
.hiw__btn svg { transition: transform 0.22s; }
.hiw__btn:hover svg { transform: translateX(4px); }

/* ---- Responsive ---- */
@media (max-width: 900px) {
  .hiw { padding: 72px 20px; }
  .hiw__grid { grid-template-columns: 1fr; gap: 16px; }
  .step-card { padding: 28px 24px; }
  .hiw__head { margin-bottom: 40px; }
}

@media (max-width: 540px) {
  .hiw { padding: 56px 16px; }
  .hiw__btn { width: 100%; justify-content: center; }
}
</style>
