<template>
  <header class="header">
    <div class="header-left">
      <button class="hamburger-btn" @click="$emit('toggle-sidebar')">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/>
          <line x1="3" y1="12" x2="21" y2="12"/>
          <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <h1 class="page-title">{{ pageTitle }}</h1>
    </div>

    <div class="header-right">
      <div class="user-menu" @click="showDropdown = !showDropdown">
        <div class="user-avatar">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <span class="user-name">Admin User</span>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </div>

      <div v-if="showDropdown" class="dropdown-menu">
        <a href="#" class="dropdown-item">Profile</a>
        <a href="#" class="dropdown-item">Settings</a>
        <hr class="dropdown-divider" />
        <a href="#" class="dropdown-item text-red" @click.prevent="handleLogout">Logout</a>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../services/api.js';

defineEmits(['toggle-sidebar']);

const route = useRoute();
const router = useRouter();
const showDropdown = ref(false);

const pageTitle = computed(() => {
  const titles = {
    '/': 'Login',
    '/dashboard': 'Dashboard',
    '/employees': 'Employees',
    '/attendance': 'Attendance',
    '/lokasi-presensi': 'Lokasi Presensi',
  };
  return titles[route.path] || 'Dashboard';
});

const handleLogout = async () => {
  try {
    const token = localStorage.getItem('token');
    if (token) {
      await api.post('/logout', {}, {
        headers: { Authorization: `Bearer ${token}` }
      });
    }
  } catch (error) {
    console.error('Logout error:', error);
  } finally {
    // Selalu hapus token dan redirect ke login
    localStorage.removeItem('token');
    router.push('/');
  }
};
</script>

<style scoped>
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 24px;
  height: 64px;
  background: #ffffff;
  border-bottom: 1px solid #e5e7eb;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.hamburger-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: none;
  background: none;
  color: #6b7280;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.hamburger-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.page-title {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.header-right {
  position: relative;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  padding: 6px 12px;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.user-menu:hover {
  background: #f3f4f6;
}

.user-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.user-name {
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  background: white;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
  border: 1px solid #e5e7eb;
  min-width: 160px;
  padding: 6px;
  z-index: 200;
}

.dropdown-item {
  display: block;
  padding: 8px 14px;
  font-size: 13px;
  color: #374151;
  text-decoration: none;
  border-radius: 6px;
  transition: background 0.15s ease;
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.dropdown-item.text-red {
  color: #ef4444;
}

.dropdown-divider {
  margin: 4px 0;
  border: none;
  border-top: 1px solid #f3f4f6;
}
</style>
