<template>
    <AdminLayout page-title="Advertisements Management">
        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Advertisements</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Configure promotion banners and announcements displayed on terminals.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="secondary" @click="router.visit(route('admin.adverts.index'))">
                        Refresh
                    </Button>
                    <Button variant="primary" @click="openAddModal">
                        Create Advertisement
                    </Button>
                </div>
            </div>

            <!-- Advertisements Grid -->
            <Card padding="none">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/50">
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Preview</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider">Created At</th>
                                <th class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="advert in adverts.data" :key="advert.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="w-16 h-10 rounded-lg overflow-hidden bg-slate-100 border border-slate-200 shadow-inner flex items-center justify-center">
                                        <img 
                                            v-if="advert.image_path" 
                                            :src="'/storage/' + advert.image_path" 
                                            :alt="advert.title" 
                                            class="w-full h-full object-cover" 
                                        />
                                        <svg v-else class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-900 text-sm">
                                        {{ advert.title }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :variant="advert.is_active ? 'success' : 'secondary'">
                                        {{ advert.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    {{ formatDate(advert.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button 
                                            @click="toggleActive(advert)" 
                                            class="text-sm font-medium"
                                            :class="advert.is_active ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800'"
                                        >
                                            {{ advert.is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        <button @click="openEditModal(advert)" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                            Edit
                                        </button>
                                        <button @click="confirmDelete(advert)" class="text-sm font-medium text-red-600 hover:text-red-800 transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <EmptyState
                        v-if="adverts.data.length === 0"
                        title="No advertisements found"
                        description="Start creating advertisements to publish offers/updates on screen."
                    >
                        <template #action>
                            <Button variant="primary" @click="openAddModal">Create Advertisement</Button>
                        </template>
                    </EmptyState>
                </div>

                <!-- Pagination -->
                <div v-if="adverts.data.length > 0" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Showing {{ adverts.from }} to {{ adverts.to }} of {{ adverts.total }} results
                    </div>
                    <div class="flex items-center gap-1.5">
                        <Button 
                            variant="ghost" 
                            size="sm" 
                            :disabled="!adverts.prev_page_url" 
                            @click="changePage(adverts.current_page - 1)"
                        >
                            Previous
                        </Button>
                        
                        <div class="flex items-center gap-1">
                            <span 
                                v-for="page in pageNumbers" 
                                :key="page"
                                class="px-3 py-1 rounded text-xs font-semibold cursor-pointer select-none transition-colors"
                                :class="page === adverts.current_page ? 'bg-primary text-white' : page === '...' ? 'text-gray-400 cursor-default' : 'text-gray-600 hover:bg-gray-100'"
                                @click="page !== '...' && changePage(page)"
                            >
                                {{ page }}
                            </span>
                        </div>

                        <Button 
                            variant="ghost" 
                            size="sm" 
                            :disabled="!adverts.next_page_url" 
                            @click="changePage(adverts.current_page + 1)"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Add/Edit Modal -->
        <Modal :show="showModal" :title="editingItem ? 'Edit Advertisement' : 'Create Advertisement'" size="lg" @close="closeModal">
            <form @submit.prevent="submitForm" class="space-y-4 p-6">
                <Input v-model="form.title" label="Advertisement Title" required :error="form.errors.title" />
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Banner Image <span class="text-red-500" v-if="!editingItem">*</span>
                    </label>
                    <div class="mt-1 flex items-center gap-4">
                        <input 
                            type="file" 
                            ref="fileInput" 
                            @change="handleFileUpload" 
                            class="hidden" 
                            accept="image/*"
                        />
                        <Button variant="secondary" type="button" @click="$refs.fileInput.click()">
                            Choose Image
                        </Button>
                        <span class="text-xs text-gray-500 font-mono" v-if="fileName">{{ fileName }}</span>
                        <span class="text-xs text-gray-400" v-else>No image selected</span>
                    </div>
                    <p v-if="form.errors.image" class="mt-1 text-sm text-red-600">{{ form.errors.image }}</p>
                </div>

                <div class="flex items-center">
                    <input
                        id="is-active"
                        v-model="form.is_active"
                        type="checkbox"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer"
                    />
                    <label for="is-active" class="ml-2.5 block text-sm font-medium text-gray-600 cursor-pointer select-none">Publish active immediately</label>
                </div>
            </form>

            <template #footer>
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-lg">
                    <Button variant="ghost" @click="closeModal">Cancel</Button>
                    <Button variant="primary" @click="submitForm" :loading="form.processing">
                        {{ editingItem ? 'Save Changes' : 'Create Banner' }}
                    </Button>
                </div>
            </template>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { route as ziggyRoute } from 'ziggy-js';
import { Ziggy } from '@/ziggy';
import { usePagination } from '@/Composables/usePagination';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import Badge from '@/Components/UI/Badge.vue';
import Modal from '@/Components/UI/Modal.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

const route = (name, params, absolute, config = Ziggy) => ziggyRoute(name, params, absolute, config);

const props = defineProps({
    adverts: Object,
});

const showModal = ref(false);
const editingItem = ref(null);
const fileName = ref('');
const fileInput = ref(null);

const form = useForm({
    title: '',
    image: null,
    is_active: true,
});

const { pageNumbers } = usePagination(computed(() => props.adverts));

const changePage = (page) => {
    router.get(route('admin.adverts.index'), { page }, { preserveState: true, preserveScroll: true });
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image = file;
        fileName.value = file.name;
    }
};

const openAddModal = () => {
    editingItem.value = null;
    fileName.value = '';
    form.reset();
    showModal.value = true;
};

const openEditModal = (advert) => {
    editingItem.value = advert;
    form.title = advert.title;
    form.is_active = !!advert.is_active;
    form.image = null;
    fileName.value = advert.image_path ? 'Current Image' : '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingItem.value = null;
    fileName.value = '';
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    // Laravel handles multi-part PUT requests weirdly in some versions,
    // so we can emulate a PUT request with POST and _method parameter, 
    // or just let useForm do it.
    if (editingItem.value) {
        form.post(route('admin.adverts.update', editingItem.value.id), {
            forceFormData: true,
            queryParams: { _method: 'PUT' }, // PUT request emulation
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.adverts.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const toggleActive = (advert) => {
    router.put(route('admin.adverts.toggle', advert.id), {}, {
        preserveScroll: true,
    });
};

const confirmDelete = (advert) => {
    if (confirm(`Are you sure you want to delete advertisement: "${advert.title}"?`)) {
        router.delete(route('admin.adverts.destroy', advert.id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};
</script>
