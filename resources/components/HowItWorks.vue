<template>
  <section class="how-it-works">
    <div class="how-it-works__heading">
      <span class="how-it-works__eyebrow">Как всё работает</span>
      <h2>От выбора предмета до результата и разбора ошибок</h2>
      <p>Четыре понятных шага, чтобы родитель и ребёнок сразу увидели, как проходит участие.</p>
    </div>

    <div class="how-it-works__timeline" aria-hidden="true">
      <span></span>
      <span></span>
      <span></span>
    </div>

    <div class="how-it-works__grid">
      <article v-for="step in steps" :key="step.id" class="step-card" :class="`step-card--${step.id}`">
        <div class="step-card__top">
          <span class="step-card__number">0{{ step.id }}</span>
          <span class="step-card__chip">{{ step.chip }}</span>
        </div>

        <div class="step-card__icon" v-html="step.icon"></div>

        <div class="step-card__copy">
          <h3>{{ step.title }}</h3>
          <p>{{ step.text }}</p>
        </div>

        <div class="step-card__tags">
          <span v-for="tag in step.tags" :key="tag">{{ tag }}</span>
        </div>

        <div class="step-card__mini" v-if="step.id === 1">
          <span>Математика</span>
          <span>Русский язык</span>
          <span>Английский</span>
        </div>

        <div class="step-card__mini step-card__mini--payment" v-else-if="step.id === 2">
          <div><strong>Профиль</strong><span>ребёнка</span></div>
          <div><strong>Оплата</strong><span>Kaspi</span></div>
        </div>

        <div class="step-card__mini step-card__mini--quiz" v-else-if="step.id === 3">
          <div class="mini-progress">
            <span>Прогресс</span>
            <strong>8 / 10</strong>
          </div>
          <div class="mini-timer">
            <span>Таймер</span>
            <strong>12:40</strong>
          </div>
        </div>

        <div class="step-card__mini step-card__mini--result" v-else>
          <div class="result-pill success">Результат</div>
          <div class="result-pill">Сертификат</div>
          <div class="result-pill accent">Работа над ошибками</div>
        </div>
      </article>
    </div>
  </section>
</template>

<script setup>
const steps = [
  {
    id: 1,
    chip: 'Онлайн',
    title: 'Выберите олимпиаду',
    text: 'Ребёнок выбирает предмет и сразу понимает, что будет дальше.',
    tags: ['Предмет', 'Класс', 'Формат'],
    icon: `
      <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <rect x="10" y="10" width="44" height="44" rx="14"></rect>
        <path d="M22 25h20"></path>
        <path d="M22 33h12"></path>
        <path d="M22 41h16"></path>
        <circle cx="45" cy="40" r="5"></circle>
      </svg>
    `,
  },
  {
    id: 2,
    chip: 'Быстро',
    title: 'Заполните данные и оплатите',
    text: 'Профиль ребёнка и оплата оформляются без лишних шагов и звонков.',
    tags: ['Kaspi', 'Профиль', 'Подтверждение'],
    icon: `
      <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <rect x="8" y="16" width="48" height="32" rx="10"></rect>
        <path d="M8 26h48"></path>
        <path d="M18 38h10"></path>
        <path d="M40 38h6"></path>
      </svg>
    `,
  },
  {
    id: 3,
    chip: 'Тест',
    title: 'Пройдите олимпиаду',
    text: 'Участник отвечает на вопросы в удобном интерфейсе с таймером и навигацией.',
    tags: ['Вопросы', 'Таймер', 'Навигация'],
    icon: `
      <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="32" cy="32" r="22"></circle>
        <path d="M32 20v14l9 5"></path>
      </svg>
    `,
  },
  {
    id: 4,
    chip: 'После теста',
    title: 'Получите результат и разбор',
    text: 'Сразу после завершения доступны итог, сертификат и работа над ошибками.',
    tags: ['Сразу после теста', 'Сертификат', 'Разбор'],
    icon: `
      <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 18h28v28H18z"></path>
        <path d="M24 32l6 6 12-14"></path>
        <path d="M46 46l6 6"></path>
      </svg>
    `,
  },
]
</script>

