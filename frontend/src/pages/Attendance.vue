<template>
  <div class="attendance-page">
    <!-- Filters -->
    <div class="filters-bar">
      <div class="filter-group">
        <label>Date</label>
        <input v-model="filters.date" type="date" class="filter-input" @change="fetchAttendances" />
      </div>
      <div class="filter-group">
        <label>Employee</label>
        <select v-model="filters.employee_id" class="filter-input" @change="fetchAttendances">
          <option value="">All Employees</option>
          <option v-for="emp in employeesList" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Status</label>
        <select v-model="filters.status" class="filter-input" @change="fetchAttendances">
          <option value="">All Status</option>
          <option value="present">Present</option>
          <option value="absent">Absent</option>
          <option value="late">Late</option>
        </select>
      </div>
      <button class="btn-mark" @click="openMarkModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        Mark Attendance
      </button>
    </div>

    <!-- Table -->
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Employee Name</th>
            <th>Date</th>
            <th>Check In</th>
            <th>Check Out</th>
            <th>Status</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="att in attendances" :key="att.id">
            <td>
              <div class="emp-name-cell">
                <div class="emp-avatar">{{ att.employee?.name?.charAt(0) || '?' }}</div>
                <span>{{ att.employee?.name || 'Unknown' }}</span>
              </div>
            </td>
            <td>{{ att.date }}</td>
            <td>{{ att.check_in || '-' }}</td>
            <td>{{ att.check_out || '-' }}</td>
            <td><Badge :text="att.status" :variant="att.status" /></td>
            <td class="text-gray notes-cell">{{ att.notes || '-' }}</td>
          </tr>
          <tr v-if="attendances.length === 0">
            <td colspan="6" class="empty-state">No attendance records found</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.lastPage > 1" class="pagination">
        <button class="page-btn" :disabled="pagination.currentPage === 1" @click="goToPage(pagination.currentPage - 1)">Previous</button>
        <span class="page-info">Page {{ pagination.currentPage }} of {{ pagination.lastPage }}</span>
        <button class="page-btn" :disabled="pagination.currentPage === pagination.lastPage" @click="goToPage(pagination.currentPage + 1)">Next</button>
      </div>
    </div>

    <!-- Mark Attendance Modal -->
    <Modal v-model="showModal" title="Mark Attendance">
      <form @submit.prevent="saveAttendance" class="att-form">
        <div class="form-group">
          <label>Employee</label>
          <select v-model="form.employee_id" required class="form-input">
            <option value="">Select employee</option>
            <option v-for="emp in employeesList" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
          </select>
          <span v-if="errors.employee_id" class="form-error">{{ errors.employee_id[0] }}</span>
        </div>
        <div class="form-group">
          <label>Date</label>
          <input v-model="form.date" type="date" required class="form-input" />
          <span v-if="errors.date" class="form-error">{{ errors.date[0] }}</span>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Check In</label>
            <input v-model="form.check_in" type="time" class="form-input" />
          </div>
          <div class="form-group">
            <label>Check Out</label>
            <input v-model="form.check_out" type="time" class="form-input" />
          </div>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select v-model="form.status" required class="form-input">
            <option value="">Select status</option>
            <option value="present">Present</option>
            <option value="absent">Absent</option>
            <option value="late">Late</option>
          </select>
          <span v-if="errors.status" class="form-error">{{ errors.status[0] }}</span>
        </div>
        <div class="form-group">
          <label>Notes</label>
          <textarea v-model="form.notes" rows="3" class="form-input form-textarea" placeholder="Optional notes..."></textarea>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="showModal = false">Cancel</button>
          <button type="submit" class="btn-save" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Modal from '../components/common/Modal.vue';
import Badge from '../components/common/Badge.vue';
import attendanceService from '../services/attendanceService';
import employeeService from '../services/employeeService';

const attendances = ref([]);
const employeesList = ref([]);
const showModal = ref(false);
const saving = ref(false);
const errors = ref({});
const pagination = ref({ currentPage: 1, lastPage: 1 });

const filters = ref({
  date: '',
  employee_id: '',
  status: '',
});

const form = ref({
  employee_id: '',
  date: new Date().toISOString().split('T')[0],
  check_in: '',
  check_out: '',
  status: '',
  notes: '',
});

async function fetchAttendances(page = 1) {
  try {
    const params = { page: typeof page === 'number' ? page : 1, per_page: 15 };
    if (filters.value.date) params.date = filters.value.date;
    if (filters.value.employee_id) params.employee_id = filters.value.employee_id;
    if (filters.value.status) params.status = filters.value.status;

    const res = await attendanceService.getAll(params);
    attendances.value = res.data.data;
    pagination.value = {
      currentPage: res.data.meta?.current_page || 1,
      lastPage: res.data.meta?.last_page || 1,
    };
  } catch (error) {
    console.error('Failed to fetch attendances:', error);
  }
}

async function fetchEmployees() {
  try {
    const res = await employeeService.getAll({ per_page: 100 });
    employeesList.value = res.data.data;
  } catch (error) {
    console.error('Failed to fetch employees:', error);
  }
}

function goToPage(page) {
  fetchAttendances(page);
}

function openMarkModal() {
  form.value = {
    employee_id: '',
    date: new Date().toISOString().split('T')[0],
    check_in: '',
    check_out: '',
    status: '',
    notes: '',
  };
  errors.value = {};
  showModal.value = true;
}

async function saveAttendance() {
  saving.value = true;
  errors.value = {};

  try {
    await attendanceService.create(form.value);
    showModal.value = false;
    fetchAttendances(pagination.value.currentPage);
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  fetchAttendances();
  fetchEmployees();
});
</script>

<style scoped>
.attendance-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.filters-bar {
  display: flex;
  align-items: flex-end;
  gap: 16px;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-group label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.filter-input {
  padding: 8px 12px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  background: white;
  min-width: 160px;
  transition: border-color 0.2s ease;
}

.filter-input:focus {
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.btn-mark {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  margin-left: auto;
}

.btn-mark:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.table-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th {
  text-align: left;
  padding: 14px 20px;
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 14px 20px;
  font-size: 14px;
  color: #374151;
  border-bottom: 1px solid #f3f4f6;
}

.data-table tr:hover {
  background: #fafafa;
}

.emp-name-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.emp-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 600;
  flex-shrink: 0;
}

.text-gray {
  color: #9ca3af;
}

.notes-cell {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.empty-state {
  text-align: center;
  color: #9ca3af;
  padding: 40px 20px !important;
  font-style: italic;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  padding: 16px 20px;
  border-top: 1px solid #f3f4f6;
}

.page-btn {
  padding: 6px 16px;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 8px;
  font-size: 13px;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
}

.page-btn:hover:not(:disabled) {
  background: #f3f4f6;
}

.page-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.page-info {
  font-size: 13px;
  color: #6b7280;
}

/* Form */
.att-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 13px;
  font-weight: 500;
  color: #374151;
}

.form-input {
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s ease;
}

.form-input:focus {
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.form-textarea {
  resize: vertical;
  font-family: inherit;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-error {
  font-size: 12px;
  color: #ef4444;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 8px;
}

.btn-cancel {
  padding: 10px 20px;
  background: #f3f4f6;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  color: #6b7280;
  cursor: pointer;
  transition: background 0.15s ease;
}

.btn-cancel:hover {
  background: #e5e7eb;
}

.btn-save {
  padding: 10px 24px;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-save:hover {
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
