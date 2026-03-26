<template>
  <div class="rates-card">
    <h3 class="rates-title">Attendance Rates</h3>

    <div class="rate-item">
      <div class="rate-header">
        <span class="rate-label">Weekly Rate</span>
        <span class="rate-value">{{ weeklyRate }}%</span>
      </div>
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: weeklyRate + '%' }"></div>
      </div>
    </div>

    <div class="rate-item">
      <div class="rate-header">
        <span class="rate-label">Monthly Rate</span>
        <span class="rate-value">{{ monthlyRate }}%</span>
      </div>
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: monthlyRate + '%' }"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  weeklyRate: { type: Number, default: 0 },
  monthlyRate: { type: Number, default: 0 },
});
</script>

<style scoped>
.rates-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.rates-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.rate-item {
  margin-bottom: 18px;
}

.rate-item:last-child {
  margin-bottom: 0;
}

.rate-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.rate-label {
  font-size: 13px;
  color: #6b7280;
  font-weight: 500;
}

.rate-value {
  font-size: 15px;
  font-weight: 700;
  color: #22c55e;
}

.progress-bar {
  height: 8px;
  background: #f3f4f6;
  border-radius: 100px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #22c55e, #16a34a);
  border-radius: 100px;
  transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
