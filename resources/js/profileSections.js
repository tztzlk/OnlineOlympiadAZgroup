// Иконки — пути SVG (viewBox 0 0 24 24, stroke) для вкладок кабинета.
export const profileSections = [
  { name: 'ProfileOverview', label: 'Обзор', to: '/profile', icon: 'M3 10l9-7 9 7v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM9 22V12h6v10' },
  { name: 'ProfileChildren', label: 'Участники', to: '/profile/children', icon: 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8' },
  { name: 'ProfileOlympiads', label: 'Олимпиады', to: '/profile/olympiads', icon: 'M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0zM17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3' },
  { name: 'ProfilePayments', label: 'Оплаты', to: '/profile/payments', icon: 'M2 6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zM2 10h20M6 15h4' },
  { name: 'ProfileTraining', label: 'Тренировки', to: '/profile/training', icon: 'M6.5 6.5l11 11M21 21l-1-1M3 3l1 1M18 22l4-4M2 6l4-4M3 10l7-7M14 21l7-7' },
  { name: 'ProfileNotifications', label: 'Уведомления', to: '/profile/notifications', icon: 'M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 0 1-3.4 0' },
]
