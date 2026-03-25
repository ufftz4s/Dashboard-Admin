<script setup>
import DashboardStats from './DashboardStats.vue';
</script>

<template>
    <div class="app-shell">
        <DashboardStats />
    </div>
</template>

<style scoped>
.app-shell {
    min-height: 100vh;
    background:
        radial-gradient(circle at 12% 12%, rgba(34, 197, 94, 0.12), transparent 28%),
        radial-gradient(circle at 86% 0%, rgba(16, 185, 129, 0.09), transparent 24%),
        linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    padding: 20px;
}

@media (min-width: 768px) {
    .app-shell {
        padding: 28px;
    }
}
</style>
