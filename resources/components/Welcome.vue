<template>
  <!-- Заставка: сначала логотип, затем первый экран -->
  <Teleport to="body">
    <Transition name="intro">
      <div v-if="showIntro" class="hero-intro" aria-hidden="true">
        <div class="hero-intro__glow"></div>
        <div class="hero-intro__logo">
          <EurikaLogo size="lg" />
        </div>
        <p class="hero-intro__tagline">Онлайн-олимпиады для школьников</p>
      </div>
    </Transition>
  </Teleport>

  <section
    ref="heroRef"
    class="hero"
    :class="{ 'is-ready': ready }"
    aria-labelledby="hero-title"
    @pointermove="onPointerMove"
    @pointerleave="resetPointer"
  >
    <div class="hero__wrapper">
      <div class="hero__content">
        <span class="hero__eyebrow">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/>
          </svg>
          Онлайн-олимпиада
        </span>

        <h1 id="hero-title" class="hero__title" v-title>
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

        <!-- Дата берётся из опубликованных предметов; если её нет — плашка не показывается -->
        <div v-if="nextOlympiadLabel" class="hero__coming-soon" role="status">
          <span class="coming-soon__pulse" aria-hidden="true"></span>
          <span>Ближайшая олимпиада — <strong>{{ nextOlympiadLabel }}</strong> · Успейте зарегистрироваться!</span>
        </div>
      </div>

      <div class="hero__visual" aria-hidden="true">
        <div class="stage">
          <div class="stage__blob"></div>
          <div class="stage__ring"></div>
          <svg class="stage__sparks" viewBox="0 0 400 400" fill="none">
            <path d="M330 40l5 12 12 5-12 5-5 12-5-12-12-5 12-5z" fill="#ffc933"/>
            <path d="M58 150l4 9 9 4-9 4-4 9-4-9-9-4 9-4z" fill="#2b5bf5"/>
            <path d="M360 250l3 7 7 3-7 3-3 7-3-7-7-3 7-3z" fill="#ff7a59"/>
            <circle cx="80" cy="60" r="7" fill="#7c5cff" opacity=".8"/>
            <circle cx="40" cy="300" r="5" fill="#14b8a6"/>
          </svg>

          <!-- Двое детей двигаются независимо: обёртка — отклик на мышь, внутри — покачивание и «дыхание» -->
          <div class="stage__duo">
            <div class="kid kid--girl">
              <span class="kid__sway">
                <img
                  class="ds-character ds-anim-breathe"
                  :src="'/images/characters/hero-girl.webp'"
                  width="489"
                  height="971"
                  alt=""
                  fetchpriority="high"
                  decoding="async"
                >
              </span>
            </div>
            <div class="kid kid--boy">
              <span class="kid__sway">
                <img
                  class="ds-character ds-anim-breathe"
                  :src="'/images/characters/hero-boy.webp'"
                  width="433"
                  height="943"
                  alt=""
                  fetchpriority="high"
                  decoding="async"
                >
              </span>
            </div>
          </div>

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
            <span>Онлайн</span>
          </div>
          <div class="float-badge float-badge--3">
            <span class="float-badge__icon float-badge__icon--blue">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3 8-8"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg>
            </span>
            <span>Разбор ошибок</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default { name: 'Hero' }
</script>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import api from '../js/api'
import EurikaLogo from './EurikaLogo.vue'

const INTRO_KEY = 'eurika_intro_seen'
const INTRO_MS = 1700

// Заставка показывается один раз за сессию и не показывается при «уменьшить движение».
const reducedMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false
let introSeen = false
try { introSeen = sessionStorage.getItem(INTRO_KEY) === '1' } catch {}

const showIntro = ref(!reducedMotion && !introSeen)
const ready = ref(!showIntro.value)
const heroRef = ref(null)
const timers = []

// Отклик персонажей на мышь: координаты в CSS-переменных, обновление раз в кадр
let frame = 0
const setPointer = (x, y) => {
  cancelAnimationFrame(frame)
  frame = requestAnimationFrame(() => {
    heroRef.value?.style.setProperty('--px', x.toFixed(3))
    heroRef.value?.style.setProperty('--py', y.toFixed(3))
  })
}

