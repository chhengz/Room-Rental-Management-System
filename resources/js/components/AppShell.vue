<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { CurrentUser } from '../lib/api';

const props = defineProps<{ user?: CurrentUser | null; title: string }>();
const mobileOpen = ref(false);
const initials = computed(() => props.user?.name.split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase() ?? 'RM');

const logout = async () => {
    await fetch('/api/v1/logout', { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json' } });
    router.visit('/login');
};

const navigation = [
    { label: 'Overview', href: '/dashboard', icon: '▦' },
    { label: 'Buildings', href: '/buildings', icon: '⌂' },
    { label: 'Room types', href: '/room-types', icon: '▥' },
    { label: 'Rooms', href: '/rooms', icon: '□' },
    { label: 'Tenants', href: '/tenants', icon: '♙' },
    { label: 'Contracts', href: '/contracts', icon: '▤' },
    { label: 'Invoices', href: '/invoices', icon: '◫' },
    { label: 'Payments', href: '/payments', icon: '$' },
    { label: 'Utilities', href: '/utility-meters', icon: '⌁' },
    { label: 'Meter readings', href: '/meter-readings', icon: '◒' },
    { label: 'Expenses', href: '/expenses', icon: '↗' },
    { label: 'Settings', href: '/settings', icon: '⚙' },
];
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800">
        <aside :class="['fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-slate-200 bg-white px-4 py-6 transition-transform lg:translate-x-0', mobileOpen ? 'translate-x-0' : '-translate-x-full']">
            <div class="mb-8 flex items-center gap-3 px-3">
                <div class="grid size-10 place-items-center rounded-xl bg-teal-700 text-xl font-bold text-white">R</div>
                <div><strong class="block text-lg tracking-tight text-slate-800">Rentora</strong><span class="text-[9px] font-bold tracking-[0.18em] text-slate-400">PROPERTY OS</span></div>
            </div>
            <p class="mb-3 px-3 text-[10px] font-bold tracking-widest text-slate-400">WORKSPACE</p>
            <nav class="space-y-1 overflow-y-auto">
                <Link v-for="item in navigation" :key="item.href" :href="item.href" :class="[$page.url.startsWith(item.href) ? 'bg-teal-50 font-semibold text-teal-800' : 'text-slate-500 hover:bg-slate-50 hover:text-teal-700', 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition']" @click="mobileOpen = false">
                    <span class="w-5 text-center text-base">{{ item.icon }}</span>{{ item.label }}
                </Link>
            </nav>
            <div class="mt-auto rounded-xl bg-slate-50 p-3 text-xs text-slate-500"><strong class="block text-slate-700">Need help?</strong><span>Visit the help center</span></div>
        </aside>
        <div v-if="mobileOpen" class="fixed inset-0 z-30 bg-slate-900/30 lg:hidden" @click="mobileOpen = false" />
        <main class="lg:pl-64">
            <header class="sticky top-0 z-20 flex h-16 items-center justify-between border-b border-slate-200 bg-white/95 px-5 backdrop-blur sm:px-8">
                <button class="rounded-lg p-2 text-slate-500 lg:hidden" @click="mobileOpen = true">☰</button>
                <div class="hidden text-sm text-slate-400 sm:block">Workspace <span class="px-2 text-slate-300">/</span><strong class="text-slate-700">{{ title }}</strong></div>
                <div class="ml-auto flex items-center gap-3">
                    <div class="grid size-8 place-items-center rounded-full bg-teal-100 text-xs font-bold text-teal-700">{{ initials }}</div>
                    <div class="hidden text-left sm:block"><strong class="block text-xs text-slate-700">{{ user?.name }}</strong><span class="text-[10px] text-slate-400">{{ user?.roles?.[0]?.label ?? 'Staff' }}</span></div>
                    <button class="text-xs text-slate-400 hover:text-red-600" @click="logout">Sign out</button>
                </div>
            </header>
            <slot />
        </main>
    </div>
</template>
