<template>
  <div class="auth-shell">
    <div class="auth-shell__grid">
      <section class="auth-shell__form" :class="{ 'is-wide': wide }">
        <slot />
      </section>

      <aside class="auth-shell__art" aria-hidden="true">
        <svg class="art-stars" viewBox="0 0 400 520" fill="none">
          <circle cx="70" cy="80" r="5" fill="#fff" opacity=".5"/>
          <circle cx="330" cy="140" r="3.5" fill="#fff" opacity=".45"/>
          <circle cx="300" cy="430" r="6" fill="#fff" opacity=".3"/>
          <circle cx="60" cy="400" r="3" fill="#fff" opacity=".5"/>
          <path d="M320 60l5 11 11 5-11 5-5 11-5-11-11-5 11-5z" fill="#ffc933"/>
          <path d="M80 240l4 8 8 4-8 4-4 8-4-8-8-4 8-4z" fill="#ffc933" opacity=".85"/>
          <path d="M340 300l3 6 6 3-6 3-3 6-3-6-6-3 6-3z" fill="#fff" opacity=".7"/>
        </svg>

        <div class="art-cards">
          <div class="art-card art-card--q">
            <span class="art-dot">A</span>
            <span class="art-line"></span>
          </div>
          <div class="art-card art-card--q is-picked">
            <span class="art-dot">B</span>
            <span class="art-line"></span>
            <span class="art-check">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
            </span>
          </div>
          <div class="art-card art-card--q">
            <span class="art-dot">C</span>
            <span class="art-line art-line--short"></span>
          </div>
        </div>

        <div class="art-medal">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
        </div>

        <div class="art-brand">
          <span class="art-brand__mark">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
          </span>
          Eurika!
        </div>
      </aside>
    </div>
  </div>
</template>

<script setup>
defineProps({
  wide: { type: Boolean, default: false },
})
</script>

<style scoped>
.auth-shell {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 24px) 20px 40px;
  background:
    radial-gradient(700px 400px at 0% 100%, color-mix(in srgb, var(--sun) 16%, transparent), transparent 70%),
    var(--bg);
}

.auth-shell__grid {
  max-width: 1080px;
  min-height: min(680px, calc(100dvh - var(--header-h) - 64px));
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 0.85fr);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
}

.auth-shell__form {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: clamp(28px, 5vw, 56px);
}

.auth-shell__form > :deep(*) {
  width: 100%;
  max-width: 420px;
}

.auth-shell__form.is-wide > :deep(*) { max-width: 560px; }

/* ---- Декоративная панель ---- */
.auth-shell__art {
  position: relative;
  overflow: hidden;
  display: grid;
  place-items: center;
  background:
    radial-gradient(circle at 30% 20%, rgba(255, 255, 255, 0.18), transparent 50%),
    linear-gradient(160deg, #3d6cff 0%, #2b5bf5 45%, #1d3fc0 100%);
}

.art-stars {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
}

.art-cards {
  position: relative;
  width: min(78%, 300px);
  display: grid;
  gap: 12px;
  transform: rotate(-4deg);
}

.art-card {
  display: flex;
  align-items: center;
  gap: 12px;
  height: 58px;
  padding: 0 14px;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.14);
  border: 1.5px solid rgba(255, 255, 255, 0.22);
  animation: artFloat 6s ease-in-out infinite;
}

.art-card:nth-child(2) { animation-delay: 0.6s; }
.art-card:nth-child(3) { animation-delay: 1.2s; }

.art-card.is-picked {
  background: #ffffff;
  border-color: #ffffff;
  box-shadow: 0 18px 40px rgba(10, 25, 90, 0.35);
  transform: translateX(14px);
}

.art-dot {
  width: 32px;
  height: 32px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
  font-size: 14px;
  font-weight: 700;
}

.is-picked .art-dot { background: #2b5bf5; }

.art-line {
  flex: 1;
  height: 10px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.3);
}

.art-line--short { flex: 0.6; }
.is-picked .art-line { background: #dfe6ff; }

.art-check {
  width: 26px;
  height: 26px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: #12a065;
  color: #ffffff;
}

.art-medal {
  position: absolute;
  top: 16%;
  right: 14%;
  width: 84px;
  height: 84px;
  display: grid;
  place-items: center;
  border-radius: 26px;
  background: #ffc933;
  color: #ffffff;
  box-shadow: 0 18px 36px rgba(10, 25, 90, 0.3);
  transform: rotate(10deg);
  animation: medal 5s ease-in-out infinite;
}

.art-brand {
  position: absolute;
  left: 28px;
  bottom: 24px;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: #ffffff;
  font-family: var(--font-display);
  font-size: 18px;
  font-weight: 700;
}

.art-brand__mark {
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  border-radius: 11px;
  background: #ffffff;
  color: #2b5bf5;
}

@keyframes artFloat {
  0%, 100% { translate: 0 0; }
  50%      { translate: 0 -6px; }
}

@keyframes medal {
  0%, 100% { transform: rotate(10deg) translateY(0); }
  50%      { transform: rotate(4deg) translateY(-10px); }
}

@media (max-width: 860px) {
  .auth-shell__grid { grid-template-columns: 1fr; min-height: 0; }
  .auth-shell__art { display: none; }
  .auth-shell__form { align-items: center; }
}

@media (max-width: 520px) {
  .auth-shell { padding: calc(var(--header-h) + 12px) 12px 96px; }
  .auth-shell__form { padding: 24px 18px; }
}
</style>
