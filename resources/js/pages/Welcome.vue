<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, reactive, ref } from 'vue';
import {
    api,
    type ApiError,
    type CurrentUser,
    type DashboardData,
    type Paginated,
} from '../lib/api';

type Row = Record<string, unknown> & { id?: number };
type FormValue = string | number | boolean | null;
type Setting = { key: string; value: FormValue; type: string };
type ModuleKey =
    | 'dashboard' | 'buildings' | 'roomTypes' | 'rooms' | 'tenants'
    | 'contracts' | 'invoices' | 'payments' | 'utilityMeters'
    | 'meterReadings' | 'expenses' | 'settings';
type Field = { key: string; label: string; type?: string; required?: boolean; options?: string[] };

const modules: Record<Exclude<ModuleKey, 'dashboard' | 'settings'>, {
    title: string; description: string; columns: { key: string; label: string }[]; fields: Field[];
}> = {
    buildings: { title: 'Buildings', description: 'Manage properties and floors.', columns: [{ key: 'name', label: 'Name' }, { key: 'code', label: 'Code' }, { key: 'address', label: 'Address' }, { key: 'total_floors', label: 'Floors' }, { key: 'is_active', label: 'Status' }], fields: [{ key: 'name', label: 'Name', required: true }, { key: 'code', label: 'Code', required: true }, { key: 'address', label: 'Address' }, { key: 'total_floors', label: 'Total floors', type: 'number', required: true }, { key: 'is_active', label: 'Active', type: 'checkbox' }] },
    roomTypes: { title: 'Room types', description: 'Reusable room classifications.', columns: [{ key: 'name', label: 'Name' }, { key: 'default_capacity', label: 'Capacity' }, { key: 'description', label: 'Description' }], fields: [{ key: 'name', label: 'Name', required: true }, { key: 'default_capacity', label: 'Capacity', type: 'number', required: true }, { key: 'description', label: 'Description', type: 'textarea' }] },
    rooms: { title: 'Rooms', description: 'Track every rentable unit and its availability.', columns: [{ key: 'room_number', label: 'Room' }, { key: 'building.name', label: 'Building' }, { key: 'room_type.name', label: 'Type' }, { key: 'floor', label: 'Floor' }, { key: 'rental_rate', label: 'Rate' }, { key: 'status', label: 'Status' }], fields: [{ key: 'building_id', label: 'Building ID', type: 'number', required: true }, { key: 'room_type_id', label: 'Room type ID', type: 'number', required: true }, { key: 'room_number', label: 'Room number', required: true }, { key: 'floor', label: 'Floor', type: 'number', required: true }, { key: 'capacity', label: 'Capacity', type: 'number', required: true }, { key: 'rental_rate', label: 'Rental rate', type: 'number', required: true }, { key: 'status', label: 'Status', options: ['available', 'occupied', 'reserved', 'maintenance', 'inactive'], required: true }] },
    tenants: { title: 'Tenants', description: 'Tenant profiles, contact information and status.', columns: [{ key: 'full_name', label: 'Name' }, { key: 'phone', label: 'Phone' }, { key: 'email', label: 'Email' }, { key: 'status', label: 'Status' }], fields: [{ key: 'full_name', label: 'Full name', required: true }, { key: 'email', label: 'Email', type: 'email' }, { key: 'phone', label: 'Phone' }, { key: 'id_number', label: 'ID number' }, { key: 'status', label: 'Status', options: ['active', 'inactive', 'blacklisted'], required: true }, { key: 'notes', label: 'Notes', type: 'textarea' }] },
    contracts: { title: 'Rental contracts', description: 'Control occupancy and contract terms.', columns: [{ key: 'contract_number', label: 'Contract' }, { key: 'tenant.full_name', label: 'Tenant' }, { key: 'room.room_number', label: 'Room' }, { key: 'start_date', label: 'Start' }, { key: 'end_date', label: 'End' }, { key: 'status', label: 'Status' }], fields: [{ key: 'contract_number', label: 'Contract number', required: true }, { key: 'tenant_id', label: 'Tenant ID', type: 'number', required: true }, { key: 'room_id', label: 'Room ID', type: 'number', required: true }, { key: 'start_date', label: 'Start date', type: 'date', required: true }, { key: 'end_date', label: 'End date', type: 'date' }, { key: 'rent_amount', label: 'Rent amount', type: 'number', required: true }, { key: 'deposit_amount', label: 'Deposit', type: 'number', required: true }, { key: 'billing_frequency', label: 'Billing frequency', options: ['monthly', 'quarterly', 'yearly'], required: true }, { key: 'due_day', label: 'Due day', type: 'number', required: true }, { key: 'status', label: 'Status', options: ['upcoming', 'active', 'expired', 'renewed', 'terminated'], required: true }] },
    invoices: { title: 'Invoices', description: 'Issue rent and utility bills and monitor balances.', columns: [{ key: 'invoice_number', label: 'Invoice' }, { key: 'tenant.full_name', label: 'Tenant' }, { key: 'issue_date', label: 'Issued' }, { key: 'due_date', label: 'Due' }, { key: 'total_amount', label: 'Total' }, { key: 'balance_amount', label: 'Balance' }, { key: 'status', label: 'Status' }], fields: [{ key: 'invoice_number', label: 'Invoice number', required: true }, { key: 'tenant_id', label: 'Tenant ID', type: 'number', required: true }, { key: 'rental_contract_id', label: 'Contract ID', type: 'number' }, { key: 'issue_date', label: 'Issue date', type: 'date', required: true }, { key: 'due_date', label: 'Due date', type: 'date', required: true }, { key: 'period_start', label: 'Period start', type: 'date', required: true }, { key: 'period_end', label: 'Period end', type: 'date', required: true }, { key: 'status', label: 'Status', options: ['draft', 'issued', 'partially_paid', 'paid', 'overdue', 'void'], required: true }, { key: 'discount_total', label: 'Discount', type: 'number' }, { key: 'late_fee_total', label: 'Late fee', type: 'number' }] },
    payments: { title: 'Payments', description: 'Record collections and allocate them to invoices.', columns: [{ key: 'payment_number', label: 'Payment' }, { key: 'tenant.full_name', label: 'Tenant' }, { key: 'payment_date', label: 'Date' }, { key: 'amount', label: 'Amount' }, { key: 'method', label: 'Method' }, { key: 'status', label: 'Status' }], fields: [{ key: 'payment_number', label: 'Payment number', required: true }, { key: 'tenant_id', label: 'Tenant ID', type: 'number', required: true }, { key: 'payment_date', label: 'Payment date', type: 'date', required: true }, { key: 'amount', label: 'Amount', type: 'number', required: true }, { key: 'method', label: 'Method', options: ['cash', 'bank_transfer', 'card', 'other'], required: true }, { key: 'status', label: 'Status', options: ['recorded', 'reversed'], required: true }, { key: 'reference', label: 'Reference' }, { key: 'allocation_invoice_id', label: 'Invoice ID', type: 'number', required: true }, { key: 'allocation_amount', label: 'Allocated amount', type: 'number', required: true }] },
    utilityMeters: { title: 'Utility meters', description: 'Register electricity and water meters.', columns: [{ key: 'meter_code', label: 'Meter' }, { key: 'type', label: 'Type' }, { key: 'room.room_number', label: 'Room' }, { key: 'is_active', label: 'Status' }], fields: [{ key: 'room_id', label: 'Room ID', type: 'number', required: true }, { key: 'type', label: 'Type', options: ['electricity', 'water'], required: true }, { key: 'meter_code', label: 'Meter code', required: true }, { key: 'installed_at', label: 'Installed at', type: 'date' }, { key: 'is_active', label: 'Active', type: 'checkbox' }] },
    meterReadings: { title: 'Meter readings', description: 'Capture readings and calculate utility charges.', columns: [{ key: 'meter.meter_code', label: 'Meter' }, { key: 'reading_date', label: 'Date' }, { key: 'previous_reading', label: 'Previous' }, { key: 'current_reading', label: 'Current' }, { key: 'consumption', label: 'Consumption' }, { key: 'charge_amount', label: 'Charge' }], fields: [{ key: 'utility_meter_id', label: 'Meter ID', type: 'number', required: true }, { key: 'reading_date', label: 'Reading date', type: 'date', required: true }, { key: 'previous_reading', label: 'Previous reading', type: 'number', required: true }, { key: 'current_reading', label: 'Current reading', type: 'number', required: true }, { key: 'adjustment_reason', label: 'Adjustment reason', type: 'textarea' }] },
    expenses: { title: 'Expenses', description: 'Track operating expenses by property.', columns: [{ key: 'expense_date', label: 'Date' }, { key: 'category.name', label: 'Category' }, { key: 'building.name', label: 'Building' }, { key: 'description', label: 'Description' }, { key: 'amount', label: 'Amount' }], fields: [{ key: 'expense_category_id', label: 'Category ID', type: 'number', required: true }, { key: 'building_id', label: 'Building ID', type: 'number' }, { key: 'expense_date', label: 'Date', type: 'date', required: true }, { key: 'amount', label: 'Amount', type: 'number', required: true }, { key: 'description', label: 'Description', required: true }, { key: 'reference', label: 'Reference' }] },
};

