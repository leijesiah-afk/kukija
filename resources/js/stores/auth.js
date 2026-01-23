import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('kukija_admin_token') || null,
        loading: false,
        lastError: null,
    }),
    actions: {
        setToken(token) {
            this.token = token;
            if (token) {
                localStorage.setItem('kukija_admin_token', token);
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            } else {
                localStorage.removeItem('kukija_admin_token');
                delete axios.defaults.headers.common['Authorization'];
            }
        },

        setUser(user) {
            this.user = user;
        },

        async login({ email, password, deviceName }) {
            this.loading = true;
            this.lastError = null;

            try {
                const { data } = await axios.post('admin/login', {
                    email,
                    password,
                    device_name: deviceName || 'admin-panel',
                });

                this.setToken(data?.token);
                this.setUser(data?.user ?? null);
            } catch (e) {
                this.lastError = e?.response?.data?.message || 'Login failed.';
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async fetchMe() {
            if (!this.token) return null;

            this.loading = true;
            this.lastError = null;

            try {
                const { data } = await axios.get('admin/me');
                this.setUser(data?.user ?? null);
                return this.user;
            } catch (e) {
                this.setToken(null);
                this.setUser(null);
                this.lastError = e?.response?.data?.message || 'Session expired.';
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            this.loading = true;
            this.lastError = null;

            try {
                if (this.token) {
                    await axios.post('admin/logout');
                }
            } catch (e) {
                // ignore
            } finally {
                this.setToken(null);
                this.setUser(null);
                this.loading = false;
            }
        },
    },
});
