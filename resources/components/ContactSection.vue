<template>
  <section class="contact" aria-labelledby="contact-title">
    <div class="contact__card" v-reveal>
      <svg class="contact__decor" viewBox="0 0 320 320" fill="none" aria-hidden="true">
        <circle cx="260" cy="60" r="90" fill="currentColor" opacity=".08"/>
        <circle cx="300" cy="250" r="50" fill="currentColor" opacity=".06"/>
        <path d="M210 40l5 11 11 5-11 5-5 11-5-11-11-5 11-5z" fill="#ffc933"/>
      </svg>

      <div class="contact__icon" aria-hidden="true">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.64 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.16 6.16l.97-.97a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18z"/>
        </svg>
      </div>

      <div class="contact__body">
        <span class="contact__eyebrow">Свяжитесь с нами</span>
        <h2 id="contact-title" class="contact__title">Остались вопросы?</h2>
        <p class="contact__text">
          Наши специалисты готовы ответить на любые вопросы об олимпиаде, регистрации и сертификатах.
        </p>
      </div>

      <div class="contact__info">
        <a href="tel:87000330226" class="contact__phone">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.64 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.16 6.16l.97-.97a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18z"/>
          </svg>
          8 (700) 033-02-26
        </a>
        <p class="contact__hours">Пн–Вс, с 9:00 до 21:00</p>
      </div>
    </div>
  </section>
</template>

<style scoped>
.contact {
  padding: 0 24px clamp(72px, 10vw, 112px);
}

.contact__card {
  position: relative;
  overflow: hidden;
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: auto minmax(0, 1fr) auto;
  align-items: center;
  gap: 32px;
  padding: clamp(28px, 4vw, 48px);
  border-radius: var(--radius-xl);
  background:
    radial-gradient(500px 300px at 0% 100%, rgba(255, 201, 51, 0.18), transparent 70%),
    linear-gradient(155deg, #1d2a5c 0%, #111a33 100%);
  color: #ffffff;
  box-shadow: var(--shadow-lg);
}

.contact__decor {
  position: absolute;
  top: 0;
  right: 0;
  width: 320px;
  height: 320px;
  color: #ffffff;
  pointer-events: none;
}

.contact__icon {
  position: relative;
  width: 68px;
  height: 68px;
  display: grid;
  place-items: center;
  border-radius: 20px;
  background: var(--sun);
  color: #1f1600;
  transform: rotate(-6deg);
}

.contact__body { position: relative; }

.contact__eyebrow {
  display: inline-block;
  margin-bottom: 8px;
  color: var(--sun);
  font-size: 14px;
  font-weight: 600;
}

.contact__title {
  margin-bottom: 8px;
  color: #ffffff;
  font-size: clamp(24px, 2.8vw, 32px);
}

.contact__text {
  max-width: 52ch;
  color: rgba(255, 255, 255, 0.78);
  font-size: 16px;
  line-height: 1.6;
}

.contact__info {
  position: relative;
  display: grid;
  justify-items: end;
  gap: 8px;
}

.contact__phone {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  min-height: 56px;
  padding: 14px 24px;
  border-radius: var(--radius-md);
  background: #ffffff;
  color: #111a33;
  font-size: 18px;
  font-weight: 700;
  text-decoration: none;
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
  transition: transform var(--dur) var(--ease-out), background-color var(--dur) ease;
}

.contact__phone svg { color: var(--brand); }

@media (hover: hover) and (pointer: fine) {
  .contact__phone:hover { background: var(--sun); transform: translateY(-2px); }
  .contact__phone:hover svg { color: #1f1600; }
}

.contact__phone:active { transform: scale(0.98); }

.contact__hours {
  color: rgba(255, 255, 255, 0.66);
  font-size: 14px;
}

@media (max-width: 900px) {
  .contact__card { grid-template-columns: 1fr; gap: 22px; }
  .contact__info { justify-items: start; }
}

@media (max-width: 540px) {
  .contact { padding: 0 16px 96px; }
  .contact__phone { width: 100%; justify-content: center; }
  .contact__info { justify-items: stretch; }
  .contact__hours { text-align: center; }
}
</style>
