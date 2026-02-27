<script setup>
import {reactive, onMounted, computed} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import {translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import {useRoute, useRouter} from 'vue-router';
import EditButton from "../components/EditButton.vue";
import {can} from "../../utils/functions.js";

const router = useRouter();
const route = useRoute();
const statusId = route.params.id;

const state = reactive({
    status: null,
    loading: true,
    error: null,
});

onMounted(async () => {
    await fetchStatusData();
});

const fetchStatusData = async () => {
    try {
        state.loading = true;
        const res = await axiosRequest.get(`/admin/statuses/${statusId}`);
        state.status = res.data.data;
    } catch (error) {
        state.error = error.response?.data?.message || 'Failed to fetch status details.';
        console.error(error);
    } finally {
        state.loading = false;
    }
};

const breadcrumbItems = computed(() => [
    {label: translate('warehouses'), url: '/admin/warehouses'},
    {label: translate('statuses'), url: '/admin/statuses'},
    {label: state.status ? state.status.name : translate('loading...'), url: null}
]);

// Extract types from comma-separated string
const statusTypes = computed(() => {
    if (!state.status?.type) return [];
    return state.status.type.split(',').map(type => type.trim());
});

// Get badge class for each type
const getTypeBadgeClass = (type) => {
    const classes = {
        'invoice': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'payment': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'safe_transaction': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        'expense': 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'order': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'
    };
    return classes[type] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

// Get icon for each type
const getTypeIcon = (type) => {
    const icons = {
        'invoice': 'fas fa-file-invoice',
        'payment': 'fas fa-credit-card',
        'safe_transaction': 'fas fa-vault',
        'expense': 'fas fa-receipt',
        'order': 'fas fa-shopping-cart'
    };
    return icons[type] || 'fas fa-circle';
};
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <Breadcrumb :items="breadcrumbItems"/>
            <div class="flex gap-2">
                <edit-button v-if="can('edit_status')" @click="router.push(`/admin/statuses/${statusId}/edit`)"/>
            </div>
        </div>

        <div v-if="state.loading" class="flex justify-center items-center h-64">
            <span class="animate-spin border-4 border-primary border-l-transparent rounded-full w-12 h-12"></span>
        </div>

        <div v-else-if="state.error" class="text-center text-red-500">
            {{ state.error }}
        </div>

        <div v-else class="space-y-6 status-content">
            <!-- Status Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <i class="fas fa-flag text-blue-600 dark:text-blue-400"></i>
                        {{ translate('basic_information') }}
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 mb-1">{{ translate('name') }}:</p>
                            <p class="font-medium text-gray-900 dark:text-white text-lg">{{ state.status.name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 mb-2">{{ translate('type') }}:</p>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="type in statusTypes" :key="type" 
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="getTypeBadgeClass(type)">
                                    <i :class="getTypeIcon(type)" class="mr-1"></i>
                                    {{ translate(type) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Color Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                        <i class="fas fa-palette text-purple-600 dark:text-purple-400"></i>
                        {{ translate('color_information') }}
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 mb-2">{{ translate('color_preview') }}:</p>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium" 
                                      :style="`background-color: ${state.status.color}20; color: ${state.status.color};`">
                                    <span class="w-2 h-2 rounded-full mr-2" :style="`background-color: ${state.status.color};`"></span>
                                    {{ state.status.name }}
                                </span>
                            </div>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 mb-2">{{ translate('color_code') }}:</p>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded border border-gray-300 dark:border-gray-600" 
                                     :style="`background-color: ${state.status.color};`"></div>
                                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm font-mono">{{ state.status.color }}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Translations -->
            <div v-if="state.status.translations && Object.keys(state.status.translations).length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-language text-indigo-600 dark:text-indigo-400"></i>
                    {{ translate('translations') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="(translation, langId) in state.status.translations" :key="langId" class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-language text-indigo-600 dark:text-indigo-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ langId === '2' ? 'Kurdish' : langId === '3' ? 'Arabic' : `Language ${langId}` }}
                            </p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ translation }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
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

    .flex.items-center.justify-between.mb-6 {
        display: none !important;
    }

    body {
        font-size: 11pt !important;
        line-height: 1.4 !important;
        margin: 0 !important;
        padding: 15px !important;
        background: white !important;
        color: #333 !important;
    }

    .container,
    .main-container,
    .page-container {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .status-content {
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

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

    .space-y-3 > div {
        border: 1px solid #e5e7eb !important;
        border-radius: 6px !important;
        margin-bottom: 12px !important;
        padding: 15px !important;
        page-break-inside: avoid !important;
        background: #f9fafb !important;
    }

    .panel {
        background: white !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 8px !important;
        color: #111827 !important;
        margin-bottom: 20px !important;
        padding: 20px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
    }

    .bg-gray-50,
    .dark\:bg-gray-700 {
        background: #f3f4f6 !important;
        border: 1px solid #d1d5db !important;
        color: #374151 !important;
        border-radius: 4px !important;
    }

    .bg-primary\/10 {
        background: #dbeafe !important;
        border: 1px solid #93c5fd !important;
        color: #1e40af !important;
        border-radius: 6px !important;
    }

    .bg-blue-100,
    .bg-green-100 {
        background: #dbeafe !important;
        border: 1px solid #93c5fd !important;
        color: #1e40af !important;
        border-radius: 6px !important;
    }

    .bg-green-100 {
        background: #dcfce7 !important;
        border-color: #86efac !important;
        color: #166534 !important;
    }

    .rounded-lg {
        border-radius: 8px !important;
    }

    .rounded-md {
        border-radius: 6px !important;
    }

    .rounded-full {
        border-radius: 50% !important;
    }

    .page-break-before {
        page-break-before: always !important;
    }

    .page-break-after {
        page-break-after: always !important;
    }

    .page-break-inside-avoid {
        page-break-inside: avoid !important;
    }

    .space-y-6 > * + * {
        margin-top: 24px !important;
    }

    .space-y-4 > * + * {
        margin-top: 16px !important;
    }

    .space-y-3 > * + * {
        margin-top: 12px !important;
    }

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
