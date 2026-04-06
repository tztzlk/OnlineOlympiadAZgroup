<script setup>
import Welcome from '../components/Welcome.vue'
import Reviews from '../components/Reviews.vue'
import News from '../components/News.vue'

const interestCards = [
  {
    title: 'Быстрый старт без перегрузки',
    text: 'Главная страница не заставляет долго разбираться. Пользователь сразу видит следующий шаг и может перейти к предметам.',
  },
  {
    title: 'Выгода понятна в первые секунды',
    text: 'Онлайн-формат, моментальный результат и сертификат вынесены наверх, поэтому ценность не прячется в глубине сайта.',
  },
  {
    title: 'Один сценарий для двух аудиторий',
    text: 'И ученик, и родитель быстро понимают, что делать дальше, без длинных пояснений и лишних развилок.',
  },
]

const desireCards = [
  {
    title: 'Прозрачный путь участника',
    text: 'От выбора предмета до сертификата пользователь видит понятную последовательность без скрытых шагов и лишних экранов.',
  },
  {
    title: 'Проверяемый результат',
    text: 'Leaderboard, сертификат и публичная проверка по ID помогают быстрее довериться платформе и принять решение.',
  },
  {
    title: 'Надежный процесс внутри платформы',
    text: 'Заявки, оплаты и обращения обрабатываются внутри системы, а не через ручную переписку и таблицы.',
  },
]

const actionSteps = [
  'Открыть предметы и выбрать подходящую олимпиаду',
  'Зарегистрироваться или войти без лишних форм',
  'Завершить участие и сразу увидеть результат',
]
</script>

<template>
  <Welcome />

  <section class="interest-section">
    <div class="section-shell section-shell--interest">
      <div class="interest-copy">
        <p class="eyebrow">Interest</p>
        <h2>Интерес появляется там, где пользователь быстро считывает выгоду</h2>
        <p>
          Сразу после первого экрана мы подтверждаем ценность участия: формат понятный,
          выгода очевидна, а путь к следующему клику не распадается на лишние решения.
        </p>
        <RouterLink to="/subject" class="btn btn-primary">Выбрать олимпиаду</RouterLink>
      </div>

      <div class="interest-grid">
        <article v-for="item in interestCards" :key="item.title" class="interest-card">
          <strong>{{ item.title }}</strong>
          <p>{{ item.text }}</p>
        </article>
      </div>
    </div>
  </section>

  <section class="desire-section">
    <div class="section-shell section-shell--desire">
      <div class="section-head">
        <div>
          <p class="eyebrow">Desire</p>
          <h2>Желание участвовать появляется из доверия, ясности и понятного результата</h2>
        </div>
        <div class="section-actions">
          <RouterLink to="/leaderboard" class="btn btn-outline">Открыть leaderboard</RouterLink>
          <RouterLink to="/certificate-check" class="btn btn-soft">Проверить сертификат</RouterLink>
        </div>
      </div>

      <div class="desire-grid">
        <article v-for="item in desireCards" :key="item.title" class="desire-card">
          <strong>{{ item.title }}</strong>
          <p>{{ item.text }}</p>
        </article>
      </div>
    </div>
  </section>

  <Reviews />

  <section class="action-section">
    <div class="section-shell section-shell--action">
      <div class="action-copy">
        <p class="eyebrow">Action</p>
        <h2>Не оставляем пользователя думать слишком долго</h2>
        <p>
          Финальный блок не завершает страницу формально, а подталкивает к действию:
          здесь только короткий маршрут и одно главное решение.
        </p>
      </div>

      <div class="action-panel">
        <div class="action-steps">
          <div v-for="(step, index) in actionSteps" :key="step" class="action-step">
            <span>{{ index + 1 }}</span>
            <p>{{ step }}</p>
          </div>
        </div>

        <div class="action-cta">
          <RouterLink to="/subject" class="btn btn-primary btn-primary--wide">Перейти к участию</RouterLink>
          <p>Минимум сомнений, минимум лишних переходов, максимум шансов на завершенную заявку.</p>
        </div>
      </div>
    </div>
  </section>

  <News />
</template>

<style scoped>
* { box-sizing: border-box; }

.interest-section,
.desire-section,
.action-section {
  padding: 0 18px 28px;
}

.section-shell {
  max-width: 1180px;
  margin: 0 auto;
  border-radius: var(--radius-lg);
  display: grid;
  gap: 24px;
}

.section-shell--interest,
.section-shell--desire,
.section-shell--action {
  padding: 34px;
}

