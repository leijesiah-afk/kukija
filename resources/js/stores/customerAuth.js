import { defineStore } from 'pinia';
import axios from 'axios';

export const useCustomerAuthStore = defineStore('customerAuth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('kukija_customer_token') || null,
        loading: false,
        lastError: null,
    }),
    actions: {
        setToken(token) {
            this.token = token;
            if (token) {
                localStorage.setItem('kukija_customer_token', token);
            } else {
                localStorage.removeItem('kukija_customer_token');
            }
        },

        setUser(user) {
            this.user = user;
        },

        async register({ firstName, lastName, email, address, contactNo, password, deviceName }) {
            this.loading = true;
            this.lastError = null;

            try {
                const { data } = await axios.post('auth/register', {
                    first_name: firstName,
                    last_name: lastName,
                    email,
                    address,
                    contact_no: contactNo,
                    password,
                    device_name: deviceName || 'storefront',
                });

                this.setToken(data?.token);
                this.setUser(data?.user ?? null);
            } catch (e) {
                this.lastError = e?.response?.data?.message || 'Sign up failed.';
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async login({ email, password, deviceName }) {
            this.loading = true;
            this.lastError = null;

            try {
                const { data } = await axios.post('auth/login', {
                    email,
                    password,
                    device_name: deviceName || 'storefront',
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
                const { data } = await axios.get('auth/me');
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
                    await axios.post('auth/logout');
                }
            } catch (e) {
            } finally {
                this.setToken(null);
                this.setUser(null);
                this.loading = false;
            }
        },
    },
});
