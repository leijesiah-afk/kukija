<template>
    <section>
        <section class="cookie-carousel-section">
            <div class="carousel-header" tabindex="0" @keydown.left.prevent="prevCookie" @keydown.right.prevent="nextCookie" @mouseenter="pauseCarousel" @mouseleave="resumeCarousel">
                <button class="carousel-arrow" type="button" @click="prevCookie">&#8592;</button>
                <div id="cookieDisplay">
                    <template v-if="bestProduct">
                        <RouterLink :to="`/products/${bestProduct.slug}`" style="display:block;">
                            <img :src="imageSrc(bestProduct)" :alt="bestProduct.name" />
                        </RouterLink>
                        <div style="max-width:520px;">
                            <div
                                style="font-family:'Fredoka',cursive;font-size:2rem;font-weight:700;margin-bottom:4px;letter-spacing:1px;"
                            >
                                {{ bestProduct.name }}
                            </div>
                            <div style="font-family:'Fredoka',cursive;font-size:1rem;opacity:0.9;line-height:1.45;">
                                {{ bestProduct.description || 'A best seller cookie you will love.' }}
                            </div>
                            <div style="font-family:'Kalam',cursive;font-size:1.2rem;color:var(--gold);margin-top:10px;font-weight:800;">
                                PHP {{ Number(bestProduct.price).toFixed(2) }}
                            </div>
                        </div>
                    </template>
                    <div v-else style="font-family:'Fredoka',cursive;color:var(--choco);">No best sellers available.</div>
                </div>
                <button class="carousel-arrow" type="button" @click="nextCookie">&#8594;</button>
            </div>
            <div style="text-align:center;margin-top:18px;">
                <span style="background:#7dbeff;border-radius:12px;padding:4px 18px;font-family:'Fredoka',cursive;font-size:1.1rem;color:#8B4513;box-shadow:0 2px 8px #d4a57433;">
                    <b>BEST SELLERS</b>
                </span>
            </div>
        </section>

        <section v-if="customer.user" style="max-width:1200px;margin:26px auto 0 auto;padding:0 24px;">
            <div class="product-card" style="align-items:flex-start;padding:22px 22px;">
                <div style="font-family:'Cookie',cursive;font-size:2.1rem;color:var(--gold);">Welcome back, {{ customer.user.name }}!</div>
                <div style="font-family:'Fredoka',cursive;font-size:1rem;opacity:0.9;margin-top:6px;">
                    Your jar is waiting.
                </div>
                <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
                    <RouterLink to="/jar" class="add-to-jar-btn" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">
                        View My Jar 🫙
                    </RouterLink>
                </div>
            </div>
        </section>

        <section style="max-width:1200px;margin:20px auto 0 auto;padding:0 24px;">
            <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
                <button
                    type="button"
                    class="filter-btn"
                    :style="filterBtnStyle('all')"
                    @click="setFilter('all')"
                >
                    All Cookies
                </button>
                <button
                    type="button"
                    class="filter-btn"
                    :style="filterBtnStyle('new')"
                    @click="setFilter('new')"
                >
                    New Arrivals
                </button>
                <button
                    type="button"
                    class="filter-btn"
                    :style="filterBtnStyle('best')"
                    @click="setFilter('best')"
                >
                    Best Sellers
                </button>
                <button
                    type="button"
                    class="filter-btn"
                    :style="filterBtnStyle('combo')"
                    @click="setFilter('combo')"
                >
                    Combo Deals
                </button>
            </div>
        </section>

        <div style="max-width:1200px;margin:22px auto 0 auto;padding:0 24px;">
            <input
                v-model="query"
                type="text"
                placeholder="Search cookies..."
                id="product_search"
                name="search"
                autocomplete="off"
                style="width:100%;max-width:520px;border-radius:14px;border:2px dashed rgba(212,165,116,0.35);padding:12px 14px;font-family:'Fredoka',cursive;background:#fff;box-shadow:0 8px 18px rgba(0,0,0,0.06);"
            />
        </div>

        <div v-if="error" class="product-grid" style="grid-template-columns: 1fr;">
            <div class="product-card" style="align-items:flex-start;">
                <div class="product-title">Oops</div>
                <div style="font-family:'Fredoka',cursive; font-size:1rem;">{{ error }}</div>
            </div>
        </div>

        <div v-else-if="loading" class="product-grid" style="grid-template-columns: 1fr;">
            <div class="product-card" style="align-items:flex-start;">
                <div class="product-title">Loading...</div>
                <div style="font-family:'Fredoka',cursive; font-size:1rem;">Fetching cookies...</div>
            </div>
        </div>

        <div v-else-if="products.length === 0" class="product-grid" style="grid-template-columns: 1fr;">
            <div class="product-card" style="align-items:flex-start;">
                <div class="product-title">No Cookies</div>
                <div style="font-family:'Fredoka',cursive; font-size:1rem;">No products found.</div>
            </div>
        </div>

        <section class="product-grid">
            <div v-for="p in products" :key="p.id" class="product-card">
                <div v-if="p.sticker" class="sticker">{{ p.sticker }}</div>
                <RouterLink :to="`/products/${p.slug}`" style="display:flex;flex-direction:column;align-items:center;text-decoration:none;color:inherit;">
                    <img :src="imageSrc(p)" :alt="p.name" class="product-img" />
                    <div class="product-title">{{ p.name }}</div>
                </RouterLink>
                <div class="product-price">PHP {{ Number(p.price).toFixed(2) }}</div>
                <button
                    v-if="canUseJar"
                    class="add-to-jar-btn"
                    :disabled="cart.loading || p.stock <= 0"
                    @click="add(p)"
                >
                    {{ p.stock <= 0 ? 'Out of Stock' : 'Add to Jar' }}
                </button>
                <button
                    v-else
                    class="add-to-jar-btn"
                    style="opacity:0.75;filter:grayscale(20%);"
                    type="button"
                    @click="promptLogin"
                >
                    Sign in to start your jar
                </button>
            </div>
        </section>
    </section>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useCartStore } from '../stores/cart';
