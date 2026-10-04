<template>
  <div class="offer-page">
    <div class="offer-card" role="dialog" aria-modal="true" aria-labelledby="offer-title">

      <!-- Header with language selector -->
      <div class="offer-header">
        <div class="offer-header__top">
          <div class="offer-header__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
              <path d="M14 2v6h6M9 13h6M9 17h4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          </div>
          <div class="language-selector">
            <button
              type="button"
              class="lang-btn"
              :class="{ active: language === 'ru' }"
              :aria-pressed="language === 'ru' ? 'true' : 'false'"
              @click="language = 'ru'"
            >
              РУ
            </button>
            <button
              type="button"
              class="lang-btn"
              :class="{ active: language === 'kk' }"
              :aria-pressed="language === 'kk' ? 'true' : 'false'"
              @click="language = 'kk'"
            >
              КК
            </button>
          </div>
        </div>
        <h1 id="offer-title" class="offer-title">{{ content[language].title }}</h1>
        <p class="offer-subtitle">{{ content[language].subtitle }}</p>
      </div>

      <!-- Content -->
      <div class="offer-content" ref="contentEl" @scroll="handleScroll">

        <p class="offer-intro">
          {{ content[language].intro }}
        </p>

        <div v-for="(section, index) in content[language].sections" :key="index" class="offer-section">
          <div class="offer-section__num">{{ String(index + 1).padStart(2, '0') }}</div>
          <div>
            <h3 class="offer-section__title">{{ section.title }}</h3>
            <p v-if="section.text" v-html="section.text"></p>
            <ul v-if="section.list" class="offer-list">
              <li v-for="(item, i) in section.list" :key="i" v-html="item"></li>
            </ul>
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
        {{ content[language].scrollHint }}
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
          <span class="checkbox-label">{{ content[language].checkbox }}</span>
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
            {{ content[language].accepted }}
          </template>
          <template v-else>
            {{ content[language].confirmBtn }}
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
            {{ content[language].successMsg }}
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
const language = ref('ru')

const emit = defineEmits(['accepted'])

