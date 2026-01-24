<template>
    <section style="max-width:520px;margin:52px auto 0 auto;padding:0 24px 72px 24px;">
        <div style="display:flex;justify-content:center;">
            <img :src="logoUrl" alt="Kukija" class="logo logo-img admin-logo-img" style="display:block;" />
        </div>

        <div class="product-card" style="margin-top:28px;align-items:stretch;padding:26px 22px;">
            <div class="product-title" style="text-align:center;font-size:1.45rem;">Admin Login</div>
            <div style="text-align:center;font-family:'Fredoka',cursive;opacity:0.9;margin-top:-2px;">Sign in to manage inventory and transactions.</div>

            <form style="margin-top:18px;" @submit.prevent="submit">
                <label class="legacy-label" for="admin_email">Email</label>
                <input id="admin_email" name="email" autocomplete="email" v-model="email" type="email" required class="legacy-input" style="margin-top:8px;margin-bottom:12px;" />

                <label class="legacy-label" for="admin_password">Password</label>
                <input id="admin_password" name="password" autocomplete="current-password" v-model="password" type="password" required class="legacy-input" style="margin-top:8px;margin-bottom:14px;" />

                <button class="add-to-jar-btn" type="submit" style="width:100%;margin-top:6px;" :disabled="auth.loading">
                    Sign in
                </button>

                <div v-if="auth.lastError" style="margin-top:12px;" class="error-msg">
                    {{ auth.lastError }}
                </div>
            </form>

            <div v-if="isDev" style="margin-top:14px;text-align:center;font-family:'Kalam',cursive;opacity:0.85;">
                Default admin: <b>admin@kukija.test</b> / <b>password</b>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const auth = useAuthStore();

const baseUrl = import.meta.env.VITE_BASE_URL || '/';
const normalizedBase = baseUrl.endsWith('/') ? baseUrl : `${baseUrl}/`;
const logoUrl = `${normalizedBase}logo.png`;

const isDev = import.meta.env.DEV;

const email = ref(isDev ? 'admin@kukija.test' : '');
const password = ref(isDev ? 'password' : '');

async function submit() {
    await auth.login({ email: email.value, password: password.value });
    await router.replace('/admin');
}
</script>
