<template>
  <section class="hero">
    <div class="hero__bg"></div>
    <div class="hero__overlay"></div>

    <div class="hero__content">
      <h1 class="hero__title">Докажи, что ты лучший</h1>

      <p class="hero__subheadline">
        Безопасная онлайн-олимпиада с сертификатом
      </p>

      <p class="hero__price">Участие: от 2 990 &#8376;</p>

      <div class="hero__actions">
        <router-link to="/subject" class="btn-primary">
          Начать бесплатно
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
          </svg>
        </router-link>
      </div>

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

      <div class="hero__stats">
        <div class="hero-stat">
          <span class="hero-stat__num">1 200+</span>
          <span class="hero-stat__label">участников</span>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <span class="hero-stat__num">98%</span>
          <span class="hero-stat__label">рекомендуют</span>
        </div>
        <div class="hero-stat-divider"></div>
        <div class="hero-stat">
          <span class="hero-stat__num">10 минут</span>
          <span class="hero-stat__label">до результата</span>
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
  min-height: 100vh;
  display: flex;
  justify-content: flex-start;
  align-items: center;
  padding: 80px 28px 100px;
  overflow: hidden;
  color: white;
}

.hero__bg {
  position: absolute;
  inset: 0;
  background: url('/welcome.png') center/cover no-repeat;
  z-index: 0;
}

.hero__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(30, 21, 12, 0.72) 0%,
    rgba(52, 40, 20, 0.48) 38%,
    rgba(18, 13, 7, 0.2) 62%,
    rgba(18, 13, 7, 0.44) 100%
  );
  z-index: 1;
}

.hero__content {
  position: relative;
  z-index: 3;
  max-width: 620px;
  text-align: left;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 36px 40px;
  margin-left: clamp(0px, 4vw, 48px);
  background: linear-gradient(180deg, rgba(245, 240, 232, 0.92), rgba(232, 223, 200, 0.84));
  border: 1px solid rgba(201, 168, 76, 0.18);
  border-radius: 32px;
  box-shadow: 0 24px 70px rgba(26, 18, 10, 0.22);
  backdrop-filter: blur(16px);
}

.hero__title {
  font-size: 56px;
  font-weight: 700;
  color: var(--text);
  line-height: 1.2;
  margin: 0;
  letter-spacing: -0.02em;
  text-shadow: none;
}

.hero__subheadline {
  font-size: 16px;
  font-weight: 400;
  line-height: 1.6;
  color: var(--text-secondary);
  margin: 16px 0 0;
  max-width: 520px;
  text-shadow: none;
}

.hero__price {
  margin: 18px 0 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 18px;
  border-radius: 999px;
  background: var(--info-soft);
  border: 1px solid rgba(26, 95, 168, 0.2);
  color: var(--info);
  font-size: 15px;
  font-weight: 700;
  letter-spacing: 0.01em;
  backdrop-filter: blur(10px);
}

.hero__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  gap: 14px;
  margin-top: 24px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 15px 32px;
  border-radius: 16px;
  background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
  color: var(--text);
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  border: none;
  cursor: pointer;
  box-shadow: 0 10px 28px rgba(139, 103, 20, 0.28);
  transition: all 0.25s ease;
}

.btn-primary svg { transition: transform 0.25s; }

.btn-primary:hover {
  background: linear-gradient(135deg, #d9b860, var(--accent));
  transform: translateY(-2px);
  box-shadow: 0 12px 32px rgba(139, 103, 20, 0.32);
}

.btn-primary:hover svg { transform: translateX(4px); }

.hero__features {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
  gap: 10px;
  margin-top: 32px;
}

.feature-chip {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text);
  background: rgba(245, 240, 232, 0.76);
  padding: 8px 16px;
  border-radius: 20px;
  border: 1px solid rgba(26, 95, 168, 0.14);
}

.feature-chip svg { color: var(--success-soft); flex-shrink: 0; }

.hero__stats {
  display: inline-flex;
  align-items: center;
  gap: 28px;
  background: rgba(245, 240, 232, 0.8);
  border: 1px solid rgba(26, 95, 168, 0.12);
  border-radius: 20px;
  padding: 18px 36px;
  margin-top: 40px;
  backdrop-filter: blur(12px);
}

.hero-stat { display: flex; flex-direction: column; align-items: center; gap: 3px; }
.hero-stat__num { font-size: 22px; font-weight: 700; color: var(--text); line-height: 1; }
.hero-stat__label { font-size: 12px; color: var(--text-secondary); font-weight: 600; }
.hero-stat-divider { width: 1px; height: 36px; background: rgba(26, 95, 168, 0.14); }

@media (max-width: 768px) {
  .hero { padding: 80px 20px 90px; justify-content: center; }
  .hero__bg {
    background-position: 64% center;
    background-size: cover;
  }
  .hero__content {
    max-width: 100%;
    margin-left: 0;
    padding: 28px 24px;
    text-align: center;
    align-items: center;
  }
  .hero__title { font-size: 36px; }
  .hero__subheadline { font-size: 15px; }
  .hero__actions, .hero__features { justify-content: center; }
  .hero__stats { gap: 16px; padding: 14px 22px; flex-wrap: wrap; justify-content: center; }
  .hero-stat-divider { display: none; }
}

@media (max-width: 480px) {
  .hero__bg {
    background-position: 68% center;
    background-size: auto 100%;
  }
  .hero__overlay {
    background: linear-gradient(
      180deg,
      rgba(21, 15, 9, 0.54) 0%,
      rgba(21, 15, 9, 0.58) 58%,
      rgba(21, 15, 9, 0.68) 100%
    );
  }
  .hero__content { padding: 24px 18px; border-radius: 24px; }
  .hero__title { font-size: 30px; }
  .btn-primary { width: 100%; justify-content: center; }
  .hero__actions { width: 100%; }
}
</style>
