<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="brand">
        <div class="brand-mark">OO</div>
        <div class="brand-copy">
          <strong>Online Olympiad</strong>
          <span>Рабочая админ-панель</span>
        </div>
      </div>

      <nav class="nav">
        <router-link to="/admin" class="nav-link" exact-active-class="nav-link-exact-active">Dashboard</router-link>
        <router-link to="/admin/requests" class="nav-link" exact-active-class="nav-link-exact-active">Requests</router-link>
        <router-link to="/admin/quizzes" class="nav-link" exact-active-class="nav-link-exact-active">Quizzes</router-link>
        <router-link to="/admin/results" class="nav-link" exact-active-class="nav-link-exact-active">Results</router-link>
      </nav>

      <div class="sidebar-footer">
        <div class="support-card">
          <span class="support-label">Для сотрудников</span>
          <strong>Высокий контраст и чистые формы</strong>
        </div>
        <router-link to="/" class="back-link">Вернуться на сайт</router-link>
      </div>
    </aside>

    <main class="content">
      <router-view />
    </main>
  </div>
</template>

<style scoped>
* { box-sizing: border-box; }

.admin-layout {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  background: #e9eef7;
}

.sidebar {
  position: sticky;
  top: 0;
  height: 100vh;
  padding: 22px 18px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  background:
    radial-gradient(circle at top, rgba(244, 63, 94, 0.18), transparent 30%),
    linear-gradient(180deg, #0f172a 0%, #16213b 100%);
  color: #fff;
  border-right: 1px solid rgba(255, 255, 255, 0.08);
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 10px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.05);
}

.brand-mark {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  font-weight: 800;
  font-size: 18px;
  background: linear-gradient(135deg, #f43f5e, #e11d48);
  box-shadow: 0 12px 30px rgba(225, 29, 72, 0.35);
}

.brand-copy strong,
.brand-copy span {
  display: block;
}

.brand-copy strong {
  font-size: 24px;
  line-height: 1.1;
}

.brand-copy span {
  margin-top: 6px;
  color: #9fb3d8;
  font-size: 13px;
}

.nav {
  display: grid;
  gap: 10px;
}

.nav-link,
.back-link {
  text-decoration: none;
  color: #d7e0f1;
  padding: 14px 16px;
  border-radius: 16px;
  font-weight: 700;
  transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.nav-link:hover,
.nav-link.nav-link-exact-active,
.back-link:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
  transform: translateX(2px);
}

.sidebar-footer {
  margin-top: auto;
  display: grid;
  gap: 12px;
}

.support-card {
  padding: 16px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.06);
}

.support-label {
  display: block;
  margin-bottom: 8px;
  color: #9fb3d8;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.support-card strong {
  color: #fff;
  line-height: 1.4;
}

.back-link {
  background: rgba(255, 255, 255, 0.05);
  text-align: center;
}

.content {
  min-width: 0;
}

@media (max-width: 960px) {
  .admin-layout {
    grid-template-columns: 1fr;
  }

  .sidebar {
    position: static;
    height: auto;
  }
}

@media (max-width: 640px) {
  .sidebar {
    padding: 16px;
  }

  .brand-copy strong {
    font-size: 20px;
  }

  .nav {
    grid-template-columns: 1fr 1fr;
  }
}
</style>
