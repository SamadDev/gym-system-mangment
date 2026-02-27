<script setup>
import {reactive, onMounted, computed} from 'vue';
import {useRoute} from 'vue-router';
import {translate, formatPrice, can} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import moment from 'moment-timezone';
import Breadcrumb from "../components/Breadcrumb.vue";
import IconStore from '../../components/icon/icon-store.vue';
import IconCalendar from '../../components/icon/icon-calendar.vue';
import IconDollarSign from '../../components/icon/icon-dollar-sign.vue';
import IconGlobe from '../../components/icon/icon-globe.vue';
import IconCheckCircle from '../../components/icon/icon-check-circle.vue';
import IconClock from '../../components/icon/icon-clock.vue';
import IconExternalLink from '../../components/icon/icon-external-link.vue';
import IconEdit from '../../components/icon/icon-edit.vue';
import EditButtonText from "../components/EditButtonText.vue";

const route = useRoute();
const storeId = route.params.id;

const state = reactive({
    store: {},
    loading: true,
});

// Computed properties for better data handling
const storeName = computed(() => state.store.name || 'N/A');
const websiteUrl = computed(() => state.store.website_url || null);
const countryName = computed(() => state.store.country?.name || 'N/A');
const currencyType = computed(() => state.store.currency_type || {
    symbol: '$',
    precision: 2,
    thousand_sep: ',',
    decimal_sep: '.'
});
const createdBy = computed(() => state.store.user?.name || 'N/A');
const createdAt = computed(() => state.store.created_at || 'N/A');
const autoPricingEnabled = computed(() => Boolean(state.store.auto_pricing_enabled));
const pricingMethod = computed(() => state.store.pricing_method || 'N/A');
const weightType = computed(() => state.store.weight_type || 'lb');
const profitPerWeightUnit = computed(() => parseFloat(state.store.profit_per_weight_unit) || 0);
const profitPerCbm = computed(() => parseFloat(state.store.profit_per_cbm) || 0);
const profitPercentage = computed(() => parseFloat(state.store.profit_percentage) || 0);
const baseFee = computed(() => parseFloat(state.store.base_fee) || 0);
const defaultLocalShipping = computed(() => parseFloat(state.store.default_local_shipping) || 0);
const defaultInternationalShipping = computed(() => parseFloat(state.store.default_international_shipping) || 0);
const defaultCustomsClearance = computed(() => parseFloat(state.store.default_customs_clearance) || 0);
const defaultTax = computed(() => parseFloat(state.store.default_tax) || 0);

// Helper functions
const getPricingMethodLabel = (method) => {
    const methods = {
        'weight_based': translate('weight_based'),
        'percentage': translate('percentage_based'),
        'cbm': translate('cbm_based')
    };
    return methods[method] || method;
};

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

const fetchStoreData = async () => {
    try {
        state.loading = true;
        const res = await axiosRequest.get('/admin/stores/' + storeId, {});
        state.store = res.data.data;
    } catch (error) {
        console.error('Error fetching store:', error);
    } finally {
        state.loading = false;
    }
};

onMounted(() => {
    fetchStoreData();
});

const breadcrumbItems = [
    {label: translate('stores'), url: '/admin/stores'},
    {label: translate('view'), url: null}
];
</script>

