import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const baseUrl = (import.meta.env.VITE_BASE_URL || '/').replace(/\/?$/, '/');
window.axios.defaults.baseURL = `${baseUrl}api/`;

window.axios.interceptors.request.use((config) => {
    const url = config?.url || '';
    const isAdminRequest = /^\/?admin(\/|$)/.test(url) || url.includes('/admin/');
    const tokenKey = isAdminRequest ? 'kukija_admin_token' : 'kukija_customer_token';
    const token = localStorage.getItem(tokenKey);

    if (token) {
        config.headers = config.headers || {};
        config.headers.Authorization = `Bearer ${token}`;
    } else if (config?.headers?.Authorization) {
        delete config.headers.Authorization;
    }

    return config;
});

window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error?.response?.status;
        if (status !== 401 && status !== 419) {
            return Promise.reject(error);
        }

        const url = error?.config?.url || '';
        const isAdminRequest = /^\/?admin(\/|$)/.test(url) || url.includes('/admin/');

        if (isAdminRequest) {
            localStorage.removeItem('kukija_admin_token');
            if (location.pathname.includes('/admin') && !location.pathname.includes('/admin/login')) {
                location.assign(`${baseUrl.replace(/\/?$/, '')}/admin/login`);
            }
        } else {
            localStorage.removeItem('kukija_customer_token');
            try {
                window.dispatchEvent(new Event('kukija:open-login'));
            } catch {
            }
        }

        return Promise.reject(error);
    }
);
