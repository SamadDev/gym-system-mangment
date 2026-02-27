<script setup>
import {reactive, onMounted, ref} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import {translate} from '../../utils/functions.js';
import VueSelect from "../components/VueSelect.vue";
import {axiosRequest} from "../../utils/apiRequest.js";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const customerId = route.params.id;
const pageType = route.name === 'edit_customer' && customerId ? 'edit' : 'create';

const state = reactive({
    customer: {
        name: '',
        email: '',
        password: '',
        phone_number: '',
        country_id: '',
        city_id: '',
        address: '',
        gender: '',
    },
    errors: {},
    disableSubmit: false,
});

const countryRef = ref(null);
const cityRef = ref(null);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(customerId);
    } else {
        resetCustomerData();
    }
});

const fetchEditData = async (customer_id) => {
    try {
        const res = await axiosRequest.get("/admin/customers/" + customer_id, {});
        const customer = res.data.data;

        state.customer = {
            name: customer.name,
            email: customer.email,
            phone_number: customer.phone_number,
            country_id: customer.details?.country?.id,
            city_id: customer.details?.city?.id,
            address: customer.details?.address,
            gender: customer.details?.gender ? customer.details?.gender : '',
        };

        countryRef.value?.setValue(customer.details?.country?.name);
        cityRef.value?.setValue(customer.details?.city?.name);

    } catch (error) {
        console.log(error);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createCustomer();
    } else if (pageType == 'edit') {
        await editCustomer();
    }

    state.disableSubmit = false;
};

const createCustomer = async () => {
    let url = "/admin/customers/create";
    let data = state.customer;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/customers');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editCustomer = async () => {
    let url = "/admin/customers/" + customerId + "/update";
    let data = state.customer;

    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/customers');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetCustomerData = () => {
    state.customer = {
        name: '',
        email: '',
        password: '',
        phone_number: '',
        country_id: '',
        city_id: '',
        address: '',
        gender: '',
    };
    countryRef.value?.reset();
    cityRef.value?.reset();
    state.errors = {};
};

const handleFormSelectDeselected = (type) => {
    state.customer[type] = null;
};

const breadcrumbItems = [
    {label: translate('customers'), url: '/admin/customers'},
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
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-6 mb-3">
                    <label for="customer-name" class="block text-sm font-medium mb-1">{{ translate('name') }}</label>
                    <input type="text" :placeholder="translate('name')" id="customer-name"
                           v-model="state.customer.name"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring focus:border-blue-500">
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6 mb-3">
                    <label for="customer-email" class="block text-sm font-medium mb-1">{{ translate('email') }}</label>
                    <input type="email" :placeholder="translate('email')" id="customer-email"
                           v-model="state.customer.email"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring focus:border-blue-500">
                    <div class="text-red-500 text-sm">{{ state.errors.email }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6 mb-3">
                    <label for="customer-password" class="block text-sm font-medium mb-1">{{
                            translate('password')
                        }}</label>
                    <input type="password" :placeholder="translate('password')" id="customer-password"
                           v-model="state.customer.password"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring focus:border-blue-500">
                    <div class="text-red-500 text-sm">{{ state.errors.password }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6 mb-3">
                    <label for="customer-phone" class="block text-sm font-medium mb-1">{{
                            translate('phone_number')
                        }}</label>
                    <input type="tel" :placeholder="translate('phone_number')" id="customer-phone"
                           v-model="state.customer.phone_number"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring focus:border-blue-500">
                    <div class="text-red-500 text-sm">{{ state.errors.phone_number }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6 mb-3">
                    <label class="block text-sm font-medium mb-1">{{ translate('country') }}</label>
                    <vue-select model="name" ref="countryRef"
                                :url="`/admin/countries/search`"
                                :placeholder="translate('select_country')"
                                @option:selected="(data) => state.customer.country_id = data.id"
                                @option:deselected="handleFormSelectDeselected('country_id')"/>
                    <div class="text-red-500 text-sm">{{ state.errors.country_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6 mb-3">
                    <label class="block text-sm font-medium mb-1">{{ translate('city') }}</label>
                    <vue-select model="name" ref="cityRef"
                                :url="`/admin/cities/search?country_id=${state.customer.country_id}`"
                                :placeholder="translate('select_city')"
                                @option:selected="(data) => state.customer.city_id = data.id"
                                @option:deselected="handleFormSelectDeselected('city_id')"/>
                    <div class="text-red-500 text-sm">{{ state.errors.city_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6 mb-3">
                    <label for="customer-gender" class="block text-sm font-medium mb-1">{{ translate('gender') }}</label>
                    <select id="customer-gender" v-model="state.customer.gender"
                            class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring focus:border-blue-500">
                        <option value="" disabled>Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    <div class="text-red-500 text-sm">{{ state.errors.gender }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6 mb-3">
                    <label for="customer-address" class="block text-sm font-medium mb-1">{{ translate('address') }}</label>
                    <textarea :placeholder="translate('address')" id="customer-address"
                              v-model="state.customer.address"
                              class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring focus:border-blue-500"></textarea>
                    <div class="text-red-500 text-sm">{{ state.errors.address }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/customers')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
