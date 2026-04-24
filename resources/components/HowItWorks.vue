<template>
  <section class="hiw" id="how-it-works" ref="sectionRef">
    <div class="hiw__inner">
      <div class="hiw__head">
        <span class="hiw__eyebrow">Как это работает</span>
        <h2 class="hiw__title">Три шага до результата</h2>
        <p class="hiw__sub">Простой процесс — от регистрации до сертификата.</p>
      </div>

      <div class="hiw__bento" :class="{ visible: isVisible }">
        <article
          v-for="(step, index) in steps"
          :key="step.id"
          :class="['step-card', `step-card--${step.id}`]"
          :style="`--i: ${index}`"
        >
          <div class="step-card__num">{{ String(step.id).padStart(2, '0') }}</div>
          <div class="step-card__icon-wrap">
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

      <div class="hiw__cta">
        <router-link to="/register" class="hiw__btn">
          Зарегистрироваться бесплатно
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
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
    ([entry]) => {
      if (entry.isIntersecting) {
        isVisible.value = true
        observer.disconnect()
      }
    },
    { threshold: 0.08, rootMargin: '0px 0px -48px 0px' }
  )
  if (sectionRef.value) observer.observe(sectionRef.value)
})

const steps = [
  {
    id: 1,
    title: 'Зарегистрируйся',
    text: 'Создай аккаунт за 2 минуты. Выбери предмет и подходящую олимпиаду для своего ребёнка.',
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

.hiw__head {
  text-align: center;
  margin-bottom: 52px;
}

.hiw__eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 15px;
  border-radius: 999px;
  background: var(--accent-soft);
  border: 1.5px solid rgba(245, 200, 66, 0.35);
  color: #92660a;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.09em;
  text-transform: uppercase;
  margin-bottom: 20px;
}

.hiw__title {
  font-size: clamp(30px, 4vw, 46px);
  font-weight: 800;
  color: var(--text);
  letter-spacing: -0.03em;
  margin-bottom: 12px;
}

.hiw__sub {
  font-size: 16px;
  color: var(--text-secondary);
  max-width: 400px;
  margin: 0 auto;
  line-height: 1.65;
}

/* Asymmetric bento grid: step 1 tall on left, steps 2+3 stacked right */
.hiw__bento {
  display: grid;
  grid-template-columns: 3fr 2fr;
  grid-template-rows: 1fr 1fr;
  gap: 18px;
  margin-bottom: 52px;
  min-height: 460px;
}

.step-card {
  position: relative;
  border-radius: 28px;
  padding: 30px 28px;
  border: 1px solid var(--surface-border);
  background: var(--card);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  gap: 0;
  transition: transform 0.22s cubic-bezier(0.23, 1, 0.32, 1),
              box-shadow 0.22s cubic-bezier(0.23, 1, 0.32, 1);
  /* scroll-reveal: start hidden */
  opacity: 0;
  transform: translateY(22px);
  transition: opacity 0.55s cubic-bezier(0.23, 1, 0.32, 1) calc(var(--i, 0) * 110ms),
              transform 0.55s cubic-bezier(0.23, 1, 0.32, 1) calc(var(--i, 0) * 110ms);
}

/* Once visible class applied, reveal */
.hiw__bento.visible .step-card {
  opacity: 1;
  transform: translateY(0);
  /* restore hover transitions after reveal */
  transition: opacity 0.55s cubic-bezier(0.23, 1, 0.32, 1) calc(var(--i, 0) * 110ms),
              transform 0.55s cubic-bezier(0.23, 1, 0.32, 1) calc(var(--i, 0) * 110ms),
              box-shadow 0.22s ease;
}

/* Step 1: spans both rows, more spacious */
.step-card--1 {
  grid-row: 1 / 3;
  background: color-mix(in srgb, var(--accent-soft) 34%, #ffffff);
  border-color: color-mix(in srgb, var(--accent) 22%, var(--border));
  padding: 36px 32px;
  justify-content: space-between;
}

.step-card--2 {
  background: color-mix(in srgb, var(--green-soft) 42%, #ffffff);
  border-color: color-mix(in srgb, var(--green) 20%, var(--border));
}

.step-card--3 {
  background: color-mix(in srgb, #eef4ff 58%, #ffffff);
  border-color: color-mix(in srgb, #9fb8de 28%, var(--border));
}

/* Top accent line on hover */
.step-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  border-radius: 28px 28px 0 0;
  background: var(--accent);
  opacity: 0;
  transition: opacity 0.22s ease;
}
.step-card--2::before { background: var(--green); }
.step-card--3::before { background: #8aa8cf; }

@media (hover: hover) and (pointer: fine) {
  .hiw__bento.visible .step-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.09);
    transition: transform 0.22s cubic-bezier(0.23, 1, 0.32, 1),
                box-shadow 0.22s cubic-bezier(0.23, 1, 0.32, 1);
  }
  .step-card:hover::before { opacity: 1; }
}

.step-card__num {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.14em;
  color: var(--text-secondary);
  margin-bottom: 20px;
  text-transform: uppercase;
}

.step-card--1 .step-card__num {
  font-size: 48px;
  font-weight: 900;
  color: rgba(146, 102, 10, 0.12);
  letter-spacing: -0.04em;
  margin-bottom: 0;
  position: absolute;
  top: 20px;
  right: 28px;
  line-height: 1;
}

.step-card__icon-wrap {
  margin-bottom: 20px;
}

.step-card__icon {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--accent-soft);
  color: #92660a;
  border: 1.5px solid rgba(245, 200, 66, 0.3);
}

.step-card--1 .step-card__icon {
  width: 72px;
  height: 72px;
  border-radius: 20px;
  margin-bottom: 24px;
}

.step-card--2 .step-card__icon {
  background: color-mix(in srgb, var(--green-soft) 90%, #ffffff);
  color: var(--green-strong);
  border-color: color-mix(in srgb, var(--green) 28%, transparent);
}

.step-card--3 .step-card__icon {
  background: color-mix(in srgb, #e8f0fb 92%, #ffffff);
  color: #6f89ad;
  border-color: color-mix(in srgb, #9fb8de 36%, transparent);
}

.step-card__icon :deep(svg) { width: 30px; height: 30px; }
.step-card--1 .step-card__icon :deep(svg) { width: 36px; height: 36px; }

.step-card__body { margin-bottom: 18px; flex: 1; }

.step-card__title {
  font-size: 20px;
  font-weight: 700;
  color: var(--text);
  letter-spacing: -0.02em;
  margin-bottom: 9px;
  line-height: 1.2;
}

.step-card--1 .step-card__title {
  font-size: 26px;
  margin-bottom: 12px;
}

.step-card__text {
  font-size: 14px;
  line-height: 1.7;
  color: var(--text-secondary);
}

.step-card--1 .step-card__text {
  font-size: 15px;
}

.step-card__tags {
  display: flex;
  flex-wrap: wrap;
  gap: 7px;
}

.tag {
  padding: 5px 11px;
  border-radius: 999px;
  background: var(--bg-alt);
  border: 1.5px solid var(--border);
  font-size: 11px;
  font-weight: 600;
  color: var(--text-secondary);
}

.step-card--1 .tag {
  background: color-mix(in srgb, var(--accent-soft) 55%, #ffffff);
  border-color: color-mix(in srgb, var(--accent) 18%, var(--border));
}
.step-card--2 .tag {
  background: color-mix(in srgb, var(--green-soft) 68%, #ffffff);
  border-color: color-mix(in srgb, var(--green) 16%, var(--border));
}
.step-card--3 .tag {
  background: color-mix(in srgb, #eef4ff 76%, #ffffff);
  border-color: color-mix(in srgb, #9fb8de 18%, var(--border));
}

.hiw__cta { text-align: center; }

.hiw__btn {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 15px 30px;
  border-radius: 14px;
  background: var(--green);
  color: #ffffff;
  font-size: 15px;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.28);
  transition: background 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
}
.hiw__btn:hover {
  background: var(--green-hover);
  transform: translateY(-2px);
  box-shadow: 0 14px 30px rgba(22, 163, 74, 0.32);
}
.hiw__btn:active { transform: scale(0.98); transition-duration: 0.08s; }
.hiw__btn svg { transition: transform 0.22s cubic-bezier(0.23, 1, 0.32, 1); }
.hiw__btn:hover svg { transform: translateX(4px); }

@media (max-width: 900px) {
  .hiw { padding: 72px 20px; }
  .hiw__bento {
    grid-template-columns: 1fr;
    grid-template-rows: auto;
    min-height: auto;
  }
  .step-card--1 { grid-row: auto; }
  .step-card--1 .step-card__num { font-size: 32px; }
  .step-card--1 .step-card__title { font-size: 22px; }
  .hiw__head { margin-bottom: 36px; }
}

@media (max-width: 540px) {
  .hiw { padding: 56px 16px; }
  .step-card { padding: 24px 20px; }
  .step-card--1 { padding: 28px 22px; }
  .hiw__btn { width: 100%; justify-content: center; }
}
</style>
