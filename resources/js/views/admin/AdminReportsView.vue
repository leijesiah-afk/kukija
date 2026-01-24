<template>
    <section>
        <div class="product-card" style="align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Reports</div>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Sales, customers, and inventory snapshots.</div>
            <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
                <button class="header-btn" type="button" :class="{ secondary: tab === 'sales' }" @click="tab = 'sales'">Sales</button>
                <button class="header-btn" type="button" :class="{ secondary: tab === 'customers' }" @click="tab = 'customers'">Customers</button>
                <button class="header-btn" type="button" :class="{ secondary: tab === 'inventory' }" @click="tab = 'inventory'">Inventory</button>
            </div>
        </div>

        <div v-if="error" class="product-card" style="margin-top:22px;align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Oops</div>
            <div style="font-family:'Fredoka',cursive;">{{ error }}</div>
        </div>
        <div v-else-if="loading" class="product-card" style="margin-top:22px;align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Loading...</div>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Fetching report.</div>
        </div>

        <div v-else style="margin-top:22px;display:grid;gap:18px;">
            <div v-if="tab === 'sales'" class="product-card" style="align-items:stretch;padding:22px 22px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div class="product-title" style="text-align:left;">Sales Report</div>
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <label class="legacy-label" for="admin_sales_period" style="margin-right:6px;">Period</label>
                        <select id="admin_sales_period" name="period" v-model="salesPeriod" class="legacy-select" style="max-width:220px;">
                            <option value="day">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="year">This Year</option>
                        </select>
                        <button class="header-btn" type="button" @click="refresh">Refresh</button>
                    </div>
                </div>

                <div class="legacy-admin-grid" style="margin-top:14px;">
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Revenue</div>
                        <div class="product-price" style="margin-bottom:0;">₱{{ Number(sales.summary?.total_revenue || 0).toFixed(2) }}</div>
                    </div>
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Paid Orders</div>
                        <div class="product-price" style="margin-bottom:0;">{{ sales.summary?.paid_orders || 0 }}</div>
                    </div>
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Total Orders</div>
                        <div class="product-price" style="margin-bottom:0;">{{ sales.summary?.total_orders || 0 }}</div>
                    </div>
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Avg Order</div>
                        <div class="product-price" style="margin-bottom:0;">₱{{ Number(sales.summary?.average_order_value || 0).toFixed(2) }}</div>
                    </div>
                    <div v-if="sales.reviews" class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Reviews</div>
                        <div class="product-price" style="margin-bottom:0;">{{ sales.reviews?.summary?.total_reviews || 0 }}</div>
                    </div>
                    <div v-if="sales.reviews" class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Avg Rating</div>
                        <div class="product-price" style="margin-bottom:0;">{{ Number(sales.reviews?.summary?.average_rating || 0).toFixed(2) }}</div>
                    </div>
                </div>

                <div class="product-title" style="text-align:left;margin-top:16px;">Top Products</div>
                <div class="legacy-table-wrap" style="margin-top:10px;">
                    <table class="legacy-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in sales.sales_by_product" :key="p.name">
                                <td style="font-weight:800;">{{ p.name }}</td>
                                <td><span class="legacy-pill">{{ p.quantity_sold }}</span></td>
                                <td><span class="legacy-pill">₱{{ Number(p.revenue || 0).toFixed(2) }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="sales.reviews" class="product-title" style="text-align:left;margin-top:16px;">Top Reviewed Products</div>
                <div v-if="sales.reviews" class="legacy-table-wrap" style="margin-top:10px;">
                    <table class="legacy-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Reviews</th>
                                <th>Avg Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in (sales.reviews?.top_products || [])" :key="p.product_id">
                                <td style="font-weight:800;">{{ p.name }}</td>
                                <td><span class="legacy-pill">{{ p.reviews_count }}</span></td>
                                <td><span class="legacy-pill">{{ Number(p.average_rating || 0).toFixed(2) }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else-if="tab === 'customers'" class="product-card" style="align-items:stretch;padding:22px 22px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div class="product-title" style="text-align:left;">Customer Activity</div>
                    <button class="header-btn" type="button" @click="refresh">Refresh</button>
                </div>

                <div class="legacy-admin-grid" style="margin-top:14px;">
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">New Customers</div>
                        <div class="product-price" style="margin-bottom:0;">{{ customers.new_customers || 0 }}</div>
                    </div>
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Active Customers</div>
                        <div class="product-price" style="margin-bottom:0;">{{ customers.active_customers || 0 }}</div>
                    </div>
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">High Value</div>
                        <div class="product-price" style="margin-bottom:0;">{{ customers.customer_segments?.high_value || 0 }}</div>
                    </div>
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Low Value</div>
                        <div class="product-price" style="margin-bottom:0;">{{ customers.customer_segments?.low_value || 0 }}</div>
                    </div>
                </div>
            </div>

            <div v-else class="product-card" style="align-items:stretch;padding:22px 22px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div class="product-title" style="text-align:left;">Inventory Report</div>
                    <button class="header-btn" type="button" @click="refresh">Refresh</button>
                </div>

                <div class="legacy-admin-grid" style="margin-top:14px;">
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Total Products</div>
                        <div class="product-price" style="margin-bottom:0;">{{ inventory.summary?.total_products || 0 }}</div>
                    </div>
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Low Stock</div>
                        <div class="product-price" style="margin-bottom:0;">{{ inventory.summary?.low_stock_products || 0 }}</div>
                    </div>
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Out of Stock</div>
                        <div class="product-price" style="margin-bottom:0;">{{ inventory.summary?.out_of_stock_products || 0 }}</div>
                    </div>
                    <div class="product-card legacy-admin-card">
                        <div class="product-title" style="text-align:left;">Stock Value</div>
                        <div class="product-price" style="margin-bottom:0;">₱{{ Number(inventory.summary?.total_stock_value || 0).toFixed(2) }}</div>
                    </div>
                </div>

                <div class="product-title" style="text-align:left;margin-top:16px;">Inventory</div>
                <div class="legacy-table-wrap" style="margin-top:10px;">
                    <table class="legacy-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Reorder</th>
                                <th>Flags</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in inventory.inventory" :key="row.product_id">
                                <td style="font-weight:800;">{{ row.product_name }}</td>
                                <td><span class="legacy-pill">{{ row.stock_quantity }}</span></td>
                                <td><span class="legacy-pill">{{ row.reorder_level }}</span></td>
                                <td>
                                    <span v-if="row.is_out_of_stock" class="legacy-pill">OUT</span>
                                    <span v-else-if="row.is_low_stock" class="legacy-pill">LOW</span>
                                    <span v-else class="legacy-pill">OK</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';

const tab = ref('sales');

const loading = ref(false);
const error = ref(null);

const salesPeriod = ref('month');
const sales = ref({ summary: {}, sales_by_day: [], sales_by_product: [] });
const customers = ref({});
const inventory = ref({ summary: {}, inventory: [] });

async function refresh() {
    loading.value = true;
    error.value = null;

    try {
        if (tab.value === 'sales') {
            const { data } = await axios.get('admin/reports/sales', { params: { period: salesPeriod.value } });
            sales.value = data || { summary: {}, sales_by_day: [], sales_by_product: [] };
        } else if (tab.value === 'customers') {
            const { data } = await axios.get('admin/reports/customer-activity');
            customers.value = data || {};
        } else {
            const { data } = await axios.get('admin/reports/inventory');
            inventory.value = data || { summary: {}, inventory: [] };
        }
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to load report.';
    } finally {
        loading.value = false;
    }
}

onMounted(refresh);
watch(tab, refresh);
watch(salesPeriod, () => {
    if (tab.value !== 'sales') return;
    refresh();
});
</script>
