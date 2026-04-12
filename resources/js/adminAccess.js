export const adminSections = [
  { key: 'dashboard', label: 'Панель', to: '/admin' },
  { key: 'requests', label: 'Заявки', to: '/admin/requests' },
  { key: 'quizzes', label: 'Олимпиады', to: '/admin/quizzes' },
  { key: 'results', label: 'Результаты', to: '/admin/results' },
  { key: 'payments', label: 'Оплаты', to: '/admin/payments' },
  { key: 'callbacks', label: 'Обращения', to: '/admin/callbacks' },
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
