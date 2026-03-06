<template>
  <footer class="footer">
    <!-- Top decorative line -->
    <div class="footer__accent-line"></div>

    <div class="footer__container">

      <!-- Brand block -->
      <div class="footer__block footer__block--brand">
        <router-link to="/" class="footer__logo">
          <div class="logo-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
          </div>
          <span>Онлайн-олимпиада</span>
        </router-link>

        <p class="footer__desc">
          Современная образовательная платформа для школьников.
          Участвуйте в олимпиадах, развивайте знания и достигайте новых высот.
        </p>

        <div class="footer__socials">
          <a href="#" class="social-btn" aria-label="VK">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M15.07 2H8.93C3.33 2 2 3.33 2 8.93v6.14C2 20.67 3.33 22 8.93 22h6.14C20.67 22 22 20.67 22 15.07V8.93C22 3.33 20.67 2 15.07 2zm3.08 13.67h-1.5c-.57 0-.74-.45-1.76-1.48-.88-.87-1.27-.99-1.49-.99-.3 0-.39.08-.39.5v1.35c0 .36-.11.57-1.05.57-1.55 0-3.27-.94-4.48-2.69C6 10.77 5.5 8.87 5.5 8.48c0-.22.08-.43.5-.43h1.5c.37 0 .51.17.65.57.72 2.07 1.92 3.88 2.42 3.88.19 0 .27-.09.27-.57V9.77c-.06-1.02-.6-1.11-.6-1.47 0-.18.15-.36.38-.36h2.36c.32 0 .43.17.43.53v2.86c0 .32.14.43.23.43.19 0 .35-.11.7-.46 1.08-1.21 1.85-3.07 1.85-3.07.1-.22.28-.43.65-.43h1.5c.45 0 .55.23.45.53-.19.88-2.02 3.46-2.02 3.46-.16.26-.22.38 0 .67.16.22.68.67 1.03 1.08.64.73 1.13 1.34 1.26 1.76.11.41-.11.62-.52.62z"/></svg>
            VK
          </a>
          <a href="#" class="social-btn" aria-label="Telegram">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8l-1.7 8.02c-.12.56-.46.7-.93.43l-2.59-1.91-1.25 1.2c-.14.14-.26.26-.52.26l.18-2.61 4.72-4.26c.2-.18-.05-.28-.32-.1L7.9 14.49l-2.55-.8c-.55-.17-.56-.55.12-.82l9.95-3.84c.46-.17.86.11.72.77z"/></svg>
            Telegram
          </a>
          <a href="https://www.youtube.com/" class="social-btn" aria-label="YouTube">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M21.58 7.19c-.23-.87-.91-1.56-1.78-1.79C18.25 5 12 5 12 5s-6.25 0-7.8.4c-.87.23-1.55.92-1.78 1.79C2 8.75 2 12 2 12s0 3.25.42 4.81c.23.87.91 1.56 1.78 1.79C5.75 19 12 19 12 19s6.25 0 7.8-.4c.87-.23 1.55-.92 1.78-1.79C22 15.25 22 12 22 12s0-3.25-.42-4.81zM10 15V9l5.2 3-5.2 3z"/></svg>
            YouTube
          </a>
        </div>
      </div>

      <!-- Navigation -->
      <div class="footer__block">
        <div class="footer__title">
          <span>Навигация</span>
          <div class="title-line"></div>
        </div>
        <ul class="footer__list">
          <li>
            <router-link to="/" class="footer__link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              Главная
            </router-link>
          </li>
          <li>
            <router-link to="/subject" class="footer__link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
              Предметы
            </router-link>
          </li>
          <li>
            <router-link to="/rules" class="footer__link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              Правила
            </router-link>
          </li>
          <li>
            <router-link to="/results" class="footer__link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              Результаты
            </router-link>
          </li>
          <li>
            <router-link to="/profile" class="footer__link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Профиль
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Contacts -->
      <div class="footer__block">
        <div class="footer__title">
          <span>Контакты</span>
          <div class="title-line"></div>
        </div>
        <div class="footer__contacts">
          <a href="mailto:support@olympiad.kz" class="contact-item">
            <div class="contact-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
              <div class="contact-label">Email</div>
              <div class="contact-value">support@olympiad.kz</div>
            </div>
          </a>
          <a href="tel:+77770000000" class="contact-item">
            <div class="contact-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.99 15a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.92 4h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 11.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <div>
              <div class="contact-label">Телефон</div>
              <div class="contact-value">+7 (777) 000-00-00</div>
            </div>
          </a>
          <div class="contact-item">
            <div class="contact-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <div class="contact-label">Страна</div>
              <div class="contact-value">Казахстан</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom bar -->
    <div class="footer__bottom">
      <div class="footer__bottom-inner">
        <span>© 2026 Онлайн-олимпиада. Все права защищены.</span>
        <div class="footer__bottom-links">
          <a href="#">Политика конфиденциальности</a>
          <span class="divider">·</span>
          <a href="#">Условия использования</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap');

