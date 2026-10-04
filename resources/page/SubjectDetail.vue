<template>
  <div class="subject-detail-page">
    <div class="sd-wrap">
      <StatePanel
        v-if="loading"
        tone="neutral" loading
        eyebrow="Предмет"
        title="Загружаем страницу олимпиады"
        description="Подготавливаем описание предмета, классы, формат участия и ответы на частые вопросы."
      />

      <StatePanel
        v-else-if="error"
        tone="warning"
        eyebrow="Предмет"
        title="Не удалось загрузить предмет"
        :description="error"
      >
        <template #actions>
          <RouterLink class="ds-btn ds-btn-ghost" to="/subject">Вернуться в каталог</RouterLink>
        </template>
      </StatePanel>

      <template v-else-if="subject">
        <nav class="breadcrumbs" aria-label="Хлебные крошки">
          <RouterLink to="/">Главная</RouterLink>
          <span aria-hidden="true">/</span>
          <RouterLink to="/subject">Каталог олимпиад</RouterLink>
          <span aria-hidden="true">/</span>
          <span aria-current="page">{{ subject.name }}</span>
        </nav>

        <section class="hero-card" aria-labelledby="sd-title">
          <div class="hero-copy">
            <span class="ds-eyebrow sun">Онлайн-олимпиада</span>
            <h1 id="sd-title">Олимпиада по предмету {{ subject.name }}</h1>
            <p class="lead">{{ subject.description }}</p>

            <div class="hero-actions">
              <RouterLink class="ds-btn ds-btn-sun ds-btn-lg" :to="subject.registration_url || `/subject?subject=${subject.id}`">Зарегистрироваться на олимпиаду</RouterLink>
              <RouterLink class="ds-btn ds-btn-lg hero-ghost" :to="`/subject?subject=${subject.id}&openRules=1`">Смотреть правила</RouterLink>
            </div>
          </div>

          <div class="hero-aside">
            <img v-if="subject.image" :src="subject.image" :alt="subject.name" class="hero-image" />
            <dl class="facts">
              <div class="fact">
                <dt>Классы</dt>
                <dd>{{ gradeRangesLabel }}</dd>
              </div>
              <div class="fact">
                <dt>Формат</dt>
                <dd>Онлайн</dd>
              </div>
              <div class="fact">
                <dt>Длительность</dt>
                <dd>{{ timeLimitLabel }}</dd>
              </div>
            </dl>
          </div>
        </section>

        <div v-if="countdownStatusLabel" class="deadline-banner">
          <span class="deadline-banner__icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2M9 2h6"/></svg>
          </span>
          <div class="deadline-banner__text">
            <p class="deadline-banner__eyebrow">Регистрация</p>
            <strong>{{ countdownStatusLabel }}</strong>
          </div>
          <span v-if="countdownParts" class="deadline-banner__value">{{ countdownParts }}</span>
        </div>

        <section class="content-grid">
          <article class="content-card" v-reveal="0">
            <span class="content-card__icon tone-blue" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg>
            </span>
            <h2>Для кого подходит олимпиада</h2>
            <p>
              Онлайн-олимпиада по предмету {{ subject.name }} подходит для школьников из Казахстана,
              а родители могут оформить участие, оплатить олимпиаду и посмотреть результат в одном кабинете.
            </p>
          </article>

          <article class="content-card" v-reveal="1">
            <span class="content-card__icon tone-sun" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </span>
            <h2>Как проходит участие</h2>
            <p>
              Сначала родитель выбирает предмет, затем сохраняет данные участника и оплачивает участие через Kaspi.
              После подтверждения платежа ребёнок получает доступ к олимпиаде и проходит задания онлайн.
            </p>
          </article>

          <article class="content-card" v-reveal="2">
            <span class="content-card__icon tone-green" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.5 13.5L17 22l-5-3-5 3 1.5-8.5"/></svg>
            </span>
            <h2>Результаты и сертификаты</h2>
            <p>
              После завершения олимпиады результат появляется в личном кабинете.
              Сертификат можно проверить публично по ID на странице проверки сертификатов.
            </p>
            <RouterLink class="inline-link" to="/certificate-check">
              Проверить сертификат
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </RouterLink>
          </article>
        </section>

        <section class="faq-card" aria-labelledby="faq-title">
          <div class="faq-head">
            <span class="ds-eyebrow">FAQ</span>
            <h2 id="faq-title">Частые вопросы родителей</h2>
          </div>

          <div class="faq-list">
            <div v-for="(item, index) in faqItems" :key="item.question" class="faq-item" :class="{ 'is-open': openFaqIndex === index }">
              <h3>
                <button
                  :id="`faq-q-${index}`"
                  class="faq-question"
                  type="button"
                  :aria-expanded="openFaqIndex === index ? 'true' : 'false'"
                  :aria-controls="`faq-a-${index}`"
                  @click="toggleFaq(index)"
                >
                  <span>{{ item.question }}</span>
                  <span class="faq-question__icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                  </span>
                </button>
              </h3>
              <p v-if="openFaqIndex === index" :id="`faq-a-${index}`" role="region" :aria-labelledby="`faq-q-${index}`">{{ item.answer }}</p>
            </div>
          </div>
        </section>

        <nav class="link-grid" aria-label="Полезные разделы">
          <RouterLink class="link-card" to="/subject">
            <strong>Каталог олимпиад</strong>
            <span>Посмотреть все доступные предметы</span>
          </RouterLink>
          <RouterLink class="link-card" to="/leaderboard">
            <strong>Рейтинг участников</strong>
            <span>Посмотреть лучшие результаты платформы</span>
          </RouterLink>
          <RouterLink class="link-card" to="/help-desk">
            <strong>Help Desk</strong>
            <span>Связаться с поддержкой по оплате и доступу</span>
          </RouterLink>
        </nav>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '../js/api'
