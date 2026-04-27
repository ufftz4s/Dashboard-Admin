<template>
  <div class="admin-page">
    <!-- Tabs -->
    <div class="tabs">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="['tab-btn', { active: activeTab === tab.key }]"
        @click="activeTab = tab.key"
      >
        <span class="tab-icon" v-html="tab.icon"></span>
        {{ tab.label }}
        <span v-if="tab.key === 'approval' && pendingCount > 0" class="tab-badge">{{ pendingCount }}</span>
      </button>
    </div>

    <!-- Tab 1: Approval -->
    <div v-if="activeTab === 'approval'" class="tab-content">
      <div class="section-header">
        <h2 class="section-title">Pending Approvals</h2>
        <div class="filter-group">
          <select v-model="approvalFilter" class="filter-input" @change="fetchPendingApprovals()">
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="">All</option>
          </select>
        </div>
      </div>

      <div class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Type</th>
              <th>Period</th>
              <th>Days</th>
              <th>Reason</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in approvalList" :key="item.id">
              <td>
                <div class="emp-name-cell">
                  <div class="emp-avatar">{{ item.pegawai?.username?.charAt(0)?.toUpperCase() || '?' }}</div>
                  <div class="emp-info">
                    <span class="emp-name">{{ item.pegawai?.username || 'Unknown' }}</span>
                    <span class="emp-nip">{{ item.nip }}</span>
                  </div>
                </div>
              </td>
              <td class="type-cell">{{ item.tipe_perizinan?.keterangan || '-' }}</td>
              <td class="date-cell">{{ formatDate(item.tanggal_mulai) }} — {{ formatDate(item.tanggal_selesai) }}</td>
              <td class="text-center">{{ item.jumlah_hari }}</td>
              <td class="reason-cell">{{ truncate(item.alasan, 40) }}</td>
              <td><Badge :text="item.status || 'pending'" :variant="getStatusVariant(item.status)" /></td>
              <td>
                <div v-if="!item.status || item.status === 'pending'" class="actions">
                  <button class="btn-approve" @click="openApproveModal(item)" title="Approve">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    Approve
                  </button>
                  <button class="btn-reject" @click="openRejectModal(item)" title="Reject">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                    Reject
                  </button>
                </div>
                <span v-else class="text-gray text-sm">—</span>
              </td>
            </tr>
            <tr v-if="approvalList.length === 0">
              <td colspan="7" class="empty-state">No requests found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tab 2: Work Schedule -->
    <div v-if="activeTab === 'schedule'" class="tab-content">
      <div class="section-header">
        <h2 class="section-title">Work Schedules</h2>
        <div class="header-actions">
          <div class="search-box search-sm">
            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input v-model="scheduleSearch" type="text" placeholder="Search NIP..." class="search-input" @input="debouncedScheduleSearch" />
          </div>
          <button class="btn-add" @click="openScheduleModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Schedule
          </button>
        </div>
      </div>

      <div class="table-card">
        <table class="data-table">
          <thead>
            <tr>
              <th>Employee</th>
              <th>Date</th>
              <th>Clock In</th>
              <th>Clock Out</th>
              <th>Created By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in scheduleList" :key="item.id">
              <td>
                <div class="emp-name-cell">
                  <div class="emp-avatar">{{ item.pegawai?.username?.charAt(0)?.toUpperCase() || '?' }}</div>
                  <div class="emp-info">
                    <span class="emp-name">{{ item.pegawai?.username || item.nip }}</span>
                    <span class="emp-nip">{{ item.nip }}</span>
                  </div>
                </div>
              </td>
              <td>{{ formatDate(item.tanggal) }}</td>
              <td>{{ item.jam_masuk || '-' }}</td>
              <td>{{ item.jam_plng || '-' }}</td>
              <td class="text-gray">{{ item.dibuat_oleh || '-' }}</td>
              <td>
                <div class="actions">
                  <button class="btn-action btn-edit" @click="openScheduleModal(item)" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                      <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                  </button>
                  <button class="btn-action btn-delete" @click="deleteSchedule(item.id)" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="scheduleList.length === 0">
              <td colspan="6" class="empty-state">No schedules found</td>
            </tr>
          </tbody>
        </table>

        <div v-if="schedulePagination.lastPage > 1" class="pagination">
          <button class="page-btn" :disabled="schedulePagination.currentPage === 1" @click="fetchSchedules(schedulePagination.currentPage - 1)">Previous</button>
          <span class="page-info">Page {{ schedulePagination.currentPage }} of {{ schedulePagination.lastPage }}</span>
          <button class="page-btn" :disabled="schedulePagination.currentPage === schedulePagination.lastPage" @click="fetchSchedules(schedulePagination.currentPage + 1)">Next</button>
        </div>
      </div>
    </div>

    <!-- Tab 3: Add Employee -->
    <div v-if="activeTab === 'employee'" class="tab-content">
      <div class="section-header">
        <h2 class="section-title">Add New Employee</h2>
      </div>

      <div class="form-card">
        <form @submit.prevent="addEmployee" class="employee-form">
          <div class="form-row">
            <div class="form-group">
              <label>Username</label>
              <input v-model="empForm.username" type="text" required class="form-input" placeholder="Enter username" />
              <span v-if="empErrors.username" class="form-error">{{ empErrors.username[0] }}</span>
            </div>
            <div class="form-group">
              <label>NIP</label>
              <input v-model="empForm.nip" type="text" required class="form-input" placeholder="Enter NIP" />
              <span v-if="empErrors.nip" class="form-error">{{ empErrors.nip[0] }}</span>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Email</label>
              <input v-model="empForm.email" type="email" required class="form-input" placeholder="Enter email" />
              <span v-if="empErrors.email" class="form-error">{{ empErrors.email[0] }}</span>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input v-model="empForm.password" type="password" required class="form-input" placeholder="Min 6 characters" />
              <span v-if="empErrors.password" class="form-error">{{ empErrors.password[0] }}</span>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Position</label>
              <input v-model="empForm.jabatan" type="text" class="form-input" placeholder="e.g. Staff, Manager" />
            </div>
            <div class="form-group">
              <label>Role</label>
              <select v-model="empForm.role" class="form-input">
                <option value="">Select role</option>
                <option value="admin">Admin</option>
                <option value="pegawai">Pegawai</option>
              </select>
            </div>
          </div>
          <div class="form-actions">
            <button type="button" class="btn-cancel" @click="resetEmpForm">Reset</button>
            <button type="submit" class="btn-save" :disabled="empSaving">
              {{ empSaving ? 'Adding...' : 'Add Employee' }}
            </button>
          </div>
        </form>

        <!-- Success message -->
        <div v-if="empSuccess" class="success-alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          Employee added successfully!
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <Modal v-model="showApproveModal" title="Approve Request">
      <form @submit.prevent="confirmApprove" class="approve-form">
        <p class="confirm-text">Approve permission request from <strong>{{ approveTarget?.pegawai?.username }}</strong>?</p>
        <div class="form-group">
          <label>Approved By</label>
          <input v-model="approveForm.disetujui_oleh" type="text" required class="form-input" placeholder="Your name" />
        </div>
        <div class="form-group">
          <label>Notes (Optional)</label>
          <textarea v-model="approveForm.catatan" rows="2" class="form-input form-textarea" placeholder="Additional notes..."></textarea>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="showApproveModal = false">Cancel</button>
          <button type="submit" class="btn-approve-confirm" :disabled="approving">
            {{ approving ? 'Approving...' : 'Confirm Approve' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Reject Modal -->
    <Modal v-model="showRejectModal" title="Reject Request">
      <form @submit.prevent="confirmReject" class="reject-form">
        <p class="confirm-text">Reject permission request from <strong>{{ rejectTarget?.pegawai?.username }}</strong>?</p>
        <div class="form-group">
          <label>Reason for Rejection</label>
          <textarea v-model="rejectForm.catatan" rows="3" required class="form-input form-textarea" placeholder="Explain why this request is rejected..."></textarea>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="showRejectModal = false">Cancel</button>
          <button type="submit" class="btn-reject-confirm" :disabled="rejecting">
            {{ rejecting ? 'Rejecting...' : 'Confirm Reject' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Schedule Modal -->
    <Modal v-model="showScheduleModal" :title="editingSchedule ? 'Edit Schedule' : 'Add Schedule'">
      <form @submit.prevent="saveSchedule" class="schedule-form">
        <div class="form-group">
          <label>Employee (NIP)</label>
          <select v-model="scheduleForm.nip" required class="form-input">
            <option value="">Select employee</option>
            <option v-for="peg in pegawaiList" :key="peg.nip" :value="peg.nip">
              {{ peg.username }} — {{ peg.nip }}
            </option>
          </select>
          <span v-if="scheduleErrors.nip" class="form-error">{{ scheduleErrors.nip[0] }}</span>
        </div>
        <div class="form-group">
          <label>Date</label>
          <input v-model="scheduleForm.tanggal" type="date" required class="form-input" />
          <span v-if="scheduleErrors.tanggal" class="form-error">{{ scheduleErrors.tanggal[0] }}</span>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Clock In</label>
            <input v-model="scheduleForm.jam_masuk" type="time" required class="form-input" />
          </div>
          <div class="form-group">
            <label>Clock Out</label>
            <input v-model="scheduleForm.jam_plng" type="time" required class="form-input" />
          </div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="showScheduleModal = false">Cancel</button>
          <button type="submit" class="btn-save" :disabled="scheduleSaving">
            {{ scheduleSaving ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Modal from '../components/common/Modal.vue';
import Badge from '../components/common/Badge.vue';
import perizinanService from '../services/perizinanService';
import jadwalKerjaService from '../services/jadwalKerjaService';
import pegawaiService from '../services/pegawaiService';

// -- Tab state --
const activeTab = ref('approval');
const tabs = [
  {
    key: 'approval',
    label: 'Approval',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
  },
  {
    key: 'schedule',
    label: 'Work Schedule',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
  },
  {
    key: 'employee',
    label: 'Add Employee',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>',
  },
];

// -- Shared --
const pegawaiList = ref([]);

// -- Approval tab --
const approvalList = ref([]);
const approvalFilter = ref('pending');
const pendingCount = ref(0);
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const approveTarget = ref(null);
const rejectTarget = ref(null);
const approving = ref(false);
const rejecting = ref(false);
const approveForm = ref({ disetujui_oleh: '', catatan: '' });
const rejectForm = ref({ catatan: '' });

// -- Schedule tab --
const scheduleList = ref([]);
const scheduleSearch = ref('');
const showScheduleModal = ref(false);
const editingSchedule = ref(null);
const scheduleSaving = ref(false);
const scheduleErrors = ref({});
const schedulePagination = ref({ currentPage: 1, lastPage: 1 });
const scheduleForm = ref({ nip: '', tanggal: '', jam_masuk: '', jam_plng: '' });

// -- Employee tab --
const empForm = ref({ username: '', nip: '', email: '', password: '', jabatan: '', role: '' });
const empErrors = ref({});
const empSaving = ref(false);
const empSuccess = ref(false);

// ======= APPROVAL =======
async function fetchPendingApprovals() {
  try {
    const params = { per_page: 50 };
    if (approvalFilter.value) params.status = approvalFilter.value;
    const res = await perizinanService.getAll(params);
    approvalList.value = res.data.data;
  } catch (e) {
    console.error('Failed to fetch approvals:', e);
  }
}

async function fetchPendingCount() {
  try {
    const res = await perizinanService.getAll({ status: 'pending', per_page: 1 });
    pendingCount.value = res.data.meta?.total || 0;
  } catch (e) {
    pendingCount.value = 0;
  }
}

function openApproveModal(item) {
  approveTarget.value = item;
  approveForm.value = { disetujui_oleh: '', catatan: '' };
  showApproveModal.value = true;
}

function openRejectModal(item) {
  rejectTarget.value = item;
  rejectForm.value = { catatan: '' };
  showRejectModal.value = true;
}

async function confirmApprove() {
  approving.value = true;
  try {
    await perizinanService.approve(approveTarget.value.id, approveForm.value);
    showApproveModal.value = false;
    fetchPendingApprovals();
    fetchPendingCount();
  } catch (e) {
    console.error('Approve failed:', e);
  } finally {
    approving.value = false;
  }
}

async function confirmReject() {
  rejecting.value = true;
  try {
    await perizinanService.reject(rejectTarget.value.id, rejectForm.value);
    showRejectModal.value = false;
    fetchPendingApprovals();
    fetchPendingCount();
  } catch (e) {
    console.error('Reject failed:', e);
  } finally {
    rejecting.value = false;
  }
}

// ======= SCHEDULE =======
let scheduleTimeout = null;
function debouncedScheduleSearch() {
  clearTimeout(scheduleTimeout);
  scheduleTimeout = setTimeout(() => fetchSchedules(), 300);
}

async function fetchSchedules(page = 1) {
  try {
    const params = { page, per_page: 15 };
    if (scheduleSearch.value) params.search = scheduleSearch.value;
    const res = await jadwalKerjaService.getAll(params);
    scheduleList.value = res.data.data;
    schedulePagination.value = {
      currentPage: res.data.meta?.current_page || 1,
      lastPage: res.data.meta?.last_page || 1,
    };
  } catch (e) {
    console.error('Failed to fetch schedules:', e);
  }
}

function openScheduleModal(item = null) {
  editingSchedule.value = item;
  scheduleErrors.value = {};
  if (item) {
    scheduleForm.value = {
      nip: item.nip,
      tanggal: item.tanggal?.split('T')[0] || '',
      jam_masuk: item.jam_masuk || '',
      jam_plng: item.jam_plng || '',
    };
  } else {
    scheduleForm.value = { nip: '', tanggal: '', jam_masuk: '08:00', jam_plng: '16:00' };
  }
  showScheduleModal.value = true;
}

async function saveSchedule() {
  scheduleSaving.value = true;
  scheduleErrors.value = {};
  try {
    if (editingSchedule.value) {
      await jadwalKerjaService.update(editingSchedule.value.id, scheduleForm.value);
    } else {
      await jadwalKerjaService.create(scheduleForm.value);
    }
    showScheduleModal.value = false;
    fetchSchedules(schedulePagination.value.currentPage);
  } catch (e) {
    if (e.response?.status === 422) {
      scheduleErrors.value = e.response.data.errors || {};
    }
  } finally {
    scheduleSaving.value = false;
  }
}

async function deleteSchedule(id) {
  if (!confirm('Are you sure you want to delete this schedule?')) return;
  try {
    await jadwalKerjaService.delete(id);
    fetchSchedules(schedulePagination.value.currentPage);
  } catch (e) {
    console.error('Failed to delete schedule:', e);
  }
}

// ======= EMPLOYEE =======
function resetEmpForm() {
  empForm.value = { username: '', nip: '', email: '', password: '', jabatan: '', role: '' };
  empErrors.value = {};
  empSuccess.value = false;
}

async function addEmployee() {
  empSaving.value = true;
  empErrors.value = {};
  empSuccess.value = false;
  try {
    await pegawaiService.create(empForm.value);
    empSuccess.value = true;
    empForm.value = { username: '', nip: '', email: '', password: '', jabatan: '', role: '' };
    // Refresh pegawai list
    fetchPegawai();
    setTimeout(() => { empSuccess.value = false; }, 3000);
  } catch (e) {
    if (e.response?.status === 422) {
      empErrors.value = e.response.data.errors || {};
    }
  } finally {
    empSaving.value = false;
  }
}

// ======= SHARED =======
async function fetchPegawai() {
  try {
    const res = await pegawaiService.getAll();
    pegawaiList.value = res.data.data;
  } catch (e) {
    console.error('Failed to fetch pegawai:', e);
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function truncate(str, len) {
  if (!str) return '-';
  return str.length > len ? str.substring(0, len) + '...' : str;
}

function getStatusVariant(status) {
  const map = { pending: 'pending', approved: 'success', rejected: 'danger' };
  return map[status] || 'pending';
}

onMounted(() => {
  fetchPendingApprovals();
  fetchPendingCount();
  fetchSchedules();
  fetchPegawai();
});
</script>

<style scoped>
.admin-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 4px;
  background: white;
  padding: 6px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border: none;
  background: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.tab-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.tab-btn.active {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(34, 197, 94, 0.3);
}

.tab-icon {
  display: flex;
  align-items: center;
}

.tab-badge {
  background: #ef4444;
  color: white;
  font-size: 11px;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 100px;
  min-width: 20px;
  text-align: center;
}

.tab-btn.active .tab-badge {
  background: white;
  color: #ef4444;
}

/* Section header */
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.section-title {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

/* Table & shared */
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
  padding: 14px 16px;
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.data-table td {
  padding: 14px 16px;
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

.emp-info {
  display: flex;
  flex-direction: column;
}

.emp-name {
  font-weight: 500;
  color: #1f2937;
}

.emp-nip {
  font-size: 12px;
  color: #9ca3af;
}

.type-cell {
  text-transform: capitalize;
  font-size: 13px;
}

.date-cell {
  white-space: nowrap;
  font-size: 13px;
}

.reason-cell {
  max-width: 180px;
  color: #6b7280;
  font-size: 13px;
}

.text-center {
  text-align: center;
}

.text-gray {
  color: #9ca3af;
}

.text-sm {
  font-size: 13px;
}

.actions {
  display: flex;
  gap: 6px;
}

.btn-approve {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  background: #dcfce7;
  color: #16a34a;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-approve:hover {
  background: #bbf7d0;
}

.btn-reject {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 6px 12px;
  background: #fee2e2;
  color: #dc2626;
  border: none;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-reject:hover {
  background: #fecaca;
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

/* Search */
.search-box {
  position: relative;
}

.search-sm {
  max-width: 220px;
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
  padding: 8px 14px 8px 36px;
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

.filter-input {
  padding: 8px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  outline: none;
  background: white;
  min-width: 140px;
}

.filter-input:focus {
  border-color: #22c55e;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  border: none;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-add:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

/* Forms */
.form-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
  padding: 28px;
}

.employee-form, .approve-form, .reject-form, .schedule-form {
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

.btn-approve-confirm {
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

.btn-approve-confirm:hover {
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.3);
}

.btn-approve-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-reject-confirm {
  padding: 10px 24px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reject-confirm:hover {
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-reject-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.confirm-text {
  font-size: 14px;
  color: #374151;
  margin: 0 0 8px 0;
}

.success-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  background: #dcfce7;
  color: #16a34a;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 500;
  margin-top: 16px;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
