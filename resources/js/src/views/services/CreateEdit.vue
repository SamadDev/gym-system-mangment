<script setup>
import {reactive, onMounted, ref, computed} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import CKEditorComponent from '../../components/CKEditorComponent.vue';
import {createFormData, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const serviceId = route.params.id;
const pageType = route.name === 'edit_service' && serviceId ? 'edit' : 'create';

const state = reactive({
    service: {
        name: '',
        type: 'link',
        description: '',
        url: '',
        image: '',
    },
    errors: {},
    disableSubmit: false,
});

const image_ref = ref(null);

const serviceTypes = [
    { value: 'link', label: 'Link' },
    { value: 'content', label: 'Content' }
];

const isLinkType = computed(() => state.service.type === 'link');
const isContentType = computed(() => state.service.type === 'content');

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(serviceId);
    } else {
        resetServiceData();
    }
});

const fetchEditData = async (service_id) => {
    try {
        const res = await axiosRequest.get("/admin/services/" + service_id, {});
        const service = res.data.data;

        state.service = {
            name: service.name,
            type: service.type,
            description: service.description || '',
            url: service.url || '',
        };
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createService();
    } else if (pageType == 'edit') {
        await editService();
    }

    state.disableSubmit = false;
};

const createService = async () => {
    let url = "/admin/services/create";
    let formData = createFormData(state.service);
    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/services');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editService = async () => {
    let url = "/admin/services/" + serviceId + "/update";
    let formData = createFormData(state.service);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/services');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetServiceData = () => {
    state.service = {
        name: '',
        type: 'link',
        description: '',
        url: '',
        image: '',
    };

    if (image_ref.value) {
        image_ref.value.value = null;
    }

    state.errors = {};
};

const handleFileUpload = (event) => {
    state.service.image = event.target.files[0];
};

const breadcrumbItems = [
    {label: translate('services'), url: '/admin/services'},
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
                <div class="col-span-12">
                    <label for="service-name">{{ translate('name') }}</label>
                    <input type="text" class="form-input" id="service-name" :placeholder="translate('name')" v-model="state.service.name">
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <div class="col-span-12">
                    <label for="service-type">{{ translate('type') }}</label>
                    <select class="form-select" id="service-type" v-model="state.service.type">
                        <option v-for="type in serviceTypes" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                    <div class="text-red-500 text-sm">{{ state.errors.type }}</div>
                </div>

                <div class="col-span-12" v-if="isLinkType">
                    <label for="service-url">{{ translate('url') }}</label>
                    <input type="text" class="form-input" id="service-url" :placeholder="translate('url')" v-model="state.service.url">
                    <div class="text-red-500 text-sm">{{ state.errors.url }}</div>
                </div>

                <div class="col-span-12" v-if="isContentType">
                    <label for="service-description">{{ translate('description') }}</label>
                    <CKEditorComponent v-model="state.service.description" />
                    <div class="text-red-500 text-sm">{{ state.errors.description }}</div>
                </div>

                <div class="col-span-12">
                    <label for="logo">{{ translate('image') }}</label>
                    <input ref="image_ref" type="file" class="form-input file:py-2 file:px-4 file:border-0 file:font-semibold p-0 file:bg-gray-100 ltr:file:mr-5 rtl:file:ml-5 file:hover:bg-gray-200 cursor-pointer" id="image" @change="handleFileUpload">
                    <div class="text-red-500 text-sm">{{ state.errors.image }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/services')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
