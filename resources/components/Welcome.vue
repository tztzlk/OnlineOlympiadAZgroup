<template>
  <section class="hero" aria-labelledby="hero-title">
    <div class="hero__wrapper">
      <div class="hero__content">
        <span class="hero__eyebrow">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/>
          </svg>
          Онлайн-олимпиада
        </span>

        <h1 id="hero-title" class="hero__title">
          Проверьте знания<br>
          <span class="hero__title-highlight">на Eurika!</span>
        </h1>

        <p class="hero__sub">
          Академические олимпиады по математике и другим предметам.
          Мгновенный результат и сертификат — онлайн.
        </p>

        <div class="hero__actions">
          <router-link to="/register" class="ds-btn ds-btn-primary ds-btn-lg hero__cta">
            Начать сейчас
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M5 12h14M13 6l6 6-6 6"/>
            </svg>
          </router-link>
          <a href="#how-it-works" class="ds-btn ds-btn-ghost ds-btn-lg">Как это работает</a>
        </div>

        <p class="hero__price">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          Участие: от 2 000 ₸
        </p>

        <ul class="hero__chips" aria-label="Что входит">
          <li class="chip">
            <span class="chip__dot" aria-hidden="true">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            Полный доступ к заданиям
          </li>
          <li class="chip">
            <span class="chip__dot" aria-hidden="true">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            Мгновенный результат
          </li>
          <li class="chip">
            <span class="chip__dot" aria-hidden="true">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            Сертификат участника
          </li>
        </ul>

        <div class="hero__coming-soon" role="status">
          <span class="coming-soon__pulse" aria-hidden="true"></span>
          <span>Ближайшая олимпиада — <strong>28 мая</strong> · Успейте зарегистрироваться!</span>
        </div>
      </div>

      <div class="hero__visual" aria-hidden="true">
        <div class="stage">
          <svg class="stage__stars" viewBox="0 0 400 400" fill="none">
            <circle cx="62" cy="70" r="5" fill="currentColor" opacity=".55"/>
            <circle cx="338" cy="96" r="3.5" fill="currentColor" opacity=".45"/>
            <circle cx="350" cy="300" r="6" fill="currentColor" opacity=".35"/>
            <circle cx="46" cy="300" r="3" fill="currentColor" opacity=".5"/>
            <path d="M318 46l4 9 9 4-9 4-4 9-4-9-9-4 9-4z" fill="#ffc933"/>
            <path d="M74 182l3 7 7 3-7 3-3 7-3-7-7-3 7-3z" fill="#ffc933" opacity=".85"/>
            <path d="M300 230l2.5 5.5 5.5 2.5-5.5 2.5-2.5 5.5-2.5-5.5-5.5-2.5 5.5-2.5z" fill="#fff" opacity=".7"/>
          </svg>
          <div class="stage__sun"></div>

          <svg class="trophy" viewBox="0 0 240 280" fill="none">
            <defs>
              <linearGradient id="cup-fill" x1="72" y1="36" x2="168" y2="214" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#ffe17a"/>
                <stop offset="100%" stop-color="#ffb800"/>
              </linearGradient>
            </defs>
            <path d="M72 62 C38 62 26 98 50 112 C60 118 72 108 72 108" stroke="#f0a800" stroke-width="9" stroke-linecap="round"/>
            <path d="M168 62 C202 62 214 98 190 112 C180 118 168 108 168 108" stroke="#f0a800" stroke-width="9" stroke-linecap="round"/>
            <path d="M72 36 H168 V138 C168 178 140 206 120 214 C100 206 72 178 72 138 V36Z" fill="url(#cup-fill)"/>
            <rect x="106" y="212" width="28" height="34" rx="5" fill="#f0a800"/>
            <rect x="80" y="244" width="80" height="18" rx="9" fill="#ffffff"/>
            <path d="M120 84 L127 104 H148 L131 116 L138 136 L120 124 L102 136 L109 116 L92 104 H113Z" fill="#ffffff"/>
            <ellipse cx="94" cy="74" rx="8" ry="20" fill="#ffffff" opacity=".35" transform="rotate(-18 94 74)"/>
          </svg>

          <div class="float-badge float-badge--1">
            <span class="float-badge__icon float-badge__icon--green">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
            <span>Сертификат</span>
          </div>
          <div class="float-badge float-badge--2">
            <span class="float-badge__icon float-badge__icon--sun">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
            </span>
            <span>1 место</span>
          </div>
          <div class="float-badge float-badge--3">
            <span class="float-badge__icon float-badge__icon--blue">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            </span>
            <span>8 мин</span>
          </div>
        </div>
      </div>
    </div>

    <div class="hero__stats-wrap">
      <dl class="hero__stats">
        <div class="stat">
          <dt>участников</dt>
          <dd>1 247+</dd>
        </div>
        <div class="stat">
          <dt>рекомендуют</dt>
          <dd>97.4%</dd>
        </div>
        <div class="stat">
          <dt>результат</dt>
          <dd>Мгновенный</dd>
        </div>
      </dl>
    </div>
  </section>
