<template>
    <div>
        <header class="header" :class="{ 'is-logged-in': !!customer.token }">
            <div class="header-content">
                <RouterLink to="/" class="logo" style="display:block;">
                    <img :src="logoUrl" alt="Kukija Logo" class="logo logo-img" style="display:block;" />
                </RouterLink>

                <div class="header-actions">
                    <RouterLink to="/about" class="header-btn about-btn" title="About Us">About Us</RouterLink>
                    <RouterLink to="/" class="header-btn subscribe-btn secondary" title="Subscribe">Subscribe</RouterLink>

                    <template v-if="customer.user">
                        <button class="header-btn" type="button" :disabled="customer.loading" @click="logoutCustomer">
                            Logout
                        </button>
                    </template>
                    <template v-else>
                        <button class="header-btn" type="button" @click="openLogin">Log in</button>
                        <button class="header-btn secondary" type="button" @click="openSignup">Sign up</button>
                    </template>

                    <RouterLink v-if="customer.token" to="/jar" class="jar-btn" title="View Jar">
                        <span style="font-size:1.5rem;">🫙</span>
                        <span class="jar-label">Jar</span>
                    </RouterLink>
                </div>
            </div>
        </header>

        <main class="main-content">
            <RouterView />
        </main>

        <div id="kukijaLoginModal" class="modal-bg" @click.self="closeLogin">
            <div class="modal">
                <button class="close-modal" type="button" @click="closeLogin">×</button>
                <h2>Log in</h2>
                <form id="kukijaLoginForm">
                    <input id="login_email" name="email" v-model="loginEmail" type="email" placeholder="Email" autocomplete="email" required />
                    <div class="kukija-field-error-wrap"><div class="kukija-field-error error-msg" data-error-for="email"></div></div>
                    <div class="kukija-password-wrap">
                        <input id="login_password" name="password" v-model="loginPassword" type="password" placeholder="Password" autocomplete="current-password" required />
                        <button class="kukija-eye" type="button" data-toggle-password="#login_password" aria-label="Toggle password visibility">👁</button>
                    </div>
                    <div class="kukija-field-error-wrap"><div class="kukija-field-error error-msg" data-error-for="password"></div></div>
                    <button class="add-to-jar-btn" type="submit" style="width:100%;">
                        Log in
                    </button>
                    <div class="kukija-form-error-wrap"><div class="kukija-form-error error-msg"></div></div>
                </form>
                <div style="margin-top:12px;text-align:center;font-family:'Fredoka',cursive;">
                    No account?
                    <button class="header-btn" type="button" style="padding:6px 10px;margin-left:8px;" @click="switchToSignup">
                        Sign up
                    </button>
                </div>
            </div>
        </div>

        <div id="kukijaSignupModal" class="modal-bg" @click.self="closeSignup">
            <div class="modal">
                <button class="close-modal" type="button" @click="closeSignup">×</button>
                <h2>Sign up</h2>
                <form id="kukijaSignupForm">
                    <input id="signup_first_name" name="first_name" v-model="signupFirstName" type="text" placeholder="First name" autocomplete="given-name" required />
                    <div class="kukija-field-error-wrap"><div class="kukija-field-error error-msg" data-error-for="first_name"></div></div>
                    <input id="signup_last_name" name="last_name" v-model="signupLastName" type="text" placeholder="Last name" autocomplete="family-name" required />
                    <div class="kukija-field-error-wrap"><div class="kukija-field-error error-msg" data-error-for="last_name"></div></div>
                    <input id="signup_email" name="email" v-model="signupEmail" type="email" placeholder="Email" autocomplete="email" required />
                    <div class="kukija-field-error-wrap"><div class="kukija-field-error error-msg" data-error-for="email"></div></div>
                    <input id="signup_address" name="address" v-model="signupAddress" type="text" placeholder="Address" autocomplete="street-address" required />
                    <div class="kukija-field-error-wrap"><div class="kukija-field-error error-msg" data-error-for="address"></div></div>
                    <input id="signup_phone" name="contact_no" v-model="signupPhone" type="tel" placeholder="Phone Number (09XXXXXXXXX or +639XXXXXXXXX)" autocomplete="tel" required />
                    <div class="kukija-field-error-wrap"><div class="kukija-field-error error-msg" data-error-for="contact_no"></div></div>
                    <div class="kukija-password-wrap">
                        <input id="signup_password" name="password" v-model="signupPassword" type="password" placeholder="Password" autocomplete="new-password" required />
                        <button class="kukija-eye" type="button" data-toggle-password="#signup_password" aria-label="Toggle password visibility">👁</button>
                    </div>
                    <div class="kukija-field-error-wrap"><div class="kukija-field-error error-msg" data-error-for="password"></div></div>
                    <div class="kukija-password-wrap">
                        <input id="signup_password_confirmation" name="password_confirmation" v-model="signupPasswordConfirm" type="password" placeholder="Retype password" autocomplete="new-password" required />
                        <button class="kukija-eye" type="button" data-toggle-password="#signup_password_confirmation" aria-label="Toggle password visibility">👁</button>
                    </div>
                    <div class="kukija-field-error-wrap"><div class="kukija-field-error error-msg" data-error-for="password_confirmation"></div></div>
                    <button class="add-to-jar-btn" type="submit" style="width:100%;">
                        Create account
                    </button>
                    <div class="kukija-form-error-wrap"><div class="kukija-form-error error-msg"></div></div>
                </form>
                <div style="margin-top:12px;text-align:center;font-family:'Fredoka',cursive;">
                    Already have an account?
                    <button class="header-btn" type="button" style="padding:6px 10px;margin-left:8px;" @click="switchToLogin">
                        Log in
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCustomerAuthStore } from './stores/customerAuth';
import { useCartStore } from './stores/cart';

