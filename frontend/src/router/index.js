import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        name: 'Dashboard',
        component: () => import('../pages/Dashboard.vue'),
    },
    {
        path: '/employees',
        name: 'Employees',
        component: () => import('../pages/Employees.vue'),
    },
    {
        path: '/attendance',
        name: 'Attendance',
        component: () => import('../pages/Attendance.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
