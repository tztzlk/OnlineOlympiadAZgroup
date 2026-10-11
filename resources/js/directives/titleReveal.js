// v-title — заголовок появляется по словам: каждое слово выезжает снизу из-под маски.
// Использование: <h2 v-title>Три шага до результата</h2>
// Только для заголовков со статичным текстом: директива заменяет текстовые узлы
// на обёртки, и Vue уже не сможет обновить такой текст реактивно.
// Вложенные элементы (например, выделенное слово) сохраняются и тоже делятся на слова.

let observer = null

function getObserver() {
  if (observer || typeof window === 'undefined' || !('IntersectionObserver' in window)) {
    return observer
  }

  observer = new IntersectionObserver((entries) => {
    for (const entry of entries) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-title-in')
        observer.unobserve(entry.target)
      }
    }
  }, { rootMargin: '0px 0px -10% 0px', threshold: 0.2 })

  return observer
}

function splitWords(node, words) {
  for (const child of [...node.childNodes]) {
    if (child.nodeType === Node.TEXT_NODE) {
      const fragment = document.createDocumentFragment()
      for (const part of child.textContent.split(/(\s+)/)) {
        if (!part) continue
        if (/^\s+$/.test(part)) {
          fragment.appendChild(document.createTextNode(' '))
          continue
        }
        const mask = document.createElement('span')
        mask.className = 'tr-mask'
        const word = document.createElement('span')
        word.className = 'tr-word'
        word.textContent = part
        mask.appendChild(word)
        fragment.appendChild(mask)
        words.push(word)
      }
      child.replaceWith(fragment)
    } else if (child.nodeType === Node.ELEMENT_NODE && child.tagName !== 'BR' && child.tagName !== 'svg') {
      splitWords(child, words)
    }
  }
}

export default {
  mounted(el) {
    const io = getObserver()
    const reduced = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
    if (!io || reduced || el.dataset.title === 'split') return

    const words = []
    splitWords(el, words)
    words.forEach((word, index) => word.style.setProperty('--tr-i', String(Math.min(index, 14))))
    el.dataset.title = 'split'

    io.observe(el)
  },
  unmounted(el) {
    observer?.unobserve(el)
  },
}
