<script setup>
import {reactive, onMounted, ref, nextTick} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import {createFormData, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import VueSelect from "../components/VueSelect.vue";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import FileUploadWithPreview from 'file-upload-with-preview';
import 'file-upload-with-preview/dist/file-upload-with-preview.min.css';
import '../../assets/css/file-upload-preview.css';
import {useRoute, useRouter} from 'vue-router';
import {useAppStore} from "../../stores";

const router = useRouter();
const route = useRoute();
const store = useAppStore();
const storeId = route.params.id;
const pageType = route.name === 'edit_store' && storeId ? 'edit' : 'create';

const state = reactive({
    store: {
        name: '',
        website_url: '',
        image: '',
        country_id: '',
        profit_per_weight_unit: 0,
        weight_type: 'lb',
        profit_per_cbm: 0,
        base_fee: 0,
        pricing_method: 'weight_based',
        profit_percentage: null,
        currency_type_id: '',
        auto_pricing_enabled: false,
        default_local_shipping: 0,
        default_international_shipping: 0,
        default_customs_clearance: 0,
        default_tax: 0,
    },
    errors: {},
    disableSubmit: false,
});

const countryRef = ref(null);
const currencyTypeRef = ref(null);
let fileUpload = null;

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(storeId);
    } else {
        resetStoreData();
    }
    initializeFileUpload();
});

const initializeFileUpload = () => {
    if (!fileUpload) {
        nextTick(() => {
            fileUpload = new FileUploadWithPreview('storeImage', {
                images: {
                    baseImage: '/assets/images/file-preview.svg',
                    backgroundImage: '',
                },
            });
        });
    }
};

