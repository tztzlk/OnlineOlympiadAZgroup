// Сервер присылает ссылки действий полным адресом (APP_URL + путь).
// Для RouterLink нужен путь: ссылку на свой сайт превращаем в путь, внешнюю не трогаем.
export function toInternalPath(url) {
  if (!url) return '/'

  try {
    const parsed = new URL(url, window.location.origin)
    if (parsed.origin === window.location.origin || /^\//.test(url)) {
      return `${parsed.pathname}${parsed.search}${parsed.hash}` || '/'
    }
  } catch {
    return '/'
  }

  return url
}
