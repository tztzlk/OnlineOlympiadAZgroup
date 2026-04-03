<template>
  <section class="hero">

    <div class="hero__bg"></div>
    <div class="hero__overlay"></div>

    <div class="hero__content">

      <h1 class="hero__title">
        Начни участвовать<br/>
        <span class="hero__title-accent">в олимпиаде прямо сейчас</span>
      </h1>

      <p class="hero__subheadline">
        Выберите предмет, зарегистрируйтесь и пройдите олимпиаду онлайн. Результат - сразу после прохождения.
      </p>

      <div class="hero__actions">
        <router-link to="/subject" class="btn-primary">
          Выбрать олимпиаду
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
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 80px 28px 100px;
  overflow: hidden;
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
    rgba(33, 24, 14, 0.46) 0%,
    rgba(63, 51, 25, 0.32) 45%,
    rgba(18, 13, 7, 0.5) 100%
  );
  z-index: 1;
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

.hero__title {
  font-size: 56px;
  font-weight: 600;
  color: #fff;
  line-height: 1.2;
  margin: 0;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 16px rgba(0, 0, 0, 0.35);
}
.hero__title-accent {
  background: linear-gradient(135deg, #f3dc94, #d7b96a);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero__subheadline {
  font-size: 16px;
  font-weight: 400;
  line-height: 1.6;
  color: rgba(255, 248, 235, 0.94);
  margin: 16px 0 0;
  max-width: 520px;
  text-shadow: 0 1px 8px rgba(0, 0, 0, 0.2);
}

.hero__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 14px;
  margin-top: 24px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 15px 32px;
  border-radius: 16px;
  background: linear-gradient(135deg, #d2b261, #bea25a);
  color: #18120a;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  border: none;
  cursor: pointer;
  box-shadow: 0 10px 28px rgba(104, 79, 28, 0.28);
  transition: all 0.25s ease;
}
.btn-primary svg { transition: transform 0.25s; }
.btn-primary:hover { background: linear-gradient(135deg, #e1c679, #c7ab63); transform: translateY(-2px); box-shadow: 0 12px 32px rgba(104, 79, 28, 0.32); }
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
  font-size: 13px;
  font-weight: 500;
  color: rgba(255, 250, 241, 0.94);
  background: rgba(255, 248, 238, 0.12);
  padding: 8px 16px;
  border-radius: 20px;
  border: 1px solid rgba(243, 223, 174, 0.22);
}
.feature-chip svg { color: #94d5a3; flex-shrink: 0; }

.hero__stats {
  display: inline-flex;
  align-items: center;
  gap: 28px;
  background: rgba(255, 248, 235, 0.11);
  border: 1px solid rgba(243, 223, 174, 0.2);
  border-radius: 20px;
  padding: 18px 36px;
  margin-top: 40px;
  backdrop-filter: blur(12px);
}
.hero-stat { display: flex; flex-direction: column; align-items: center; gap: 3px; }
.hero-stat__num { font-size: 22px; font-weight: 600; color: #fff; line-height: 1; }
.hero-stat__label { font-size: 12px; color: rgba(255, 242, 220, 0.72); font-weight: 500; }
.hero-stat-divider { width: 1px; height: 36px; background: rgba(243, 223, 174, 0.18); }

@media (max-width: 768px) {
  .hero { padding: 80px 20px 90px; }
  .hero__bg {
    background-position: 64% center;
    background-size: cover;
  }
  .hero__title { font-size: 36px; }
  .hero__subheadline { font-size: 15px; }
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
      rgba(21, 15, 9, 0.4) 0%,
      rgba(21, 15, 9, 0.5) 58%,
      rgba(21, 15, 9, 0.62) 100%
    );
  }
  .hero__title { font-size: 30px; }
  .btn-primary { width: 100%; justify-content: center; }
  .hero__actions { width: 100%; }
}
</style>
