<template>
  <span :class="['badge', `badge-${variant}`]">
    {{ text }}
  </span>
</template>

<script setup>
defineProps({
  text: { type: String, required: true },
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'present', 'absent', 'late', 'success', 'danger', 'warning', 'pending'].includes(v),
  },
});
</script>

<style scoped>
.badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  border-radius: 100px;
  font-size: 12px;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-default {
  background: #f3f4f6;
  color: #6b7280;
}

.badge-present,
.badge-success {
  background: #dcfce7;
  color: #16a34a;
}

.badge-absent,
.badge-danger {
  background: #fee2e2;
  color: #dc2626;
}

.badge-late,
.badge-warning {
  background: #fef3c7;
  color: #d97706;
}

.badge-pending {
  background: #fff7ed;
  color: #ea580c;
}
</style>
