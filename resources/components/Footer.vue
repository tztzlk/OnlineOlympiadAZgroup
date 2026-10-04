<template>
  <footer class="footer">
    <div class="footer__container">
      <div class="footer__brand">
        <router-link to="/" class="footer__logo" aria-label="Eurika — на главную">
          <span class="footer__mark" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9z"/></svg>
          </span>
          <span>Eurika!</span>
        </router-link>

        <p class="footer__desc">
          Eurika! — онлайн-олимпиады по математике и другим предметам. Мгновенный результат, сертификат и разбор ошибок.
        </p>

        <div class="footer__chips">
          <router-link to="/help-desk" class="footer__chip">Help Desk</router-link>
          <router-link to="/rules" class="footer__chip">Правила</router-link>
          <router-link to="/subject" class="footer__chip">Олимпиады</router-link>
        </div>
      </div>

      <nav class="footer__block" aria-labelledby="footer-nav-title">
        <h2 id="footer-nav-title" class="footer__title">Навигация</h2>
        <ul class="footer__list">
          <li><router-link to="/" class="footer__link">Главная</router-link></li>
          <li><router-link to="/subject" class="footer__link">Предметы</router-link></li>
          <li><router-link to="/results" class="footer__link">Результаты</router-link></li>
          <li><router-link to="/forgot-password" class="footer__link">Восстановить пароль</router-link></li>
          <li><router-link to="/help-desk" class="footer__link">Обратная связь</router-link></li>
        </ul>
      </nav>

      <div class="footer__block">
        <h2 class="footer__title">Контакты</h2>
        <div class="footer__contacts">
          <a href="mailto:eurica001olimp@gmail.com" class="contact-item">
            <span class="contact-item__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="M22 7l-10 6L2 7"/></svg>
            </span>
            <span>
              <span class="contact-label">Email</span>
              <span class="contact-value">eurica001olimp@gmail.com</span>
            </span>
          </a>
          <router-link to="/help-desk" class="contact-item">
            <span class="contact-item__icon" aria-hidden="true">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </span>
            <span>
              <span class="contact-label">Поддержка</span>
              <span class="contact-value">Help Desk и обратная связь</span>
            </span>
          </router-link>
        </div>
      </div>
    </div>

    <div class="footer__bottom">
      <div class="footer__bottom-inner">
        <span>© 2026 Eurika!. Все права защищены.</span>
        <div class="footer__bottom-links">
          <router-link to="/help-desk">Help Desk</router-link>
          <span class="divider" aria-hidden="true">·</span>
          <router-link to="/rules">Правила</router-link>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer {
  background: var(--card);
  border-top: 1px solid var(--border);
}

.footer__container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 56px 24px 44px;
  display: grid;
  grid-template-columns: 1.6fr 1fr 1fr;
  gap: 48px;
}

.footer__logo {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
  color: var(--text);
  text-decoration: none;
  font-family: var(--font-display);
  font-size: 18px;
  font-weight: 700;
}

.footer__mark {
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  border-radius: 11px;
  background: var(--brand);
  color: var(--sun);
  transform: rotate(-6deg);
}

.footer__desc {
  max-width: 44ch;
  margin-bottom: 20px;
  color: var(--text-secondary);
  font-size: 15px;
  line-height: 1.65;
}

.footer__chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.footer__chip {
  padding: 8px 14px;
  border-radius: var(--radius-sm);
  background: var(--brand-soft);
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
  transition: background-color var(--dur) ease, color var(--dur) ease;
}

.footer__chip:hover { background: var(--brand); color: var(--on-brand); }

.footer__title {
  margin-bottom: 14px;
  font-family: var(--font-sans);
  font-size: 15px;
  font-weight: 700;
  letter-spacing: 0;
}

.footer__list {
  display: grid;
  gap: 2px;
  list-style: none;
}

.footer__link {
  display: inline-flex;
  padding: 6px 0;
  color: var(--text-secondary);
  font-size: 15px;
  text-decoration: none;
  transition: color var(--dur) ease;
}

.footer__link:hover { color: var(--brand-ink); }

.footer__contacts {
  display: grid;
  gap: 10px;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: var(--radius-md);
  background: var(--bg);
  color: var(--text);
  text-decoration: none;
  transition: background-color var(--dur) ease;
}

.contact-item:hover { background: var(--brand-softer); }

.contact-item__icon {
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  border-radius: 12px;
  background: var(--card);
  color: var(--brand);
  flex-shrink: 0;
}

.contact-label {
  display: block;
  color: var(--text-tertiary);
  font-size: 12.5px;
  font-weight: 500;
}

.contact-value {
  display: block;
  font-size: 14.5px;
  font-weight: 600;
  word-break: break-word;
}

.footer__bottom { border-top: 1px solid var(--border); }

.footer__bottom-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: 18px 24px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  color: var(--text-tertiary);
  font-size: 14px;
}

.footer__bottom-links {
  display: flex;
  align-items: center;
  gap: 10px;
}

.footer__bottom-links a {
  color: var(--text-secondary);
  text-decoration: none;
}

.footer__bottom-links a:hover { color: var(--brand-ink); }

@media (max-width: 900px) {
  .footer__container { grid-template-columns: 1fr 1fr; gap: 36px; }
  .footer__brand { grid-column: 1 / -1; }
}

@media (max-width: 600px) {
  .footer__container { grid-template-columns: 1fr; padding: 40px 16px 32px; gap: 28px; }
  .footer__bottom-inner { flex-direction: column; align-items: flex-start; padding: 18px 16px 96px; }
}
</style>
