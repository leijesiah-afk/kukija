<template>
    <section class="legacy-about">
        <div class="hero">
            <div class="about-card">
                <h1>We bake smiles</h1>
                <p>At Kukija we believe cookies are tiny celebrations. We handcraft small-batch recipes, decorate with playful accents, and package each jar with a ribbon of joy. Our flavors are inspired by memory, comfort, and a dash of whimsy.</p>
                <p>We bake for parties, cozy evenings, and for the moments that deserve a sweet, cartoony twist. Follow us on social or visit the shop to add a cookie to your jar.</p>
                <p><a style="display:inline-block;margin-top:12px;padding:10px 14px;border-radius:12px;background:linear-gradient(90deg,#ffd28a,#ffadad);color:#fff;text-decoration:none;font-weight:800;" :href="visitShopHref">Visit Shop</a></p>
            </div>
            <div class="image-panel">
                <img :src="logoSrc" alt="Kukija logo" />
            </div>
        </div>

        <section class="team">
            <div class="member">
                <img :src="bakerSrc" alt="Baker" />
                <h3>Chef Miko</h3>
                <div>Head Baker & Flavor Architect</div>
            </div>
            <div class="member">
                <img :src="decoratorSrc" alt="Decorator" />
                <h3>Ao</h3>
                <div>Design & Decoration</div>
            </div>
            <div class="member">
                <img :src="logisticsSrc" alt="Logistics" />
                <h3>Rin</h3>
                <div>Packaging & Care</div>
            </div>
        </section>

        <footer>
            <small>© Kukija — handmade cookies, baked with joy.</small>
        </footer>

        <div id="toastContainer" aria-live="polite" aria-atomic="true"></div>
    </section>
</template>

<script setup>
import { onMounted } from 'vue';

const baseUrl = (import.meta.env.VITE_BASE_URL || '/').replace(/\/?$/, '/');

const logoSrc = `${baseUrl}logo.png`;
const bakerSrc = `${baseUrl}imgs/milky_alaska_cookie.png`;
const decoratorSrc = `${baseUrl}imgs/ring.png`;
const logisticsSrc = `${baseUrl}imgs/sharkie.png`;
const visitShopHref = `${baseUrl}`;

onMounted(() => {
    function showToast(msg, timeout) {
        timeout = timeout || 3000;
        var c = document.getElementById('toastContainer');
        if (!c) return;
        var t = document.createElement('div');
        t.className = 'toast';
        t.textContent = msg;
        c.appendChild(t);
        requestAnimationFrame(function () {
            t.classList.add('show');
        });
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () {
                t.remove();
            }, 220);
        }, timeout);
    }

    (function () {
        try {
            var u = JSON.parse(sessionStorage.getItem('kukijaUser') || 'null');
            if (u && (u.firstName || u.email)) showToast('Welcome back, ' + (u.firstName || u.email));
        } catch (e) {
        }
    })();
});
</script>

<style>
 .legacy-about{ --choco:#8B4513; --gold:#d4a574; --accent:#ffd28a; }
 .legacy-about *{ box-sizing:border-box }
 .legacy-about .hero{ max-width:1100px; margin:28px auto; display:flex; gap:24px; align-items:center; padding:22px; }
 .legacy-about .about-card{ background:linear-gradient(180deg,#fff8f1,#fffdf9); border-radius:18px; padding:22px; box-shadow:0 24px 60px rgba(0,0,0,0.06); flex:1; }
 .legacy-about .about-card h1{ font-family:'Cookie',cursive; color:var(--gold); font-size:2.4rem; margin-bottom:8px; }
 .legacy-about .about-card p{ line-height:1.6; }
 .legacy-about .image-panel{ width:360px; border-radius:18px; overflow:hidden; box-shadow:0 24px 60px rgba(0,0,0,0.08); }
 .legacy-about .image-panel img{ width:100%; display:block; }
 .legacy-about .team{ max-width:1100px; margin:20px auto; display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:18px; padding:0 22px 40px 22px; }
 .legacy-about .member{ background:#fff; border-radius:14px; padding:14px; box-shadow:0 12px 30px rgba(0,0,0,0.06); text-align:center; }
 .legacy-about .member img{ width:86px;height:86px;border-radius:50%; object-fit:cover; }
 .legacy-about footer{ text-align:center; padding:18px; color:#8B4513cc; }
 .legacy-about #toastContainer{ position:fixed; right:16px; bottom:20px; z-index:10000; display:flex; flex-direction:column; gap:8px; align-items:flex-end; }
 .legacy-about .toast{ background:#333; color:#fff; padding:10px 14px; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,0.2); opacity:0; transform:translateY(10px); transition:transform 220ms ease, opacity 220ms ease; }
 .legacy-about .toast.show{ opacity:1; transform:none; }
</style>