import StatePanel from '../components/StatePanel.vue'
import { applySeo, buildSubjectSeo } from '../js/composables/useSeo'

const route = useRoute()
const loading = ref(true)
const error = ref('')
const subject = ref(null)
const openFaqIndex = ref(0)
const nowTs = ref(Date.now())

const gradeRangesLabel = computed(() =>
  subject.value?.grade_ranges?.length ? subject.value.grade_ranges.join(', ') : '3-11 классы'
)

const timeLimitLabel = computed(() =>
  subject.value?.time_limit ? `${subject.value.time_limit} минут` : 'По правилам олимпиады'
)

const registrationDeadline = computed(() => {
  const raw = subject.value?.start_date
  if (!raw) return null
  const parsed = new Date(raw)
  return Number.isNaN(parsed.getTime()) ? null : parsed
})

const countdownMs = computed(() => {
  if (!registrationDeadline.value) return null
  return registrationDeadline.value.getTime() - nowTs.value
})

const countdownStatusLabel = computed(() => {
  if (!registrationDeadline.value) return ''
  if ((countdownMs.value ?? 0) <= 0) return 'Регистрация обновляется'
  return 'До закрытия регистрации'
})

const countdownParts = computed(() => {
  if ((countdownMs.value ?? 0) <= 0) return ''
  const totalHours = Math.floor((countdownMs.value || 0) / 3600000)
  const days = Math.floor(totalHours / 24)
  const hours = totalHours % 24
  if (days > 0) return `${days} дн. ${hours} ч.`
  return `${Math.max(hours, 1)} ч.`
})

