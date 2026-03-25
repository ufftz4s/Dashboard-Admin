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
        required: true,
    },
    accent: {
        type: String,
        default: '#2563eb',
    },
});
</script>

<template>
    <article class="stat-card" :style="{ '--card-accent': props.accent }" :aria-label="`${props.title}: ${props.value}`">
        <div class="stat-card__glow" aria-hidden="true"></div>

        <header class="stat-card__header">
            <div class="stat-card__icon" aria-hidden="true">
                <span>{{ props.icon }}</span>
            </div>

            <span class="stat-card__pulse"></span>
        </header>

        <p class="stat-card__title">{{ props.title }}</p>
        <p class="stat-card__value">{{ props.value }}</p>
    </article>
</template>

<style scoped>
.stat-card {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    border: 1px solid color-mix(in srgb, var(--card-accent) 16%, #d8dee9);
    border-top: 4px solid var(--card-accent);
    padding: 1.25rem;
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.94) 100%);
    box-shadow:
        0 24px 60px -34px rgba(15, 23, 42, 0.5),
        0 18px 32px -30px rgba(15, 23, 42, 0.42);
    transition:
        transform 0.24s ease,
        box-shadow 0.24s ease,
        border-color 0.24s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    border-color: color-mix(in srgb, var(--card-accent) 38%, #d8dee9);
    box-shadow:
        0 30px 70px -34px rgba(15, 23, 42, 0.58),
        0 20px 36px -28px rgba(15, 23, 42, 0.45);
}

.stat-card__glow {
    position: absolute;
    right: -44px;
    top: -44px;
    width: 140px;
    height: 140px;
    border-radius: 999px;
    background: radial-gradient(circle, color-mix(in srgb, var(--card-accent) 16%, transparent), transparent 70%);
    pointer-events: none;
}

.stat-card__header {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.stat-card__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3rem;
    height: 3rem;
    border-radius: 1rem;
    color: var(--card-accent);
    background: color-mix(in srgb, var(--card-accent) 12%, #ffffff);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85);
}

.stat-card__icon span {
    font-size: 1.35rem;
    line-height: 1;
}

.stat-card__pulse {
    width: 0.75rem;
    height: 0.75rem;
    border-radius: 999px;
    background: var(--card-accent);
    box-shadow: 0 0 0 0 color-mix(in srgb, var(--card-accent) 32%, transparent);
    animation: pulse 1.8s infinite;
}

.stat-card__title {
    position: relative;
    margin: 1.1rem 0 0.45rem;
    color: #64748b;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.stat-card__value {
    position: relative;
    margin: 0;
    color: #0f172a;
    font-size: clamp(1.8rem, 3vw, 2.35rem);
    font-weight: 800;
    line-height: 1.05;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 color-mix(in srgb, var(--card-accent) 28%, transparent);
    }

    70% {
        box-shadow: 0 0 0 12px transparent;
    }

    100% {
        box-shadow: 0 0 0 0 transparent;
    }
}
</style>
