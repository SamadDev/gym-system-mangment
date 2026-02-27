<script setup>
import {reactive, onMounted} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import {translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const safeId = route.params.id;
const pageType = route.name === 'edit_safe' && safeId ? 'edit' : 'create';

const state = reactive({
    safe: {
        name: '',
        address: '',
        description: '',
        type: '',
    },
    errors: {},
    disableSubmit: false,
});

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(safeId);
    } else {
        resetSafeData();
    }
});

const fetchEditData = async (safe_id) => {
    try {
        const res = await axiosRequest.get("/admin/safes/" + safe_id, {});
        const safe = res.data.data;

        state.safe = {
            name: safe.name,
            address: safe.address,
            description: safe.description,
            type: safe.type,
        };
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createSafe();
    } else if (pageType == 'edit') {
        await editSafe();
    }

    state.disableSubmit = false;
};

const createSafe = async () => {
    let url = "/admin/safes/create";
    let data = state.safe;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/safes');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editSafe = async () => {
    let url = "/admin/safes/" + safeId + "/update";
    let data = state.safe;

    try {
        const res = await axiosRequest.put(url, data, {notify: true});
        router.push('/admin/safes');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetSafeData = () => {
    state.safe = {
        name: '',
        address: '',
        description: '',
        type: '',
    };
    state.errors = {};
};

const breadcrumbItems = [
    {label: translate('safes'), url: '/admin/safes'},
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
                    <label for="safe-name">{{ translate('name') }}</label>
                    <input type="text" class="form-input" id="safe-name" v-model="state.safe.name">
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <div class="col-span-12">
                    <label for="safe-type">{{ translate('type') }}</label>
                    <select class="form-input" id="safe-type" v-model="state.safe.type">
                        <option value="" disabled>{{ translate('select_type') }}</option>
                        <option value="physical">{{ translate('physical') }}</option>
                        <option value="bank_account">{{ translate('bank_account') }}</option>
                    </select>
                    <div class="text-red-500 text-sm">{{ state.errors.type }}</div>
                </div>

                <div class="col-span-12">
                    <label for="safe-address">{{ translate('address') }}</label>
                    <input type="text" class="form-input" id="safe-address" v-model="state.safe.address">
                    <div class="text-red-500 text-sm">{{ state.errors.address }}</div>
                </div>

                <div class="col-span-12">
                    <label for="safe-description">{{ translate('description') }}</label>
                    <textarea class="form-input" id="safe-description" v-model="state.safe.description"></textarea>
                    <div class="text-red-500 text-sm">{{ state.errors.description }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/safes')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
