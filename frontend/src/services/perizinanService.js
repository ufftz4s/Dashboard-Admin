import api from './api';

export default {
    getAll(params = {}) {
        return api.get('/perizinan', { params });
    },
    getById(id) {
        return api.get(`/perizinan/${id}`);
    },
    create(data) {
        return api.post('/perizinan', data);
    },
    update(id, data) {
        return api.put(`/perizinan/${id}`, data);
    },
    approve(id, data) {
        return api.patch(`/perizinan/${id}/approve`, data);
    },
    reject(id, data) {
        return api.patch(`/perizinan/${id}/reject`, data);
    },
};