const user = ref<CurrentUser | null>(null);
const dashboard = ref<DashboardData | null>(null);
const active = ref<ModuleKey>('dashboard');
const rows = ref<Row[]>([]);
const loading = ref(true);
const saving = ref(false);
const loginLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const search = ref('');
const page = ref(1);
const lastPage = ref(1);
const total = ref(0);
const modalOpen = ref(false);
const editing = ref<Row | null>(null);
const form = reactive<Record<string, any>>({});
const loginForm = reactive({ email: 'admin@rental.local', password: 'password' });
const settings = ref<Setting[]>([]);
const mobileMenuOpen = ref(false);

const resourceApi = (key: Exclude<ModuleKey, 'dashboard' | 'settings'>) => api[key];
const moduleConfig = computed(() => active.value !== 'dashboard' && active.value !== 'settings' ? modules[active.value] : null);
const title = computed(() => moduleConfig.value?.title ?? (active.value === 'settings' ? 'Settings' : 'Overview'));
const firstName = computed(() => user.value?.name.split(' ')[0] ?? 'there');
const initials = computed(() => user.value?.name.split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase() ?? 'RM');
const money = (value: unknown) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(Number(value ?? 0));
const displayValue = (row: Row, key: string) => {
    const value = key.split('.').reduce<unknown>((current, part) => (current as Row)?.[part], row);
    if (typeof value === 'boolean') return value ? 'Active' : 'Inactive';
    if (key.includes('amount') || key.includes('rate') || key === 'rental_rate') return money(value);
    return value === null || value === undefined || value === '' ? '—' : String(value);
};

