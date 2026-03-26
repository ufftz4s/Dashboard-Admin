import api from './api';

export default {
    getAll(params = {}) {
        return api.get('/employees', { params });
    },
    create(data) {
        return api.post('/employees', data);
    },
    update(id, data) {
        return api.put(`/employees/${id}`, data);
    },
    delete(id) {
        return api.delete(`/employees/${id}`);
    },
};
