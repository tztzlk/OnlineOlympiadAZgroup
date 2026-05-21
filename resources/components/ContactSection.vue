<template>
  <section class="contact-section" ref="sectionRef" :class="{ visible: isVisible }">
    <div class="contact-inner">
      <div class="contact-card">
        <div class="contact-icon" aria-hidden="true">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.64 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.16 6.16l.97-.97a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18z"/>
          </svg>
        </div>

        <div class="contact-body">
          <span class="contact-eyebrow">Свяжитесь с нами</span>
          <h2 class="contact-title">Остались вопросы?</h2>
          <p class="contact-text">
            Наши специалисты готовы ответить на любые вопросы об олимпиаде, регистрации и сертификатах.
          </p>
        </div>

        <div class="contact-info">
          <a href="tel:87000330226" class="contact-phone">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.64 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.16 6.16l.97-.97a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.18z"/>
            </svg>
            8 (700) 033-02-26
          </a>
          <p class="contact-hours">Пн–Вс, с 9:00 до 21:00</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const sectionRef = ref(null)
const isVisible = ref(false)

onMounted(() => {
  const observer = new IntersectionObserver(
    ([entry]) => {
      if (entry.isIntersecting) {
        isVisible.value = true
        observer.disconnect()
      }
    },
    { threshold: 0.1 }
  )
  if (sectionRef.value) observer.observe(sectionRef.value)
})
</script>

<style scoped>
* { box-sizing: border-box; }

.contact-section {
  background: var(--bg-alt);
  padding: 72px 28px;
}

.contact-inner {
  max-width: 900px;
  margin: 0 auto;
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0s, transform 0s;
}

.contact-section.visible .contact-inner {
  opacity: 1;
  transform: translateY(0);
  transition: opacity 0.55s cubic-bezier(0.23, 1, 0.32, 1), transform 0.55s cubic-bezier(0.23, 1, 0.32, 1);
}

.contact-card {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 32px;
  padding: 40px 44px;
  border-radius: 28px;
  background: var(--card);
  border: 1px solid var(--surface-border);
  box-shadow: 0 4px 28px rgba(0, 0, 0, 0.06);
}

.contact-icon {
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: var(--green-soft);
  border: 1.5px solid rgba(22, 163, 74, 0.22);
  color: var(--green-strong);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.contact-eyebrow {
  display: inline-flex;
  padding: 5px 12px;
  border-radius: 999px;
  background: var(--accent-soft);
  border: 1.5px solid rgba(245, 200, 66, 0.35);
  color: #92660a;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.09em;
  text-transform: uppercase;
  margin-bottom: 10px;
}

.contact-title {
  font-size: 24px;
  font-weight: 800;
  color: var(--text);
  letter-spacing: -0.02em;
  margin: 0 0 8px;
}

.contact-text {
  font-size: 15px;
  line-height: 1.65;
  color: var(--text-secondary);
  margin: 0;
}

.contact-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  flex-shrink: 0;
}

.contact-phone {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  padding: 14px 24px;
  border-radius: 14px;
  background: var(--green);
  color: #ffffff;
  font-size: 18px;
  font-weight: 800;
  text-decoration: none;
  box-shadow: 0 8px 24px rgba(22, 163, 74, 0.28);
  transition: background 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
  letter-spacing: 0.01em;
}

.contact-phone:hover {
  background: var(--green-hover);
  transform: translateY(-2px);
  box-shadow: 0 14px 30px rgba(22, 163, 74, 0.32);
}

.contact-phone:active {
  transform: scale(0.98);
  transition-duration: 0.08s;
}

.contact-hours {
  font-size: 13px;
  color: var(--text-secondary);
  margin: 0;
  text-align: right;
}

@media (max-width: 900px) {
  .contact-card {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 24px;
    padding: 32px 28px;
  }

  .contact-icon {
    margin: 0 auto;
  }

  .contact-eyebrow {
    margin: 0 auto 10px;
  }

  .contact-info {
    align-items: center;
  }

  .contact-hours {
    text-align: center;
  }
}

@media (max-width: 540px) {
  .contact-section {
    padding: 56px 16px;
  }

  .contact-phone {
    width: 100%;
    justify-content: center;
    font-size: 16px;
  }
}
</style>
