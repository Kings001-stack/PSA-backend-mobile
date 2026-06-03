<template>
    <AdminLayout page-title="Dashboard">
        <div class="space-y-8 animate-fade-in">
            <!-- Top Alert Banner for Pharmacist (If there are critical alerts) -->
            <div
                v-if="stats.lowStockAlerts > 0 || stats.expiringSoon > 0"
                class="bg-gradient-to-r from-red-50 to-amber-50 border-l-4 border-red-500 rounded-r-xl p-4 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="p-2 bg-red-100 text-red-600 rounded-lg shrink-0"
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
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                    </span>
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">
                            Attention Required!
                        </h4>
                        <p class="text-xs text-gray-600 mt-0.5">
                            You have
                            <span class="font-semibold text-red-600"
                                >{{ stats.lowStockAlerts }} low stock
                                items</span
                            >
                            and
                            <span class="font-semibold text-amber-600"
                                >{{ stats.expiringSoon }} batches expiring
                                soon</span
                            >. Please review the inventory alerts below.
                        </p>
                    </div>
                </div>
                <Link
                    :href="route('admin.inventory.index')"
                    class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-red-700 bg-red-100 hover:bg-red-200 active:bg-red-300 rounded-lg transition-colors shrink-0"
                >
                    Manage Inventory
                </Link>
            </div>

            <!-- Header Welcome Card -->
            <div
                class="relative overflow-hidden bg-gradient-to-r from-slate-900 to-indigo-950 text-white rounded-2xl p-6 sm:p-8 shadow-xl"
            >
                <!-- Background decorative elements -->
                <div
                    class="absolute right-0 top-0 -mt-12 -mr-12 w-64 h-64 bg-indigo-500 rounded-full opacity-10 blur-3xl pointer-events-none"
                ></div>
                <div
                    class="absolute right-12 bottom-0 -mb-16 w-48 h-48 bg-emerald-500 rounded-full opacity-10 blur-2xl pointer-events-none"
                ></div>

                <div
                    class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6"
                >
                    <div>
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-emerald-400 text-xs font-semibold backdrop-blur-md mb-3 border border-white/5"
                        >
                            <span
                                class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"
                            ></span>
                            System Online
                        </div>
                        <h2
                            class="text-2xl sm:text-3xl font-extrabold tracking-tight"
                        >
                            Welcome Back,
                            <span
                                class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 to-blue-300"
                                >{{ user?.name }}</span
                            >!
                        </h2>
                        <p class="text-slate-300 text-sm mt-2 max-w-xl">
                            Here is the current health index of
                            <span class="text-white font-semibold">{{
                                tenant?.name || "PrimeChem Pharmacy"
                            }}</span
                            >. Monitor stock levels, track critical expiries,
                            and dispatch refills.
                        </p>
                    </div>
                    <div class="flex items-center gap-4 shrink-0">
                        <div
                            class="p-3 bg-white/10 rounded-xl backdrop-blur-md border border-white/5 text-right"
                        >
                            <div class="text-xs text-slate-400">Date Today</div>
                            <div class="text-sm font-bold text-white mt-0.5">
                                {{ formattedToday }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Medications -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
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
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                                />
                            </svg>
                        </div>
                        <span
                            class="text-xs font-semibold px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full"
                            >Catalog</span
                        >
                    </div>
                    <div class="mt-6">
                        <span
                            class="text-xs text-slate-400 font-medium uppercase tracking-wider block"
                            >Total Medications</span
                        >
                        <div class="flex items-baseline gap-2 mt-1">
                            <span
                                class="text-3xl font-extrabold text-slate-900"
                                >{{ stats.totalMedications }}</span
                            >
                            <span class="text-xs text-slate-400"
                                >formulations</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Total Stock -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
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
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                />
                            </svg>
                        </div>
                        <span
                            class="text-xs font-semibold px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full"
                            >Active Units</span
                        >
                    </div>
                    <div class="mt-6">
                        <span
                            class="text-xs text-slate-400 font-medium uppercase tracking-wider block"
                            >Total Inventory</span
                        >
                        <div class="flex items-baseline gap-2 mt-1">
                            <span
                                class="text-3xl font-extrabold text-slate-900"
                                >{{ stats.totalInventory }}</span
                            >
                            <span class="text-xs text-slate-400"
                                >units in stock</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
                            :class="
                                stats.lowStockAlerts > 0
                                    ? 'bg-red-50 text-red-600'
                                    : 'bg-slate-50 text-slate-400'
                            "
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
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                />
                            </svg>
                        </div>
                        <span
                            class="text-xs font-semibold px-2.5 py-1 rounded-full"
                            :class="
                                stats.lowStockAlerts > 0
                                    ? 'bg-red-50 text-red-700'
                                    : 'bg-slate-50 text-slate-500'
                            "
                        >
                            {{
                                stats.lowStockAlerts > 0
                                    ? "Action Needed"
                                    : "Healthy"
                            }}
                        </span>
                    </div>
                    <div class="mt-6">
                        <span
                            class="text-xs text-slate-400 font-medium uppercase tracking-wider block"
                            >Low Stock Alerts</span
                        >
                        <div class="flex items-baseline gap-2 mt-1">
                            <span
                                class="text-3xl font-extrabold"
                                :class="
                                    stats.lowStockAlerts > 0
                                        ? 'text-red-600'
                                        : 'text-slate-900'
                                "
                            >
                                {{ stats.lowStockAlerts }}
                            </span>
                            <span class="text-xs text-slate-400"
                                >below threshold</span
                            >
                        </div>
                    </div>
                </div>

                <!-- Expiring Soon -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 group flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
                            :class="
                                stats.expiringSoon > 0
                                    ? 'bg-amber-50 text-amber-600'
                                    : 'bg-slate-50 text-slate-400'
                            "
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
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <span
                            class="text-xs font-semibold px-2.5 py-1 rounded-full"
                            :class="
                                stats.expiringSoon > 0
                                    ? 'bg-amber-50 text-amber-700 font-medium'
                                    : 'bg-slate-50 text-slate-500'
                            "
                        >
                            {{
                                stats.expiringSoon > 0 ? "< 30 Days" : "Secure"
                            }}
                        </span>
                    </div>
                    <div class="mt-6">
                        <span
                            class="text-xs text-slate-400 font-medium uppercase tracking-wider block"
                            >Expiring Soon</span
                        >
                        <div class="flex items-baseline gap-2 mt-1">
                            <span
                                class="text-3xl font-extrabold"
                                :class="
                                    stats.expiringSoon > 0
                                        ? 'text-amber-600'
                                        : 'text-slate-900'
                                "
                            >
                                {{ stats.expiringSoon }}
                            </span>
                            <span class="text-xs text-slate-400"
                                >batches near expiry</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Visualization Section with Pie Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Stock Status Pie Chart -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100"
                >
                    <div class="mb-6">
                        <h3 class="text-base font-extrabold text-slate-900">
                            Stock Status Distribution
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Visual breakdown of inventory health
                        </p>
                    </div>
                    <div class="h-64">
                        <PieChart :data="stockChartData" />
                    </div>
                </div>

                <!-- Expiry Status Pie Chart -->
                <div
                    class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100"
                >
                    <div class="mb-6">
                        <h3 class="text-base font-extrabold text-slate-900">
                            Expiry Timeline Analysis
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Batch expiration distribution
                        </p>
                    </div>
                    <div class="h-64">
                        <PieChart :data="expiryChartData" />
                    </div>
                </div>
            </div>

            <!-- Two Column Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Alerts Table (ColSpan 2) -->
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden lg:col-span-2 flex flex-col"
                >
                    <div
                        class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-white"
                    >
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">
                                Critical Stock & Expiry Alerts
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Real-time alerts of medicines needing urgent
                                action.
                            </p>
                        </div>
                        <Link
                            :href="route('admin.inventory.index')"
                            class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors flex items-center gap-1"
                        >
                            View All Inventory
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
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>
                        </Link>
                    </div>

                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="border-b border-slate-100 bg-slate-50/50"
                                >
                                    <th
                                        class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider"
                                    >
                                        Medication Details
                                    </th>
                                    <th
                                        class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider"
                                    >
                                        Batch Info
                                    </th>
                                    <th
                                        class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider"
                                    >
                                        Stock Status
                                    </th>
                                    <th
                                        class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider"
                                    >
                                        Expiry Status
                                    </th>
                                    <th
                                        class="px-6 py-3.5 text-xs font-bold text-slate-400 uppercase tracking-wider text-right"
                                    >
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="item in alertItems"
                                    :key="item.id"
                                    class="hover:bg-slate-50/60 transition-colors group"
                                >
                                    <!-- Name -->
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span
                                                class="font-bold text-slate-900 text-sm group-hover:text-indigo-600 transition-colors"
                                            >
                                                {{ item.medication_name }}
                                            </span>
                                            <span
                                                class="text-[10px] text-slate-400 mt-0.5"
                                                >ID: #INV-{{ item.id }}</span
                                            >
                                        </div>
                                    </td>
                                    <!-- Batch -->
                                    <td class="px-6 py-4 text-xs">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 bg-slate-100 text-slate-600 rounded font-mono text-[10px]"
                                        >
                                            {{ item.batch_number || "N/A" }}
                                        </span>
                                    </td>
                                    <!-- Stock Level / Progress -->
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col gap-1.5 w-32">
                                            <div
                                                class="flex items-center justify-between text-xs"
                                            >
                                                <span
                                                    class="font-bold"
                                                    :class="
                                                        item.is_low_stock
                                                            ? 'text-red-600'
                                                            : 'text-slate-700'
                                                    "
                                                >
                                                    {{ item.quantity }} units
                                                </span>
                                                <span
                                                    class="text-slate-400 text-[10px]"
                                                    >/
                                                    {{
                                                        item.reorder_level
                                                    }}</span
                                                >
                                            </div>
                                            <div
                                                class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden"
                                            >
                                                <div
                                                    class="h-full rounded-full transition-all duration-500"
                                                    :class="
                                                        item.is_low_stock
                                                            ? 'bg-red-500'
                                                            : 'bg-emerald-500'
                                                    "
                                                    :style="`width: ${Math.min(100, (item.quantity / (item.reorder_level || 10)) * 100)}%`"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Expiry -->
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-xs font-semibold"
                                                :class="
                                                    item.is_expiring
                                                        ? 'text-amber-600'
                                                        : 'text-slate-700'
                                                "
                                            >
                                                {{
                                                    item.expiry_date
                                                        ? formatDate(
                                                              item.expiry_date,
                                                          )
                                                        : "N/A"
                                                }}
                                            </span>
                                            <span
                                                v-if="item.is_expiring"
                                                class="text-[10px] font-bold text-red-500 mt-0.5 flex items-center gap-0.5"
                                            >
                                                <span
                                                    class="w-1 h-1 rounded-full bg-red-500 animate-ping"
                                                ></span>
                                                {{
                                                    getDaysRemaining(
                                                        item.expiry_date,
                                                    )
                                                }}
                                                days left
                                            </span>
                                            <span
                                                v-else
                                                class="text-[10px] text-emerald-500 font-medium mt-0.5"
                                                >Secure Batch</span
                                            >
                                        </div>
                                    </td>
                                    <!-- Actions -->
                                    <td class="px-6 py-4 text-right">
                                        <Link
                                            :href="
                                                route('admin.inventory.index')
                                            "
                                            class="inline-flex items-center justify-center p-1.5 bg-slate-50 hover:bg-indigo-50 hover:text-indigo-600 text-slate-500 rounded-lg border border-slate-100 transition-all"
                                            title="Update stock level"
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
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                />
                                            </svg>
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="alertItems.length === 0">
                                    <td
                                        colspan="5"
                                        class="px-6 py-16 text-center"
                                    >
                                        <div
                                            class="flex flex-col items-center justify-center"
                                        >
                                            <div
                                                class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-600 mb-4 shadow-inner"
                                            >
                                                <svg
                                                    class="w-8 h-8"
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
                                            <p
                                                class="text-base font-extrabold text-slate-900"
                                            >
                                                All Systems Clear
                                            </p>
                                            <p
                                                class="text-xs text-slate-400 mt-1 max-w-xs"
                                            >
                                                No medications are currently
                                                below reorder thresholds or
                                                nearing expiry dates.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pharmacy Health & Stats -->
                <div class="space-y-6">
                    <!-- Health Check Index -->
                    <div
                        class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100"
                    >
                        <h3 class="text-base font-extrabold text-slate-900">
                            Stock Security Index
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Overview of active batch integrity.
                        </p>

                        <!-- Big Circular Health Percentage -->
                        <div
                            class="flex flex-col items-center justify-center py-6"
                        >
                            <div
                                class="relative w-32 h-32 flex items-center justify-center"
                            >
                                <!-- Outer ring svg -->
                                <svg
                                    class="w-full h-full transform -rotate-90"
                                    viewBox="0 0 100 100"
                                >
                                    <circle
                                        class="text-slate-100"
                                        stroke-width="8"
                                        stroke="currentColor"
                                        fill="transparent"
                                        r="40"
                                        cx="50"
                                        cy="50"
                                    />
                                    <circle
                                        :class="healthScoreColorClass"
                                        stroke-width="8"
                                        stroke-linecap="round"
                                        stroke="currentColor"
                                        fill="transparent"
                                        r="40"
                                        cx="50"
                                        cy="50"
                                        :stroke-dasharray="2 * Math.PI * 40"
                                        :stroke-dashoffset="
                                            (1 - healthScore / 100) *
                                            (2 * Math.PI * 40)
                                        "
                                        class="transition-all duration-1000 ease-out"
                                    />
                                </svg>
                                <div
                                    class="absolute flex flex-col items-center justify-center"
                                >
                                    <span
                                        class="text-3xl font-extrabold text-slate-900"
                                        >{{ healthScore }}%</span
                                    >
                                    <span
                                        class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mt-0.5"
                                        >Safety</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Mini stats list -->
                        <div class="space-y-3 mt-4">
                            <div
                                class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/50 text-xs"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-2.5 h-2.5 rounded-full bg-emerald-500 block"
                                    ></span>
                                    <span class="text-slate-600 font-medium"
                                        >Safe Items</span
                                    >
                                </div>
                                <span class="font-bold text-slate-900">{{
                                    stats.totalMedications -
                                    stats.lowStockAlerts
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/50 text-xs"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-2.5 h-2.5 rounded-full bg-red-500 block"
                                    ></span>
                                    <span class="text-slate-600 font-medium"
                                        >Low Stock Items</span
                                    >
                                </div>
                                <span class="font-bold text-slate-900">{{
                                    stats.lowStockAlerts
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/50 text-xs"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-2.5 h-2.5 rounded-full bg-amber-500 block"
                                    ></span>
                                    <span class="text-slate-600 font-medium"
                                        >Near Expiry</span
                                    >
                                </div>
                                <span class="font-bold text-slate-900">{{
                                    stats.expiringSoon
                                }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div
                        class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100"
                    >
                        <h3 class="text-base font-extrabold text-slate-900">
                            Task Control
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Quick shortcuts to vital workflows.
                        </p>

                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <Link
                                :href="route('admin.inventory.index')"
                                class="flex flex-col items-center justify-center p-4 rounded-xl bg-indigo-50/50 hover:bg-indigo-50 border border-indigo-100/30 text-indigo-700 transition-all text-center group"
                            >
                                <svg
                                    class="w-6 h-6 mb-2 group-hover:scale-115 transition-transform"
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
                                <span class="text-[11px] font-bold"
                                    >Add Inventory</span
                                >
                            </Link>

                            <Link
                                :href="route('admin.refills.index')"
                                class="flex flex-col items-center justify-center p-4 rounded-xl bg-emerald-50/50 hover:bg-emerald-50 border border-emerald-100/30 text-emerald-700 transition-all text-center group"
                            >
                                <svg
                                    class="w-6 h-6 mb-2 group-hover:scale-115 transition-transform"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                    />
                                </svg>
                                <span class="text-[11px] font-bold"
                                    >Refill Requests</span
                                >
                            </Link>

                            <Link
                                :href="route('admin.users.index')"
                                class="flex flex-col items-center justify-center p-4 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 transition-all text-center group"
                            >
                                <svg
                                    class="w-6 h-6 mb-2 group-hover:scale-115 transition-transform"
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
                                <span class="text-[11px] font-bold"
                                    >Manage Users</span
                                >
                            </Link>

                            <Link
                                :href="route('admin.alerts.index')"
                                class="flex flex-col items-center justify-center p-4 rounded-xl bg-rose-50/50 hover:bg-rose-50 border border-rose-100/30 text-rose-700 transition-all text-center group"
                            >
                                <svg
                                    class="w-6 h-6 mb-2 group-hover:scale-115 transition-transform"
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
                                <span class="text-[11px] font-bold"
                                    >Active Alerts</span
                                >
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PieChart from "@/Components/PieChart.vue";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { route as ziggyRoute } from "ziggy-js";
import { Ziggy } from "@/ziggy";

// Custom route helper
const route = (name, params, absolute, config = Ziggy) =>
    ziggyRoute(name, params, absolute, config);

const props = defineProps({
    user: Object,
    tenant: Object,
    stats: {
        type: Object,
        default: () => ({
            totalMedications: 0,
            totalInventory: 0,
            lowStockAlerts: 0,
            expiringSoon: 0,
        }),
    },
    alertItems: {
        type: Array,
        default: () => [],
    },
});

// Debug: Log actual stats values
console.log("📊 Dashboard Stats:", {
    totalMedications: props.stats?.totalMedications,
    totalInventory: props.stats?.totalInventory,
    lowStockAlerts: props.stats?.lowStockAlerts,
    expiringSoon: props.stats?.expiringSoon,
});

// Format today's date elegantly
const formattedToday = computed(() => {
    const options = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    };
    return new Date().toLocaleDateString("en-US", options);
});

// Calculate inventory safety percentage
const healthScore = computed(() => {
    const total = props.stats.totalMedications || 1;
    const compromised = props.stats.lowStockAlerts + props.stats.expiringSoon;
    const secure = Math.max(0, total - compromised);
    return Math.round((secure / total) * 100);
});

// Dynamic ring stroke colors depending on health index
const healthScoreColorClass = computed(() => {
    if (healthScore.value >= 85) return "text-emerald-500";
    if (healthScore.value >= 60) return "text-amber-500";
    return "text-red-500";
});

// Format dates nicely
const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric",
    });
};

// Calculate exact days remaining until expiry
const getDaysRemaining = (expiryDateString) => {
    if (!expiryDateString) return 0;
    const expiry = new Date(expiryDateString);
    const today = new Date();
    const diffTime = expiry.getTime() - today.getTime();
    return Math.max(0, Math.ceil(diffTime / (1000 * 60 * 60 * 24)));
};

// Stock Status Chart Data
const stockChartData = computed(() => {
    const safeStock = Math.max(
        0,
        props.stats.totalMedications - props.stats.lowStockAlerts,
    );
    const lowStock = props.stats.lowStockAlerts;

    const data = {
        labels: ["Healthy Stock", "Low Stock"],
        datasets: [
            {
                data: [safeStock, lowStock],
                backgroundColor: [
                    "rgba(16, 185, 129, 0.8)", // Emerald
                    "rgba(239, 68, 68, 0.8)", // Red
                ],
                borderColor: ["rgba(16, 185, 129, 1)", "rgba(239, 68, 68, 1)"],
                borderWidth: 2,
            },
        ],
    };

    console.log("📊 Stock Chart Data:", data);
    return data;
});

// Expiry Timeline Chart Data
const expiryChartData = computed(() => {
    const safeItems = Math.max(
        0,
        props.stats.totalMedications - props.stats.expiringSoon,
    );
    const expiringSoon = props.stats.expiringSoon;

    const data = {
        labels: ["Secure (>30 days)", "Expiring Soon (<30 days)"],
        datasets: [
            {
                data: [safeItems, expiringSoon],
                backgroundColor: [
                    "rgba(59, 130, 246, 0.8)", // Blue
                    "rgba(251, 191, 36, 0.8)", // Amber
                ],
                borderColor: ["rgba(59, 130, 246, 1)", "rgba(251, 191, 36, 1)"],
                borderWidth: 2,
            },
        ],
    };

    console.log("📊 Expiry Chart Data:", data);
    return data;
});
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
