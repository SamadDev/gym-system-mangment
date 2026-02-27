<script setup>
import {reactive, onMounted, ref, nextTick} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import {createFormData, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import FileUploadWithPreview from 'file-upload-with-preview';
import 'file-upload-with-preview/dist/file-upload-with-preview.min.css';
import '../../assets/css/file-upload-preview.css';
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const paymentMethodId = route.params.id;
const pageType = route.name === 'edit_payment_method' && paymentMethodId ? 'edit' : 'create';

const state = reactive({
    paymentMethod: {
        name: '',
        code: '',
        description: '',
        is_active: true,
        image: '',
        sort_order: 0,
    },
    errors: {},
    disableSubmit: false,
});

let fileUpload = null;

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(paymentMethodId);
    } else {
        resetPaymentMethodData();
    }
    initializeFileUpload();
});

const initializeFileUpload = () => {
    if (!fileUpload) {
        nextTick(() => {
            fileUpload = new FileUploadWithPreview('paymentMethodImage', {
                images: {
                    baseImage: '/assets/images/file-preview.svg',
                    backgroundImage: '',
                },
            });
        });
    }
};

const fetchEditData = async (paymentMethod_id) => {
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
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    // Convert boolean to integer for backend
    const formData = {
        name: state.paymentMethod.name,
        code: state.paymentMethod.code,
        description: state.paymentMethod.description,
        is_active: state.paymentMethod.is_active ? 1 : 0,
        sort_order: state.paymentMethod.sort_order
    };

    // Only add image if a new file is selected
    const cachedFileArray = fileUpload.cachedFileArray;
    if (cachedFileArray && cachedFileArray.length > 0) {
        formData.image = cachedFileArray[0];
    }

    if (pageType == 'create') {
        await createPaymentMethod(formData);
    } else if (pageType == 'edit') {
        await editPaymentMethod(formData);
    }

    state.disableSubmit = false;
};

const createPaymentMethod = async (formData) => {
    let url = "/admin/payment-methods/create";
    let formDataToSend = createFormData(formData);
    try {
        const res = await axiosRequest.post(url, formDataToSend, {notify: true});
        router.push('/admin/payment-methods');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editPaymentMethod = async (formData) => {
    let url = "/admin/payment-methods/" + paymentMethodId + "/update";
    let formDataToSend = createFormData(formData);

    try {
        const res = await axiosRequest.post(url, formDataToSend, {notify: true});
        router.push('/admin/payment-methods');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetPaymentMethodData = () => {
    state.paymentMethod = {
        name: '',
        code: '',
        description: '',
        is_active: true,
        image: '',
        sort_order: 0,
    };

    // Clear the file upload preview
    if (fileUpload) {
        fileUpload.clearPreviewPanel();
    }

    state.errors = {};
};

const breadcrumbItems = [
    {label: translate('payment_methods'), url: '/admin/payment-methods'},
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
            <div class="grid grid-cols-12 gap-6">
                <!-- Basic Information Card -->
                <div class="col-span-12 lg:col-span-8">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ translate('basic_information') }}</h3>
                        
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 lg:col-span-6">
                                <label for="payment-method-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ translate('name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" class="form-input" id="payment-method-name" :placeholder="translate('name')" v-model="state.paymentMethod.name">
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.name }}</div>
                            </div>

                            <div class="col-span-12 lg:col-span-6">
                                <label for="payment-method-code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ translate('code') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" class="form-input" id="payment-method-code" :placeholder="translate('code')" v-model="state.paymentMethod.code">
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.code }}</div>
                            </div>

                            <div class="col-span-12">
                                <label for="payment-method-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ translate('description') }}
                                </label>
                                <textarea class="form-input" id="payment-method-description" :placeholder="translate('description')" v-model="state.paymentMethod.description" rows="4"></textarea>
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.description }}</div>
                            </div>

                            <div class="col-span-12 lg:col-span-6">
                                <label for="payment-method-sort-order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ translate('sort_order') }}
                                </label>
                                <input type="number" class="form-input" id="payment-method-sort-order" :placeholder="translate('sort_order')" v-model="state.paymentMethod.sort_order" min="0">
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.sort_order }}</div>
                            </div>

                            <div class="col-span-12 lg:col-span-6">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ translate('status') }}
                                </label>
                                <div class="flex items-center">
                                    <input type="checkbox" id="payment-method-active" v-model="state.paymentMethod.is_active" class="form-checkbox">
                                    <label for="payment-method-active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ translate('is_active') }}</label>
                                </div>
                                <div class="text-red-500 text-sm mt-1">{{ state.errors.is_active }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image Upload Card -->
                <div class="col-span-12 lg:col-span-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">{{ translate('payment_method_image') }}</h3>
                        
                        <div class="custom-file-container" data-upload-id="paymentMethodImage">
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
                        <div class="text-red-500 text-sm mt-1">{{ state.errors.image }}</div>
                        
                        <p class="text-xs text-gray-500 mt-2">{{ translate('recommended_size') }}: 200x200px</p>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/payment-methods')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