<style scoped>
* { box-sizing: border-box; }

.how-it-works {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
  padding: 34px 28px 36px;
}

.how-it-works__heading {
  max-width: 760px;
  margin: 0 auto 26px;
  text-align: center;
}

.how-it-works__eyebrow {
  display: inline-flex;
  align-items: center;
  padding: 8px 14px;
  border-radius: 999px;
  border: 1px solid rgba(208, 179, 107, 0.2);
  background: rgba(255, 250, 240, 0.7);
  color: #755d29;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  backdrop-filter: blur(8px);
}

.how-it-works__heading h2 {
  margin: 16px 0 12px;
  font-size: clamp(30px, 4.5vw, 50px);
  line-height: 1.02;
  color: var(--text-primary);
}

.how-it-works__heading p {
  margin: 0;
  font-size: 17px;
  line-height: 1.7;
  color: var(--text-secondary);
}

.how-it-works__timeline {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
  max-width: 920px;
  margin: 0 auto 18px;
  opacity: 0.48;
}

.how-it-works__timeline span {
  height: 2px;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(73, 168, 107, 0.4), rgba(208, 179, 107, 0.75));
}

.how-it-works__grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;
}

.step-card {
  position: relative;
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
}

.step-card::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  pointer-events: none;
  background: linear-gradient(140deg, rgba(255, 255, 255, 0.2), transparent 45%);
}

.step-card--4 {
  background:
    linear-gradient(180deg, rgba(255, 255, 255, 0.56), rgba(247, 252, 245, 0.88)),
    radial-gradient(circle at top right, rgba(73, 168, 107, 0.18), transparent 36%);
}

.step-card__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 18px;
}

.step-card__number {
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.12em;
  color: #7b6332;
}

.step-card__chip {
  padding: 7px 10px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.54);
  border: 1px solid rgba(117, 93, 41, 0.1);
  font-size: 11px;
  font-weight: 700;
  color: var(--text-secondary);
}

.step-card__icon {
  width: 72px;
  height: 72px;
  border-radius: 22px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(208, 179, 107, 0.2), rgba(255, 255, 255, 0.58));
  color: #6b5a2e;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
}

.step-card__icon :deep(svg) {
  width: 36px;
  height: 36px;
}

.step-card__copy {
  margin-top: 18px;
}

.step-card__copy h3 {
  margin: 0 0 10px;
  font-size: 24px;
  line-height: 1.08;
  color: var(--text-primary);
}

.step-card__copy p {
  margin: 0;
  font-size: 14px;
  line-height: 1.7;
  color: var(--text-secondary);
}

.step-card__tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 18px;
}

.step-card__tags span,
.step-card__mini span,
.result-pill {
  padding: 7px 10px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.62);
  border: 1px solid rgba(117, 93, 41, 0.1);
  font-size: 11px;
  font-weight: 700;
  color: var(--text-secondary);
}

.step-card__mini {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 18px;
}

.step-card__mini--payment,
.step-card__mini--quiz {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.step-card__mini--payment div,
.step-card__mini--quiz > div {
  padding: 12px 14px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.54);
  border: 1px solid rgba(117, 93, 41, 0.08);
}

.step-card__mini--payment strong,
.step-card__mini--quiz strong {
  display: block;
  font-size: 15px;
  color: var(--text-primary);
}

.step-card__mini--payment span,
.step-card__mini--quiz span {
  display: block;
  padding: 0;
  margin-top: 4px;
  border: 0;
  background: transparent;
  color: var(--text-secondary);
  font-size: 12px;
}

.result-pill.success {
  color: #2f7a51;
}

.result-pill.accent {
  background: rgba(73, 168, 107, 0.12);
  color: #2f7a51;
}

@media (max-width: 1100px) {
  .how-it-works__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 720px) {
  .how-it-works {
    padding: 26px 20px 22px;
  }

  .how-it-works__timeline {
    display: none;
  }

  .how-it-works__grid {
    grid-template-columns: 1fr;
  }

  .step-card {
    min-height: auto;
  }
}
</style>
