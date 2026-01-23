import { defineStore } from 'pinia';
import axios from 'axios';

export const useCartStore = defineStore('cart', {
    state: () => ({
        cart: null,
        items: [],
        totals: {
            subtotal: 0,
            total_items: 0,
        },
        loading: false,
        lastError: null,
    }),
    getters: {
        count: (state) => state.totals?.total_items ?? 0,
        total: (state) => state.totals?.subtotal ?? 0,
    },
    actions: {
        applyApiCart(payload) {
            this.cart = payload?.cart ?? null;
            this.items = payload?.items ?? [];
            this.totals = payload?.totals ?? { subtotal: 0, total_items: 0 };
        },

        clear() {
            this.cart = null;
            this.items = [];
            this.totals = { subtotal: 0, total_items: 0 };
        },

        async fetch() {
            this.loading = true;
            this.lastError = null;

            try {
                const { data } = await axios.get('jar');
                this.applyApiCart(data);
            } catch (e) {
                if (e?.response?.status === 401) {
                    this.lastError = e?.response?.data?.message || 'You must be logged in to start a jar.';
                    this.clear();
                    return;
                }
                this.lastError = e?.response?.data?.message || 'Failed to load cart.';
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async addItem(productId, quantity = 1) {
            this.loading = true;
            this.lastError = null;

            try {
                const { data } = await axios.post('jar/items', {
                    product_id: productId,
                    quantity,
                });
                this.applyApiCart(data);
                try {
                    window.dispatchEvent(new Event('kukija:jar-bump'));
                } catch {
                }
            } catch (e) {
                if (e?.response?.status === 401) {
                    this.lastError = e?.response?.data?.message || 'You must be logged in to start a jar.';
                    this.clear();
                    throw e;
                }
                this.lastError = e?.response?.data?.message || 'Failed to add item.';
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async updateItem(itemId, quantity) {
            this.loading = true;
            this.lastError = null;

            try {
                const { data } = await axios.patch(`jar/items/${itemId}`, {
                    quantity,
                });
                this.applyApiCart(data);
            } catch (e) {
                if (e?.response?.status === 401) {
                    this.lastError = e?.response?.data?.message || 'You must be logged in to start a jar.';
                    this.clear();
                    throw e;
                }
                this.lastError = e?.response?.data?.message || 'Failed to update item.';
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async removeItem(itemId) {
            this.loading = true;
            this.lastError = null;

            try {
                const { data } = await axios.delete(`jar/items/${itemId}`);
                this.applyApiCart(data);
            } catch (e) {
                if (e?.response?.status === 401) {
                    this.lastError = e?.response?.data?.message || 'You must be logged in to start a jar.';
                    this.clear();
                    throw e;
                }
                this.lastError = e?.response?.data?.message || 'Failed to remove item.';
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async checkout({ name, email, address, phone, payment_method, payment_reference }) {
            this.loading = true;
            this.lastError = null;

            try {
                const { data } = await axios.post('checkout', {
                    name,
                    email,
                    address,
                    phone,
                    payment_method,
                    payment_reference,
                });

                await this.fetch();
                return data;
            } catch (e) {
                if (e?.response?.status === 401) {
                    this.lastError = e?.response?.data?.message || 'You must be logged in to checkout.';
                    this.clear();
                    throw e;
                }
                this.lastError = e?.response?.data?.message || 'Checkout failed.';
                throw e;
            } finally {
                this.loading = false;
            }
        },
    },
});
