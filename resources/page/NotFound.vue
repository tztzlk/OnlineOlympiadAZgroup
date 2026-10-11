<template>
  <div class="not-found">
    <div class="not-found__card">
      <div class="not-found__art" aria-hidden="true">
        <span class="not-found__code">404</span>
        <img
          class="not-found__kid ds-character"
          :src="'/images/characters/boy-map.webp'"
          width="434"
          height="760"
          alt=""
          decoding="async"
        >
      </div>
      <h1 v-title>Такой страницы нет</h1>
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
    radial-gradient(600px 320px at 50% 30%, color-mix(in srgb, var(--sun) 22%, transparent), transparent 70%),
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
  position: relative;
  display: grid;
  place-items: end center;
  width: min(100%, 420px);
  height: clamp(240px, 40vw, 320px);
  margin-bottom: 4px;
}

/* Крупные цифры за персонажем */
.not-found__code {
  position: absolute;
  inset: 0 0 auto;
  font-family: var(--font-display);
  font-size: clamp(110px, 24vw, 190px);
  font-weight: 700;
  line-height: 1;
  letter-spacing: -0.04em;
  color: var(--brand-soft);
  -webkit-text-stroke: 2px color-mix(in srgb, var(--brand) 30%, transparent);
  user-select: none;
}

.not-found__kid {
  position: relative;
  width: auto;
  height: clamp(240px, 40vw, 320px);
}


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
