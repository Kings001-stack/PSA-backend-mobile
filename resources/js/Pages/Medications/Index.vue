<template>
    <AuthenticatedLayout>
        <div class="flex justify-between items-center mb-6">
            <div>
                 <h2 class="text-xl font-bold text-gray-900">Medications</h2>
                 <p class="text-sm text-gray-500">Manage your pharmacy's drug catalog.</p>
            </div>
            <button
                @click="showCreateModal = true"
                class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-hover shadow-sm transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Add Medication
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/50">
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Details</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Generic Name</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="medication in medications.data" :key="medication.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-900">{{ medication.name }}</span>
                                    <span class="text-xs text-gray-500">{{ medication.strength }} • {{ medication.dosage_form }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ medication.generic_name || '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                ${{ medication.price }}
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="medication.requires_prescription" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                    Rx Required
                                </span>
                                <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    OTC
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <button @click="edit(medication)" class="text-sm font-medium text-primary hover:text-primary-hover">Edit</button>
                                <button @click="deleteMedication(medication)" class="text-sm font-medium text-red-600 hover:text-red-700">Delete</button>
                            </td>
                        </tr>
                         <tr v-if="medications.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                    <p class="text-base font-medium text-gray-900">No medications found</p>
                                    <p class="text-sm text-gray-500 mt-1">Get started by adding your first medication.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="medications.links.length > 3" class="mt-6 flex justify-center">
             <div class="flex space-x-1 bg-white p-1 rounded-lg shadow-sm border border-gray-200">
                <Component 
                    :is="link.url ? 'Link' : 'span'"
                    v-for="(link, i) in medications.links" 
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
                                    {{ form.id ? 'Edit Medication' : 'Add Medication' }}
                                </h3>
                                <div class="mt-6 space-y-4">
                                     <div>
                                        <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                                        <input v-model="form.name" type="text" 
                                            class="mt-1 block w-full rounded-md shadow-sm sm:text-sm focus:ring focus:ring-opacity-50"
                                            :class="form.errors.name ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-primary focus:ring-primary'"
                                            placeholder="Enter medication name">
                                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Generic Name</label>
                                        <input v-model="form.generic_name" type="text" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 sm:text-sm"
                                            placeholder="e.g., Acetaminophen">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Dosage Form</label>
                                            <select v-model="form.dosage_form" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 sm:text-sm">
                                                <option value="">Select form</option>
                                                <option value="Tablet">Tablet</option>
                                                <option value="Capsule">Capsule</option>
                                                <option value="Syrup">Syrup</option>
                                                <option value="Injection">Injection</option>
                                                <option value="Cream">Cream</option>
                                                <option value="Drops">Drops</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Strength</label>
                                            <input v-model="form.strength" type="text" 
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 sm:text-sm"
                                                placeholder="e.g., 500mg">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Price <span class="text-red-500">*</span></label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <input v-model="form.price" type="number" step="0.01" 
                                                    class="pl-7 block w-full rounded-md shadow-sm sm:text-sm focus:ring focus:ring-opacity-50"
                                                    :class="form.errors.price ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-primary focus:ring-primary'"
                                                    placeholder="0.00">
                                            </div>
                                            <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                                        </div>
                                        <div class="flex items-center h-full pt-6">
                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                <input v-model="form.requires_prescription" type="checkbox" class="rounded text-primary focus:ring-primary h-4 w-4 border-gray-300">
                                                <span class="text-sm text-gray-700">Rx Required</span>
                                            </label>
                                        </div>
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
    medications: Object,
});

const showCreateModal = ref(false);
const form = useForm({
    id: null,
    name: '',
    generic_name: '',
    dosage_form: '',
    strength: '',
    price: '',
    requires_prescription: false,
});

const submit = () => {
    if (form.id) {
        form.put(route('medications.update', form.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('medications.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const edit = (medication) => {
    form.id = medication.id;
    form.name = medication.name;
    form.price = medication.price;
    form.requires_prescription = !!medication.requires_prescription;
    // ... map other fields if we had them in the form
    showCreateModal.value = true;
};

const deleteMedication = (medication) => {
    if (confirm('Are you sure you want to delete this medication?')) {
        form.delete(route('medications.destroy', medication.id));
    }
};

const closeModal = () => {
    showCreateModal.value = false;
    form.reset();
    form.clearErrors();
};
</script>
