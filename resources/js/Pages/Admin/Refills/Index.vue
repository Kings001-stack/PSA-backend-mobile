<template>
    <AdminLayout page-title="Refill Requests">
        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Refill Requests</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Track and process prescription refills for patients.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="secondary" @click="router.visit(route('admin.refills.index'))">
                        Refresh
                    </Button>
                </div>
            </div>

            <!-- Filters Panel -->
            <Card padding="normal">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                    <div class="sm:col-span-2">
                        <select
                            v-model="statusFilter"
                            @change="handleFilterChange"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm py-2 px-3 bg-white"
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending Review</option>
                            <option value="approved">Approved</option>
                            <option value="ready_for_pickup">Ready for Pickup</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <Button
                            variant="ghost"
                            full-width
                            @click="clearFilters"
                            v-if="statusFilter"
                        >
                            Clear Filters
                        </Button>
                    </div>
                </div>
            </Card>

            <!-- Refills Table -->
            <Card padding="none">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/50">
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Patient</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Medication</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Requested On</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="refill in refills.data" :key="refill.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-900 text-sm">{{ refill.user?.name || 'Unknown Patient' }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ refill.user?.email || 'N/A' }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">{{ refill.medication?.name || 'Unknown Medication' }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5" v-if="refill.medication?.dosage">
                                            {{ refill.medication.dosage }} • {{ refill.medication.form }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(refill.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :variant="getStatusVariant(refill.status)">
                                        {{ formatStatus(refill.status) }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Pending Actions -->
                                        <template v-if="refill.status === 'pending'">
                                            <Button variant="success" size="sm" @click="processRefill(refill, 'approve')">
                                                Approve
                                            </Button>
                                            <Button variant="danger" size="sm" @click="processRefill(refill, 'reject')">
                                                Reject
                                            </Button>
                                        </template>

                                        <!-- Approved Actions -->
                                        <template v-else-if="refill.status === 'approved'">
                                            <Button variant="primary" size="sm" @click="processRefill(refill, 'ready')">
                                                Mark Ready
                                            </Button>
                                        </template>

                                        <!-- Ready for Pickup Actions -->
                                        <template v-else-if="refill.status === 'ready_for_pickup'">
                                            <Button variant="success" size="sm" @click="processRefill(refill, 'complete')">
                                                Complete Pickup
                                            </Button>
                                        </template>

                                        <!-- Completed / Rejected Actions -->
                                        <span v-else class="text-xs text-gray-400 font-medium italic">
                                            Processed
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <EmptyState
                        v-if="refills.data.length === 0"
                        title="No refill requests found"
                        description="There are currently no prescription refill requests in this category."
                    />
                </div>

                <!-- Pagination -->
                <div v-if="refills.data.length > 0" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Showing {{ refills.from }} to {{ refills.to }} of {{ refills.total }} results
                    </div>
                    <div class="flex items-center gap-1.5">
                        <Button 
                            variant="ghost" 
                            size="sm" 
                            :disabled="!refills.prev_page_url" 
                            @click="changePage(refills.current_page - 1)"
                        >
                            Previous
                        </Button>
                        
                        <div class="flex items-center gap-1">
                            <span 
                                v-for="page in pageNumbers" 
                                :key="page"
                                class="px-3 py-1 rounded text-xs font-semibold cursor-pointer select-none transition-colors"
                                :class="page === refills.current_page ? 'bg-primary text-white' : page === '...' ? 'text-gray-400 cursor-default' : 'text-gray-600 hover:bg-gray-100'"
                                @click="page !== '...' && changePage(page)"
                            >
                                {{ page }}
                            </span>
                        </div>

                        <Button 
                            variant="ghost" 
                            size="sm" 
                            :disabled="!refills.next_page_url" 
                            @click="changePage(refills.current_page + 1)"
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
    refills: Object,
    filters: Object,
});

const statusFilter = ref(props.filters.status || '');
const { pageNumbers } = usePagination(computed(() => props.refills));

const handleFilterChange = () => {
    router.get(route('admin.refills.index'), {
        status: statusFilter.value,
    }, { preserveState: true, preserveScroll: true });
};

const clearFilters = () => {
    statusFilter.value = '';
    router.get(route('admin.refills.index'));
};

const changePage = (page) => {
    router.get(route('admin.refills.index'), {
        page,
        status: statusFilter.value,
    }, { preserveState: true, preserveScroll: true });
};

const processRefill = (refill, action) => {
    let confirmMsg = '';
    let routeName = '';

    if (action === 'approve') {
        confirmMsg = 'Are you sure you want to approve this refill request?';
        routeName = 'admin.refills.approve';
    } else if (action === 'reject') {
        confirmMsg = 'Are you sure you want to reject this refill request?';
        routeName = 'admin.refills.reject';
    } else if (action === 'ready') {
        confirmMsg = 'Mark this refill as ready for pickup?';
        routeName = 'admin.refills.ready';
    } else if (action === 'complete') {
        confirmMsg = 'Complete this refill pickup?';
        routeName = 'admin.refills.complete';
    }

    if (confirm(confirmMsg)) {
        router.put(route(routeName, refill.id), {}, {
            preserveScroll: true,
        });
    }
};

const getStatusVariant = (status) => {
    switch (status) {
        case 'pending': return 'warning';
        case 'approved': return 'primary';
        case 'ready_for_pickup': return 'info';
        case 'completed': return 'success';
        case 'rejected': return 'danger';
        default: return 'secondary';
    }
};

const formatStatus = (status) => {
    switch (status) {
        case 'pending': return 'Pending';
        case 'approved': return 'Approved';
        case 'ready_for_pickup': return 'Ready for Pickup';
        case 'completed': return 'Completed';
        case 'rejected': return 'Rejected';
        default: return status;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>
