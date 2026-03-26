<template>
  <div class="chart-card">
    <h3 class="chart-title">Last 7 Days Attendance</h3>
    <div class="chart-container">
      <Line v-if="loaded" :data="chartData" :options="chartOptions" />
      <div v-else class="chart-loading">Loading chart...</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const props = defineProps({
  weeklyData: { type: Array, default: () => [] },
});

const loaded = ref(false);
const chartData = ref({ labels: [], datasets: [] });

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    intersect: false,
    mode: 'index',
  },
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        usePointStyle: true,
        padding: 20,
        font: { size: 12, family: "'Inter', sans-serif" },
      },
    },
    tooltip: {
      backgroundColor: 'rgba(0,0,0,0.8)',
      padding: 12,
      titleFont: { size: 13 },
      bodyFont: { size: 12 },
      cornerRadius: 8,
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: {
        font: { size: 12, family: "'Inter', sans-serif" },
        color: '#9ca3af',
      },
    },
    y: {
      beginAtZero: true,
      grid: { color: '#f3f4f6' },
      ticks: {
        font: { size: 12, family: "'Inter', sans-serif" },
        color: '#9ca3af',
        stepSize: 5,
      },
    },
  },
};

function updateChart(data) {
  if (!data || data.length === 0) return;

  chartData.value = {
    labels: data.map((d) => d.day),
    datasets: [
      {
        label: 'Present',
        data: data.map((d) => d.present),
        borderColor: '#22c55e',
        backgroundColor: 'rgba(34, 197, 94, 0.08)',
        fill: true,
        tension: 0.4,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBackgroundColor: '#22c55e',
        borderWidth: 2.5,
      },
      {
        label: 'Absent',
        data: data.map((d) => d.absent),
        borderColor: '#ef4444',
        backgroundColor: 'rgba(239, 68, 68, 0.08)',
        fill: true,
        tension: 0.4,
        pointRadius: 5,
        pointHoverRadius: 7,
        pointBackgroundColor: '#ef4444',
        borderWidth: 2.5,
      },
    ],
  };
  loaded.value = true;
}

watch(() => props.weeklyData, updateChart, { immediate: true });

onMounted(() => {
  if (props.weeklyData.length > 0) {
    updateChart(props.weeklyData);
  }
});
</script>

<style scoped>
.chart-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.chart-title {
  font-size: 16px;
  font-weight: 600;
  color: #1f2937;
  margin: 0 0 20px 0;
}

.chart-container {
  height: 280px;
}

.chart-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #9ca3af;
  font-size: 14px;
}
</style>
