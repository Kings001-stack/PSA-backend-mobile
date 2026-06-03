<template>
    <AdminLayout page-title="Patient Profile">
        <div class="space-y-6">
            <!-- Header with Back Button -->
            <div class="flex items-center justify-between">
                <Button
                    variant="ghost"
                    @click="router.visit(route('admin.users.index'))"
                >
                    <svg
                        class="w-4 h-4 mr-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                    Back to Patients
                </Button>

                <div class="flex items-center gap-3">
                    <!-- Call Button -->
                    <a
                        v-if="permissions.can_contact && user.phone"
                        :href="`tel:${user.phone}`"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 rounded-lg shadow-md hover:shadow-lg transition-all duration-200"
                    >
                        <svg
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                            />
                        </svg>
                        Call Patient
                    </a>

                    <!-- Send Notification Button -->
                    <Button
                        v-if="permissions.can_contact"
                        variant="primary"
                        @click="openNotificationModal"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                            />
                        </svg>
                        Send Notification
                    </Button>
                </div>
            </div>

            <!-- Profile Header Card -->
            <Card>
                <div class="flex items-start gap-6 p-8">
                    <!-- Avatar -->
                    <div>
                        <div
                            v-if="user.avatar_url"
                            class="w-24 h-24 rounded-full overflow-hidden shadow-lg"
                        >
                            <img
                                :src="user.avatar_url"
                                :alt="user.name"
                                class="w-full h-full object-cover"
                            />
                        </div>
                        <div
                            v-else
                            class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold uppercase text-3xl shadow-lg"
                        >
                            {{ user.name.substring(0, 2) }}
                        </div>
                    </div>

                    <!-- Profile Info -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">
                                    {{ user.name }}
                                </h1>
                                <p class="text-sm text-gray-500 mt-1">
                                    Patient ID: #{{ user.id }}
                                </p>
                            </div>
                            <Badge
                                :variant="getStatusVariant(user.account_status)"
                            >
                                {{ formatStatus(user.account_status) }}
                            </Badge>
                        </div>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Email -->
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0"
                                >
                                    <svg
                                        class="w-5 h-5 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-gray-500 uppercase tracking-wide font-semibold"
                                    >
                                        Email
                                    </p>
                                    <p class="text-sm text-gray-900 mt-1">
                                        {{ user.email }}
                                    </p>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0"
                                >
                                    <svg
                                        class="w-5 h-5 text-green-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-gray-500 uppercase tracking-wide font-semibold"
                                    >
                                        Phone
                                    </p>
                                    <p class="text-sm text-gray-900 mt-1">
                                        {{ user.phone || "Not provided" }}
                                    </p>
                                </div>
                            </div>

                            <!-- Member Since -->
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0"
                                >
                                    <svg
                                        class="w-5 h-5 text-purple-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p
                                        class="text-xs text-gray-500 uppercase tracking-wide font-semibold"
                                    >
                                        Member Since
                                    </p>
                                    <p class="text-sm text-gray-900 mt-1">
                                        {{ user.created_at }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="user.last_login_at"
                            class="mt-4 text-sm text-gray-500"
                        >
                            Last login: {{ user.last_login_at }}
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Pharmacy Activity Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <Card>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Total Refills
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 mt-2"
                                >
                                    {{ refillStats.total_requests }}
                                </p>
                            </div>
                            <div
                                class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center"
                            >
                                <svg
                                    class="w-6 h-6 text-blue-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </Card>

                <Card>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Pending Requests
                                </p>
                                <p
                                    class="text-3xl font-bold text-amber-600 mt-2"
                                >
                                    {{ refillStats.pending_requests }}
                                </p>
                            </div>
                            <div
                                class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center"
                            >
                                <svg
                                    class="w-6 h-6 text-amber-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </Card>

                <Card>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Ready for Pickup
                                </p>
                                <p
                                    class="text-3xl font-bold text-green-600 mt-2"
                                >
                                    {{ refillStats.ready_for_pickup }}
                                </p>
                            </div>
                            <div
                                class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center"
                            >
                                <svg
                                    class="w-6 h-6 text-green-600"
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
                            </div>
                        </div>
                    </div>
                </Card>

                <Card>
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">
                                    Completed
                                </p>
                                <p
                                    class="text-3xl font-bold text-gray-900 mt-2"
                                >
                                    {{ refillStats.collected }}
                                </p>
                            </div>
                            <div
                                class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center"
                            >
                                <svg
                                    class="w-6 h-6 text-gray-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Top Medications -->
            <Card v-if="topMedications.length > 0">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        Most Requested Medications
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="med in topMedications"
                            :key="med.medication_name"
                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center"
                                >
                                    <svg
                                        class="w-5 h-5 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                        />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">
                                        {{ med.medication_name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ med.dosage }} • {{ med.form }}
                                    </p>
                                </div>
                            </div>
                            <Badge variant="primary"
                                >{{ med.request_count }} requests</Badge
                            >
                        </div>
                    </div>
                </div>
            </Card>

            <!-- Refill History -->
            <Card>
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        Refill Request History
                    </h3>

                    <div
                        v-if="refillRequests.data.length > 0"
                        class="space-y-4"
                    >
                        <div
                            v-for="refill in refillRequests.data"
                            :key="refill.id"
                            class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-semibold text-gray-900">
                                            {{ refill.medication_name }}
                                        </h4>
                                        <Badge
                                            :variant="
                                                getRefillStatusVariant(
                                                    refill.status,
                                                )
                                            "
                                        >
                                            {{
                                                formatRefillStatus(
                                                    refill.status,
                                                )
                                            }}
                                        </Badge>
                                        <Badge
                                            v-if="refill.is_urgent"
                                            variant="danger"
                                            size="sm"
                                            >Urgent</Badge
                                        >
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        {{ refill.medication_dosage }} •
                                        {{ refill.medication_form }} • Qty:
                                        {{ refill.quantity }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        Requested: {{ refill.created_at }}
                                    </p>
                                    <p
                                        v-if="refill.reviewed_at"
                                        class="text-xs text-gray-500"
                                    >
                                        Reviewed: {{ refill.reviewed_at }} by
                                        {{ refill.reviewed_by_name }}
                                    </p>
                                    <p
                                        v-if="refill.notes"
                                        class="text-sm text-gray-700 mt-2"
                                    >
                                        <span class="font-medium"
                                            >Patient Notes:</span
                                        >
                                        {{ refill.notes }}
                                    </p>
                                    <p
                                        v-if="refill.admin_notes"
                                        class="text-sm text-gray-700 mt-2"
                                    >
                                        <span class="font-medium"
                                            >Pharmacist Notes:</span
                                        >
                                        {{ refill.admin_notes }}
                                    </p>
                                    <p
                                        v-if="refill.rejection_reason"
                                        class="text-sm text-red-600 mt-2"
                                    >
                                        <span class="font-medium"
                                            >Rejection Reason:</span
                                        >
                                        {{ refill.rejection_reason }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Pagination for refills -->
                        <div
                            v-if="refillRequests.last_page > 1"
                            class="flex items-center justify-between border-t border-gray-200 pt-4"
                        >
                            <p class="text-sm text-gray-600">
                                Page {{ refillRequests.current_page }} of
                                {{ refillRequests.last_page }}
                            </p>
                            <div class="flex gap-2">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    :disabled="
                                        refillRequests.current_page === 1
                                    "
                                    @click="
                                        loadRefillPage(
                                            refillRequests.current_page - 1,
                                        )
                                    "
                                >
                                    Previous
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    :disabled="
                                        refillRequests.current_page ===
                                        refillRequests.last_page
                                    "
                                    @click="
                                        loadRefillPage(
                                            refillRequests.current_page + 1,
                                        )
                                    "
                                >
                                    Next
                                </Button>
                            </div>
                        </div>
                    </div>

                    <EmptyState
                        v-else
                        title="No refill requests yet"
                        description="This patient hasn't submitted any refill requests."
                    />
                </div>
            </Card>

            <!-- Recent Notifications -->
            <Card v-if="recentNotifications.length > 0">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        Recent Notifications
                    </h3>
                    <div class="space-y-3">
                        <div
                            v-for="notification in recentNotifications"
                            :key="notification.id"
                            class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg"
                        >
                            <div
                                class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0"
                            >
                                <svg
                                    class="w-5 h-5 text-purple-600"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                                    />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 text-sm">
                                    {{ notification.title }}
                                </p>
                                <p class="text-sm text-gray-600 mt-1">
                                    {{ notification.message }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ notification.created_at }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Send Notification Modal -->
        <Modal
            :show="showNotificationModal"
            title="Send Notification"
            size="lg"
            @close="closeNotificationModal"
        >
            <form @submit.prevent="submitNotification" class="space-y-5 p-8">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold uppercase text-sm shadow-md"
                        >
                            {{ user.name.substring(0, 2) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ user.name }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ user.email }}
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Notification Type <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="notificationForm.notification_type"
                        required
                        class="block w-full rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm py-2.5 px-3.5 bg-white"
                    >
                        <option value="pharmacy">Pharmacy Update</option>
                        <option value="refill">Refill Status</option>
                        <option value="alert">Important Alert</option>
                        <option value="system">System Message</option>
                    </select>
                </div>

                <Input
                    v-model="notificationForm.title"
                    label="Title"
                    required
                    placeholder="e.g., Your prescription is ready"
                    :error="notificationForm.errors.title"
                />

                <div>
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-2"
                    >
                        Message <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="notificationForm.message"
                        required
                        rows="4"
                        placeholder="Enter your message here..."
                        class="block w-full rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm py-2.5 px-3.5 bg-white resize-none"
                    ></textarea>
                    <p
                        v-if="notificationForm.errors.message"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ notificationForm.errors.message }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ notificationForm.message?.length || 0 }} / 1000
                        characters
                    </p>
                </div>
            </form>

            <template #footer>
                <div
                    class="flex items-center justify-end gap-3 px-8 py-5 bg-gradient-to-r from-gray-50 via-white to-gray-50 border-t border-gray-100"
                >
                    <Button variant="ghost" @click="closeNotificationModal"
                        >Cancel</Button
                    >
                    <Button
                        variant="primary"
                        @click="submitNotification"
                        :loading="notificationForm.processing"
                    >
                        <svg
                            class="w-4 h-4 mr-2"
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
                    </Button>
                </div>
            </template>
        </Modal>
    </AdminLayout>
