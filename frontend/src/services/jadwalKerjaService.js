import api from './api';

export default {
    getAll(params = {}) {
        return api.get('/jadwal-kerja', { params });
    },
    create(data) {
        return api.post('/jadwal-kerja', data);
    },
    update(id, data) {
        return api.put(`/jadwal-kerja/${id}`, data);
    },
    delete(id) {
        return api.delete(`/jadwal-kerja/${id}`);
    },
};
