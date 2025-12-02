<template>
  <div class="app-container">
    <aside class="sidebar">
      <div class="sidebar-logo">
        <span class="dot"></span>
        <span class="title">Mini ERP</span>
      </div>

      <nav class="menu">
        <RouterLink
          to="/produtos"
          class="menu-item"
          :class="{ active: route.name === 'produtos' }"
        >
          <span>Produtos</span>
        </RouterLink>

        <RouterLink
          to="/compras"
          class="menu-item"
          :class="{ active: route.name === 'compras' }"
        >
          <span>Compras</span>
        </RouterLink>

        <RouterLink
          to="/vendas"
          class="menu-item"
          :class="{ active: route.name === 'vendas' }"
        >
          <span>Vendas</span>
        </RouterLink>
      </nav>
    </aside>

    <main class="main-content">
      <RouterView />
    </main>
  </div>
</template>

<script setup lang="ts">
import { RouterView, RouterLink, useRoute } from 'vue-router'

const route = useRoute()
</script>

<style scoped>
.app-container {
  display: flex;
  min-height: 100vh;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  background: #f3f4f6;
}

.sidebar {
  width: 220px;
  background: #0f172a;
  color: #e5e7eb;
  padding: 1.25rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  font-size: 1rem;
}

.sidebar-logo .dot {
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: #0d99ff;
}

.sidebar-logo .title {
  white-space: nowrap;
}

.menu {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 0.55rem 0.75rem;
  border-radius: 999px;
  font-size: 0.9rem;
  text-decoration: none;
  color: #e5e7eb;
  transition: background 0.15s ease, color 0.15s ease, transform 0.05s ease;
}

.menu-item:hover {
  background: rgba(148, 163, 184, 0.15);
}

.menu-item.active {
  background: #0d99ff;
  color: #ffffff;
  transform: translateX(2px);
}

.main-content {
  flex: 1;
  padding: 1.5rem;
  overflow: auto;
}

@media (max-width: 768px) {
  .app-container {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
  }

  .menu {
    flex-direction: row;
    justify-content: flex-end;
    gap: 0.5rem;
  }

  .menu-item {
    border-radius: 999px;
    padding-inline: 0.9rem;
  }

  .main-content {
    padding: 1rem;
  }

  .sidebar-logo {
    display: none;
  }
}
</style>