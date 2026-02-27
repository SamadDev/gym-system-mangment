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
const userId = route.params.id;
const pageType = route.name === 'edit_user' && userId ? 'edit' : 'create';

const state = reactive({
    user: {
        name: '',
        email: '',
        password: '',
        phone_number: '',
        gender: '',
        role_id: ''
    },
    errors: {},
    disableSubmit: false,
});

const role_ref = ref(null);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(userId);
    } else {
        resetUserData();
    }
});

const fetchEditData = async (user_id) => {
    try {
        const res = await axiosRequest.get("/admin/users/" + user_id, {});
        const user = res.data.data;
        state.user = {
            name: user.name,
            email: user.email,
            phone_number: user.phone_number,
            gender: user.details ? user.details.gender : '',
            role_id: user.roles.length ? user.roles[0].id : '',
        };

        role_ref.value.setValue(user.roles.length ? user.roles[0].name : null);
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createUser();
    } else if (pageType == 'edit') {
        await editUser();
    }

    state.disableSubmit = false;
};

const createUser = async () => {
    let url = "/admin/users/create";
    let data = state.user;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/users');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editUser = async () => {
    let url = "/admin/users/" + userId + "/update";
    let data = state.user;

    try {
        const res = await axiosRequest.put(url, data, {notify: true});
        router.push('/admin/users');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetUserData = () => {
    state.user = {
        name: '',
        email: '',
        password: '',
        phone_number: '',
        gender: '',
        role_id: ''
    };
    role_ref.value?.reset();
    state.errors = {};
};

const breadcrumbItems = [
    {label: translate('users'), url: '/admin/users'},
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
                    <label for="user-name">{{ translate('name') }}</label>
                    <input type="text" class="form-input"
                           id="user-name"
                           :placeholder="translate('full_name')"
                           v-model="state.user.name"
                    >
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <div class="col-span-12">
                    <label for="user-email">{{ translate('email') }}</label>
                    <input type="email" class="form-input"
                           id="user-email"
                           :placeholder="translate('email')"
                           v-model="state.user.email"
                    >
                    <div class="text-red-500 text-sm">{{ state.errors.email }}</div>
                </div>

                <div class="col-span-12">
                    <label for="user-password">{{ translate('password') }}</label>
                    <input type="password" class="form-input"
                           id="user-password"
                           :placeholder="translate('password')"
                           v-model="state.user.password"
                    >
                    <div class="text-red-500 text-sm">{{ state.errors.password }}</div>
                </div>

                <div class="col-span-12">
                    <label for="user-phone">{{ translate('phone_number') }}</label>
                    <input type="tel" class="form-input"
                           :placeholder="translate('phone_number')"
                           id="user-phone"
                           v-model="state.user.phone_number"
                    >
                    <div class="text-red-500 text-sm">{{ state.errors.phone_number }}</div>
                </div>

                <div class="col-span-12">
                    <label for="user-gender">{{ translate('gender') }}</label>
                    <select class="form-input" id="user-gender" v-model="state.user.gender">
                        <option value="" disabled>{{ translate('select_gender') }}</option>
                        <option value="male">{{ translate('male') }}</option>
                        <option value="female">{{ translate('female') }}</option>
                        <option value="other">{{ translate('other') }}</option>
                    </select>
                    <div class="text-red-500 text-sm">{{ state.errors.gender }}</div>
                </div>

                <div class="col-span-12">
                    <label for="user-role">{{ translate('role') }}</label>
                    <vue-select
                        ref="role_ref"
                        model="name"
                        rel="role_ref"
                        url="/admin/roles/search"
                        :placeholder="translate('select_role')"
                        @option:selected="(data) => state.user.role_id = data.id"
                        @option:deselected="() => state.user.role_id = null"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.role_id }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/users')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
