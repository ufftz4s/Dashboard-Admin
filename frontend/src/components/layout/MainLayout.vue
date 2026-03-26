<template>
  <div class="app-layout">
    <Sidebar :collapsed="sidebarCollapsed" />
    <div :class="['main-wrapper', { 'sidebar-collapsed': sidebarCollapsed }]">
      <Header @toggle-sidebar="sidebarCollapsed = !sidebarCollapsed" />
      <main class="main-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import Sidebar from './Sidebar.vue';
import Header from './Header.vue';

const sidebarCollapsed = ref(false);
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
}

.main-wrapper {
  flex: 1;
  margin-left: 220px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  transition: margin-left 0.3s ease;
}

.main-wrapper.sidebar-collapsed {
  margin-left: 68px;
}

.main-content {
  flex: 1;
  padding: 24px;
  background: #f5f5f5;
}
</style>