import { useCustomerAuthStore } from '../stores/customerAuth';

const cart = useCartStore();
const customer = useCustomerAuthStore();
const query = ref('');
const selectedCategory = ref('all');

const bestProducts = ref([]);
const carouselIndex = ref(0);
const carouselPaused = ref(false);

let carouselTimer = null;

const loading = ref(false);
const error = ref(null);
const products = ref([]);

const baseUrl = (import.meta.env.VITE_BASE_URL || '/').replace(/\/?$/, '/');

const canUseJar = computed(() => !!customer.token);

let lastRequestId = 0;
let searchTimer = null;

async function fetchProducts() {
    const requestId = ++lastRequestId;
    loading.value = true;
    error.value = null;

    try {
        const { data } = await axios.get('products', {
            params: {
                search: query.value || undefined,
                category: selectedCategory.value !== 'all' ? selectedCategory.value : undefined,
                per_page: 30,
            },
        });

        if (requestId !== lastRequestId) return;
        const list = data?.data;
        if (!Array.isArray(list)) {
            throw new Error('Invalid API response');
        }
        products.value = list;
    } catch (e) {
        if (requestId !== lastRequestId) return;
        error.value = e?.response?.data?.message || e?.message || 'Failed to load products.';
    } finally {
        if (requestId !== lastRequestId) return;
        loading.value = false;
    }
}

async function fetchBestSellers() {
    try {
        const { data } = await axios.get('products', {
            params: {
                category: 'best',
                per_page: 50,
            },
        });

        const list = data?.data;
        if (Array.isArray(list)) {
            bestProducts.value = list;
            if (carouselIndex.value >= list.length) {
                carouselIndex.value = 0;
            }
        } else {
            bestProducts.value = [];
        }
    } catch {
        bestProducts.value = [];
    }
}

const bestProduct = computed(() => {
    if (!bestProducts.value.length) return null;
    const idx = ((carouselIndex.value % bestProducts.value.length) + bestProducts.value.length) % bestProducts.value.length;
    return bestProducts.value[idx] || null;
});

onMounted(async () => {
    await fetchBestSellers();
    await fetchProducts();

    carouselTimer = setInterval(() => {
        if (carouselPaused.value) return;
        nextCookie();
    }, 3500);
});

onUnmounted(() => {
    if (carouselTimer) {
        clearInterval(carouselTimer);
        carouselTimer = null;
    }
});

watch(query, () => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(async () => {
        await fetchProducts();
    }, 250);
});

watch(selectedCategory, async () => {
    await fetchProducts();
});

function prevCookie() {
    if (!bestProducts.value.length) return;
    carouselIndex.value = carouselIndex.value - 1;
}

function nextCookie() {
    if (!bestProducts.value.length) return;
    carouselIndex.value = carouselIndex.value + 1;
}

function pauseCarousel() {
    carouselPaused.value = true;
}

function resumeCarousel() {
    carouselPaused.value = false;
}

function clearSearch() {
    query.value = '';
}

function setFilter(slug) {
    selectedCategory.value = slug;
}

function filterBtnStyle(slug) {
    const active = selectedCategory.value === slug;
    if (active) {
        return 'padding:10px 20px;background:linear-gradient(90deg,#ffd28a,#ffadad);color:#fff;border:none;border-radius:12px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.1);transition:all 0.3s ease;';
    }
    return 'padding:10px 20px;background:#fff;color:#8B4513;border:2px solid #ffd28a;border-radius:12px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(0,0,0,0.06);transition:all 0.3s ease;';
}

async function add(p) {
    await cart.addItem(p.id, 1);
}

function promptLogin() {
    window.dispatchEvent(new Event('kukija:open-login'));
}

function imageSrc(p) {
    if (p?.image_path) return `${baseUrl}${encodeURI(p.image_path)}`;
    return `${baseUrl}logo.png`;
}
</script>
