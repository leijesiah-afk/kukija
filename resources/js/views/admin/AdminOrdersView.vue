<template>
    <section>
        <div class="product-card" style="align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Orders</div>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Review jars that were checked out.</div>
            <div class="legacy-admin-grid" style="margin-top:14px;width:100%;">
                <div>
                    <label class="legacy-label" for="admin_order_search">Search</label>
                    <input
                        v-model="search"
                        class="legacy-input"
                        placeholder="Search order # / customer"
                        id="admin_order_search"
                        name="search"
                        style="margin-top:8px;"
                    />
                </div>
                <div>
                    <label class="legacy-label" for="admin_order_status">Status</label>
                    <select id="admin_order_status" name="status" v-model="status" class="legacy-select" style="margin-top:8px;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>

        <div v-if="error" class="product-card" style="margin-top:22px;align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Oops</div>
            <div style="font-family:'Fredoka',cursive;">{{ error }}</div>
        </div>
        <div v-else-if="loading" class="product-card" style="margin-top:22px;align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Loading...</div>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Fetching orders.</div>
        </div>

        <div v-else style="margin-top:22px;display:grid;gap:18px;">
            <div class="product-card" style="align-items:stretch;padding:22px 22px;">
                <div class="product-title" style="text-align:left;">List</div>
                <div class="legacy-table-wrap" style="margin-top:10px;">
                    <table class="legacy-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Placed</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="o in orders" :key="o.id">
                                <td>
                                    <div style="font-weight:800;">{{ o.order_number }}</div>
                                    <div style="font-family:'Kalam',cursive;opacity:0.8;">{{ o.item_count || (o.items?.length ?? 0) }} items</div>
                                </td>
                                <td>
                                    <div style="font-weight:800;">{{ o.customer_name }}</div>
                                    <div style="font-family:'Kalam',cursive;opacity:0.8;">{{ o.customer_email }}</div>
                                </td>
                                <td>
                                    <span class="legacy-pill">{{ o.status }}</span>
                                </td>
                                <td>
                                    <span class="legacy-pill">₱{{ Number(o.total || 0).toFixed(2) }}</span>
                                </td>
                                <td style="font-family:'Kalam',cursive;opacity:0.85;">{{ formatDate(o.placed_at || o.created_at) }}</td>
                                <td style="text-align:right;">
                                    <button class="header-btn" type="button" @click="openOrder(o.id)">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <button class="header-btn" type="button" :disabled="page <= 1 || loading" @click="prev">Prev</button>
                <div class="legacy-pill">Page {{ page }}</div>
                <button class="header-btn" type="button" :disabled="!hasMore || loading" @click="next">Next</button>
            </div>
        </div>

        <div class="modal-bg" :class="{ active: !!activeOrder }" @click.self="closeOrder">
            <div v-if="activeOrder" class="modal" style="min-width: min(820px, 95vw);">
                <button class="close-modal" type="button" @click="closeOrder">×</button>
                <h2>Order {{ activeOrder.order_number }}</h2>

                <div style="display:grid;gap:12px;">
                    <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;font-family:'Fredoka',cursive;">
                        <div>
                            <div style="font-weight:800;">{{ activeOrder.customer_name }}</div>
                            <div style="opacity:0.85;">{{ activeOrder.customer_email }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800;">₱{{ Number(activeOrder.total || 0).toFixed(2) }}</div>
                            <div style="opacity:0.85;">{{ formatDate(activeOrder.placed_at || activeOrder.created_at) }}</div>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Status</div>
                        <select
                            :id="`admin_order_status_${activeOrder.id}`"
                            :name="`status_${activeOrder.id}`"
                            v-model="activeStatus"
                            class="rounded-lg border px-3 py-2 text-sm"
                        >
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <button
                            class="add-to-jar-btn"
                            type="button"
                            style="padding:8px 18px;"
                            :disabled="saving"
                            @click="saveStatus"
                        >
                            Save
                        </button>
                    </div>

                    <div class="rounded-2xl bg-white border" style="padding:14px;">
                        <div class="product-title" style="text-align:left;">Items</div>
                        <div style="margin-top:10px;display:grid;gap:10px;">
                            <div v-for="it in activeOrder.items" :key="it.id" style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;font-family:'Fredoka',cursive;">
                                <div>
                                    <div style="font-weight:800;">{{ it.product_name }}</div>
                                    <div style="opacity:0.85;font-family:'Kalam',cursive;">Qty {{ it.quantity }}</div>
                                </div>
                                <div style="font-weight:800;">₱{{ Number(it.line_total || 0).toFixed(2) }}</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="modalError" class="error-msg">{{ modalError }}</div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref, watch } from 'vue';

const loading = ref(false);
const error = ref(null);
const orders = ref([]);

const page = ref(1);
const hasMore = ref(false);

const search = ref('');
const status = ref('');

const activeOrder = ref(null);
const activeStatus = ref('pending');
const saving = ref(false);
const modalError = ref(null);

let searchTimer = null;

function formatDate(value) {
    if (!value) return '';
    try {
        return new Date(value).toLocaleString();
    } catch {
        return String(value);
    }
}

async function fetchOrders() {
    loading.value = true;
    error.value = null;

    try {
        const { data } = await axios.get('admin/orders', {
            params: {
                page: page.value,
                per_page: 15,
                search: search.value || undefined,
                status: status.value || undefined,
            },
        });

        orders.value = data?.data ?? [];
        hasMore.value = !!data?.next_page_url;
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to load orders.';
    } finally {
        loading.value = false;
    }
}

function prev() {
    if (page.value <= 1) return;
    page.value -= 1;
}

function next() {
    if (!hasMore.value) return;
    page.value += 1;
}

async function openOrder(id) {
    modalError.value = null;
    saving.value = false;

    try {
        const { data } = await axios.get(`admin/orders/${id}`);
        activeOrder.value = data;
        activeStatus.value = data?.status || 'pending';
    } catch (e) {
        modalError.value = e?.response?.data?.message || 'Failed to load order.';
    }
}

function closeOrder() {
    activeOrder.value = null;
    modalError.value = null;
}

async function saveStatus() {
    if (!activeOrder.value) return;

    saving.value = true;
    modalError.value = null;

    try {
        const { data } = await axios.patch(`admin/orders/${activeOrder.value.id}/status`, {
            status: activeStatus.value,
        });

        activeOrder.value = data?.order ?? activeOrder.value;
        await fetchOrders();
    } catch (e) {
        modalError.value = e?.response?.data?.message || 'Failed to update order.';
    } finally {
        saving.value = false;
    }
}

onMounted(fetchOrders);
watch(page, fetchOrders);
watch(status, async () => {
    page.value = 1;
    await fetchOrders();
});
watch(search, () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        page.value = 1;
        await fetchOrders();
    }, 250);
});
</script>
