const dynamicSchemaSelector = 'script[data-dynamic-seo-schema="true"]'

const normalizePath = (path) => {
  if (!path || path === '/') return '/'
  return `/${String(path).replace(/^\/+|\/+$/g, '')}`
}

const ensureMeta = (selector, attributes, content) => {
  let node = document.head.querySelector(selector)

  if (!node) {
    node = document.createElement('meta')
    Object.entries(attributes).forEach(([key, value]) => node.setAttribute(key, value))
    document.head.appendChild(node)
  }

  node.setAttribute('content', content)
}

const ensureLink = (selector, attributes) => {
  let node = document.head.querySelector(selector)

  if (!node) {
    node = document.createElement('link')
    document.head.appendChild(node)
  }

  Object.entries(attributes).forEach(([key, value]) => node.setAttribute(key, value))
}

const clearDynamicSchemas = () => {
  document.head.querySelectorAll(dynamicSchemaSelector).forEach((node) => node.remove())
}

const applySchemas = (schemas = []) => {
  clearDynamicSchemas()

  schemas.forEach((schema) => {
    const node = document.createElement('script')
    node.type = 'application/ld+json'
    node.dataset.dynamicSeoSchema = 'true'
    node.textContent = JSON.stringify(schema)
    document.head.appendChild(node)
  })
}

export const getStaticSeoForPath = (path) => {
  const map = window.__SEO_ROUTE_MAP__ || {}
  return map[normalizePath(path)] || null
}

export const buildSubjectSeo = (subject) => {
  if (!subject) return null

  const gradeRanges = subject.grade_ranges?.length ? subject.grade_ranges.join(', ') : '3-11 классы'
  const timeLimit = subject.time_limit ? `${subject.time_limit} минут` : 'по правилам олимпиады'
  const title = `${subject.name}: онлайн-олимпиада для школьников`
  const description = `${subject.description || `Онлайн-олимпиада по предмету ${subject.name} для школьников в Казахстане.`} Классы: ${gradeRanges}. Формат участия — онлайн, оплата через Kaspi, результаты и сертификаты доступны на платформе.`
  const canonical = `${(window.__SEO_DEFAULTS__?.canonical || `${window.location.origin}/`).replace(/\/$/, '')}/subjects/${subject.id}`

  return {
    title,
    description,
    canonical,
    image: subject.image || window.__SEO_DEFAULTS__?.image,
    robots: 'index,follow,max-image-preview:large',
    type: 'article',
    schemas: [
      {
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
          { '@type': 'ListItem', position: 1, name: 'Главная', item: `${window.location.origin}/` },
          { '@type': 'ListItem', position: 2, name: subject.name, item: canonical },
        ],
      },
      {
        '@context': 'https://schema.org',
        '@type': 'Course',
        name: title,
        description,
        provider: {
          '@type': 'Organization',
          name: window.__SEO_DEFAULTS__?.siteName || 'Online Olympiad',
          url: window.location.origin,
        },
      },
      {
        '@context': 'https://schema.org',
        '@type': 'FAQPage',
        mainEntity: (subject.faq || []).map((item) => ({
          '@type': 'Question',
          name: item.question,
          acceptedAnswer: {
            '@type': 'Answer',
            text: item.answer,
          },
        })),
      },
    ],
    lang: 'ru-KZ',
    locale: window.__SEO_DEFAULTS__?.locale || 'ru_KZ',
  }
}

export const applySeo = (seo) => {
  if (typeof document === 'undefined' || !seo) return

  const defaults = window.__SEO_DEFAULTS__ || {}
  const merged = {
    ...defaults,
    ...seo,
  }

  document.documentElement.setAttribute('lang', merged.lang || 'ru-KZ')
  document.title = merged.title || defaults.title || 'Online Olympiad'

  ensureMeta('meta[name="description"]', { name: 'description' }, merged.description || defaults.description || '')
  ensureMeta('meta[name="robots"]', { name: 'robots' }, merged.robots || defaults.robots || 'index,follow')
  ensureMeta('meta[property="og:type"]', { property: 'og:type' }, merged.type || defaults.type || 'website')
  ensureMeta('meta[property="og:site_name"]', { property: 'og:site_name' }, defaults.siteName || 'Online Olympiad')
  ensureMeta('meta[property="og:locale"]', { property: 'og:locale' }, merged.locale || defaults.locale || 'ru_KZ')
  ensureMeta('meta[property="og:title"]', { property: 'og:title' }, merged.title || defaults.title || '')
  ensureMeta('meta[property="og:description"]', { property: 'og:description' }, merged.description || defaults.description || '')
  ensureMeta('meta[property="og:url"]', { property: 'og:url' }, merged.canonical || defaults.canonical || window.location.href)
  ensureMeta('meta[property="og:image"]', { property: 'og:image' }, merged.image || defaults.image || '')
  ensureMeta('meta[name="twitter:card"]', { name: 'twitter:card' }, 'summary_large_image')
  ensureMeta('meta[name="twitter:title"]', { name: 'twitter:title' }, merged.title || defaults.title || '')
  ensureMeta('meta[name="twitter:description"]', { name: 'twitter:description' }, merged.description || defaults.description || '')
  ensureMeta('meta[name="twitter:image"]', { name: 'twitter:image' }, merged.image || defaults.image || '')
  ensureLink('link[rel="canonical"]', { rel: 'canonical', href: merged.canonical || defaults.canonical || window.location.href })

  applySchemas(merged.schemas || [])
}
