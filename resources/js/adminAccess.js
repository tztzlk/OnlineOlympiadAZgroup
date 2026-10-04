// icon — путь SVG (viewBox 0 0 24 24, stroke) для бокового меню админки.
export const adminSections = [
  { key: 'dashboard', label: 'Панель', to: '/admin', icon: 'M3 3h7v9H3zM14 3h7v5h-7zM14 12h7v9h-7zM3 16h7v5H3z' },
  { key: 'requests', label: 'Заявки', to: '/admin/requests', icon: 'M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v0a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2zM9 14l2 2 4-4' },
  { key: 'quizzes', label: 'Олимпиады', to: '/admin/quizzes', icon: 'M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2zM22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z' },
  { key: 'results', label: 'Результаты', to: '/admin/results', icon: 'M18 20V10M12 20V4M6 20v-6' },
  { key: 'payments', label: 'Оплаты', to: '/admin/payments', icon: 'M2 6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zM2 10h20M6 15h4' },
  { key: 'callbacks', label: 'Обращения', to: '/admin/callbacks', icon: 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z' },
]

export const hasAdminAccess = (user) => Boolean(user?.has_admin_access ?? user?.is_admin ?? user?.admin_role)

export const adminCapabilities = (user) => Array.isArray(user?.admin_capabilities) ? user.admin_capabilities : []

export const hasAdminCapability = (user, capability) => {
  if (!hasAdminAccess(user)) return false
  if (!capability) return true
  return adminCapabilities(user).includes(capability)
}

export const firstAdminRoute = (user) => {
  const allowed = adminSections.find((section) => hasAdminCapability(user, section.key))
  return allowed?.to || '/'
}
