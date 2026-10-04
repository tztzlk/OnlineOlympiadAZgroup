<template>
  <section class="hiw" id="how-it-works" aria-labelledby="hiw-title">
    <div class="hiw__inner">
      <header class="hiw__head" v-reveal>
        <span class="ds-eyebrow">Как это работает</span>
        <h2 id="hiw-title" class="hiw__title">Три шага до результата</h2>
        <p class="hiw__sub">Простой процесс — от регистрации до сертификата.</p>
      </header>

      <ol class="hiw__bento">
        <li
          v-for="(step, index) in steps"
          :key="step.id"
          v-reveal="index"
          :class="['step-card', `step-card--${step.id}`]"
        >
          <div class="step-card__top">
            <div class="step-card__icon" aria-hidden="true" v-html="step.icon"></div>
            <span class="step-card__num">{{ step.id }} шаг</span>
          </div>
          <div class="step-card__body">
            <h3 class="step-card__title">{{ step.title }}</h3>
            <p class="step-card__text">{{ step.text }}</p>
          </div>
          <ul class="step-card__tags">
            <li v-for="tag in step.tags" :key="tag" class="tag">{{ tag }}</li>
          </ul>
        </li>
      </ol>

      <div class="hiw__cta" v-reveal>
        <router-link to="/register" class="ds-btn ds-btn-primary ds-btn-lg hiw__btn">
          Пройти регистрацию
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6"/>
          </svg>
        </router-link>
      </div>
    </div>
  </section>
</template>

<script setup>
const steps = [
  {
    id: 1,
    title: 'Зарегистрируйтесь',
    text: 'Создайте аккаунт за 2 минуты. Выберите удобное для вас время прохождения олимпиады и подходящий предмет для вашего ребёнка.',
    tags: ['Бесплатно', 'Онлайн', 'Мгновенный результат'],
    icon: `
      <svg viewBox="0 0 56 56" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="28" cy="20" r="9"/>
        <path d="M10 46c0-9.94 8.06-18 18-18s18 8.06 18 18"/>
        <path d="M36 14l3 3-3 3"/>
      </svg>
    `,
  },
  {
    id: 2,
    title: 'Выполните задания',
    text: 'Удобный интерфейс, таймер с обратным отсчётом, система прокторинга. Задания составлены согласно вашей возрастной категории.',
    tags: ['Таймер', 'Прокторинг', 'Онлайн'],
    icon: `
      <svg viewBox="0 0 56 56" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="28" cy="28" r="20"/>
        <path d="M28 16v14l8 5"/>
      </svg>
    `,
  },
  {
    id: 3,
    title: 'Получите результат',
    text: 'Мгновенный результат после завершения теста, сертификат участника и подробный разбор ошибок — всё онлайн.',
    tags: ['Сертификат', 'Результат', 'Разбор ошибок'],
    icon: `
      <svg viewBox="0 0 56 56" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
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
.hiw {
  padding: clamp(64px, 9vw, 112px) 24px clamp(72px, 10vw, 120px);
  background: var(--card);
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
}

.hiw__inner {
  max-width: 1200px;
  margin: 0 auto;
}

.hiw__head {
  display: grid;
  justify-items: start;
  gap: 14px;
  max-width: 640px;
  margin-bottom: 44px;
}

.hiw__title { letter-spacing: -0.03em; }

.hiw__sub {
  color: var(--text-secondary);
  font-size: 18px;
}

/* Асимметричная сетка: первый шаг крупный слева, второй и третий справа */
.hiw__bento {
  display: grid;
  grid-template-columns: 1.25fr 1fr;
  grid-template-rows: auto auto;
  gap: 18px;
  margin: 0 0 44px;
  padding: 0;
  list-style: none;
}

.step-card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 18px;
  padding: 28px;
  border-radius: var(--radius-xl);
  background: var(--bg);
  border: 1px solid var(--border);
  overflow: hidden;
  transition: transform var(--dur-slow) var(--ease-out), box-shadow var(--dur-slow) ease, opacity 560ms var(--ease-out);
}

@media (hover: hover) and (pointer: fine) {
  .step-card.is-revealed:hover,
  .step-card:not([data-reveal]):hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
  }
}

.step-card--1 {
  grid-row: 1 / 3;
  padding: 36px;
  justify-content: space-between;
  background:
    radial-gradient(420px 300px at 100% 0%, rgba(255, 255, 255, 0.16), transparent 70%),
    linear-gradient(160deg, #3d6cff 0%, #2b5bf5 50%, #2046d4 100%);
  border-color: transparent;
  color: #ffffff;
  box-shadow: 0 24px 50px rgba(43, 91, 245, 0.28);
}

.step-card__top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.step-card__icon {
  width: 60px;
  height: 60px;
  display: grid;
  place-items: center;
  border-radius: 18px;
  flex-shrink: 0;
}

.step-card__icon :deep(svg) { width: 30px; height: 30px; }

.step-card--1 .step-card__icon {
  width: 76px;
  height: 76px;
  border-radius: 22px;
  background: var(--sun);
  color: #1f1600;
  transform: rotate(-5deg);
}
.step-card--1 .step-card__icon :deep(svg) { width: 38px; height: 38px; }

.step-card--2 .step-card__icon { background: var(--sun-soft); color: #c98a00; }
.step-card--3 .step-card__icon { background: var(--tone-violet-soft); color: var(--tone-violet); }

.step-card__num {
  padding: 5px 12px;
  border-radius: var(--radius-pill);
  background: var(--card);
  border: 1px solid var(--border);
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 600;
  white-space: nowrap;
}

.step-card--1 .step-card__num {
  background: rgba(255, 255, 255, 0.16);
  border-color: rgba(255, 255, 255, 0.24);
  color: #ffffff;
}

.step-card__body { flex: 1; }

.step-card__title {
  margin-bottom: 8px;
  font-size: 21px;
  letter-spacing: -0.02em;
}

.step-card--1 .step-card__title {
  font-family: var(--font-display);
  font-size: clamp(24px, 2.6vw, 32px);
  margin: 24px 0 12px;
}

.step-card__text {
  max-width: 46ch;
  color: var(--text-secondary);
  font-size: 15.5px;
  line-height: 1.65;
}

.step-card--1 .step-card__text {
  color: rgba(255, 255, 255, 0.86);
  font-size: 17px;
}

.step-card__tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 0;
  padding: 0;
  list-style: none;
}

.tag {
  padding: 5px 11px;
  border-radius: var(--radius-xs);
  background: var(--card);
  border: 1px solid var(--border);
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 600;
}

.step-card--1 .tag {
  background: rgba(255, 255, 255, 0.14);
  border-color: transparent;
  color: #ffffff;
}

.hiw__cta { display: flex; }

.hiw__btn svg { transition: transform var(--dur) var(--ease-out); }

@media (hover: hover) and (pointer: fine) {
  .hiw__btn:hover svg { transform: translateX(4px); }
}

@media (max-width: 900px) {
  .hiw__bento { grid-template-columns: 1fr; }
  .step-card--1 { grid-row: auto; padding: 28px; }
}

@media (max-width: 560px) {
  .hiw { padding-left: 16px; padding-right: 16px; }
  .step-card { padding: 22px; border-radius: var(--radius-lg); }
  .step-card--1 { padding: 24px; }
  .hiw__btn { width: 100%; }
}
</style>
