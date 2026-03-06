<template>
  <section class="hero">

    <div class="hero__bg"></div>
    <div class="hero__overlay"></div>
    <div class="hero__grain"></div>

    <div class="hero__content">

      <div class="hero__badge">
        <div class="badge-dot"></div>
        Онлайн-олимпиада 2026
      </div>

      <h1 class="hero__title">
        Начни участвовать<br/>
        <span class="hero__title-accent">в олимпиаде прямо сейчас</span>
      </h1>

      <div class="hero__features">
        <div class="feature-chip">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Полный доступ к заданиям
        </div>
        <div class="feature-chip">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Мгновенный результат
        </div>
        <div class="feature-chip">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Интерактивный формат
        </div>
      </div>

      <div class="hero__actions">
        <router-link to="/subject" class="btn-primary">
          Выбрать олимпиаду
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
          </svg>
        </router-link>
        <router-link to="/rules" class="btn-ghost">
          Как это работает
        </router-link>
      </div>

      <div class="hero__stats">
        <div class="hero-stat">
          <span class="hero-stat__num">1 200+</span>
          <span class="hero-stat__label">участников</span>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <span class="hero-stat__num">2</span>
          <span class="hero-stat__label">предмета</span>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <span class="hero-stat__num">4.9 ★</span>
          <span class="hero-stat__label">средняя оценка</span>
        </div>
      </div>

    </div>

    <div class="hero__scroll">
      <div class="scroll-mouse">
        <div class="scroll-wheel"></div>
      </div>
    </div>

  </section>
</template>

<script>
export default { name: 'Hero' }
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; }

.hero {
  position: relative;
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 80px 28px 100px;
  overflow: hidden;
  font-family: 'Manrope', sans-serif;
  color: white;
}

.hero__bg {
  position: absolute;
  inset: 0;
  background: url('/public/welcome.png') center/cover no-repeat;
  z-index: 0;
}

.hero__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(15, 14, 40, 0.78) 0%,
    rgba(30, 27, 75, 0.65) 50%,
    rgba(15, 14, 40, 0.72) 100%
  );
  z-index: 1;
}

.hero__grain {
  position: absolute;
  inset: 0;
  z-index: 2;
  opacity: 0.04;
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
  background-repeat: repeat;
  background-size: 200px;
  pointer-events: none;
}

.hero__content {
  position: relative;
  z-index: 3;
  max-width: 780px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.hero__badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: white;
  background: rgba(255, 255, 255, 0.12);
  padding: 8px 18px;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.22);
  backdrop-filter: blur(8px);
  margin-bottom: 28px;
}
.badge-dot {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: #a5b4fc;
  box-shadow: 0 0 0 3px rgba(165, 180, 252, 0.3);
  animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot {
  0%, 100% { box-shadow: 0 0 0 3px rgba(165, 180, 252, 0.3); }
  50%       { box-shadow: 0 0 0 7px rgba(165, 180, 252, 0.1); }
}

.hero__title {
  font-family: 'Playfair Display', serif;
  font-size: 58px;
  font-weight: 700;
  color: white;
  line-height: 1.15;
  margin: 0 0 28px;
  letter-spacing: -0.5px;
  text-shadow: 0 2px 24px rgba(0, 0, 0, 0.4);
}
.hero__title-accent {
  background: linear-gradient(135deg, #a5b4fc, #c4b5fd);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero__features {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
  margin-bottom: 40px;
}
.feature-chip {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  background: rgba(255, 255, 255, 0.1);
  padding: 8px 16px;
  border-radius: 20px;
  border: 1px solid rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(6px);
}
.feature-chip svg { color: #a5b4fc; flex-shrink: 0; }

.hero__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 14px;
  margin-bottom: 52px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 15px 32px;
  border-radius: 16px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  border: none;
  cursor: pointer;
  box-shadow: 0 8px 24px rgba(99, 102, 241, 0.5);
  transition: all 0.25s ease;
}
.btn-primary svg { transition: transform 0.25s; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(99, 102, 241, 0.65); }
.btn-primary:hover svg { transform: translateX(4px); }

.btn-ghost {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 15px 28px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  border: 1px solid rgba(255, 255, 255, 0.25);
  cursor: pointer;
  backdrop-filter: blur(6px);
  transition: all 0.25s ease;
}
.btn-ghost:hover { background: rgba(255, 255, 255, 0.18); transform: translateY(-2px); }

.hero__stats {
  display: inline-flex;
  align-items: center;
  gap: 28px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.16);
  border-radius: 20px;
  padding: 18px 36px;
  backdrop-filter: blur(12px);
}
.hero-stat { display: flex; flex-direction: column; align-items: center; gap: 3px; }
.hero-stat__num { font-size: 22px; font-weight: 700; color: white; line-height: 1; }
.hero-stat__label { font-size: 12px; color: rgba(255,255,255,0.55); font-weight: 600; }
.hero-stat-divider { width: 1px; height: 36px; background: rgba(255,255,255,0.15); }

.hero__scroll {
  position: absolute;
  bottom: 32px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 3;
}
.scroll-mouse {
  width: 22px; height: 34px;
  border: 2px solid rgba(255,255,255,0.3);
  border-radius: 11px;
  display: flex;
  justify-content: center;
  padding-top: 6px;
}
.scroll-wheel {
  width: 3px; height: 7px;
  background: rgba(255,255,255,0.6);
  border-radius: 2px;
  animation: scroll-anim 2s ease-in-out infinite;
}
@keyframes scroll-anim {
  0% { transform: translateY(0); opacity: 1; }
  100% { transform: translateY(10px); opacity: 0; }
}

@media (max-width: 768px) {
  .hero { padding: 80px 20px 90px; }
  .hero__title { font-size: 36px; }
  .hero__stats { gap: 16px; padding: 14px 22px; flex-wrap: wrap; justify-content: center; }
  .hero-stat-divider { display: none; }
}
@media (max-width: 480px) {
  .hero__title { font-size: 30px; }
  .btn-primary, .btn-ghost { width: 100%; justify-content: center; }
  .hero__actions { width: 100%; }
}
</style>