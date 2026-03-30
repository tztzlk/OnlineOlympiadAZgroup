<template>
  <div class="offer-page">

    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>

    <div class="offer-card">

      <!-- Header -->
      <div class="offer-header">
        <div class="offer-header__icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M14 2v6h6M9 13h6M9 17h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </div>
        <h1 class="offer-title">Публичная оферта</h1>
        <p class="offer-subtitle">Внимательно ознакомьтесь с условиями перед подтверждением</p>
      </div>

      <!-- Content -->
      <div class="offer-content" ref="contentEl" @scroll="handleScroll">

        <p class="offer-intro">
          Используя сервис, вы принимаете условия публичной оферты. Внимательно ознакомьтесь с текстом документа перед подтверждением.
        </p>

        <div class="offer-section">
          <div class="offer-section__num">01</div>
          <div>
            <h3 class="offer-section__title">Общие положения</h3>
            <p>Использование сервиса означает полное согласие с правилами компании. Настоящая оферта является официальным предложением и содержит все существенные условия договора.</p>
          </div>
        </div>

        <div class="offer-section">
          <div class="offer-section__num">02</div>
          <div>
            <h3 class="offer-section__title">Права и обязанности</h3>
            <p>Пользователь обязуется соблюдать правила сервиса и действующее законодательство Республики Казахстан. Администрация сервиса вправе изменять условия оферты с уведомлением пользователей.</p>
          </div>
        </div>

        <div class="offer-section">
          <div class="offer-section__num">03</div>
          <div>
            <h3 class="offer-section__title">Оплата и возврат</h3>
            <p>Финансовые операции регулируются внутренними правилами сервиса. Возврат средств осуществляется в соответствии с действующим законодательством и условиями, установленными сервисом.</p>
          </div>
        </div>

        <div class="offer-section">
          <div class="offer-section__num">04</div>
          <div>
            <h3 class="offer-section__title">Персональные данные</h3>
            <p>Пользователь даёт согласие на обработку персональных данных в целях, предусмотренных настоящей офертой. Данные не передаются третьим лицам без явного согласия пользователя.</p>
          </div>
        </div>

        <div class="offer-section">
          <div class="offer-section__num">05</div>
          <div>
            <h3 class="offer-section__title">Ответственность сторон</h3>
            <p>Сервис не несёт ответственности за действия пользователей, нарушающих условия настоящей оферты. Пользователь несёт полную ответственность за достоверность предоставляемых данных.</p>
          </div>
        </div>

        <!-- Fade overlay at bottom -->
        <div class="content-fade" :class="{ hidden: scrolledToBottom }"></div>
      </div>

      <!-- Scroll hint -->
      <div class="scroll-hint" :class="{ hidden: scrolledToBottom }">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
          <path d="M2 5l5 5 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Прокрутите для ознакомления
      </div>

      <!-- Agreement -->
      <div class="offer-agreement">

        <label class="checkbox-wrap" :class="{ checked: agreed }">
          <input type="checkbox" v-model="agreed" />
          <span class="checkbox-custom">
            <svg v-if="agreed" width="12" height="12" viewBox="0 0 12 12" fill="none">
              <path d="M2 6l3 3 5-5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </span>
          <span class="checkbox-label">Я прочитал(а) и согласен(на) с условиями публичной оферты</span>
        </label>

        <button
          class="confirm-btn"
          :class="{ 'confirm-btn--active': agreed }"
          :disabled="!agreed || loading"
          @click="confirmOffer"
        >
          <span v-if="loading" class="btn-loader"></span>
          <template v-else-if="success">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Принято
          </template>
          <template v-else>
            Подтвердить и продолжить
            <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
              <path d="M3 7.5h9M9 4.5l3 3-3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </template>
        </button>

        <Transition name="msg">
          <p v-if="error" class="msg msg--error">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.3"/>
              <path d="M7 4.5v3M7 9.5v.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            {{ error }}
          </p>
        </Transition>

        <Transition name="msg">
          <p v-if="success" class="msg msg--success">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <circle cx="7" cy="7" r="6" stroke="currentColor" stroke-width="1.3"/>
              <path d="M4.5 7l2 2 3-3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Оферта успешно принята
          </p>
        </Transition>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const agreed = ref(false)
const loading = ref(false)
const error = ref('')
const success = ref(false)
const scrolledToBottom = ref(false)
const contentEl = ref(null)

const emit = defineEmits(['accepted'])

const handleScroll = () => {
  const el = contentEl.value
  if (!el) return
  scrolledToBottom.value = el.scrollTop + el.clientHeight >= el.scrollHeight - 16
}

const confirmOffer = async () => {
  if (!agreed.value) return
  loading.value = true
  error.value = ''
  await new Promise(r => setTimeout(r, 1200))
  localStorage.setItem('offer_accepted', 'true')
  emit('accepted')
  loading.value = false
  success.value = true
}
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.offer-page {
  min-height: 100vh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 48px 16px 64px;
  background: var(--bg);
  position: relative;
  overflow: hidden;
}

/* Orbs */
.bg-orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  pointer-events: none;
}
.bg-orb--1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(225,29,72,0.1), transparent 70%);
  top: -100px; right: -80px;
  opacity: 0.6;
}
.bg-orb--2 {
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(225,29,72,0.08), transparent 70%);
  bottom: -80px; left: -60px;
  opacity: 0.5;
}