const faqItems = computed(() => {
  if (subject.value?.faq?.length) return subject.value.faq

  const gradeLabel = subject.value?.grade_ranges?.length ? subject.value.grade_ranges.join(', ') : '3-11 классы'

  return [
    {
      question: 'Для какого возраста подходит олимпиада?',
      answer: `Олимпиада рассчитана на школьников, которым подходит диапазон ${gradeLabel}.`,
    },
    {
      question: 'На каком языке проходит олимпиада?',
      answer: 'Язык выбирается при оформлении участия. Обычно доступны русский, казахский и при необходимости английский.',
    },
    {
      question: 'Получу ли я сертификат?',
      answer: 'Да, после завершения олимпиады результат и сертификат появляются в личном кабинете участника.',
    },
    {
      question: 'Когда открывается доступ к тесту?',
      answer: 'Доступ открывается после оформления заявки и подтверждения оплаты администратором.',
    },
    {
      question: 'Сколько времени даётся на выполнение?',
      answer: `Для этой олимпиады ориентир по времени: ${timeLimitLabel.value}.`,
    },
    {
      question: 'Можно ли пройти олимпиаду дома?',
      answer: 'Да, формат полностью онлайн. Ребёнок проходит задания на платформе из любого удобного места.',
    },
  ]
})

const toggleFaq = (index) => {
  openFaqIndex.value = openFaqIndex.value === index ? -1 : index
}

const loadSubject = async () => {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get(`/subjects/${route.params.subjectId}`)
    subject.value = data
    applySeo(buildSubjectSeo(data))
  } catch (err) {
    error.value = err.response?.data?.message || 'Страница предмета сейчас недоступна.'
  } finally {
    loading.value = false
  }
}

let clockTimer = null

onMounted(() => {
  loadSubject()
  clockTimer = setInterval(() => {
    nowTs.value = Date.now()
  }, 60000)
})

onBeforeUnmount(() => {
  if (clockTimer) clearInterval(clockTimer)
})
</script>

<style scoped>
.subject-detail-page {
  min-height: 100dvh;
  padding: calc(var(--header-h) + 24px) 20px 80px;
  background: var(--bg);
}

.sd-wrap {
  max-width: 1160px;
  margin: 0 auto;
  display: grid;
  gap: 18px;
}

.breadcrumbs {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  color: var(--text-tertiary);
  font-size: 14px;
}

.breadcrumbs a { color: var(--text-secondary); text-decoration: none; }
.breadcrumbs a:hover { color: var(--brand-ink); }
.breadcrumbs [aria-current] { color: var(--text); font-weight: 500; }

/* ---- Первый экран ---- */
.hero-card {
  position: relative;
  overflow: hidden;
  display: grid;
  grid-template-columns: minmax(0, 1.5fr) minmax(280px, 1fr);
  gap: 32px;
  align-items: center;
  padding: clamp(24px, 4vw, 44px);
  border-radius: var(--radius-xl);
  background:
    radial-gradient(600px 300px at 100% 0%, rgba(255, 255, 255, 0.16), transparent 70%),
    linear-gradient(155deg, #3d6cff 0%, #2b5bf5 50%, #2046d4 100%);
  color: #ffffff;
  box-shadow: var(--shadow-brand), var(--shadow-md);
}

.hero-copy {
  display: grid;
  justify-items: start;
  gap: 14px;
}

.hero-copy h1 { font-size: clamp(28px, 4vw, 46px); }

.lead {
  max-width: 54ch;
  color: rgba(255, 255, 255, 0.86);
  font-size: 17px;
  line-height: 1.6;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 8px;
}

.hero-ghost {
  background: rgba(255, 255, 255, 0.14);
  border-color: rgba(255, 255, 255, 0.3);
  color: #ffffff;
}

.hero-ghost:hover { background: rgba(255, 255, 255, 0.24); }

.hero-aside {
  display: grid;
  gap: 14px;
}

.hero-image {
  max-height: 170px;
  justify-self: center;
  object-fit: contain;
  filter: drop-shadow(0 18px 24px rgba(10, 25, 90, 0.35));
}

.facts {
  display: grid;
  gap: 8px;
  margin: 0;
}

.fact {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 16px;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.14);
}

.fact dt {
  color: rgba(255, 255, 255, 0.78);
  font-size: 14px;
}

.fact dd {
  margin: 0;
  font-size: 17px;
  font-weight: 700;
  text-align: right;
}

/* ---- Дедлайн ---- */
.deadline-banner {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 20px;
  border-radius: var(--radius-lg);
  background: var(--sun-soft);
}

