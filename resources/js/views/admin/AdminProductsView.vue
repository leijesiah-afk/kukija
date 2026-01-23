<template>
    <section>
        <div class="product-card" style="align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Products</div>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Manage inventory, pricing, and visibility.</div>
            <div style="margin-top:14px;width:100%;">
                <input
                    v-model="search"
                    class="legacy-input"
                    placeholder="Search products"
                    id="admin_product_search"
                    name="search"
                />
            </div>
        </div>

        <div v-if="error" class="product-card" style="margin-top:22px;align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Oops</div>
            <div style="font-family:'Fredoka',cursive;">{{ error }}</div>
        </div>

        <div v-else-if="loading" class="product-card" style="margin-top:22px;align-items:flex-start;padding:22px 22px;">
            <div class="product-title" style="text-align:left;">Loading...</div>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;">Fetching products.</div>
        </div>

        <div v-else style="margin-top:22px;display:grid;gap:18px;">
            <div class="product-card" style="align-items:flex-start;padding:22px 22px;">
                <div class="product-title" style="text-align:left;">Add product</div>
                <div class="legacy-admin-grid" style="margin-top:14px;">
                    <div>
                        <label class="legacy-label" for="admin_create_name">Name</label>
                        <input id="admin_create_name" name="name" v-model="createForm.name" class="legacy-input" style="margin-top:8px;" />
                    </div>
                    <div>
                        <label class="legacy-label" for="admin_create_category">Category</label>
                        <select id="admin_create_category" name="category_id" v-model.number="createForm.category_id" class="legacy-select" style="margin-top:8px;">
                            <option :value="null" disabled>Select</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="legacy-label" for="admin_create_price">Price</label>
                        <input id="admin_create_price" name="price" v-model.number="createForm.price" type="number" min="0" step="0.01" class="legacy-input" style="margin-top:8px;" />
                    </div>
                    <div>
                        <label class="legacy-label" for="admin_create_stock">Stock</label>
                        <input id="admin_create_stock" name="stock" v-model.number="createForm.stock" type="number" min="0" step="1" class="legacy-input" style="margin-top:8px;" />
                    </div>
                    <div style="display:flex;align-items:flex-end;">
                        <button class="add-to-jar-btn" type="button" style="width:100%;" :disabled="saving" @click="createProduct">
                            Add
                        </button>
                    </div>
                </div>

                <div v-if="createError" style="margin-top:12px;" class="error-msg">
                    {{ createError }}
                </div>
            </div>

            <div class="product-card" style="align-items:stretch;padding:22px 22px;">
                <div class="product-title" style="text-align:left;">List</div>
                <div class="legacy-table-wrap" style="margin-top:10px;">
                    <table class="legacy-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Active</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="p in products" :key="p.id">
                                <td>
                                    <div style="font-weight:800;">{{ p.name }}</div>
                                    <div style="font-family:'Kalam',cursive;opacity:0.8;">{{ p.slug }}</div>
                                </td>
                                <td>
                                    <select
                                        v-model.number="edits[p.id].category_id"
                                        :id="`admin_category_${p.id}`"
                                        :name="`category_${p.id}`"
                                        class="legacy-select"
                                    >
                                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                                    </select>
                                </td>
                                <td>
                                    <input
                                        v-model.number="edits[p.id].price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        :id="`admin_price_${p.id}`"
                                        :name="`price_${p.id}`"
                                        class="legacy-input"
                                        style="max-width:140px;"
                                    />
                                </td>
                                <td>
                                    <input
                                        v-model.number="edits[p.id].stock"
                                        type="number"
                                        min="0"
                                        step="1"
                                        :id="`admin_stock_${p.id}`"
                                        :name="`stock_${p.id}`"
                                        class="legacy-input"
                                        style="max-width:120px;"
                                    />
                                </td>
                                <td>
                                    <label style="display:flex;gap:10px;align-items:center;">
                                        <input
                                            v-model="edits[p.id].is_active"
                                            type="checkbox"
                                            :id="`admin_active_${p.id}`"
                                            :name="`is_active_${p.id}`"
                                        />
                                        <span class="legacy-pill">{{ edits[p.id].is_active ? 'YES' : 'NO' }}</span>
                                    </label>
                                </td>
                                <td style="text-align:right;">
                                    <div style="display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;">
                                        <button class="header-btn" type="button" :disabled="saving" @click="saveProduct(p.id)">Save</button>
                                        <button class="header-btn secondary" type="button" :disabled="saving" @click="deleteProduct(p.id)">Delete</button>
                                    </div>
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
    </section>
</template>

<script setup>
import axios from 'axios';
import { onMounted, reactive, ref, watch } from 'vue';

const loading = ref(false);
const saving = ref(false);
const error = ref(null);

const createError = ref(null);
const categories = ref([]);

const search = ref('');
const page = ref(1);
const hasMore = ref(false);

const products = ref([]);
const edits = reactive({});

const createForm = reactive({
    name: '',
    category_id: null,
    price: 0,
    stock: 0,
});

async function fetchCategories() {
    const { data } = await axios.get('categories');
    categories.value = data?.data ?? [];

    if (!createForm.category_id && categories.value.length) {
        createForm.category_id = categories.value[0].id;
    }
}

function hydrateEdits(items) {
    for (const p of items) {
        edits[p.id] = {
            category_id: p.category_id,
            price: Number(p.price),
            stock: Number(p.stock),
            is_active: Boolean(p.is_active),
        };
    }
}

async function fetchProducts() {
    loading.value = true;
    error.value = null;

    try {
        const { data } = await axios.get('admin/products', {
            params: {
                search: search.value || undefined,
                per_page: 20,
                page: page.value,
            },
        });

        products.value = data?.data ?? [];
        hydrateEdits(products.value);

        hasMore.value = Boolean(data?.next_page_url);
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to load products.';
    } finally {
        loading.value = false;
    }
}

async function createProduct() {
    saving.value = true;
    createError.value = null;

    try {
        await axios.post('admin/products', {
            category_id: createForm.category_id,
            name: createForm.name,
            price: createForm.price,
            stock: createForm.stock,
            is_active: true,
        });

        createForm.name = '';
        createForm.price = 0;
        createForm.stock = 0;

        await fetchProducts();
    } catch (e) {
        createError.value = e?.response?.data?.message || 'Failed to create product.';
    } finally {
        saving.value = false;
    }
}

async function saveProduct(id) {
    saving.value = true;

    try {
        const payload = edits[id];
        await axios.patch(`admin/products/${id}`, payload);
        await fetchProducts();
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to save product.';
    } finally {
        saving.value = false;
    }
}

async function deleteProduct(id) {
    saving.value = true;

    try {
        await axios.delete(`admin/products/${id}`);
        await fetchProducts();
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to delete product.';
    } finally {
        saving.value = false;
    }
}

function next() {
    page.value += 1;
}

function prev() {
    page.value = Math.max(1, page.value - 1);
}

onMounted(async () => {
    await fetchCategories();
    await fetchProducts();
});

watch([search, page], async () => {
    await fetchProducts();
});
</script>
