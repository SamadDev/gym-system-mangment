<script setup>
import {reactive, onMounted, computed} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import {translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const warehouseId = route.params.id;

const state = reactive({
    warehouse: null,
    loading: true,
});

const warehouseNumber = computed(() => {
    return state.warehouse ? `#${state.warehouse.id.toString().padStart(4, '0')}` : '';
});

onMounted(async () => {
    await fetchWarehouseData();
});

const fetchWarehouseData = async () => {
    try {
        const res = await axiosRequest.get("/admin/warehouses/" + warehouseId, {});
        state.warehouse = res.data.data;
    } catch (error) {
        console.log(error.response);
    } finally {
        state.loading = false;
    }
};

const breadcrumbItems = [
    {label: translate('warehouses'), url: '/admin/warehouses'},
    {label: translate('view'), url: null}
];

</script>

<template>
    <!-- Loading state -->
    <div v-if="state.loading" class="flex items-center justify-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
    </div>

    <!-- Main content -->
    <div v-else class="space-y-6 warehouse-content">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ translate('warehouse') }} {{ warehouseNumber }}
                    </h1>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center bg-blue-200 dark:bg-blue-800">
                            <svg class="w-2.5 h-2.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <span class="capitalize">{{ translate('warehouse') }}</span>
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-400">{{ state.warehouse?.name }}</p>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('basic_information') }}</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                            <div class="md:col-span-2 xl:col-span-3">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ translate('name') }}</label>
                                <p class="text-gray-900 dark:text-white font-medium">{{ state.warehouse?.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('address_information') }}</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ translate('address_line_1') }}</label>
                                <p class="text-gray-900 dark:text-white">{{ state.warehouse?.address_line_1 }}</p>
                            </div>
                            <div class="md:col-span-2" v-if="state.warehouse?.address_line_2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ translate('address_line_2') }}</label>
                                <p class="text-gray-900 dark:text-white">{{ state.warehouse?.address_line_2 }}</p>
                            </div>
                            <div v-if="state.warehouse?.street">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ translate('street') }}</label>
                                <p class="text-gray-900 dark:text-white">{{ state.warehouse?.street }}</p>
                            </div>
                            <div v-if="state.warehouse?.postal_code">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ translate('postal_code') }}</label>
                                <p class="text-gray-900 dark:text-white">{{ state.warehouse?.postal_code }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('contact_information') }}</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="state.warehouse?.phone_number">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ translate('phone_number') }}</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ state.warehouse?.phone_number }}</p>
                                </div>
                            </div>
                            <div v-if="state.warehouse?.email">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ translate('email') }}</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ state.warehouse?.email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="xl:col-span-1 space-y-6">
                <!-- Location Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('location_information') }}</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ translate('country') }}</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ state.warehouse?.country?.name }}</p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ translate('city') }}</label>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ state.warehouse?.city?.name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-primary/10 dark:bg-primary/20 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-primary dark:text-primary mb-4">{{ translate('warehouse_summary') }}</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ translate('warehouse_id') }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ warehouseNumber }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ translate('created_by') }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ state.warehouse?.user?.name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ translate('status') }}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                {{ translate('active') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Print styles for warehouse view */
@media print {
    /* Hide navigation and UI elements */
    .sidebar,
    .header,
    nav,
    .breadcrumb,
    .btn,
    button,
    .collapse-icon,
    .main-logo,
    .perfect-scrollbar,
    .nav-item,
    .nav-link,
    .group,
    .animate-spin,
    .icon-carets-down,
    .icon-menu-dashboard,
    .icon-shopping-cart,
    .icon-menu-elements,
    .icon-menu-authentication,
    .icon-menu-invoice,
    .icon-menu-datatables,
    .icon-menu-notes,
    .icon-globe,
    .icon-minus,
    .icon-caret-down,
    .icon-menu-users,
    .icon-menu-charts,
    .icon-menu-contacts,
    .icon-menu-safe,
    .icon-edit,
    .icon-printer,
    .icon-user,
    .icon-calendar,
    .icon-dollar-sign,
    .icon-truck,
    .icon-package,
    .icon-check-circle,
    .icon-clock,
    .icon-x-circle,
    .icon-alert-circle,
    .icon-external-link,
    .edit-button-text,
    .print-button-text {
        display: none !important;
    }

    /* Hide breadcrumb and action buttons */
    .flex.items-center.justify-between.mb-6 {
        display: none !important;
    }

    /* Reset page layout for print */
    body {
        font-size: 11pt !important;
        line-height: 1.4 !important;
        margin: 0 !important;
        padding: 15px !important;
        background: white !important;
        color: #333 !important;
    }

    /* Main container adjustments */
    .container,
    .main-container,
    .page-container {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Warehouse content styling */
    .warehouse-content {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Grid layout - maintain structure but optimize for print */
    .grid.grid-cols-1.xl\:grid-cols-3 {
        display: grid !important;
        grid-template-columns: 2fr 1fr !important;
        gap: 20px !important;
    }

    .xl\:col-span-2 {
        grid-column: 1 !important;
    }

    .xl\:col-span-1 {
        grid-column: 2 !important;
    }

    /* Card styling - maintain visual design */
    .bg-white,
    .dark\:bg-gray-800 {
        background: white !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        margin-bottom: 20px !important;
        padding: 20px !important;
        page-break-inside: avoid !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    }

    /* Typography - maintain hierarchy */
    h1, h2, h3, h4, h5, h6 {
        color: #111827 !important;
        font-weight: 600 !important;
        margin-bottom: 8px !important;
    }

    h1 {
        font-size: 18pt !important;
    }

    h2 {
        font-size: 16pt !important;
    }

    h3 {
        font-size: 14pt !important;
    }

    /* Text colors - maintain contrast */
    .text-gray-900,
    .dark\:text-white {
        color: #111827 !important;
    }

    .text-gray-600,
    .dark\:text-gray-400 {
        color: #6b7280 !important;
    }

    .text-gray-500,
    .dark\:text-gray-400 {
        color: #9ca3af !important;
    }

    /* Summary section - maintain highlighting */
    .bg-primary\/10 {
        background: #dbeafe !important;
        border: 1px solid #93c5fd !important;
        color: #1e40af !important;
        border-radius: 6px !important;
    }

    /* Maintain rounded corners for modern look */
    .rounded-lg {
        border-radius: 8px !important;
    }

    .rounded-md {
        border-radius: 6px !important;
    }

    .rounded-full {
        border-radius: 50% !important;
    }

    /* Page breaks */
    .page-break-before {
        page-break-before: always !important;
    }

    .page-break-after {
        page-break-after: always !important;
    }

    .page-break-inside-avoid {
        page-break-inside: avoid !important;
    }

    /* Maintain spacing */
    .space-y-6 > * + * {
        margin-top: 24px !important;
    }

    .space-y-4 > * + * {
        margin-top: 16px !important;
    }

    .space-y-3 > * + * {
        margin-top: 12px !important;
    }

    /* Maintain grid layouts within cards */
    .grid.grid-cols-1.md\:grid-cols-2.xl\:grid-cols-3 {
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
    }

    .md\:col-span-2 {
        grid-column: span 2 !important;
    }

    .xl\:col-span-3 {
        grid-column: span 2 !important;
    }
}
</style>
