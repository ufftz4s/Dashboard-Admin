import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Response interceptor for global error handling
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            const { status, data } = error.response;

            if (status === 422) {
                console.error('Validation Error:', data.errors);
            } else if (status === 404) {
                console.error('Resource not found');
            } else if (status === 500) {
                console.error('Server error');
            }
        } else if (error.request) {
            console.error('Network error: No response received');
        }

        return Promise.reject(error);
    }
);

export default api;