.section-shell--interest {
  grid-template-columns: minmax(300px, 0.92fr) minmax(0, 1.08fr);
  background: linear-gradient(180deg, rgba(255, 249, 238, 0.94) 0%, rgba(250, 244, 230, 0.9) 100%);
  border: 1px solid rgba(201, 171, 99, 0.18);
  box-shadow: 0 18px 42px rgba(77, 58, 18, 0.08);
  align-items: start;
}

.section-shell--desire {
  background: rgba(255, 252, 246, 0.98);
  border: 1px solid rgba(109, 89, 42, 0.12);
  box-shadow: 0 20px 46px rgba(77, 58, 18, 0.06);
}

.section-shell--action {
  grid-template-columns: minmax(280px, 0.88fr) minmax(0, 1.12fr);
  align-items: center;
  background:
    radial-gradient(circle at top right, rgba(201, 171, 99, 0.14), transparent 26%),
    linear-gradient(135deg, rgba(255, 248, 234, 0.96) 0%, rgba(247, 238, 214, 0.94) 100%);
  border: 1px solid rgba(201, 171, 99, 0.24);
  box-shadow: 0 24px 52px rgba(77, 58, 18, 0.09);
}

.section-head {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  align-items: flex-start;
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 12px;
  font-weight: 800;
  color: var(--accent-strong);
}

.interest-copy,
.action-copy {
  display: grid;
  gap: 14px;
  align-content: start;
}

.section-head h2,
.interest-copy h2,
.action-copy h2 {
  margin: 0;
  max-width: 18ch;
  color: var(--text);
}

.interest-copy p,
.action-copy p {
  margin: 0;
  max-width: 43ch;
  color: var(--text-secondary);
  line-height: 1.72;
}

.interest-grid,
.desire-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.interest-card,
.desire-card,
.action-panel {
  border-radius: var(--radius-md);
}

.interest-card {
  padding: 24px 20px;
  background: rgba(255, 255, 255, 0.62);
  border: 1px solid rgba(201, 171, 99, 0.16);
  box-shadow: 0 12px 28px rgba(77, 58, 18, 0.04);
}

.desire-card {
  padding: 24px 20px;
  background: rgba(255, 249, 239, 0.76);
  border: 1px solid var(--surface-border);
}

.interest-card strong,
.desire-card strong {
  display: block;
  margin-bottom: 10px;
  color: var(--text);
  font-size: 18px;
  line-height: 1.35;
}

.interest-card p,
.desire-card p,
.action-step p,
.action-cta p {
  margin: 0;
  color: var(--text-secondary);
  line-height: 1.64;
}

.section-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.action-panel {
  padding: 24px;
  background: rgba(255, 253, 247, 0.94);
  border: 1px solid rgba(201, 171, 99, 0.18);
  display: grid;
  gap: 18px;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.4);
}

.action-steps {
  display: grid;
  gap: 12px;
}

.action-step {
  display: grid;
  grid-template-columns: 40px minmax(0, 1fr);
  gap: 12px;
  align-items: start;
  padding: 14px 0;
  border-bottom: 1px solid rgba(109, 89, 42, 0.1);
}

.action-step:last-child {
  border-bottom: 0;
  padding-bottom: 0;
}

.action-step span {
  width: 40px;
  height: 40px;
  border-radius: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(201, 171, 99, 0.16);
  color: var(--accent-strong);
  font-weight: 800;
}

.action-cta {
  display: grid;
  gap: 10px;
  padding-top: 8px;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 50px;
  padding: 12px 18px;
  border-radius: var(--radius-sm);
  border: 0;
  font-weight: 700;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%);
  color: var(--text);
  box-shadow: 0 14px 30px rgba(77, 58, 18, 0.12);
}

.btn-primary--wide {
  width: fit-content;
  min-width: 220px;
}

.btn-outline {
  background: rgba(255, 251, 243, 0.92);
  color: var(--accent-strong);
  border: 1px solid rgba(201, 171, 99, 0.28);
}

.btn-soft {
  background: rgba(79, 167, 116, 0.12);
  color: #2f7f4b;
  border: 1px solid rgba(79, 167, 116, 0.2);
}

@media (max-width: 980px) {
  .section-shell--interest,
  .section-shell--action,
  .interest-grid,
  .desire-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 860px) {
  .section-head {
    flex-direction: column;
  }

  .section-head h2,
  .interest-copy h2,
  .action-copy h2 {
    max-width: none;
  }
}

@media (max-width: 640px) {
  .interest-section,
  .desire-section,
  .action-section {
    padding-inline: 14px;
    padding-bottom: 20px;
  }

  .section-shell--interest,
  .section-shell--desire,
  .section-shell--action {
    padding: 24px 18px;
  }

  .section-actions {
    width: 100%;
  }

  .btn-primary--wide,
  .section-actions .btn,
  .interest-copy .btn {
    width: 100%;
  }
}
</style>
