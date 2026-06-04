<template>
  <div class="offer-page">

    <div class="bg-orb bg-orb--1"></div>
    <div class="bg-orb bg-orb--2"></div>

    <div class="offer-card">

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
              class="lang-btn"
              :class="{ active: language === 'ru' }"
              @click="language = 'ru'"
            >
              РУ
            </button>
            <button
              class="lang-btn"
              :class="{ active: language === 'kk' }"
              @click="language = 'kk'"
            >
              КК
            </button>
          </div>
        </div>
        <h1 class="offer-title">{{ content[language].title }}</h1>
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
    title: 'Публичная оферта',
    subtitle: 'Внимательно ознакомьтесь с условиями перед подтверждением',
    intro: 'Используя сервис, вы принимаете условия публичной оферты. Внимательно ознакомьтесь с текстом документа перед подтверждением.',
    checkbox: 'Я прочитал(а) и согласен(на) с условиями публичной оферты',
    confirmBtn: 'Подтвердить и продолжить',
    accepted: 'Принято',
    successMsg: 'Оферта успешно принята',
    scrollHint: 'Прокрутите для ознакомления',
    sections: [
      {
        title: 'Общие положения',
        text: 'Настоящий документ является официальным предложением (публичной офертой) Образовательной компании ТОО «AZ GROUP LLC» (далее — Исполнитель) заключить договор на оказание услуг на изложенных ниже условиях. В соответствии с гражданским законодательством Республики Казахстан данный документ является публичной офертой. Акцептом (принятием) настоящей оферты считается факт оплаты услуги Заказчиком.'
      },
      {
        title: 'Предмет договора',
        text: 'Исполнитель предоставляет Заказчику доступ к участию в онлайн-олимпиаде <strong>Eurika</strong> по математике, английскому языку для учащихся 3–11 классов. Услуга предоставляется в дистанционном формате через интернет на сайте eurikaolympiads.com.'
      },
      {
        title: 'Стоимость услуг и порядок оплаты',
        text: 'Стоимость участия в одной олимпиаде составляет <strong>2000 (две тысячи) тенге</strong>. Оплата производится единовременно через доступные на сайте способы оплаты. Услуга считается оплаченной с момента поступления денежных средств на счёт Исполнителя.'
      },
      {
        title: 'Порядок оказания услуг',
        text: 'После оплаты Заказчику предоставляется доступ к участию в олимпиаде. Сроки проведения олимпиады и условия участия публикуются на сайте. Исполнитель не несёт ответственности за невозможность участия по причинам, не зависящим от него (проблемы с интернетом, устройством пользователя и др.).'
      },
      {
        title: 'Права и обязанности сторон',
        list: [
          '<strong>Исполнитель обязуется:</strong>',
          'Предоставить доступ к олимпиаде',
          'Обеспечить корректную работу платформы (в пределах технических возможностей)',
          '<strong>Заказчик обязуется:</strong>',
          'Предоставить достоверные данные при регистрации',
          'Соблюдать правила участия в олимпиаде'
        ]
      },
      {
        title: 'Возврат средств',
        text: 'После предоставления доступа к олимпиаде услуга считается оказанной. Возврат денежных средств не осуществляется, за исключением случаев технических сбоев по вине Исполнителя.'
      },
      {
        title: 'Ответственность сторон',
        text: 'Стороны несут ответственность в соответствии с законодательством Республики Казахстан. Исполнитель не несёт ответственности за результаты участия Заказчика в олимпиаде.'
      },
      {
        title: 'Заключительные положения',
        text: 'Исполнитель имеет право вносить изменения в настоящую оферту без предварительного уведомления. Новая редакция вступает в силу с момента её публикации на сайте. Заказчик обязуется самостоятельно отслеживать изменения.'
      },
      {
        title: 'Реквизиты Исполнителя',
        list: [
          '<strong>Название компании:</strong> ТОО "AZ GROUP LLC"',
          '<strong>БИН:</strong> 241140003039',
          '<strong>Адрес:</strong> Астана, Жилой массив Ақ-Бұлақ-3 улица Аскар Токпанов, дом 27',
          '<strong>Телефон:</strong> +7 (700) 033 02 26'
        ]
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

.offer-header__top {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-bottom: 18px;
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
  flex-shrink: 0;
}

.language-selector {
  display: flex;
  gap: 6px;
  background: rgba(225, 29, 72, 0.08);
  padding: 4px;
  border-radius: 10px;
  border: 1px solid rgba(225, 29, 72, 0.15);
}

.lang-btn {
  padding: 6px 12px;
  border: none;
  border-radius: 8px;
  background: transparent;
  color: var(--text-muted-on-surface);
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  letter-spacing: 0.5px;
}

.lang-btn.active {
  background: #E11D48;
  color: white;
  box-shadow: 0 2px 8px rgba(225, 29, 72, 0.25);
}

.lang-btn:hover:not(.active) {
  background: rgba(225, 29, 72, 0.1);
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

.offer-section p,
.offer-list li {
  font-size: 13.5px;
  line-height: 1.7;
  color: var(--text-muted-on-surface);
  font-weight: 300;
}

.offer-list {
  margin: 0;
  padding-left: 20px;
  list-style: none;
}

.offer-list li {
  margin-bottom: 8px;
  padding-left: 8px;
}

.offer-list li:before {
  content: "•";
  margin-right: 8px;
  color: #E11D48;
  font-weight: bold;
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
  .offer-header__top { flex-direction: column; gap: 12px; }
}
</style>

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
