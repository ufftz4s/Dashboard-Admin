import axios from 'axios';

export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';
export const DASHBOARD_STATS_URL = import.meta.env.VITE_DASHBOARD_STATS_URL || `${API_BASE_URL}/dashboard/stats`;

const api = axios.create({
    timeout: 10000,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

const tokenFromEnv = import.meta.env.VITE_API_TOKEN;
const tokenFromStorage = typeof window !== 'undefined' ? window.localStorage.getItem('api_token') : null;
const bearerToken = tokenFromEnv || tokenFromStorage;

if (bearerToken) {
    api.defaults.headers.common.Authorization = `Bearer ${bearerToken}`;
}

export default api;
