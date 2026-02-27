<script setup>
import {reactive, onMounted, ref} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import VueSelect from "../components/VueSelect.vue";
import {translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const warehouseId = route.params.id;
const pageType = route.name === 'edit_warehouse' && warehouseId ? 'edit' : 'create';

const state = reactive({
    warehouse: {
        name: '',
        address_line_1: '',
        address_line_2: '',
        phone_number: '',
        email: '',
        postal_code: '',
        street: '',
        country_id: '',
        city_id: '',
    },
    errors: {},
    disableSubmit: false,
});

const country_ref = ref(null);
const city_ref = ref(null);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(warehouseId);
    } else {
        resetWarehouseData();
    }
});

const fetchEditData = async (warehouse_id) => {
    try {
        const res = await axiosRequest.get("/admin/warehouses/" + warehouse_id, {});
        const warehouse = res.data.data;

        state.warehouse = {
            name: warehouse.name,
            address_line_1: warehouse.address_line_1,
            address_line_2: warehouse.address_line_2 || '',
            phone_number: warehouse.phone_number || '',
            email: warehouse.email || '',
            postal_code: warehouse.postal_code || '',
            street: warehouse.street || '',
            country_id: warehouse.country_id,
            city_id: warehouse.city_id,
        };

        country_ref.value.setValue(warehouse.country?.name);
        city_ref.value.setValue(warehouse.city?.name);
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createWarehouse();
    } else if (pageType == 'edit') {
        await editWarehouse();
    }

    state.disableSubmit = false;
};

const createWarehouse = async () => {
    let url = "/admin/warehouses/create";
    let data = state.warehouse;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/warehouses');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editWarehouse = async () => {
    let url = "/admin/warehouses/" + warehouseId + "/update";
    let data = state.warehouse;

    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/warehouses');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetWarehouseData = () => {
    state.warehouse = {
        name: '',
        address_line_1: '',
        address_line_2: '',
        phone_number: '',
        email: '',
        postal_code: '',
        street: '',
        country_id: '',
        city_id: '',
    };
    country_ref.value?.reset();
    city_ref.value?.reset();
    state.errors = {};
};

const handleCountryDeselected = () => {
    state.warehouse['country_id'] = null;
    state.warehouse['city_id'] = null;
    city_ref.value?.reset();
};

const handleCityDeselected = () => {
    state.warehouse['city_id'] = null;
};

const breadcrumbItems = [
    {label: translate('warehouses'), url: '/admin/warehouses'},
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
            <div class="space-y-4">
                <!-- Basic Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-warehouse text-blue-600 dark:text-blue-400 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('basic_information') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label for="warehouse-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('name') }}</label>
                            <input type="text" class="form-input" id="warehouse-name" v-model="state.warehouse.name" :placeholder="translate('warehouse_name')">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.name }}</div>
                        </div>
                    </div>
                </div>

                <!-- Address Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-map-marker-alt text-green-600 dark:text-green-400 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('address_information') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="warehouse-address-1" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('address_line_1') }}</label>
                            <input type="text" class="form-input" id="warehouse-address-1" v-model="state.warehouse.address_line_1" :placeholder="translate('address_line_1')">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.address_line_1 }}</div>
                        </div>
                        <div>
                            <label for="warehouse-address-2" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('address_line_2') }}</label>
                            <input type="text" class="form-input" id="warehouse-address-2" v-model="state.warehouse.address_line_2" :placeholder="translate('address_line_2')">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.address_line_2 }}</div>
                        </div>
                        <div>
                            <label for="warehouse-street" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('street') }}</label>
                            <input type="text" class="form-input" id="warehouse-street" v-model="state.warehouse.street" :placeholder="translate('street')">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.street }}</div>
                        </div>
                        <div>
                            <label for="warehouse-postal" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('postal_code') }}</label>
                            <input type="text" class="form-input" id="warehouse-postal" v-model="state.warehouse.postal_code" :placeholder="translate('postal_code')">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.postal_code }}</div>
                        </div>
                    </div>
                </div>

                <!-- Location Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-globe text-purple-600 dark:text-purple-400 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('location_information') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('country') }}</label>
                            <vue-select model="name" ref="country_ref"
                                        url="/admin/countries/search"
                                        :placeholder="translate('select_country')"
                                        @option:selected="(data) => state.warehouse.country_id = data.id"
                                        @option:deselected="handleCountryDeselected" />
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.country_id }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('city') }}</label>
                            <vue-select model="name" ref="city_ref"
                                        :url="`/admin/cities/search?country_id=${state.warehouse.country_id}`"
                                        :placeholder="translate('select_city')"
                                        @option:selected="(data) => state.warehouse.city_id = data.id"
                                        @option:deselected="handleCityDeselected"/>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.city_id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-phone text-orange-600 dark:text-orange-400 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('contact_information') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="warehouse-phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('phone_number') }}</label>
                            <input type="tel" class="form-input" id="warehouse-phone" v-model="state.warehouse.phone_number" :placeholder="translate('phone_number')">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.phone_number }}</div>
                        </div>
                        <div>
                            <label for="warehouse-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('email') }}</label>
                            <input type="email" class="form-input" id="warehouse-email" v-model="state.warehouse.email" :placeholder="translate('email')">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.email }}</div>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-end gap-3">
                        <cancel-button @click="router.push('/admin/warehouses')" />
                        <submit-button :disabled="state.disableSubmit" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
