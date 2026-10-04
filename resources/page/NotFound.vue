<template>
  <div class="not-found">
    <div class="not-found__card">
      <div class="not-found__art" aria-hidden="true">
        <span class="digit">4</span>
        <span class="digit digit--zero">
          <svg width="56" height="56" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
        </span>
        <span class="digit">4</span>
      </div>
      <h1>Такой страницы нет</h1>
      <p>Возможно, ссылка устарела или в адресе опечатка. Вернитесь на главную или выберите олимпиаду.</p>
      <div class="not-found__actions">
        <RouterLink to="/" class="ds-btn ds-btn-primary ds-btn-lg">На главную</RouterLink>
        <RouterLink to="/subject" class="ds-btn ds-btn-ghost ds-btn-lg">Выбрать олимпиаду</RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.not-found {
  min-height: 100dvh;
  display: grid;
  place-items: center;
  padding: calc(var(--header-h) + 24px) 20px 80px;
  background:
    radial-gradient(600px 320px at 50% 30%, color-mix(in srgb, var(--brand) 12%, transparent), transparent 70%),
    var(--bg);
}

.not-found__card {
  max-width: 560px;
  display: grid;
  justify-items: center;
  gap: 14px;
  text-align: center;
}

.not-found__art {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.digit {
  font-family: var(--font-display);
  font-size: clamp(88px, 18vw, 140px);
  font-weight: 700;
  line-height: 1;
  color: var(--brand);
}

.digit--zero {
  width: clamp(88px, 18vw, 130px);
  height: clamp(88px, 18vw, 130px);
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: var(--sun);
  color: #ffffff;
  animation: spin-slow 8s linear infinite;
}

@keyframes spin-slow { to { transform: rotate(360deg); } }

h1 { font-size: clamp(26px, 4vw, 36px); }

p {
  color: var(--text-secondary);
  font-size: 17px;
}

.not-found__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
  margin-top: 8px;
}
</style>
