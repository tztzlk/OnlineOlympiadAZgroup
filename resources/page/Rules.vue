<template>
  <div class="rules-page">
    <div class="rules-container">
      <header class="page-header">
        <span class="ds-eyebrow sun">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
          </svg>
          Правила
        </span>
        <h1 class="page-title">Правила проведения<br/>олимпиады</h1>
        <p class="page-subtitle">
          Пожалуйста, внимательно ознакомьтесь с правилами перед началом участия
        </p>
      </header>

      <div class="notice" role="note">
        <span class="notice__icon" aria-hidden="true">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0zM12 9v4M12 17h.01"/>
          </svg>
        </span>
        <p>Нарушение правил может привести к аннулированию результатов. Участие в олимпиаде означает согласие со всеми условиями.</p>
      </div>

      <ol class="rules-list">
        <li
          v-for="(rule, index) in rules"
          :key="index"
          v-reveal="index"
          class="rule-card"
          :class="`tone-${index % 6}`"
        >
          <div class="rule-card__top">
            <span class="rule-icon" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <template v-for="(segment, segmentIndex) in icons[index]" :key="`${index}-${segmentIndex}`">
                  <path v-if="segment.type === 'path'" :d="segment.d" />
                  <circle v-else-if="segment.type === 'circle'" :cx="segment.cx" :cy="segment.cy" :r="segment.r" />
                  <polyline v-else-if="segment.type === 'polyline'" :points="segment.points" />
                  <line v-else-if="segment.type === 'line'" :x1="segment.x1" :y1="segment.y1" :x2="segment.x2" :y2="segment.y2" />
                </template>
              </svg>
            </span>
            <span class="rule-number">{{ String(index + 1).padStart(2, '0') }}</span>
          </div>
          <h2 class="rule-title">{{ rule.title }}</h2>
          <p class="rule-desc">{{ rule.desc }}</p>
        </li>
      </ol>

      <div class="rules-cta">
        <span class="cta-check" aria-hidden="true">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
        </span>
        <span class="rules-cta__text">Я ознакомлен(а) с правилами и готов(а) участвовать</span>
        <router-link to="/subject" class="ds-btn ds-btn-sun ds-btn-lg cta-btn">
          Выбрать олимпиаду
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
const rules = [
  {
    title: 'Индивидуальное участие',
    desc: 'Каждый участник должен проходить олимпиаду самостоятельно. Совместное прохождение не допускается.'
  },
  {
    title: 'Ограничение по времени',
    desc: 'Время на выполнение заданий ограничено и отображается в каждом разделе. Следите за таймером.'
  },
  {
    title: 'Академическая честность',
    desc: 'Запрещено использовать помощь других людей, учебники или интернет во время прохождения.'
  },
  {
    title: 'Отправка ответов',
    desc: 'Все ответы должны быть отправлены через платформу до окончания отведённого времени.'
  },
  {
    title: 'Технические проблемы',
    desc: 'В случае технических неполадок немедленно уведомите организаторов через форму обратной связи.'
  },
  {
    title: 'Аннулирование результатов',
    desc: 'Нарушение правил ведёт к автоматическому аннулированию результатов без права на пересмотр.'
  },
]

const icons = [
  [
    { type: 'path', d: 'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2' },
    { type: 'circle', cx: '12', cy: '7', r: '4' },
  ],
  [
    { type: 'circle', cx: '12', cy: '12', r: '10' },
    { type: 'polyline', points: '12 6 12 12 16 14' },
  ],
  [
    { type: 'polyline', points: '20 6 9 17 4 12' },
  ],
  [
    { type: 'path', d: 'M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.99 15' },
    { type: 'path', d: 'M16 2v4' },
    { type: 'path', d: 'M3 7h18' },
  ],
  [
    { type: 'path', d: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z' },
  ],
  [
    { type: 'circle', cx: '12', cy: '12', r: '10' },
    { type: 'line', x1: '15', y1: '9', x2: '9', y2: '15' },
    { type: 'line', x1: '9', y1: '9', x2: '15', y2: '15' },
  ],
]
</script>

<style scoped>
.rules-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 36px) 20px 96px;
  background:
    radial-gradient(700px 360px at 50% 0%, color-mix(in srgb, var(--sun) 16%, transparent), transparent 70%),
    var(--bg);
}