const apiError = (error: unknown) => error instanceof Error ? error.message : 'Something went wrong.';
const clearMessages = () => { errorMessage.value = ''; successMessage.value = ''; };

async function loadDashboard() {
    const response = await api.dashboard();
    dashboard.value = response.data;
}

async function loadResource() {
    if (!moduleConfig.value || active.value === 'settings' || active.value === 'dashboard') return;
    loading.value = true; clearMessages();
    try {
        const response = await resourceApi(active.value).list({ page: page.value, search: search.value, per_page: 10 });
        const result = response.data as Paginated<Row>;
        rows.value = result.data; lastPage.value = result.last_page; total.value = result.total;
    } catch (error) { errorMessage.value = apiError(error); }
    finally { loading.value = false; }
}

async function loadSettings() {
    loading.value = true; clearMessages();
    try { settings.value = (await api.settings.list()).data as Setting[]; }
    catch (error) { errorMessage.value = apiError(error); }
    finally { loading.value = false; }
}

async function selectModule(key: ModuleKey) {
    active.value = key; mobileMenuOpen.value = false; page.value = 1; search.value = '';
    if (key === 'dashboard') { loading.value = true; try { await loadDashboard(); } catch (error) { errorMessage.value = apiError(error); } finally { loading.value = false; } }
    else if (key === 'settings') await loadSettings();
    else await loadResource();
}

