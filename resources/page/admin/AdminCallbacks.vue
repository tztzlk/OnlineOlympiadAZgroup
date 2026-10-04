<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Обращения</p>
        <h1>Help Desk и обратные звонки</h1>
        <p class="subtext">Входящие обращения из формы поддержки и запросы обратного звонка.</p>
      </div>
      <button class="primary-btn" @click="downloadExport">Выгрузить Excel</button>
    </header>

    <div v-if="loading" class="state-card">Загружаем обращения...</div>
    <div v-else-if="!callbacks.length" class="state-card empty">Обращений пока нет.</div>

    <section v-else class="table-card">
      <table>
        <thead>
          <tr>
            <th>Тип</th>
            <th>Имя</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Тема</th>
            <th>Сообщение</th>
            <th>Дата</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in callbacks" :key="item.id">
            <td>
              <span class="type-badge" :class="item.type">
                {{ item.type === 'helpdesk' ? 'Help Desk' : 'Звонок' }}
              </span>
            </td>
            <td>{{ item.name || '—' }}</td>
            <td>{{ item.phone && item.phone !== 'not_provided' ? item.phone : '—' }}</td>
            <td>{{ item.email || '—' }}</td>
            <td>{{ item.topic || '—' }}</td>
            <td class="message-cell" :title="item.message">{{ item.message || '—' }}</td>
            <td class="date-cell">{{ item.date }}</td>
          </tr>
        </tbody>
      </table>
    </section>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../js/api'

const callbacks = ref([])
const loading = ref(true)

const load = async () => {
  loading.value = true
  try {
    const { data } = await api.get('/admin/callbacks')
    callbacks.value = data
  } finally {
    loading.value = false
  }
}

const downloadExport = async () => {
  const { data } = await api.get('/admin/callbacks/export', { responseType: 'blob' })
  const url = URL.createObjectURL(new Blob([data], { type: 'application/vnd.ms-excel' }))
  const link = document.createElement('a')
  link.href = url
  link.download = 'callbacks.xls'
  link.click()
  URL.revokeObjectURL(url)
}

onMounted(load)
</script>

<style scoped>
.type-badge.helpdesk { background: var(--brand-soft); color: var(--brand-ink); }
.type-badge:not(.helpdesk) { background: var(--sun-soft); color: var(--sun-ink); }

.message-cell {
  max-width: 360px;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  color: var(--text-secondary);
}

.date-cell {
  color: var(--text-tertiary);
  white-space: nowrap;
  font-variant-numeric: tabular-nums;
}
</style>
