<script setup>
import {reactive, onMounted, computed, ref} from 'vue';
import {useRoute} from 'vue-router';
import {translate, can, formatPrice} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import OrdersTable from "../orders/OrdersTable.vue";
import PaymentsTable from "../Finance/Payments/PaymentsTable.vue";
import InvoicesTable from "../Finance/Invoices/InvoicesTable.vue";
import WalletsTable from "../wallets/WalletsTable.vue";
import WalletModal from "../wallets/WalletModal.vue";
import Breadcrumb from "../components/Breadcrumb.vue";
import IconUser from '../../components/icon/icon-user.vue';
import IconDollarSign from '../../components/icon/icon-dollar-sign.vue';
import IconPackage from '../../components/icon/icon-package.vue';
import IconCheckCircle from '../../components/icon/icon-check-circle.vue';
import IconClock from '../../components/icon/icon-clock.vue';
import IconMail from '../../components/icon/icon-mail.vue';
import IconShoppingCart from '../../components/icon/icon-shopping-cart.vue';
import EditButtonText from "../components/EditButtonText.vue";
import AddButton from "../components/AddButton.vue";
import {useAppStore} from "../../stores";
import eventBus from "../../eventBus.js";

const route = useRoute();
const customerId = route.params.id;

const state = reactive({
    customer: {},
    customerData: {}, // Contains both statistics and performance metrics
    loading: true,
    dataLoading: true,
});

// Tab management
const activeTab = ref('orders');

const fetchCustomerData = async () => {
    try {
        state.loading = true;
        const res = await axiosRequest.get('/admin/customers/' + customerId, {});
        state.customer = res.data.data;
    } catch (error) {
        console.error('Error fetching customer:', error);
    } finally {
        state.loading = false;
    }
};

const fetchCustomerDataAndMetrics = async () => {
    try {
        state.dataLoading = true;
        const res = await axiosRequest.get('/admin/customers/' + customerId + '/performance-metrics', {});
        state.customerData = res.data.data;
    } catch (error) {
        console.error('Error fetching customer data and metrics:', error);
    } finally {
        state.dataLoading = false;
    }
};

const store = useAppStore();
const mainCurrency = ref({});

onMounted(() => {
    mainCurrency.value = store.currencyTypeList.find(item => item.is_main === 1);
    fetchCustomerData();
    fetchCustomerDataAndMetrics();
});

const breadcrumbItems = [
    {label: translate('customers'), url: '/admin/customers'},
    {label: translate('view'), url: null}
];
</script>