/* Card */
.offer-card {
  position: relative;
  width: 100%;
  max-width: 740px;
  background: var(--surface);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid var(--surface-border);
  border-radius: 24px;
  padding: 44px 48px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
  animation: cardIn 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
}

@keyframes cardIn {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Header */
.offer-header {
  text-align: center;
  margin-bottom: 32px;
}

.offer-header__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 56px; height: 56px;
  background: rgba(225, 29, 72, 0.2);
  border-radius: 16px;
  color: #E11D48;
  border: 1px solid rgba(225, 29, 72, 0.3);
  box-shadow: 0 4px 16px rgba(225, 29, 72, 0.2);
  margin-bottom: 18px;
}

.offer-title {
  font-size: 32px;
  font-weight: 700;
  color: var(--text-on-surface);
  letter-spacing: -0.5px;
  margin-bottom: 8px;
}

.offer-subtitle {
  font-size: 13.5px;
  color: var(--text-muted-on-surface);
  font-weight: 300;
}

/* Content scroll area */
.offer-content {
  position: relative;
  max-height: 340px;
  overflow-y: auto;
  padding-right: 12px;
  margin-bottom: 6px;
  scrollbar-width: thin;
  scrollbar-color: rgba(225,29,72,0.3) var(--surface-soft);
}

.offer-content::-webkit-scrollbar { width: 4px; }
.offer-content::-webkit-scrollbar-track { background: var(--surface-soft); border-radius: 4px; }
.offer-content::-webkit-scrollbar-thumb { background: rgba(225,29,72,0.3); border-radius: 4px; }

.offer-intro {
  font-size: 14px;
  line-height: 1.75;
  color: var(--text-muted-on-surface);
  margin-bottom: 24px;
  padding: 16px 18px;
  background: rgba(225, 29, 72, 0.08);
  border-radius: 12px;
  border-left: 3px solid #E11D48;
}

/* Sections */
.offer-section {
  display: flex;
  gap: 18px;
  padding: 20px 0;
  border-bottom: 1px solid var(--surface-border);
}

.offer-section:last-of-type { border-bottom: none; }

.offer-section__num {
  font-size: 11px;
  font-weight: 500;
  color: #E11D48;
  letter-spacing: 0.5px;
  flex-shrink: 0;
  padding-top: 3px;
  width: 24px;
}

.offer-section__title {
  font-size: 14px;
  font-weight: 600;
  color: var(--text-on-surface);
  margin-bottom: 8px;
  letter-spacing: -0.1px;
}

.offer-section p {
  font-size: 13.5px;
  line-height: 1.7;
  color: var(--text-muted-on-surface);
  font-weight: 300;
}

/* Fade overlay */
.content-fade {
  position: sticky;
  bottom: 0;
  left: 0; right: 0;
  height: 48px;
  background: linear-gradient(to bottom, transparent, var(--surface));
  pointer-events: none;
  transition: opacity 0.3s;
}
.content-fade.hidden { opacity: 0; }

/* Scroll hint */
.scroll-hint {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 11.5px;
  color: var(--text-muted-on-surface);
  margin-bottom: 20px;
  transition: opacity 0.3s;
  animation: bounce 2s ease-in-out infinite;
}
.scroll-hint.hidden { opacity: 0; pointer-events: none; }

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50%       { transform: translateY(3px); }
}

/* Agreement */
.offer-agreement {
  border-top: 1px solid var(--surface-border);
  padding-top: 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* Checkbox */
.checkbox-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  padding: 14px 16px;
  border-radius: 12px;
  border: 1.5px solid var(--surface-border);
  background: var(--surface-soft);
  transition: border-color 0.2s, background 0.2s;
}

.checkbox-wrap.checked {
  border-color: rgba(225, 29, 72, 0.4);
  background: rgba(225, 29, 72, 0.1);
}

.checkbox-wrap input { display: none; }

.checkbox-custom {
  width: 20px; height: 20px;
  border-radius: 6px;
  border: 1.5px solid rgba(225, 29, 72, 0.4);
  background: transparent;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: background 0.2s, border-color 0.2s;
}

.checkbox-wrap.checked .checkbox-custom {
  background: #E11D48;
  border-color: #E11D48;
}

.checkbox-label {
  font-size: 13.5px;
  color: var(--text-on-surface);
  line-height: 1.4;
  font-weight: 400;
}

/* Button */
.confirm-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 14px;
  border: none;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  background: var(--surface-soft);
  color: var(--text-muted-on-surface);
  transition: all 0.25s;
  min-height: 50px;
}

.confirm-btn--active {
  background: #E11D48;
  color: white;
  box-shadow: 0 4px 16px rgba(225, 29, 72, 0.35);
}

.confirm-btn--active:hover {
  background: #BE123C;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(225, 29, 72, 0.45);
}

.confirm-btn:disabled { cursor: not-allowed; }

/* Loader */
.btn-loader {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Messages */
.msg {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  padding: 10px 14px;
  border-radius: 10px;
}

.msg--error  { background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; }
.msg--success { background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; }

.msg-enter-active, .msg-leave-active { transition: all 0.25s ease; }
.msg-enter-from, .msg-leave-to { opacity: 0; transform: translateY(-6px); }

@media (max-width: 600px) {
  .offer-card { padding: 28px 20px; }
  .offer-title { font-size: 26px; }
}
</style>
