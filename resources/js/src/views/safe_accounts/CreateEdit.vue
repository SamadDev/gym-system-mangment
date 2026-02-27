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
const safeAccountId = route.params.id;
const pageType = route.name === 'edit_safe_account' && safeAccountId ? 'edit' : 'create';

const state = reactive({
    safe_account: {
        name: '',
        account_number: '',
        safe_id: '',
    },
    errors: {},
    disableSubmit: false,
});

const safe_select = ref(null);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(safeAccountId);
    } else {
        resetSafeAccountData();
    }
});

const fetchEditData = async (safe_account_id) => {
    try {
        const res = await axiosRequest.get("/admin/safe-accounts/" + safe_account_id, {});
        const safe_account = res.data.data;

        state.safe_account = {
            name: safe_account.name,
            account_number: safe_account.account_number,
            safe_id: safe_account.safe_id,
        };
        safe_select.value = safe_account.safe.name;
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createSafeAccount();
    } else if (pageType == 'edit') {
        await editSafeAccount();
    }

    state.disableSubmit = false;
};

const createSafeAccount = async () => {
    let url = "/admin/safe-accounts/create";
    let data = state.safe_account;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/safe-accounts');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editSafeAccount = async () => {
    let url = "/admin/safe-accounts/" + safeAccountId + "/update";
    let data = state.safe_account;

    try {
        const res = await axiosRequest.put(url, data, {notify: true});
        router.push('/admin/safe-accounts');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetSafeAccountData = () => {
    state.safe_account = {
        name: '',
        account_number: '',
        safe_id: '',
    };
    state.errors = {};
    safe_select.value = null;
};

const breadcrumbItems = [
    {label: translate('safe_accounts'), url: '/admin/safe-accounts'},
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
                    <input type="text" class="form-input" id="safe-name" v-model="state.safe_account.name">
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <div class="col-span-12">
                    <label for="safe-account-number">{{ translate('account_number') }}</label>
                    <input type="text" class="form-input" id="safe-account-number" v-model="state.safe_account.account_number">
                    <div class="text-red-500 text-sm">{{ state.errors.account_number }}</div>
                </div>

                <div class="col-span-12">
                    <label>{{ translate('safe') }}</label>
                    <vue-select
                        ref="roleSelect"
                        model="name"
                        v-model="safe_select"
                        url="/admin/safes/search"
                        :placeholder="translate('select_safe')"
                        @option:selected="(data) => state.safe_account.safe_id = data.id"
                        @option:deselected="() => state.safe_account.safe_id = null"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.safe_id }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/safe-accounts')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
