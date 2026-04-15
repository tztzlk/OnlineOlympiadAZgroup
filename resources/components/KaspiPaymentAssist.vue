<template>
  <div class="kaspi-assist">
    <div class="kaspi-assist__main">
      <a :href="paymentUrl" target="_blank" rel="noopener" class="kaspi-assist__button">
        {{ isMobile ? mobileCta : desktopCta }}
      </a>
      <span class="kaspi-assist__hint">{{ hint }}</span>
    </div>

    <div v-if="showQr" class="kaspi-assist__qr">
      <div class="kaspi-assist__qr-code">
        <QrcodeVue :value="paymentUrl" :size="148" level="M" render-as="svg" />
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
}

.kaspi-assist__main {
  display: grid;
  gap: 6px;
}

.kaspi-assist__button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
  padding: 12px 18px;
  border-radius: 14px;
  text-decoration: none;
  font-weight: 700;
  background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%);
  color: var(--text);
}

.kaspi-assist__hint {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
}

.kaspi-assist__qr {
  display: grid;
  grid-template-columns: 164px 1fr;
  gap: 16px;
  align-items: center;
  padding: 16px;
  border-radius: 18px;
  border: 1px solid rgba(201,171,99,.24);
  background: rgba(255,252,244,.82);
}

.kaspi-assist__qr-code {
  width: 164px;
  height: 164px;
  display: grid;
  place-items: center;
  border-radius: 18px;
  background: white;
  box-shadow: inset 0 0 0 1px rgba(201,171,99,.18);
}

.kaspi-assist__qr-copy strong {
  display: block;
  margin-bottom: 6px;
  color: var(--text);
}

.kaspi-assist__qr-copy p {
  margin: 0;
  color: var(--text-secondary);
  line-height: 1.6;
}

@media (max-width: 640px) {
  .kaspi-assist__button {
    width: 100%;
  }
}
</style>
