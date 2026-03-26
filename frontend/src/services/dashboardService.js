import api from './api';

export default {
    getStats() {
        return api.get('/dashboard/stats');
    },
    getWeeklyAttendance() {
        return api.get('/dashboard/weekly-attendance');
    },
    getAttendanceRates() {
        return api.get('/dashboard/attendance-rates');
    },
    getDepartmentBreakdown() {
        return api.get('/dashboard/department-breakdown');
    },
};
