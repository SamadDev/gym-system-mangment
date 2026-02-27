<script setup>
import {reactive, onMounted, computed} from 'vue';
import {useRoute} from 'vue-router';
import {translate, formatPrice, can} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import moment from 'moment-timezone';
import Breadcrumb from "../components/Breadcrumb.vue";
import IconUser from '../../components/icon/icon-user.vue';
import IconCalendar from '../../components/icon/icon-calendar.vue';
import IconDollarSign from '../../components/icon/icon-dollar-sign.vue';
import IconPackage from '../../components/icon/icon-package.vue';
import IconCheckCircle from '../../components/icon/icon-check-circle.vue';
import IconClock from '../../components/icon/icon-clock.vue';
import IconAlertCircle from '../../components/icon/icon-alert-circle.vue';
import IconExternalLink from '../../components/icon/icon-external-link.vue';
import IconPrinter from '../../components/icon/icon-printer.vue';
import IconEdit from '../../components/icon/icon-edit.vue';
import DateTimeConstants from "../../utils/DateTimeConstants.js";
import EditButtonText from "../components/EditButtonText.vue";
import PrintButtonText from "../components/PrintButtonText.vue";

const route = useRoute();
const expenseId = route.params.id;

const state = reactive({
    expense: {},
    loading: true,
});

// Computed properties for better data handling
const expenseAmount = computed(() => Number(state.expense?.amount) || 0);
const expenseCategory = computed(() => state.expense?.expense_category?.name || 'N/A');
const safeName = computed(() => state.expense?.safe?.name || 'N/A');
const safeAccountName = computed(() => state.expense?.safe_account?.name || 'N/A');
const createdBy = computed(() => state.expense?.user?.name || 'N/A');
const expenseDate = computed(() => state.expense?.expense_date || 'N/A');
const currencyType = computed(() => state.expense?.currency_type || {
    symbol: '$',
    precision: 2,
    thousand_sep: ',',
    decimal_sep: '.'
});

const fetchExpenseData = async () => {
    try {
        state.loading = true;
        const res = await axiosRequest.get('/admin/expenses/' + expenseId, {});
        state.expense = res.data.data;
    } catch (error) {
        console.error('Error fetching expense:', error);
    } finally {
        state.loading = false;
    }
};

onMounted(() => {
    fetchExpenseData();
});

const handlePrint = () => {
    window.print();
}

const handleDownload = () => {
    // Implement download functionality if needed
    console.log('Download expense');
}

const breadcrumbItems = [
    {label: translate('expenses'), url: '/admin/expenses'},
    {label: translate('view'), url: null}
];
</script>

