<script setup>
import {reactive, onMounted, ref} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import {createFormData, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import VueSelect from "../components/VueSelect.vue";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const subscriptionId = route.params.id;
const pageType = route.name === 'edit_subscription' && subscriptionId ? 'edit' : 'create';

const state = reactive({
    subscription: {
        name: '',
        description: '',
        monthly_price: 0,
        annual_price: 0,
        currency_type_id: '',
        is_active: true,
    },
    errors: {},
    disableSubmit: false,
});

const currencyTypeRef = ref(null);
const storeList = ref([]);

const fetchStores = async () => {
    try {
        const res = await axiosRequest.get("/admin/stores/search?limit=-1", {});
        storeList.value = res.data.data;
        console.log(storeList.value)
    } catch (error) {
        console.log(error.response);
    }
}

onMounted(async () => {

    if (pageType === 'edit') {
        await fetchEditData(subscriptionId);
    } else {
        resetSubscriptionData();
    }

    fetchStores();

});

const fetchEditData = async (subscription_id) => {
    try {
        const res = await axiosRequest.get("/admin/subscriptions/" + subscription_id, {});
        const subscription = res.data.data;

        state.subscription = {
            name: subscription.name,
            description: subscription.description || '',
            monthly_price: parseFloat(subscription.monthly_price) || 0,
            annual_price: parseFloat(subscription.annual_price) || 0,
            currency_type_id: subscription.currency_type?.id || '',
            is_active: Boolean(subscription.is_active),
        };

        // Set currency type value in VueSelect
        if (subscription.currency_type) {
            currencyTypeRef.value?.setValue(subscription.currency_type.name);
        }
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createSubscription();
    } else if (pageType == 'edit') {
        await editSubscription();
    }

    state.disableSubmit = false;
};

const createSubscription = async () => {
    let url = "/admin/subscriptions/create";
    const subscriptionData = {
        ...state.subscription,
        is_active: state.subscription.is_active ? 1 : 0,
    };
    let formData = createFormData(subscriptionData);
    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/subscriptions');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editSubscription = async () => {
    let url = "/admin/subscriptions/" + subscriptionId + "/update";
    const subscriptionData = {
        ...state.subscription,
        is_active: state.subscription.is_active ? 1 : 0,
    };
    let formData = createFormData(subscriptionData);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/subscriptions');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetSubscriptionData = () => {
    state.subscription = {
        name: '',
        description: '',
        monthly_price: 0,
        annual_price: 0,
        currency_type_id: '',
        is_active: true,
    };

    if (currencyTypeRef.value) {
        currencyTypeRef.value.reset();
    }

    state.errors = {};
};

const handleFormSelectDeselected = (type) => {
    state.subscription[type] = null;
};

const breadcrumbItems = [
    {label: translate('subscriptions'), url: '/admin/subscriptions'},
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
                <!-- Left Column: Main Form -->
                <div class="xl:col-span-2 space-y-6">
                    <!-- Basic Information Panel -->
                    <div class="panel">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 text-sm"></i>
                                </div>
                                <h5 class="font-semibold text-lg dark:text-white-light">
                                    {{ translate('basic_information') }}
                                </h5>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="subscription-name">{{ translate('name') }} <span
                                    class="text-red-500">*</span></label>
                                <input type="text" class="form-input" id="subscription-name"
                                       :placeholder="translate('name')" v-model="state.subscription.name">
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.name }}</div>
                            </div>

                            <div>
                                <label for="subscription-description">{{ translate('description') }}</label>
                                <textarea class="form-input" id="subscription-description"
                                          :placeholder="translate('description')"
                                          v-model="state.subscription.description" rows="4"></textarea>
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.description }}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">
                                        {{ translate('currency') }} <span class="text-red-500">*</span>
                                    </label>
                                    <vue-select
                                        model="name"
                                        ref="currencyTypeRef"
                                        url="/admin/currency-types/search"
                                        :placeholder="translate('select_currency')"
                                        @option:selected="(data) => state.subscription.currency_type_id = data.id"
                                        @option:deselected="handleFormSelectDeselected('currency_type_id')"
                                    />
                                    <div class="text-red-500 text-sm mt-1">{{ state.errors.currency_type_id }}</div>
                                </div>

                                <div>
                                    <label class="flex items-center cursor-pointer mt-6">
                                        <input
                                            type="checkbox"
                                            v-model="state.subscription.is_active"
                                            class="form-checkbox"
                                        />
                                        <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ translate('is_active') }}
                                        </span>
                                    </label>
                                    <div class="text-red-500 text-sm mt-1">{{ state.errors.is_active }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="subscription-monthly-price">{{ translate('monthly_price') }} <span
                                        class="text-red-500">*</span></label>
                                    <input type="number" class="form-input" id="subscription-monthly-price"
                                           :placeholder="translate('monthly_price')"
                                           v-model.number="state.subscription.monthly_price" step="0.01" min="0">
                                    <div class="text-red-500 text-sm mt-1">{{ state.errors.monthly_price }}</div>
                                </div>

                                <div>
                                    <label for="subscription-annual-price">{{ translate('annual_price') }} <span
                                        class="text-red-500">*</span></label>
                                    <input type="number" class="form-input" id="subscription-annual-price"
                                           :placeholder="translate('annual_price')"
                                           v-model.number="state.subscription.annual_price" step="0.01" min="0">
                                    <div class="text-red-500 text-sm mt-1">{{ state.errors.annual_price }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div
                class="flex justify-end gap-3 mt-6 bg-gray-100 dark:bg-gray-800 border-t border-gray-300 dark:border-gray-700 px-3 py-2 -mx-3 -mb-3">
                <cancel-button @click="router.push('/admin/subscriptions')"/>
                <submit-button :disabled="state.disableSubmit"/>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>

