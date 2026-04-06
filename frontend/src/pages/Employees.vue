<template>
  <div class="employees-page">
    <!-- Top Bar -->
    <div class="top-bar">
      <div class="left-controls">
        <div class="search-box">
          <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search employees..."
            class="search-input"
            @input="debouncedSearch"
          />
        </div>
        <div class="filter-wrapper" style="position: relative;">
          <button class="btn-filter" @click="isFilterOpen = !isFilterOpen">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            {{ filterDisplay }}
          </button>
          <div v-show="isFilterOpen" class="dropdown-menu">
            <div class="dropdown-item" @click="setFilter('')">Semua Tipe</div>
            <div class="dropdown-item" @click="setFilter('ASN')">ASN</div>
            <div class="dropdown-item" @click="setFilter('honorer')">Honorer</div>
            <div class="dropdown-item" @click="setFilter('kontrak')">Kontrak</div>
          </div>
        </div>
      </div>
      <button class="btn-add" @click="openAddModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Employee
      </button>
    </div>

    <!-- Table -->
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Department</th>
            <th>Position</th>
            <th>Email</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="emp in employees" :key="emp.id">
            <td>
              <div class="emp-name-cell">
                <div class="emp-avatar">{{ emp.name.charAt(0) }}</div>
                <span>{{ emp.name }}</span>
              </div>
            </td>
            <td><Badge v-if="emp.type" :text="emp.type" variant="success" /><span v-else class="text-gray">-</span></td>
            <td><Badge :text="emp.department" variant="success" /></td>
            <td>{{ emp.position }}</td>
            <td class="text-gray">{{ emp.email }}</td>
            <td>
              <div class="actions">
                <button class="btn-action btn-edit" @click="openEditModal(emp)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button class="btn-action btn-delete" @click="deleteEmployee(emp.id)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="employees.length === 0">
            <td colspan="5" class="empty-state">No employees found</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.lastPage > 1" class="pagination">
        <button
          class="page-btn"
          :disabled="pagination.currentPage === 1"
          @click="goToPage(pagination.currentPage - 1)"
        >
          Previous
        </button>
        <span class="page-info">Page {{ pagination.currentPage }} of {{ pagination.lastPage }}</span>
        <button
          class="page-btn"
          :disabled="pagination.currentPage === pagination.lastPage"
          @click="goToPage(pagination.currentPage + 1)"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Modal -->
    <Modal v-model="showModal" :title="isEditing ? 'Edit Employee' : 'Add Employee'">
      <form @submit.prevent="saveEmployee" class="emp-form">
        <div class="form-group">
          <label>Nama Lengkap</label>
          <input v-model="form.name" type="text" required class="form-input" />
          <span v-if="errors.name" class="form-error">{{ errors.name[0] }}</span>
        </div>
        <div class="form-group">
          <label>Tipe Pegawai</label>
          <select v-model="form.type" required class="form-input">
            <option value="">Pilih Tipe Pegawai</option>
            <option value="ASN">ASN</option>
            <option value="honorer">Honorer</option>
            <option value="kontrak">Kontrak</option>
          </select>
          <span v-if="errors.type" class="form-error">{{ errors.type[0] }}</span>
        </div>
        <div class="form-group">
          <label>Tempat, Tanggal Lahir</label>
          <input v-model="form.birth_place_date" type="text" required class="form-input" placeholder="Contoh: Jakarta, 01 Januari 1990" />
          <span v-if="errors.birth_place_date" class="form-error">{{ errors.birth_place_date[0] }}</span>
        </div>
        <div class="form-group">
          <label>NIP</label>
          <input v-model="form.nip" type="text" required class="form-input" />
          <span v-if="errors.nip" class="form-error">{{ errors.nip[0] }}</span>
        </div>
        <div class="form-group">
          <label>Lokasi Kerja</label>
          <input v-model="form.work_location" type="text" required class="form-input" />
          <span v-if="errors.work_location" class="form-error">{{ errors.work_location[0] }}</span>
        </div>
        <div class="form-group">
          <label>Unit Kerja</label>
          <input v-model="form.work_unit" type="text" required class="form-input" />
          <span v-if="errors.work_unit" class="form-error">{{ errors.work_unit[0] }}</span>
        </div>
        <div class="form-group">
          <label>Jabatan</label>
          <input v-model="form.position" type="text" required class="form-input" />
          <span v-if="errors.position" class="form-error">{{ errors.position[0] }}</span>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" required class="form-input" />
          <span v-if="errors.email" class="form-error">{{ errors.email[0] }}</span>
        </div>

        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="showModal = false">Cancel</button>
          <button type="submit" class="btn-save" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import Modal from '../components/common/Modal.vue';