<template>
    <div>
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-6">
            <Breadcrumb :items="breadcrumbItems"/>
            <div class="flex items-center gap-3">
                <edit-button-text v-if="can('edit_expense')" :href="`/admin/expenses/${expenseId}/edit`"/>
                <print-button-text @click.prevent="handlePrint" />
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="state.loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <!-- Expense Content -->
        <div v-else class="expense-container print:space-y-4">
            <!-- Compact Header -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-4 print:mb-3">
                <div class="p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                <IconDollarSign class="w-5 h-5 text-primary"/>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ translate('expense') }} #{{ expenseId }}
                                </h1>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ moment(expenseDate).format('MMMM DD, YYYY') }}
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ formatPrice(expenseAmount, currencyType) }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ expenseCategory }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compact Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 print:grid-cols-3 gap-4 mb-4 print:mb-3">
                <!-- Expense Category -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <IconPackage class="w-4 h-4 text-blue-600"/>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ translate('expense_category') }}</h3>
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">{{ expenseCategory }}</div>
                </div>

                <!-- Safe Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <IconCheckCircle class="w-4 h-4 text-green-600"/>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ translate('safe') }}</h3>
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">{{ safeName }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ safeAccountName }}</div>
                </div>

                <!-- Created By -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <IconUser class="w-4 h-4 text-indigo-600"/>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ translate('created_by') }}</h3>
                    </div>
                    <div class="text-sm text-gray-900 dark:text-white">{{ createdBy }}</div>
                </div>
            </div>

            <!-- Additional Information with Financial Summary -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 print:grid-cols-2 gap-4">
                        <!-- Left side - Notes and Attachments -->
                        <div class="space-y-2">
                            <div v-if="state.expense.note" class="text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ translate('note') }}:</span>
                                <span class="text-gray-900 dark:text-white ml-2">{{ state.expense.note }}</span>
                            </div>

                            <div v-if="state.expense.attachment" class="text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ translate('attachment') }}:</span>
                                <a :href="state.expense.attachment" target="_blank"
                                   class="inline-flex items-center gap-1 ml-2 text-primary hover:text-primary/80">
                                    <IconExternalLink class="w-3 h-3"/>
                                    {{ translate('download') }}
                                </a>
                            </div>
                        </div>

                        <!-- Right side - Financial Summary -->
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 mb-2">
                                <IconDollarSign class="w-4 h-4 text-purple-600"/>
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ translate('financial_summary') }}</h4>
                            </div>
                            <div class="text-sm space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300">{{ translate('amount') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ formatPrice(expenseAmount, currencyType) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300">{{ translate('currency') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ currencyType.symbol }} - {{ currencyType.name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700 dark:text-gray-300">{{ translate('currency_rate') }}:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ state.expense.currency_rate || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Print styles - Keep the page looking like the actual page */
@media print {
    /* Hide non-printable elements */
    .no-print {
        display: none !important;
    }
    
    /* Remove breadcrumb and action buttons */
    .flex.items-center.justify-between.mb-6 {
        display: none !important;
    }
    
    /* Keep the page structure but optimize for print */
    body {
        font-size: 12px !important;
        line-height: 1.4 !important;
        margin: 0 !important;
        padding: 10px !important;
        background: white !important;
    }
    
    /* Maintain card structure but remove shadows */
    .bg-white {
        background: white !important;
        border: 1px solid #e5e7eb !important;
        margin-bottom: 12px !important;
        page-break-inside: avoid !important;
    }
    
    /* Keep text colors readable */
    .text-gray-900 {
        color: #111827 !important;
    }
    
    .text-gray-500 {
        color: #6b7280 !important;
    }
    
    .text-gray-700 {
        color: #374151 !important;
    }
    
    /* Remove shadows but keep borders */
    .shadow-sm {
        box-shadow: none !important;
    }
    
    /* Padding is handled by Tailwind classes */
    
    /* Spacing is handled by Tailwind print utilities */
    
    /* Optimize font sizes for print */
    .text-xl {
        font-size: 18px !important;
    }
    
    .text-2xl {
        font-size: 20px !important;
    }
    
    .text-lg {
        font-size: 14px !important;
    }
    
    .text-sm {
        font-size: 11px !important;
    }
    
    .text-xs {
        font-size: 10px !important;
    }
    
    /* Table optimization */
    table {
        font-size: 11px !important;
        border-collapse: collapse !important;
    }
    
    th, td {
        padding: 6px 8px !important;
        border: 1px solid #e5e7eb !important;
    }
    
    th {
        background-color: #f9fafb !important;
        font-weight: 600 !important;
    }
    
    /* Keep icons but make them smaller */
    .w-10, .w-8, .w-6, .w-5, .w-4, .w-3 {
        width: 16px !important;
        height: 16px !important;
    }
    
    .h-10, .h-8, .h-6, .h-5, .h-4, .h-3 {
        height: 16px !important;
    }
    
    /* Ensure proper page breaks */
    .expense-container {
        page-break-inside: avoid !important;
    }
    
    /* Grid layout is handled by Tailwind print utilities */
    
    /* Maintain the visual hierarchy */
    .font-bold {
        font-weight: 700 !important;
    }
    
    .font-semibold {
        font-weight: 600 !important;
    }
    
    .font-medium {
        font-weight: 500 !important;
    }
    
    /* Keep the primary color for important elements */
    .text-primary {
        color: #3b82f6 !important;
    }
    
    /* Ensure links are visible */
    a {
        color: #3b82f6 !important;
        text-decoration: underline !important;
    }
}
</style>
