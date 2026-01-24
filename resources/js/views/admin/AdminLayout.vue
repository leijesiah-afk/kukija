<template>
    <div>
        <header class="header">
            <div class="header-content">
                <RouterLink to="/admin" class="logo" style="display:block;">
                    <img :src="logoUrl" alt="Kukija" class="logo logo-img admin-logo-img" style="display:block;" />
                </RouterLink>

                <nav class="header-actions" style="flex-wrap:wrap; justify-content:flex-end;">
                    <RouterLink to="/admin" class="header-btn" title="Dashboard">Dashboard</RouterLink>
                    <RouterLink to="/admin/products" class="header-btn" title="Products">Products</RouterLink>
                    <RouterLink to="/admin/orders" class="header-btn" title="Orders">Orders</RouterLink>
                    <RouterLink to="/admin/customers" class="header-btn" title="Customers">Customers</RouterLink>
                    <RouterLink to="/admin/reports" class="header-btn secondary" title="Reports">Reports</RouterLink>
                    <button class="header-btn" type="button" :disabled="auth.loading" @click="logout">Logout</button>
                </nav>
            </div>
        </header>

        <main class="main-content" style="max-width:1200px;margin:0 auto;padding:40px 24px 72px 24px;">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const auth = useAuthStore();
const baseUrl = import.meta.env.VITE_BASE_URL || '/';
const normalizedBase = baseUrl.endsWith('/') ? baseUrl : `${baseUrl}/`;
const logoUrl = `${normalizedBase}logo.png`;

onMounted(async () => {
    if (!auth.token) {
        await router.replace('/admin/login');
        return;
    }

    try {
        await auth.fetchMe();
    } catch {
        await router.replace('/admin/login');
    }
});

async function logout() {
    await auth.logout();
    await router.replace('/admin/login');
}
</script>
