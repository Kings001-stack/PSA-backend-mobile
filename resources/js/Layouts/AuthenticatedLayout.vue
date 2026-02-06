<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar Navigation -->
        <aside class="hidden lg:flex w-72 flex-col fixed inset-y-0 z-50 bg-white border-r border-gray-200">
            <!-- Logo -->
            <div class="flex items-center gap-3 px-6 h-16 border-b border-gray-100">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                </div>
                <span class="text-lg font-semibold text-gray-900">Pharmacy AI</span>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                    :class="$page.component === 'Dashboard' ? 'bg-blue-50 text-primary' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                    Dashboard
                </Link>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Management</p>
                </div>

                <Link
                    :href="route('medications.index')"
                    class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                    :class="$page.component.startsWith('Medications') ? 'bg-blue-50 text-primary' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    Medications
                </Link>

                <Link
                    :href="route('inventory.index')"
                    class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                    :class="$page.component.startsWith('Inventory') ? 'bg-blue-50 text-primary' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    Inventory
                </Link>

                <Link
                    :href="route('chat.index')"
                    class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors"
                    :class="$page.component.startsWith('Chat') ? 'bg-blue-50 text-primary' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900'"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                    AI Assistant
                </Link>
            </nav>

            <!-- User Profile (Bottom Sidebar) -->
            <div class="p-4 border-t border-gray-200" v-if="$page.props.auth?.user">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-sm font-bold text-gray-600">
                        {{ $page.props.auth.user.name?.charAt(0) || '?' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $page.props.auth.user.name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $page.props.auth.tenant?.name || 'Guest user' }}</p>
                    </div>
                </div>
                 <button
                    @click="logout"
                    class="mt-3 w-full flex items-center justify-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-md transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                    Sign Out
                </button>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col lg:pl-72 transition-all duration-300">
            <!-- Top Header (Search, Mobile Menu) -->
            <header class="h-16 px-6 sm:px-8 bg-white border-b border-gray-200 flex items-center justify-between z-10 sticky top-0">
                <div class="flex items-center gap-4 lg:hidden">
                    <!-- Mobile Menu Button Placeholder -->
                    <button class="text-gray-500 hover:text-gray-700">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>
                     <span class="font-semibold text-gray-900">Pharmacy AI</span>
                </div>

                <div class="flex-1 px-4 lg:px-8 max-w-2xl">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input
                            type="text"
                            class="block w-full pl-10 pr-3 py-2 border-none rounded-md leading-5 bg-gray-50 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:ring-0 sm:text-sm transition-colors"
                            placeholder="Global search..."
                        />
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button class="relative p-2 text-gray-500 hover:text-primary transition-colors">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                         <span class="absolute top-2 right-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 px-6 sm:px-8 py-8">
                <!-- Flash Notifications -->
                <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="mb-6 space-y-2">
                    <div v-if="$page.props.flash.success" class="p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        <p class="text-sm text-green-800 font-medium">{{ $page.props.flash.success }}</p>
                    </div>
                    <div v-if="$page.props.flash.error" class="p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 animate-in fade-in slide-in-from-top-4 duration-300">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <p class="text-sm text-red-800 font-medium">{{ $page.props.flash.error }}</p>
                    </div>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "@/ziggy";
import { onMounted } from 'vue';

const route = (name, params, absolute, config = Ziggy) => ziggyRoute(name, params, absolute, config);

const page = usePage();
onMounted(() => {
    console.log('Layout mounted. Auth props:', page.props.auth);
});

const logout = () => {
    router.post(route('logout', undefined, undefined, Ziggy));
};
</script>
