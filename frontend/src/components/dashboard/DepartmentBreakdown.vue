<template>
  <div class="breakdown-card">
    <h3 class="breakdown-title">Department Breakdown</h3>

    <div class="dept-list">
      <div v-for="dept in departments" :key="dept.department" class="dept-item">
        <span class="dept-name">{{ dept.department }}</span>
        <span class="dept-badge">{{ dept.count }}</span>
      </div>
      <div v-if="departments.length === 0" class="dept-empty">
        No data available
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  departments: { type: Array, default: () => [] },
});
</script>

<style scoped>
.breakdown-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.breakdown-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.dept-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.dept-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 14px;
  background: #f9fafb;
  border-radius: 10px;
  transition: background 0.15s ease;
}

.dept-item:hover {
  background: #f0fdf4;
}

.dept-name {
  font-size: 14px;
  color: #374151;
  font-weight: 500;
}

.dept-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  height: 28px;
  padding: 0 8px;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  font-size: 12px;
  font-weight: 600;
  border-radius: 100px;
}

.dept-empty {
  text-align: center;
  color: #9ca3af;
  font-size: 14px;
  padding: 20px;
}
</style>
