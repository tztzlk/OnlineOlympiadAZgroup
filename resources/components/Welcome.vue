<template>
  <section class="hero">
    <div class="hero__blob hero__blob--yellow" aria-hidden="true"></div>
    <div class="hero__blob hero__blob--green" aria-hidden="true"></div>
    <div class="hero__blob hero__blob--yellow-sm" aria-hidden="true"></div>

    <div class="hero__wrapper">
      <div class="hero__content">
        <span class="hero__eyebrow">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
          Онлайн-олимпиада
        </span>

        <h1 class="hero__title">
          Проверь знания<br>
          <span class="hero__title-highlight">на Eurika!</span>
        </h1>

        <p class="hero__sub">
          Академические олимпиады по математике и другим предметам.
          Мгновенный результат и сертификат — онлайн.
        </p>

        <p class="hero__price">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          Участие: от 2 990 ₸
        </p>

        <div class="hero__actions">
          <router-link to="/register" class="btn-cta">
            Начать сейчас
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
              <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
            </svg>
          </router-link>
          <a href="#how-it-works" class="btn-outline">Как это работает</a>
        </div>

        <div class="hero__chips">
          <span class="chip">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Полный доступ к заданиям
          </span>
          <span class="chip">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Мгновенный результат
          </span>
          <span class="chip">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            Сертификат участника
          </span>
        </div>

        <div class="hero__stats">
          <div class="stat">
            <strong>1 247+</strong>
            <span>участников</span>
          </div>
          <div class="stat-divider" aria-hidden="true"></div>
          <div class="stat">
            <strong>97.4%</strong>
            <span>рекомендуют</span>
          </div>
          <div class="stat-divider" aria-hidden="true"></div>
          <div class="stat">
            <strong>8 мин</strong>
            <span>до результата</span>
          </div>
        </div>
      </div>

      <div class="hero__visual" aria-hidden="true">
        <div class="trophy-glow"></div>
        <div class="trophy-wrap">
          <svg class="trophy-svg" viewBox="0 0 240 280" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M72 36 H168 V138 C168 178 140 206 120 214 C100 206 72 178 72 138 V36Z"
              fill="url(#cup-fill)" stroke="#e6b52e" stroke-width="2.5"/>
            <path d="M72 62 C38 62 26 98 50 112 C60 118 72 108 72 108"
              stroke="#d4a017" stroke-width="8" stroke-linecap="round" fill="none"/>
            <path d="M168 62 C202 62 214 98 190 112 C180 118 168 108 168 108"
              stroke="#d4a017" stroke-width="8" stroke-linecap="round" fill="none"/>
            <rect x="106" y="214" width="28" height="34" rx="4" fill="#d4a017"/>
            <rect x="84" y="248" width="72" height="14" rx="7" fill="#d4a017"/>
            <path d="M120 88 L126 106 H144 L130 116 L136 134 L120 124 L104 134 L110 116 L96 106 H114Z"
              fill="rgba(255,255,255,0.9)"/>
            <ellipse cx="98" cy="72" rx="10" ry="18" fill="rgba(255,255,255,0.2)" transform="rotate(-20 98 72)"/>
            <defs>
              <linearGradient id="cup-fill" x1="72" y1="36" x2="168" y2="214" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#fde047"/>
                <stop offset="100%" stop-color="#f5c842"/>
              </linearGradient>
            </defs>
          </svg>

          <div class="float-badge float-badge--1">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Сертификат</span>
          </div>
          <div class="float-badge float-badge--2">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#f5c842" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span>1 место</span>
          </div>
          <div class="float-badge float-badge--3">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <span>8 мин</span>
          </div>

          <div class="dot dot--1"></div>
          <div class="dot dot--2"></div>
          <div class="dot dot--3"></div>
          <div class="dot dot--4"></div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default { name: 'Hero' }
</script>

<style scoped>
* { box-sizing: border-box; }

.hero {
  position: relative;
  width: 100%;
  min-height: 100dvh;
  background: var(--bg);
  display: flex;
  align-items: center;
  padding: 96px 28px 80px;
  overflow: hidden;
}

