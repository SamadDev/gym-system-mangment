<script setup>
import {reactive, onMounted, ref, computed} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import {translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import {useRoute, useRouter} from 'vue-router';
import {useAppStore} from '../../stores';

const router = useRouter();
const route = useRoute();
const store = useAppStore();
const statusId = route.params.id;
const pageType = route.name === 'edit_status' && statusId ? 'edit' : 'create';

const state = reactive({
    status: {
        name: '',
        color: '#3B82F6',
        translations: {},
    },
    errors: {},
    disableSubmit: false,
});

// Get available languages (excluding English as it's default)
const availableLanguages = computed(() => {
    return store.languageList?.filter(lang => lang.code !== 'en') || [];
});

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(statusId);
    } else {
        resetStatusData();
    }
});

const fetchEditData = async (id) => {
    try {
        const res = await axiosRequest.get("/admin/statuses/" + id, {});
        const status = res.data.data;

        state.status = {
            name: status.name,
            color: status.color,
            translations: status.translations || {},
        };

    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createStatus();
    } else if (pageType == 'edit') {
        await editStatus();
    }

    state.disableSubmit = false;
};

const createStatus = async () => {
    let url = "/admin/statuses/create";
    let data = {
        ...state.status,
        type: 'order'
    };
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/statuses');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editStatus = async () => {
    let url = "/admin/statuses/" + statusId + "/update";
    let data = state.status;

    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/statuses');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetStatusData = () => {
    state.status = {
        name: '',
        color: '#3B82F6',
        translations: {},
    };
    state.errors = {};
};

const breadcrumbItems = [
    {label: translate('warehouses'), url: '/admin/warehouses'},
    {label: translate('statuses'), url: '/admin/statuses'},
    {label: pageType === 'create' ? translate('add') : translate('edit'), url: null}
];

// Predefined color options
const colorOptions = [
    { name: 'Blue', value: '#3B82F6' },
    { name: 'Green', value: '#10B981' },
    { name: 'Yellow', value: '#F59E0B' },
    { name: 'Red', value: '#EF4444' },
    { name: 'Purple', value: '#8B5CF6' },
    { name: 'Pink', value: '#EC4899' },
    { name: 'Indigo', value: '#6366F1' },
    { name: 'Gray', value: '#6B7280' },
    { name: 'Orange', value: '#F97316' },
    { name: 'Teal', value: '#14B8A6' },
];
</script>

<template>
    <div>
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>
        </div>

        <form @submit.prevent="handleSubmit">
            <div class="space-y-4">
                <!-- Status Information Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-flag text-blue-600 dark:text-blue-400 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('status_information') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="status-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('name') }}</label>
                            <input type="text" class="form-input" id="status-name" v-model="state.status.name" :placeholder="translate('status_name')">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.name }}</div>
                        </div>
                        <div>
                            <label for="status-color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('color') }}</label>
                            <div class="flex items-center gap-2">
                                <input type="color" class="w-12 h-10 rounded border border-gray-300 dark:border-gray-600" v-model="state.status.color">
                                <input type="text" class="form-input flex-1" v-model="state.status.color" placeholder="#3B82F6">
                            </div>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.color }}</div>
                        </div>
                    </div>
                </div>

                <!-- Color Preview Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-palette text-purple-600 dark:text-purple-400 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('preview') }}</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('status_preview') }}</label>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium"
                                      :style="`background-color: ${state.status.color}20; color: ${state.status.color};`">
                                    <span class="w-2 h-2 rounded-full mr-2" :style="`background-color: ${state.status.color};`"></span>
                                    {{ state.status.name || translate('status_name') }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ translate('color_options') }}</label>
                            <div class="grid grid-cols-5 gap-3">
                                <button
                                    v-for="color in colorOptions"
                                    :key="color.value"
                                    type="button"
                                    @click="state.status.color = color.value"
                                    class="w-10 h-10 rounded-lg border-2 transition-all duration-300 hover:scale-110 group"
                                    :class="state.status.color === color.value ? 'border-gray-900 dark:border-white shadow-lg' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400'"
                                    :style="`background-color: ${color.value};`"
                                    :title="color.name"
                                >
                                    <div v-if="state.status.color === color.value" class="w-full h-full flex items-center justify-center">
                                        <i class="fas fa-check text-white text-xs drop-shadow-lg"></i>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Translations Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                            <i class="fas fa-language text-indigo-600 dark:text-indigo-400 text-sm"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ translate('translations') }}</h3>
                    </div>
                    <div class="space-y-4">
                        <div v-for="lang in availableLanguages" :key="lang.id" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label :for="`translation-${lang.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center gap-2">
                                        <img :src="lang.image_url" :alt="lang.name" class="w-5 h-4 rounded-sm">
                                        {{ lang.name }} ({{ lang.code.toUpperCase() }})
                                    </div>
                                </label>
                                <input
                                    type="text"
                                    :id="`translation-${lang.id}`"
                                    class="form-input"
                                    :placeholder="`${translate('enter')} ${lang.name} ${translate('translation')}`"
                                    v-model="state.status.translations[lang.id]"
                                >
                                <div class="text-red-500 text-sm mt-1">{{ state.errors[`translations.${lang.id}`] }}</div>
                            </div>
                        </div>
                        <div v-if="availableLanguages.length === 0" class="text-center text-gray-500 py-4">
                            {{ translate('no_additional_languages_available') }}
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-end gap-3">
                        <cancel-button @click="router.push('/admin/statuses')" />
                        <submit-button :disabled="state.disableSubmit" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</template>