</template>

<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "@/ziggy";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Card from "@/Components/UI/Card.vue";
import Button from "@/Components/UI/Button.vue";
import Input from "@/Components/UI/Input.vue";
import Badge from "@/Components/UI/Badge.vue";
import Modal from "@/Components/UI/Modal.vue";
import EmptyState from "@/Components/UI/EmptyState.vue";

const route = (name, params, absolute, config = Ziggy) =>
    ziggyRoute(name, params, absolute, config);

const props = defineProps({
    user: Object,
    refillStats: Object,
    topMedications: Array,
    refillRequests: Object,
    recentNotifications: Array,
    permissions: Object,
});

const showNotificationModal = ref(false);

const notificationForm = useForm({
    title: "",
    message: "",
    notification_type: "pharmacy",
});

const openNotificationModal = () => {
    notificationForm.reset();
    notificationForm.notification_type = "pharmacy";
    showNotificationModal.value = true;
};

const closeNotificationModal = () => {
    showNotificationModal.value = false;
    notificationForm.reset();
    notificationForm.clearErrors();
};

const submitNotification = () => {
    notificationForm.post(route("admin.users.notify", props.user.id), {
        onSuccess: () => {
            closeNotificationModal();
        },
    });
};

const loadRefillPage = (page) => {
    router.visit(route("admin.users.show", { user: props.user.id, page }), {
        preserveState: true,
        preserveScroll: true,
    });
};

const getStatusVariant = (status) => {
    switch (status) {
        case "active":
            return "success";
        case "suspended":
            return "warning";
        case "deleted":
            return "danger";
        default:
            return "secondary";
    }
};

const formatStatus = (status) => {
    switch (status) {
        case "active":
            return "Active";
        case "suspended":
            return "Suspended";
        case "deleted":
            return "Deleted";
        default:
            return status || "Active";
    }
};

const getRefillStatusVariant = (status) => {
    switch (status) {
        case "pending":
            return "warning";
        case "under_review":
            return "info";
        case "approved":
            return "primary";
        case "rejected":
            return "danger";
        case "ready_for_pickup":
            return "success";
        case "collected":
            return "secondary";
        case "cancelled":
            return "danger";
        default:
            return "secondary";
    }
};

const formatRefillStatus = (status) => {
    return status
        .split("_")
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(" ");
};
</script>