const content = {
  ru: {
    title: 'ПУБЛИЧНАЯ ОФЕРТА',
    subtitle: 'о предоставлении услуг участия в онлайн-олимпиаде',
    intro: 'Используя сервис, вы принимаете условия публичной оферты. Внимательно ознакомьтесь с текстом документа перед подтверждением.',
    checkbox: 'Я прочитал(а) и согласен(на) с условиями публичной оферты',
    confirmBtn: 'Подтвердить и продолжить',
    accepted: 'Принято',
    successMsg: 'Оферта успешно принята',
    scrollHint: 'Прокрутите для ознакомления',
    sections: [
      {
        title: '1. Общие положения',
        text: '1.1. Настоящий документ является официальным предложением (публичной офертой) Образовательной компании ТОО «AZ GROUP LLC» (далее — Исполнитель) заключить договор на оказание услуг на изложенных ниже условиях.<br><br>1.2. В соответствии с гражданским законодательством Республики Казахстан данный документ является публичной офертой.<br><br>1.3. Акцептом (принятием) настоящей оферты считается факт оплаты услуги Заказчиком.'
      },
      {
        title: '2. Предмет договора',
        text: '2.1. Исполнитель предоставляет Заказчику доступ к участию в онлайн-олимпиаде <strong>Eurika</strong> по математике, английскому языку для учащихся 3–11 классов.<br><br>2.2. Услуга предоставляется в дистанционном формате через интернет на сайте eurikaolympiads.com.'
      },
      {
        title: '3. Стоимость услуг и порядок оплаты',
        text: '3.1. Стоимость участия в одной олимпиаде составляет <strong>2000 (две тысячи) тенге</strong>.<br><br>3.2. Оплата производится единовременно через доступные на сайте способы оплаты.<br><br>3.3. Услуга считается оплаченной с момента поступления денежных средств на счёт Исполнителя.'
      },
      {
        title: '4. Порядок оказания услуг',
        text: '4.1. После оплаты Заказчику предоставляется доступ к участию в олимпиаде.<br><br>4.2. Сроки проведения олимпиады и условия участия публикуются на сайте.<br><br>4.3. Исполнитель не несёт ответственности за невозможность участия по причинам, не зависящим от него (проблемы с интернетом, устройством пользователя и др.).'
      },
      {
        title: '5. Права и обязанности сторон',
        text: '<strong>Исполнитель обязуется:</strong><br> - предоставить доступ к олимпиаде;<br> - обеспечить корректную работу платформы (в пределах технических возможностей).<br><br><strong>Заказчик обязуется:</strong><br> - предоставить достоверные данные при регистрации;<br> - соблюдать правила участия в олимпиаде.'
      },
      {
        title: '6. Возврат средств',
        text: '6.1. После предоставления доступа к олимпиаде услуга считается оказанной.<br><br>6.2. Возврат денежных средств не осуществляется, за исключением случаев технических сбоев по вине Исполнителя.'
      },
      {
        title: '7. Ответственность сторон',
        text: '7.1. Стороны несут ответственность в соответствии с законодательством Республики Казахстан.<br><br>7.2. Исполнитель не несёт ответственности за результаты участия Заказчика в олимпиаде.'
      },
      {
        title: '8. Заключительные положения',
        text: '8.1. Исполнитель имеет право вносить изменения в настоящую оферту без предварительного уведомления.<br><br>8.2. Новая редакция вступает в силу с момента её публикации на сайте.<br><br>8.3. Заказчик обязуется самостоятельно отслеживать изменения.'
      },
      {
        title: '9. Реквизиты Исполнителя',
        text: '<strong>Название компании:</strong> ТОО "AZ GROUP LLC"<br><strong>БИН:</strong> 241140003039<br><strong>Адрес:</strong> Астана, Жилой массив Ақ-Бұлақ-3 улица Аскар Токпанов, дом 27<br><strong>Телефон:</strong> +7 (700) 033 02 26'
      }
    ]
  },
  kk: {
    title: 'Публичтік ұсыныс',
    subtitle: 'Растау алдында шарттарымен мұқият танысыңыз',
    intro: 'Сервисті пайдалану арқылы сіз публичтік ұсынысының шарттарын қабылдайсыз. Растау алдында құжаттың мәтінімен мұқият танысыңыз.',
    checkbox: 'Мен публичтік ұсынысының шарттарымен танысқан және келісемін',
    confirmBtn: 'Растау және жалғастыру',
    accepted: 'Қабылданды',
    successMsg: 'Ұсыныс сәтті қабылданды',
    scrollHint: 'Танысу үшін жүргіңіз',
    sections: [
      {
        title: 'Жалпы ережелер',
        text: 'Берілген құжат Білім ортасының ТОО «AZ GROUP LLC» компаниясының ресми ұсынысы (публичтік ұсыныс) болып табылады және төмендегі шарттарда қызмет көрсету туралы келісімді жасау ұсынысы. Қазақстан Республикасының азаматтық заңнамасына сәйкес берілген құжат публичтік ұсыныс болып табылады. Осы ұсынысты қабылдау (акцепт) деп сатып алушының қызмет төлеуі есептеледі.'
      },
      {
        title: 'Шарттың мәні',
        text: 'Орындаушы сатып алушыға онлайн олимпиадасы <strong>Eurika</strong> арқылы 3–11 сыныптарының оқушыларына арналған математика, ағылшын тілінде қатысуға қол жеткізуді ұсынады. Қызмет eurikaolympiads.com сайты арқылы интернет арасында қашықтықтан берілінеді.'
      },
      {
        title: 'Қызметтердің құны және төлеу тәртібі',
        text: 'Бір олимпиадаға қатысуының құны <strong>2000 (екі мың) теңге</strong> құрайды. Төлем сайтта қол жетімді төлем әдістері арқылы біржамасын жүргізіледі. Қызмет Орындаушының шотына ақша түскен сәтінен бастап төлінген болып есептеледі.'
      },
      {
        title: 'Қызметтерді ұсыну тәртібі',
        text: 'Төлегеннен кейін сатып алушыға олимпиадаға қатысуға қол жеткізіледі. Олимпиаданы өткізу сроктары және қатысу шарттары сайтта жарияланады. Орындаушы оның қарамағына тіс емес себептердің салдарынан қатысудың мүмкін еместігіне жауапты емес (интернеттегі проблемалар, пайдаланушының құрылғысындағы ақаулар және т.б.).'
      },
      {
        title: 'Тараптардың құқықтары және міндеттері',
        list: [
          '<strong>Орындаушы міндеттенеді:</strong>',
          'Олимпиадаға қол жеткізуді ұсыну',
          'Платформаның ағымды жұмысын қамтамасыз ету (техникалық мүмкіндіктер аясында)',
          '<strong>Сатып алушы міндеттенеді:</strong>',
          'Тіркелуде дәл деректер ұсыну',
          'Олимпиадаға қатысу ережелерін сақтау'
        ]
      },
      {
        title: 'Ақшаны қайтару',
        text: 'Олимпиадаға қол жеткізіліп берілгеннен кейін қызмет ұсынылған болып есептеледі. Орындаушының кінәсінен болған техникалық ақаулар жағдайлары басқа қайтара барлық ақша қайтарылмайды.'
      },
      {
        title: 'Тараптардың жауапкершілігі',
        text: 'Тараптар Қазақстан Республикасының заңнамасына сәйкес жауапты. Орындаушы олимпиадада сатып алушының қатысуының нәтижелеріне жауапты емес.'
      },
      {
        title: 'Қорытынды ережелер',
        text: 'Орындаушы осы ұсынысына алдын ала ескертпесіз өзгеріс енгізу құқығына ие. Жаңа редакция сайтта жарияланған сәтінен бастап күшіне енеді. Сатып алушы өзі өзгерістерді қадағалау міндеттенеді.'
      },
      {
        title: 'Орындаушының деректемелері',
        list: [
          '<strong>Компания аты:</strong> ТОО "AZ GROUP LLC"',
          '<strong>БСН:</strong> 241140003039',
          '<strong>Мекен-жайы:</strong> Астана, Ақ-Бұлақ-3 тұрғын массиві Аскар Төкпанов көшесі, 27 үй',
          '<strong>Телефон:</strong> +7 (700) 033 02 26'
        ]
      }
    ]
  }
}