.rules-container {
  max-width: 1160px;
  margin: 0 auto;
  display: grid;
  gap: 24px;
}

.page-header {
  display: grid;
  justify-items: center;
  gap: 14px;
  text-align: center;
}

.page-title { font-size: clamp(30px, 4.6vw, 52px); }

.page-subtitle {
  color: var(--text-secondary);
  font-size: 18px;
}

.notice {
  display: flex;
  align-items: center;
  gap: 14px;
  max-width: 860px;
  width: 100%;
  margin: 0 auto;
  padding: 16px 20px;
  border-radius: var(--radius-lg);
  background: var(--warning-soft);
  border: 1px solid color-mix(in srgb, var(--warning) 30%, transparent);
}

.notice__icon {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 14px;
  background: var(--card);
  color: #c27800;
}

.notice p {
  color: var(--warning-ink);
  font-size: 15px;
  font-weight: 500;
}

.rules-list {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  list-style: none;
}

.rule-card {
  display: grid;
  align-content: start;
  gap: 10px;
  padding: 24px;
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  transition: transform var(--dur-slow) var(--ease-out), box-shadow var(--dur-slow) ease, opacity 560ms var(--ease-out);
}

@media (hover: hover) and (pointer: fine) {
  .rule-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); }
  .rule-card:hover .rule-icon { transform: rotate(-8deg) scale(1.05); }
}

.rule-card__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
}

.rule-icon {
  width: 50px;
  height: 50px;
  display: grid;
  place-items: center;
  border-radius: 16px;
  transition: transform var(--dur-slow) var(--ease-out);
}

.tone-0 .rule-icon { background: var(--brand-soft); color: var(--brand); }
.tone-1 .rule-icon { background: var(--sun-soft); color: #c98a00; }
.tone-2 .rule-icon { background: var(--success-soft); color: var(--success); }
.tone-3 .rule-icon { background: var(--tone-violet-soft); color: var(--tone-violet); }
.tone-4 .rule-icon { background: var(--tone-teal-soft); color: #0d8a7c; }
.tone-5 .rule-icon { background: var(--danger-soft); color: var(--danger); }

.rule-number {
  font-family: var(--font-display);
  color: var(--bg-alt);
  font-size: 34px;
  font-weight: 700;
  line-height: 1;
  -webkit-text-stroke: 1.5px var(--border-strong);
}

.rule-title {
  font-family: var(--font-sans);
  font-size: 19px;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.rule-desc {
  color: var(--text-secondary);
  font-size: 15px;
  line-height: 1.6;
}

.rules-cta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 16px;
  padding: clamp(20px, 3vw, 28px) clamp(20px, 3vw, 32px);
  border-radius: var(--radius-xl);
  background:
    radial-gradient(400px 220px at 0% 100%, rgba(255, 201, 51, 0.18), transparent 70%),
    linear-gradient(155deg, #1d2a5c 0%, #111a33 100%);
  color: #ffffff;
}

.cta-check {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--success);
}

.rules-cta__text {
  flex: 1;
  min-width: 240px;
  font-size: 18px;
  font-weight: 700;
}

.cta-btn svg { transition: transform var(--dur) var(--ease-out); }

@media (hover: hover) and (pointer: fine) {
  .cta-btn:hover svg { transform: translateX(4px); }
}

@media (max-width: 1024px) {
  .rules-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 600px) {
  .rules-page { padding: calc(var(--header-h) + 18px) 12px 96px; }
  .rules-list { grid-template-columns: 1fr; }
  .notice { align-items: flex-start; }
  .rules-cta__text { min-width: 0; }
  .cta-btn { width: 100%; }
}
</style>
