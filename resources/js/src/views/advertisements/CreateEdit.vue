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
const advertisementId = route.params.id;
const pageType = route.name === 'edit_advertisement' && advertisementId ? 'edit' : 'create';

const state = reactive({
    advertisement: {
        title: '',
        type: 'link',
        description: '',
        url: '',
        image: '',
    },
    errors: {},
    disableSubmit: false,
});

const image_ref = ref(null);

const advertisementTypes = [
    { value: 'link', label: 'Link' },
    { value: 'content', label: 'Content' }
];

const isLinkType = computed(() => state.advertisement.type === 'link');
const isContentType = computed(() => state.advertisement.type === 'content');

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(advertisementId);
    } else {
        resetAdvertisementData();
    }
});

const fetchEditData = async (advertisement_id) => {
    try {
        const res = await axiosRequest.get("/admin/advertisements/" + advertisement_id, {});
        const advertisement = res.data.data;

        state.advertisement = {
            title: advertisement.title,
            type: advertisement.type,
            description: advertisement.description || '',
            url: advertisement.url || '',
        };
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createAdvertisement();
    } else if (pageType == 'edit') {
        await editAdvertisement();
    }

    state.disableSubmit = false;
};

const createAdvertisement = async () => {
    let url = "/admin/advertisements/create";
    let formData = createFormData(state.advertisement);
    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/advertisements');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editAdvertisement = async () => {
    let url = "/admin/advertisements/" + advertisementId + "/update";
    let formData = createFormData(state.advertisement);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/advertisements');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetAdvertisementData = () => {
    state.advertisement = {
        title: '',
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
    state.advertisement.image = event.target.files[0];
};

const breadcrumbItems = [
    {label: translate('advertisements'), url: '/admin/advertisements'},
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
                    <label for="advertisement-title">{{ translate('title') }}</label>
                    <input type="text" class="form-input" id="advertisement-title" :placeholder="translate('title')" v-model="state.advertisement.title">
                    <div class="text-red-500 text-sm">{{ state.errors.title }}</div>
                </div>

                <div class="col-span-12">
                    <label for="advertisement-type">{{ translate('type') }}</label>
                    <select class="form-select" id="advertisement-type" v-model="state.advertisement.type">
                        <option v-for="type in advertisementTypes" :key="type.value" :value="type.value">
                            {{ type.label }}
                        </option>
                    </select>
                    <div class="text-red-500 text-sm">{{ state.errors.type }}</div>
                </div>

                <div class="col-span-12" v-if="isLinkType">
                    <label for="advertisement-url">{{ translate('url') }}</label>
                    <input type="text" class="form-input" id="advertisement-url" :placeholder="translate('url')" v-model="state.advertisement.url">
                    <div class="text-red-500 text-sm">{{ state.errors.url }}</div>
                </div>

                <div class="col-span-12" v-if="isContentType">
                    <label for="advertisement-description">{{ translate('description') }}</label>
                    <CKEditorComponent v-model="state.advertisement.description" />
                    <div class="text-red-500 text-sm">{{ state.errors.description }}</div>
                </div>

                <div class="col-span-12">
                    <label for="logo">{{ translate('image') }}</label>
                    <input ref="image_ref" type="file" class="form-input file:py-2 file:px-4 file:border-0 file:font-semibold p-0 file:bg-gray-100 ltr:file:mr-5 rtl:file:ml-5 file:hover:bg-gray-200 cursor-pointer" id="image" @change="handleFileUpload">
                    <div class="text-red-500 text-sm">{{ state.errors.image }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/advertisements')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