const handleScroll = () => {
  const el = contentEl.value
  if (!el) return
  scrolledToBottom.value = el.scrollTop + el.clientHeight >= el.scrollHeight - 16
}

const confirmOffer = async () => {
  if (!agreed.value) return
  loading.value = true
  error.value = ''
  localStorage.setItem('offer_accepted', 'true')
  emit('accepted')
  loading.value = false
  success.value = true
}
</script>

<style scoped>
.offer-page {
  width: 100%;
}

.offer-card {
  display: flex;
  flex-direction: column;
  max-height: calc(100dvh - 64px);
  overflow: hidden;
  border-radius: var(--radius-xl);
  background: var(--card);
  box-shadow: var(--shadow-lg);
}

/* ---- Шапка ---- */
.offer-header {
  padding: 24px 28px 18px;
  border-bottom: 1px solid var(--border);
}

.offer-header__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
}

.offer-header__icon {
  width: 48px;
  height: 48px;
  display: grid;
  place-items: center;
  border-radius: 15px;
  background: var(--brand);
  color: var(--sun);
  transform: rotate(-5deg);
}

.language-selector {
  display: inline-flex;
  gap: 4px;
  padding: 4px;
  border-radius: var(--radius-sm);
  background: var(--bg-alt);
}

.lang-btn {
  min-width: 48px;
  min-height: 36px;
  padding: 6px 12px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 700;
}

.lang-btn.active {
  background: var(--card);
  color: var(--brand-ink);
  box-shadow: var(--shadow-sm);
}

.offer-title {
  font-size: clamp(22px, 3vw, 28px);
  letter-spacing: -0.01em;
}

.offer-subtitle {
  margin-top: 4px;
  color: var(--text-secondary);
  font-size: 15px;
}

/* ---- Текст оферты ---- */
.offer-content {
  position: relative;
  flex: 1;
  min-height: 0;
  overflow-y: auto;
  padding: 20px 28px 8px;
  overscroll-behavior: contain;
}

.offer-intro {
  margin-bottom: 18px;
  padding: 14px 16px;
  border-radius: var(--radius-md);
  background: var(--brand-softer);
  color: var(--text);
  font-size: 15px;
  line-height: 1.6;
}

.offer-section {
  display: grid;
  grid-template-columns: 40px minmax(0, 1fr);
  gap: 14px;
  padding: 14px 0;
  border-bottom: 1px solid var(--border);
}

