<script setup>
import Footer from '../components/Footer.vue'
import Header from '../components/Header.vue'
import PublicOffer from '../components/PublicOffer.vue'
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const offerAccepted = ref(false)

const isAdminRoute = computed(() => route.path.startsWith('/admin'))
const isQuizRoute = computed(() => route.path.startsWith('/quiz/'))
const showChrome = computed(() => !isAdminRoute.value && !isQuizRoute.value)
const showOfferModal = computed(() => {
  if (typeof window === 'undefined') return false
  return !isQuizRoute.value && localStorage.getItem('offer_accepted') !== 'true' && !offerAccepted.value
})

const acceptOffer = () => {
  offerAccepted.value = true
}
</script>

<template>
  <div class="app-root">
    <Header v-if="showChrome" />

    <main class="app-main">
      <router-view />
    </main>

    <Footer v-if="showChrome" />
  </div>

  <Transition name="offer-modal">
    <div v-if="showOfferModal" class="offer-modal">
      <div class="offer-overlay"></div>
      <div class="offer-modal-content">
        <PublicOffer @accepted="acceptOffer" />
      </div>
    </div>
  </Transition>
</template>

<style>
.app-root {
  min-height: 100vh;
  background: var(--bg);
}

.app-main {
  flex: 1;
}

.offer-modal {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 32px 16px;
  overflow-y: auto;
}

.offer-overlay {
  position: fixed;
  inset: 0;
  background: var(--overlay);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.offer-modal-content {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 760px;
  background: transparent;
}

.offer-modal-enter-active {
  transition: opacity 0.35s ease;
}

.offer-modal-leave-active {
  transition: opacity 0.22s ease;
}

.offer-modal-enter-from,
.offer-modal-leave-to {
  opacity: 0;
}

.offer-modal-enter-active .offer-modal-content {
  animation: modalSlideIn 0.45s cubic-bezier(0.23, 1, 0.32, 1) both;
}

.offer-modal-leave-active .offer-modal-content {
  animation: modalSlideOut 0.22s ease both;
}

@keyframes modalSlideIn {
  from { opacity: 0; transform: translateY(24px) scale(0.95); }
  to   { opacity: 1; transform: translateY(0)   scale(1); }
}

@keyframes modalSlideOut {
  from { opacity: 1; transform: translateY(0)  scale(1); }
  to   { opacity: 0; transform: translateY(12px) scale(0.97); }
}
</style>