<template>
    <!-- wallet modal -->
    <wallet-modal
        v-if="can('add_wallet')"
        :customerId="customerId"
        :titles="[translate('add_wallet'), translate('edit_wallet')]"
    />
    <!-- Main container -->
    <div>
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-6">
            <Breadcrumb :items="breadcrumbItems"/>
            <div class="flex items-center gap-3">
                <add-button v-if="can('add_order')" href="/admin/orders/create"
                            :text="translate('add_order')" class="me-2"/>
                <add-button v-if="can('add_payment')" href="/admin/payments/create"
                            :text="translate('add_payment')" class="me-2"/>
                <add-button v-if="can('add_invoice')" href="/admin/invoices/create"
                            :text="translate('add_invoice')" class="me-2"/>
                <add-button v-if="can('add_wallet')" @click="() => eventBus.emit('openWalletCreateModal', )"
                            :text="translate('add_wallet')" class="me-2"/>
                <edit-button-text v-if="can('edit_customer')" :href="`/admin/customers/${customerId}/edit`"/>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="state.loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <!-- Customer Content -->
        <div v-else class="space-y-6">
            <!-- Clean Customer Profile Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <!-- Customer Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-lg flex items-center justify-center">
                                <IconUser class="w-8 h-8 text-white"/>
                            </div>
                            <div>
                                <h1 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">
                                    {{ state.customer.name }}
                                </h1>
                                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center gap-1">
                                        <IconMail class="w-4 h-4"/>
                                        <span>{{ state.customer.email }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <IconClock class="w-4 h-4"/>
                                        <span>{{ state.customer.phone_number }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ translate('location') }}</div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ state.customer.details?.city?.name }},
                                {{ state.customer.details?.country?.name }}
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Grid -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Total Orders -->
                        <div
                            class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ translate('total_orders') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ state.customerData.orders_count || 0 }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ formatPrice(state.customerData.total_orders ?? 0, mainCurrency) }}</p>
                                </div>
                                <div
                                    class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <IconPackage class="w-5 h-5 text-blue-600 dark:text-blue-400"/>
                                </div>
                            </div>
                        </div>

                        <!-- Total Payments -->
                        <div
                            class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ translate('total_payments') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ state.customerData.payments_count || 0 }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ formatPrice(state.customerData.total_payments ?? 0, mainCurrency) }}</p>
                                </div>
                                <div
                                    class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <IconDollarSign class="w-5 h-5 text-green-600 dark:text-green-400"/>
                                </div>
                            </div>
                        </div>

                        <!-- Total Invoices -->
                        <div
                            class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ translate('total_invoices') }}</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ state.customerData.invoices_count || 0 }}</p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ formatPrice(state.customerData.total_invoices ?? 0, mainCurrency) }}</p>
                                </div>
                                <div
                                    class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <IconCheckCircle class="w-5 h-5 text-purple-600 dark:text-purple-400"/>
                                </div>
                            </div>
                        </div>

                        <!-- Wallet -->
                        <div
                            class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                        {{ translate('wallet') }}</p>
                                    <p class="text-1xl font-bold text-gray-900 dark:text-white">{{
                                            formatPrice(state.customerData.total_wallets ?? 0, mainCurrency)
                                        }}</p>
                                </div>
                                <div
                                    class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                    <IconShoppingCart class="w-5 h-5 text-orange-600 dark:text-orange-400"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics - Compact -->
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-white">{{
                            translate('performance_metrics')
                        }}</h3>
                    <div class="w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                        <IconShoppingCart class="w-3 h-3 text-gray-600 dark:text-gray-400"/>
                    </div>
                </div>

                <!-- Loading State for Metrics -->
                <div v-if="state.dataLoading" class="flex justify-center items-center py-4">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
                </div>

                <!-- Metrics Content -->
                <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Order Frequency -->
                    <div class="text-center">
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">{{ state.customerData.orders_per_month || 0 }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ translate('orders_per_month') }}</div>
                    </div>

                    <!-- Payment Success Rate -->
                    <div class="text-center">
                        <div class="text-lg font-semibold text-green-600">{{ parseFloat(state.customerData.payment_rate ?? 0) }}%</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">{{
                                translate('payment_rate')
                            }}
                        </div>
                    </div>

                    <!-- Customer Lifetime Value -->
                    <div class="text-center">
                        <div class="text-lg font-semibold text-blue-600">{{ formatPrice(state.customerData.lifetime_value ?? 0, mainCurrency) }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ translate('lifetime_value') }}</div>
                    </div>

                    <!-- Customer Rating -->
                    <div class="text-center">
                        <div class="text-lg font-semibold text-yellow-600">{{ state.customerData.customer_rating || 0 }}/5</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ translate('customer_rating') }}</div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Tabs Section -->
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <!-- Modern Tab Navigation -->
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3">
                    <nav class="flex space-x-1" aria-label="Tabs">
                        <button @click="activeTab = 'orders'"
                                :class="[
                                    activeTab === 'orders'
                                        ? 'text-primary border-primary bg-white dark:bg-gray-800 shadow-sm'
                                        : 'text-gray-500 hover:text-gray-700 hover:bg-white/50 dark:hover:bg-gray-600/50 border-transparent',
                                    'px-4 py-2 border-b-2 font-medium text-sm flex items-center gap-2 transition-all duration-200 rounded-t-lg'
                                ]">
                            <IconPackage class="w-4 h-4"/>
                            {{ translate('orders') }}
                        </button>
                        <button @click="activeTab = 'payments'"
                                :class="[
                                    activeTab === 'payments'
                                        ? 'text-primary border-primary bg-white dark:bg-gray-800 shadow-sm'
                                        : 'text-gray-500 hover:text-gray-700 hover:bg-white/50 dark:hover:bg-gray-600/50 border-transparent',
                                    'px-4 py-2 border-b-2 font-medium text-sm flex items-center gap-2 transition-all duration-200 rounded-t-lg'
                                ]">
                            <IconDollarSign class="w-4 h-4"/>
                            {{ translate('payments') }}
                        </button>
                        <button @click="activeTab = 'invoices'"
                                :class="[
                                    activeTab === 'invoices'
                                        ? 'text-primary border-primary bg-white dark:bg-gray-800 shadow-sm'
                                        : 'text-gray-500 hover:text-gray-700 hover:bg-white/50 dark:hover:bg-gray-600/50 border-transparent',
                                    'px-4 py-2 border-b-2 font-medium text-sm flex items-center gap-2 transition-all duration-200 rounded-t-lg'
                                ]">
                            <IconCheckCircle class="w-4 h-4"/>
                            {{ translate('invoices') }}
                        </button>
                        <button @click="activeTab = 'wallet'"
                                :class="[
                                    activeTab === 'wallet'
                                        ? 'text-primary border-primary bg-white dark:bg-gray-800 shadow-sm'
                                        : 'text-gray-500 hover:text-gray-700 hover:bg-white/50 dark:hover:bg-gray-600/50 border-transparent',
                                    'px-4 py-2 border-b-2 font-medium text-sm flex items-center gap-2 transition-all duration-200 rounded-t-lg'
                                ]">
                            <IconShoppingCart class="w-4 h-4"/>
                            {{ translate('wallet') }}
                        </button>
                    </nav>
                </div>

                <!-- Enhanced Tab Content -->
                <div class="">
                    <!-- Orders Tab -->
                    <div v-if="activeTab === 'orders'">
                        <orders-table :customerId="customerId"/>
                    </div>

                    <!-- Payments Tab -->
                    <div v-if="activeTab === 'payments'">
                        <payments-table :customerId="customerId"/>
                    </div>

                    <!-- Invoices Tab -->
                    <div v-if="activeTab === 'invoices'">
                        <invoices-table :customerId="customerId"/>
                    </div>

                    <!-- Wallet Tab -->
                    <div v-if="activeTab === 'wallet'">
                        <wallets-table :customerId="customerId"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.panel {
    @apply bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6;
}
</style>
