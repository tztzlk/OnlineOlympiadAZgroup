<template>
  <section class="hiw" id="how-it-works" aria-labelledby="hiw-title">
    <div class="hiw__inner">
      <header class="hiw__head">
        <span class="ds-eyebrow" v-reveal>Как это работает</span>
        <h2 id="hiw-title" class="hiw__title" v-title>Три шага до результата</h2>
        <p class="hiw__sub" v-reveal>Простой процесс — от регистрации до сертификата.</p>
      </header>

      <ol class="hiw__bento">
        <li
          v-for="(step, index) in steps"
          :key="step.id"
          v-reveal="index"
          :class="['step', `step--${step.id}`, 'ds-tile', `ds-tile--${step.tone}`]"
        >
          <div class="step__text">
            <span class="step__num">{{ step.id }} шаг</span>
            <h3 class="step__title">{{ step.title }}</h3>
            <p class="step__desc">{{ step.text }}</p>
            <ul class="step__tags">
              <li v-for="tag in step.tags" :key="tag" class="tag">{{ tag }}</li>
            </ul>
          </div>
          <img
            class="step__art ds-character ds-anim-sway"
            :src="step.image"
            :width="step.width"
            :height="step.height"
            alt=""
            loading="lazy"
            decoding="async"
          >
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
    tone: 'blue',
    image: '/images/characters/girl-wave.webp',
    width: 333,
    height: 760,
  },
  {
    id: 2,
    title: 'Выполните задания',
    text: 'Удобный интерфейс, таймер с обратным отсчётом, система прокторинга. Задания составлены согласно вашей возрастной категории.',
    tags: ['Таймер', 'Прокторинг', 'Онлайн'],
    tone: 'sun',
    image: '/images/characters/boy-laptop.webp',
    width: 586,
    height: 760,
  },
  {
    id: 3,
    title: 'Получите результат',
    text: 'Мгновенный результат после завершения теста, сертификат участника и подробный разбор ошибок — всё онлайн.',
    tags: ['Сертификат', 'Результат', 'Разбор ошибок'],
    tone: 'coral',
    image: '/images/characters/girl-certificate.webp',
    width: 334,
    height: 760,
  },
]
</script>

<style scoped>
.hiw {
  padding: clamp(64px, 9vw, 112px) 24px clamp(72px, 10vw, 120px);
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
  grid-template-columns: 1.1fr 1fr;
  grid-template-rows: auto auto;
  gap: 18px;
  margin: 0 0 44px;
  padding: 0;
  list-style: none;
}

.step {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  align-items: end;
  gap: 8px;
  min-height: 250px;
  padding-bottom: 0;
  transition: transform var(--dur-slow) var(--ease-out), opacity 560ms var(--ease-out);
}

@media (hover: hover) and (pointer: fine) {
  .step:hover { transform: translateY(-4px); }

}

/* Крупный шаг: текст сверху во всю ширину, персонаж стоит внизу справа */
.step--1 {
  grid-row: 1 / 3;
  grid-template-columns: minmax(0, 1fr);
}

.step--1 .step__art { justify-self: end; }

.step__text {
  align-self: start;
  display: grid;
  justify-items: start;
  gap: 12px;
  padding-bottom: 28px;
}

.step__num {
  padding: 5px 12px;
  border-radius: var(--radius-pill);
  background: rgba(255, 255, 255, 0.7);
  font-size: 13px;
  font-weight: 700;
  white-space: nowrap;
}

.dark .step__num { background: rgba(255, 255, 255, 0.12); }

.step__title {
  font-size: 22px;
  letter-spacing: -0.02em;
}

.step--1 .step__title {
  margin-top: 12px;
  font-family: var(--font-display);
  font-size: clamp(26px, 2.8vw, 36px);
}

.step__desc {
  max-width: 40ch;
  opacity: 0.8;
  font-size: 15.5px;
  line-height: 1.6;
}

.step--1 .step__desc { font-size: 17px; }

.step__tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin: 4px 0 0;
  padding: 0;
  list-style: none;
}

.tag {
  padding: 5px 11px;
  border-radius: var(--radius-pill);
  background: rgba(255, 255, 255, 0.6);
  font-size: 13px;
  font-weight: 600;
}

.dark .tag { background: rgba(255, 255, 255, 0.1); }

/* Персонаж стоит на нижнем краю плитки */
.step__art {
  align-self: end;
  width: auto;
  height: 230px;
  margin-right: -8px;
  transition: transform 500ms cubic-bezier(0.2, 0.9, 0.25, 1.2);
}

.step--1 .step__art {
  height: 340px;
  margin-top: -110px;
}

.step--1 .step__text { padding-bottom: 0; }
.step--1 .step__tags { max-width: 60%; }

.step--2 .step__art { height: 210px; animation-duration: 4.2s; animation-delay: -1.4s; }
.step--3 .step__art { animation-duration: 5.6s; animation-delay: -2.6s; }

.hiw__cta { display: flex; }

.hiw__btn svg { transition: transform var(--dur) var(--ease-out); }

@media (hover: hover) and (pointer: fine) {
  .hiw__btn:hover svg { transform: translateX(4px); }
}

@media (max-width: 900px) {
  .hiw__bento { grid-template-columns: 1fr; }
  .step--1 { grid-row: auto; }
  .step--1 .step__art { height: 260px; margin-top: -40px; }
  .step--1 .step__tags { max-width: none; }
}

@media (max-width: 560px) {
  .hiw { padding-left: 16px; padding-right: 16px; }
  .step,
  .step--1 { grid-template-columns: 1fr; min-height: 0; }
  .step__text { padding-bottom: 0; }
  .step__art,
  .step--1 .step__art,
  .step--2 .step__art { justify-self: end; height: 180px; margin: -24px -6px 0 0; }
  .hiw__btn { width: 100%; }
}
</style>