function openCreate() {
    editing.value = null; Object.keys(form).forEach((key) => delete form[key]);
    moduleConfig.value?.fields.forEach((field) => { form[field.key] = field.type === 'checkbox' ? true : field.options?.[0] ?? ''; });
    modalOpen.value = true; clearMessages();
}
function openEdit(row: Row) {
    editing.value = row; Object.keys(form).forEach((key) => delete form[key]);
    moduleConfig.value?.fields.forEach((field) => { form[field.key] = row[field.key] ?? ''; });
    modalOpen.value = true; clearMessages();
}
function payload() {
    const value: Record<string, unknown> = { ...form };
    if (active.value === 'invoices') value.items = [{ category: 'rent', description: 'Rent and charges', quantity: 1, unit_price: Number(value.total_amount ?? 0) }];
    if (active.value === 'payments') { value.allocations = [{ invoice_id: Number(value.allocation_invoice_id), amount: Number(value.allocation_amount) }]; delete value.allocation_invoice_id; delete value.allocation_amount; }
    return value;
}
async function save() {
    if (!moduleConfig.value || active.value === 'dashboard' || active.value === 'settings') return;
    saving.value = true; clearMessages();
    try {
        if (editing.value?.id) await resourceApi(active.value).update(editing.value.id, payload());
        else await resourceApi(active.value).create(payload());
        modalOpen.value = false; successMessage.value = `${title.value} saved successfully.`; await loadResource();
    } catch (error) { errorMessage.value = apiError(error); }
    finally { saving.value = false; }
}
async function remove(row: Row) {
    if (!row.id || !window.confirm(`Delete this ${title.value.toLowerCase().slice(0, -1)}?`)) return;
    try { await resourceApi(active.value as Exclude<ModuleKey, 'dashboard' | 'settings'>).remove(row.id); successMessage.value = 'Record deleted.'; await loadResource(); }
    catch (error) { errorMessage.value = apiError(error); }
}
async function saveSettings() {
    saving.value = true;
    try { await api.settings.update(settings.value); successMessage.value = 'Settings saved.'; }
    catch (error) { errorMessage.value = apiError(error); }
    finally { saving.value = false; }
}
async function login() {
    loginLoading.value = true; clearMessages();
    try { user.value = (await api.login(loginForm.email, loginForm.password)).data; await loadDashboard(); }
    catch (error) { errorMessage.value = apiError(error); }
    finally { loginLoading.value = false; }
}
async function logout() { await api.logout(); user.value = null; dashboard.value = null; }

onMounted(async () => {
    try { user.value = (await api.me()).data; await loadDashboard(); }
    catch (error) { const apiErr = error as ApiError; if (apiErr.status !== 401) errorMessage.value = apiError(error); }
    finally { loading.value = false; }
});
</script>

