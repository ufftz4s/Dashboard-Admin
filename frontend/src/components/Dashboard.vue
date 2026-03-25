<script setup>
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { getDashboardStats } from '../services/dashboard';
import StatCard from './StatCard.vue';

const DASHBOARD_REFRESH_MS = 10_000;
const numberFormatter = new Intl.NumberFormat('en-US');

const EMPTY_STATS = Object.freeze({
    totalPegawai: 0,
    hadirHariIni: 0,
    tepatWaktu: 0,
    izinHariIni: 0,
    terlambat: 0,
});

const statItems = Object.freeze([
    {
        key: 'totalPegawai',
        title: 'Total Pegawai',
        accent: '#2563eb',
        icon: '👥',
    },
    {
        key: 'hadirHariIni',
        title: 'Hadir Hari Ini',
        accent: '#059669',
        icon: '✅',
    },
    {
        key: 'tepatWaktu',
        title: 'Tepat Waktu',
        accent: '#7c3aed',
        icon: '⏰',
    },
    {
        key: 'izinHariIni',
        title: 'Izin Hari Ini',
        accent: '#d97706',
        icon: '📝',
    },
    {
        key: 'terlambat',
        title: 'Terlambat',
        accent: '#dc2626',
        icon: '⚠️',
    },
]);

const stats = ref({ ...EMPTY_STATS });
const isLoading = ref(true);
const isRefreshing = ref(false);
const errorMessage = ref('');
const hasLoadedOnce = ref(false);
const lastUpdatedAt = ref(null);

let refreshTimer = null;
let currentAbortController = null;
let requestPending = false;

const cards = computed(() =>
    statItems.map((item) => ({
        ...item,
        value: formatNumber(stats.value[item.key]),
    })),
);

const showSkeleton = computed(() => isLoading.value && !hasLoadedOnce.value);
const showCards = computed(() => hasLoadedOnce.value);

function toNumber(value) {
    const numericValue = Number(value);
    return Number.isFinite(numericValue) ? numericValue : 0;
}

function normalizeStats(payload = {}) {
    return {
        totalPegawai: toNumber(payload.totalPegawai),
        hadirHariIni: toNumber(payload.hadirHariIni),
        tepatWaktu: toNumber(payload.tepatWaktu),
        izinHariIni: toNumber(payload.izinHariIni),
        terlambat: toNumber(payload.terlambat),
    };
}

function formatNumber(value) {
    return numberFormatter.format(toNumber(value));
}

function formatUpdatedTime(date) {
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
}

function resolveErrorMessage(error) {
    if (axios.isCancel?.(error) || error?.code === 'ERR_CANCELED') {
        return '';
    }

    if (error?.message === 'Network Error') {
        return 'Unable to reach the Laravel API. Make sure http://127.0.0.1:8000 is running and CORS allows http://localhost:5173.';
    }

    if (error?.code === 'ECONNABORTED') {
        return 'Dashboard request timed out. Please try again.';
    }

    if (typeof error?.response?.data?.message === 'string' && error.response.data.message.trim()) {
        return error.response.data.message;
    }

    return 'Failed to load dashboard stats.';
}

async function loadDashboardStats({ withLoader = false } = {}) {
    if (requestPending) {
        return;
    }

    requestPending = true;
    errorMessage.value = '';

    if (withLoader && !hasLoadedOnce.value) {
        isLoading.value = true;
    } else if (hasLoadedOnce.value) {
        isRefreshing.value = true;
    }

    const abortController = new AbortController();
    currentAbortController = abortController;

    try {
        const payload = await getDashboardStats({ signal: abortController.signal });
        stats.value = normalizeStats(payload);
        hasLoadedOnce.value = true;
        lastUpdatedAt.value = new Date();
    } catch (error) {
        const message = resolveErrorMessage(error);

        if (message) {
            errorMessage.value = message;
        }
    } finally {
        if (currentAbortController === abortController) {
            currentAbortController = null;
        }

        isLoading.value = false;
        isRefreshing.value = false;
        requestPending = false;
    }
}

function startAutoRefresh() {
    refreshTimer = window.setInterval(() => {
        loadDashboardStats();
    }, DASHBOARD_REFRESH_MS);
}

function stopAutoRefresh() {
    if (refreshTimer) {
        window.clearInterval(refreshTimer);
        refreshTimer = null;
    }

    if (currentAbortController) {
        currentAbortController.abort();
        currentAbortController = null;
    }
}

onMounted(async () => {
    await loadDashboardStats({ withLoader: true });
    startAutoRefresh();
});

onUnmounted(() => {
    stopAutoRefresh();
});
</script>

