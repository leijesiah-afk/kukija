<template>
    <section style="max-width:1200px;margin:48px auto 72px auto;padding:0 24px;">
        <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;">
            <div>
                <div style="font-family:'Cookie',cursive;font-size:2.2rem;color:var(--gold);">Checkout</div>
                <div style="font-family:'Fredoka',cursive;font-size:1rem;opacity:0.9;">Complete checkout with a dummy payment.</div>
            </div>
            <RouterLink to="/jar" class="header-btn" style="text-decoration:none;">Back to Jar</RouterLink>
        </div>

        <div style="margin-top:22px;display:grid;gap:18px;grid-template-columns: 1fr;">
            <div class="product-card" style="align-items:flex-start;width:100%;padding:28px 22px;">
                <div class="product-title" style="text-align:left;">Delivery Details</div>

                <form id="kukijaCheckoutForm" style="width:100%;margin-top:14px;">
                    <div class="checkout-form-grid">
                        <div>
                            <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Full name</div>
                            <input id="checkout_name" name="name" autocomplete="name" v-model="name" required type="text" />
                            <div class="kukija-field-error error-msg" data-error-for="name" style="display:none;margin-top:10px;"></div>
                        </div>
                        <div>
                            <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Email</div>
                            <input id="checkout_email" name="email" autocomplete="email" v-model="email" required type="email" />
                            <div class="kukija-field-error error-msg" data-error-for="email" style="display:none;margin-top:10px;"></div>
                        </div>
                        <div class="checkout-form-full">
                            <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Address</div>
                            <input id="checkout_address" name="address" autocomplete="street-address" v-model="address" required type="text" />
                            <div class="kukija-field-error error-msg" data-error-for="address" style="display:none;margin-top:10px;"></div>
                        </div>
                        <div class="checkout-form-full">
                            <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Phone (optional)</div>
                            <input id="checkout_phone" name="phone" autocomplete="tel" v-model="phone" type="tel" />
                            <div class="kukija-field-error error-msg" data-error-for="phone" style="display:none;margin-top:10px;"></div>
                        </div>
                        <div class="checkout-form-full">
                            <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Payment Method</div>
                            <select id="checkout_payment_method" name="payment_method" v-model="paymentMethod" required>
                                <option value="">Select a payment method</option>
                                <option value="gcash">GCash</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                            <div class="kukija-field-error error-msg" data-error-for="payment_method" style="display:none;margin-top:10px;"></div>
                        </div>
                        <div v-if="paymentMethod && paymentMethod !== 'cash'" class="checkout-form-full">
                            <div style="font-family:'Kalam',cursive;font-size:1.05rem;">Reference / Account</div>
                            <input
                                id="checkout_payment_reference"
                                name="payment_reference"
                                v-model="paymentReference"
                                type="text"
                                :placeholder="paymentMethod === 'gcash' ? 'GCash reference number' : 'Bank name + reference'"
                            />
                            <div class="kukija-field-error error-msg" data-error-for="payment_reference" style="display:none;margin-top:10px;"></div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="add-to-jar-btn"
                        style="width:100%;margin-top:16px;padding:12px 30px;"
                        :disabled="cart.items.length === 0 || cart.loading"
                    >
                        Pay PHP {{ Number(cart.total).toFixed(2) }}
                    </button>

                    <div v-if="cart.lastError" style="margin-top:14px;background:#ffecec;border:2px solid rgba(139,69,19,0.12);padding:10px 12px;border-radius:14px;font-family:'Fredoka',cursive;">
                        {{ cart.lastError }}
                        <button class="header-btn" type="button" style="margin-top:12px;" @click="promptLogin">Log in</button>
                    </div>

                    <div v-if="paid" style="margin-top:14px;background:#e9ffe9;border:2px solid rgba(139,69,19,0.12);padding:10px 12px;border-radius:14px;font-family:'Fredoka',cursive;">
                        Payment successful (dummy). Order:
                        <b>{{ paid.order?.order_number }}</b>
                    </div>

                    <div class="kukija-form-error error-msg" style="display:none;margin-top:14px;"></div>
                </form>
            </div>

            <div class="product-card" style="align-items:flex-start;width:100%;padding:28px 22px;">
                <div class="product-title" style="text-align:left;">Summary</div>

                <div style="margin-top:14px;display:grid;gap:10px;width:100%;">
                    <div v-for="item in cart.items" :key="item.id" style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;">
                        <div>
                            <div style="font-family:'Fredoka',cursive;font-weight:800;">{{ item.product?.name ?? 'Product' }}</div>
                            <div style="font-family:'Kalam',cursive;opacity:0.85;">Qty {{ item.quantity }}</div>
                        </div>
                        <div style="font-family:'Fredoka',cursive;font-weight:800;">PHP {{ Number(item.line_total).toFixed(2) }}</div>
                    </div>
                </div>

                <div style="margin-top:14px;padding-top:14px;border-top:2px dashed rgba(212,165,116,0.25);display:flex;align-items:center;justify-content:space-between;width:100%;">
                    <div style="font-family:'Fredoka',cursive;font-weight:800;">Total</div>
                    <div style="font-family:'Kalam',cursive;color:var(--gold);font-weight:800;font-size:1.2rem;">PHP {{ Number(cart.total).toFixed(2) }}</div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal-bg" :class="{ active: showConfirm }" @click.self="closeConfirm">
        <div class="modal">
            <button class="close-modal" type="button" @click="closeConfirm">×</button>
            <h2>Confirm Payment</h2>
            <div style="text-align:center; font-family:'Fredoka',cursive; margin-bottom:12px;">
                Confirm your payment details.
            </div>
            <div style="text-align:center; font-family:'Fredoka',cursive; margin-bottom:10px;">
                Method:
                <b>{{ paymentMethod ? paymentMethod.replace('_', ' ').toUpperCase() : '—' }}</b>
            </div>
            <div v-if="paymentMethod && paymentMethod !== 'cash'" style="text-align:center; font-family:'Kalam',cursive; margin-bottom:10px; opacity:0.9;">
                Ref:
                <b>{{ paymentReference || '—' }}</b>
            </div>
            <div style="text-align:center; font-family:'Kalam',cursive; font-size:1.2rem; color:var(--gold); font-weight:800;">
                PHP {{ Number(cart.total).toFixed(2) }}
            </div>
            <div style="display:flex; gap:10px; margin-top:18px; justify-content:center; flex-wrap:wrap;">
                <button class="header-btn" type="button" @click="closeConfirm">Cancel</button>
                <button class="add-to-jar-btn" type="button" style="padding:10px 26px;" :disabled="cart.loading" @click="pay">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { useCartStore } from '../stores/cart';

const cart = useCartStore();

const name = ref('');
const email = ref('');
const address = ref('');
const phone = ref('');
const paymentMethod = ref('');
const paymentReference = ref('');
const paid = ref(null);
const showConfirm = ref(false);

const checkoutValidHandler = () => {
    openConfirm();
};

onMounted(async () => {
    await cart.fetch();
    window.addEventListener('kukija:checkout-valid', checkoutValidHandler);
});

onUnmounted(() => {
    window.removeEventListener('kukija:checkout-valid', checkoutValidHandler);
});

function openConfirm() {
    if (cart.items.length === 0 || cart.loading) return;
    showConfirm.value = true;
}

function closeConfirm() {
    showConfirm.value = false;
}

async function pay() {
    showConfirm.value = false;
    paid.value = await cart.checkout({
        name: name.value,
        email: email.value,
        address: address.value,
        phone: phone.value || null,
        payment_method: paymentMethod.value,
        payment_reference: paymentReference.value || null,
    });
}

function promptLogin() {
    window.dispatchEvent(new Event('kukija:open-login'));
}
</script>
