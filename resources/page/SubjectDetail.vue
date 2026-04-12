<template>
  <div class="subject-detail-page">
    <StatePanel
      v-if="loading"
      tone="neutral"
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
        <RouterLink class="cta secondary" to="/subject">Вернуться в каталог</RouterLink>
      </template>
    </StatePanel>

    <template v-else-if="subject">
      <section class="hero-card">
        <div class="hero-copy">
          <p class="eyebrow">Онлайн-олимпиада</p>
          <h1>Олимпиада по предмету {{ subject.name }}</h1>
          <p class="lead">{{ subject.description }}</p>

          <div class="hero-actions">
            <RouterLink class="cta primary" :to="subject.registration_url || `/subject?subject=${subject.id}`">Зарегистрироваться на олимпиаду</RouterLink>
            <RouterLink class="cta secondary" :to="`/subject?subject=${subject.id}&openRules=1`">Смотреть правила</RouterLink>
          </div>

          <div v-if="countdownStatusLabel" class="deadline-banner">
            <div>
              <p class="eyebrow">Регистрация</p>
              <strong>{{ countdownStatusLabel }}</strong>
            </div>
            <span v-if="countdownParts">{{ countdownParts }}</span>
          </div>
        </div>

        <div class="hero-aside">
          <article class="info-pill">
            <span>Классы</span>
            <strong>{{ gradeRangesLabel }}</strong>
          </article>
          <article class="info-pill">
            <span>Формат</span>
            <strong>Онлайн</strong>
          </article>
          <article class="info-pill">
            <span>Длительность</span>
            <strong>{{ timeLimitLabel }}</strong>
          </article>
        </div>
      </section>

      <section class="content-grid">
        <article class="content-card">
          <h2>Для кого подходит олимпиада</h2>
          <p>
            Онлайн-олимпиада по предмету {{ subject.name }} подходит для школьников из Казахстана,
            а родители могут оформить участие, оплатить олимпиаду и посмотреть результат в одном кабинете.
          </p>
        </article>

        <article class="content-card">
          <h2>Как проходит участие</h2>
          <p>
            Сначала родитель выбирает предмет, затем сохраняет данные участника и оплачивает участие через Kaspi.
            После подтверждения платежа ребёнок получает доступ к олимпиаде и проходит задания онлайн.
          </p>
        </article>

        <article class="content-card">
          <h2>Результаты и сертификаты</h2>
          <p>
            После завершения олимпиады результат появляется в личном кабинете.
            Сертификат можно проверить публично по ID на странице проверки сертификатов.
          </p>
          <RouterLink class="inline-link" to="/certificate-check">Проверить сертификат</RouterLink>
        </article>
      </section>

      <section class="faq-card">
        <div class="faq-head">
          <p class="eyebrow">FAQ</p>
          <h2>Частые вопросы родителей</h2>
        </div>

        <div class="faq-list">
          <article v-for="(item, index) in faqItems" :key="item.question" class="faq-item">
            <button class="faq-question" type="button" @click="toggleFaq(index)">
              <h3>{{ item.question }}</h3>
              <span>{{ openFaqIndex === index ? '−' : '+' }}</span>
            </button>
            <p v-if="openFaqIndex === index">{{ item.answer }}</p>
          </article>
        </div>
      </section>

      <section class="link-grid">
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
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
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

onMounted(() => {
  loadSubject()
  setInterval(() => {
    nowTs.value = Date.now()
  }, 60000)
})
</script>

<style scoped>
* { box-sizing: border-box; }

.subject-detail-page {
  min-height: 100vh;
  padding: 110px 18px 48px;
  background: radial-gradient(circle at top left, rgba(201, 171, 99, 0.14), transparent 24%), var(--bg);
  display: grid;
  gap: 18px;
}

.hero-card,
.content-card,
.faq-card,
.link-card {
  max-width: 1120px;
  width: 100%;
  margin: 0 auto;
  border-radius: var(--radius-lg);
  border: 1px solid var(--surface-border);
  background: var(--surface);
  box-shadow: var(--shadow-card);
}

.hero-card {
  padding: 28px;
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(260px, 1fr);
  gap: 22px;
}

.eyebrow {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .12em;
  color: var(--accent-strong);
}

.hero-copy h1,
.faq-head h2,
.content-card h2 {
  margin: 0;
  color: var(--text);
}

.lead,
.content-card p,
.faq-item p,
.link-card span {
  color: var(--text-secondary);
  line-height: 1.65;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 22px;
}

.deadline-banner {
  margin-top: 18px;
  padding: 16px 18px;
  border-radius: var(--radius-md);
  border: 1px solid rgba(201, 171, 99, 0.24);
  background: linear-gradient(135deg, rgba(210, 178, 97, 0.14), rgba(255, 248, 232, 0.86));
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.deadline-banner strong {
  color: var(--text);
  font-size: 18px;
}

.deadline-banner span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 96px;
  padding: 10px 14px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.72);
  color: var(--accent-strong);
  font-weight: 800;
}

.cta {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 48px;
  padding: 12px 18px;
  border-radius: var(--radius-sm);
  text-decoration: none;
  font-weight: 700;
}

.cta.primary {
  background: linear-gradient(135deg, var(--accent) 0%, #e3c06e 100%);
  color: var(--text);
}

.cta.secondary {
  background: rgba(255, 252, 244, 0.82);
  border: 1px solid var(--surface-border);
  color: var(--accent-strong);
}

.hero-aside {
  display: grid;
  gap: 12px;
}

.info-pill,
.faq-item,
.content-card,
.link-card {
  padding: 18px;
}

.info-pill {
  border-radius: var(--radius-md);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 244, 0.82);
  display: grid;
  gap: 8px;
}

.info-pill span {
  color: var(--text-secondary);
  font-size: 13px;
}

.info-pill strong {
  color: var(--text);
  font-size: 24px;
}

.content-grid,
.faq-list,
.link-grid {
  max-width: 1120px;
  width: 100%;
  margin: 0 auto;
  display: grid;
  gap: 16px;
}

.content-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.faq-card {
  padding: 24px;
}

.faq-list {
  margin-top: 16px;
}

.faq-item {
  border-radius: var(--radius-md);
  border: 1px solid var(--surface-border);
  background: rgba(255, 252, 244, 0.82);
}

.faq-question {
  width: 100%;
  border: 0;
  background: transparent;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 0;
  text-align: left;
  cursor: pointer;
}

.faq-item h3 {
  margin: 0;
}

.faq-question span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 999px;
  background: rgba(201, 171, 99, 0.16);
  color: var(--accent-strong);
  font-weight: 800;
}

.faq-item p {
  margin: 12px 0 0;
}

.link-grid {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.link-card {
  text-decoration: none;
  color: var(--text);
  display: grid;
  gap: 6px;
}

.inline-link {
  display: inline-flex;
  margin-top: 12px;
  color: var(--accent-strong);
  font-weight: 700;
  text-decoration: none;
}

@media (max-width: 960px) {
  .hero-card,
  .content-grid,
  .link-grid {
    grid-template-columns: 1fr;
  }

  .deadline-banner {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
