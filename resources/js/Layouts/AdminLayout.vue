<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar Navigation -->
        <aside
            :class="[
                'fixed inset-y-0 z-50 flex flex-col bg-white border-r border-gray-200 transition-all duration-300',
                sidebarOpen ? 'w-72' : 'w-20',
                'lg:translate-x-0',
                mobileSidebarOpen
                    ? 'translate-x-0'
                    : '-translate-x-full lg:translate-x-0',
            ]"
        >
            <!-- Logo -->
            <div
                class="flex items-center gap-3 px-6 h-16 border-b border-gray-100"
            >
                <div
                    class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white flex-shrink-0"
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
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                        />
                    </svg>
                </div>
                <span
                    v-if="sidebarOpen"
                    class="text-lg font-semibold text-gray-900"
                    >PrimeChem</span
                >
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <Link
                    :href="route('dashboard')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors',
                        $page.component === 'Dashboard'
                            ? 'bg-blue-50 text-blue-600 font-bold'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
                        !sidebarOpen && 'justify-center',
                    ]"
                >
                    <svg
                        class="w-5 h-5 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"
                        />
                    </svg>
                    <span v-if="sidebarOpen">Dashboard</span>
                </Link>

                <div v-if="sidebarOpen" class="pt-4 pb-2">
                    <p
                        class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider"
                    >
                        Management
                    </p>
                </div>

                <Link
                    :href="route('admin.inventory.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors',
                        $page.component.startsWith('Admin/Inventory')
                            ? 'bg-blue-50 text-blue-600 font-bold'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
                        !sidebarOpen && 'justify-center',
                    ]"
                >
                    <svg
                        class="w-5 h-5 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                        />
                    </svg>
                    <span v-if="sidebarOpen">Inventory</span>
                </Link>

                <Link
                    :href="route('admin.refills.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors',
                        $page.component.startsWith('Admin/Refills')
                            ? 'bg-blue-50 text-blue-600 font-bold'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
                        !sidebarOpen && 'justify-center',
                    ]"
                >
                    <svg
                        class="w-5 h-5 flex-shrink-0"
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
                    <span v-if="sidebarOpen">Refills</span>
                </Link>

                <Link
                    :href="route('admin.users.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors',
                        $page.component.startsWith('Admin/Users')
                            ? 'bg-blue-50 text-blue-600 font-bold'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
                        !sidebarOpen && 'justify-center',
                    ]"
                >
                    <svg
                        class="w-5 h-5 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                        />
                    </svg>
                    <span v-if="sidebarOpen">Users</span>
                </Link>

                <Link
                    :href="route('admin.adverts.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors',
                        $page.component.startsWith('Admin/Adverts')
                            ? 'bg-blue-50 text-blue-600 font-bold'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
                        !sidebarOpen && 'justify-center',
                    ]"
                >
                    <svg
                        class="w-5 h-5 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <span v-if="sidebarOpen">Advertisements</span>
                </Link>

                <Link
                    :href="route('admin.alerts.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors',
                        $page.component.startsWith('Admin/Alerts')
                            ? 'bg-blue-50 text-blue-600 font-bold'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
                        !sidebarOpen && 'justify-center',
                    ]"
                >
                    <svg
                        class="w-5 h-5 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                    <span v-if="sidebarOpen">Alerts</span>
                </Link>

                <Link
                    :href="route('admin.activity.index')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors',
                        $page.component.startsWith('Admin/Activity')
                            ? 'bg-blue-50 text-blue-600 font-bold'
                            : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900',
                        !sidebarOpen && 'justify-center',
                    ]"
                >
                    <svg
                        class="w-5 h-5 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <span v-if="sidebarOpen">Activity Logs</span>
                </Link>
            </nav>

            <!-- User Profile (Bottom Sidebar) -->
            <div class="p-4 border-t border-gray-200">
                <div v-if="sidebarOpen" class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-600 flex-shrink-0"
                    >
                        {{ user?.name?.charAt(0) || "?" }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ user?.name }}
                        </p>
                        <p class="text-xs text-gray-500 truncate capitalize">
                            {{ user?.role }}
                        </p>
                    </div>
                </div>
                <button
                    v-else
                    class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-600 mx-auto"
                >
                    {{ user?.name?.charAt(0) || "?" }}
                </button>
                <button
                    @click="logout"
                    :class="[
                        'mt-3 w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-md transition-colors',
                        !sidebarOpen && 'justify-center',
                    ]"
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
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                        />
                    </svg>
                    <span v-if="sidebarOpen">Sign Out</span>
                </button>
            </div>

            <!-- Sidebar Toggle (Desktop) -->
            <button
                @click="sidebarOpen = !sidebarOpen"
                class="hidden lg:flex absolute -right-3 top-20 w-6 h-6 bg-white border border-gray-200 rounded-full items-center justify-center text-gray-400 hover:text-gray-600 shadow-sm"
            >
                <svg
                    class="w-3 h-3 transition-transform"
                    :class="{ 'rotate-180': !sidebarOpen }"
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
            </button>
        </aside>

        <!-- Mobile Overlay -->
        <div
            v-if="mobileSidebarOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
            @click="mobileSidebarOpen = false"
        ></div>

        <!-- Main Content Area -->
        <div
            :class="[
                'flex-1 flex flex-col transition-all duration-300',
                sidebarOpen ? 'lg:pl-72' : 'lg:pl-20',
            ]"
        >
            <!-- Top Header -->
            <header
                class="h-16 px-6 sm:px-8 bg-white border-b border-gray-200 flex items-center justify-between z-10 sticky top-0"
            >
                <div class="flex items-center gap-4">
                    <button
                        @click="mobileSidebarOpen = !mobileSidebarOpen"
                        class="lg:hidden text-gray-500 hover:text-gray-700"
                    >
                        <svg
                            class="w-6 h-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>
                    <h1
                        v-if="pageTitle"
                        class="text-lg font-semibold text-gray-900"
                    >
                        {{ pageTitle }}
                    </h1>
                </div>

                <div class="flex items-center gap-4">
                    <button
                        class="relative p-2 text-gray-500 hover:text-primary transition-colors"
                    >
                        <svg
                            class="w-6 h-6"
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
                        <span
                            class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"
                        ></span>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 px-6 sm:px-8 py-8">
                <!-- Flash Notifications -->
                <div
                    v-if="
                        $page.props.flash?.success || $page.props.flash?.error
                    "
                    class="mb-6 space-y-2"
                >
                    <div
                        v-if="$page.props.flash.success"
                        class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3"
                    >
                        <svg
                            class="w-5 h-5 text-green-500"
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
                        <p class="text-sm text-green-800 font-medium">
                            {{ $page.props.flash.success }}
                        </p>
                    </div>
                    <div
                        v-if="$page.props.flash.error"
                        class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3"
                    >
                        <svg
                            class="w-5 h-5 text-red-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <p class="text-sm text-red-800 font-medium">
                            {{ $page.props.flash.error }}
                        </p>
                    </div>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "@/ziggy";

const route = (name, params, absolute, config = Ziggy) =>
    ziggyRoute(name, params, absolute, config);

const page = usePage();
const user = computed(() => page.props.auth?.user);

const sidebarOpen = ref(true);
const mobileSidebarOpen = ref(false);

defineProps({
    pageTitle: {
        type: String,
        default: "",
    },
});

const logout = () => {
    router.post(route("logout"));
};
</script>