.hero__blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
  z-index: 0;
}
.hero__blob--yellow {
  width: 560px;
  height: 560px;
  background: rgba(245, 200, 66, 0.22);
  top: -120px;
  right: -80px;
}
.hero__blob--green {
  width: 320px;
  height: 320px;
  background: rgba(22, 163, 74, 0.10);
  bottom: 40px;
  left: -60px;
}
.hero__blob--yellow-sm {
  width: 200px;
  height: 200px;
  background: rgba(245, 200, 66, 0.14);
  bottom: 80px;
  right: 20%;
}

.hero__wrapper {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1160px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 1fr 1fr;
  align-items: center;
  gap: 64px;
}

.hero__content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0;
}

.hero__eyebrow { animation: heroFadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; animation-delay: 0ms; }
.hero__title   { animation: heroFadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; animation-delay: 80ms; }
.hero__sub     { animation: heroFadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; animation-delay: 150ms; }
.hero__price   { animation: heroFadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; animation-delay: 210ms; }
.hero__actions { animation: heroFadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; animation-delay: 270ms; }
.hero__chips   { animation: heroFadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; animation-delay: 340ms; }
.hero__stats   { animation: heroFadeUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) both; animation-delay: 420ms; }

@keyframes heroFadeUp {
  from { opacity: 0; transform: translateY(18px); }
  to   { opacity: 1; transform: translateY(0); }
}

.hero__eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 15px;
  border-radius: 999px;
  background: var(--accent-soft);
  border: 1.5px solid rgba(245, 200, 66, 0.4);
  color: #92660a;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.09em;
  text-transform: uppercase;
  margin-bottom: 24px;
}
.hero__eyebrow svg { color: var(--accent); flex-shrink: 0; }

.hero__title {
  font-size: clamp(44px, 5.5vw, 68px);
  font-weight: 800;
  line-height: 1.05;
  color: var(--text);
  letter-spacing: -0.03em;
  margin: 0 0 20px;
}
.hero__title-highlight {
  color: var(--accent);
  position: relative;
  display: inline-block;
}
.hero__title-highlight::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 0;
  right: 0;
  height: 3px;
  border-radius: 2px;
  background: var(--accent);
  opacity: 0.38;
}

.hero__sub {
  font-size: 17px;
  line-height: 1.68;
  color: var(--text-secondary);
  max-width: 460px;
  margin: 0 0 20px;
}

.hero__price {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 15px;
  border-radius: 999px;
  background: var(--green-soft);
  border: 1.5px solid rgba(22, 163, 74, 0.25);
  color: var(--green-strong);
  font-size: 13px;
  font-weight: 700;
  margin: 0 0 28px;
}
.hero__price svg { color: var(--green); flex-shrink: 0; }

.hero__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 28px;
}

.btn-cta {
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
  border: none;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.30);
  transition: background 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
}
.btn-cta svg { transition: transform 0.2s cubic-bezier(0.23, 1, 0.32, 1); }

@media (hover: hover) and (pointer: fine) {
  .btn-cta:hover {
    background: var(--green-hover);
    transform: translateY(-2px);
    box-shadow: 0 14px 32px rgba(22, 163, 74, 0.36);
  }
  .btn-cta:hover svg { transform: translateX(4px); }
}
.btn-cta:active {
  transform: scale(0.98) translateY(0);
  box-shadow: 0 4px 14px rgba(22, 163, 74, 0.22);
  transition-duration: 0.08s;
}

.btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 15px 26px;
  border-radius: 14px;
  background: transparent;
  color: var(--text);
  font-size: 15px;
  font-weight: 600;
  text-decoration: none;
  border: 2px solid var(--border);
  transition: border-color 0.22s ease, background 0.22s ease, color 0.22s ease;
}
.btn-outline:hover {
  border-color: var(--accent);
  background: var(--accent-soft);
  color: #92660a;
}
.btn-outline:active {
  transform: scale(0.98);
  transition-duration: 0.08s;
}

