<script setup>
import Footer from '../components/Footer.vue';
import Header from '../components/Header.vue';
import PublicOffer from '../components/PublicOffer.vue';

import { useRoute } from 'vue-router'
import { computed, ref } from 'vue'

const route = useRoute()

const showHeader = computed(() => {
  return !route.path.startsWith('/admin')
})

/* Оферта */
const showOfferModal = ref(true)

const acceptOffer = () => {
  showOfferModal.value = false
}
</script>

<template>
  <div class="app-root">
    <Header v-if="showHeader" />

    <main class="app-main">
      <router-view />
    </main>

    <Footer v-if="showHeader" />
  </div>

  <!-- Модальное окно оферты -->
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
  background: color-mix(in srgb, var(--bg) 75%, transparent);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.offer-modal-content {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 760px;
  /* Убираем лишний белый фон — карточка стеклянная внутри */
  background: transparent;
}

/* Анимация появления */
.offer-modal-enter-active {
  transition: opacity 0.35s ease;
}
.offer-modal-leave-active {
  transition: opacity 0.25s ease;
}
.offer-modal-enter-from,
.offer-modal-leave-to {
  opacity: 0;
}

.offer-modal-enter-active .offer-modal-content {
  animation: modalSlideIn 0.4s cubic-bezier(0.22, 1, 0.36, 1) both;
}
.offer-modal-leave-active .offer-modal-content {
  animation: modalSlideOut 0.25s ease both;
}

@keyframes modalSlideIn {
  from { opacity: 0; transform: translateY(20px) scale(0.98); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes modalSlideOut {
  from { opacity: 1; transform: translateY(0) scale(1); }
  to   { opacity: 0; transform: translateY(10px) scale(0.98); }
}
</style>