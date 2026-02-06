<template>
    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Page Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
                    <p class="text-sm text-gray-500 mt-1">Welcome back, here's your pharmacy at a glance.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">Last updated: Just now</span>
                    <button class="p-2 text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </button>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Stat Card 1 - Total Medications -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                        </div>
                        <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Catalog</span>
                    </div>
                    <div class="mt-auto">
                        <p class="text-sm font-medium text-gray-500">Total Medications</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalMedications }}</h3>
                    </div>
                </div>

                <!-- Stat Card 2 - Total Inventory -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                         <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        </div>
                         <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">Stock</span>
                    </div>
                     <div class="mt-auto">
                        <p class="text-sm font-medium text-gray-500">Total Inventory</p>
                         <h3 class="text-3xl font-bold text-gray-900 mt-1">{{ stats.totalInventory }}</h3>
                    </div>
                </div>

                <!-- Stat Card 3 - Low Stock Alerts -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center text-red-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </div>
                         <span v-if="stats.lowStockAlerts > 0" class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">Alert</span>
                         <span v-else class="text-xs font-medium text-gray-500 bg-gray-50 px-2 py-1 rounded-full">OK</span>
                    </div>
                    <div class="mt-auto">
                        <p class="text-sm font-medium text-gray-500">Low Stock Alerts</p>
                         <h3 class="text-3xl font-bold" :class="stats.lowStockAlerts > 0 ? 'text-red-600' : 'text-gray-900'">{{ stats.lowStockAlerts }}</h3>
                    </div>
                </div>

                <!-- Stat Card 4 - Expiring Soon -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
                     <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-yellow-50 rounded-lg flex items-center justify-center text-yellow-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                         <span v-if="stats.expiringSoon > 0" class="text-xs font-medium text-yellow-600 bg-yellow-50 px-2 py-1 rounded-full">Warning</span>
                         <span v-else class="text-xs font-medium text-gray-500 bg-gray-50 px-2 py-1 rounded-full">OK</span>
                    </div>
                    <div class="mt-auto">
                        <p class="text-sm font-medium text-gray-500">Expiring Soon</p>
                         <h3 class="text-3xl font-bold" :class="stats.expiringSoon > 0 ? 'text-yellow-600' : 'text-gray-900'">{{ stats.expiringSoon }}</h3>
                    </div>
                </div>
            </div>

            <!-- Alerts Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-white">
                    <h2 class="text-lg font-bold text-gray-900">Inventory Alerts</h2>
                    <Link :href="route('inventory.index')" class="text-sm font-medium text-primary hover:text-primary-hover">View All Inventory</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50">
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Medication</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Level</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Expiry</th>
                                <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in alertItems" :key="item.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-medium text-gray-900 text-sm">{{ item.medication_name }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ item.batch_number || 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold" :class="item.is_low_stock ? 'text-red-600' : 'text-gray-900'">
                                            {{ item.quantity }}
                                        </span>
                                        <span class="text-xs text-gray-400">/ {{ item.reorder_level }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm" :class="item.is_expiring ? 'text-yellow-600 font-medium' : 'text-gray-500'">
                                    {{ item.expiry_date ? formatDate(item.expiry_date) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span v-if="item.is_low_stock" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                        Low Stock
                                    </span>
                                    <span v-else-if="item.is_expiring" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700 border border-yellow-200">
                                        Expiring
                                    </span>
                                    <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                        OK
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="alertItems.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-green-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <p class="text-base font-medium text-gray-900">All Clear!</p>
                                        <p class="text-sm text-gray-500 mt-1">No low stock or expiring items at the moment.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link } from '@inertiajs/vue3';
import { route } from "ziggy-js";
import { Ziggy } from "@/ziggy";

const props = defineProps({
    user: Object,
    tenant: Object,
    stats: {
        type: Object,
        default: () => ({
            totalMedications: 0,
            totalInventory: 0,
            lowStockAlerts: 0,
            expiringSoon: 0,
        }),
    },
    alertItems: {
        type: Array,
        default: () => [],
    },
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

