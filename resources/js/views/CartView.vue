<template>
    <section style="max-width:1200px;margin:48px auto 72px auto;padding:0 24px;">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <div style="font-family:'Cookie',cursive;font-size:2.2rem;color:var(--gold);">Your Jar</div>
                <div style="font-family:'Fredoka',cursive;font-size:1rem;opacity:0.9;">Review your cookies before checkout.</div>
            </div>
            <div style="font-family:'Kalam',cursive;font-size:1.1rem;color:var(--choco);background:#ffffffb5;padding:8px 14px;border-radius:14px;box-shadow:0 8px 18px rgba(0,0,0,0.06);">
                {{ cart.count }} items
            </div>
        </div>

        <div v-if="cart.lastError" class="product-grid" style="grid-template-columns: 1fr; margin-top:22px;">
            <div class="product-card" style="align-items:flex-start;">
                <div class="product-title">Oops</div>
                <div style="font-family:'Fredoka',cursive; font-size:1rem;">{{ cart.lastError }}</div>
                <button class="header-btn" type="button" style="margin-top:14px;" @click="promptLogin">Log in</button>
            </div>
        </div>

        <div v-else-if="cart.loading" class="product-grid" style="grid-template-columns: 1fr; margin-top:22px;">
            <div class="product-card" style="align-items:flex-start;">
                <div class="product-title">Loading...</div>
                <div style="font-family:'Fredoka',cursive; font-size:1rem;">Fetching your jar...</div>
            </div>
        </div>

        <div v-else-if="cart.items.length === 0" class="product-grid" style="grid-template-columns: 1fr; margin-top:22px;">
            <div class="product-card" style="align-items:flex-start;">
                <div class="product-title">Empty Jar</div>
                <div style="font-family:'Fredoka',cursive; font-size:1rem;">Add cookies from the shop.</div>
                <RouterLink to="/" class="header-btn" style="margin-top:14px;text-decoration:none;">Shop Cookies</RouterLink>
            </div>
        </div>

        <div v-else style="margin-top:22px;display:grid;grid-template-columns: 1fr;gap:16px;">
            <div v-for="item in cart.items" :key="item.id" class="product-card" style="flex-direction:row;align-items:center;justify-content:space-between;gap:18px;padding:18px 18px;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <img :src="imageSrc(item.product)" :alt="item.product?.name ?? 'Product'" style="width:72px;height:72px;border-radius:50%;box-shadow:0 10px 22px rgba(212,165,116,0.16);background:#fff;object-fit:contain;" />
                    <div>
                        <div style="font-family:'Fredoka',cursive;font-weight:800;font-size:1.05rem;">{{ item.product?.name ?? 'Product' }}</div>
                        <div style="font-family:'Kalam',cursive;color:var(--gold);">PHP {{ Number(item.unit_price).toFixed(2) }}</div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end;">
                    <input
                        style="width:88px;border-radius:14px;border:2px dashed rgba(212,165,116,0.35);padding:10px 12px;font-family:'Fredoka',cursive;background:#fff;"
                        type="number"
                        min="1"
                        :id="`jar_qty_${item.id}`"
                        :name="`quantity_${item.id}`"
                        :value="item.quantity"
                        @change="onQtyChange(item, $event)"
                    />
                    <div style="font-family:'Fredoka',cursive;font-weight:800;min-width:120px;text-align:right;">
                        PHP {{ Number(item.line_total).toFixed(2) }}
                    </div>
                    <button type="button" @click="cart.removeItem(item.id)" class="header-btn" style="padding:8px 12px;">Remove</button>
                </div>
            </div>

            <div class="product-card" style="flex-direction:row;align-items:center;justify-content:space-between;padding:18px 18px;">
                <div style="font-family:'Fredoka',cursive;font-weight:800;font-size:1.05rem;">Total</div>
                <div style="font-family:'Kalam',cursive;color:var(--gold);font-weight:800;font-size:1.2rem;">PHP {{ cart.total.toFixed(2) }}</div>
            </div>

            <div style="display:flex;justify-content:flex-end;">
                <RouterLink to="/checkout" class="add-to-jar-btn" style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;">
                    Proceed to Checkout
                </RouterLink>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onMounted } from 'vue';
import { useCartStore } from '../stores/cart';

const cart = useCartStore();

const baseUrl = (import.meta.env.VITE_BASE_URL || '/').replace(/\/?$/, '/');

onMounted(async () => {
    await cart.fetch();
});

async function onQtyChange(item, event) {
    const value = Number(event?.target?.value);
    if (!Number.isFinite(value) || value < 1) return;
    await cart.updateItem(item.id, value);
}

function imageSrc(p) {
    if (p?.image_path) return `${baseUrl}${encodeURI(p.image_path)}`;
    return `${baseUrl}logo.png`;
}

function promptLogin() {
    window.dispatchEvent(new Event('kukija:open-login'));
}
</script>