</template>

<script>
export default { name: 'Hero' }
</script>

<style scoped>
.hero {
  position: relative;
  overflow: hidden;
  padding: calc(var(--header-h) + 48px) 24px 56px;
  background:
    radial-gradient(900px 520px at 88% -10%, color-mix(in srgb, var(--brand) 14%, transparent), transparent 70%),
    radial-gradient(600px 420px at -8% 110%, color-mix(in srgb, var(--sun) 22%, transparent), transparent 70%),
    var(--bg);
}

.hero__wrapper {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr);
  align-items: center;
  gap: 56px;
}

/* ---- Текст ---- */
.hero__content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.hero__content > * {
  animation: heroFadeUp 640ms var(--ease-out) both;
}
.hero__eyebrow       { animation-delay: 0ms; }
.hero__title         { animation-delay: 70ms; }
.hero__sub           { animation-delay: 140ms; }
.hero__actions       { animation-delay: 210ms; }
.hero__price         { animation-delay: 270ms; }
.hero__chips         { animation-delay: 330ms; }
.hero__coming-soon   { animation-delay: 400ms; }

@keyframes heroFadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: none; }
}

.hero__eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 7px 14px 7px 10px;
  margin-bottom: 22px;
  border-radius: var(--radius-pill);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-xs);
  color: var(--text);
  font-size: 14px;
  font-weight: 600;
}

.hero__eyebrow svg {
  color: var(--sun);
  filter: drop-shadow(0 1px 0 rgba(122, 79, 0, 0.25));
}

.hero__title {
  position: relative;
  z-index: 0; /* свой слой, чтобы «маркер» лёг под текст, а не под фон секции */
  font-size: clamp(36px, 5.4vw, 64px);
  line-height: 1.04;
  letter-spacing: -0.035em;
  margin-bottom: 20px;
}

.hero__title-highlight {
  position: relative;
  display: inline-block;
  color: var(--brand);
  white-space: nowrap;
}

/* «Маркер» под словом — живая деталь вместо ровной линии */
.hero__title-highlight::after {
  content: '';
  position: absolute;
  left: -2%;
  right: -2%;
  bottom: 0.04em;
  height: 0.3em;
  z-index: -1;
  border-radius: 0.2em;
  background: var(--sun);
  opacity: 0.55;
  transform: rotate(-1.2deg);
}

.hero__sub {
  max-width: 34ch;
  margin-bottom: 30px;
  color: var(--text-secondary);
  font-size: clamp(17px, 1.6vw, 19px);
  line-height: 1.6;
}

.hero__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 22px;
}

.hero__cta svg {
  transition: transform var(--dur) var(--ease-out);
}

@media (hover: hover) and (pointer: fine) {
  .hero__cta:hover svg { transform: translateX(4px); }
}

.hero__price {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 18px;
  color: var(--text);
  font-size: 15px;
  font-weight: 600;
}

.hero__price svg { color: var(--success); }

.hero__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px 16px;
  margin-bottom: 26px;
  list-style: none;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 500;
}

.chip__dot {
  width: 20px;
  height: 20px;
  display: grid;
  place-items: center;
  border-radius: 7px;
  background: var(--success-soft);
  color: var(--success);
  flex-shrink: 0;
}

.hero__coming-soon {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  padding: 12px 18px;
  border-radius: var(--radius-md);
  background: var(--card);
  border: 1px solid color-mix(in srgb, var(--danger) 26%, var(--border));
  box-shadow: var(--shadow-sm);
  color: var(--text);
  font-size: 15px;
  font-weight: 500;
}

.hero__coming-soon strong {
  color: var(--danger);
  font-weight: 700;
}

.coming-soon__pulse {
  position: relative;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: var(--danger);
  flex-shrink: 0;
}

.coming-soon__pulse::before {
  content: '';
  position: absolute;
  inset: -5px;
  border-radius: 50%;
  background: color-mix(in srgb, var(--danger) 30%, transparent);
  animation: pulse-ring 1.8s var(--ease-out) infinite;
}

@keyframes pulse-ring {
  0%   { transform: scale(0.6); opacity: 1; }
  100% { transform: scale(1.9); opacity: 0; }
}

