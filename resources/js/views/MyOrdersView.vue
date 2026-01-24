<template>
    <section style="max-width:1200px;margin:48px auto 72px auto;padding:0 24px;">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <div style="font-family:'Cookie',cursive;font-size:2.2rem;color:var(--gold);">My Past Orders</div>
                <div style="font-family:'Fredoka',cursive;font-size:1rem;opacity:0.9;">Review your past cookie orders.</div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                <button class="header-btn" type="button" :disabled="loading" @click="fetchOrders">Refresh</button>
                <RouterLink to="/" class="header-btn" style="text-decoration:none;">Back to Shop</RouterLink>
            </div>
        </div>

        <div v-if="loading" class="product-grid" style="grid-template-columns: 1fr; margin-top:22px;">
            <div class="product-card" style="align-items:flex-start;">
                <div class="product-title">Loading...</div>
                <div style="font-family:'Fredoka',cursive; font-size:1rem;">Fetching your orders...</div>
            </div>
        </div>

        <div v-else-if="error" class="product-grid" style="grid-template-columns: 1fr; margin-top:22px;">
            <div class="product-card" style="align-items:flex-start;">
                <div class="product-title">Oops</div>
                <div style="font-family:'Fredoka',cursive; font-size:1rem;">{{ error }}</div>
            </div>
        </div>

        <div v-else-if="orders.length === 0" class="product-grid" style="grid-template-columns: 1fr; margin-top:22px;">
            <div class="product-card" style="align-items:flex-start;">
                <div class="product-title">No Orders</div>
                <div style="font-family:'Fredoka',cursive; font-size:1rem;">You have no orders yet.</div>
                <RouterLink to="/" class="header-btn" style="margin-top:14px;text-decoration:none;">Shop Cookies</RouterLink>
            </div>
        </div>

        <div v-else style="margin-top:22px;display:grid;grid-template-columns: 1fr;gap:16px;">
            <div v-for="o in orders" :key="o.id" class="product-card" style="align-items:stretch;padding:22px 22px;">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                    <div>
                        <div class="product-title" style="text-align:left;">{{ o.order_number }}</div>
                        <div style="font-family:'Kalam',cursive;opacity:0.9;">Placed: {{ formatDate(o.placed_at || o.created_at) }}</div>
                        <div style="font-family:'Fredoka',cursive;opacity:0.9;margin-top:6px;">
                            Status:
                            <b>{{ displayStatus(o.status) }}</b>
                            <span v-if="o.received_at" style="margin-left:8px;opacity:0.85;">(Received)</span>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-family:'Fredoka',cursive;font-weight:900;color:var(--gold);font-size:1.2rem;">₱{{ Number(o.total || 0).toFixed(2) }}</div>
                        <button
                            v-if="canMarkReceived(o)"
                            class="add-to-jar-btn"
                            type="button"
                            style="margin-top:10px;padding:10px 18px;"
                            :disabled="savingId === o.id"
                            @click="markReceived(o)"
                        >
                            Order Received
                        </button>
                    </div>
                </div>

                <div style="margin-top:14px;padding-top:14px;border-top:2px dashed rgba(212,165,116,0.25);display:grid;gap:10px;">
                    <div v-for="it in o.items" :key="it.id" style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div style="display:flex;gap:12px;align-items:flex-start;">
                                <img
                                    :src="itemImageSrc(it)"
                                    :alt="it.product?.name || it.product_name"
                                    style="width:56px;height:56px;border-radius:50%;object-fit:contain;background:#fff;box-shadow:0 10px 22px rgba(212,165,116,0.18);flex:0 0 auto;"
                                />
                                <div>
                                    <div style="font-family:'Fredoka',cursive;font-weight:900;">{{ it.product?.name || it.product_name }}</div>
                                    <div v-if="it.product?.description" style="font-family:'Fredoka',cursive;opacity:0.85;line-height:1.35;max-width:680px;">
                                        {{ it.product.description }}
                                    </div>
                                    <div style="font-family:'Kalam',cursive;opacity:0.9;">Qty {{ it.quantity }}</div>
                                </div>
                            </div>
                        </div>
                        <div style="display:flex;gap:10px;align-items:center;justify-content:flex-end;flex-wrap:wrap;">
                            <div style="font-family:'Fredoka',cursive;font-weight:900;">₱{{ Number(it.line_total || 0).toFixed(2) }}</div>
                            <button
                                class="header-btn"
                                type="button"
                                :disabled="!canReview(o, it)"
                                :style="reviewButtonStyle(o, it)"
                                :title="canReview(o, it) ? 'Leave a Review' : 'Available when delivered'"
                                @click="openReview(o, it)"
                            >
                                Leave a Review
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal-bg" :class="{ active: !!reviewOrder }" @click.self="closeReview">
        <div v-if="reviewOrder" class="modal" style="min-width: min(520px, 95vw);">
            <button class="close-modal" type="button" @click="closeReview">×</button>
            <h2>Leave a Review</h2>
            <div style="font-family:'Fredoka',cursive;opacity:0.9;text-align:center;margin-bottom:12px;">
                {{ reviewItem?.product_name }}
            </div>

            <div style="display:grid;gap:10px;">
                <div>
                    <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Rating (1-5)</div>
                    <input v-model.number="reviewRating" type="number" min="1" max="5" style="width:100%;border-radius:14px;border:2px dashed rgba(212,165,116,0.35);padding:10px 12px;font-family:'Fredoka',cursive;background:#fff;" />
                </div>
                <div>
                    <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Comment (optional)</div>
                    <textarea v-model="reviewComment" rows="4" style="width:100%;border-radius:14px;border:2px dashed rgba(212,165,116,0.35);padding:10px 12px;font-family:'Fredoka',cursive;background:#fff;"></textarea>
                </div>

                <div v-if="reviewError" class="error-msg">{{ reviewError }}</div>

                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:6px;">
                    <button class="header-btn" type="button" @click="closeReview">Cancel</button>
                    <button class="add-to-jar-btn" type="button" style="padding:10px 22px;" :disabled="reviewSaving" @click="submitReview">
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';

