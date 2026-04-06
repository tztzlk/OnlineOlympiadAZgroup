<script setup>
const promiseChips = [
  'Для родителей и учеников',
  'Онлайн-участие без бумажной регистрации',
  'Результат и сертификат сразу после прохождения',
]

const messageCards = [
  {
    label: 'Что это',
    title: 'Современная онлайн-олимпиада',
    text: 'Пользователь сразу понимает формат: выбрать предмет, пройти олимпиаду и получить прозрачный итог.',
  },
  {
    label: 'Для кого',
    title: 'Для ученика и родителя',
    text: 'Интерфейс объясняет путь обеим аудиториям без лишних экранов и сложных терминов.',
  },
  {
    label: 'Какая выгода',
    title: 'Быстрый понятный результат',
    text: 'Ценность считывается за несколько секунд: участие онлайн, ясные правила и короткий путь до результата.',
  },
  {
    label: 'Куда нажать',
    title: 'Один главный сценарий',
    text: 'Доминирующий CTA ведет к выбору олимпиады и не спорит с вторичными действиями.',
  },
]

const steps = [
  { label: '1', title: 'Выберите предмет', text: 'Пользователь сразу видит доступные олимпиады и не тратит время на лишние переходы.' },
  { label: '2', title: 'Пройдите онлайн', text: 'Интерфейс ведет по шагам и удерживает внимание на самом участии, а не на форме.' },
  { label: '3', title: 'Получите результат', text: 'После завершения участник сразу понимает итог и следующий полезный шаг.' },
]

const stats = [
  { value: '1 200+', label: 'участников уже выбрали платформу' },
  { value: '4.9/5', label: 'средняя оценка по отзывам' },
  { value: 'до 3 мин', label: 'чтобы начать участие' },
]
</script>

<template>
  <section class="hero">
    <div class="hero__bg"></div>
    <div class="hero__overlay"></div>
    <div class="hero__glow hero__glow--one"></div>
    <div class="hero__glow hero__glow--two"></div>

    <div class="hero__shell">
      <div class="hero__content">
        <div class="hero__eyebrow">Attention • Interest • Desire • Action</div>

        <h1 class="hero__title">
          Олимпиада, где
          <span class="hero__title-accent">решение принять легко</span>
        </h1>

        <p class="hero__subheadline">
          На первом экране пользователь сразу видит, что это за платформа, кому она подходит,
          какую пользу дает и куда нужно нажать, чтобы быстро перейти к участию.
        </p>

        <div class="hero__actions">
          <router-link to="/subject" class="btn-primary">
            Начать участие
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="5" y1="12" x2="19" y2="12" />
              <polyline points="12 5 19 12 12 19" />
            </svg>
          </router-link>

          <router-link to="/rules" class="btn-secondary">
            Как это работает
          </router-link>
        </div>

        <div class="hero__chips">
          <div v-for="item in promiseChips" :key="item" class="feature-chip">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            {{ item }}
          </div>
        </div>

        <div class="hero__messages">
          <article v-for="item in messageCards" :key="item.label" class="hero-message">
            <span>{{ item.label }}</span>
            <strong>{{ item.title }}</strong>
            <p>{{ item.text }}</p>
          </article>
        </div>

        <div class="hero__stats">
          <div v-for="item in stats" :key="item.label" class="hero-stat">
            <span class="hero-stat__num">{{ item.value }}</span>
            <span class="hero-stat__label">{{ item.label }}</span>
          </div>
        </div>
      </div>

      <aside class="hero__panel">
        <p class="hero__panel-tag">Короткий путь к действию</p>
        <h2>Пользователь не должен долго думать перед первым кликом</h2>
        <p class="hero__panel-copy">
          Поэтому первый экран показывает выгоду сразу, а рядом закрепляет простой маршрут:
          выбрать предмет, пройти олимпиаду, получить результат.
        </p>

        <div class="hero__steps">
          <article v-for="step in steps" :key="step.label" class="hero-step">
            <div class="hero-step__badge">{{ step.label }}</div>
            <div>
              <strong>{{ step.title }}</strong>
              <p>{{ step.text }}</p>
            </div>
          </article>
        </div>
      </aside>
    </div>
  </section>
</template>

<style scoped>
* { box-sizing: border-box; }

.hero {
  position: relative;
  width: 100%;
  min-height: 100vh;
  padding: 108px 24px 88px;
  overflow: hidden;
  color: white;
}

.hero__bg,
.hero__overlay,
.hero__glow {
  position: absolute;
  inset: 0;
}

.hero__bg {
  background: url('/welcome.png') center/cover no-repeat;
  z-index: 0;
  transform: scale(1.04);
}

.hero__overlay {
  background:
    linear-gradient(112deg, rgba(21, 16, 9, 0.84) 8%, rgba(44, 33, 18, 0.68) 42%, rgba(22, 16, 9, 0.84) 100%),
    radial-gradient(circle at top left, rgba(201, 171, 99, 0.34), transparent 34%);
  z-index: 1;
}

.hero__glow {
  z-index: 2;
  pointer-events: none;
}

.hero__glow--one {
  background: radial-gradient(circle at 18% 28%, rgba(255, 214, 130, 0.22), transparent 28%);
}

.hero__glow--two {
  background: radial-gradient(circle at 86% 80%, rgba(95, 170, 120, 0.18), transparent 24%);
}

.hero__shell {
  position: relative;
  z-index: 3;
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1.08fr) minmax(320px, 420px);
  gap: 32px;
  align-items: start;
}

.hero__content {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding-top: 18px;
}

