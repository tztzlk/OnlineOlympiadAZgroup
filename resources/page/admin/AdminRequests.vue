<template>
  <div class="admin-page">

    <!-- Topbar -->
    <header class="topbar">
      <div class="topbar__left">
        <h1 class="page-title">Заявки</h1>
        <span class="page-subtitle">Всего: {{ filteredRequests.length }}</span>
      </div>
      <div class="topbar__right">
        <div class="search-box">
          <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <circle cx="6" cy="6" r="4.5" stroke="#93B4D8" stroke-width="1.5"/>
            <path d="M9.5 9.5L12 12" stroke="#93B4D8" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <input v-model="search" type="text" placeholder="Поиск по имени или email..." />
        </div>
        <div class="filter-tabs">
          <button
            v-for="f in filters"
            :key="f.value"
            class="filter-tab"
            :class="{ 'filter-tab--active': activeFilter === f.value }"
            @click="activeFilter = f.value"
          >
            {{ f.label }}
          </button>
        </div>
      </div>
    </header>

    <!-- Skeleton -->
    <div v-if="loading" class="table-wrap">
      <div class="skeleton-row" v-for="i in 5" :key="i" :style="{ animationDelay: i * 0.07 + 's' }"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!filteredRequests.length" class="empty">
      <div class="empty__icon">
        <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
          <path d="M4 8a2 2 0 012-2h24a2 2 0 012 2v20a2 2 0 01-2 2H6a2 2 0 01-2-2V8z" stroke="#BFDBFE" stroke-width="2"/>
          <path d="M10 14h16M10 19h10" stroke="#BFDBFE" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </div>
      <p class="empty__text">Заявок не найдено</p>
    </div>

    <!-- Table -->
    <div v-else class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th @click="sortBy('id')" class="th-sortable">
              ID
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none" :class="{ 'sort-active': sortKey === 'id', 'sort-desc': sortKey === 'id' && sortDesc }">
                <path d="M5 2v6M2.5 5.5L5 8l2.5-2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </th>
            <th @click="sortBy('name')" class="th-sortable">
              Имя
              <svg width="10" height="10" viewBox="0 0 10 10" fill="none" :class="{ 'sort-active': sortKey === 'name', 'sort-desc': sortKey === 'name' && sortDesc }">
                <path d="M5 2v6M2.5 5.5L5 8l2.5-2.5" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </th>
            <th>Email</th>
            <th>Статус</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(req, i) in filteredRequests"
            :key="req.id"
            class="table-row"
            :style="{ animationDelay: i * 0.04 + 's' }"
          >
            <td class="td-id">#{{ req.id }}</td>
            <td class="td-name">
              <div class="user-cell">
                <div class="user-avatar" :style="{ background: avatarColor(req.name) }">
                  {{ req.name?.charAt(0).toUpperCase() }}
                </div>
                <span>{{ req.name }}</span>
              </div>
            </td>
            <td class="td-email">{{ req.email }}</td>
            <td>
              <span class="status-badge" :class="'status-badge--' + req.status">
                <span class="status-dot"></span>
                {{ statusLabel(req.status) }}
              </span>
            </td>
            <td class="td-actions">
              <button class="action-btn action-btn--view" title="Просмотр" @click="viewRequest(req)">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M1 7s2-4.5 6-4.5S13 7 13 7s-2 4.5-6 4.5S1 7 1 7z" stroke="currentColor" stroke-width="1.4"/>
                  <circle cx="7" cy="7" r="1.8" stroke="currentColor" stroke-width="1.4"/>
                </svg>
              </button>
              <button class="action-btn action-btn--approve" title="Одобрить" @click="updateStatus(req, 'approved')" v-if="req.status !== 'approved'">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M2.5 7l3 3 6-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
              <button class="action-btn action-btn--reject" title="Отклонить" @click="updateStatus(req, 'rejected')" v-if="req.status !== 'rejected'">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M3 3l8 8M11 3l-8 8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import api from "../../js/api"


const requests = ref([])
const loading = ref(true)
const search = ref("")
const activeFilter = ref("all")
const sortKey = ref("id")
const sortDesc = ref(false)

const filters = [
  { value: "all",      label: "Все" },
  { value: "pending",  label: "Ожидают" },
  { value: "approved", label: "Одобрены" },
  { value: "rejected", label: "Отклонены" },
]

const statusLabel = (s) => ({
  pending:  "Ожидает",
  approved: "Одобрена",
  rejected: "Отклонена",
}[s] ?? s)

const avatarColors = ["#3B82F6","#6366F1","#0EA5E9","#8B5CF6","#06B6D4","#2563EB"]
const avatarColor = (name) => avatarColors[(name?.charCodeAt(0) ?? 0) % avatarColors.length]

const filteredRequests = computed(() => {
  let list = requests.value
  if (activeFilter.value !== "all") list = list.filter(r => r.status === activeFilter.value)
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(r => r.name?.toLowerCase().includes(q) || r.email?.toLowerCase().includes(q))
  }
  return [...list].sort((a, b) => {
    const va = a[sortKey.value], vb = b[sortKey.value]
    return sortDesc.value ? (va < vb ? 1 : -1) : (va > vb ? 1 : -1)
  })
})

const sortBy = (key) => {
  if (sortKey.value === key) sortDesc.value = !sortDesc.value
  else { sortKey.value = key; sortDesc.value = false }
}

const updateStatus = async (req, status) => {
  try {
    await api.patch(`/admin/requests/${req.id}/status`, { status })

    req.status = status

  } catch (e) {
    alert("Ошибка обновления статуса")
  }
}

