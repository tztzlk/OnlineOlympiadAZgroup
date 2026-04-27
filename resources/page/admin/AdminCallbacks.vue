<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Обращения</p>
        <h1>Help Desk и обратные звонки</h1>
      </div>
      <button class="primary-btn" @click="downloadExport">Выгрузить Excel</button>
    </header>

    <section class="table-card">
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
            <td>{{ item.type === 'helpdesk' ? 'Help Desk' : 'Callback' }}</td>
            <td>{{ item.name }}</td>
            <td>{{ item.phone && item.phone !== 'not_provided' ? item.phone : '—' }}</td>
            <td>{{ item.email || '—' }}</td>
            <td>{{ item.topic || '—' }}</td>
            <td>{{ item.message || '—' }}</td>
            <td>{{ item.date }}</td>
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

const load = async () => {
  const { data } = await api.get('/admin/callbacks')
  callbacks.value = data
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
* { box-sizing: border-box; }

.admin-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(201, 171, 99, 0.12), transparent 24%),
    linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%);
  padding: 28px;
  color: var(--text);
  display: grid;
  gap: 18px;
  align-content: start;
}

.header,
.table-card {
  background: var(--surface);
  border: 1px solid var(--surface-border);
  border-radius: var(--radius-lg);
  padding: 22px;
  box-shadow: var(--shadow-card);
}

.header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 6px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  font-size: 12px;
  font-weight: 700;
  color: var(--accent-strong);
}

h1 {
  margin: 0;
  font-size: clamp(26px, 4vw, 38px);
  color: var(--text);
}

.primary-btn {
  flex-shrink: 0;
  border: 0;
  border-radius: var(--radius-sm);
  padding: 12px 16px;
  background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%);
  color: var(--text);
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 12px 26px rgba(201, 171, 99, 0.2);
}

.table-card {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 700px;
}

thead th {
  color: var(--text-secondary);
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0 14px 14px;
  text-align: left;
  border-bottom: 1px solid var(--surface-border);
}

tbody td {
  padding: 14px;
  text-align: left;
  border-bottom: 1px solid var(--surface-border);
  vertical-align: top;
  color: var(--text);
}

tbody tr:last-child td {
  border-bottom: 0;
}

tbody tr:hover td {
  background: rgba(201, 171, 99, 0.04);
}

@media (max-width: 760px) {
  .admin-page { padding: 16px; }
  .header { flex-direction: column; align-items: stretch; }
}
</style>