.hero__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 32px;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 13px;
  border-radius: 999px;
  background: var(--bg-alt);
  border: 1.5px solid var(--border);
  font-size: 12px;
  font-weight: 500;
  color: var(--text-secondary);
}
.chip svg { color: var(--green); flex-shrink: 0; }

.hero__stats {
  display: inline-flex;
  align-items: center;
  gap: 22px;
  padding: 15px 26px;
  border-radius: 16px;
  background: var(--bg-alt);
  border: 1.5px solid var(--border);
  box-shadow: var(--shadow-card);
}

.stat {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}
.stat strong {
  font-size: 19px;
  font-weight: 800;
  color: var(--text);
  line-height: 1;
  letter-spacing: -0.02em;
}
.stat span {
  font-size: 10px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.07em;
}
.stat-divider {
  width: 1px;
  height: 30px;
  background: var(--border);
  flex-shrink: 0;
}

.hero__visual {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 460px;
}

.trophy-glow {
  position: absolute;
  width: 360px;
  height: 360px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(245, 200, 66, 0.28) 0%, rgba(245, 200, 66, 0.08) 60%, transparent 100%);
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 0;
}

.trophy-wrap {
  position: relative;
  z-index: 1;
  width: 280px;
  height: 340px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.trophy-svg {
  width: 220px;
  height: auto;
  filter: drop-shadow(0 24px 40px rgba(212, 160, 23, 0.30));
  animation: trophy-float 4s ease-in-out infinite;
}

@keyframes trophy-float {
  0%, 100% { transform: translateY(0); }
  50%       { transform: translateY(-12px); }
}

.float-badge {
  position: absolute;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 13px;
  border-radius: 12px;
  background: var(--card);
  border: 1.5px solid var(--border);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.10);
  font-size: 12px;
  font-weight: 700;
  color: var(--text);
  white-space: nowrap;
  animation: badge-float 5s ease-in-out infinite;
}
.float-badge--1 { top: 30px;    right: -20px; animation-delay: 0s; }
.float-badge--2 { bottom: 80px; left: -24px;  animation-delay: 1.5s; }
.float-badge--3 { bottom: 20px; right: 10px;  animation-delay: 0.8s; }

@keyframes badge-float {
  0%, 100% { transform: translateY(0); }
  50%       { transform: translateY(-6px); }
}

.dot { position: absolute; border-radius: 50%; }
.dot--1 { width: 14px; height: 14px; background: var(--accent); top: 20px;    left: 20px;  opacity: 0.7; }
.dot--2 { width: 10px; height: 10px; background: var(--green);  top: 80px;    right: 10px; opacity: 0.5; }
.dot--3 { width: 18px; height: 18px; background: var(--accent); bottom: 40px; left: 40px;  opacity: 0.4; }
.dot--4 { width: 8px;  height: 8px;  background: var(--green);  bottom: 100px; right: 30px; opacity: 0.6; }

@media (max-width: 900px) {
  .hero { padding: 88px 20px 64px; min-height: auto; }
  .hero__wrapper { grid-template-columns: 1fr; gap: 48px; text-align: center; }
  .hero__content { align-items: center; }
  .hero__title-highlight::after { display: none; }
  .hero__sub { max-width: 560px; }
  .hero__actions, .hero__chips { justify-content: center; }
  .hero__stats { flex-wrap: wrap; justify-content: center; gap: 16px; }
  .hero__visual { min-height: 300px; }
  .trophy-wrap { width: 220px; height: 280px; }
  .trophy-svg { width: 180px; }
  .float-badge--1 { right: -10px; }
  .float-badge--2 { left: -10px; }
}

@media (max-width: 540px) {
  .hero { padding: 80px 16px 56px; }
  .hero__title { font-size: 38px; }
  .hero__sub { font-size: 15px; }
  .btn-cta, .btn-outline { width: 100%; justify-content: center; }
  .hero__actions { width: 100%; }
  .hero__stats { width: 100%; }
  .stat-divider { display: none; }
  .hero__visual { display: none; }
}
</style>