/* ---- Иллюстрация ---- */
.hero__visual {
  position: relative;
  display: flex;
  justify-content: center;
  animation: heroFadeUp 800ms var(--ease-out) 160ms both;
}

.stage {
  position: relative;
  width: min(100%, 460px);
  aspect-ratio: 1;
  border-radius: 40% 60% 54% 46% / 48% 42% 58% 52%;
  background:
    radial-gradient(circle at 30% 25%, color-mix(in srgb, #ffffff 22%, transparent), transparent 55%),
    linear-gradient(155deg, #3d6cff 0%, #2b5bf5 45%, #1f43c9 100%);
  box-shadow: 0 30px 70px rgba(43, 91, 245, 0.35);
  display: grid;
  place-items: center;
  color: #ffffff;
}

.stage__stars {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  color: #ffffff;
}

.stage__sun {
  position: absolute;
  width: 52%;
  aspect-ratio: 1;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(255, 201, 51, 0.45) 0%, rgba(255, 201, 51, 0.12) 55%, transparent 72%);
}

.trophy {
  position: relative;
  width: 48%;
  height: auto;
  filter: drop-shadow(0 22px 26px rgba(10, 25, 90, 0.4));
  animation: trophy-float 5s ease-in-out infinite;
}

@keyframes trophy-float {
  0%, 100% { transform: translateY(0) rotate(-2deg); }
  50%      { transform: translateY(-12px) rotate(2deg); }
}

.float-badge {
  position: absolute;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px 8px 8px;
  border-radius: var(--radius-md);
  background: var(--card);
  color: var(--text);
  box-shadow: var(--shadow-lg);
  font-size: 14px;
  font-weight: 700;
  white-space: nowrap;
  animation: badge-float 6s ease-in-out infinite;
}

.float-badge--1 { top: 12%; right: -4%; animation-delay: 0s; }
.float-badge--2 { bottom: 20%; left: -8%; animation-delay: 1.4s; }
.float-badge--3 { bottom: 4%; right: 8%; animation-delay: 0.7s; }

.float-badge__icon {
  width: 28px;
  height: 28px;
  display: grid;
  place-items: center;
  border-radius: 9px;
}
.float-badge__icon--green { background: var(--success-soft); color: var(--success); }
.float-badge__icon--sun   { background: var(--sun-soft); color: #e3a400; }
.float-badge__icon--blue  { background: var(--brand-soft); color: var(--brand); }

@keyframes badge-float {
  0%, 100% { transform: translateY(0); }
  50%      { transform: translateY(-7px); }
}

/* ---- Цифры ---- */
.hero__stats-wrap {
  position: relative;
  max-width: 1200px;
  margin: 56px auto 0;
}

.hero__stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  margin: 0;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-sm);
  animation: heroFadeUp 640ms var(--ease-out) 480ms both;
}

.stat {
  display: flex;
  flex-direction: column-reverse;
  gap: 4px;
  padding: 22px 28px;
}

.stat + .stat { border-left: 1px solid var(--border); }

.stat dd {
  margin: 0;
  font-family: var(--font-display);
  font-size: clamp(22px, 2.6vw, 30px);
  font-weight: 700;
  letter-spacing: -0.03em;
  line-height: 1.1;
  color: var(--text);
  font-variant-numeric: tabular-nums;
}

.stat:first-child dd { color: var(--brand); }

.stat dt {
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 500;
}

/* ---- Адаптив ---- */
@media (max-width: 960px) {
  .hero { padding-top: calc(var(--header-h) + 32px); }
  .hero__wrapper { grid-template-columns: 1fr; gap: 40px; }
  .hero__visual { order: -1; }
  .stage { width: min(78vw, 360px); }
  .float-badge { font-size: 13px; }
  .float-badge--2 { left: -4%; }
}

@media (max-width: 600px) {
  .hero { padding: calc(var(--header-h) + 20px) 16px 40px; }
  .hero__title { font-size: clamp(32px, 9vw, 40px); }
  .hero__actions { width: 100%; }
  .hero__actions .ds-btn { flex: 1 1 100%; }
  .hero__coming-soon { width: 100%; font-size: 14px; }
  .stage { width: min(56vw, 220px); }
  .float-badge { padding: 6px 10px 6px 6px; font-size: 12px; }
  .float-badge__icon { width: 24px; height: 24px; }
  .float-badge--1 { right: -10%; }
  .float-badge--2 { left: -12%; }
  .hero__stats-wrap { margin-top: 36px; }
  .hero__stats { grid-template-columns: 1fr; }
  .stat { flex-direction: row-reverse; justify-content: space-between; align-items: baseline; padding: 16px 20px; }
  .stat + .stat { border-left: 0; border-top: 1px solid var(--border); }
}
</style>
