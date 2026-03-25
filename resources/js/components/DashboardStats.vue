<script setup>
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import StatCard from './StatCard.vue';

const REFRESH_INTERVAL_MS = 10_000;
const numberFormatter = new Intl.NumberFormat('id-ID');

const stats = ref({
    totalPegawai: 0,
    hadirHariIni: 0,
    tepatWaktu: 0,
    izinHariIni: 0,
    terlambat: 0,
});

const isInitialLoading = ref(true);
const isRefreshing = ref(false);
const hasLoadedOnce = ref(false);
const errorMessage = ref('');
const lastUpdated = ref(null);

let refreshTimer = null;
let isFetching = false;

const cardConfig = [
    { key: 'totalPegawai', title: 'Total Pegawai', icon: '👥', accent: '#2563eb' },
    { key: 'hadirHariIni', title: 'Hadir Hari Ini', icon: '✅', accent: '#16a34a' },
    { key: 'tepatWaktu', title: 'Tepat Waktu', icon: '⏰', accent: '#10b981' },
    { key: 'izinHariIni', title: 'Izin Hari Ini', icon: '📝', accent: '#f59e0b' },
    { key: 'terlambat', title: 'Terlambat', icon: '⚠️', accent: '#ef4444' },
];

const cards = computed(() =>
    cardConfig.map((item) => ({
        ...item,
        value: formatNumber(stats.value[item.key]),
    })),
);

const canShowCards = computed(() => hasLoadedOnce.value && !isInitialLoading.value);

function formatNumber(value) {
    const numericValue = Number(value ?? 0);
    return numberFormatter.format(Number.isFinite(numericValue) ? numericValue : 0);
}

function normalizeStats(payload) {
    return {
        totalPegawai: Number(payload?.totalPegawai ?? 0),
        hadirHariIni: Number(payload?.hadirHariIni ?? 0),
        tepatWaktu: Number(payload?.tepatWaktu ?? 0),
        izinHariIni: Number(payload?.izinHariIni ?? 0),
        terlambat: Number(payload?.terlambat ?? 0),
    };
}

function getErrorMessage(error) {
    if (axios.isAxiosError(error)) {
        if (typeof error.response?.data?.message === 'string' && error.response.data.message.trim()) {
            return error.response.data.message;
        }

        if (error.code === 'ECONNABORTED') {
            return 'Permintaan timeout. Silakan coba lagi.';
        }
    }

    return 'Gagal memuat data dashboard. Silakan coba beberapa saat lagi.';
}

async function fetchStats({ initial = false } = {}) {
    if (isFetching) {
        return;
    }

    isFetching = true;
    errorMessage.value = '';

    if (initial) {
        isInitialLoading.value = true;
    } else {
        isRefreshing.value = true;
    }

    try {
        const response = await axios.get('/dashboard/stats', { timeout: 10_000 });
        stats.value = normalizeStats(response.data);
        hasLoadedOnce.value = true;
        lastUpdated.value = new Date();
    } catch (error) {
        errorMessage.value = getErrorMessage(error);
    } finally {
        isInitialLoading.value = false;
        isRefreshing.value = false;
        isFetching = false;
    }
}

function startAutoRefresh() {
    refreshTimer = window.setInterval(() => {
        fetchStats();
    }, REFRESH_INTERVAL_MS);
}

function stopAutoRefresh() {
    if (refreshTimer) {
        clearInterval(refreshTimer);
        refreshTimer = null;
    }
}

onMounted(async () => {
    await fetchStats({ initial: true });
    startAutoRefresh();
});

onUnmounted(() => {
    stopAutoRefresh();
});
</script>

<template>
    <section class="dashboard-stats" aria-live="polite">
        <header class="stats-header">
            <div>
                <h1>Ringkasan Kehadiran</h1>
                <p>Data statistik kehadiran pegawai hari ini.</p>
            </div>

            <div class="meta">
                <span v-if="isRefreshing" class="meta-badge">Memperbarui...</span>
                <span v-else-if="lastUpdated" class="meta-text">
                    Update:
                    {{ lastUpdated.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }}
                </span>
                <span class="meta-text">Auto-refresh 10 detik</span>
            </div>
        </header>

        <div v-if="errorMessage" class="error-banner" role="alert">
            <span>{{ errorMessage }}</span>
            <button type="button" @click="fetchStats({ initial: !hasLoadedOnce })">Coba lagi</button>
        </div>

        <div v-if="isInitialLoading" class="stats-grid" aria-label="Memuat data dashboard">
            <article v-for="n in 5" :key="n" class="skeleton-card">
                <div class="skeleton-line w-30"></div>
                <div class="skeleton-line w-60"></div>
            </article>
        </div>

        <transition-group
            v-else-if="canShowCards"
            name="card-fade"
            tag="div"
            class="stats-grid"
            aria-label="Statistik dashboard"
        >
            <StatCard
                v-for="card in cards"
                :key="card.key"
                :title="card.title"
                :value="card.value"
                :icon="card.icon"
                :accent="card.accent"
            />
        </transition-group>
    </section>
</template>

<style scoped>
.dashboard-stats {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    padding: 20px 0;
}

.stats-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 18px;
}

.stats-header h1 {
    margin: 0;
    color: #0f172a;
    font-size: clamp(1.25rem, 2.6vw, 1.75rem);
    font-weight: 700;
}

.stats-header p {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 0.95rem;
}

.meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.meta-text {
    color: #64748b;
    font-size: 0.83rem;
    font-weight: 500;
}

.meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 0.76rem;
    font-weight: 700;
    color: #166534;
    background-color: #dcfce7;
}

.error-banner {
    margin-bottom: 16px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-radius: 12px;
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #b91c1c;
    padding: 12px 14px;
    font-size: 0.9rem;
}

.error-banner button {
    border: 1px solid #ef4444;
    background: #ef4444;
    color: #fff;
    font-weight: 600;
    border-radius: 10px;
    padding: 7px 10px;
    cursor: pointer;
    transition: filter 0.2s ease;
}

.error-banner button:hover {
    filter: brightness(0.92);
}

.stats-grid {
    display: grid;
    gap: 14px;
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 640px) {
    .stats-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }
}

.skeleton-card {
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 18px;
    background: #fff;
    box-shadow:
        0 12px 28px -20px rgba(15, 23, 42, 0.4),
        0 8px 18px -14px rgba(15, 23, 42, 0.28);
}

.skeleton-line {
    border-radius: 999px;
    background: linear-gradient(90deg, #e2e8f0 0%, #f1f5f9 50%, #e2e8f0 100%);
    background-size: 200% 100%;
    animation: shimmer 1.1s infinite linear;
}

.skeleton-line + .skeleton-line {
    margin-top: 12px;
}

.w-30 {
    width: 30%;
    height: 10px;
}

.w-60 {
    width: 60%;
    height: 26px;
}

.card-fade-enter-active,
.card-fade-leave-active {
    transition: all 0.24s ease;
}

.card-fade-enter-from,
.card-fade-leave-to {
    opacity: 0;
    transform: translateY(6px);
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}
</style>
