<template>
  <div>
    <!-- Кнопка смены языка -->
    <button @click="toggleLanguage">
      {{ $i18n.locale === 'ru' ? 'EN' : 'RU' }}
    </button>

    <h1>{{ $t('greeting') }}</h1>
    <p>{{ $t('welcome') }}</p>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()

const toggleLanguage = () => {
  locale.value = locale.value === 'ru' ? 'en' : 'ru'
  localStorage.setItem('lang', locale.value)
}
</script>