const viewRequest = (req) => {
  alert(`Заявка #${req.id}\nИмя: ${req.name}\nEmail: ${req.email}\nСтатус: ${statusLabel(req.status)}`)
}

onMounted(async () => {
  try {
    const res = await api.get('/admin/requests')

   requests.value = res.data.data.map(r => ({
  ...r,
  name: `${r.first_name} ${r.last_name}`,
  email: r.parent_email
}))

  } catch (e) {
    console.error(e)
    alert("Ошибка загрузки заявок")
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.admin-page {
  padding: 28px 32px;
  background: #F0F5FF;
  min-height: 100vh;
  font-family: 'Sora', sans-serif;
  display: flex;
  flex-direction: column;
  gap: 22px;
}

/* ── Topbar ── */
.topbar {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.topbar__left {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.page-title {
  font-size: 22px;
  font-weight: 600;
  color: #0F2355;
  letter-spacing: -0.4px;
}

.page-subtitle {
  font-size: 12.5px;
  color: #93B4D8;
  font-weight: 300;
}

.topbar__right {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FFFFFF;
  border: 1px solid #DBEAFE;
  border-radius: 10px;
  padding: 9px 14px;
}

.search-box input {
  border: none;
  outline: none;
  font-family: 'Sora', sans-serif;
  font-size: 13px;
  color: #0F2355;
  background: transparent;
  width: 210px;
}

.search-box input::placeholder { color: #B0C8E4; }

.filter-tabs {
  display: flex;
  gap: 4px;
  background: #FFFFFF;
  border: 1px solid #DBEAFE;
  border-radius: 10px;
  padding: 4px;
}

.filter-tab {
  padding: 6px 13px;
  border: none;
  border-radius: 7px;
  font-family: 'Sora', sans-serif;
  font-size: 12.5px;
  cursor: pointer;
  background: transparent;
  color: #6B84B0;
  transition: all 0.15s;
  white-space: nowrap;
}

.filter-tab--active {
  background: linear-gradient(135deg, #EFF6FF, #DBEAFE);
  color: #2563EB;
  font-weight: 500;
}

/* ── Skeleton ── */
.skeleton-row {
  height: 52px;
  background: linear-gradient(90deg, #E8EFFC 25%, #F3F7FF 50%, #E8EFFC 75%);
  background-size: 200% 100%;
  border-radius: 10px;
  margin-bottom: 8px;
  animation: shimmer 1.4s infinite;
}

@keyframes shimmer {
  from { background-position: 200% 0; }
  to   { background-position: -200% 0; }
}

/* ── Empty ── */
.empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 64px 24px;
  background: #FFFFFF;
  border-radius: 18px;
  border: 1px dashed #BFDBFE;
}

.empty__icon {
  width: 68px; height: 68px;
  background: #EFF6FF;
  border-radius: 16px;
  display: flex; align-items: center; justify-content: center;
}

.empty__text {
  font-size: 14px;
  color: #93B4D8;
  font-weight: 300;
}

/* ── Table wrap ── */
.table-wrap {
  background: #FFFFFF;
  border-radius: 18px;
  border: 1px solid #E2EAFC;
  overflow: hidden;
}

/* ── Table ── */
.table {
  width: 100%;
  border-collapse: collapse;
}

.table thead tr {
  border-bottom: 1px solid #EEF2FF;
}

.table th {
  padding: 14px 18px;
  font-size: 11.5px;
  font-weight: 500;
  color: #93B4D8;
  text-align: left;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #F8FAFF;
  user-select: none;
}

.th-sortable {
  cursor: pointer;
  display: table-cell;
}

.th-sortable:hover { color: #3B82F6; }

.th-sortable svg {
  margin-left: 4px;
  vertical-align: middle;
  color: #C8D9F0;
  transition: transform 0.2s, color 0.2s;
}

.th-sortable svg.sort-active { color: #3B82F6; }
.th-sortable svg.sort-desc { transform: rotate(180deg); }

/* Rows */
.table-row {
  border-bottom: 1px solid #F0F5FF;
  animation: fadeIn 0.3s ease both;
  transition: background 0.12s;
}

.table-row:last-child { border-bottom: none; }
.table-row:hover { background: #F8FAFF; }

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to   { opacity: 1; transform: translateY(0); }
}

.table td {
  padding: 14px 18px;
  font-size: 13.5px;
  color: #2C3E6A;
  vertical-align: middle;
}

.td-id {
  font-family: 'DM Mono', monospace;
  font-size: 12.5px;
  color: #93B4D8;
  width: 60px;
}

.td-name { font-weight: 500; }

.user-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.user-avatar {
  width: 30px; height: 30px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white;
  font-size: 12px;
  font-weight: 600;
  flex-shrink: 0;
}

.td-email {
  font-size: 13px;
  color: #6B84B0;
}

/* Status badge */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.status-badge--pending  { background: #FEF9C3; color: #CA8A04; }
.status-badge--approved { background: #DCFCE7; color: #16A34A; }
.status-badge--rejected { background: #FEE2E2; color: #DC2626; }

/* Action buttons */
.td-actions {
  display: flex;
  gap: 6px;
}

.action-btn {
  width: 30px; height: 30px;
  border: none;
  border-radius: 7px;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.15s, color 0.15s, transform 0.1s;
}

.action-btn:active { transform: scale(0.92); }

.action-btn--view    { background: #EFF6FF; color: #3B82F6; }
.action-btn--view:hover { background: #DBEAFE; }

.action-btn--approve { background: #F0FDF4; color: #16A34A; }
.action-btn--approve:hover { background: #DCFCE7; }

.action-btn--reject  { background: #FEF2F2; color: #DC2626; }
.action-btn--reject:hover { background: #FEE2E2; }
</style>