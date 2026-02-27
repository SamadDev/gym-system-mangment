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
const roleId = route.params.id;
const pageType = route.name === 'edit_role' && roleId ? 'edit' : 'create';

const state = reactive({
    role: {
        name: '',
    },
    errors: {},
    disableSubmit: false,
    rules: {
        name: 'required|string',
    },
});

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(roleId);
    } else {
        resetRoleData();
    }
});

const fetchEditData = async (role_id) => {
    try {
        const res = await axiosRequest.get("/admin/roles/" + role_id + "/show", {});
        const role = res.data.data;

        state.role = {
            name: role.name,
        };
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createRole();
    } else if (pageType == 'edit') {
        await editRole();
    }

    state.disableSubmit = false;
};

const createRole = async () => {
    let url = "/admin/roles/create";
    let data = state.role;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/roles');
    } catch (error) {
        console.log(error.response);
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editRole = async () => {
    let url = "/admin/roles/" + roleId + "/update";
    let data = state.role;

    try {
        const res = await axiosRequest.put(url, data, {notify: true});
        router.push('/admin/roles');
    } catch (error) {
        console.log(error.response);
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetRoleData = () => {
    state.role = {
        name: '',
    };
    state.errors = {};
};

const breadcrumbItems = [
    {label: translate('roles'), url: '/admin/roles'},
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
                    <label for="role-name">{{ translate('name') }}</label>
                    <input type="text" class="form-input" id="role-name" v-model="state.role.name">
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/roles')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
