<template>
    <section>
        <div class="product-card" style="align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Customers</div>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Customer list, order history, and status.</div>
            <div class="legacy-admin-grid" style="margin-top:14px;width:100%;">
                <div>
                    <label class="legacy-label" for="admin_customer_search">Search</label>
                    <input
                        v-model="search"
                        class="legacy-input"
                        placeholder="Search name / email"
                        id="admin_customer_search"
                        name="search"
                        style="margin-top:8px;"
                    />
                </div>
                <div>
                    <label class="legacy-label" for="admin_customer_status">Status</label>
                    <select id="admin_customer_status" name="status" v-model="status" class="legacy-select" style="margin-top:8px;">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
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
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Fetching customers.</div>
        </div>

        <div v-else style="margin-top:22px;display:grid;gap:18px;">
            <div class="product-card" style="align-items:stretch;padding:22px 22px;">
                <div class="product-title" style="text-align:left;">List</div>
                <div class="legacy-table-wrap" style="margin-top:10px;">
                    <table class="legacy-table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Orders</th>
                                <th>Revenue</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in customers" :key="c.id">
                                <td>
                                    <div style="font-weight:800;">{{ c.name || (c.first_name + ' ' + c.last_name) }}</div>
                                    <div style="font-family:'Kalam',cursive;opacity:0.8;">{{ c.email }}</div>
                                </td>
                                <td>
                                    <span class="legacy-pill">{{ c.status || 'active' }}</span>
                                </td>
                                <td>
                                    <span class="legacy-pill">{{ c.orders_count || 0 }}</span>
                                </td>
                                <td>
                                    <span class="legacy-pill">₱{{ Number(c.total_revenue || 0).toFixed(2) }}</span>
                                </td>
                                <td style="text-align:right;">
                                    <button class="header-btn" type="button" @click="openCustomer(c.id)">View</button>
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

        <div class="modal-bg" :class="{ active: !!activeCustomer }" @click.self="closeCustomer">
            <div v-if="activeCustomer" class="modal" style="min-width: min(820px, 95vw);">
                <button class="close-modal" type="button" @click="closeCustomer">×</button>
                <h2>Customer</h2>

                <div style="display:grid;gap:12px;">
                    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;font-family:'Fredoka',cursive;">
                        <div>
                            <div style="font-weight:800;">{{ activeCustomer.name || (activeCustomer.first_name + ' ' + activeCustomer.last_name) }}</div>
                            <div style="opacity:0.85;">{{ activeCustomer.email }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:800;">Orders: {{ activeCustomer.orders_count || 0 }}</div>
                            <div style="opacity:0.85;">Revenue: ₱{{ Number(activeCustomer.total_revenue || 0).toFixed(2) }}</div>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                        <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Status</div>
                        <select
                            :id="`admin_customer_status_${activeCustomer.id}`"
                            :name="`status_${activeCustomer.id}`"
                            v-model="activeStatus"
                            class="rounded-lg border px-3 py-2 text-sm"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
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
                        <div class="product-title" style="text-align:left;">Recent Orders</div>
                        <div v-if="!activeCustomer.orders || activeCustomer.orders.length === 0" style="margin-top:10px;font-family:'Fredoka',cursive;opacity:0.85;">
                            No orders yet.
                        </div>
                        <div v-else style="margin-top:10px;display:grid;gap:10px;">
                            <div v-for="o in activeCustomer.orders" :key="o.id" style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;font-family:'Fredoka',cursive;">
                                <div>
                                    <div style="font-weight:800;">{{ o.order_number }}</div>
                                    <div style="opacity:0.85;font-family:'Kalam',cursive;">{{ formatDate(o.created_at) }}</div>
                                </div>
                                <div style="font-weight:800;">₱{{ Number(o.total || 0).toFixed(2) }}</div>
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
const customers = ref([]);

const page = ref(1);
const hasMore = ref(false);

const search = ref('');
const status = ref('');

const activeCustomer = ref(null);
const activeStatus = ref('active');
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

async function fetchCustomers() {
    loading.value = true;
    error.value = null;

    try {
        const { data } = await axios.get('admin/customers', {
            params: {
                page: page.value,
                per_page: 15,
                search: search.value || undefined,
                status: status.value || undefined,
            },
        });

        customers.value = data?.data ?? [];
        hasMore.value = !!data?.next_page_url;
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to load customers.';
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

async function openCustomer(id) {
    modalError.value = null;
    saving.value = false;

    try {
        const { data } = await axios.get(`admin/customers/${id}`);
        activeCustomer.value = data;
        activeStatus.value = data?.status || 'active';
    } catch (e) {
        modalError.value = e?.response?.data?.message || 'Failed to load customer.';
    }
}

function closeCustomer() {
    activeCustomer.value = null;
    modalError.value = null;
}

async function saveStatus() {
    if (!activeCustomer.value) return;

    saving.value = true;
    modalError.value = null;

    try {
        const { data } = await axios.patch(`admin/customers/${activeCustomer.value.id}/status`, {
            status: activeStatus.value,
        });

        activeCustomer.value = {
            ...activeCustomer.value,
            ...(data?.customer ?? {}),
        };

        await fetchCustomers();
    } catch (e) {
        modalError.value = e?.response?.data?.message || 'Failed to update customer.';
    } finally {
        saving.value = false;
    }
}

onMounted(fetchCustomers);
watch(page, fetchCustomers);
watch(status, async () => {
    page.value = 1;
    await fetchCustomers();
});
watch(search, () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        page.value = 1;
        await fetchCustomers();
    }, 250);
});
</script>