<template>
    <Head :title="title" />
    <div class="app-shell">
        <aside class="sidebar" :class="{ 'sidebar-open': mobileMenuOpen }">
            <div class="brand"><div class="brand-mark">R</div><div><strong>Rentora</strong><span>PROPERTY OS</span></div></div>
            <div class="workspace-label">WORKSPACE</div>
            <nav class="nav-list">
                <button class="nav-item" :class="{ active: active === 'dashboard' }" @click="selectModule('dashboard')"><span class="nav-icon">▦</span> Overview</button>
                <button v-for="item in ([['buildings','Properties'],['rooms','Rooms'],['tenants','Tenants'],['contracts','Contracts'],['invoices','Billing'],['payments','Payments'],['utilityMeters','Utilities'],['meterReadings','Readings'],['expenses','Expenses']] as [ModuleKey,string][])"
                    :key="item[0]" class="nav-item" :class="{ active: active === item[0] }" @click="selectModule(item[0])"><span class="nav-icon">⌂</span> {{ item[1] }}</button>
            </nav>
            <div class="workspace-label settings-label">MANAGE</div>
            <nav class="nav-list"><button class="nav-item" :class="{ active: active === 'settings' }" @click="selectModule('settings')"><span class="nav-icon">⚙</span> Settings</button></nav>
            <div class="sidebar-footer"><div class="help-card"><span class="help-icon">?</span><div><strong>Need help?</strong><small>Visit the help center</small></div></div><small class="version">Rentora v1.0.0</small></div>
        </aside>
        <main class="main-content">
            <header class="topbar"><button class="menu-toggle" @click="mobileMenuOpen = !mobileMenuOpen">☰</button><div class="breadcrumb"><span>Workspace</span><b>/</b><strong>{{ title }}</strong></div><div v-if="user" class="user-menu"><div class="avatar">{{ initials }}</div><div class="user-copy"><strong>{{ user.name }}</strong><small>{{ user.roles?.[0]?.label ?? 'Staff' }}</small></div><button class="logout-button" @click="logout">Sign out</button></div></header>
            <section class="page-body">
                <div v-if="!user && !loading" class="login-panel"><div class="login-visual"><span>R</span><p>Everything your rental business needs, in one calm workspace.</p></div><form class="login-form" @submit.prevent="login"><p class="eyebrow">SECURE ACCESS</p><h2>Welcome back</h2><p class="subtitle">Sign in to manage your rental operations.</p><label>Email<input v-model="loginForm.email" type="email" required /></label><label>Password<input v-model="loginForm.password" type="password" required /></label><p v-if="errorMessage" class="form-error">{{ errorMessage }}</p><button class="primary-button full-button" :disabled="loginLoading">{{ loginLoading ? 'Signing in...' : 'Sign in to Rentora' }}</button><small class="demo-hint">Local demo: admin@rental.local / password</small></form></div>
                <template v-else>
                    <div class="page-heading"><div><p class="eyebrow">RENTAL OPERATIONS</p><h1>{{ active === 'dashboard' ? `Good morning, ${firstName}` : title }} <span class="wave">✦</span></h1><p class="subtitle">{{ moduleConfig?.description ?? 'A live view of your properties, finances and daily operations.' }}</p></div><button v-if="active !== 'dashboard' && active !== 'settings'" class="primary-button" @click="openCreate">＋ Add {{ title.slice(0, -1) }}</button></div>
                    <div v-if="errorMessage" class="alert">{{ errorMessage }}</div><div v-if="successMessage" class="success-alert">{{ successMessage }}</div>
                    <div v-if="active === 'dashboard'" class="metric-grid">
                        <article class="metric-card"><div class="metric-top"><span>Total rooms</span><span class="metric-icon blue">⌂</span></div><strong>{{ dashboard?.rooms.total ?? '—' }}</strong><small>{{ dashboard?.rooms.available ?? 0 }} available · {{ dashboard?.rooms.maintenance ?? 0 }} maintenance</small></article>
                        <article class="metric-card"><div class="metric-top"><span>Occupancy rate</span><span class="metric-icon green">◉</span></div><strong>{{ dashboard ? `${dashboard.rooms.occupancy_rate}%` : '—' }}</strong><small>{{ dashboard?.rooms.occupied ?? 0 }} occupied rooms</small></article>
                        <article class="metric-card"><div class="metric-top"><span>Collected this month</span><span class="metric-icon purple">$</span></div><strong>{{ money(dashboard?.finance.collected_total) }}</strong><small>{{ money(dashboard?.finance.invoices_total) }} invoiced</small></article>
                        <article class="metric-card"><div class="metric-top"><span>Outstanding balance</span><span class="metric-icon orange">!</span></div><strong>{{ money(dashboard?.finance.outstanding_total) }}</strong><small class="negative">{{ dashboard?.finance.overdue_invoice_count ?? 0 }} overdue invoices</small></article>
                    </div>
                    <div v-if="active === 'dashboard'" class="content-grid"><article class="panel"><div class="panel-heading"><div><h2>Portfolio occupancy</h2><p>Current room availability</p></div></div><div class="occupancy-content"><div class="donut" :style="{ '--occupancy': `${dashboard?.rooms.occupancy_rate ?? 0}%` }"><div><strong>{{ dashboard?.rooms.occupancy_rate ?? 0 }}%</strong><small>occupied</small></div></div><div class="legend"><div><i class="dot occupied" /> <span>Occupied</span><b>{{ dashboard?.rooms.occupied ?? 0 }}</b></div><div><i class="dot available" /> <span>Available</span><b>{{ dashboard?.rooms.available ?? 0 }}</b></div><div><i class="dot reserved" /> <span>Reserved</span><b>{{ dashboard?.rooms.reserved ?? 0 }}</b></div><div><i class="dot maintenance" /> <span>Maintenance</span><b>{{ dashboard?.rooms.maintenance ?? 0 }}</b></div></div></div></article><article class="panel"><div class="panel-heading"><div><h2>Financial performance</h2><p>Selected period summary</p></div></div><div class="finance-total"><strong>{{ money(dashboard?.finance.collected_total) }}</strong><small>collected revenue</small></div><div class="summary-list"><div><span>Invoices issued</span><b>{{ money(dashboard?.finance.invoices_total) }}</b></div><div><span>Operating expenses</span><b>{{ money(dashboard?.finance.expenses_total) }}</b></div><div><span>Utilities</span><b>{{ money(dashboard?.finance.utilities_total) }}</b></div><div><span>Net operating result</span><b class="positive">{{ money(dashboard?.finance.net_operating_result) }}</b></div></div></article></div>
                    <div v-if="active === 'dashboard'" class="quick-actions"><button @click="selectModule('tenants')">Manage tenants →</button><button @click="selectModule('invoices')">Create invoice →</button><button @click="selectModule('meterReadings')">Record meter reading →</button><button @click="selectModule('expenses')">Add expense →</button></div>
                    <section v-if="active !== 'dashboard' && active !== 'settings'" class="panel resource-panel"><div class="table-toolbar"><div><h2>{{ title }}</h2><p>{{ total }} records</p></div><input v-model="search" placeholder="Search records..." @keyup.enter="loadResource" /><button class="secondary-button" @click="loadResource">Search</button></div><div class="table-wrap"><table><thead><tr><th v-for="column in moduleConfig?.columns" :key="column.key">{{ column.label }}</th><th>Actions</th></tr></thead><tbody><tr v-if="loading"><td colspan="10" class="empty-cell">Loading records...</td></tr><tr v-else-if="rows.length === 0"><td colspan="10" class="empty-cell">No records found.</td></tr><tr v-for="row in rows" v-else :key="row.id"><td v-for="column in moduleConfig?.columns" :key="column.key"><span v-if="column.key === 'status'" class="status-pill">{{ displayValue(row, column.key) }}</span><span v-else>{{ displayValue(row, column.key) }}</span></td><td class="actions"><button @click="openEdit(row)">Edit</button><button class="danger-link" @click="remove(row)">Delete</button></td></tr></tbody></table></div><div class="pagination"><button :disabled="page <= 1" @click="page--; loadResource()">Previous</button><span>Page {{ page }} of {{ lastPage }}</span><button :disabled="page >= lastPage" @click="page++; loadResource()">Next</button></div></section>
                    <section v-if="active === 'settings'" class="panel settings-panel"><div class="panel-heading"><div><h2>Business settings</h2><p>Configure values used by billing and operations.</p></div><button class="primary-button" :disabled="saving" @click="saveSettings">Save settings</button></div><div v-if="loading" class="empty-cell">Loading settings...</div><div v-else class="settings-grid"><label v-for="setting in settings" :key="setting.key">{{ setting.key }}<input v-model="setting.value" :type="setting.type === 'number' ? 'number' : 'text'" /></label><p v-if="settings.length === 0" class="empty-cell">No settings have been configured yet.</p></div></section>
                </template>
            </section>
        </main>
        <div v-if="modalOpen" class="modal-backdrop" @click.self="modalOpen = false"><form class="record-modal" @submit.prevent="save"><div class="modal-heading"><div><p class="eyebrow">{{ editing ? 'EDIT RECORD' : 'NEW RECORD' }}</p><h2>{{ editing ? 'Edit' : 'Add' }} {{ title.slice(0, -1) }}</h2></div><button type="button" class="close-button" @click="modalOpen = false">×</button></div><div class="form-grid"><label v-for="field in moduleConfig?.fields" :key="field.key">{{ field.label }}<select v-if="field.options" v-model="form[field.key]" :required="field.required"><option v-for="option in field.options" :key="option" :value="option">{{ option }}</option></select><textarea v-else-if="field.type === 'textarea'" v-model="form[field.key]" :required="field.required" /><input v-else v-model="form[field.key]" :type="field.type === 'checkbox' ? 'checkbox' : field.type ?? 'text'" :required="field.required" /></label></div><p v-if="errorMessage" class="form-error">{{ errorMessage }}</p><div class="modal-actions"><button type="button" class="secondary-button" @click="modalOpen = false">Cancel</button><button class="primary-button" :disabled="saving">{{ saving ? 'Saving...' : 'Save record' }}</button></div></form></div>
    </div>
</template>