const loading = ref(false);
const error = ref(null);
const orders = ref([]);
const savingId = ref(null);

const reviewOrder = ref(null);
const reviewItem = ref(null);
const reviewRating = ref(5);
const reviewComment = ref('');
const reviewSaving = ref(false);
const reviewError = ref(null);

const baseUrl = (import.meta.env.VITE_BASE_URL || '/').replace(/\/?$/, '/');

let pollTimer = null;

function displayStatus(value) {
    const raw = String(value || '').trim().toLowerCase();
    if (!raw) return '';

    const map = {
        pending: 'Pending',
        processing: 'Processing',
        shipped: 'Shipped',
        delivered: 'Delivered',
        cancelled: 'Cancelled',
        canceled: 'Cancelled',
    };

    if (map[raw]) return map[raw];
    return raw.charAt(0).toUpperCase() + raw.slice(1);
}

function formatDate(value) {
    if (!value) return '';
    try {
        return new Date(value).toLocaleString();
    } catch {
        return String(value);
    }
}

function startPolling() {
    stopPolling();
    pollTimer = setInterval(() => {
        if (loading.value) return;
        fetchOrders();
    }, 15000);
}

function stopPolling() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

function isReviewed(order, item) {
    const list = order?.my_reviews || [];
    return Array.isArray(list) && list.some((r) => Number(r.product_id) === Number(item.product_id));
}

function canMarkReceived(order) {
    if (!order) return false;
    if (order.received_at) return false;
    return order.status === 'shipped' || order.status === 'delivered';
}

function canReview(order, item) {
    if (!order || !item) return false;
    if (order.status !== 'delivered') return false;
    if (isReviewed(order, item)) return false;
    return true;
}

function itemImageSrc(item) {
    const p = item?.product;
    if (p?.image_path) return `${baseUrl}${encodeURI(p.image_path)}`;
    return `${baseUrl}logo.png`;
}

function reviewButtonStyle(order, item) {
    if (canReview(order, item)) return '';
    return 'opacity:0.55;filter:grayscale(100%);cursor:not-allowed;';
}

async function fetchOrders() {
    loading.value = true;
    error.value = null;

    try {
        const { data } = await axios.get('orders');
        orders.value = data?.data ?? [];
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to load orders.';
    } finally {
        loading.value = false;
    }
}

async function markReceived(order) {
    if (!order) return;
    savingId.value = order.id;

    try {
        await axios.patch(`orders/${order.id}/received`);
        await fetchOrders();
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to mark received.';
    } finally {
        savingId.value = null;
    }
}

function openReview(order, item) {
    reviewOrder.value = order;
    reviewItem.value = item;
    reviewRating.value = 5;
    reviewComment.value = '';
    reviewError.value = null;
    reviewSaving.value = false;
}

function closeReview() {
    reviewOrder.value = null;
    reviewItem.value = null;
    reviewError.value = null;
    reviewSaving.value = false;
}

async function submitReview() {
    if (!reviewOrder.value || !reviewItem.value) return;

    reviewSaving.value = true;
    reviewError.value = null;

    try {
        await axios.post('reviews', {
            order_id: reviewOrder.value.id,
            product_id: reviewItem.value.product_id,
            rating: reviewRating.value,
            comment: reviewComment.value || null,
        });

        closeReview();
        await fetchOrders();
    } catch (e) {
        reviewError.value = e?.response?.data?.message || 'Failed to submit review.';
    } finally {
        reviewSaving.value = false;
    }
}

onMounted(async () => {
    await fetchOrders();
    startPolling();
});

onUnmounted(stopPolling);
</script>