.deadline-banner__icon {
  width: 42px;
  height: 42px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 13px;
  background: var(--card);
  color: #d99a00;
}

.deadline-banner__text { flex: 1; }

.deadline-banner__eyebrow {
  color: var(--sun-ink);
  font-size: 13px;
  font-weight: 600;
}

.deadline-banner strong { font-size: 17px; }

.deadline-banner__value {
  font-family: var(--font-display);
  font-size: 22px;
  font-weight: 700;
  white-space: nowrap;
}

/* ---- Контент ---- */
.content-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
}

.content-card {
  display: grid;
  align-content: start;
  gap: 10px;
  padding: 22px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
}

.content-card__icon {
  width: 46px;
  height: 46px;
  display: grid;
  place-items: center;
  border-radius: 14px;
}

.tone-blue { background: var(--brand-soft); color: var(--brand); }
.tone-sun { background: var(--sun-soft); color: #c98a00; }
.tone-green { background: var(--success-soft); color: var(--success); }

.content-card h2 {
  font-family: var(--font-sans);
  font-size: 18px;
  font-weight: 700;
  letter-spacing: -0.01em;
}

.content-card p {
  color: var(--text-secondary);
  font-size: 15px;
  line-height: 1.6;
}

.inline-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--brand-ink);
  font-weight: 600;
  text-decoration: none;
}

.inline-link:hover { text-decoration: underline; text-underline-offset: 3px; }

/* ---- FAQ ---- */
.faq-card {
  display: grid;
  grid-template-columns: minmax(0, 0.8fr) minmax(0, 1.4fr);
  gap: 32px;
  padding: clamp(22px, 3vw, 36px);
  border-radius: var(--radius-xl);
  background: var(--card);
  border: 1px solid var(--border);
}

.faq-head {
  display: grid;
  justify-items: start;
  align-content: start;
  gap: 12px;
}

.faq-head h2 { font-size: clamp(22px, 2.6vw, 30px); }

.faq-list {
  display: grid;
  gap: 8px;
}

.faq-item {
  border-radius: var(--radius-md);
  background: var(--bg);
  transition: background-color var(--dur) ease;
}

.faq-item.is-open { background: var(--brand-softer); }

.faq-item h3 {
  font-size: inherit;
  letter-spacing: 0;
}

.faq-question {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 16px 18px;
  border: 0;
  border-radius: var(--radius-md);
  background: transparent;
  color: var(--text);
  font-size: 16px;
  font-weight: 600;
  text-align: left;
}

.faq-question__icon {
  width: 30px;
  height: 30px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border-radius: 10px;
  background: var(--card);
  color: var(--brand);
  transition: transform var(--dur) var(--ease-out), background-color var(--dur) ease, color var(--dur) ease;
}

.faq-item.is-open .faq-question__icon {
  transform: rotate(45deg);
  background: var(--brand);
  color: #ffffff;
}

.faq-item p {
  padding: 0 18px 16px;
  color: var(--text-secondary);
  font-size: 15px;
  line-height: 1.6;
}

/* ---- Ссылки ---- */
.link-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.link-card {
  display: grid;
  gap: 4px;
  padding: 18px 20px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
  color: var(--text);
  text-decoration: none;
  transition: transform var(--dur) var(--ease-out), border-color var(--dur) ease;
}

.link-card:hover { transform: translateY(-2px); border-color: var(--brand); }
.link-card strong { font-size: 16px; }
.link-card span { color: var(--text-secondary); font-size: 14px; }

@media (max-width: 960px) {
  .hero-card,
  .faq-card { grid-template-columns: 1fr; }
  .content-grid,
  .link-grid { grid-template-columns: 1fr; }
}

@media (max-width: 600px) {
  .subject-detail-page { padding: calc(var(--header-h) + 14px) 12px 96px; }
  .hero-actions .ds-btn { flex: 1 1 100%; }
  .deadline-banner { flex-wrap: wrap; }
}
</style>
