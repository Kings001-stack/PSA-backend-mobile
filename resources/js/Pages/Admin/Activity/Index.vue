<template>
    <AdminLayout page-title="Activity Logs">
        <div class="space-y-6">
            <!-- Header -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        Activity Logs
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Monitor system activities, user actions, and audit
                        trails.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="secondary" @click="exportLogs">
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
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                            />
                        </svg>
                        Export
                    </Button>
                    <Button variant="primary" @click="refreshLogs">
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
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                        Refresh
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card padding="normal">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search activities..."
                            @input="handleSearch"
                        />
                    </div>
                    <div>
                        <select
                            v-model="actionFilter"
                            @change="handleFilterChange"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm py-2 px-3 bg-white"
                        >
                            <option value="">All Actions</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="create">Create</option>
                            <option value="update">Update</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                    <div>
                        <select
                            v-model="userFilter"
                            @change="handleFilterChange"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm py-2 px-3 bg-white"
                        >
                            <option value="">All Users</option>
                            <option value="pharmacist">Pharmacists</option>
                            <option value="super_admin">Super Admins</option>
                        </select>
                    </div>
                </div>
            </Card>

            <!-- Activity Table -->
            <Card padding="none">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Timestamp
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    User
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Action
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Description
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    IP Address
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-if="loading">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <LoadingSpinner size="lg" />
                                </td>
                            </tr>
                            <tr
                                v-else-if="
                                    !activities || activities.length === 0
                                "
                            >
                                <td colspan="5" class="px-6 py-12">
                                    <EmptyState
                                        title="No activity logs found"
                                        description="There are no activity logs to display."
                                        icon="document"
                                    />
                                </td>
                            </tr>
                            <tr
                                v-else
                                v-for="activity in activities"
                                :key="activity.id"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                                >
                                    {{ formatDate(activity.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-8 w-8 bg-gray-200 rounded-full flex items-center justify-center"
                                        >
                                            <span
                                                class="text-xs font-medium text-gray-600"
                                            >
                                                {{
                                                    activity.user?.name?.charAt(
                                                        0,
                                                    ) || "?"
                                                }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <p
                                                class="text-sm font-medium text-gray-900"
                                            >
                                                {{
                                                    activity.user?.name ||
                                                    "Unknown"
                                                }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ activity.user?.role }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Badge
                                        :variant="
                                            getActionBadgeVariant(
                                                activity.action,
                                            )
                                        "
                                    >
                                        {{ activity.action }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ activity.description }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ activity.ip_address || "N/A" }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div
                    v-if="activities && activities.length > 0"
                    class="px-6 py-4 border-t border-gray-200"
                >
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{
                                pagination.from
                            }}</span>
                            to
                            <span class="font-medium">{{ pagination.to }}</span>
                            of
                            <span class="font-medium">{{
                                pagination.total
                            }}</span>
                            results
                        </div>
                        <div class="flex gap-2">
                            <Button
                                variant="secondary"
                                size="sm"
                                :disabled="!pagination.prev_page_url"
                                @click="goToPage(pagination.current_page - 1)"
                            >
                                Previous
                            </Button>
                            <Button
                                variant="secondary"
                                size="sm"
                                :disabled="!pagination.next_page_url"
                                @click="goToPage(pagination.current_page + 1)"
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import Card from "@/Components/UI/Card.vue";
import Button from "@/Components/UI/Button.vue";
import Input from "@/Components/UI/Input.vue";
import Badge from "@/Components/UI/Badge.vue";
import LoadingSpinner from "@/Components/UI/LoadingSpinner.vue";
import EmptyState from "@/Components/UI/EmptyState.vue";
import { useDebounce } from "@/Composables/useDebounce";

const props = defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
    pagination: {
        type: Object,
        default: () => ({
            current_page: 1,
            from: 0,
            to: 0,
            total: 0,
            prev_page_url: null,
            next_page_url: null,
        }),
    },
});

const loading = ref(false);
const searchQuery = ref("");
const actionFilter = ref("");
const userFilter = ref("");

const { debouncedValue: debouncedSearch, debounce } = useDebounce(
    searchQuery,
    500,
);

const handleSearch = () => {
    debounce(() => {
        handleFilterChange();
    });
};

const handleFilterChange = () => {
    loading.value = true;
    router.get(
        route("admin.activity.index"),
        {
            search: searchQuery.value,
            action: actionFilter.value,
            user_role: userFilter.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const refreshLogs = () => {
    router.reload({ only: ["activities", "pagination"] });
};

const exportLogs = () => {
    window.location.href = route("admin.activity.export", {
        search: searchQuery.value,
        action: actionFilter.value,
        user_role: userFilter.value,
    });
};

const goToPage = (page) => {
    loading.value = true;
    router.get(
        route("admin.activity.index"),
        {
            page,
            search: searchQuery.value,
            action: actionFilter.value,
            user_role: userFilter.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const formatDate = (date) => {
    return new Date(date).toLocaleString("en-US", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const getActionBadgeVariant = (action) => {
    const variants = {
        login: "success",
        logout: "secondary",
        create: "primary",
        update: "warning",
        delete: "danger",
    };
    return variants[action?.toLowerCase()] || "secondary";
};
</script>
