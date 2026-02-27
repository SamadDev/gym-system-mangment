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
import IconTruck from '../../components/icon/icon-truck.vue';
import IconPackage from '../../components/icon/icon-package.vue';
import IconCheckCircle from '../../components/icon/icon-check-circle.vue';
import IconClock from '../../components/icon/icon-clock.vue';
import IconAlertCircle from '../../components/icon/icon-alert-circle.vue';
import IconExternalLink from '../../components/icon/icon-external-link.vue';
import DateTimeConstants from "../../utils/DateTimeConstants.js";
import EditButtonText from "../components/EditButtonText.vue";
import PrintButtonText from "../components/PrintButtonText.vue";

const route = useRoute();
const orderId = route.params.id;

const state = reactive({
    order: {},
    loading: true,
});

// Computed properties for better data handling
const orderNumber = computed(() => state.order.order_number || 'N/A');
const customerName = computed(() => state.order.customer?.name || 'N/A');
const customerEmail = computed(() => state.order.customer?.email || 'N/A');
const customerPhone = computed(() => state.order.customer?.phone_number || 'N/A');
const createdBy = computed(() => state.order.user?.name || 'N/A');
const statusHistory = computed(() => state.order.statuses || []);
const orderItems = computed(() => state.order.items || []);
const orderType = computed(() => state.order.order_type || 'order');
const shippingType = computed(() => state.order.shipping_type || null);
const localDelivery = computed(() => Boolean(state.order.local_delivery));

