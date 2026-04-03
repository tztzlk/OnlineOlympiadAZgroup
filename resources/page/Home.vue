<script setup>
import Welcome from '../components/Welcome.vue'
import Reviews from '../components/Reviews.vue'
import News from '../components/News.vue'

const trustItems = [
  {
    title: 'Прозрачный путь участника',
    text: 'Родитель видит все шаги от заявки до сертификата в одном кабинете, без потери статусов и лишних звонков.',
  },
  {
    title: 'Проверяемые результаты',
    text: 'После завершения олимпиады доступны результат, сертификат и отдельная публичная проверка по ID.',
  },
  {
    title: 'Операционный контроль',
    text: 'Команда платформы обрабатывает заявки, оплаты и обращения через рабочую панель, а не вручную по таблицам.',
  },
]
</script>

<template>
  <Welcome />

  <section class="trust-section">
    <div class="trust-shell">
      <div class="trust-head">
        <div>
          <p class="eyebrow">Why parents trust it</p>
          <h2>Платформа уже работает как полноценный олимпиадный сервис, а не как форма подачи заявки</h2>
        </div>
        <div class="trust-actions">
          <RouterLink to="/leaderboard" class="btn btn-primary">Открыть leaderboard</RouterLink>
          <RouterLink to="/certificate-check" class="btn btn-outline">Проверить сертификат</RouterLink>
        </div>
      </div>

      <div class="trust-grid">
        <article v-for="item in trustItems" :key="item.title" class="trust-card">
          <strong>{{ item.title }}</strong>
          <p>{{ item.text }}</p>
        </article>
      </div>
    </div>
  </section>

  <Reviews />
  <News />
</template>

<style scoped>
* { box-sizing: border-box; }

.trust-section {
  padding: 0 18px 18px;
}

.trust-shell {
  max-width: 1180px;
  margin: 0 auto;
  padding: 28px;
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
  display: grid;
  gap: 20px;
}

.trust-head {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  align-items: flex-start;
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: .12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

.trust-head h2 {
  margin: 0;
  max-width: 18ch;
  color: var(--text);
}

.trust-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.trust-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
}

.trust-card {
  padding: 18px;
  border-radius: var(--radius-md);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 244, 0.82);
}

.trust-card strong {
  display: block;
  margin-bottom: 8px;
  color: var(--text);
  font-size: 18px;
}

.trust-card p {
  margin: 0;
  color: var(--text-secondary);
  line-height: 1.6;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
  padding: 12px 18px;
  border-radius: var(--radius-sm);
  border: 0;
  font-weight: 700;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%);
  color: var(--text);
}

.btn-outline {
  background: rgba(255, 251, 243, 0.86);
  color: var(--accent-strong);
  border: 1px solid rgba(201, 171, 99, 0.28);
}

@media (max-width: 860px) {
  .trust-head {
    flex-direction: column;
  }

  .trust-head h2,
  .trust-grid {
    max-width: none;
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .trust-section {
    padding-inline: 14px;
  }

  .trust-shell {
    padding: 22px 18px;
  }
}
</style>
