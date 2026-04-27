<template>
  <div class="perizinan-page">
    <!-- Top Bar -->
    <div class="top-bar">
      <div class="search-box">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by NIP or name..."
          class="search-input"
          @input="debouncedSearch"
        />
      </div>
      <div class="filter-group">
        <select v-model="statusFilter" class="filter-input" @change="fetchPerizinan()">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
      <button class="btn-add" @click="openAddModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        New Request
      </button>
    </div>

    <!-- Table -->
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Employee</th>
            <th>Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Days</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in perizinanList" :key="item.id">
            <td>
              <div class="emp-name-cell">
                <div class="emp-avatar">{{ item.pegawai?.username?.charAt(0)?.toUpperCase() || '?' }}</div>
                <div class="emp-info">
                  <span class="emp-name">{{ item.pegawai?.username || 'Unknown' }}</span>
                  <span class="emp-nip">{{ item.nip }}</span>
                </div>
              </div>
            </td>
            <td><span class="type-label">{{ item.tipe_perizinan?.keterangan || '-' }}</span></td>
            <td>{{ formatDate(item.tanggal_mulai) }}</td>
            <td>{{ formatDate(item.tanggal_selesai) }}</td>
            <td class="text-center">{{ item.jumlah_hari || '-' }}</td>
            <td><Badge :text="item.status || 'pending'" :variant="getStatusVariant(item.status)" /></td>
            <td class="text-gray">{{ formatDate(item.tanggal_pengajuan) }}</td>
            <td>
              <div class="actions">
                <button class="btn-action btn-view" @click="viewDetail(item)" title="View Detail">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
                <button v-if="!item.status || item.status === 'pending'" class="btn-action btn-edit" @click="openEditModal(item)" title="Edit">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="perizinanList.length === 0">
            <td colspan="8" class="empty-state">No permission requests found</td>
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

    <!-- Add/Edit Modal -->
    <Modal v-model="showFormModal" :title="isEditing ? 'Edit Request' : 'New Permission Request'">
      <form @submit.prevent="saveRequest" class="perizinan-form">
        <div class="form-group">
          <label>Employee (NIP)</label>
          <select v-model="form.nip" required class="form-input">
            <option value="">Select employee</option>
            <option v-for="peg in pegawaiList" :key="peg.nip" :value="peg.nip">
              {{ peg.username }} — {{ peg.nip }}
            </option>
          </select>
          <span v-if="errors.nip" class="form-error">{{ errors.nip[0] }}</span>
        </div>
        <div class="form-group">
          <label>Permission Type</label>
          <select v-model="form.tipe" required class="form-input">
            <option value="">Select type</option>
            <option v-for="t in tipeList" :key="t.id" :value="t.id">
              {{ t.keterangan }}
            </option>
          </select>
          <span v-if="errors.tipe" class="form-error">{{ errors.tipe[0] }}</span>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Start Date</label>
            <input v-model="form.tanggal_mulai" type="date" required class="form-input" />
            <span v-if="errors.tanggal_mulai" class="form-error">{{ errors.tanggal_mulai[0] }}</span>
          </div>
          <div class="form-group">
            <label>End Date</label>
            <input v-model="form.tanggal_selesai" type="date" required class="form-input" />
            <span v-if="errors.tanggal_selesai" class="form-error">{{ errors.tanggal_selesai[0] }}</span>
          </div>
        </div>
        <div class="form-group">
          <label>Reason</label>
          <textarea v-model="form.alasan" rows="3" required class="form-input form-textarea" placeholder="Explain the reason..."></textarea>
          <span v-if="errors.alasan" class="form-error">{{ errors.alasan[0] }}</span>
        </div>
        <div class="form-group">
          <label>Document Number (Optional)</label>
          <input v-model="form.no_surat" type="text" class="form-input" placeholder="e.g. SPT/001/2025" />
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="showFormModal = false">Cancel</button>
          <button type="submit" class="btn-save" :disabled="saving">
            {{ saving ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </Modal>

    <!-- Detail Modal -->
    <Modal v-model="showDetailModal" title="Request Detail" maxWidth="560px">
      <div v-if="selectedItem" class="detail-content">
        <div class="detail-row">
          <span class="detail-label">Employee</span>
          <span class="detail-value">{{ selectedItem.pegawai?.username || '-' }} ({{ selectedItem.nip }})</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Type</span>
          <span class="detail-value">{{ selectedItem.tipe_perizinan?.keterangan || '-' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Period</span>
          <span class="detail-value">{{ formatDate(selectedItem.tanggal_mulai) }} — {{ formatDate(selectedItem.tanggal_selesai) }} ({{ selectedItem.jumlah_hari }} days)</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Reason</span>
          <span class="detail-value">{{ selectedItem.alasan || '-' }}</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Status</span>
          <Badge :text="selectedItem.status || 'pending'" :variant="getStatusVariant(selectedItem.status)" />
        </div>
        <div v-if="selectedItem.disetujui_oleh" class="detail-row">
          <span class="detail-label">Approved By</span>
          <span class="detail-value">{{ selectedItem.disetujui_oleh }}</span>
        </div>
        <div v-if="selectedItem.catatan" class="detail-row">
          <span class="detail-label">Notes</span>
          <span class="detail-value">{{ selectedItem.catatan }}</span>
        </div>
        <div v-if="selectedItem.no_surat" class="detail-row">
          <span class="detail-label">Document No.</span>
          <span class="detail-value">{{ selectedItem.no_surat }}</span>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Modal from '../components/common/Modal.vue';
import Badge from '../components/common/Badge.vue';
import perizinanService from '../services/perizinanService';
import tipeService from '../services/tipeService';
import pegawaiService from '../services/pegawaiService';

const perizinanList = ref([]);
const pegawaiList = ref([]);
const tipeList = ref([]);
const searchQuery = ref('');
const statusFilter = ref('');
const showFormModal = ref(false);
const showDetailModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const saving = ref(false);
const errors = ref({});
const selectedItem = ref(null);
const pagination = ref({ currentPage: 1, lastPage: 1 });

const form = ref({
  nip: '',
  tipe: '',
  alasan: '',
  tanggal_mulai: '',
  tanggal_selesai: '',
  no_surat: '',
});

let searchTimeout = null;

function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchPerizinan();
  }, 300);
}

async function fetchPerizinan(page = 1) {
  try {
    const params = { page, per_page: 10 };
    if (searchQuery.value) params.search = searchQuery.value;
    if (statusFilter.value) params.status = statusFilter.value;

    const res = await perizinanService.getAll(params);
    perizinanList.value = res.data.data;
    pagination.value = {
      currentPage: res.data.meta?.current_page || 1,
      lastPage: res.data.meta?.last_page || 1,
    };
  } catch (error) {
    console.error('Failed to fetch perizinan:', error);
  }
}

async function fetchDropdownData() {
  try {
    const [pegRes, tipeRes] = await Promise.all([
      pegawaiService.getAll(),
      tipeService.getAll(),
    ]);
    pegawaiList.value = pegRes.data.data;
    tipeList.value = tipeRes.data.data;
  } catch (error) {
    console.error('Failed to fetch dropdown data:', error);
  }
}

function goToPage(page) {
  fetchPerizinan(page);
}

function openAddModal() {
  isEditing.value = false;
  editingId.value = null;
  form.value = { nip: '', tipe: '', alasan: '', tanggal_mulai: '', tanggal_selesai: '', no_surat: '' };
  errors.value = {};
  showFormModal.value = true;
}

function openEditModal(item) {
  isEditing.value = true;
  editingId.value = item.id;
  form.value = {
    nip: item.nip,
    tipe: item.tipe,
    alasan: item.alasan || '',
    tanggal_mulai: item.tanggal_mulai?.split('T')[0] || '',
    tanggal_selesai: item.tanggal_selesai?.split('T')[0] || '',
    no_surat: item.no_surat || '',
  };
  errors.value = {};
  showFormModal.value = true;
}

function viewDetail(item) {
  selectedItem.value = item;
  showDetailModal.value = true;
}

async function saveRequest() {
  saving.value = true;
  errors.value = {};
  try {
    if (isEditing.value) {
      await perizinanService.update(editingId.value, form.value);
    } else {
      await perizinanService.create(form.value);
    }
    showFormModal.value = false;
    fetchPerizinan(pagination.value.currentPage);
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
  } finally {
    saving.value = false;
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function getStatusVariant(status) {
  const map = { pending: 'pending', approved: 'success', rejected: 'danger' };
  return map[status] || 'pending';
}

onMounted(() => {
  fetchPerizinan();
  fetchDropdownData();
});
</script>

<style scoped>
.perizinan-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.top-bar {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 320px;
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

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.filter-input {
  padding: 10px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  font-size: 14px;
  outline: none;
  background: white;
  min-width: 150px;
  transition: border-color 0.2s ease;
}

.filter-input:focus {
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.btn-add {
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

.btn-add:hover {
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

.type-label {
  font-size: 13px;
  text-transform: capitalize;
}

.text-center {
  text-align: center;
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

.btn-view {
  background: #f3f0ff;
  color: #7c3aed;
}

.btn-view:hover {
  background: #ede9fe;
}

.btn-edit {
  background: #eff6ff;
  color: #3b82f6;
}

.btn-edit:hover {
  background: #dbeafe;
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
.perizinan-form {
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

/* Detail Modal */
.detail-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.detail-row {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.detail-label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-value {
  font-size: 14px;
  color: #1f2937;
}
</style>
