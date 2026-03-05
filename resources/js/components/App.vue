<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';

const now = ref(new Date());
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timer) {
        clearInterval(timer);
    }
});

const formattedNow = computed(() =>
    new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'full',
        timeStyle: 'medium',
    }).format(now.value),
);

const stats = [
    { label: 'API Endpoint', value: '7+' },
    { label: 'Status', value: 'Vue Running' },
    { label: 'Build Tool', value: 'Vite 7' },
];
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-slate-950 text-slate-100">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-24 left-[-12%] h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl"></div>
            <div class="absolute top-1/3 right-[-10%] h-80 w-80 rounded-full bg-fuchsia-500/20 blur-3xl"></div>
            <div class="absolute bottom-[-120px] left-1/4 h-72 w-72 rounded-full bg-emerald-400/20 blur-3xl"></div>
        </div>

        <main class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-10 sm:px-10">
            <header class="mb-10 flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="mb-2 inline-flex rounded-full border border-cyan-300/30 bg-cyan-300/10 px-3 py-1 text-xs tracking-wider text-cyan-100">
                        Dashboard Admin
                    </p>
                    <h1 class="text-3xl font-semibold leading-tight sm:text-5xl">Vue sekarang jadi halaman utama</h1>
                    <p class="mt-3 max-w-2xl text-sm text-slate-300 sm:text-base">
                        Ini adalah shell frontend Vue untuk Dashboard Admin. Struktur ini sudah siap untuk ditambahkan router, state management,
                        dan koneksi API backend Laravel.
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-800 bg-slate-900/70 px-4 py-3 text-sm text-slate-200 shadow-xl shadow-slate-950/40">
                    {{ formattedNow }}
                </div>
            </header>

            <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="item in stats"
                    :key="item.label"
                    class="rounded-2xl border border-slate-800 bg-slate-900/70 p-5 shadow-lg shadow-slate-950/30"
                >
                    <p class="text-xs uppercase tracking-wider text-slate-400">{{ item.label }}</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ item.value }}</p>
                </article>
            </section>

            <section class="mt-8 rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-xl shadow-slate-950/40">
                <h2 class="text-lg font-semibold text-white">Next step yang umum</h2>
                <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-300">
                    <li>Buat halaman login dan dashboard sebagai komponen terpisah.</li>
                    <li>Tambah Vue Router untuk navigasi multi-halaman SPA.</li>
                    <li>Integrasikan API Laravel (`/api/login`, `/api/pegawai`, dll) dengan `axios`.</li>
                </ol>
            </section>
        </main>
    </div>
</template>
