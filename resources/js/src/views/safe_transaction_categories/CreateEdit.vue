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
const safeTransactionCategoryId = route.params.id;
const pageType = route.name === 'edit_safe_transaction_category' && safeTransactionCategoryId ? 'edit' : 'create';

const state = reactive({
    safe_transaction_category: {
        name: '',
        safe_transaction_type_id: '',
    },
    errors: {},
    disableSubmit: false,
});

const safe_transaction_type_select = ref(null);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(safeTransactionCategoryId);
    } else {
        resetSafeTransactionCategoryData();
    }
});

const fetchEditData = async (safe_transaction_category_id) => {
    try {
        const res = await axiosRequest.get("/admin/safe-transaction-categories/" + safe_transaction_category_id, {});
        const safe_transaction_category = res.data.data;

        state.safe_transaction_category = {
            name: safe_transaction_category.name,
            safe_transaction_type_id: safe_transaction_category.safe_transaction_type_id,
        };
        safe_transaction_type_select.value = safe_transaction_category.safe_transaction_type.name;
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createSafeTransactionCategory();
    } else if (pageType == 'edit') {
        await editSafeTransactionCategory();
    }

    state.disableSubmit = false;
};

const createSafeTransactionCategory = async () => {
    let url = "/admin/safe-transaction-categories/create";
    let data = state.safe_transaction_category;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/safe-transaction-categories');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editSafeTransactionCategory = async () => {
    let url = "/admin/safe-transaction-categories/" + safeTransactionCategoryId + "/update";
    let data = state.safe_transaction_category;

    try {
        const res = await axiosRequest.put(url, data, {notify: true});
        router.push('/admin/safe-transaction-categories');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetSafeTransactionCategoryData = () => {
    state.safe_transaction_category = {
        name: '',
        safe_transaction_type_id: '',
    };
    state.errors = {};
    safe_transaction_type_select.value = null;
};

const breadcrumbItems = [
    {label: translate('safe_transaction_categories'), url: '/admin/safe-transaction-categories'},
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
                    <input type="text" class="form-input" id="safe-name" v-model="state.safe_transaction_category.name">
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <div class="col-span-12">
                    <label>{{ translate('type') }}</label>
                    <vue-select
                        model="name"
                        v-model="safe_transaction_type_select"
                        url="/admin/safe-transaction-types/search"
                        :placeholder="translate('select_safe_transaction_type')"
                        @option:selected="(data) => state.safe_transaction_category.safe_transaction_type_id = data.id"
                        @option:deselected="() => state.safe_transaction_category.safe_transaction_type_id = null"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.safe_transaction_type_id }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/safe-transaction-categories')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
