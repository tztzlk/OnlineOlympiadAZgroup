<template>
  <span class="eurika-logo" :class="[`eurika-logo--${size}`, { 'is-inverse': inverse, 'is-animated': animated }]">
    <svg class="eurika-logo__mark" viewBox="0 0 52 52" fill="none" aria-hidden="true">
      <rect x="2" y="6" width="44" height="44" rx="15" class="eurika-logo__tile" />
      <!-- Буква «e»: перекладина и дуга почти на полный круг -->
      <path
        class="eurika-logo__e"
        d="M14.5 28.5H33.5A9.6 9.6 0 1 0 30.6 35.3"
        stroke-width="5.4"
        stroke-linecap="round"
        stroke-linejoin="round"
        pathLength="100"
      />
      <!-- Искра озарения — момент «Эврика!» -->
      <path
        class="eurika-logo__spark"
        d="M42 1.5l2.1 5.9 5.9 2.1-5.9 2.1L42 17.5l-2.1-5.9-5.9-2.1 5.9-2.1z"
      />
    </svg>
    <span v-if="withText" class="eurika-logo__word">Eurika<span class="eurika-logo__bang">!</span></span>
  </span>
</template>

<script setup>
defineProps({
  size: { type: String, default: 'md' },
  withText: { type: Boolean, default: true },
  inverse: { type: Boolean, default: false },
  animated: { type: Boolean, default: true },
})
</script>

<style scoped>
.eurika-logo {
  --logo-mark: 40px;
  --logo-text: 20px;

  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: var(--text);
  line-height: 1;
}

.eurika-logo--sm { --logo-mark: 32px; --logo-text: 17px; gap: 8px; }
.eurika-logo--lg { --logo-mark: 56px; --logo-text: 28px; gap: 14px; }

.eurika-logo__mark {
  width: var(--logo-mark);
  height: var(--logo-mark);
  flex-shrink: 0;
  overflow: visible;
  transition: transform 420ms var(--ease-out);
}

.eurika-logo__tile { fill: var(--brand); }
.eurika-logo__e { stroke: #ffffff; }
.eurika-logo__spark {
  fill: var(--sun);
  transform-box: fill-box;
  transform-origin: center;
}

.is-inverse { color: #ffffff; }
.is-inverse .eurika-logo__tile { fill: #ffffff; }
.is-inverse .eurika-logo__e { stroke: var(--brand); }

.eurika-logo__word {
  font-family: var(--font-display);
  font-size: var(--logo-text);
  font-weight: 700;
  letter-spacing: -0.035em;
  white-space: nowrap;
}

.eurika-logo__bang {
  display: inline-block;
  margin-left: 1px;
  color: var(--sun);
  -webkit-text-stroke: 0.5px color-mix(in srgb, var(--sun-ink) 35%, transparent);
  transform-origin: 50% 90%;
}

/* Появление: буква прорисовывается, искра вспыхивает, «!» подпрыгивает */
.is-animated .eurika-logo__e {
  stroke-dasharray: 100;
  animation: logo-draw 900ms var(--ease-out) 120ms both;
}
.is-animated .eurika-logo__spark { animation: logo-spark 700ms var(--ease-out) 700ms both; }
.is-animated .eurika-logo__bang { animation: logo-bang 600ms var(--ease-out) 900ms both; }

@media (hover: hover) and (pointer: fine) {
  a:hover > .eurika-logo .eurika-logo__mark,
  .eurika-logo:hover .eurika-logo__mark { transform: rotate(-6deg) scale(1.05); }
  a:hover > .eurika-logo .eurika-logo__spark,
  .eurika-logo:hover .eurika-logo__spark { animation: logo-twinkle 900ms var(--ease-out); }
}

@keyframes logo-draw {
  from { stroke-dashoffset: 100; }
  to   { stroke-dashoffset: 0; }
}

@keyframes logo-spark {
  0%   { opacity: 0; transform: scale(0) rotate(-90deg); }
  70%  { opacity: 1; transform: scale(1.25) rotate(10deg); }
  100% { opacity: 1; transform: scale(1) rotate(0); }
}

@keyframes logo-twinkle {
  0%, 100% { transform: scale(1) rotate(0); }
  45%      { transform: scale(1.35) rotate(45deg); }
}

@keyframes logo-bang {
  0%   { opacity: 0; transform: translateY(-40%) scaleY(1.2); }
  60%  { opacity: 1; transform: translateY(6%) scaleY(0.9); }
  100% { opacity: 1; transform: none; }
}
</style>
