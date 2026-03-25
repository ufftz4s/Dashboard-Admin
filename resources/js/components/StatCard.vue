<script setup>
const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: String,
        default: '📊',
    },
    accent: {
        type: String,
        default: '#22c55e',
    },
});
</script>

<template>
    <article class="stat-card" :style="{ '--accent': props.accent }" role="status" :aria-label="title">
        <div class="card-top">
            <span class="icon" aria-hidden="true">{{ icon }}</span>
            <span class="indicator"></span>
        </div>

        <p class="title">{{ title }}</p>
        <p class="value">{{ value }}</p>
    </article>
</template>

<style scoped>
.stat-card {
    border: 1px solid color-mix(in srgb, var(--accent) 26%, #d1d5db);
    border-radius: 16px;
    padding: 18px;
    background: linear-gradient(165deg, #ffffff 0%, color-mix(in srgb, var(--accent) 8%, #ffffff) 100%);
    box-shadow:
        0 12px 28px -20px rgba(15, 23, 42, 0.45),
        0 8px 18px -14px rgba(15, 23, 42, 0.3);
    transition:
        transform 0.22s ease,
        box-shadow 0.22s ease,
        border-color 0.22s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    border-color: color-mix(in srgb, var(--accent) 40%, #d1d5db);
    box-shadow:
        0 20px 35px -24px rgba(15, 23, 42, 0.5),
        0 12px 20px -14px rgba(15, 23, 42, 0.34);
}

.card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: color-mix(in srgb, var(--accent) 12%, #ffffff);
    font-size: 20px;
}

.indicator {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    background-color: var(--accent);
    box-shadow: 0 0 0 5px color-mix(in srgb, var(--accent) 16%, #ffffff);
}

.title {
    margin: 14px 0 8px;
    color: #64748b;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 600;
}

.value {
    margin: 0;
    color: #0f172a;
    font-size: clamp(1.45rem, 2vw, 1.9rem);
    font-weight: 700;
    line-height: 1.15;
}
</style>