import Badge from '../components/common/Badge.vue';
import employeeService from '../services/employeeService';

const employees = ref([]);
const searchQuery = ref('');
const typeFilter = ref('');
const isFilterOpen = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const saving = ref(false);
const errors = ref({});
const pagination = ref({ currentPage: 1, lastPage: 1 });

const filterDisplay = computed(() => {
  if (typeFilter.value === 'ASN') return 'ASN';
  if (typeFilter.value === 'honorer') return 'Honorer';
  if (typeFilter.value === 'kontrak') return 'Kontrak';
  return 'Filter';
});

function setFilter(val) {
  typeFilter.value = val;
  isFilterOpen.value = false;
  fetchEmployees(1);
}

const form = ref({
  name: '',
  birth_place_date: '',
  nip: '',
  work_location: '',
  work_unit: '',
  position: '',
  email: '',
  type: '',
});

let searchTimeout = null;

function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchEmployees();
  }, 300);
}

async function fetchEmployees(page = 1) {
  try {
    const params = { page, per_page: 10 };
    if (searchQuery.value) params.search = searchQuery.value;
    if (typeFilter.value) params.type = typeFilter.value;

    const res = await employeeService.getAll(params);
    employees.value = res.data.data;
    pagination.value = {
      currentPage: res.data.meta?.current_page || 1,
      lastPage: res.data.meta?.last_page || 1,
    };
  } catch (error) {
    console.error('Failed to fetch employees:', error);
  }
}

function goToPage(page) {
  fetchEmployees(page);
}

function openAddModal() {
  isEditing.value = false;
  editingId.value = null;
  form.value = {
    name: '',
    birth_place_date: '',
    nip: '',
    work_location: '',
    work_unit: '',
    position: '',
    email: '',
    type: ''
  };
  errors.value = {};
  showModal.value = true;
}

function openEditModal(emp) {
  isEditing.value = true;
  editingId.value = emp.id;
  form.value = {
    name: emp.name,
    birth_place_date: emp.birth_place_date || '',
    nip: emp.nip || '',
    work_location: emp.work_location || '',
    work_unit: emp.work_unit || emp.department || '',
    position: emp.position || '',
    email: emp.email || '',
    type: emp.type || ''
  };
  errors.value = {};
  showModal.value = true;
}

async function saveEmployee() {
  saving.value = true;
  errors.value = {};

  try {
    if (isEditing.value) {
      await employeeService.update(editingId.value, form.value);
    } else {
      await employeeService.create(form.value);
    }
    showModal.value = false;
    fetchEmployees(pagination.value.currentPage);
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
  } finally {
    saving.value = false;
  }
}

async function deleteEmployee(id) {
  if (!confirm('Are you sure you want to delete this employee?')) return;

  try {
    await employeeService.delete(id);
    fetchEmployees(pagination.value.currentPage);
  } catch (error) {
    console.error('Failed to delete employee:', error);
  }
}

onMounted(() => {
  fetchEmployees();
});
</script>

<style scoped>
.employees-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.left-controls {
  display: flex;
  gap: 12px;
  flex: 1;
}

.search-box {
  position: relative;
  width: 100%;
  max-width: 360px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.search-input {
  width: 100%;
  padding: 10px 14px 10px 40px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s ease;
  background: white;
}

.search-input:focus {
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.btn-filter, .btn-add {
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
}

.btn-filter:hover, .btn-add:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  margin-top: 8px;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  width: 140px;
  z-index: 10;
  overflow: hidden;
}

.dropdown-item {
  padding: 10px 16px;
  font-size: 14px;
  color: #374151;
  cursor: pointer;
  transition: background 0.2s ease;
}

.dropdown-item:hover {
  background: #f3f4f6;
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

.actions {
  display: flex;
  gap: 6px;
}

.btn-action {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-edit {
  background: #eff6ff;
  color: #3b82f6;
}

.btn-edit:hover {
  background: #dbeafe;
}

.btn-delete {
  background: #fee2e2;
  color: #ef4444;
}

.btn-delete:hover {
  background: #fecaca;
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

/* Form Styles */
.emp-form {
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
