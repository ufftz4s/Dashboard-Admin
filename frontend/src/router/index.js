import { createRouter, createWebHistory } from 'vue-router';
import Login from '../pages/Login.vue';
import Dashboard from '../pages/Dashboard.vue';

const routes = [
  {
    path: '/',
    name: 'Login',
    component: () => import('../pages/Login.vue')
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('../pages/Dashboard.vue')
  },
  {
    path: '/employees',
    name: 'Employees',
    component: () => import('../pages/Employees.vue')
  },
  {
    path: '/attendance',
    name: 'Attendance',
    component: () => import('../pages/Attendance.vue')
  },
  {
    path: '/perizinan',
    name: 'Perizinan',
    component: () => import('../pages/Perizinan.vue')
  },
  {
    path: '/admin',
    name: 'AdminPanel',
    component: () => import('../pages/AdminPanel.vue')
  },
  {
    path: '/lokasi-presensi',
    name: 'LokasiPresensi',
    component: () => import('../pages/LokasiPresensi.vue')
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;