const baseUrl = (import.meta.env.VITE_BASE_URL || '/').replace(/\/?$/, '/');
const logoUrl = `${baseUrl}logo.png`;

const customer = useCustomerAuthStore();
const cart = useCartStore();
const route = useRoute();
const router = useRouter();

const openLoginHandler = () => openLogin();
const openSignupHandler = () => openSignup();
const jarBumpHandler = () => {
    const $ = window.jQuery || window.$;
    if (!$) return;
    const $btn = $('.jar-btn').first();
    if (!$btn.length) return;
    $btn.removeClass('jar-bump');
    requestAnimationFrame(() => {
        $btn.addClass('jar-bump');
        setTimeout(() => {
            $btn.removeClass('jar-bump');
        }, 450);
    });
};
const authUpdatedHandler = (event) => {
    const data = event?.detail;
    if (data?.token) {
        customer.setToken(data.token);
    }
    if (data?.user) {
        customer.setUser(data.user);
    }
};

const loginEmail = ref('');
const loginPassword = ref('');

const signupFirstName = ref('');
const signupLastName = ref('');
const signupEmail = ref('');
const signupAddress = ref('');
const signupPhone = ref('');
const signupPassword = ref('');
const signupPasswordConfirm = ref('');

onMounted(async () => {
    window.addEventListener('kukija:open-login', openLoginHandler);
    window.addEventListener('kukija:open-signup', openSignupHandler);
    window.addEventListener('kukija:auth-updated', authUpdatedHandler);
    window.addEventListener('kukija:jar-bump', jarBumpHandler);

    if (customer.token) {
        try {
            await customer.fetchMe();
        } catch {
        }
    }

    if (route.query?.auth === 'login') {
        openLogin();
        const { auth, reason, ...rest } = route.query;
        router.replace({ query: rest });
    }

    if (route.query?.auth === 'signup') {
        openSignup();
        const { auth, reason, ...rest } = route.query;
        router.replace({ query: rest });
    }
});

onUnmounted(() => {
    window.removeEventListener('kukija:open-login', openLoginHandler);
    window.removeEventListener('kukija:open-signup', openSignupHandler);
    window.removeEventListener('kukija:auth-updated', authUpdatedHandler);
    window.removeEventListener('kukija:jar-bump', jarBumpHandler);
});

function openLogin() {
    const $ = window.jQuery || window.$;
    if ($) {
        $('#kukijaLoginForm .kukija-field-error, #kukijaLoginForm .kukija-form-error').hide().text('');
        $('#kukijaSignupModal').removeClass('active');
        $('#kukijaLoginModal').addClass('active');
    }
}

function closeLogin() {
    const $ = window.jQuery || window.$;
    if ($) {
        $('#kukijaLoginModal').removeClass('active');
    }
}

function openSignup() {
    const $ = window.jQuery || window.$;
    if ($) {
        $('#kukijaSignupForm .kukija-field-error, #kukijaSignupForm .kukija-form-error').hide().text('');
        $('#kukijaLoginModal').removeClass('active');
        $('#kukijaSignupModal').addClass('active');
    }
}

function closeSignup() {
    const $ = window.jQuery || window.$;
    if ($) {
        $('#kukijaSignupModal').removeClass('active');
    }
}

function switchToSignup() {
    closeLogin();
    openSignup();
}

function switchToLogin() {
    closeSignup();
    openLogin();
}

async function logoutCustomer() {
    await customer.logout();
    cart.clear();
}
</script>
