<script setup>
import {reactive, onMounted, computed} from 'vue';
import {useRoute} from 'vue-router';
import {translate, formatPrice, can} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import moment from 'moment-timezone';
import Breadcrumb from "../components/Breadcrumb.vue";
import IconDollarSign from '../../components/icon/icon-dollar-sign.vue';
import IconCheckCircle from '../../components/icon/icon-check-circle.vue';
import IconClock from '../../components/icon/icon-clock.vue';
import EditButtonText from "../components/EditButtonText.vue";

const route = useRoute();
const subscriptionId = route.params.id;

const state = reactive({
    subscription: {},
    loading: true,
});

// Computed properties for better data handling
const subscriptionName = computed(() => state.subscription.name || 'N/A');
const description = computed(() => state.subscription.description || null);
const monthlyPrice = computed(() => parseFloat(state.subscription.monthly_price) || 0);
const annualPrice = computed(() => parseFloat(state.subscription.annual_price) || 0);
const currencyType = computed(() => state.subscription.currency_type || {
    symbol: '$',
    precision: 2,
    thousand_sep: ',',
    decimal_sep: '.'
});
const isActive = computed(() => Boolean(state.subscription.is_active));
const createdBy = computed(() => state.subscription.user?.name || 'N/A');
const createdAt = computed(() => state.subscription.created_at || 'N/A');

// Helper functions
const formatCurrency = (amount) => {
    if (!currencyType.value) return `$${amount.toFixed(2)}`;
    const symbol = currencyType.value.symbol || '$';
    const precision = currencyType.value.precision || 2;
    return `${symbol}${amount.toFixed(precision)}`;
};

const formatDate = (date) => {
    if (!date || date === 'N/A') return 'N/A';
    return moment(date).format('MMMM DD, YYYY');
};

const fetchSubscriptionData = async () => {
    try {
        state.loading = true;
        const res = await axiosRequest.get('/admin/subscriptions/' + subscriptionId, {});
        state.subscription = res.data.data;
    } catch (error) {
        console.error('Error fetching subscription:', error);
    } finally {
        state.loading = false;
    }
};

onMounted(() => {
    fetchSubscriptionData();
});

const breadcrumbItems = [
    {label: translate('subscriptions'), url: '/admin/subscriptions'},
    {label: translate('view'), url: null}
];
</script>

<template>
    <div>
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-6">
            <Breadcrumb :items="breadcrumbItems"/>
            <div class="flex items-center gap-3">
                <edit-button-text v-if="can('edit_subscription')" :href="`/admin/subscriptions/${subscriptionId}/edit`"/>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="state.loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <!-- Subscription Content -->
        <div v-else class="space-y-6">
            <!-- Subscription Header Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-primary to-primary/80 rounded-lg flex items-center justify-center">
                                <IconDollarSign class="w-8 h-8 text-white"/>
                            </div>
                            <div>
                                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                                    {{ subscriptionName }}
                                </h1>
                                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center gap-2">
                                        <IconClock class="w-4 h-4"/>
                                        <span>{{ translate('created_on') }} {{ formatDate(createdAt) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div :class="[
                                'w-2 h-2 rounded-full',
                                isActive ? 'bg-green-500' : 'bg-gray-400'
                            ]"></div>
                            <span class="text-sm font-medium" :class="[
                                isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400'
                            ]">
                                {{ isActive ? translate('active') : translate('inactive') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Information Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-5">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 text-sm"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ translate('basic_information') }}
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('name') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ subscriptionName }}</p>
                            </div>
                            <div v-if="description">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('description') }}
                                </label>
                                <p class="text-sm text-gray-900 dark:text-white">{{ description }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('currency') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ currencyType.name || 'N/A' }} ({{ currencyType.symbol || '$' }})
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('created_by') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ createdBy }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Duration -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-5">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-green-600 dark:text-green-400 text-sm"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ translate('pricing_information') }}
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('monthly_price') }}
                                </label>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(monthlyPrice) }}
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('annual_price') }}
                                </label>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ formatCurrency(annualPrice) }}
                                </p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('status') }}
                                </label>
                                <div class="flex items-center gap-2">
                                    <div :class="[
                                        'w-2 h-2 rounded-full',
                                        isActive ? 'bg-green-500' : 'bg-gray-400'
                                    ]"></div>
                                    <span class="text-sm font-medium" :class="[
                                        isActive ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400'
                                    ]">
                                        {{ isActive ? translate('active') : translate('inactive') }}
                                    </span>
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
</style>

