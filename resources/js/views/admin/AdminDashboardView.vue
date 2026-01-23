<template>
    <section>
        <div class="product-card" style="align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Dashboard</div>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Today’s snapshot for Kukija.</div>
        </div>

        <div v-if="error" class="product-card" style="margin-top:22px;align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Oops</div>
            <div style="font-family:'Fredoka',cursive;">{{ error }}</div>
        </div>

        <div v-else-if="loading" class="product-card" style="margin-top:22px;align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Loading...</div>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Fetching admin stats.</div>
        </div>

        <div v-else style="margin-top:22px;">
            <div class="legacy-admin-grid">
                <div class="product-card legacy-admin-card">
                    <div class="product-title" style="text-align:left;">Sales (Today)</div>
                    <div class="product-price" style="margin-bottom:0;">₱{{ Number(stats.sales_today || 0).toFixed(2) }}</div>
                </div>
                <div class="product-card legacy-admin-card">
                    <div class="product-title" style="text-align:left;">Orders (Today)</div>
                    <div class="product-price" style="margin-bottom:0;">{{ stats.orders_today || 0 }}</div>
                </div>
                <div class="product-card legacy-admin-card">
                    <div class="product-title" style="text-align:left;">Low Stock</div>
                    <div class="product-price" style="margin-bottom:0;">{{ stats.low_stock || 0 }}</div>
                </div>
                <div class="product-card legacy-admin-card">
                    <div class="product-title" style="text-align:left;">Active Products</div>
                    <div class="product-price" style="margin-bottom:0;">{{ stats.total_products || 0 }}</div>
                </div>
            </div>

            <div class="product-card" style="margin-top:18px;align-items:flex-start;padding:22px 22px;">
                <div style="font-family:'Fredoka',cursive;opacity:0.9;">Next: Orders, customers, and reports.</div>
            </div>
        </div>
    </section>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';

const loading = ref(false);
const error = ref(null);
const stats = ref({});

onMounted(async () => {
    loading.value = true;
    error.value = null;

    try {
        const { data } = await axios.get('admin/dashboard/stats');
        stats.value = data || {};
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to load dashboard.';
    } finally {
        loading.value = false;
    }
});
</script>
