import api from './api';

export default {
    getAll(params = {}) {
        return api.get('/pegawai', { params });
    },
    create(data) {
        return api.post('/pegawai', data);
    },
};