.hero__eyebrow {
  display: inline-flex;
  width: fit-content;
  padding: 8px 14px;
  border-radius: 999px;
  border: 1px solid rgba(255, 241, 202, 0.18);
  background: rgba(255, 248, 232, 0.12);
  color: rgba(255, 244, 220, 0.92);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.hero__title {
  max-width: 10.5ch;
  font-size: clamp(42px, 6vw, 76px);
  line-height: 0.96;
  letter-spacing: -0.045em;
  text-shadow: 0 14px 34px rgba(0, 0, 0, 0.22);
}

.hero__title-accent {
  display: block;
  color: #f2d68d;
}

.hero__subheadline {
  max-width: 58ch;
  font-size: 18px;
  line-height: 1.72;
  color: rgba(255, 247, 231, 0.92);
}

.hero__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
}

.btn-primary,
.btn-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  min-height: 56px;
  padding: 14px 24px;
  border-radius: 16px;
  text-decoration: none;
  font-size: 15px;
  font-weight: 800;
  transition: transform 0.22s ease, box-shadow 0.22s ease, background 0.22s ease, border-color 0.22s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #e7c779 0%, #c79d45 100%);
  color: #1e160c;
  box-shadow: 0 20px 44px rgba(88, 62, 16, 0.32);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 26px 54px rgba(88, 62, 16, 0.38);
}

.btn-primary svg {
  transition: transform 0.22s ease;
}

.btn-primary:hover svg {
  transform: translateX(4px);
}

.btn-secondary {
  border: 1px solid rgba(255, 243, 214, 0.24);
  background: rgba(255, 248, 235, 0.08);
  color: white;
  backdrop-filter: blur(12px);
}

.btn-secondary:hover {
  transform: translateY(-2px);
  background: rgba(255, 248, 235, 0.14);
}

.hero__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.feature-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 16px;
  border-radius: 999px;
  background: rgba(255, 248, 236, 0.1);
  border: 1px solid rgba(255, 239, 201, 0.18);
  color: rgba(255, 248, 232, 0.95);
  font-size: 13px;
  font-weight: 600;
}

.feature-chip svg {
  color: #90d6a5;
  flex-shrink: 0;
}

.hero__messages {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.hero-message {
  padding: 18px 18px 16px;
  border-radius: 20px;
  background: rgba(255, 248, 235, 0.12);
  border: 1px solid rgba(255, 239, 201, 0.14);
  backdrop-filter: blur(12px);
  display: grid;
  gap: 8px;
}

.hero-message span {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: rgba(255, 236, 189, 0.82);
}

.hero-message strong {
  font-size: 17px;
  line-height: 1.3;
  color: white;
}

.hero-message p {
  margin: 0;
  color: rgba(255, 243, 221, 0.8);
  line-height: 1.62;
  font-size: 14px;
}

.hero__stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
}

.hero-stat {
  padding: 18px 18px 16px;
  border-radius: 20px;
  background: rgba(255, 248, 235, 0.11);
  border: 1px solid rgba(255, 239, 201, 0.15);
  backdrop-filter: blur(12px);
}

.hero-stat__num {
  display: block;
  margin-bottom: 8px;
  font-size: 28px;
  font-weight: 800;
  color: white;
}

.hero-stat__label {
  display: block;
  color: rgba(255, 243, 221, 0.78);
  font-size: 13px;
  line-height: 1.55;
}

.hero__panel {
  padding: 28px;
  border-radius: 30px;
  background: rgba(255, 249, 238, 0.94);
  color: var(--text);
  border: 1px solid rgba(255, 236, 191, 0.45);
  box-shadow: 0 28px 60px rgba(28, 21, 10, 0.28);
  display: grid;
  gap: 18px;
  margin-top: 72px;
}

.hero__panel-tag {
  margin: 0;
  color: var(--accent-strong);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.hero__panel h2 {
  font-size: clamp(24px, 3vw, 34px);
  color: var(--text);
}

.hero__panel-copy {
  color: var(--text-secondary);
  line-height: 1.72;
}

.hero__steps {
  display: grid;
  gap: 12px;
}

.hero-step {
  display: grid;
  grid-template-columns: 42px minmax(0, 1fr);
  gap: 14px;
  align-items: start;
  padding: 16px;
  border-radius: 20px;
  background: rgba(255, 252, 245, 0.98);
  border: 1px solid var(--surface-border);
}

.hero-step__badge {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--accent) 0%, #e6cb88 100%);
  color: #251a0c;
  font-size: 15px;
  font-weight: 800;
}

.hero-step strong {
  display: block;
  margin-bottom: 6px;
  font-size: 16px;
}

.hero-step p {
  margin: 0;
  color: var(--text-secondary);
  line-height: 1.6;
  font-size: 14px;
}

@media (max-width: 980px) {
  .hero {
    min-height: auto;
    padding-top: 96px;
  }

  .hero__shell {
    grid-template-columns: 1fr;
  }

  .hero__panel {
    margin-top: 0;
  }
}

@media (max-width: 720px) {
  .hero {
    padding-inline: 16px;
    padding-bottom: 56px;
  }

  .hero__title {
    font-size: clamp(34px, 11vw, 54px);
  }

  .hero__subheadline {
    font-size: 16px;
  }

  .hero__messages,
  .hero__stats {
    grid-template-columns: 1fr;
  }

  .hero__panel {
    padding: 22px 18px;
    border-radius: 24px;
  }
}

@media (max-width: 520px) {
  .hero__actions {
    flex-direction: column;
  }

  .btn-primary,
  .btn-secondary {
    width: 100%;
  }

  .hero__chips {
    flex-direction: column;
  }
}
</style>