const onPointerMove = (event) => {
  if (reducedMotion || event.pointerType !== 'mouse') return
  const rect = heroRef.value.getBoundingClientRect()
  setPointer((event.clientX - rect.left) / rect.width - 0.5, (event.clientY - rect.top) / rect.height - 0.5)
}

const resetPointer = () => setPointer(0, 0)

onMounted(() => {
  if (!showIntro.value) return
  try { sessionStorage.setItem(INTRO_KEY, '1') } catch {}
  timers.push(setTimeout(() => { showIntro.value = false }, INTRO_MS))
  timers.push(setTimeout(() => { ready.value = true }, INTRO_MS + 150))
})

onBeforeUnmount(() => {
  timers.forEach(clearTimeout)
  cancelAnimationFrame(frame)
})

const nextOlympiadLabel = ref('')

// Ближайшая дата старта среди опубликованных предметов (сегодня и позже).
onMounted(async () => {
  try {
    const { data } = await api.get('/subjects')
    const today = new Date().toISOString().slice(0, 10)
    const next = (Array.isArray(data) ? data : data?.data || [])
      .map((subject) => subject.start_date)
      .filter((date) => typeof date === 'string' && date.slice(0, 10) >= today)
      .sort()[0]

    if (next) {
      nextOlympiadLabel.value = new Date(`${next.slice(0, 10)}T00:00:00`)
        .toLocaleDateString('ru-RU', { day: 'numeric', month: 'long' })
    }
  } catch {
    // Без данных плашка просто не показывается.
  }
})
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

/* Заголовок появляется по словам (v-title), остальное — каскадом снизу */
.hero__content > :not(.hero__title) {
  animation: heroFadeUp 640ms var(--ease-out) both;
}
.hero__eyebrow       { animation-delay: 0ms; }
.hero__sub           { animation-delay: 300ms; }
.hero__actions       { animation-delay: 380ms; }
.hero__price         { animation-delay: 440ms; }
.hero__chips         { animation-delay: 500ms; }
.hero__coming-soon   { animation-delay: 560ms; }

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
  background: var(--sun-soft);
  color: var(--sun-ink);
  font-size: 14px;
  font-weight: 600;
}

.hero__eyebrow svg {
  color: #f0a800;
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
  transform-origin: 0 50%;
  animation: marker-in 700ms var(--ease-out) 650ms both;
}

@keyframes marker-in {
  from { transform: rotate(-1.2deg) scaleX(0); }
  to   { transform: rotate(-1.2deg) scaleX(1); }
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
  border-radius: var(--radius-pill);
  background: var(--danger-soft);
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
  width: min(100%, 520px);
  aspect-ratio: 1;
  display: grid;
  place-items: end center;
}

