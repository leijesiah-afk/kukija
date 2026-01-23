<template>
    <div>
        <header class="header" :class="{ 'is-logged-in': !!customer.token }">
            <div class="header-content">
                <RouterLink to="/" class="logo" style="display:block;">
                    <img :src="logoUrl" alt="Kukija Logo" class="logo logo-img" style="display:block;" />
                </RouterLink>

                <div class="header-actions">
                    <RouterLink to="/" class="header-btn about-btn" title="About Us">About Us</RouterLink>
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

        <div class="modal-bg" :class="{ active: showLogin }" @click.self="closeLogin">
            <div class="modal">
                <button class="close-modal" type="button" @click="closeLogin">×</button>
                <h2>Log in</h2>
                <form @submit.prevent="submitLogin">
                    <input id="login_email" name="email" v-model="loginEmail" type="email" placeholder="Email" autocomplete="email" required />
                    <input id="login_password" name="password" v-model="loginPassword" type="password" placeholder="Password" autocomplete="current-password" required />
                    <button class="add-to-jar-btn" type="submit" style="width:100%;">
                        Log in
                    </button>
                    <div v-if="customer.lastError" style="margin-top:12px;" class="error-msg">
                        {{ customer.lastError }}
                    </div>
                </form>
                <div style="margin-top:12px;text-align:center;font-family:'Fredoka',cursive;">
                    No account?
                    <button class="header-btn" type="button" style="padding:6px 10px;margin-left:8px;" @click="switchToSignup">
                        Sign up
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-bg" :class="{ active: showSignup }" @click.self="closeSignup">
            <div class="modal">
                <button class="close-modal" type="button" @click="closeSignup">×</button>
                <h2>Sign up</h2>
                <form @submit.prevent="submitSignup">
                    <input id="signup_first_name" name="first_name" v-model="signupFirstName" type="text" placeholder="First name" autocomplete="given-name" required />
                    <input id="signup_last_name" name="last_name" v-model="signupLastName" type="text" placeholder="Last name" autocomplete="family-name" required />
                    <input id="signup_email" name="email" v-model="signupEmail" type="email" placeholder="Email" autocomplete="email" required />
                    <input id="signup_password" name="password" v-model="signupPassword" type="password" placeholder="Password" autocomplete="new-password" required />
                    <button class="add-to-jar-btn" type="submit" style="width:100%;">
                        Create account
                    </button>
                    <div v-if="customer.lastError" style="margin-top:12px;" class="error-msg">
                        {{ customer.lastError }}
                    </div>
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

const showLogin = ref(false);
const showSignup = ref(false);

const loginEmail = ref('');
const loginPassword = ref('');

const signupFirstName = ref('');
const signupLastName = ref('');
const signupEmail = ref('');
const signupPassword = ref('');

onMounted(async () => {
    window.addEventListener('kukija:open-login', openLoginHandler);

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
});

onUnmounted(() => {
    window.removeEventListener('kukija:open-login', openLoginHandler);
});

function openLogin() {
    showSignup.value = false;
    showLogin.value = true;
}

function closeLogin() {
    showLogin.value = false;
}

function openSignup() {
    showLogin.value = false;
    showSignup.value = true;
}

function closeSignup() {
    showSignup.value = false;
}

function switchToSignup() {
    closeLogin();
    openSignup();
}

function switchToLogin() {
    closeSignup();
    openLogin();
}

async function submitLogin() {
    await customer.login({
        email: loginEmail.value,
        password: loginPassword.value,
    });
    closeLogin();
}

async function submitSignup() {
    const first = (signupFirstName.value || '').trim();
    const last = (signupLastName.value || '').trim();
    const full = `${first} ${last}`.trim();
    await customer.register({
        firstName: first,
        lastName: last,
        name: full,
        email: signupEmail.value,
        password: signupPassword.value,
    });
    closeSignup();
}

async function logoutCustomer() {
    await customer.logout();
    cart.clear();
}
</script>
