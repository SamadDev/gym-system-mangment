<script setup>
import {reactive, onMounted} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import {translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const paymentMethodId = route.params.id;

const state = reactive({
    paymentMethod: {
        name: '',
        code: '',
        description: '',
        is_active: true,
        image: '',
        sort_order: 0,
    },
    loading: true,
});

onMounted(async () => {
    await fetchPaymentMethodData(paymentMethodId);
});

const fetchPaymentMethodData = async (paymentMethod_id) => {
    try {
        const res = await axiosRequest.get("/admin/payment-methods/" + paymentMethod_id, {});
        const paymentMethod = res.data.data;

        state.paymentMethod = {
            name: paymentMethod.name,
            code: paymentMethod.code,
            description: paymentMethod.description,
            is_active: paymentMethod.is_active,
            image: paymentMethod.image,
            sort_order: paymentMethod.sort_order,
        };
        state.loading = false;
    } catch (error) {
        console.log(error.response);
        state.loading = false;
    }
};

const breadcrumbItems = [
    {label: translate('payment_methods'), url: '/admin/payment-methods'},
    {label: translate('view'), url: null}
];

</script>

<template>
    <!-- Main container -->
    <div>
        <!-- Top row: Breadcrumb -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>
        </div>

        <div v-if="state.loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-32 w-32 border-b-2 border-primary"></div>
        </div>

        <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <!-- Payment Method Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div v-if="state.paymentMethod.image" class="w-16 h-16 rounded-lg overflow-hidden">
                        <img :src="state.paymentMethod.image" alt="Payment Method" class="w-full h-full object-cover"/>
                    </div>
                    <div v-else class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                        <i class="fas fa-image text-2xl text-gray-400"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ state.paymentMethod.name }}</h1>
                        <p class="text-gray-600 dark:text-gray-400">{{ state.paymentMethod.code }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span v-if="state.paymentMethod.is_active" class="badge bg-success">Active</span>
                    <span v-else class="badge bg-danger">Inactive</span>
                </div>
            </div>

            <!-- Payment Method Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ translate('name') }}</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ state.paymentMethod.name }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ translate('code') }}</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ state.paymentMethod.code }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ translate('sort_order') }}</label>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ state.paymentMethod.sort_order }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ translate('description') }}</label>
                        <p class="text-gray-900 dark:text-white">{{ state.paymentMethod.description || '-' }}</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ translate('image') }}</label>
                        <div v-if="state.paymentMethod.image" class="mt-2">
                            <img :src="state.paymentMethod.image" alt="Payment Method Image" class="w-24 h-24 object-cover rounded-lg"/>
                        </div>
                        <p v-else class="text-gray-500 mt-2">No image uploaded</p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ translate('status') }}</label>
                        <div class="flex items-center gap-2">
                            <span v-if="state.paymentMethod.is_active" class="badge bg-success">Active</span>
                            <span v-else class="badge bg-danger">Inactive</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
