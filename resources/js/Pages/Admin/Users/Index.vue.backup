<template>
    <AdminLayout page-title="Inventory Management">
        <div class="space-y-6">
            <!-- Header Actions -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Inventory</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Manage your pharmacy inventory, stock levels, and
                        batches.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button
                        variant="secondary"
                        @click="router.visit(route('admin.inventory.index'))"
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
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                        Refresh
                    </Button>
                    <Button variant="success" @click="openRefillSearchModal">
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
                        Refill Inventory
                    </Button>
                    <Button variant="primary" @click="openAddModal">
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
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                            />
                        </svg>
                        Add New Product
                    </Button>
                </div>
            </div>

            <!-- Filters Panel -->
            <Card padding="normal">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by medication name or batch number..."
                            @input="handleSearch"
                        />
                    </div>
                    <div>
                        <select
                            v-model="statusFilter"
                            @change="handleFilterChange"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm py-2 px-3 bg-white"
                        >
                            <option value="">All Stock Status</option>
                            <option value="low_stock">Low Stock</option>
                            <option value="expiring">Expiring Soon</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                    <div>
                        <Button
                            variant="ghost"
                            full-width
                            @click="clearFilters"
                            v-if="searchQuery || statusFilter"
                        >
                            Clear Filters
                        </Button>
                    </div>
                </div>
            </Card>

            <!-- Inventory Table -->
            <Card padding="none">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/50">
                                <th
                                    class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                >
                                    Medication
                                </th>
                                <th
                                    class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                >
                                    Batch Info
                                </th>
                                <th
                                    class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                >
                                    Stock level
                                </th>
                                <th
                                    class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                >
                                    Expiry
                                </th>
                                <th
                                    class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3.5 text-xs font-semibold text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="item in inventory.data"
                                :key="item.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="px-6 py-4">
                                    <div>
                                        <p
                                            class="font-semibold text-gray-900 text-sm"
                                        >
                                            {{ item.medication_name }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            {{ item.medication_dosage }} •
                                            {{ item.medication_form }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-mono text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded w-max"
                                            >#{{
                                                item.batch_number || "N/A"
                                            }}</span
                                        >
                                        <span
                                            class="text-xs text-gray-400 mt-1"
                                            v-if="item.supplier"
                                            >Supplier: {{ item.supplier }}</span
                                        >
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span
                                        :class="
                                            item.is_low_stock
                                                ? 'text-red-600 font-bold'
                                                : 'text-gray-900 font-semibold'
                                        "
                                    >
                                        {{ item.quantity }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        / {{ item.reorder_level }}</span
                                    >
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span
                                        :class="{
                                            'text-red-600 font-medium':
                                                item.is_expired,
                                            'text-yellow-600 font-medium':
                                                item.is_expiring,
                                            'text-gray-500':
                                                !item.is_expired &&
                                                !item.is_expiring,
                                        }"
                                    >
                                        {{ item.expiry_date || "N/A" }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge
                                        v-if="item.is_expired"
                                        variant="danger"
                                        >Expired</Badge
                                    >
                                    <Badge
                                        v-else-if="item.is_low_stock"
                                        variant="danger"
                                        >Low Stock</Badge
                                    >
                                    <Badge
                                        v-else-if="item.is_expiring"
                                        variant="warning"
                                        >Expiring Soon</Badge
                                    >
                                    <Badge v-else variant="success">Good</Badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="openEditModal(item)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 transform hover:scale-105"
                                        >
                                            <svg
                                                class="w-3.5 h-3.5"
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
                                            Edit
                                        </button>
                                        <button
                                            @click="confirmDelete(item)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-lg shadow-sm hover:shadow-md transition-all duration-200 transform hover:scale-105"
                                        >
                                            <svg
                                                class="w-3.5 h-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <EmptyState
                        v-if="inventory.data.length === 0"
                        title="No inventory items found"
                        description="Try modifying your search filter or add a new inventory item."
                    >
                        <template #action>
                            <Button variant="primary" @click="openAddModal"
                                >Add Inventory Item</Button
                            >
                        </template>
                    </EmptyState>
                </div>

                <!-- Pagination -->
                <div
                    v-if="inventory.data.length > 0"
                    class="px-6 py-4 border-t border-gray-200 flex items-center justify-between"
                >
                    <div class="text-sm text-gray-500">
                        Showing {{ inventory.from }} to {{ inventory.to }} of
                        {{ inventory.total }} results
                    </div>
                    <div class="flex items-center gap-1.5">
                        <Button
                            variant="ghost"
                            size="sm"
                            :disabled="!inventory.prev_page_url"
                            @click="changePage(inventory.current_page - 1)"
                        >
                            Previous
                        </Button>

                        <div class="flex items-center gap-1">
                            <span
                                v-for="page in pageNumbers"
                                :key="page"
                                class="px-3 py-1 rounded text-xs font-semibold cursor-pointer select-none transition-colors"
                                :class="
                                    page === inventory.current_page
                                        ? 'bg-primary text-white'
                                        : page === '...'
                                          ? 'text-gray-400 cursor-default'
                                          : 'text-gray-600 hover:bg-gray-100'
                                "
                                @click="page !== '...' && changePage(page)"
                            >
                                {{ page }}
                            </span>
                        </div>

                        <Button
                            variant="ghost"
                            size="sm"
                            :disabled="!inventory.next_page_url"
                            @click="changePage(inventory.current_page + 1)"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Refill Search Modal -->
        <Modal
            :show="showRefillSearchModal"
            title="Select Product to Refill"
            size="lg"
            @close="closeRefillSearchModal"
        >
            <div
                class="p-8 space-y-6 bg-gradient-to-br from-blue-50/30 via-white to-purple-50/20"
            >
                <div class="relative">
                    <label
                        class="block text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2"
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
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        Search for Product to Refill
                    </label>
                    <div class="relative group">
                        <input
                            v-model="refillSearchQuery"
                            @input="searchInventoryItems"
                            @focus="showRefillDropdown = true"
                            type="text"
                            placeholder="Type medication name, batch number, or dosage..."
                            class="block w-full rounded-2xl border-2 border-gray-200 shadow-lg focus:border-blue-500 focus:ring-4 focus:ring-blue-500/20 sm:text-sm py-4 px-5 pr-14 transition-all duration-300 bg-white hover:border-blue-300 hover:shadow-xl"
                        />
                        <div
                            class="absolute right-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg group-focus-within:scale-110 transition-transform duration-200"
                        >
                            <svg
                                class="w-4 h-4 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                    </div>

                    <!-- Beautiful Searchable Dropdown - Fixed positioning -->
                    <div
                        v-if="
                            showRefillDropdown &&
                            filteredInventoryItems.length > 0
                        "
                        class="mt-4 bg-white border-2 border-blue-100 rounded-2xl shadow-2xl max-h-96 overflow-hidden animate-slideDown"
                    >
                        <div class="max-h-96 overflow-y-auto custom-scrollbar">
                            <div
                                v-for="item in filteredInventoryItems"
                                :key="item.id"
                                @click="selectItemForRefill(item)"
                                class="px-5 py-4 hover:bg-gradient-to-r hover:from-blue-50 hover:via-blue-100/50 hover:to-purple-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-all duration-200 group relative overflow-hidden"
                            >
                                <!-- Hover effect bar -->
                                <div
                                    class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 to-purple-600 transform scale-y-0 group-hover:scale-y-100 transition-transform duration-300"
                                ></div>

                                <div
                                    class="flex items-center justify-between pl-2"
                                >
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-md group-hover:scale-110 transition-transform duration-200 flex-shrink-0"
                                            >
                                                <svg
                                                    class="w-5 h-5 text-white"
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
                                            <div class="min-w-0 flex-1">
                                                <p
                                                    class="font-bold text-gray-900 text-base group-hover:text-blue-700 transition-colors truncate"
                                                >
                                                    {{ item.medication_name }}
                                                </p>
                                                <div
                                                    class="flex flex-wrap items-center gap-2 mt-1"
                                                >
                                                    <span
                                                        class="px-2 py-0.5 bg-gray-100 rounded-md text-xs text-gray-700"
                                                        >{{
                                                            item.medication_dosage
                                                        }}</span
                                                    >
                                                    <span class="text-gray-400"
                                                        >•</span
                                                    >
                                                    <span
                                                        class="px-2 py-0.5 bg-gray-100 rounded-md text-xs text-gray-700"
                                                        >{{
                                                            item.medication_form
                                                        }}</span
                                                    >
                                                    <span
                                                        v-if="item.batch_number"
                                                        class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md font-mono text-xs"
                                                    >
                                                        #{{ item.batch_number }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right ml-4 flex-shrink-0">
                                        <p
                                            class="text-sm font-bold mb-1 whitespace-nowrap"
                                            :class="
                                                item.is_low_stock
                                                    ? 'text-red-600'
                                                    : 'text-gray-900'
                                            "
                                        >
                                            {{ item.quantity }} units
                                        </p>
                                        <Badge
                                            v-if="item.is_low_stock"
                                            variant="danger"
                                            >Low Stock</Badge
                                        >
                                        <Badge
                                            v-else-if="item.is_expiring"
                                            variant="warning"
                                            >Expiring Soon</Badge
                                        >
                                        <Badge v-else variant="success"
                                            >In Stock</Badge
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="
                            showRefillDropdown &&
                            refillSearchQuery &&
                            filteredInventoryItems.length === 0
                        "
                        class="mt-4 text-center py-12 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200"
                    >
                        <svg
                            class="w-16 h-16 mx-auto mb-3 text-gray-300"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <p class="text-gray-500 font-medium">
                            No matching products found
                        </p>
                        <p class="text-sm text-gray-400 mt-1">
                            Try a different search term
                        </p>
                    </div>

                    <div
                        v-if="
                            !refillSearchQuery || refillSearchQuery.length < 2
                        "
                        class="mt-4 text-center py-12 bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl border-2 border-blue-100"
                    >
                        <svg
                            class="w-16 h-16 mx-auto mb-3 text-blue-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <p class="text-gray-700 font-medium">
                            Start typing to search
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Enter at least 2 characters
                        </p>
                    </div>
                </div>
            </div>

            <template #footer>
                <div
                    class="flex items-center justify-end gap-3 px-8 py-5 bg-gradient-to-r from-gray-50 via-white to-gray-50 border-t border-gray-100"
                >
                    <Button variant="ghost" @click="closeRefillSearchModal"
                        >Cancel</Button
                    >
                </div>
            </template>
        </Modal>

        <!-- Add/Edit Modal -->
        <Modal
            :show="showModal"
            :title="editingItem ? 'Update Inventory Item' : 'Add New Product'"
            size="2xl"
            @close="closeModal"
        >
            <form
                @submit.prevent="submitForm"
                class="space-y-6 p-8 bg-gradient-to-br from-white via-blue-50/20 to-purple-50/10"
            >
                <!-- Product Information Section -->
                <div class="space-y-5">
                    <div
                        class="flex items-center gap-3 pb-3 border-b border-gray-200"
                    >
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg"
                        >
                            <svg
                                class="w-5 h-5 text-white"
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
                            <h3 class="text-lg font-bold text-gray-900">
                                Product Information
                            </h3>
                            <p class="text-sm text-gray-500">
                                Enter the medication details
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <Input
                            v-model="form.medication_name"
                            label="Medication Name"
                            required
                            placeholder="e.g., Paracetamol"
                            :error="form.errors.medication_name"
                        />
                        <Input
                            v-model="form.medication_dosage"
                            label="Dosage"
                            required
                            placeholder="e.g., 500mg"
                            :error="form.errors.medication_dosage"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                            >
                                Form <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.medication_form"
                                required
                                class="block w-full rounded-xl border-2 border-gray-200 shadow-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm py-2.5 px-3.5 bg-white hover:border-blue-300 transition-all duration-200"
                            >
                                <option value="">Select form</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Injection">Injection</option>
                                <option value="Cream">Cream</option>
                                <option value="Drops">Drops</option>
                                <option value="Inhaler">Inhaler</option>
                                <option value="Powder">Powder</option>
                            </select>
                            <p
                                v-if="form.errors.medication_form"
                                class="mt-2 text-sm text-red-600"
                            >
                                {{ form.errors.medication_form }}
                            </p>
                        </div>
                        <Input
                            v-model="form.manufacturer"
                            label="Manufacturer (Optional)"
                            placeholder="e.g., Pfizer"
                            :error="form.errors.manufacturer"
                        />
                    </div>
                </div>

                <!-- Stock Information Section -->
                <div class="space-y-5">
                    <div
                        class="flex items-center gap-3 pb-3 border-b border-gray-200"
                    >
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center shadow-lg"
                        >
                            <svg
                                class="w-5 h-5 text-white"
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
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                Stock Details
                            </h3>
                            <p class="text-sm text-gray-500">
                                Quantity and batch information
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <Input
                            v-model="form.quantity"
                            label="Quantity"
                            type="number"
                            required
                            placeholder="0"
                            :error="form.errors.quantity"
                        />
                        <Input
                            v-model="form.reorder_level"
                            label="Reorder Level"
                            type="number"
                            placeholder="Minimum stock"
                            :error="form.errors.reorder_level"
                        />
                        <Input
                            v-model="form.batch_number"
                            label="Batch Number"
                            placeholder="e.g., BATCH2024-001"
                            :error="form.errors.batch_number"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <Input
                            v-model="form.expiry_date"
                            label="Expiry Date"
                            type="date"
                            :error="form.errors.expiry_date"
                        />
                        <Input
                            v-model="form.supplier"
                            label="Supplier"
                            placeholder="Supplier name"
                            :error="form.errors.supplier"
                        />
                    </div>
                </div>

                <!-- Pricing Section -->
                <div class="space-y-5">
                    <div
                        class="flex items-center gap-3 pb-3 border-b border-gray-200"
                    >
                        <div
                            class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center shadow-lg"
                        >
                            <svg
                                class="w-5 h-5 text-white"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                Pricing
                            </h3>
                            <p class="text-sm text-gray-500">
                                Cost and selling prices
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <Input
                            v-model="form.cost_price"
                            label="Cost Price"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            :error="form.errors.cost_price"
                        />
                        <Input
                            v-model="form.selling_price"
                            label="Selling Price"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            :error="form.errors.selling_price"
                        />
                    </div>
                </div>
            </form>

            <template #footer>
                <div
                    class="flex items-center justify-between px-8 py-5 bg-gradient-to-r from-gray-50 via-white to-gray-50 border-t border-gray-100"
                >
                    <p class="text-sm text-gray-500">
                        <span class="text-red-500">*</span> Required fields
                    </p>
                    <div class="flex items-center gap-3">
                        <Button variant="ghost" @click="closeModal"
                            >Cancel</Button
                        >
                        <Button
                            variant="primary"
                            @click="submitForm"
                            :loading="form.processing"
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
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                            {{ editingItem ? "Update Product" : "Add Product" }}
                        </Button>
                    </div>
                </div>
            </template>
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
import Card from "@/Components/UI/Card.vue";
import Button from "@/Components/UI/Button.vue";
import Input from "@/Components/UI/Input.vue";
import Badge from "@/Components/UI/Badge.vue";
import Modal from "@/Components/UI/Modal.vue";
import EmptyState from "@/Components/UI/EmptyState.vue";

const route = (name, params, absolute, config = Ziggy) =>
    ziggyRoute(name, params, absolute, config);

const props = defineProps({
    inventory: Object,
    medications: Array,
    filters: Object,
});

const searchQuery = ref(props.filters.search || "");
const statusFilter = ref(props.filters.status || "");
const showModal = ref(false);
const editingItem = ref(null);

// Refill Inventory State
const showRefillSearchModal = ref(false);
const refillSearchQuery = ref("");
const showRefillDropdown = ref(false);
const filteredInventoryItems = ref([]);

const form = useForm({
    medication_name: "",
    medication_dosage: "",
    medication_form: "",
    manufacturer: "",
    quantity: "",
    reorder_level: "",
    batch_number: "",
    expiry_date: "",
    supplier: "",
    cost_price: "",
    selling_price: "",
});

const refillForm = useForm({
    quantity: "",
    batch_number: "",
    expiry_date: "",
    supplier: "",
    cost_price: "",
    selling_price: "",
});

const { pageNumbers } = usePagination(computed(() => props.inventory));

const handleSearch = debounce(() => {
    router.get(
        route("admin.inventory.index"),
        {
            search: searchQuery.value,
            status: statusFilter.value,
        },
        { preserveState: true, preserveScroll: true },
    );
}, 500);

const handleFilterChange = () => {
    router.get(
        route("admin.inventory.index"),
        {
            search: searchQuery.value,
            status: statusFilter.value,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const clearFilters = () => {
    searchQuery.value = "";
    statusFilter.value = "";
    router.get(route("admin.inventory.index"));
};

const changePage = (page) => {
    router.get(
        route("admin.inventory.index"),
        {
            page,
            search: searchQuery.value,
            status: statusFilter.value,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const openAddModal = () => {
    editingItem.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (item) => {
    editingItem.value = item;
    form.medication_name = item.medication_name;
    form.medication_dosage = item.medication_dosage;
    form.medication_form = item.medication_form;
    form.manufacturer = item.manufacturer || "";
    form.quantity = item.quantity;
    form.reorder_level = item.reorder_level;
    form.batch_number = item.batch_number;
    form.expiry_date = item.expiry_date;
    form.supplier = item.supplier;
    form.cost_price = item.cost_price;
    form.selling_price = item.selling_price;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingItem.value = null;
    form.reset();
    form.clearErrors();
};

const submitForm = () => {
    if (editingItem.value) {
        form.put(route("admin.inventory.update", editingItem.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route("admin.inventory.store"), {
            onSuccess: () => closeModal(),
        });
    }
};

const confirmDelete = (item) => {
    if (confirm(`Are you sure you want to delete ${item.medication_name}?`)) {
        router.delete(route("admin.inventory.destroy", item.id));
    }
};

// Refill Inventory Functions - Opens search modal, then edit modal
const openRefillSearchModal = () => {
    showRefillSearchModal.value = true;
    refillSearchQuery.value = "";
    filteredInventoryItems.value = [];
    showRefillDropdown.value = false;
};

const closeRefillSearchModal = () => {
    showRefillSearchModal.value = false;
    showRefillDropdown.value = false;
    refillSearchQuery.value = "";
    filteredInventoryItems.value = [];
};

const searchInventoryItems = debounce(() => {
    if (refillSearchQuery.value.length < 2) {
        filteredInventoryItems.value = [];
        showRefillDropdown.value = false;
        return;
    }

    const query = refillSearchQuery.value.toLowerCase();
    filteredInventoryItems.value = props.inventory.data.filter(
        (item) =>
            item.medication_name.toLowerCase().includes(query) ||
            (item.batch_number &&
                item.batch_number.toLowerCase().includes(query)) ||
            (item.medication_dosage &&
                item.medication_dosage.toLowerCase().includes(query)),
    );
    showRefillDropdown.value = true;
}, 300);

const selectItemForRefill = (item) => {
    closeRefillSearchModal();
    // Open edit modal with the selected item
    openEditModal(item);
};
</script>

<style scoped>
/* Custom scrollbar for dropdown */
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #3b82f6, #2563eb);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #2563eb, #1d4ed8);
}
</style>
