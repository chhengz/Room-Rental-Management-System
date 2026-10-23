<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { api } from '../lib/api';
const form = reactive({ email: 'admin@rental.local', password: 'password' });
const loading = ref(false); const error = ref('');
const submit = async () => { loading.value = true; error.value = ''; try { await api.login(form.email, form.password); router.visit('/dashboard'); } catch (e) { error.value = e instanceof Error ? e.message : 'Unable to sign in.'; } finally { loading.value = false; } };
</script>
<template>
    <Head title="Sign in" />
    <main class="grid min-h-screen lg:grid-cols-2">
        <section class="hidden flex-col justify-between bg-teal-800 p-12 text-white lg:flex"><div class="flex items-center gap-3"><div class="grid size-10 place-items-center rounded-xl bg-white text-xl font-bold text-teal-800">R</div><strong class="text-xl">Rentora</strong></div><div><p class="max-w-lg text-5xl font-bold leading-tight tracking-tight">A calmer way to manage your rental business.</p><p class="mt-6 max-w-md text-teal-100">Rooms, tenants, contracts, billing and utilities in one connected workspace.</p></div><p class="text-xs text-teal-200">Rentora Property OS · Local development</p></section>
        <section class="flex items-center justify-center bg-slate-50 p-6"><form class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl shadow-slate-200/50 sm:p-10" @submit.prevent="submit"><div class="mb-8 lg:hidden"><div class="grid size-10 place-items-center rounded-xl bg-teal-700 text-xl font-bold text-white">R</div></div><p class="text-xs font-bold uppercase tracking-widest text-teal-700">SECURE ACCESS</p><h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-800">Welcome back</h1><p class="mt-2 text-sm text-slate-500">Sign in to manage your rental operations.</p><label class="mt-8 block text-sm font-medium text-slate-600">Email<input v-model="form.email" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-3 outline-none focus:border-teal-600" type="email" required /></label><label class="mt-5 block text-sm font-medium text-slate-600">Password<input v-model="form.password" class="mt-2 w-full rounded-lg border border-slate-200 px-3 py-3 outline-none focus:border-teal-600" type="password" required /></label><p v-if="error" class="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ error }}</p><button class="mt-6 w-full rounded-lg bg-teal-700 py-3 font-semibold text-white hover:bg-teal-800 disabled:opacity-50" :disabled="loading">{{ loading ? 'Signing in...' : 'Sign in' }}</button><p class="mt-5 text-center text-xs text-slate-400">Demo: admin@rental.local / password</p></form></section>
    </main>
</template>