/* Солнечная «клякса» под персонажами, медленно меняет форму */
.stage__blob {
  position: absolute;
  inset: 12% 6% 4%;
  border-radius: 46% 54% 50% 50% / 52% 46% 54% 48%;
  background:
    radial-gradient(circle at 32% 28%, rgba(255, 255, 255, 0.55), transparent 50%),
    linear-gradient(160deg, #ffe07a 0%, #ffc933 55%, #ffb21a 100%);
  box-shadow: 0 40px 80px rgba(245, 170, 15, 0.32);
  animation: blob-morph 14s ease-in-out infinite;
}

.stage__ring {
  position: absolute;
  inset: 4% 0 -4%;
  border-radius: 50%;
  border: 2px dashed color-mix(in srgb, var(--brand) 30%, transparent);
  animation: ring-spin 60s linear infinite;
}

.stage__sparks {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  animation: badge-float 7s ease-in-out infinite;
}

/* Пара детей: пропорции исходной картинки 884×971, каждый ребёнок на своём месте */
.stage__duo {
  --px: 0;
  --py: 0;
  position: relative;
  width: 92%;
  aspect-ratio: 884 / 971;
  margin-bottom: -2%;
  transform-origin: 50% 100%;
  animation: kids-in 900ms cubic-bezier(0.2, 0.9, 0.25, 1.2) 200ms both;
}

.kid {
  position: absolute;
  bottom: 0;
  transition: transform 600ms var(--ease-out);
}

.kid--girl {
  left: 0;
  width: 55.3%;
  transform: translate(calc(var(--px, 0) * 22px), calc(var(--py, 0) * 12px));
}

.kid--boy {
  left: 51%;
  width: 49%;
  transform: translate(calc(var(--px, 0) * 12px), calc(var(--py, 0) * 7px));
}

.kid__sway { display: block; }
.kid img { width: 100%; height: auto; }

/* Разные периоды и фазы — дети двигаются не синхронно */
.kid--girl img { animation-duration: 3.4s; }
.kid--boy img { animation-duration: 3.9s; animation-delay: -1.1s; }

.stage__blob,
.stage__sparks {
  translate: calc(var(--px, 0) * -14px) calc(var(--py, 0) * -10px);
  transition: translate 700ms var(--ease-out);
}

/* Пока идёт заставка, анимации первого экрана стоят на старте */
.hero:not(.is-ready),
.hero:not(.is-ready) :deep(*) {
  animation-play-state: paused !important;
}

.hero:not(.is-ready) :deep(.tr-word) {
  transition: none;
  transform: translateY(110%) rotate(4deg);
  opacity: 0;
}

/* ---- Заставка ---- */
.hero-intro {
  position: fixed;
  inset: 0;
  z-index: var(--z-toast);
  display: grid;
  place-content: center;
  justify-items: center;
  gap: 18px;
  background: var(--bg);
}

.hero-intro__glow {
  position: absolute;
  left: 50%;
  top: 50%;
  width: min(70vw, 560px);
  aspect-ratio: 1;
  border-radius: 50%;
  background: radial-gradient(circle, color-mix(in srgb, var(--sun) 40%, transparent) 0%, transparent 65%);
  transform: translate(-50%, -50%) scale(0.6);
  animation: intro-glow 1600ms var(--ease-out) both;
}

.hero-intro__logo {
  position: relative;
  transform: scale(1.6);
}

.hero-intro__tagline {
  position: relative;
  margin-top: 34px;
  color: var(--text-secondary);
  font-size: 16px;
  font-weight: 500;
  animation: intro-tagline 600ms var(--ease-out) 800ms both;
}

@keyframes intro-glow {
  to { transform: translate(-50%, -50%) scale(1.15); opacity: 0.9; }
}

@keyframes intro-tagline {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: none; }
}

.intro-leave-active { transition: opacity 450ms ease, visibility 450ms; }
.intro-leave-active .hero-intro__logo { transition: transform 450ms var(--ease-in-out); }
.intro-leave-to { opacity: 0; }
.intro-leave-to .hero-intro__logo { transform: scale(1.1) translateY(-24px); }

@keyframes blob-morph {
  0%, 100% { border-radius: 46% 54% 50% 50% / 52% 46% 54% 48%; }
  50%      { border-radius: 56% 44% 42% 58% / 44% 56% 46% 54%; }
}

@keyframes ring-spin { to { transform: rotate(360deg); } }

@keyframes kids-in {
  from { opacity: 0; transform: translateY(40px) scale(0.92); }
  to   { opacity: 1; transform: none; }
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

.float-badge { z-index: 2; }
.float-badge--1 { top: 18%; right: -2%; animation-delay: 0s; }
.float-badge--2 { top: 46%; left: -6%; animation-delay: 1.4s; }
.float-badge--3 { bottom: 8%; right: 2%; animation-delay: 0.7s; }

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

/* ---- Адаптив ---- */
@media (max-width: 960px) {
  .hero { padding-top: calc(var(--header-h) + 32px); }
  .hero__wrapper { grid-template-columns: 1fr; gap: 40px; }
  .hero__visual { order: -1; }
  .stage { width: min(80vw, 400px); }
  .float-badge { font-size: 13px; }
  .float-badge--2 { left: -2%; }
}

@media (max-width: 600px) {
  .hero { padding: calc(var(--header-h) + 20px) 16px 40px; }
  .hero__title { font-size: clamp(32px, 9vw, 40px); }
  .hero__actions { width: 100%; }
  .hero__actions .ds-btn { flex: 1 1 100%; }
  .hero__coming-soon { width: 100%; font-size: 14px; }
  .stage { width: min(86vw, 320px); }
  .float-badge { padding: 6px 10px 6px 6px; font-size: 12px; }
  .float-badge__icon { width: 24px; height: 24px; }
  .float-badge--1 { right: -4%; }
  .float-badge--2 { left: -4%; }
}
</style>
