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
          Выбрать олимпиаду
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="5" y1="12" x2="19" y2="12" />
            <polyline points="12 5 19 12 12 19" />
          </svg>
        </router-link>
      </div>

      <div class="hero__features">
        <div class="feature-chip">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12" />
          </svg>
          Полный доступ к заданиям
        </div>
        <div class="feature-chip">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12" />
          </svg>
          Мгновенный результат
        </div>
        <div class="feature-chip">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12" />
          </svg>
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
  justify-content: center;
  align-items: center;
  padding: 80px 28px 100px;
  overflow: hidden;
  color: white;
}

.hero__bg {
  position: absolute;
  inset: 0;
  background: url('/.png') center/cover no-repeat;
  z-index: 0;
}

.hero__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    rgba(22, 16, 10, 0.58) 0%,
    rgba(28, 20, 12, 0.34) 36%,
    rgba(22, 16, 10, 0.16) 62%,
    rgba(18, 13, 7, 0.34) 100%
  );
  z-index: 1;
}

.hero__content {
  position: relative;
  z-index: 3;
  width: min(1100px, 100%);
  max-width: 1100px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 24px 0;
  margin-left: 0;
}

.hero__title {
  max-width: 980px;
  font-size: clamp(48px, 6vw, 74px);
  font-weight: 700;
  color: #fff;
  line-height: 1.08;
  margin: 0;
  letter-spacing: -0.02em;
  text-shadow: 0 8px 28px rgba(0, 0, 0, 0.28);
}

.hero__subheadline {
  font-size: 20px;
  font-weight: 500;
  line-height: 1.55;
  color: rgba(255, 249, 236, 0.92);
  margin: 18px 0 0;
  max-width: 760px;
  text-shadow: 0 4px 18px rgba(0, 0, 0, 0.22);
}

.hero__price {
  margin: 20px 0 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 22px;
  border-radius: 999px;
  background: rgba(214, 176, 77, 0.18);
  border: 1px solid rgba(214, 176, 77, 0.28);
  color: #f1d27a;
  font-size: 17px;
  font-weight: 700;
  letter-spacing: 0.01em;
  backdrop-filter: blur(10px);
  box-shadow: 0 12px 26px rgba(0, 0, 0, 0.14);
}

.hero__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 14px;
  margin-top: 28px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 18px 40px;
  border-radius: 20px;
  background: linear-gradient(135deg, var(--accent) 0%, var(--accent-hover) 100%);
  color: #241a08;
  font-size: 18px;
  font-weight: 700;
  text-decoration: none;
  border: none;
  cursor: pointer;
  box-shadow: 0 18px 40px rgba(37, 26, 6, 0.24);
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
  justify-content: center;
  gap: 10px;
  margin-top: 32px;
}

.feature-chip {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 14px;
  font-weight: 500;
  color: rgba(255, 250, 238, 0.96);
  background: rgba(70, 52, 25, 0.26);
  padding: 10px 18px;
  border-radius: 999px;
  border: 1px solid rgba(255, 240, 204, 0.2);
  backdrop-filter: blur(12px);
}

.feature-chip svg { color: #8be0a7; flex-shrink: 0; }

.hero__stats {
  display: inline-flex;
  align-items: center;
  gap: 28px;
  background: rgba(122, 86, 45, 0.26);
  border: 1px solid rgba(255, 235, 202, 0.18);
  border-radius: 28px;
  padding: 18px 36px;
  margin-top: 40px;
  backdrop-filter: blur(16px);
  box-shadow: 0 18px 40px rgba(0, 0, 0, 0.16);
}

.hero-stat { display: flex; flex-direction: column; align-items: center; gap: 3px; }
.hero-stat__num { font-size: 22px; font-weight: 700; color: #fff8ea; line-height: 1; }
.hero-stat__label { font-size: 12px; color: rgba(255, 243, 220, 0.82); font-weight: 600; }
.hero-stat-divider { width: 1px; height: 36px; background: rgba(255, 240, 210, 0.18); }

@media (max-width: 768px) {
  .hero { padding: 80px 20px 90px; justify-content: center; }
  .hero__bg {
    background-position: 64% center;
    background-size: cover;
  }
  .hero__content {
    max-width: 100%;
    margin-left: 0;
    padding: 28px 0;
    text-align: center;
    align-items: center;
  }
  .hero__title { font-size: 36px; }
  .hero__subheadline { font-size: 16px; }
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
      rgba(21, 15, 9, 0.5) 0%,
      rgba(21, 15, 9, 0.56) 58%,
      rgba(21, 15, 9, 0.7) 100%
    );
  }
  .hero__content { padding: 24px 0; }
  .hero__title { font-size: 30px; }
  .btn-primary { width: 100%; justify-content: center; }
  .hero__actions { width: 100%; }
}
</style>