<template>
    <div>
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-6">
            <Breadcrumb :items="breadcrumbItems"/>
            <div class="flex items-center gap-3">
                <edit-button-text v-if="can('edit_store')" :href="`/admin/stores/${storeId}/edit`"/>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="state.loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <!-- Store Content -->
        <div v-else class="space-y-6">
            <!-- Store Header Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center gap-6">
                        <!-- Store Image -->
                        <div class="flex-shrink-0">
                            <div v-if="state.store.image" class="w-24 h-24 rounded-lg overflow-hidden border-2 border-gray-200 dark:border-gray-700">
                                <img :src="state.store.image" :alt="storeName" class="w-full h-full object-cover"/>
                            </div>
                            <div v-else class="w-24 h-24 bg-gradient-to-br from-primary to-primary/80 rounded-lg flex items-center justify-center border-2 border-gray-200 dark:border-gray-700">
                                <IconStore class="w-12 h-12 text-white"/>
                            </div>
                        </div>

                        <!-- Store Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ storeName }}
                                    </h1>
                                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                        <div class="flex items-center gap-2">
                                            <IconGlobe class="w-4 h-4"/>
                                            <span>{{ countryName }}</span>
                                        </div>
                                        <div v-if="websiteUrl" class="flex items-center gap-2">
                                            <IconExternalLink class="w-4 h-4"/>
                                            <a :href="websiteUrl" target="_blank" rel="noopener noreferrer" 
                                               class="text-primary hover:underline">
                                                {{ translate('visit_website') }}
                                            </a>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <IconClock class="w-4 h-4"/>
                                            <span>{{ translate('created_on') }} {{ formatDate(createdAt) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Store Information Grid -->
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
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ storeName }}</p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('country') }}
                                </label>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ countryName }}</p>
                            </div>
                            <div v-if="websiteUrl">
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('website_url') }}
                                </label>
                                <a :href="websiteUrl" target="_blank" rel="noopener noreferrer" 
                                   class="text-sm text-primary hover:underline flex items-center gap-1">
                                    {{ websiteUrl }}
                                    <IconExternalLink class="w-3 h-3"/>
                                </a>
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

                <!-- Automatic Pricing Configuration -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center gap-2 mb-5">
                            <div class="w-8 h-8 bg-primary/10 dark:bg-primary/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calculator text-primary text-sm"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ translate('automatic_pricing') }}
                            </h3>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                    {{ translate('status') }}
                                </label>
                                <div class="flex items-center gap-2">
                                    <div :class="[
                                        'w-2 h-2 rounded-full',
                                        autoPricingEnabled ? 'bg-green-500' : 'bg-gray-400'
                                    ]"></div>
                                    <span class="text-sm font-medium" :class="[
                                        autoPricingEnabled ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400'
                                    ]">
                                        {{ autoPricingEnabled ? translate('enabled') : translate('disabled') }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="autoPricingEnabled">
                                <div>
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                        {{ translate('pricing_method') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ getPricingMethodLabel(pricingMethod) }}
                                    </p>
                                </div>

                                <!-- Weight Based Pricing -->
                                <div v-if="pricingMethod === 'weight_based'">
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                        {{ translate('profit_per_weight_unit') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(profitPerWeightUnit) }} / {{ weightType }}
                                    </p>
                                </div>

                                <!-- Percentage Based Pricing -->
                                <div v-if="pricingMethod === 'percentage'">
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                        {{ translate('profit_percentage') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ profitPercentage.toFixed(2) }}%
                                    </p>
                                </div>

                                <!-- CBM Based Pricing -->
                                <div v-if="pricingMethod === 'cbm'">
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                        {{ translate('profit_per_cbm') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(profitPerCbm) }} / m³
                                    </p>
                                </div>

                                <!-- Base Fee -->
                                <div v-if="baseFee > 0">
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                        {{ translate('base_fee') }}
                                    </label>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(baseFee) }}
                                    </p>
                                </div>
                            </div>
                            <div v-else class="text-sm text-gray-500 dark:text-gray-400 italic">
                                {{ translate('automatic_pricing_description') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipping & Fees Configuration -->
            <div v-if="autoPricingEnabled" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-5">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shipping-fast text-green-600 dark:text-green-400 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ translate('shipping_and_fees') }}
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                {{ translate('local_shipping') }}
                            </label>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(defaultLocalShipping) }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                {{ translate('international_shipping') }}
                            </label>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(defaultInternationalShipping) }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                {{ translate('customs_clearance') }}
                            </label>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(defaultCustomsClearance) }}
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 block">
                                {{ translate('tax') }} (%)
                            </label>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ defaultTax.toFixed(2) }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
</style>

