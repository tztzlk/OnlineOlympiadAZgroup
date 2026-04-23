<template>
  <div class="rules-page">
    <div class="bg-orbs">
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
    </div>

    <div class="rules-container">

      <!-- Header -->
      <div class="page-header">
        <div class="page-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          Правила
        </div>
        <h1 class="page-title">Правила проведения<br/>олимпиады</h1>
        <p class="page-subtitle">
          Пожалуйста, внимательно ознакомьтесь с правилами перед началом участия
        </p>
      </div>

      <!-- Important notice -->
      <div class="notice">
        <div class="notice__icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
          </svg>
        </div>
        <p>Нарушение правил может привести к аннулированию результатов. Участие в олимпиаде означает согласие со всеми условиями.</p>
      </div>

      <!-- Rules grid -->
      <div class="rules-list">
        <div
          class="rule-card"
          v-for="(rule, index) in rules"
          :key="index"
          :style="{ '--delay': index * 0.07 + 's' }"
        >
          <div class="rule-card__top">
            <div class="rule-number">{{ String(index + 1).padStart(2, '0') }}</div>
            <div class="rule-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <template v-for="(segment, segmentIndex) in icons[index]" :key="`${index}-${segmentIndex}`">
                  <path v-if="segment.type === 'path'" :d="segment.d" />
                  <circle v-else-if="segment.type === 'circle'" :cx="segment.cx" :cy="segment.cy" :r="segment.r" />
                  <polyline v-else-if="segment.type === 'polyline'" :points="segment.points" />
                  <line v-else-if="segment.type === 'line'" :x1="segment.x1" :y1="segment.y1" :x2="segment.x2" :y2="segment.y2" />
                </template>
              </svg>
            </div>
          </div>
          <h2 class="rule-title">{{ rule.title }}</h2>
          <p class="rule-desc">{{ rule.desc }}</p>
        </div>
      </div>

      <!-- CTA -->
      <div class="rules-cta">
        <div class="cta-check">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <span>Я ознакомлен(а) с правилами и готов(а) участвовать</span>
        <router-link to="/subject" class="cta-btn">
          Выбрать олимпиаду
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
          </svg>
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
    title: 'Честное выполнение',
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
* { box-sizing: border-box; }

.rules-page {
  min-height: 100vh;
  background: var(--bg);
  padding: 80px 28px 100px;
  position: relative;
  overflow: hidden;
}

/* Orbs */
.bg-orbs { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
.orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: 0.3; }
.orb-1 { width: 550px; height: 550px; background: radial-gradient(circle, rgba(208,179,107,0.14), transparent); top: -180px; right: -120px; }
.orb-2 { width: 450px; height: 450px; background: radial-gradient(circle, rgba(73,168,107,0.1), transparent); bottom: -120px; left: -80px; }

.rules-container {
  max-width: 1100px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

/* Header */
.page-header { text-align: center; margin-bottom: 40px; }
.page-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #8b6c11;
  background: color-mix(in srgb, var(--accent) 14%, transparent);
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid color-mix(in srgb, var(--accent) 26%, transparent);
  margin-bottom: 18px;
}
.page-title {
  font-size: clamp(32px, 4.4vw, 52px);
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 14px;
  line-height: 1.06;
}
.page-subtitle { font-size: 16px; font-weight: 400; color: var(--text-secondary); margin: 0; line-height: 1.6; }

/* Notice */
.notice {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 16px;
  padding: 16px 22px;
  margin-bottom: 48px;
}
.notice__icon {
  width: 42px; height: 42px;
  background: #fef3c7;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: #d97706;
  flex-shrink: 0;
}
.notice p {
  font-size: 14px;
  color: #92400e;
  line-height: 1.6;
  margin: 0;
  font-weight: 500;
}

/* Rules grid */
.rules-list {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-bottom: 48px;
}

/* Rule card */
.rule-card {
  position: relative;
  background: var(--surface);
  padding: 24px;
  border-radius: 24px;
  border: 1px solid var(--surface-border);
  box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
  overflow: hidden;
  transition: all 0.3s ease;
  animation: fadeUp 0.5s ease both;
  animation-delay: var(--delay);
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.rule-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--accent), color-mix(in srgb, var(--accent) 68%, var(--green)), var(--green));
  opacity: 0;
  transition: opacity 0.3s;
}
.rule-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 24px 56px rgba(15, 23, 42, 0.12);
  border-color: color-mix(in srgb, var(--accent) 28%, var(--surface-border));
}
.rule-card:hover::before { opacity: 1; }

.rule-card__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}

.rule-number {
  font-size: 28px;
  font-weight: 700;
  color: color-mix(in srgb, var(--text-on-surface) 20%, transparent);
  line-height: 1;
  letter-spacing: -1px;
}

.rule-icon {
  width: 42px; height: 42px;
  background: color-mix(in srgb, var(--accent) 12%, transparent);
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  color: #8b6c11;
  transition: all 0.3s;
}
.rule-card:hover .rule-icon {
  background: linear-gradient(135deg, var(--green), #59bd7f);
  color: white;
  box-shadow: 0 8px 18px rgba(73, 168, 107, 0.22);
}

.rule-title {
  font-size: 18px;
  font-weight: 600;
  color: var(--text-on-surface);
  margin: 0 0 10px;
  line-height: 1.35;
}

.rule-desc {
  font-size: 14px;
  color: var(--text-muted-on-surface);
  line-height: 1.7;
  margin: 0;
}

/* CTA */
.rules-cta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  flex-wrap: wrap;
  background: var(--surface);
  border: 1px solid var(--surface-border);
  border-radius: 28px;
  padding: 28px 32px;
  box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
}
.cta-check {
  width: 40px; height: 40px;
  background: var(--green-soft);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: var(--green);
  flex-shrink: 0;
}
.rules-cta span {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-on-surface);
  flex: 1;
  min-width: 260px;
}
.cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  justify-content: center;
  min-height: 60px;
  padding: 14px 26px;
  border-radius: 18px;
  background: linear-gradient(135deg, #d8b860, #cba84e);
  color: #1a1408;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 14px 30px rgba(104, 79, 28, 0.22);
  transition: all 0.2s;
  white-space: nowrap;
}
.cta-btn svg { transition: transform 0.2s; }
.cta-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 18px 38px rgba(104, 79, 28, 0.28);
}
.cta-btn:hover svg { transform: translateX(4px); }

/* Responsive */
@media (max-width: 1024px) {
  .rules-list { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
  .rules-page { padding: 60px 16px 80px; }
  .page-title { font-size: 30px; }
  .rules-list { grid-template-columns: 1fr 1fr; gap: 14px; }
  .notice { flex-direction: column; text-align: center; }
  .rules-cta { padding: 24px 20px; }
  .rules-cta span { font-size: 16px; min-width: 0; }
}
@media (max-width: 520px) {
  .rules-list { grid-template-columns: 1fr; }
  .rules-cta { flex-direction: column; text-align: center; }
  .rules-cta span { min-width: unset; text-align: center; }
  .cta-btn { width: 100%; justify-content: center; }
}
</style>