<template>
    <section class="dashboard" aria-live="polite">
        <div class="dashboard__panel">
            <header class="dashboard__header">
                <div class="dashboard__intro">
                    <span class="dashboard__eyebrow">Laravel API + Vue SPA</span>
                    <h1>Dashboard Statistik Kehadiran</h1>
                    <p>
                        This dashboard fetches live stats from
                        <code>http://127.0.0.1:8000/api/dashboard/stats</code>
                        using Axios with a full URL, no Blade, and no authentication.
                    </p>
                </div>

                <div class="dashboard__actions">
                    <button
                        type="button"
                        class="dashboard__refresh"
                        :disabled="isLoading || isRefreshing"
                        @click="loadDashboardStats()"
                    >
                        {{ isRefreshing ? 'Refreshing...' : 'Refresh Data' }}
                    </button>

                    <p class="dashboard__meta">Auto refresh every 10 seconds</p>
                    <p class="dashboard__meta">
                        {{ lastUpdatedAt ? `Last updated ${formatUpdatedTime(lastUpdatedAt)}` : 'Waiting for data...' }}
                    </p>
                </div>
            </header>

            <div v-if="errorMessage" class="dashboard__error" role="alert">
                <span>{{ errorMessage }}</span>
                <button type="button" @click="loadDashboardStats({ withLoader: !hasLoadedOnce })">Retry</button>
            </div>

            <div v-if="showSkeleton" class="dashboard__grid" aria-label="Memuat statistik dashboard">
                <article class="dashboard__loading-card">
                    <p class="dashboard__loading-title">Loading...</p>
                    <p class="dashboard__loading-copy">Fetching dashboard statistics from the Laravel API.</p>
                </article>
                <article v-for="placeholder in 5" :key="placeholder" class="dashboard__skeleton">
                    <div class="dashboard__skeleton-icon"></div>
                    <div class="dashboard__skeleton-line dashboard__skeleton-line--short"></div>
                    <div class="dashboard__skeleton-line dashboard__skeleton-line--long"></div>
                </article>
            </div>

            <transition-group v-else-if="showCards" name="dashboard-card" tag="div" class="dashboard__grid">
                <StatCard
                    v-for="card in cards"
                    :key="card.key"
                    :title="card.title"
                    :value="card.value"
                    :icon="card.icon"
                    :accent="card.accent"
                />
            </transition-group>
        </div>
    </section>
</template>

<style scoped>
.dashboard {
    width: 100%;
}

.dashboard__panel {
    position: relative;
    overflow: hidden;
    padding: 1.5rem;
    border-radius: 32px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    background:
        radial-gradient(circle at top right, rgba(37, 99, 235, 0.1), transparent 25%),
        radial-gradient(circle at left bottom, rgba(5, 150, 105, 0.12), transparent 28%),
        rgba(255, 255, 255, 0.84);
    backdrop-filter: blur(18px);
    box-shadow:
        0 40px 90px -56px rgba(15, 23, 42, 0.62),
        inset 0 1px 0 rgba(255, 255, 255, 0.72);
}

.dashboard__header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.dashboard__intro {
    max-width: 46rem;
}

.dashboard__eyebrow {
    display: inline-flex;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    background: rgba(15, 23, 42, 0.06);
    color: #0f766e;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.dashboard__intro h1 {
    margin: 1rem 0 0.6rem;
    color: #0f172a;
    font-size: clamp(1.9rem, 4vw, 3rem);
    line-height: 1.05;
}

.dashboard__intro p {
    margin: 0;
    color: #475569;
    line-height: 1.7;
}

.dashboard__intro code {
    font-family: inherit;
    font-weight: 700;
    color: #0f172a;
}

.dashboard__actions {
    display: grid;
    gap: 0.55rem;
    justify-items: start;
}

.dashboard__refresh,
.dashboard__error button {
    border: none;
    border-radius: 999px;
    padding: 0.82rem 1.2rem;
    background: linear-gradient(135deg, #0f766e 0%, #2563eb 100%);
    color: #ffffff;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 16px 30px -20px rgba(37, 99, 235, 0.8);
    transition:
        transform 0.2s ease,
        opacity 0.2s ease,
        box-shadow 0.2s ease;
}

.dashboard__refresh:hover,
.dashboard__error button:hover {
    transform: translateY(-1px);
    box-shadow: 0 22px 36px -22px rgba(37, 99, 235, 0.82);
}

.dashboard__refresh:disabled {
    cursor: progress;
    opacity: 0.78;
    transform: none;
}

.dashboard__meta {
    margin: 0;
    color: #64748b;
    font-size: 0.88rem;
}

.dashboard__error {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.9rem;
    margin-bottom: 1.25rem;
    padding: 1rem 1.1rem;
    border-radius: 20px;
    border: 1px solid rgba(220, 38, 38, 0.18);
    background: rgba(254, 242, 242, 0.92);
    color: #b91c1c;
}

.dashboard__grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.dashboard__loading-card {
    display: grid;
    gap: 0.35rem;
    grid-column: 1 / -1;
    padding: 1.1rem 1.2rem;
    border-radius: 20px;
    border: 1px solid rgba(37, 99, 235, 0.14);
    background: rgba(239, 246, 255, 0.9);
}

.dashboard__loading-title,
.dashboard__loading-copy {
    margin: 0;
}

.dashboard__loading-title {
    color: #1d4ed8;
    font-weight: 700;
}

.dashboard__loading-copy {
    color: #475569;
}

.dashboard__skeleton {
    padding: 1.25rem;
    border-radius: 24px;
    border: 1px solid rgba(148, 163, 184, 0.14);
    background: rgba(255, 255, 255, 0.94);
    box-shadow:
        0 24px 50px -34px rgba(15, 23, 42, 0.45),
        0 10px 24px -20px rgba(15, 23, 42, 0.22);
}

.dashboard__skeleton-icon,
.dashboard__skeleton-line {
    background: linear-gradient(90deg, rgba(226, 232, 240, 0.9), rgba(241, 245, 249, 1), rgba(226, 232, 240, 0.9));
    background-size: 200% 100%;
    animation: shimmer 1.2s linear infinite;
}

.dashboard__skeleton-icon {
    width: 3rem;
    height: 3rem;
    border-radius: 1rem;
}

.dashboard__skeleton-line {
    margin-top: 1rem;
    border-radius: 999px;
}

.dashboard__skeleton-line--short {
    width: 42%;
    height: 0.7rem;
}

.dashboard__skeleton-line--long {
    width: 64%;
    height: 2rem;
}

.dashboard-card-enter-active,
.dashboard-card-leave-active {
    transition: all 0.28s ease;
}

.dashboard-card-enter-from,
.dashboard-card-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

@media (min-width: 640px) {
    .dashboard__panel {
        padding: 2rem;
    }

    .dashboard__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 960px) {
    .dashboard__grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }
}
</style>
