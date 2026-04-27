import api from './api';

export default {
    getAll(params = {}) {
        return api.get('/lokasi', { params });
    },
    create(data) {
        return api.post('/lokasi', data);
    },
    update(kodeLokasi, data) {
        return api.put(`/lokasi/${kodeLokasi}`, data);
    },
    delete(kodeLokasi) {
        return api.delete(`/lokasi/${kodeLokasi}`);
    },
};
