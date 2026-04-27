<template>
  <div class="lokasi-page">
    <!-- Top Bar -->
    <div class="top-bar">
      <div class="search-box">
        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari instansi atau unit..."
          class="search-input"
          @input="debouncedSearch"
        />
      </div>
      <button class="btn-add" @click="openAddModal">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Lokasi
      </button>
    </div>

    <!-- Table -->
    <div class="table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Kode Lokasi</th>
            <th>Instansi</th>
            <th>Unit Instansi</th>
            <th>Koordinat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="loc in locations" :key="loc.kode_lokasi">
            <td>
              <div class="kode-cell">
                <div class="kode-badge">{{ loc.kode_lokasi }}</div>
              </div>
            </td>
            <td>
              <span class="instansi-text">{{ loc.instansi }}</span>
            </td>
            <td><Badge :text="loc.unit_instansi" variant="success" /></td>
            <td class="text-gray">
              <div class="koordinat-cell">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
                <span>{{ loc.latitude }}, {{ loc.longitude }}</span>
              </div>
            </td>
            <td>
              <div class="actions">
                <button class="btn-action btn-edit" @click="openEditModal(loc)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button class="btn-action btn-delete" @click="deleteLocation(loc.kode_lokasi)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="locations.length === 0">
            <td colspan="5" class="empty-state">Belum ada data lokasi presensi</td>
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
        <span class="page-info">Halaman {{ pagination.currentPage }} dari {{ pagination.lastPage }}</span>
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
    <Modal v-model="showModal" :title="isEditing ? 'Edit Lokasi Presensi' : 'Tambah Lokasi Presensi'">
      <form @submit.prevent="saveLocation" class="loc-form">
        <div class="form-group">
          <label>Kode Lokasi</label>
          <input
            v-model.number="form.kode_lokasi"
            type="number"
            required
            class="form-input"
            :disabled="isEditing"
            placeholder="Masukkan kode lokasi"
          />
          <span v-if="errors.kode_lokasi" class="form-error">{{ errors.kode_lokasi[0] }}</span>
        </div>
        <div class="form-group">
          <label>Instansi</label>
          <input v-model="form.instansi" type="text" required class="form-input" placeholder="Nama instansi" />
          <span v-if="errors.instansi" class="form-error">{{ errors.instansi[0] }}</span>
        </div>
        <div class="form-group">
          <label>Unit Instansi</label>
          <input v-model="form.unit_instansi" type="text" required class="form-input" placeholder="Nama unit instansi" />
          <span v-if="errors.unit_instansi" class="form-error">{{ errors.unit_instansi[0] }}</span>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Latitude</label>
            <input v-model="form.latitude" type="text" required class="form-input" placeholder="-7.005145" />
            <span v-if="errors.latitude" class="form-error">{{ errors.latitude[0] }}</span>
          </div>
          <div class="form-group">
            <label>Longitude</label>
            <input v-model="form.longitude" type="text" required class="form-input" placeholder="110.414456" />
            <span v-if="errors.longitude" class="form-error">{{ errors.longitude[0] }}</span>
          </div>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="showModal = false">Batal</button>
          <button type="submit" class="btn-save" :disabled="saving">
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
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
import lokasiService from '../services/lokasiService';

const locations = ref([]);
const searchQuery = ref('');
const showModal = ref(false);
const isEditing = ref(false);
const editingKode = ref(null);
const saving = ref(false);
const errors = ref({});
const pagination = ref({ currentPage: 1, lastPage: 1 });

const form = ref({
  kode_lokasi: '',
  instansi: '',
  unit_instansi: '',
  longitude: '',
  latitude: '',
});

let searchTimeout = null;

function debouncedSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchLocations();
  }, 300);
}

async function fetchLocations(page = 1) {
  try {
    const params = { page, per_page: 10 };
    if (searchQuery.value) params.search = searchQuery.value;

    const res = await lokasiService.getAll(params);
    locations.value = res.data.data;
    pagination.value = {
      currentPage: res.data.meta?.current_page || 1,
      lastPage: res.data.meta?.last_page || 1,
    };
  } catch (error) {
    console.error('Failed to fetch locations:', error);
  }
}

function goToPage(page) {
  fetchLocations(page);
}

function openAddModal() {
  isEditing.value = false;
  editingKode.value = null;
  form.value = { kode_lokasi: '', instansi: '', unit_instansi: '', longitude: '', latitude: '' };
  errors.value = {};
  showModal.value = true;
}

function openEditModal(loc) {
  isEditing.value = true;
  editingKode.value = loc.kode_lokasi;
  form.value = {
    kode_lokasi: loc.kode_lokasi,
    instansi: loc.instansi,
    unit_instansi: loc.unit_instansi,
    longitude: loc.longitude,
    latitude: loc.latitude,
  };
  errors.value = {};
  showModal.value = true;
}

async function saveLocation() {
  saving.value = true;
  errors.value = {};

  try {
    if (isEditing.value) {
      await lokasiService.update(editingKode.value, {
        instansi: form.value.instansi,
        unit_instansi: form.value.unit_instansi,
        longitude: form.value.longitude,
        latitude: form.value.latitude,
      });
    } else {
      await lokasiService.create(form.value);
    }
    showModal.value = false;
    fetchLocations(pagination.value.currentPage);
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {};
    }
  } finally {
    saving.value = false;
  }
}

async function deleteLocation(kodeLokasi) {
  if (!confirm('Apakah Anda yakin ingin menghapus lokasi ini?')) return;

  try {
    await lokasiService.delete(kodeLokasi);
    fetchLocations(pagination.value.currentPage);
  } catch (error) {
    console.error('Failed to delete location:', error);
  }
}

onMounted(() => {
  fetchLocations();
});
</script>

<style scoped>
.lokasi-page {
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

.search-box {
  position: relative;
  flex: 1;
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

.kode-cell {
  display: flex;
  align-items: center;
}

.kode-badge {
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

.instansi-text {
  font-weight: 500;
}

.koordinat-cell {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #6b7280;
  font-size: 13px;
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
.loc-form {
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

.form-input:disabled {
  background: #f3f4f6;
  color: #9ca3af;
  cursor: not-allowed;
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
