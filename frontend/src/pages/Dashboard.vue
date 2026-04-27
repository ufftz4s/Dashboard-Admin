<template>
  <div class="dashboard">
    <!-- Stat Cards -->
    <div class="stats-row">
      <StatCard
        label="Total Pegawai"
        :value="stats.total_employees"
        :icon="icons.employees"
        borderColor="#22c55e"
        iconBg="#f0fdf4"
        valueColor="#22c55e"
      />
      <StatCard
        label="Hadir Hari Ini"
        :value="stats.present_today"
        :icon="icons.present"
        borderColor="#22c55e"
        iconBg="#f0fdf4"
        valueColor="#22c55e"
      />
      <StatCard
        label="Izin Hari Ini"
        :value="stats.absent_today"
        :icon="icons.absent"
        borderColor="#ef4444"
        iconBg="#fee2e2"
        valueColor="#ef4444"
      />
      <StatCard
        label="Terlambat Hari Ini"
        :value="stats.late_today"
        :icon="icons.late"
        borderColor="#f59e0b"
        iconBg="#fef3c7"
        valueColor="#ef4444"
      />
    </div>

    <!-- Charts & Panels Row -->
    <div class="content-row">
      <div class="chart-section">
        <AttendanceChart :weeklyData="weeklyData" />
      </div>
      <div class="panel-section">
        <AttendanceRates
          :weeklyRate="rates.weekly_rate"
          :monthlyRate="rates.monthly_rate"
        />
        <DepartmentBreakdown :departments="departments" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import StatCard from '../components/dashboard/StatCard.vue';
import AttendanceChart from '../components/dashboard/AttendanceChart.vue';
import AttendanceRates from '../components/dashboard/AttendanceRates.vue';
import DepartmentBreakdown from '../components/dashboard/DepartmentBreakdown.vue';
import dashboardService from '../services/dashboardService';

const stats = ref({ total_employees: 0, present_today: 0, absent_today: 0, late_today: 0 });
const weeklyData = ref([]);
const rates = ref({ weekly_rate: 0, monthly_rate: 0 });
const departments = ref([]);

const icons = {
  employees: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>',
  present: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
  absent: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
  late: '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
};

onMounted(async () => {
  try {
    const [statsRes, weeklyRes, ratesRes, deptRes] = await Promise.all([
      dashboardService.getStats(),
      dashboardService.getWeeklyAttendance(),
      dashboardService.getAttendanceRates(),
      dashboardService.getDepartmentBreakdown(),
    ]);

    stats.value = statsRes.data.data;
    weeklyData.value = weeklyRes.data.data;
    rates.value = ratesRes.data.data;
    departments.value = deptRes.data.data;
  } catch (error) {
    console.error('Failed to load dashboard data:', error);
  }
});
</script>

<style scoped>
.dashboard {
  background: #f3f4f6;
  min-height: calc(100vh - 96px);
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 20px;
}

.content-row {
  gap: 20px;
  display: grid;
  grid-template-columns: 3fr 2fr;
}

.content-row {
  display: grid;
  grid-template-columns: 3fr 2fr;
  gap: 20px;
}

.panel-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

@media (max-width: 1024px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
  .content-row {
    grid-template-columns: 1fr;
  }
}
</style>
