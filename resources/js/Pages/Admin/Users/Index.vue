﻿﻿
<template>
    <AdminLayout page-title="User Management">
        <div class="min-h-screen space-y-6 pb-12">
            <!-- Premium Header -->
            <div
                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 p-6 sm:p-10 shadow-2xl"
            >
                <div
                    class="absolute top-0 right-0 h-32 w-32 rounded-full bg-white opacity-10 blur-3xl"
                ></div>
                <div class="relative">
                    <h1 class="text-3xl font-bold text-white">
                        {{
                            permissions.is_pharmacist
                                ? "Patient Management"
                                : "User Management"
                        }}
                    </h1>
                    <p class="text-blue-100 mt-2">
                        Professional user management with role-based access
                    </p>
                    <div class="flex gap-3 mt-6">
                        <button
                            @click="router.visit(route('admin.users.index'))"
                            class="px-5 py-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold transition-all"
                        >
                            Refresh
                        </button>
                        <button
                            v-if="permissions.can_create_users"
                            @click="openAddModal"
                            class="px-6 py-2.5 bg-white text-blue-600 hover:bg-blue-50 rounded-xl font-bold transition-all"
                        >
                            Add User
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <input
                        v-model="searchQuery"
                        @input="handleSearch"
                        type="text"
                        placeholder="Search users..."
                        class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <select
                        v-model="statusFilter"
                        @change="handleFilterChange"
                        class="px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <button
                        v-if="searchQuery || statusFilter"
                        @click="clearFilters"
                        class="px-4 py-3 bg-gray-100 hover:bg-gray-200 rounded-xl font-semibold"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Users Table/Cards -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <!-- Mobile Cards -->
                <div class="lg:hidden divide-y divide-blue-100">
                    <div
                        v-for="user in users.data"
                        :key="user.id"
                        class="p-6 hover:bg-blue-50/30 transition-colors duration-200"
                    >
                        <div class="flex gap-4">
                            <div
                                v-if="user.avatar_url"
                                class="w-16 h-16 rounded-2xl overflow-hidden"
                            >
                                <img
                                    :src="user.avatar_url"
                                    :alt="user.name"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <div
                                v-else
                                class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-xl shadow-xl"
                            >
                                {{ user.name.substring(0, 2).toUpperCase() }}
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">
                                    {{ user.name }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ user.email }}
                                </p>
                                <p
                                    v-if="user.phone"
                                    class="text-sm text-gray-600"
                                >
                                    {{ user.phone }}
                                </p>
                                <div class="flex gap-2 mt-3">
                                    <button
                                        v-if="user.can_view_profile"
                                        @click="viewProfile(user)"
                                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold"
                                    >
                                        View
                                    </button>
                                    <a
                                        v-if="user.can_contact && user.phone"
                                        :href="`tel:SilentlyContinue{user.phone}`"
                                        class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold"
                                        >Call</a
                                    >
                                    <button
                                        v-if="user.can_contact"
                                        @click="openNotificationModal(user)"
                                        class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-semibold"
                                    >
                                        Notify
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table -->
                <table class="hidden lg:table w-full">
                    <thead>
                        <tr class="border-b-2 border-blue-100">
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase"
                            >
                                Patient
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase"
                            >
                                Contact
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase"
                            >
                                Activity
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-100">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="hover:bg-blue-50/30 transition-colors duration-200"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div
                                        v-if="user.avatar_url"
                                        class="w-12 h-12 rounded-xl overflow-hidden"
                                    >
                                        <img
                                            :src="user.avatar_url"
                                            :alt="user.name"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <div
                                        v-else
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md"
                                    >
                                        {{
                                            user.name
                                                .substring(0, 2)
                                                .toUpperCase()
                                        }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">
                                            {{ user.name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            ID: #{{ user.id }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm">{{ user.email }}</p>
                                <p
                                    v-if="user.phone"
                                    class="text-sm text-gray-600"
                                >
                                    {{ user.phone }}
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold"
                                    >{{ user.refill_stats.total }} refills</span
                                >
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="
                                        user.account_status === 'active'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-amber-100 text-amber-700'
                                    "
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold"
                                    >{{ user.account_status }}</span
                                >
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button
                                        v-if="user.can_view_profile"
                                        @click="viewProfile(user)"
                                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700"
                                    >
                                        View
                                    </button>
                                    <a
                                        v-if="user.can_contact && user.phone"
                                        :href="`tel:SilentlyContinue{user.phone}`"
                                        class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700"
                                        >Call</a
                                    >
                                    <button
                                        v-if="user.can_contact"
                                        @click="openNotificationModal(user)"
                                        class="px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-semibold hover:bg-purple-700"
                                    >
                                        Notify
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div
                    v-if="users.data.length > 0"
                    class="border-t border-blue-100 p-6 flex justify-between items-center"
                >
                    <p class="text-sm text-gray-600">
                        Showing {{ users.from }} to {{ users.to }} of
                        {{ users.total }}
                    </p>
                    <div class="flex gap-2">
                        <button
                            :disabled="!users.prev_page_url"
                            @click="changePage(users.current_page - 1)"
                            class="px-4 py-2 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        >
                            Previous
                        </button>
                        <button
                            :disabled="!users.next_page_url"
                            @click="changePage(users.current_page + 1)"
                            class="px-4 py-2 bg-white border border-blue-200 rounded-lg hover:bg-blue-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Modal -->
        <Modal
            :show="showNotificationModal"
            title="Send Notification"
            @close="closeNotificationModal"
        >
            <div class="p-8 space-y-6">
                <!-- Recipient Info Card -->
                <div
                    v-if="selectedUser"
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-5"
                >
                    <div class="flex items-center gap-4">
                        <div
                            v-if="selectedUser.avatar_url"
                            class="w-14 h-14 rounded-xl overflow-hidden shadow-md"
                        >
                            <img
                                :src="selectedUser.avatar_url"
                                :alt="selectedUser.name"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md"
                        >
                            {{
                                selectedUser.name.substring(0, 2).toUpperCase()
                            }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900 text-lg">
                                {{ selectedUser.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ selectedUser.email }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notification Type -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Notification Type <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="notificationForm.notification_type"
                        class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 bg-white"
                    >
                        <option value="pharmacy">💊 Pharmacy Update</option>
                        <option value="refill">📋 Refill Status</option>
                        <option value="alert">⚠️ Important Alert</option>
                    </select>
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="notificationForm.title"
                        type="text"
                        placeholder="e.g., Your prescription is ready"
                        class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 bg-white placeholder-gray-400"
                    />
                </div>

                <!-- Message -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Message <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="notificationForm.message"
                        rows="4"
                        placeholder="Enter your message here..."
                        class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 bg-white placeholder-gray-400 resize-none"
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-2">
                        {{ notificationForm.message?.length || 0 }} / 1000
                        characters
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-4">
                    <button
                        @click="closeNotificationModal"
                        type="button"
                        class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-all duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitNotification"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-bold hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2"
                    >
                        <svg
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                            />
                        </svg>
                        Send Notification
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Add User Modal -->
        <Modal
            v-if="permissions.can_create_users"
            :show="showModal"
            :title="editingItem ? 'Edit User' : 'Add New User'"
            @close="closeModal"
        >
            <div class="p-8 space-y-6">
                <!-- Header Info -->
                <div
                    class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-5 mb-6"
                >
                    <h3 class="text-lg font-bold text-gray-900 mb-1">
                        {{
                            editingItem
                                ? "Update User Information"
                                : "Create New User Account"
                        }}
                    </h3>
                    <p class="text-sm text-gray-600">
                        {{
                            editingItem
                                ? "Modify the user details below"
                                : "Enter the details for the new user account"
                        }}
                    </p>
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="e.g., John Smith"
                        class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 bg-white placeholder-gray-400"
                    />
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="e.g., john.smith@example.com"
                        class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 bg-white placeholder-gray-400"
                    />
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Phone Number
                    </label>
                    <input
                        v-model="form.phone"
                        type="tel"
                        placeholder="e.g., 555-0101"
                        class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 bg-white placeholder-gray-400"
                    />
                </div>

                <!-- Password (only for new users) -->
                <div v-if="!editingItem">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.password"
                        type="password"
                        placeholder="Enter secure password"
                        class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 bg-white placeholder-gray-400"
                    />
                    <p class="text-xs text-gray-500 mt-2">
                        Minimum 8 characters
                    </p>
                </div>

                <!-- Role -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        User Role <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.role"
                        class="w-full px-4 py-3 border-2 border-blue-200 rounded-xl shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all duration-200 bg-white"
                    >
                        <option value="">Select Role...</option>
                        <option value="user">👤 Patient (Regular User)</option>
                        <option value="pharmacist">
                            💊 Pharmacist (Staff)
                        </option>
                        <option value="super_admin">
                            👑 Super Admin (Full Access)
                        </option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-6">
                    <button
                        @click="closeModal"
                        type="button"
                        class="flex-1 px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-all duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="submitForm"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-bold hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2"
                    >
                        <svg
                            v-if="!editingItem"
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        <svg
                            v-else
                            class="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        {{ editingItem ? "Update User" : "Create User" }}
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "@/ziggy";
import { debounce } from "@/Composables/useDebounce";
import { usePagination } from "@/Composables/usePagination";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Modal from "@/Components/UI/Modal.vue";

const route = (name, params, absolute, config = Ziggy) =>
    ziggyRoute(name, params, absolute, config);

const props = defineProps({
    users: Object,
    filters: Object,
    permissions: Object,
});

const searchQuery = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "");
const showModal = ref(false);
const showNotificationModal = ref(false);
const editingItem = ref(null);
const selectedUser = ref(null);

const form = useForm({
    name: "",
    email: "",
    phone: "",
    password: "",
    role: "",
});
const notificationForm = useForm({
    title: "",
    message: "",
    notification_type: "pharmacy",
});

const { pageNumbers } = usePagination(computed(() => props.users));

const handleSearch = debounce(() => {
    router.get(
        route("admin.users.index"),
        { search: searchQuery.value, status: statusFilter.value },
        { preserveState: true, preserveScroll: true },
    );
}, 500);
const handleFilterChange = () => {
    router.get(
        route("admin.users.index"),
        { search: searchQuery.value, status: statusFilter.value },
        { preserveState: true },
    );
};
const clearFilters = () => {
    searchQuery.value = "";
    statusFilter.value = "";
    router.get(route("admin.users.index"));
};
const changePage = (page) => {
    router.get(
        route("admin.users.index"),
        { page, search: searchQuery.value, status: statusFilter.value },
        { preserveState: true },
    );
};
const viewProfile = (user) => {
    router.visit(route("admin.users.show", user.id));
};
const openNotificationModal = (user) => {
    selectedUser.value = user;
    notificationForm.reset();
    showNotificationModal.value = true;
};
const closeNotificationModal = () => {
    showNotificationModal.value = false;
    selectedUser.value = null;
    notificationForm.reset();
};
const submitNotification = () => {
    notificationForm.post(route("admin.users.notify", selectedUser.value.id), {
        onSuccess: () => closeNotificationModal(),
    });
};
const openAddModal = () => {
    editingItem.value = null;
    form.reset();
    showModal.value = true;
};
const closeModal = () => {
    showModal.value = false;
    editingItem.value = null;
    form.reset();
};
const submitForm = () => {
    editingItem.value
        ? form.put(route("admin.users.update", editingItem.value.id), {
              onSuccess: () => closeModal(),
          })
        : form.post(route("admin.users.store"), {
              onSuccess: () => closeModal(),
          });
};
</script>
