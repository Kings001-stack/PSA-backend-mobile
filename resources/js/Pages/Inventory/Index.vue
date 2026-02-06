<template>
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                 <h2 class="text-xl font-bold text-gray-900">Inventory Management</h2>
                 <p class="text-sm text-gray-500">Track stock levels and expiry dates.</p>
            </div>
            <button
                @click="showCreateModal = true"
                class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover shadow-sm transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Stock
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Medication</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch Info</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Level</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in inventory.data" :key="item.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">{{ item.medication?.name || 'Unknown' }}</span>
                                    <span class="text-xs text-gray-500">{{ item.medication?.strength }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-900">#{{ item.batch_number || 'N/A' }}</span>
                                    <span class="text-xs text-gray-500" v-if="item.expiry_date">Exp: {{ new Date(item.expiry_date).toLocaleDateString() }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                     <span class="text-sm font-semibold" :class="item.quantity < (item.reorder_level || 10) ? 'text-red-600' : 'text-gray-900'">
                                        {{ item.quantity }}
                                    </span>
                                    <span class="text-xs text-gray-400">units</span>
                                </div>
                            </td>
                             <td class="px-6 py-4">
                                <span v-if="item.quantity < (item.reorder_level || 10)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                    Low Stock
                                </span>
                                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    In Stock
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <button @click="edit(item)" class="text-sm font-medium text-primary hover:text-primary-hover">Edit</button>
                                <button @click="deleteItem(item)" class="text-sm font-medium text-red-600 hover:text-red-700">Remove</button>
                            </td>
                        </tr>
                        <tr v-if="inventory.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    <p class="text-base font-medium text-gray-900">No inventory items found</p>
                                    <p class="text-sm text-gray-500 mt-1">Add stock to start tracking inventory.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
             </div>
        </div>

        <!-- Pagination -->
        <div v-if="inventory.links.length > 3" class="mt-6 flex justify-center">
             <div class="flex space-x-1 bg-white p-1 rounded-lg shadow-sm border border-gray-200">
                <Component 
                    :is="link.url ? 'Link' : 'span'"
                    v-for="(link, i) in inventory.links" 
                    :key="i"
                    :href="link.url"
                    v-html="link.label"
                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors"
                    :class="{ 'bg-primary text-white': link.active, 'text-gray-500 hover:bg-gray-50': !link.active && link.url, 'text-gray-300': !link.url }"
                />
             </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                     <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    {{ form.id ? 'Edit Stock' : 'Add Stock' }}
                                </h3>
                                <div class="mt-6 space-y-4">
                                     <div>
                                        <label class="block text-sm font-medium text-gray-700">Medication <span class="text-red-500">*</span></label>
                                        <select v-model="form.medication_id" 
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:ring focus:ring-opacity-50"
                                            :class="form.errors.medication_id ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-primary focus:ring-primary'"
                                            :disabled="!!form.id">
                                            <option value="">Select Medication</option>
                                            <option v-for="med in medications" :key="med.id" :value="med.id">
                                                {{ med.name }} ({{ med.strength }})
                                            </option>
                                        </select>
                                        <p v-if="form.errors.medication_id" class="mt-1 text-sm text-red-600">{{ form.errors.medication_id }}</p>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Quantity <span class="text-red-500">*</span></label>
                                            <input v-model="form.quantity" type="number" min="0"
                                                class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:ring focus:ring-opacity-50"
                                                :class="form.errors.quantity ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-primary focus:ring-primary'"
                                                placeholder="0">
                                            <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">{{ form.errors.quantity }}</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Reorder Level</label>
                                            <input v-model="form.reorder_level" type="number" min="0"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 sm:text-sm"
                                                placeholder="10">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Batch Number</label>
                                        <input v-model="form.batch_number" type="text" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 sm:text-sm"
                                            placeholder="e.g., BATCH-2024-001">
                                    </div>

                                    <div>
                                         <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
                                        <input v-model="form.expiry_date" type="date" 
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:ring focus:ring-opacity-50"
                                            :class="form.errors.expiry_date ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-primary focus:ring-primary'">
                                         <p v-if="form.errors.expiry_date" class="mt-1 text-sm text-red-600">{{ form.errors.expiry_date }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="submit" :disabled="form.processing" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm transition-colors disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : 'Save' }}
                        </button>
                        <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "@/ziggy";
import { ref } from 'vue';

const route = (name, params, absolute, config = Ziggy) => ziggyRoute(name, params, absolute, config);

const props = defineProps({
    inventory: Object,
    medications: Array,
});

const showCreateModal = ref(false);
const form = useForm({
    id: null,
    medication_id: '',
    quantity: '',
    batch_number: '',
    expiry_date: '',
    reorder_level: 10,
});

const submit = () => {
    if (form.id) {
        form.put(route('inventory.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('inventory.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const edit = (item) => {
    form.id = item.id;
    form.medication_id = item.medication_id;
    form.quantity = item.quantity;
    form.batch_number = item.batch_number;
    // Format date for date input (YYYY-MM-DD)
    if (item.expiry_date) {
        form.expiry_date = new Date(item.expiry_date).toISOString().split('T')[0];
    }
    form.reorder_level = item.reorder_level;
    showCreateModal.value = true;
};

const deleteItem = (item) => {
    if (confirm('Are you sure you want to remove this inventory item?')) {
        form.delete(route('inventory.destroy', item.id));
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    form.reset();
    form.clearErrors();
};
</script>
