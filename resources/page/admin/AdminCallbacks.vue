<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Callbacks</p>
        <h1>Заявки на обратный звонок</h1>
      </div>
      <button class="primary-btn" @click="downloadExport">Выгрузить Excel</button>
    </header>

    <section class="table-card">
      <table>
        <thead>
          <tr>
            <th>Имя</th>
            <th>Телефон</th>
            <th>Email</th>
            <th>Сообщение</th>
            <th>Дата</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in callbacks" :key="item.id">
            <td>{{ item.name }}</td>
            <td>{{ item.phone }}</td>
            <td>{{ item.email || '—' }}</td>
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
.admin-page { min-height: 100vh; padding: 28px; color: #102347; }
.header, .table-card { background: rgba(255,255,255,0.94); border: 1px solid rgba(148,163,184,0.2); border-radius: 24px; padding: 22px; box-shadow: 0 18px 48px rgba(15,35,85,0.08); }
.header { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
.eyebrow { margin: 0 0 6px; text-transform: uppercase; letter-spacing: 0.08em; font-size: 12px; font-weight: 700; color: #e11d48; }
.table-card { margin-top: 18px; overflow-x: auto; }
.primary-btn { border: 0; border-radius: 14px; padding: 12px 16px; background: linear-gradient(90deg, #2563eb, #1d4ed8); color: white; font-weight: 700; cursor: pointer; }
table { width: 100%; border-collapse: collapse; }
th, td { text-align: left; padding: 12px 10px; border-bottom: 1px solid #dbe6f3; vertical-align: top; }
@media (max-width: 760px) { .admin-page { padding: 16px; } .header { flex-direction: column; align-items: stretch; } }
</style>
