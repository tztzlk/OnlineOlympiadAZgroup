<template>
  <footer class="footer">
    <div class="footer__accent-line"></div>

    <div class="footer__container">
      <div class="footer__block footer__block--brand">
        <router-link to="/" class="footer__logo">
          <div class="logo-icon">E</div>
          <span>EUrica!</span>
        </router-link>

        <p class="footer__desc">
          EUrica! — онлайн-олимпиады по математике и другим предметам. Мгновенный результат, сертификат и разбор ошибок.
        </p>

        <div class="footer__socials">
          <router-link to="/help-desk" class="social-btn">Help Desk</router-link>
          <router-link to="/rules" class="social-btn">Правила</router-link>
          <router-link to="/subject" class="social-btn">Олимпиады</router-link>
        </div>
      </div>

      <div class="footer__block">
        <div class="footer__title">
          <span>Навигация</span>
          <div class="title-line"></div>
        </div>
        <ul class="footer__list">
          <li><router-link to="/" class="footer__link">Главная</router-link></li>
          <li><router-link to="/subject" class="footer__link">Предметы</router-link></li>
          <li><router-link to="/results" class="footer__link">Результаты</router-link></li>
          <li><router-link to="/forgot-password" class="footer__link">Восстановить пароль</router-link></li>
          <li><router-link to="/help-desk" class="footer__link">Обратная связь</router-link></li>
        </ul>
      </div>

      <div class="footer__block">
        <div class="footer__title">
          <span>Контакты</span>
          <div class="title-line"></div>
        </div>
        <div class="footer__contacts">
          <a href="mailto:support@olympiad.kz" class="contact-item">
            <div>
              <div class="contact-label">Email</div>
              <div class="contact-value">support@olympiad.kz</div>
            </div>
          </a>
          <a href="tel:+77770000000" class="contact-item">
            <div>
              <div class="contact-label">Телефон</div>
              <div class="contact-value">+7 (777) 000-00-00</div>
            </div>
          </a>
          <router-link to="/help-desk" class="contact-item">
            <div>
              <div class="contact-label">Поддержка</div>
              <div class="contact-value">Help Desk и обратная связь</div>
            </div>
          </router-link>
        </div>
      </div>
    </div>

    <div class="footer__bottom">
      <div class="footer__bottom-inner">
        <span>© 2026 EUrica!. Все права защищены.</span>
        <div class="footer__bottom-links">
          <router-link to="/help-desk">Help Desk</router-link>
          <span class="divider">·</span>
          <router-link to="/rules">Правила</router-link>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
* { box-sizing: border-box; }
.footer { background: var(--bg); position: relative; overflow: hidden; }
.footer__accent-line { height: 3px; background: linear-gradient(90deg, var(--accent), var(--green), var(--accent)); }
.footer__container { max-width: 1200px; margin: 0 auto; padding: 60px 28px 48px; display: grid; grid-template-columns: 1.6fr 1fr 1fr; gap: 48px; }
.footer__logo { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; margin-bottom: 18px; }
.logo-icon { width: 34px; height: 34px; background: var(--green); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 15px; }
.footer__logo span { font-size: 18px; font-weight: 700; color: var(--text); }
.footer__desc { color: var(--text-secondary); font-size: 14px; line-height: 1.7; margin: 0 0 24px; }
.footer__socials { display: flex; gap: 10px; flex-wrap: wrap; }
.social-btn { display: inline-flex; align-items: center; gap: 7px; padding: 8px 14px; background: color-mix(in srgb, var(--green-soft) 82%, #ffffff); border: 1px solid color-mix(in srgb, var(--green) 24%, var(--border)); border-radius: 10px; color: var(--green-strong); text-decoration: none; font-size: 13px; font-weight: 700; transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease, transform 0.2s ease; }
@media (hover: hover) and (pointer: fine) { .social-btn:hover { background: var(--green); color: #ffffff; border-color: var(--green); transform: translateY(-1px); } }
.social-btn:active { background: var(--green-hover); border-color: var(--green-hover); color: #ffffff; }
.footer__title { margin-bottom: 20px; }
.footer__title span { font-size: 13px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--text); }
.title-line { width: 28px; height: 2px; background: var(--accent); border-radius: 2px; margin-top: 8px; }
.footer__list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }
.footer__link { display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--text-secondary); font-size: 14px; font-weight: 500; padding: 8px 10px; border-radius: 10px; transition: background 0.2s ease, color 0.2s ease; }
@media (hover: hover) and (pointer: fine) { .footer__link:hover { background: color-mix(in srgb, var(--accent) 8%, transparent); color: var(--accent-strong); } }
.footer__link:active { background: color-mix(in srgb, var(--accent) 14%, transparent); }
.footer__contacts { display: flex; flex-direction: column; gap: 14px; }
.contact-item { display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 10px 12px; background: color-mix(in srgb, var(--text) 3%, transparent); border: 1px solid var(--border); border-radius: 12px; transition: background 0.2s ease, border-color 0.2s ease; }
@media (hover: hover) and (pointer: fine) { .contact-item:hover { background: color-mix(in srgb, var(--accent) 7%, transparent); border-color: color-mix(in srgb, var(--accent) 22%, transparent); } }
.contact-item:active { background: color-mix(in srgb, var(--accent) 12%, transparent); }
.contact-label { font-size: 11px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 2px; }
.contact-value { font-size: 14px; font-weight: 600; color: var(--text); }
.footer__bottom { border-top: 1px solid var(--border); }
.footer__bottom-inner { max-width: 1200px; margin: 0 auto; padding: 18px 28px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.footer__bottom span, .footer__bottom-links a { font-size: 13px; color: var(--text-secondary); text-decoration: none; transition: color 0.2s ease; }
@media (hover: hover) and (pointer: fine) { .footer__bottom-links a:hover { color: var(--accent-strong); } }
.footer__bottom-links { display: flex; align-items: center; gap: 10px; }
.divider { color: var(--text-secondary); opacity: 0.7; }
@media (max-width: 900px) { .footer__container { grid-template-columns: 1fr 1fr; gap: 36px; } .footer__block--brand { grid-column: 1 / -1; } }
@media (max-width: 600px) { .footer__container { grid-template-columns: 1fr; padding: 40px 20px 36px; gap: 32px; } .footer__block { text-align: center; } .footer__logo, .footer__socials { justify-content: center; } .title-line { margin: 8px auto 0; } .footer__link { justify-content: center; } .footer__bottom-inner { flex-direction: column; text-align: center; gap: 10px; } }
</style>
