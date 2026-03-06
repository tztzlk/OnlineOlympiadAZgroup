<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="sidebar__brand">
        <div class="brand-icon">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <rect x="1" y="1" width="8" height="8" rx="2" fill="#3B82F6"/>
            <rect x="11" y="1" width="8" height="8" rx="2" fill="#3B82F6" opacity="0.5"/>
            <rect x="1" y="11" width="8" height="8" rx="2" fill="#3B82F6" opacity="0.5"/>
            <rect x="11" y="11" width="8" height="8" rx="2" fill="#3B82F6" opacity="0.3"/>
          </svg>
        </div>
        <span class="brand-name">AdminPanel</span>
      </div>

      <nav class="sidebar__nav">
        <router-link to="/admin" class="nav-item">
          <svg width="17" height="17" viewBox="0 0 17 17" fill="none">
            <rect x="1" y="1" width="6.5" height="6.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
            <rect x="9.5" y="1" width="6.5" height="6.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
            <rect x="1" y="9.5" width="6.5" height="6.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
            <rect x="9.5" y="9.5" width="6.5" height="6.5" rx="1.5" stroke="currentColor" stroke-width="1.5"/>
          </svg>
          <span>Dashboard</span>
        </router-link>

        <router-link to="/admin/requests" class="nav-item">
          <svg width="17" height="17" viewBox="0 0 17 17" fill="none">
            <path d="M2 4a1 1 0 011-1h11a1 1 0 011 1v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4z" stroke="currentColor" stroke-width="1.5"/>
            <path d="M5 7h7M5 10h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <span>Requests</span>
        </router-link>

        <router-link to="/admin/quizzes" class="nav-item">
          <svg width="17" height="17" viewBox="0 0 17 17" fill="none">
            <rect x="2" y="2" width="13" height="13" rx="2" stroke="currentColor" stroke-width="1.5"/>
            <path d="M5.5 8.5h6M5.5 11h4M5.5 6h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
          </svg>
          <span>Quizzes</span>
        </router-link>

        <router-link to="/admin/results" class="nav-item">
          <svg width="17" height="17" viewBox="0 0 17 17" fill="none">
            <path d="M3 13V10M6.5 13V6M10 13V8M13.5 13V4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M3 10L6.5 7L10 9L13.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Results</span>
        </router-link>
      </nav>

      <div class="sidebar__footer">
        <router-link to="/" class="btn-home">
          <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
            <path d="M1.5 7L7.5 1.5L13.5 7V13H9.5V9.5H5.5V13H1.5V7Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
          </svg>
          На главную
        </router-link>
        <div class="admin-badge">
          <div class="admin-avatar">A</div>
          <div class="admin-info">
            <span class="admin-name">Administrator</span>
            <span class="admin-role">Superadmin</span>
          </div>
        </div>
      </div>
    </aside>

    <main class="content">
      <router-view />
    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.admin-layout {
  display: flex;
  min-height: 100vh;
  font-family: 'Sora', sans-serif;
}

/* ── Sidebar ── */
.sidebar {
  width: 230px;
  flex-shrink: 0;
  background: #FFFFFF;
  border-right: 1px solid #E2EAFC;
  display: flex;
  flex-direction: column;
  padding: 24px 0;
  position: sticky;
  top: 0;
  height: 100vh;
}

.sidebar__brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 20px 24px;
  border-bottom: 1px solid #EEF2FF;
}

.brand-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px; height: 36px;
  background: linear-gradient(135deg, #EFF6FF, #DBEAFE);
  border-radius: 9px;
  border: 1px solid rgba(59, 130, 246, 0.15);
  flex-shrink: 0;
}

.brand-name {
  font-size: 14.5px;
  font-weight: 600;
  color: #0F2355;
  letter-spacing: -0.2px;
}

/* Nav */
.sidebar__nav {
  flex: 1;
  padding: 16px 10px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 400;
  color: #6B84B0;
  text-decoration: none;
  transition: background 0.15s, color 0.15s;
}

.nav-item:hover {
  background: #F0F5FF;
  color: #2563EB;
}

.nav-item.router-link-active,
.nav-item.router-link-exact-active {
  background: linear-gradient(135deg, #EFF6FF, #DBEAFE);
  color: #2563EB;
  font-weight: 500;
}

/* Footer */
.sidebar__footer {
  padding: 16px 12px 0;
  border-top: 1px solid #EEF2FF;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.btn-home {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 12px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
  color: #6B84B0;
  text-decoration: none;
  border: 1px solid #E2EAFC;
  background: #F8FAFF;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}

.btn-home:hover {
  background: #EFF6FF;
  color: #2563EB;
  border-color: #BFDBFE;
}

.admin-badge {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border-radius: 10px;
  background: #F8FAFF;
}

.admin-avatar {
  width: 32px; height: 32px;
  background: linear-gradient(135deg, #3B82F6, #1D4ED8);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white;
  font-size: 13px;
  font-weight: 600;
  flex-shrink: 0;
}

.admin-info {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.admin-name {
  font-size: 12.5px;
  font-weight: 500;
  color: #0F2355;
}

.admin-role {
  font-size: 11px;
  color: #93B4D8;
}

/* ── Content ── */
.content {
  flex: 1;
  background: #F0F5FF;
  min-width: 0;
}
</style>