const fetchEditData = async (store_id) => {
    try {
        const res = await axiosRequest.get("/admin/stores/" + store_id, {});
        const store = res.data.data;

        state.store = {
            name: store.name,
            website_url: store.website_url,
            country_id: store.country?.id || '',
            profit_per_weight_unit: parseFloat(store.profit_per_weight_unit) || 0,
            weight_type: store.weight_type || 'lb',
            profit_per_cbm: parseFloat(store.profit_per_cbm) || 0,
            base_fee: parseFloat(store.base_fee) || 0,
            pricing_method: store.pricing_method || 'weight_based',
            profit_percentage: store.profit_percentage ? parseFloat(store.profit_percentage) : null,
            currency_type_id: store.currency_type?.id || '',
            auto_pricing_enabled: Boolean(store.auto_pricing_enabled),
            default_local_shipping: parseFloat(store.default_local_shipping) || 0,
            default_international_shipping: parseFloat(store.default_international_shipping) || 0,
            default_customs_clearance: parseFloat(store.default_customs_clearance) || 0,
            default_tax: parseFloat(store.default_tax) || 0,
        };

        // Set country value in VueSelect
        if (store.country) {
            countryRef.value?.setValue(store.country.name);
        }

        // Set currency type value in VueSelect
        if (store.currency_type) {
            currencyTypeRef.value?.setValue(store.currency_type.name);
        }
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    // Get the uploaded file from the file upload component
    const cachedFileArray = fileUpload.cachedFileArray;
    if (cachedFileArray && cachedFileArray.length > 0) {
        state.store.image = cachedFileArray[0];
    }

    if (pageType == 'create') {
        await createStore();
    } else if (pageType == 'edit') {
        await editStore();
    }

    state.disableSubmit = false;
};

const createStore = async () => {
    let url = "/admin/stores/create";
    // Prepare data with boolean conversion
    const storeData = {
        ...state.store,
        auto_pricing_enabled: state.store.auto_pricing_enabled ? 1 : 0,
    };
    let formData = createFormData(storeData);
    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/stores');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editStore = async () => {
    let url = "/admin/stores/" + storeId + "/update";
    // Prepare data with boolean conversion
    const storeData = {
        ...state.store,
        auto_pricing_enabled: state.store.auto_pricing_enabled ? 1 : 0,
    };
    let formData = createFormData(storeData);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/stores');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetStoreData = () => {
    state.store = {
        name: '',
        website_url: '',
        image: '',
        country_id: '',
        profit_per_weight_unit: 0,
        weight_type: 'lb',
        profit_per_cbm: 0,
        base_fee: 0,
        pricing_method: 'weight_based',
        profit_percentage: null,
        currency_type_id: '',
        auto_pricing_enabled: false,
        default_local_shipping: 0,
        default_international_shipping: 0,
        default_customs_clearance: 0,
        default_tax: 0,
    };

    // Clear the file upload preview
    if (fileUpload) {
        fileUpload.clearPreviewPanel();
    }

    if (countryRef.value) {
        countryRef.value.reset();
    }

    if (currencyTypeRef.value) {
        currencyTypeRef.value.reset();
    }

    state.errors = {};
};

const handleFormSelectDeselected = (type) => {
    state.store[type] = null;
};

const getCurrencySymbol = () => {
    if (!state.store.currency_type_id) return '$';
    const currency = store.currencyTypeList?.find(c => c.id === state.store.currency_type_id);
    return currency?.symbol || '$';
};

const breadcrumbItems = [
    {label: translate('stores'), url: '/admin/stores'},
    {label: pageType === 'create' ? translate('add') : translate('edit'), url: null}
];

</script>

<template>
    <!-- Main container -->
    <div>
        <!-- Top row: Breadcrumb -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>
        </div>

        <form @submit.prevent="handleSubmit">
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <!-- Left Column: Basic Information -->
                <div class="xl:col-span-2 space-y-6">
                    <!-- Basic Information Panel -->
                    <div class="panel">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-store text-blue-600 dark:text-blue-400 text-sm"></i>
                                </div>
                                <h5 class="font-semibold text-lg dark:text-white-light">
                                    {{ translate('basic_information') }}
                                </h5>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="store-name">{{ translate('name') }} <span class="text-red-500">*</span></label>
                    <input type="text" class="form-input" id="store-name" :placeholder="translate('name')" v-model="state.store.name">
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.name }}</div>
                </div>

                            <div>
                    <label for="store-website-url">{{ translate('website_url') }}</label>
                    <input type="text" class="form-input" id="store-website-url" :placeholder="translate('website_url')" v-model="state.store.website_url">
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.website_url }}</div>
                </div>

                            <div>
                    <label class="block text-sm font-medium mb-1">{{ translate('country') }}</label>
                    <vue-select
                        model="name"
                        ref="countryRef"
                        :url="`/admin/countries/search`"
                        :placeholder="translate('select_country')"
                        @option:selected="(data) => state.store.country_id = data.id"
                        @option:deselected="handleFormSelectDeselected('country_id')"/>
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.country_id }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1">
                                    {{ translate('currency') }} <span v-if="state.store.auto_pricing_enabled" class="text-red-500">*</span>
                                </label>
                                <vue-select
                                    model="name"
                                    ref="currencyTypeRef"
                                    url="/admin/currency-types/search"
                                    :placeholder="translate('select_currency')"
                                    @option:selected="(data) => state.store.currency_type_id = data.id"
                                    @option:deselected="handleFormSelectDeselected('currency_type_id')"
                                />
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.currency_type_id }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Enable Automatic Pricing Toggle -->
                    <div class="panel">
                        <div class="space-y-4">
                            <label class="flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="state.store.auto_pricing_enabled"
                                    class="form-checkbox"
                                />
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {{ translate('enable_automatic_pricing') }}
                                </span>
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ translate('automatic_pricing_description') }}
                            </p>
                        </div>
                    </div>

                    <!-- Shipping & Fees Panel -->
                    <div v-if="state.store.auto_pricing_enabled" class="panel">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-shipping-fast text-green-600 dark:text-green-400 text-sm"></i>
                                </div>
                                <h5 class="font-semibold text-lg dark:text-white-light">
                                    {{ translate('shipping_and_fees') }}
                                </h5>
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                            {{ translate('shipping_fees_description') }}
                        </p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ translate('local_shipping') }}
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">{{ getCurrencySymbol() }}</span>
                                    </div>
                                    <input
                                        type="number"
                                        v-model.number="state.store.default_local_shipping"
                                        step="0.01"
                                        min="0"
                                        class="form-input pl-8"
                                        :placeholder="translate('enter_local_shipping')"
                                    />
                                </div>
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.default_local_shipping }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ translate('international_shipping') }}
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">{{ getCurrencySymbol() }}</span>
                                    </div>
                                    <input
                                        type="number"
                                        v-model.number="state.store.default_international_shipping"
                                        step="0.01"
                                        min="0"
                                        class="form-input pl-8"
                                        :placeholder="translate('enter_international_shipping')"
                                    />
                                </div>
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.default_international_shipping }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ translate('customs_clearance') }}
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">{{ getCurrencySymbol() }}</span>
                                    </div>
                                    <input
                                        type="number"
                                        v-model.number="state.store.default_customs_clearance"
                                        step="0.01"
                                        min="0"
                                        class="form-input pl-8"
                                        :placeholder="translate('enter_customs_clearance')"
                                    />
                                </div>
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.default_customs_clearance }}</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ translate('tax') }} (%)
                                </label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        v-model.number="state.store.default_tax"
                                        step="0.01"
                                        min="0"
                                        max="100"
                                        class="form-input pr-8"
                                        :placeholder="translate('enter_tax_rate')"
                                    />
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">%</span>
                                    </div>
                                </div>
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.default_tax }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Automatic Pricing Section -->
                    <div v-if="state.store.auto_pricing_enabled" class="panel">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-primary/10 dark:bg-primary/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-calculator text-primary text-sm"></i>
                                </div>
                                <h5 class="font-semibold text-lg dark:text-white-light">
                                    {{ translate('automatic_pricing') }}
                                </h5>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Pricing Method -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ translate('pricing_method') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="state.store.pricing_method"
                                        class="form-select"
                                    >
                                        <option value="weight_based">{{ translate('weight_based') }}</option>
                                        <option value="percentage">{{ translate('percentage_based') }}</option>
                                        <option value="cbm">{{ translate('cbm_based') }}</option>
                                    </select>
                                    <div class="text-red-500 text-sm mt-1">{{ state.errors.pricing_method }}</div>
                                </div>

                                <!-- Profit Per Weight Unit (Weight Based) -->
                                <div v-if="state.store.pricing_method === 'weight_based'">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ translate('profit_per_weight_unit') }} <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 text-sm">{{ getCurrencySymbol() }}</span>
                                                </div>
                                                <input
                                                    type="number"
                                                    v-model.number="state.store.profit_per_weight_unit"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-input pl-8"
                                                    :placeholder="translate('enter_profit_per_weight_unit')"
                                                />
                                            </div>
                                            <div class="text-red-500 text-sm mt-1">{{ state.errors.profit_per_weight_unit }}</div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                {{ translate('weight_type') }} <span class="text-red-500">*</span>
                                            </label>
                                            <select
                                                v-model="state.store.weight_type"
                                                class="form-select"
                                            >
                                                <option value="kg">kg</option>
                                                <option value="lb">lb</option>
                                            </select>
                                            <div class="text-red-500 text-sm mt-1">{{ state.errors.weight_type }}</div>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ translate('profit_per_weight_unit_description') }}
                                    </p>
                                </div>

                                <!-- Profit Per CBM (CBM Based) -->
                                <div v-if="state.store.pricing_method === 'cbm'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ translate('profit_per_cbm') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">{{ getCurrencySymbol() }}</span>
                                        </div>
                                        <input
                                            type="number"
                                            v-model.number="state.store.profit_per_cbm"
                                            step="0.01"
                                            min="0"
                                            class="form-input pl-8"
                                            :placeholder="translate('enter_profit_per_cbm')"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ translate('profit_per_cbm_description') }}
                                    </p>
                                    <div class="text-red-500 text-sm mt-1">{{ state.errors.profit_per_cbm }}</div>
                                </div>

                                <!-- Base Fee -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ translate('base_fee') }}
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">{{ getCurrencySymbol() }}</span>
                                        </div>
                                        <input
                                            type="number"
                                            v-model.number="state.store.base_fee"
                                            step="0.01"
                                            min="0"
                                            class="form-input pl-8"
                                            :placeholder="translate('enter_base_fee')"
                                        />
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ translate('base_fee_description') }}
                                    </p>
                                    <div class="text-red-500 text-sm mt-1">{{ state.errors.base_fee }}</div>
                                </div>

                                <!-- Profit Percentage -->
                                <div v-if="state.store.pricing_method === 'percentage'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ translate('profit_percentage') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            type="number"
                                            v-model.number="state.store.profit_percentage"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            class="form-input pr-8"
                                            :placeholder="translate('enter_profit_percentage')"
                                        />
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-sm">%</span>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ translate('profit_percentage_description') }}
                                    </p>
                                    <div class="text-red-500 text-sm mt-1">{{ state.errors.profit_percentage }}</div>
                                </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Store Image -->
                <div class="xl:col-span-1">
                    <div class="panel sticky top-6">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-image text-purple-600 dark:text-purple-400 text-sm"></i>
                                </div>
                                <h5 class="font-semibold text-lg dark:text-white-light">
                                    {{ translate('store_image') }}
                                </h5>
                            </div>
                        </div>

                        <div class="space-y-4">
                    <div class="custom-file-container" data-upload-id="storeImage">
                        <div class="label-container">
                            <label>{{ translate('image') }}</label>
                            <a href="javascript:;" class="custom-file-container__image-clear" title="Clear Image">×</a>
                        </div>
                        <label class="custom-file-container__custom-file">
                            <input type="file" class="custom-file-container__custom-file__custom-file-input" accept="image/*" />
                            <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
                            <span class="custom-file-container__custom-file__custom-file-control ltr:pr-20 rtl:pl-20"></span>
                        </label>
                        <div class="custom-file-container__image-preview"></div>
                    </div>
                    <div class="text-red-500 text-sm">{{ state.errors.image }}</div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ translate('store_image_description') }}
                            </p>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Action buttons -->
            <div class="flex justify-end gap-3 mt-6 bg-gray-100 dark:bg-gray-800 border-t border-gray-300 dark:border-gray-700 px-3 py-2 -mx-3 -mb-3">
                    <cancel-button @click="router.push('/admin/stores')" />
                    <submit-button :disabled="state.disableSubmit" />
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
