<script setup>
import {reactive, ref, computed, watch, onMounted, nextTick} from 'vue';
import VueSelect from "../components/VueSelect.vue";
import {createFormData, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import {useAppStore} from "../../stores";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import Breadcrumb from "../components/Breadcrumb.vue";
import {useRouter, useRoute} from "vue-router";

const store = useAppStore();
const router = useRouter();
const route = useRoute();
const orderId = route.params.id;

const orderCurrencyType = computed(() => {
    if (!Array.isArray(store.currencyTypeList)) return null;
    return store.currencyTypeList.find(item => item.is_main === 1) || null;
});

const state = reactive({
    order: {
        customer_id: '',
        store_id: '',
        order_type: 'order',
        shipping_type: null,
        local_delivery: false,
        customer_address_id: null,
        local_shipping: 0,
        tax: 0,
        international_shipping: 0,
        customs_clearance: 0,
        service_fee: 0,
        is_free_delivery: false,
        base_cost: 0,
        order_total: 0,
        note: '',
        status_id: '',
        items: [],
    },
    errors: {},
    disableSubmit: false,
    loading: true,
});

const customerAddresses = ref([]);

// Computed properties for calculations
const totalItemsPrice = computed(() => {
    if (state.order.order_type === 'shipping') return 0;
    return state.order.items?.reduce((sum, item) => {
        return sum + (item.quantity * parseFloat(item.product_price) || 0);
    }, 0) || 0;
});

const taxAmount = computed(() => {
    return (totalItemsPrice.value * (state.order.tax / 100)) || 0;
});

const localShippingAmount = computed(() => {
    return state.order.order_type === 'shipping' ? 0 : state.order.local_shipping;
});

const baseCost = computed(() => {
    return totalItemsPrice.value + taxAmount.value + (state.order.is_free_delivery ? 0 : localShippingAmount.value);
});

const orderTotal = computed(() => {
    const orderTotalBaseCost = totalItemsPrice.value + taxAmount.value + localShippingAmount.value;
    return orderTotalBaseCost + (state.order.international_shipping || 0) +
        (state.order.customs_clearance || 0) + (state.order.service_fee || 0);
});

// Watch for changes and update calculated fields
watch([totalItemsPrice, taxAmount, localShippingAmount, orderTotal], () => {
    state.order.base_cost = baseCost.value;
    state.order.order_total = orderTotal.value;
});

// Watch for order_type changes and set/reset shipping_type
watch(() => state.order.order_type, (newType) => {
    if (newType === 'shipping') {
        // Only set to 'air' if shipping_type is null (don't override existing value)
        if (!state.order.shipping_type) {
            state.order.shipping_type = 'air';
        }
    } else {
        state.order.shipping_type = null;
    }
});

// Watch for local_delivery changes and reset customer_address_id when unchecked
watch(() => state.order.local_delivery, (isEnabled) => {
    if (!isEnabled) {
        state.order.customer_address_id = null;
    }
});

const customerRef = ref(null);
const storeRef = ref(null);
const statusRef = ref(null);

const handleSubmit = async () => {
    if (state.disableSubmit) return;

    state.disableSubmit = true;
    try {
        await updateOrder();
    } finally {
        state.disableSubmit = false;
    }
};

const updateOrder = async () => {
    // Prepare items data with proper field mapping
    const itemsData = state.order.items.map((item, index) => {
        const itemData = {
            id: item.id,
            product_name: item.product_name,
            link: item.link, // OrderService expects 'link'
            product_price: item.product_price || 0,
            quantity: item.quantity || 1,
            color: item.color, // OrderService expects 'color'
            size: item.size, // OrderService expects 'size'
            weight: item.weight || 0,
            cbm: item.cbm || 0,
            image: item.image || ''
        };

        return itemData;
    });

    // Create form data with items as JSON
    const formData = createFormData({
        customer_id: state.order.customer_id,
        store_id: state.order.store_id,
        order_type: state.order.order_type,
        shipping_type: state.order.order_type === 'shipping' ? state.order.shipping_type : null,
        local_delivery: state.order.local_delivery ? 1 : 0,
        customer_address_id: state.order.local_delivery ? state.order.customer_address_id : null,
        local_shipping: state.order.local_shipping,
        tax: state.order.tax,
        international_shipping: state.order.international_shipping,
        customs_clearance: state.order.customs_clearance,
        service_fee: state.order.service_fee,
        is_free_delivery: state.order.is_free_delivery ? 1 : 0,
        base_cost: state.order.base_cost,
        order_total: state.order.order_total,
        note: state.order.note,
        status_id: state.order.status_id,
        items: JSON.stringify(itemsData), // Send items as JSON string
    });

    // Add image files separately if they exist
    state.order.items.forEach((item, index) => {
        if (item.image_file) {
            formData.append(`images[${index}]`, item.image_file);
        }
    });

    try {
        await axiosRequest.post(`/admin/orders/${orderId}/update`, formData, {notify: true});
        router.push({name: 'orders'});
    } catch (error) {
        if (error.response?.status === 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const fetchOrderData = async () => {
    try {
        state.loading = true;
        const res = await axiosRequest.get('/admin/orders/' + orderId, {});
        const orderData = res.data.data;

        // Populate form with existing order data
        state.order = {
            customer_id: orderData.customer_id || '',
            store_id: orderData.store_id || '',
            order_type: orderData.order_type || 'order',
            shipping_type: orderData.shipping_type || null,
            local_delivery: Boolean(orderData.local_delivery),
            customer_address_id: orderData.customer_address_id || null,
            local_shipping: parseFloat(orderData.local_shipping) || 0,
            tax: parseFloat(orderData.tax) || 0,
            international_shipping: parseFloat(orderData.international_shipping) || 0,
            customs_clearance: parseFloat(orderData.customs_clearance) || 0,
            service_fee: parseFloat(orderData.service_fee) || 0,
            is_free_delivery: Boolean(orderData.is_free_delivery),
            base_cost: parseFloat(orderData.base_cost) || 0,
            order_total: parseFloat(orderData.order_total) || 0,
            note: orderData.note || '',
            status_id: orderData.status?.status_id || '',
            items: orderData.items || [],
        };

        // Set selected values for VueSelect components
        customerAddresses.value = orderData.customer?.addresses || [];
        customerRef.value?.setValue(orderData.customer?.name);
        storeRef.value?.setValue(orderData.store?.name, orderData.store);
        statusRef.value?.setValue(orderData.status?.name);

        // Set image previews for existing items
        if (state.order.items && state.order.items.length > 0) {
            state.order.items.forEach((item, index) => {
                if (item.image && !item.image_preview) {
                    state.order.items[index].image_preview = item.image;
                }
            });
        }

    } catch (error) {
        console.error('Error fetching order:', error);
    } finally {
        state.loading = false;
    }
};

const addItem = () => {
    const newItem = {
        product_name: '',
        link: '',
        color: '',
        size: '',
        quantity: 1,
        product_price: 0,
        weight: 0,
        cbm: 0,
        image: '',
        image_preview: null,
        image_file: null,
    };
    state.order.items.push(newItem);
};

const removeItem = (index) => {
    state.order.items.splice(index, 1);
};

const handleItemChange = (index, field, value) => {
    state.order.items[index][field] = value;
};

const handleImageUpload = (event, index) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            state.order.items[index].image_preview = e.target.result;
            state.order.items[index].image_file = file;
        };
        reader.readAsDataURL(file);
    }
};

const removeImage = (index) => {
    state.order.items[index].image_preview = null;
    state.order.items[index].image_file = null;
    const fileInput = document.getElementById(`item-image-${index}`);
    if (fileInput) fileInput.value = '';
};

const handleCustomerSelect = (customer) => {
    state.order.customer_id = customer.id;
    // Reset address selection when customer changes
    state.order.customer_address_id = null;
    // Get addresses from customer object
    customerAddresses.value = customer.addresses || [];
};

const handleCustomerDeselect = () => {
    state.order.customer_id = '';
    state.order.customer_address_id = null;
    customerAddresses.value = [];
};

const handleStoreSelect = (store) => {
    state.order.store_id = store.id;
};

const handleStoreDeselect = () => {
    state.order.store_id = '';
};

const handleStatusSelect = (status) => {
    state.order.status_id = status.id;
};

// Shipping type options
const shippingTypeOptions = [
    {value: 'air', label: translate('air')},
    {value: 'sea', label: translate('sea')},
    {value: 'land', label: translate('land')}
];

// Computed properties
const isShippingOrder = computed(() => state.order.order_type === 'shipping');
const showShippingTypeSelector = computed(() => isShippingOrder.value);

const totalWeight = computed(() => state.order.items.reduce((sum, item) => sum + parseFloat(item.weight), 0));
const totalCBM = computed(() => state.order.items.reduce((sum, item) => sum + parseFloat(item.cbm), 0));

// Helper function to get shipping type option classes
const getShippingTypeOptionClasses = (shippingType) => {
    const isSelected = state.order.shipping_type === shippingType;
    return [
        'flex items-center gap-2 cursor-pointer rounded-md border px-3 py-2 transition-all duration-200',
        isSelected
            ? 'border-primary bg-primary/10 text-primary'
            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300'
    ];
};

// Helper function to get radio button classes
const getRadioButtonClasses = (shippingType) => {
    const isSelected = state.order.shipping_type === shippingType;
    return [
        'w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all duration-200',
        isSelected
            ? 'border-primary bg-primary'
            : 'border-gray-300 dark:border-gray-600'
    ];
};

// Helper function to format address for display
const formatAddressDisplay = (address) => {
    if (!address) return '';

    const parts = [];
    if (address.address_label) parts.push(address.address_label);

    const addressParts = [];
    if (address.building_name) addressParts.push(address.building_name);
    if (address.apartment_number) addressParts.push('Apt: ' + address.apartment_number);
    if (address.floor) addressParts.push('Floor: ' + address.floor);
    if (address.house) addressParts.push(address.house);
    if (address.street) addressParts.push(address.street);

    const formattedAddress = addressParts.length > 0 ? addressParts.join(', ') : 'Address #' + address.id;

    return parts.length > 0 ? parts.join(' - ') + ' - ' + formattedAddress : formattedAddress;
};

onMounted(() => {
    fetchOrderData();
});

const showCalculateButton = computed(() => storeRef?.value?.state?.selected_option && storeRef?.value?.state?.selected_option.auto_pricing_enabled === 1);
const autoCalculateByStore = () => {
    const selectedStore = storeRef.value.state.selected_option;
    if (selectedStore && selectedStore.auto_pricing_enabled === 1) {
        state.order.local_shipping = parseFloat(selectedStore.default_local_shipping);
        state.order.international_shipping = parseFloat(selectedStore.default_international_shipping);
        state.order.customs_clearance = parseFloat(selectedStore.default_customs_clearance);
        state.order.tax = parseFloat(selectedStore.default_tax);

        let calculateUnit = 0;
        let calculatedServiceFee = 0;
        if (selectedStore.pricing_method === 'weight_based') {
            calculateUnit = parseFloat(selectedStore.profit_per_weight_unit);
            calculatedServiceFee = totalWeight.value * calculateUnit;
        } else if (selectedStore.pricing_method === 'percentage') {
            calculateUnit = parseFloat(selectedStore.profit_percentage);
            calculatedServiceFee = totalItemsPrice.value * (calculateUnit / 100);
        } else if (selectedStore.pricing_method === 'cbm') {
            calculateUnit = parseFloat(selectedStore.profit_per_cbm);
            calculatedServiceFee = totalCBM.value * calculateUnit;
        }
        calculatedServiceFee = calculatedServiceFee + parseFloat(selectedStore.base_fee);
        state.order.service_fee = calculatedServiceFee;
    }
}

const breadcrumbItems = [
    {label: translate('orders'), url: '/admin/orders'},
    {label: translate('edit'), url: null}
];

</script>

<template>

    <!-- Main container -->
    <div>
        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

        </div>

        <!-- Loading State -->
        <div v-if="state.loading" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <form>
            <div>
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <!-- Main Form Section -->
                    <div class="xl:col-span-2 space-y-6">
                        <!-- Customer, Store & Status -->
                        <div class="panel">
                            <div class="flex items-center justify-between mb-5">
                                <h5 class="font-semibold text-lg dark:text-white-light">
                                    {{ translate('customer_and_status') }}</h5>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label>{{ translate('customer') }}</label>
                                    <vue-select
                                        ref="customerRef"
                                        model="name"
                                        url="/admin/customers/search"
                                        :placeholder="translate('search_customer')"
                                        @option:selected="handleCustomerSelect"
                                        @option:deselected="handleCustomerDeselect"
                                    />
                                    <div class="text-danger text-sm mt-1">{{ state.errors.customer_id }}</div>
                                </div>
                                <div>
                                    <label>{{ translate('store') }}</label>
                                    <vue-select
                                        ref="storeRef"
                                        model="name"
                                        url="/admin/stores/search"
                                        :placeholder="translate('select_store')"
                                        @option:selected="handleStoreSelect"
                                        @option:deselected="handleStoreDeselect"
                                    />
                                    <div class="text-danger text-sm mt-1">{{ state.errors.store_id }}</div>
                                </div>
                                <div>
                                    <label>{{ translate('status') }}</label>
                                    <vue-select
                                        ref="statusRef"
                                        model="name"
                                        url="/admin/statuses/search?status_type=order"
                                        :placeholder="translate('select_status')"
                                        @option:selected="handleStatusSelect"
                                        @option:deselected="() => state.order.status_id = ''"
                                    />
                                    <div class="text-danger text-sm mt-1">{{ state.errors.status_id }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="panel">
                            <div class="flex items-center justify-between mb-5">
                                <div class="">
                                    <h5 class="font-semibold text-lg dark:text-white-light">{{
                                            translate('order_items')
                                        }}</h5>
                                    <span class="font-semibold text-sm mt-2 me-2">T.Weight: {{
                                            totalWeight.toFixed(2)
                                        }}</span>
                                    <span class="font-semibold text-sm">T.CBM: {{ totalCBM.toFixed(2) }}m³</span>
                                </div>
                                <button
                                    type="button"
                                    @click="addItem"
                                    class="btn btn-primary btn-sm"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ translate('add_item') }}
                                </button>
                            </div>

                            <!-- Empty State -->
                            <div v-if="state.order.items.length === 0" class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">{{ translate('no_items_added') }}</p>
                                <p class="text-sm text-gray-400 mt-1">{{ translate('click_add_item_to_start') }}</p>
                            </div>

                            <!-- Items List -->
                            <div v-else class="space-y-3">
                                <div v-for="(item, index) in state.order.items" :key="index"
                                     class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                                    <!-- Item Header -->
                                    <div
                                        class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-700">
                                        <div class="flex items-center gap-3">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Item {{
                                                index + 1
                                            }}</span>
                                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{
                                                    orderCurrencyType?.symbol || '$'
                                                }}{{ (item.quantity * item.product_price).toFixed(2) }}
                                            </div>
                                        </div>
                                        <button
                                            @click="removeItem(index)"
                                            type="button"
                                            class="btn btn-danger btn-sm"
                                            :title="translate('remove_item')"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Item Content -->
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                                            <!-- Product Image -->
                                            <div class="lg:col-span-2">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{
                                                        translate('image')
                                                    }}</label>
                                                <div class="relative w-full aspect-square max-w-20">
                                                    <div v-if="item.image_preview || item.image" class="relative group">
                                                        <img :src="item.image_preview || item.image"
                                                             class="w-full h-full object-cover rounded-lg border border-gray-200 dark:border-gray-600"/>
                                                        <button
                                                            @click="removeImage(index)"
                                                            class="absolute -top-1 -right-1 bg-red-500 hover:bg-red-600 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                                                        >
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                 viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                      stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </button>
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
                                                    <input
                                                        :id="`item-image-${index}`"
                                                        type="file"
                                                        @change="handleImageUpload($event, index)"
                                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                                        accept="image/*"
                                                    />
                                                </div>
                                            </div>

                                            <!-- Product Details -->
                                            <div class="lg:col-span-10">
                                                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                                    <!-- Product Name -->
                                                    <div class="md:col-span-1 xl:col-span-1">
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('product_name') }} <span
                                                            class="text-red-500">*</span>
                                                        </label>
                                                        <input
                                                            v-model="item.product_name"
                                                            type="text"
                                                            class="form-input"
                                                            :placeholder="translate('product_name')"
                                                        />
                                                    </div>

                                                    <!-- Product Link -->
                                                    <div class="md:col-span-1 xl:col-span-2">
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('product_link') }} <span
                                                            class="text-red-500">*</span>
                                                        </label>
                                                        <input
                                                            v-model="item.link"
                                                            type="url"
                                                            class="form-input"
                                                            :placeholder="translate('product_link')"
                                                        />
                                                    </div>

                                                    <!-- Color -->
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{
                                                                translate('color')
                                                            }}</label>
                                                        <input
                                                            v-model="item.color"
                                                            type="text"
                                                            class="form-input"
                                                            :placeholder="translate('color')"
                                                        />
                                                    </div>

                                                    <!-- Size -->
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{
                                                                translate('size')
                                                            }}</label>
                                                        <input
                                                            v-model="item.size"
                                                            type="text"
                                                            class="form-input"
                                                            :placeholder="translate('size')"
                                                        />
                                                    </div>

                                                    <!-- Weight (optional) -->
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('weight') }}
                                                        </label>
                                                        <input
                                                            v-model.number="item.weight"
                                                            type="number"
                                                            step="any"
                                                            min="0"
                                                            class="form-input"
                                                            :placeholder="translate('weight')"
                                                        />
                                                    </div>

                                                    <!-- Quantity -->
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('quantity') }} <span
                                                            class="text-red-500">*</span>
                                                        </label>
                                                        <input
                                                            v-model.number="item.quantity"
                                                            type="number"
                                                            min="1"
                                                            class="form-input"
                                                            :placeholder="translate('qty')"
                                                        />
                                                    </div>

                                                    <!-- Price -->
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('price') }} <span class="text-red-500">*</span>
                                                        </label>
                                                        <div class="relative">
                                                            <div
                                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                            <span class="text-gray-500 text-sm">{{
                                                                    orderCurrencyType?.symbol || '$'
                                                                }}</span>
                                                            </div>
                                                            <input
                                                                v-model.number="item.product_price"
                                                                type="number"
                                                                step="any"
                                                                min="0"
                                                                class="form-input pl-8"
                                                                :placeholder="translate('price')"
                                                            />
                                                        </div>
                                                    </div>

                                                    <!-- CBM (optional) -->
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                            {{ translate('cbm') }}
                                                        </label>
                                                        <input
                                                            v-model.number="item.cbm"
                                                            type="number"
                                                            step="any"
                                                            min="0"
                                                            class="form-input"
                                                            :placeholder="translate('cbm')"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="panel">
                            <div class="flex items-center justify-between mb-5">
                                <h5 class="font-semibold text-lg dark:text-white-light">
                                    {{ translate('additional_information') }}</h5>
                            </div>

                            <div class="space-y-4">
                                <!-- Local Delivery Section -->
                                <div
                                    class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 border border-blue-200 dark:border-blue-800">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="state.order.local_delivery"
                                            type="checkbox"
                                            class="form-checkbox w-4 h-4 text-blue-600 rounded focus:ring-blue-500 focus:ring-2"
                                        />
                                        <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ translate('local_delivery') }}
                                        </span>
                                    </label>

                                    <!-- Address Selector (shown when local_delivery is checked) -->
                                    <div v-if="state.order.local_delivery" class="mt-3 space-y-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">
                                            {{ translate('delivery_address') }} <span class="text-red-500">*</span>
                                        </label>

                                        <div v-if="!state.order.customer_id"
                                             class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded p-2">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 flex-shrink-0"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                                <span class="text-xs text-amber-800 dark:text-amber-300">
                                                    {{ translate('please_select_customer_first') }}
                                                </span>
                                            </div>
                                        </div>

                                        <div v-else-if="customerAddresses.length === 0"
                                             class="bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded p-2">
                                            <div class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none"
                                                     stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                </svg>
                                                <span class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ translate('no_addresses_found') }}
                                                </span>
                                            </div>
                                        </div>

                                        <select
                                            v-else
                                            v-model="state.order.customer_address_id"
                                            class="form-select text-sm py-1.5"
                                        >
                                            <option value="">{{ translate('select_delivery_address') }}</option>
                                            <option
                                                v-for="address in customerAddresses"
                                                :key="address.id"
                                                :value="address.id"
                                            >
                                                {{ formatAddressDisplay(address) }}
                                            </option>
                                        </select>

                                        <div class="text-danger text-xs mt-1">{{
                                                state.errors.customer_address_id
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Note Section -->
                                <div>
                                    <label for="note">{{ translate('note') }}</label>
                                    <textarea
                                        id="note"
                                        v-model="state.order.note"
                                        rows="3"
                                        class="form-textarea"
                                        :placeholder="translate('add_notes_here')"
                                    ></textarea>
                                    <div class="text-danger text-sm mt-1">{{ state.errors.note }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Sidebar -->
                    <div class="xl:col-span-1 space-y-6">
                        <!-- Order Type Selector -->
                        <div class="panel">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-semibold text-base dark:text-white-light">{{
                                        translate('order_type')
                                    }}</h5>
                            </div>
                            <div class="flex gap-3">
                                <!-- Order Type Option -->
                                <div
                                    @click="state.order.order_type = 'order'"
                                    :class="[
                                        'flex items-center gap-2 cursor-pointer rounded-md border px-3 py-2 transition-all duration-200',
                                        state.order.order_type === 'order'
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300'
                                    ]"
                                >
                                    <div
                                        :class="[
                                            'w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all duration-200',
                                            state.order.order_type === 'order'
                                                ? 'border-primary bg-primary'
                                                : 'border-gray-300 dark:border-gray-600'
                                        ]"
                                    >
                                        <div
                                            v-if="state.order.order_type === 'order'"
                                            class="w-1.5 h-1.5 bg-white rounded-full"
                                        ></div>
                                    </div>
                                    <div
                                        class="w-5 h-5 bg-blue-100 dark:bg-blue-900/30 rounded flex items-center justify-center">
                                        <svg class="w-3 h-3 text-blue-600 dark:text-blue-400" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium">{{ translate('order') }}</span>
                                </div>

                                <!-- Shipping Type Option -->
                                <div
                                    @click="state.order.order_type = 'shipping'"
                                    :class="[
                                        'flex items-center gap-2 cursor-pointer rounded-md border px-3 py-2 transition-all duration-200',
                                        state.order.order_type === 'shipping'
                                            ? 'border-primary bg-primary/10 text-primary'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300'
                                    ]"
                                >
                                    <div
                                        :class="[
                                            'w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all duration-200',
                                            state.order.order_type === 'shipping'
                                                ? 'border-primary bg-primary'
                                                : 'border-gray-300 dark:border-gray-600'
                                        ]"
                                    >
                                        <div
                                            v-if="state.order.order_type === 'shipping'"
                                            class="w-1.5 h-1.5 bg-white rounded-full"
                                        ></div>
                                    </div>
                                    <div
                                        class="w-5 h-5 bg-green-100 dark:bg-green-900/30 rounded flex items-center justify-center">
                                        <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none"
                                             stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium">{{ translate('shipping') }}</span>
                                </div>
                            </div>

                            <!-- Shipping Type Selector (only shown when order_type is shipping) -->
                            <div v-if="showShippingTypeSelector" class="mt-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="font-semibold text-base dark:text-white-light">
                                        {{ translate('shipping_type') }}
                                    </h5>
                                </div>
                                <div class="flex gap-3">
                                    <div
                                        v-for="option in shippingTypeOptions"
                                        :key="option.value"
                                        @click="state.order.shipping_type = option.value"
                                        :class="getShippingTypeOptionClasses(option.value)"
                                    >
                                        <div :class="getRadioButtonClasses(option.value)">
                                            <div
                                                v-if="state.order.shipping_type === option.value"
                                                class="w-1.5 h-1.5 bg-white rounded-full"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium">{{ option.label }}</span>
                                    </div>
                                </div>
                                <div class="text-danger text-sm mt-1">{{ state.errors.shipping_type }}</div>
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="panel sticky top-6">
                            <div class="flex items-center justify-between mb-5">
                                <h5 class="font-semibold text-lg dark:text-white-light">{{
                                        translate('order_summary')
                                    }}</h5>
                                <button
                                    v-if="showCalculateButton"
                                    type="button"
                                    class="btn btn-primary btn-sm flex items-center justify-center gap-1.5"
                                    @click="autoCalculateByStore"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-xs">{{ translate('calculate') }}</span>
                                </button>
                            </div>

                            <!-- Cost Breakdown -->
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">{{ translate('subtotal') }}</span>
                                    <span class="font-medium">{{
                                            orderCurrencyType?.symbol || '$'
                                        }}{{ totalItemsPrice.toFixed(2) }}</span>
                                </div>

                                <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-2">{{
                                                    translate('local_shipping')
                                                }}</label>
                                            <div class="flex">
                                                <div
                                                    class="bg-gray-100 dark:bg-gray-700 flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600">
                                                    {{ orderCurrencyType?.symbol || '$' }}
                                                </div>
                                                <input
                                                    v-model.number="state.order.local_shipping"
                                                    type="number"
                                                    step="any"
                                                    min="0"
                                                    class="form-input rounded-l-none flex-1"
                                                    :placeholder="translate('enter_amount')"
                                                />
                                            </div>
                                            <div class="text-danger text-sm mt-1">{{
                                                    state.errors.local_shipping
                                                }}
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-2">{{
                                                    translate('international_shipping')
                                                }}</label>
                                            <div class="flex">
                                                <div
                                                    class="bg-gray-100 dark:bg-gray-700 flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600">
                                                    {{ orderCurrencyType?.symbol || '$' }}
                                                </div>
                                                <input
                                                    v-model.number="state.order.international_shipping"
                                                    type="number"
                                                    step="any"
                                                    min="0"
                                                    class="form-input rounded-l-none flex-1"
                                                    :placeholder="translate('enter_amount')"
                                                />
                                            </div>
                                            <div class="text-danger text-sm mt-1">
                                                {{ state.errors.international_shipping }}
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-2">{{
                                                    translate('customs_clearance')
                                                }}</label>
                                            <div class="flex">
                                                <div
                                                    class="bg-gray-100 dark:bg-gray-700 flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600">
                                                    {{ orderCurrencyType?.symbol || '$' }}
                                                </div>
                                                <input
                                                    v-model.number="state.order.customs_clearance"
                                                    type="number"
                                                    step="any"
                                                    min="0"
                                                    class="form-input rounded-l-none flex-1"
                                                    :placeholder="translate('enter_amount')"
                                                />
                                            </div>
                                            <div class="text-danger text-sm mt-1">{{
                                                    state.errors.customs_clearance
                                                }}
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-2">{{
                                                    translate('service_fee')
                                                }}</label>
                                            <div class="flex">
                                                <div
                                                    class="bg-gray-100 dark:bg-gray-700 flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600">
                                                    {{ orderCurrencyType?.symbol || '$' }}
                                                </div>
                                                <input
                                                    v-model.number="state.order.service_fee"
                                                    type="number"
                                                    step="any"
                                                    min="0"
                                                    class="form-input rounded-l-none flex-1"
                                                    :placeholder="translate('enter_amount')"
                                                />
                                            </div>
                                            <div class="text-danger text-sm mt-1">{{
                                                    state.errors.service_fee
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="state.order.is_free_delivery"
                                            type="checkbox"
                                            class="form-checkbox"
                                        />
                                        <span class="text-sm ml-2 text-blue-700 dark:text-blue-300">{{
                                                translate('free_delivery')
                                            }}</span>
                                    </label>
                                </div>

                                <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                                    <div class="flex justify-between text-lg font-semibold">
                                        <span>{{ translate('order_total') }}</span>
                                        <span class="text-primary">{{
                                                orderCurrencyType?.symbol || '$'
                                            }}{{ orderTotal.toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tax Rate -->
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                                <div>
                                    <label class="block text-sm font-medium mb-2">
                                        {{ translate('tax_rate') }} ({{ state.order.tax }}%) -
                                        {{ orderCurrencyType?.symbol || '$' }}{{ taxAmount.toFixed(2) }}
                                    </label>
                                    <input
                                        v-model.number="state.order.tax"
                                        type="number"
                                        step="any"
                                        min="0"
                                        max="100"
                                        class="form-input"
                                        :placeholder="translate('enter_tax_rate')"
                                    />
                                    <div class="text-danger text-sm mt-1">{{ state.errors.tax }}</div>
                                </div>
                            </div>

                            <!-- Base Cost & Order Total -->
                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-600 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium mb-2">{{ translate('base_cost') }}</label>
                                    <div
                                        class="form-input bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white font-medium text-center">
                                        {{ orderCurrencyType?.symbol || '$' }}{{ baseCost.toFixed(2) }}
                                    </div>
                                    <div class="text-danger text-sm mt-1">{{ state.errors.base_cost }}</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-2">{{ translate('order_total') }}</label>
                                    <div
                                        class="form-input bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-300 font-medium text-center">
                                        {{ orderCurrencyType?.symbol || '$' }}{{ orderTotal.toFixed(2) }}
                                    </div>
                                    <div class="text-danger text-sm mt-1">{{ state.errors.order_total }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="flex justify-end items-center mt-8 bg-gray-100 border-t border-gray-300 px-3 py-2">
                <cancel-button href="/admin/orders"/>
                <submit-button @click.prevent="handleSubmit" :disabled="state.disableSubmit"/>
            </div>
        </form>
    </div>

</template>
