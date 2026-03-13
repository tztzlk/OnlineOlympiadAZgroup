import { ref, onMounted } from 'vue'

const STORAGE_KEY = 'theme'

export function useTheme() {
  const isDark = ref(document.documentElement.classList.contains('dark'))

  function applyTheme(dark) {
    isDark.value = dark
    document.documentElement.classList.toggle('dark', dark)
    localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light')
  }

  function toggle() {
    applyTheme(!isDark.value)
  }

  function syncFromDom() {
    isDark.value = document.documentElement.classList.contains('dark')
  }

  onMounted(syncFromDom)

  return { isDark, toggle, applyTheme }
}
