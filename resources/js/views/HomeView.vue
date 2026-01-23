<template>
    <section class="mx-auto max-w-6xl px-4 py-10">
        <div class="rounded-2xl bg-white border p-8">
            <h1 class="text-2xl font-semibold tracking-tight">Freshly Baked Deals</h1>
            <p class="mt-2 text-zinc-600">Browse products, add to cart, and checkout with a dummy payment flow.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <RouterLink to="/products" class="px-4 py-2 rounded-lg bg-zinc-900 text-white text-sm">Shop Products</RouterLink>
                <RouterLink to="/admin" class="px-4 py-2 rounded-lg border text-sm">Go to Admin</RouterLink>
            </div>
        </div>
    </section>
</template>