.offer-section:last-of-type { border-bottom: 0; }

.offer-section__num {
  width: 40px;
  height: 40px;
  display: grid;
  place-items: center;
  border-radius: 12px;
  background: var(--bg-alt);
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 700;
}

.offer-section__title {
  margin-bottom: 6px;
  font-family: var(--font-sans);
  font-size: 16px;
  font-weight: 700;
  letter-spacing: 0;
}

.offer-section p,
.offer-list {
  color: var(--text-secondary);
  font-size: 14.5px;
  line-height: 1.65;
}

.offer-list {
  display: grid;
  gap: 4px;
  padding-left: 18px;
}

.offer-section :deep(strong) { color: var(--text); }

.content-fade {
  position: sticky;
  bottom: -8px;
  height: 48px;
  margin-top: -48px;
  background: linear-gradient(to bottom, transparent, var(--card));
  pointer-events: none;
  transition: opacity var(--dur) ease;
}

.content-fade.hidden { opacity: 0; }

.scroll-hint {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 6px;
  color: var(--text-tertiary);
  font-size: 13px;
  font-weight: 500;
  transition: opacity var(--dur) ease;
}

.scroll-hint svg { animation: hint-bounce 1.6s ease-in-out infinite; }
.scroll-hint.hidden { opacity: 0; }

@keyframes hint-bounce {
  0%, 100% { transform: translateY(0); }
  50%      { transform: translateY(3px); }
}

/* ---- Согласие ---- */
.offer-agreement {
  display: grid;
  gap: 12px;
  padding: 16px 28px 24px;
  border-top: 1px solid var(--border);
  background: var(--bg);
}

.checkbox-wrap {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 14px;
  border-radius: var(--radius-md);
  border: 2px solid var(--border-strong);
  background: var(--card);
  cursor: pointer;
  transition: border-color var(--dur) ease, background-color var(--dur) ease;
}

.checkbox-wrap.checked {
  border-color: var(--brand);
  background: var(--brand-softer);
}

.checkbox-wrap:has(input:focus-visible) { box-shadow: var(--focus-ring); }

.checkbox-wrap input {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
}

.checkbox-custom {
  width: 22px;
  height: 22px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  margin-top: 1px;
  border-radius: 7px;
  border: 2px solid var(--border-strong);
  background: var(--card);
  transition: background-color var(--dur) ease, border-color var(--dur) ease;
}

.checkbox-wrap.checked .checkbox-custom {
  border-color: var(--brand);
  background: var(--brand);
}

.checkbox-label {
  font-size: 15px;
  font-weight: 500;
  line-height: 1.5;
}

.confirm-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  min-height: 54px;
  border: 0;
  border-radius: var(--radius-md);
  background: var(--bg-alt);
  color: var(--text-tertiary);
  font-size: 16px;
  font-weight: 700;
  cursor: not-allowed;
  transition: background-color var(--dur) ease, color var(--dur) ease, box-shadow var(--dur) ease, transform var(--dur) var(--ease-out);
}

.confirm-btn--active {
  background: var(--brand);
  color: var(--on-brand);
  box-shadow: var(--shadow-brand);
  cursor: pointer;
}

.confirm-btn--active:hover { background: var(--brand-hover); }
.confirm-btn--active:active { transform: scale(0.98); }

.btn-loader {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 2.5px solid rgba(255, 255, 255, 0.35);
  border-top-color: #ffffff;
  animation: offer-spin 0.7s linear infinite;
}

@keyframes offer-spin { to { transform: rotate(360deg); } }

.msg {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
}

.msg--error { color: var(--danger-ink); }
.msg--success { color: var(--success-ink); }

.msg-enter-active,
.msg-leave-active { transition: opacity var(--dur) ease; }
.msg-enter-from,
.msg-leave-to { opacity: 0; }

@media (max-width: 560px) {
  .offer-card { max-height: calc(100dvh - 24px); border-radius: var(--radius-lg); }
  .offer-header { padding: 18px 18px 14px; }
  .offer-content { padding: 16px 18px 8px; }
  .offer-agreement { padding: 14px 18px 18px; }
  .offer-section { grid-template-columns: 1fr; gap: 8px; }
}
</style>