// Helper functions for shipping type display
const getShippingTypeBadgeClass = (shippingType) => {
    const classes = {
        'air': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'sea': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'land': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'
    };
    return classes[shippingType] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getShippingTypeIcon = (shippingType) => {
    const icons = {
        'air': 'fas fa-plane',
        'sea': 'fas fa-ship',
        'land': 'fas fa-truck'
    };
    return icons[shippingType] || 'fas fa-circle';
};

// Display values as stored in database (no calculations)
const subtotal = computed(() => {
    return orderItems.value.reduce((sum, item) => sum + (item.product_price * item.quantity), 0);
});

const taxAmount = computed(() => state.order.tax || 0);
const localShippingAmount = computed(() => state.order.local_shipping || 0);
const internationalShippingAmount = computed(() => state.order.international_shipping || 0);
const customsClearanceAmount = computed(() => state.order.customs_clearance || 0);
const serviceFee = computed(() => state.order.service_fee || 0);
const baseCost = computed(() => state.order.base_cost || 0);
const totalAmount = computed(() => state.order.order_total || 0);
const fetchOrderData = async () => {
    try {
        state.loading = true;
        const res = await axiosRequest.get('/admin/orders/' + orderId, {});
        state.order = res.data.data;
    } catch (error) {
        console.error('Error fetching order:', error);
    } finally {
        state.loading = false;
    }
};

onMounted(() => {
    fetchOrderData();
});

const handlePrint = () => {
    window.print();
}

const breadcrumbItems = [
    {label: translate('orders'), url: '/admin/orders'},
    {label: translate('view'), url: null}
];
</script>

<template>
    <div>
        <!-- Header Section -->
        <div class="flex items-center justify-between mb-6">
            <Breadcrumb :items="breadcrumbItems"/>
            <div class="flex items-center gap-3">
                <edit-button-text v-if="can('edit_order')" :href="`/admin/orders/${orderId}/edit`"/>
                <print-button-text @click.prevent="handlePrint" />
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="state.loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <!-- Order Content -->
        <div v-else class="space-y-4 lg:space-y-6">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4 lg:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 lg:gap-4">
                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                <IconPackage class="w-5 h-5 text-primary"/>
                            </div>
                            <div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mb-2">
                                    <h1 class="text-lg lg:text-xl font-semibold text-gray-900 dark:text-white">
                                        {{ translate('order') }} #{{ orderNumber }}
                                    </h1>
                                    <!-- Order Type Badge -->
                                    <div class="flex items-center gap-2">
                                        <div
                                            :class="[
                                                'inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium transition-all duration-200',
                                                orderType === 'order'
                                                    ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800'
                                                    : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800'
                                            ]"
                                        >
                                            <div
                                                :class="[
                                                    'w-4 h-4 rounded-full flex items-center justify-center',
                                                    orderType === 'order'
                                                        ? 'bg-blue-200 dark:bg-blue-800'
                                                        : 'bg-green-200 dark:bg-green-800'
                                                ]"
                                            >
                                                <svg
                                                    v-if="orderType === 'order'"
                                                    class="w-2.5 h-2.5 text-blue-600 dark:text-blue-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                                </svg>
                                                <svg
                                                    v-else
                                                    class="w-2.5 h-2.5 text-green-600 dark:text-green-400"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            </div>
                                            <span class="capitalize">{{ translate(orderType) }}</span>
                                        </div>
                                        <!-- Shipping Type Badge (only shown when order_type is shipping) -->
                                        <div v-if="orderType === 'shipping' && shippingType" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium capitalize"
                                             :class="getShippingTypeBadgeClass(shippingType)">
                                            <i :class="getShippingTypeIcon(shippingType)"></i>
                                            <span>{{ shippingType }}</span>
                                        </div>
                                        <!-- Local Delivery Badge (only shown when local_delivery is enabled) -->
                                        <div v-if="localDelivery" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span>{{ translate('local_delivery') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 text-sm text-gray-600 dark:text-gray-400">
                                    <div class="flex items-center gap-1">
                                        <IconCalendar class="w-4 h-4"/>
                                        <span>{{
                                                moment(state.order.created_at).tz(DateTimeConstants.getTimezone()).format(DateTimeConstants.DATETIME_FORMAT)
                                            }}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <IconUser class="w-4 h-4"/>
                                        <span>{{ translate('created_by') }}: {{ createdBy }}</span>
                                    </div>
                                    <div v-if="state.order.store" class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span>{{ translate('store') }}: {{ state.order.store.name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="state.order.status" class="text-right">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{
                                    translate('current_status')
                                }}
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-md px-3 py-1 flex items-center gap-2">
                                <span
                                    class="px-2 py-1 rounded text-xs font-medium"
                                >
                                    {{ state.order.status.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                <!-- Left Column - Customer & Items -->
                <div class="lg:col-span-2 space-y-4 lg:space-y-6">
                    <!-- Customer Information -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-blue-50 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                                    <IconUser class="w-4 h-4 text-blue-600 dark:text-blue-400"/>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ translate('customer_information') }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Customer details and contact
                                        information</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div
                                        class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-2">
                                        <IconUser class="w-4 h-4 text-gray-600 dark:text-gray-400"/>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{
                                            translate('name')
                                        }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            customerName
                                        }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-2">
                                        <IconCalendar class="w-4 h-4 text-gray-600 dark:text-gray-400"/>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{
                                            translate('email')
                                        }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            customerEmail
                                        }}
                                    </div>
                                </div>
                                <div class="text-center">
                                    <div
                                        class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-2">
                                        <IconDollarSign class="w-4 h-4 text-gray-600 dark:text-gray-400"/>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{
                                            translate('phone')
                                        }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{
                                            customerPhone
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-gray-50 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                    <IconPackage class="w-4 h-4 text-gray-600 dark:text-gray-400"/>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ translate('order_items') }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Products and services in this
                                        order</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4">
                            <div v-if="orderItems && orderItems.length > 0" class="space-y-3">
                                <div
                                    v-for="(item, index) in orderItems"
                                    :key="item.id"
                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm"
                                >
                                    <!-- Item Header -->
                                    <div
                                        class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-gray-100 dark:border-gray-700 gap-2">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Item {{
                                                    index + 1
                                                }}</span>
                                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{
                                                    formatPrice(item.product_price * item.quantity, item.currency_type)
                                                }}
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{
                                                    translate('qty')
                                                }}: {{ item.quantity }}</span>
                                        </div>
                                    </div>

                                    <!-- Item Content -->
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                                            <!-- Product Image -->
                                            <div class="lg:col-span-2">
                                                <div class="relative w-full aspect-square max-w-20">
                                                    <div v-if="item.image" class="relative group">
                                                        <img :src="item.image"
                                                             class="w-full h-full object-cover rounded-lg border border-gray-200 dark:border-gray-600"/>
                                                    </div>
                                                    <div v-else
                                                         class="w-full h-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none"
                                                             stroke="currentColor"
                                                             viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2"
                                                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Product Details -->
                                            <div class="lg:col-span-10">
                                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                                    <!-- Product Name -->
                                                    <div class="sm:col-span-2 lg:col-span-3">
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('product_name') }}
                                                        </label>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ item.product_name || 'N/A' }}
                                                        </div>
                                                    </div>

                                                    <!-- Product Link -->
                                                    <div class="sm:col-span-2 lg:col-span-3">
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('product_link') }}
                                                        </label>
                                                        <div v-if="item.link" class="text-sm">
                                                            <a :href="item.link" target="_blank"
                                                               class="inline-flex items-center gap-1 text-primary hover:text-primary/80 transition-colors">
                                                                {{ translate('view_product') }}
                                                                <IconExternalLink class="w-3 h-3"/>
                                                            </a>
                                                        </div>
                                                        <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ translate('no_link_provided') }}
                                                        </div>
                                                    </div>

                                                    <!-- Color -->
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('color') }}
                                                        </label>
                                                        <div v-if="item.color"
                                                             class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ item.color }}
                                                        </div>
                                                        <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ translate('no_color_specified') }}
                                                        </div>
                                                    </div>

                                                    <!-- Size -->
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('size') }}
                                                        </label>
                                                        <div v-if="item.size"
                                                             class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ item.size }}
                                                        </div>
                                                        <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ translate('no_size_specified') }}
                                                        </div>
                                                    </div>

                                                    <!-- Price -->
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('unit_price') }}
                                                        </label>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                            {{ formatPrice(item.product_price, item.currency_type) }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Item Total -->
                                                <div class="mt-4 bg-primary/10 rounded-lg p-3 border border-primary/20">
                                                    <div class="flex justify-between items-center">
                                                        <div class="flex items-center gap-2">
                                                            <div
                                                                class="w-6 h-6 bg-primary/20 rounded flex items-center justify-center">
                                                                <IconDollarSign class="w-3 h-3 text-primary"/>
                                                            </div>
                                                            <span
                                                                class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                                                    translate('item_total')
                                                                }}</span>
                                                        </div>
                                                        <span class="text-lg font-bold text-primary">
                                                            {{
                                                                formatPrice(item.product_price * item.quantity, item.currency_type)
                                                            }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                <div
                                    class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <IconPackage class="w-6 h-6 opacity-50"/>
                                </div>
                                <p class="text-sm font-medium">{{ translate('no_items') }}</p>
                            </div>

                            <!-- Order Notes -->
                            <div v-if="state.order.note"
                                 class="mt-4 bg-amber-50 dark:bg-amber-900/10 rounded-lg p-3 border border-amber-200 dark:border-amber-800">
                                <div class="flex items-center gap-2 mb-2">
                                    <div
                                        class="w-6 h-6 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                        <IconAlertCircle class="w-3 h-3 text-amber-600 dark:text-amber-400"/>
                                    </div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ translate('order_notes') }}</h4>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 text-xs leading-relaxed">{{
                                        state.order.note
                                    }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Summary & Timeline -->
                <div class="space-y-4 lg:space-y-6">
                    <!-- Order Summary -->
                    <div
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-gray-50 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                    <IconDollarSign class="w-4 h-4 text-gray-600 dark:text-gray-400"/>
                                </div>
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ translate('order_summary') }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Financial breakdown and
                                        totals</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 space-y-4">
                            <!-- Subtotal -->
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">{{ translate('subtotal') }}</span>
                                <span class="font-medium">{{ formatPrice(subtotal, state.order.currency_type) }}</span>
                            </div>

                            <!-- Tax Section -->
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                <div class="flex justify-between items-center mb-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                                            <IconCheckCircle class="w-3 h-3 text-gray-600 dark:text-gray-400"/>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                                translate('tax')
                                            }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                            formatPrice(taxAmount, state.order.currency_type)
                                        }}</span>
                                </div>
                            </div>

                            <!-- Shipping Section -->
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                <div class="space-y-3">
                                    <!-- Local Shipping -->
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                                                <IconTruck class="w-3 h-3 text-gray-600 dark:text-gray-400"/>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                                    translate('local_shipping')
                                                }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{
                                                state.order.is_free_delivery ? translate('free') : formatPrice(localShippingAmount, state.order.currency_type)
                                            }}
                                        </span>
                                    </div>

                                    <!-- International Shipping -->
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                                                <IconTruck class="w-3 h-3 text-gray-600 dark:text-gray-400"/>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                                    translate('international_shipping')
                                                }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                                formatPrice(internationalShippingAmount, state.order.currency_type)
                                            }}</span>
                                    </div>

                                    <!-- Customs Clearance -->
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                                                <IconCheckCircle class="w-3 h-3 text-gray-600 dark:text-gray-400"/>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                                    translate('customs_clearance')
                                                }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                                formatPrice(customsClearanceAmount, state.order.currency_type)
                                            }}</span>
                                    </div>

                                    <!-- Service Fee -->
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                                                <IconDollarSign class="w-3 h-3 text-gray-600 dark:text-gray-400"/>
                                            </div>
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{
                                                    translate('service_fee')
                                                }}</span>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                                formatPrice(serviceFee, state.order.currency_type)
                                            }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Free Delivery Indicator -->
                            <div v-if="state.order.is_free_delivery"
                                 class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                                <div class="flex items-center">
                                    <div
                                        class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded flex items-center justify-center">
                                        <IconCheckCircle class="w-3 h-3 text-blue-600 dark:text-blue-400"/>
                                    </div>
                                    <span class="text-sm ml-2 text-blue-700 dark:text-blue-300">{{
                                            translate('free_delivery')
                                        }}</span>
                                </div>
                            </div>

                            <!-- Base Cost -->
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                                <div class="flex justify-between items-center mb-2">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-6 h-6 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center">
                                            <IconPackage class="w-3 h-3 text-gray-600 dark:text-gray-400"/>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{
                                                translate('base_cost')
                                            }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                            formatPrice(baseCost, state.order.currency_type)
                                        }}</span>
                                </div>
                            </div>

                            <!-- Order Total -->
                            <div class="bg-primary/10 rounded-lg p-3">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-primary/20 rounded flex items-center justify-center">
                                            <IconDollarSign class="w-3 h-3 text-primary"/>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                                translate('order_total')
                                            }}</span>
                                    </div>
                                    <span class="text-lg font-bold text-primary">{{
                                            formatPrice(totalAmount, state.order.currency_type)
                                        }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="panel">
                        <div class="flex items-center gap-3 mb-4">
                            <IconClock class="w-5 h-5 text-primary"/>
                            <h3 class="text-lg font-semibold">{{ translate('status_timeline') }}</h3>
                        </div>
                        <div v-if="statusHistory && statusHistory.length > 0" class="max-w-[900px] mx-auto">
                            <div
                                v-for="(status, index) in statusHistory"
                                :key="status.id"
                                class="flex"
                            >
                                <p class="text-[#3b3f5c] dark:text-white-light font-semibold text-[13px] mr-3">
                                    {{
                                        moment(status.created_at).tz(DateTimeConstants.getTimezone()).format(DateTimeConstants.DATE_FORMAT)
                                    }}
                                </p>
                                <div
                                    class="relative
           before:absolute before:left-1/2 before:-translate-x-1/2 before:top-[15px]
           before:w-2.5 before:h-2.5 before:border-2 before:rounded-full
           after:absolute after:left-1/2 after:-translate-x-1/2 after:top-[25px]
           after:-bottom-[15px] after:w-0 after:h-auto after:border-l-2 after:rounded-full"
                                    :style="{ '--status-color': status.color }"
                                ></div>
                                <div class="p-2.5 self-center ltr:ml-2.5 rtl:mr-2.5">
                                    <p class="text-[#3b3f5c] dark:text-white-light font-semibold text-[13px]">
                                        {{ status.name }}
                                    </p>
                                    <p v-if="status.note" class="text-[#3b3f5c] dark:text-white-light text-[13px] mt-1">
                                        {{ status.note }}
                                    </p>
                                    <p class="text-white-dark text-xs font-bold self-center min-w-[100px] max-w-[100px]">
                                        {{ moment(status.created_at).tz(DateTimeConstants.getTimezone()).fromNow() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <IconClock class="w-8 h-8 mx-auto mb-2 opacity-50"/>
                            <p>{{ translate('no_status_history') }}</p>
                        </div>
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

.btn {
    @apply inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200;
}

.btn-primary {
    @apply bg-primary text-white hover:bg-primary/90 focus:ring-primary;
}

.btn-outline-primary {
    @apply border-primary text-primary hover:bg-primary hover:text-white focus:ring-primary;
}

.btn-outline-secondary {
    @apply border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500;
}

/* Use CSS variable instead of Tailwind border classes */
div::before {
    border-color: var(--status-color);
}

div::after {
    border-left-color: var(--status-color);
}

</style>

