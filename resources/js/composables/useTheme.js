import { ref, onMounted } from 'vue'

const STORAGE_KEY = 'theme'

function getInitialDark() {
  if (typeof window === 'undefined') return true

  const stored = localStorage.getItem(STORAGE_KEY)
  if (stored === 'dark') return true
  if (stored === 'light') return false

  return true
}

export function useTheme() {
  const isDark = ref(getInitialDark())

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

  onMounted(() => {
    applyTheme(isDark.value)
    syncFromDom()
  })

  return { isDark, toggle, applyTheme }
}
