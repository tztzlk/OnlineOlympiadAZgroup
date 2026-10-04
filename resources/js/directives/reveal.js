// v-reveal — плавное появление блока при прокрутке.
// Использование: <div v-reveal> или <li v-reveal="index"> (индекс задаёт задержку каскада).
// Без IntersectionObserver (или при reduced motion) блок просто виден сразу.

let observer = null

function getObserver() {
  if (observer || typeof window === 'undefined' || !('IntersectionObserver' in window)) {
    return observer
  }

  observer = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-revealed')
        observer.unobserve(entry.target)
      }
    }
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 })

  return observer
}

export default {
  mounted(el, binding) {
    const io = getObserver()
    const reduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches

    if (!io || reduced) {
      return
    }

    el.setAttribute('data-reveal', '')

    if (Number.isFinite(binding.value)) {
      el.style.setProperty('--reveal-i', String(Math.min(binding.value, 8)))
    }

    io.observe(el)
  },
  unmounted(el) {
    observer?.unobserve(el)
  },
}
