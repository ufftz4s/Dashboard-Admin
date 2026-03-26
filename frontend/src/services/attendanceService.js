import api from './api';

export default {
    getAll(params = {}) {
        return api.get('/attendances', { params });
    },
    create(data) {
        return api.post('/attendances', data);
    },
    update(id, data) {
        return api.put(`/attendances/${id}`, data);
    },
};