* { box-sizing: border-box; }

.footer {
  background: #0f0e1a;
  font-family: 'Manrope', sans-serif;
  position: relative;
  overflow: hidden;
}

/* Subtle background pattern */
.footer::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
    radial-gradient(circle at 80% 80%, rgba(167, 139, 250, 0.06) 0%, transparent 50%);
  pointer-events: none;
}

.footer__accent-line {
  height: 3px;
  background: linear-gradient(90deg, #818cf8, #6366f1, #4f46e5, #a78bfa, #818cf8);
  background-size: 200% 100%;
  animation: shimmer 5s linear infinite;
}
@keyframes shimmer {
  0% { background-position: 0% 0; }
  100% { background-position: 200% 0; }
}

.footer__container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 60px 28px 48px;
  display: grid;
  grid-template-columns: 1.6fr 1fr 1fr;
  gap: 48px;
  position: relative;
  z-index: 1;
}

/* Brand block */
.footer__block--brand { padding-right: 20px; }

.footer__logo {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  margin-bottom: 18px;
}
.logo-icon {
  width: 34px; height: 34px;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}
.footer__logo span {
  font-family: 'Playfair Display', serif;
  font-size: 18px;
  font-weight: 700;
  color: white;
}

.footer__desc {
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
  margin: 0 0 24px;
}

/* Socials */
.footer__socials {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
.social-btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 8px 14px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  color: #94a3b8;
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.2s;
}
.social-btn:hover {
  background: rgba(99, 102, 241, 0.15);
  border-color: rgba(99, 102, 241, 0.4);
  color: #a5b4fc;
  transform: translateY(-2px);
}

/* Section title */
.footer__title {
  margin-bottom: 20px;
}
.footer__title span {
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: white;
}
.title-line {
  width: 28px;
  height: 2px;
  background: linear-gradient(90deg, #6366f1, #a78bfa);
  border-radius: 2px;
  margin-top: 8px;
}

/* Nav list */
.footer__list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.footer__link {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: #64748b;
  font-size: 14px;
  font-weight: 500;
  padding: 8px 10px;
  border-radius: 10px;
  transition: all 0.2s;
}
.footer__link svg { color: #475569; flex-shrink: 0; transition: color 0.2s; }
.footer__link:hover {
  color: #a5b4fc;
  background: rgba(99, 102, 241, 0.08);
  padding-left: 14px;
}
.footer__link:hover svg { color: #6366f1; }
.footer__link.router-link-active {
  color: #a5b4fc;
}

/* Contacts */
.footer__contacts {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.contact-item {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  padding: 10px 12px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  transition: all 0.2s;
  cursor: default;
}
a.contact-item { cursor: pointer; }
a.contact-item:hover {
  background: rgba(99, 102, 241, 0.08);
  border-color: rgba(99, 102, 241, 0.25);
  transform: translateX(4px);
}
.contact-icon {
  width: 34px; height: 34px;
  background: rgba(99, 102, 241, 0.12);
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  color: #818cf8;
  flex-shrink: 0;
}
.contact-label {
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #475569;
  margin-bottom: 2px;
}
.contact-value {
  font-size: 14px;
  font-weight: 600;
  color: #cbd5e1;
}

/* Bottom bar */
.footer__bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.06);
  position: relative;
  z-index: 1;
}
.footer__bottom-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 18px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.footer__bottom span {
  font-size: 13px;
  color: #475569;
}
.footer__bottom-links {
  display: flex;
  align-items: center;
  gap: 10px;
}
.footer__bottom-links a {
  font-size: 13px;
  color: #475569;
  text-decoration: none;
  transition: color 0.2s;
}
.footer__bottom-links a:hover { color: #a5b4fc; }
.divider { color: #334155; }

/* Responsive */
@media (max-width: 900px) {
  .footer__container {
    grid-template-columns: 1fr 1fr;
    gap: 36px;
  }
  .footer__block--brand {
    grid-column: 1 / -1;
    padding-right: 0;
  }
}

@media (max-width: 600px) {
  .footer__container {
    grid-template-columns: 1fr;
    padding: 40px 20px 36px;
    gap: 32px;
  }
  .footer__block { text-align: center; }
  .footer__logo { justify-content: center; }
  .footer__socials { justify-content: center; }
  .title-line { margin: 8px auto 0; }
  .footer__link { justify-content: center; }
  .footer__link:hover { padding-left: 10px; }
  .contact-item { text-align: left; }
  .footer__bottom-inner { flex-direction: column; text-align: center; gap: 10px; }
}
</style>