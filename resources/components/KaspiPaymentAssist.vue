<template>
  <div class="kaspi-assist">
    <div class="kaspi-assist__main">
      <a :href="paymentUrl" target="_blank" rel="noopener noreferrer" class="kaspi-assist__button">
        <img :src="'/kaspi.png'" alt="" width="24" height="24" class="kaspi-assist__logo" />
        {{ isMobile ? mobileCta : desktopCta }}
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7M8 7h9v9"/></svg>
      </a>
      <span class="kaspi-assist__hint">{{ hint }}</span>
    </div>

    <div v-if="showQr" class="kaspi-assist__qr">
      <div class="kaspi-assist__qr-code">
        <QrcodeVue :value="paymentUrl" :size="140" level="M" render-as="svg" />
      </div>
      <div class="kaspi-assist__qr-copy">
        <strong>Оплата с телефона</strong>
        <p>Откройте Kaspi на телефоне и отсканируйте QR-код, чтобы быстро перейти к оплате.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import QrcodeVue from 'qrcode.vue'

const props = defineProps({
  paymentUrl: { type: String, required: true },
  hint: { type: String, default: 'Результат сразу после теста' },
  mobileCta: { type: String, default: 'Оплатить через Kaspi' },
  desktopCta: { type: String, default: 'Открыть ссылку оплаты' },
})

const isMobile = ref(false)
let mediaQuery = null

const syncViewport = () => {
  if (typeof window === 'undefined') return

  const narrow = window.matchMedia('(max-width: 820px)').matches
  const touchDevice = /Android|iPhone|iPad|iPod|Mobile/i.test(window.navigator.userAgent)
  isMobile.value = narrow || touchDevice
}

const showQr = computed(() => !isMobile.value && Boolean(props.paymentUrl))

onMounted(() => {
  if (typeof window === 'undefined') return

  syncViewport()
  mediaQuery = window.matchMedia('(max-width: 820px)')

  if (mediaQuery.addEventListener) {
    mediaQuery.addEventListener('change', syncViewport)
  } else {
    mediaQuery.addListener(syncViewport)
  }
})

onBeforeUnmount(() => {
  if (!mediaQuery) return

  if (mediaQuery.removeEventListener) {
    mediaQuery.removeEventListener('change', syncViewport)
  } else {
    mediaQuery.removeListener(syncViewport)
  }
})
</script>

<style scoped>
.kaspi-assist {
  display: grid;
  gap: 14px;
  width: 100%;
}

.kaspi-assist__main {
  display: grid;
  justify-items: start;
  gap: 6px;
}

.kaspi-assist__button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-height: 52px;
  padding: 12px 20px 12px 14px;
  border-radius: var(--radius-sm);
  background: #f14635;
  color: #ffffff;
  font-size: 16px;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 8px 20px rgba(241, 70, 53, 0.3);
  transition: background-color var(--dur) ease, transform var(--dur) var(--ease-out);
}

.kaspi-assist__button:hover { background: #dc3626; }
.kaspi-assist__button:active { transform: scale(0.98); }

.kaspi-assist__logo {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  background: #ffffff;
  object-fit: contain;
}

.kaspi-assist__hint {
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 500;
}

.kaspi-assist__qr {
  display: grid;
  grid-template-columns: auto minmax(0, 1fr);
  gap: 18px;
  align-items: center;
  padding: 16px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.kaspi-assist__qr-code {
  width: 156px;
  height: 156px;
  display: grid;
  place-items: center;
  border-radius: var(--radius-md);
  background: #ffffff;
  box-shadow: var(--shadow-xs);
}

.kaspi-assist__qr-copy strong {
  display: block;
  margin-bottom: 4px;
  font-size: 16px;
}

.kaspi-assist__qr-copy p {
  color: var(--text-secondary);
  font-size: 14.5px;
  line-height: 1.55;
}

@media (max-width: 640px) {
  .kaspi-assist__main { justify-items: stretch; }
  .kaspi-assist__button { width: 100%; }
}
</style>
