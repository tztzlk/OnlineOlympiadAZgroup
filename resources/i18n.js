// src/i18n.js
import { createI18n } from 'vue-i18n'

const messages = {
  en: {
    greeting: "Hello",
    welcome: "Welcome to our website"
  },
  ru: {
    greeting: "Привет",
    welcome: "Добро пожаловать на наш сайт"
  }
}

export const i18n = createI18n({
  locale: 'ru',       // язык по умолчанию
  fallbackLocale: 'en',
  messages,
})
