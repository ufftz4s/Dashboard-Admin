import api, { DASHBOARD_STATS_URL } from './api';

export async function getDashboardStats({ signal } = {}) {
    const response = await api.get(DASHBOARD_STATS_URL, { signal });
    console.debug('Dashboard stats response:', response.data);
    return response.data;
}
