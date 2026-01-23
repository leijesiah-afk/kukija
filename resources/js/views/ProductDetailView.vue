<template>
    <section style="max-width:1200px;margin:40px auto 72px auto;padding:0 24px;">
        <div v-if="error" class="product-card" style="align-items:flex-start;">
            <div class="product-title">Oops</div>
            <div style="font-family:'Fredoka',cursive; font-size:1rem;">{{ error }}</div>
        </div>

        <div v-else-if="loading" class="product-card" style="align-items:flex-start;">
            <div class="product-title">Loading...</div>
            <div style="font-family:'Fredoka',cursive; font-size:1rem;">Fetching cookie details...</div>
        </div>

        <div v-else-if="product" class="product-card" style="padding:32px 28px;">
            <div v-if="product.sticker" class="sticker">{{ product.sticker }}</div>

            <div style="display:grid;grid-template-columns: 220px 1fr;gap:28px;align-items:center;width:100%;">
                <div style="display:flex;justify-content:center;">
                    <img :src="imageSrc(product)" :alt="product.name" class="product-img" style="width:200px;height:200px;margin-bottom:0;" />
                </div>

                <div style="width:100%;">
                    <div class="product-title" style="text-align:left;font-size:1.8rem;">{{ product.name }}</div>
                    <div class="product-price" style="font-size:1.2rem;">PHP {{ Number(product.price).toFixed(2) }}</div>
                    <div v-if="product.description" style="margin-top:10px;font-family:'Fredoka',cursive;line-height:1.5;opacity:0.9;">
                        {{ product.description }}
                    </div>

                    <div style="margin-top:18px;display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
                        <button
                            v-if="canUseJar"
                            class="add-to-jar-btn"
                            style="padding:12px 30px;"
                            :disabled="cart.loading || product.stock <= 0"
                            @click="addToJar"
                        >
                            {{ product.stock <= 0 ? 'Out of Stock' : 'Add to Jar' }}
                        </button>
                        <button
                            v-else
                            class="add-to-jar-btn"
                            style="padding:12px 30px;opacity:0.75;filter:grayscale(20%);"
                            type="button"
                            @click="promptLogin"
                        >
                            Sign in to start your jar
                        </button>

                        <RouterLink to="/" class="header-btn" style="text-decoration:none;">Back</RouterLink>
                    </div>

                    <div style="margin-top:14px;font-family:'Fredoka',cursive;font-size:0.95rem;opacity:0.85;">
                        Stock: <b>{{ product.stock }}</b>
                    </div>

                    <div v-if="added" style="margin-top:14px;background:#e9ffe9;border:2px solid rgba(139,69,19,0.12);padding:10px 12px;border-radius:14px;font-family:'Fredoka',cursive;">
                        Added to Jar.
                        <RouterLink to="/jar" style="margin-left:8px; text-decoration:underline;">View Jar</RouterLink>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useCartStore } from '../stores/cart';
import { useCustomerAuthStore } from '../stores/customerAuth';

const route = useRoute();
const cart = useCartStore();
const customer = useCustomerAuthStore();

const loading = ref(false);
const error = ref(null);
const product = ref(null);
const added = ref(false);

const baseUrl = (import.meta.env.VITE_BASE_URL || '/').replace(/\/?$/, '/');

const canUseJar = computed(() => !!customer.token);

async function fetchProduct() {
    const slug = route.params.slug;
    if (!slug) return;

    loading.value = true;
    error.value = null;
    product.value = null;
    added.value = false;

    try {
        const { data } = await axios.get(`products/${slug}`);
        product.value = data?.data ?? null;
    } catch (e) {
        error.value = e?.response?.data?.message || 'Failed to load product.';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchProduct);
watch(() => route.params.slug, fetchProduct);

function imageSrc(p) {
    if (p?.image_path) return `${baseUrl}${encodeURI(p.image_path)}`;
    return `${baseUrl}logo.png`;
}

async function addToJar() {
    if (!product.value) return;
    await cart.addItem(product.value.id, 1);
    added.value = true;
}

function promptLogin() {
    window.dispatchEvent(new Event('kukija:open-login'));
}
</script>
