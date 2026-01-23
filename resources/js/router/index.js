import { createRouter, createWebHistory } from 'vue-router';

import ProductsView from '../views/ProductsView.vue';
import CartView from '../views/CartView.vue';
import CheckoutView from '../views/CheckoutView.vue';
import ProductDetailView from '../views/ProductDetailView.vue';
import AdminLayout from '../views/admin/AdminLayout.vue';
import AdminDashboardView from '../views/admin/AdminDashboardView.vue';
import AdminLoginView from '../views/admin/AdminLoginView.vue';
import AdminProductsView from '../views/admin/AdminProductsView.vue';
import AdminOrdersView from '../views/admin/AdminOrdersView.vue';
import AdminCustomersView from '../views/admin/AdminCustomersView.vue';
import AdminReportsView from '../views/admin/AdminReportsView.vue';

const base = import.meta.env.VITE_BASE_URL || '/';

const router = createRouter({
    history: createWebHistory(base),
    routes: [
        { path: '/', name: 'home', component: ProductsView },
        { path: '/products', name: 'products', component: ProductsView },
        { path: '/products/:slug', name: 'products.show', component: ProductDetailView },
        { path: '/jar', name: 'jar', component: CartView },
        { path: '/cart', name: 'cart', component: CartView },
        { path: '/checkout', name: 'checkout', component: CheckoutView },
        { path: '/admin/login', name: 'admin.login', component: AdminLoginView },
        {
            path: '/admin',
            component: AdminLayout,
            meta: { requiresAdmin: true },
            children: [
                { path: '', name: 'admin.dashboard', component: AdminDashboardView },
                { path: 'products', name: 'admin.products', component: AdminProductsView },
                { path: 'orders', name: 'admin.orders', component: AdminOrdersView },
                { path: 'customers', name: 'admin.customers', component: AdminCustomersView },
                { path: 'reports', name: 'admin.reports', component: AdminReportsView },
            ],
        },
    ],
});

router.beforeEach((to) => {
    if (to.name === 'jar' || to.name === 'cart' || to.name === 'checkout') {
        const token = localStorage.getItem('kukija_customer_token');
        if (!token) {
            return { name: 'home', query: { auth: 'login', reason: 'jar' } };
        }
    }

    if (!to.matched.some((r) => r.meta?.requiresAdmin)) {
        return true;
    }

    const token = localStorage.getItem('kukija_admin_token');
    if (!token) {
        return { name: 'admin.login' };
    }

    return true;
});

export default router;
