<template>
    <AdminLayout page-title="Inventory Alerts">
        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Inventory Alerts</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Review stock shortages, expiration alerts, and items requiring attention.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="secondary" @click="router.visit(route('admin.alerts.index'))">
                        Refresh
                    </Button>
                </div>
            </div>

            <!-- Alerts List -->
            <Card padding="none">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/50">
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Alert Details</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Medication Name</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock level</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="alert in alerts.data" :key="alert.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">#ALT-{{ alert.id }}</p>
                                        <p class="text-xs text-red-500 font-medium mt-1">
                                            {{ alert.alert_message || 'Stock level dropped below recommended threshold.' }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold">
                                    {{ alert.inventory?.medication?.name || 'Unknown Medication' }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="text-red-600 font-bold">
                                        {{ alert.inventory?.quantity || 0 }}
                                    </span>
                                    <span class="text-xs text-gray-400"> / {{ alert.inventory?.reorder_level || 10 }} units</span>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge variant="danger">Low Stock</Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <Button variant="success" size="sm" @click="resolveAlert(alert)">
                                            Resolve
                                        </Button>
                                        <Button variant="ghost" size="sm" @click="router.visit(route('admin.inventory.index'))">
                                            Restock
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <EmptyState
                        v-if="alerts.data.length === 0"
                        title="All Alerts Resolved"
                        description="Excellent! There are no unresolved inventory alerts."
                    />
                </div>

                <!-- Pagination -->
                <div v-if="alerts.data.length > 0" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Showing {{ alerts.from }} to {{ alerts.to }} of {{ alerts.total }} results
                    </div>
                    <div class="flex items-center gap-1.5">
                        <Button 
                            variant="ghost" 
                            size="sm" 
                            :disabled="!alerts.prev_page_url" 
                            @click="changePage(alerts.current_page - 1)"
                        >
                            Previous
                        </Button>
                        
                        <div class="flex items-center gap-1">
                            <span 
                                v-for="page in pageNumbers" 
                                :key="page"
                                class="px-3 py-1 rounded text-xs font-semibold cursor-pointer select-none transition-colors"
                                :class="page === alerts.current_page ? 'bg-primary text-white' : page === '...' ? 'text-gray-400 cursor-default' : 'text-gray-600 hover:bg-gray-100'"
                                @click="page !== '...' && changePage(page)"
                            >
                                {{ page }}
                            </span>
                        </div>

                        <Button 
                            variant="ghost" 
                            size="sm" 
                            :disabled="!alerts.next_page_url" 
                            @click="changePage(alerts.current_page + 1)"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route as ziggyRoute } from 'ziggy-js';
import { Ziggy } from '@/ziggy';
import { usePagination } from '@/Composables/usePagination';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

const route = (name, params, absolute, config = Ziggy) => ziggyRoute(name, params, absolute, config);

const props = defineProps({
    alerts: Object,
});

const { pageNumbers } = usePagination(computed(() => props.alerts));

const changePage = (page) => {
    router.get(route('admin.alerts.index'), { page }, { preserveState: true, preserveScroll: true });
};

const resolveAlert = (alert) => {
    if (confirm('Mark this stock alert as resolved?')) {
        router.put(route('admin.alerts.resolve', alert.id), {}, {
            preserveScroll: true,
        });
    }
};
</script>
