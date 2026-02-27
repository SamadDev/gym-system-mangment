<script setup>
import {reactive, onMounted, ref} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import VueSelect from "../components/VueSelect.vue";
import {createFormData, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const branchId = route.params.id;
const pageType = route.name === 'edit_branch' && branchId ? 'edit' : 'create';

const state = reactive({
    branch: {
        name: '',
        email: '',
        phone_number: '',
        website: '',
        country_id: '',
        city_id: '',
        address: '',
        description: '',
        logo: null,
    },
    errors: {},
    disableSubmit: false,
    rules: {
        name: 'required|string',
        email: 'required',
        phone_number: 'required',
        website: 'required',
        country_id: 'required',
        city_id: 'required',
        address: 'required',
        description: 'required',
    },
});

const city_ref = ref(null);
const country_ref = ref(null);
const logo_ref = ref(null);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(branchId);
    } else {
        resetBranchData();
    }
});

const fetchEditData = async (branch_id) => {
    try {
        const res = await axiosRequest.get("/admin/branches/" + branch_id, {});
        const branch = res.data.data;

        state.branch = {
            name: branch.name,
            email: branch.email,
            phone_number: branch.phone_number,
            website: branch.website,
            country_id: branch.country_id,
            city_id: branch.city_id,
            address: branch.address,
            description: branch.description,
        };

        country_ref.value.setValue(branch?.country.name);
        city_ref.value.setValue(branch?.city.name);

    } catch (error) {
        console.log(error);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createBranch();
    } else if (pageType == 'edit') {
        await editBranch();
    }

    state.disableSubmit = false;
};

const createBranch = async () => {
    let url = "/admin/branches/create";
    let formData = createFormData(state.branch);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/branches');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editBranch = async () => {
    let url = "/admin/branches/" + branchId + "/update";
    let formData = createFormData(state.branch);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/branches');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetBranchData = () => {
    state.branch = {
        name: '',
        email: '',
        phone_number: '',
        website: '',
        country_id: '',
        city_id: '',
        address: '',
        description: '',
        logo: null,
    };
    country_ref.value?.reset();
    city_ref.value?.reset();
    if (logo_ref.value) {
        logo_ref.value.value = null;
    }
    state.errors = {};
};

const handleCountryDeselected = () => {
    state.branch['country_id'] = null;
};

const handleCityDeselected = () => {
    state.branch['city_id'] = null;
};

const handleFileUpload = (event) => {
    state.branch.logo = event.target.files[0];
};

const breadcrumbItems = [
    {label: translate('branches'), url: '/admin/branches'},
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
                <div class="col-span-12 lg:col-span-6">
                    <label for="branch-name">{{ translate('name') }}</label>
                    <input type="text" class="form-input" id="branch-name" v-model="state.branch.name">
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <label for="branch-email">{{ translate('email') }}</label>
                    <input type="text" class="form-input" id="branch-email" v-model="state.branch.email">
                    <div class="text-red-500 text-sm">{{ state.errors.email }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <label for="branch-phone-number">{{ translate('phone_number') }}</label>
                    <input type="text" class="form-input" id="branch-phone-number" v-model="state.branch.phone_number">
                    <div class="text-red-500 text-sm">{{ state.errors.phone_number }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <label for="branch-website">{{ translate('website') }}</label>
                    <input type="text" class="form-input" id="branch-website" v-model="state.branch.website">
                    <div class="text-red-500 text-sm">{{ state.errors.website }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <label for="user-role">{{ translate('country') }}</label>
                    <vue-select model="name" ref="country_ref"
                                url="/admin/countries/search"
                                :placeholder="translate('select_country')"
                                @option:selected="(data) => state.branch.country_id = data.id"
                                @option:deselected="handleCountryDeselected"/>
                    <div class="text-red-500 text-sm">{{ state.errors.country_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <label>{{ translate('city') }}</label>
                    <vue-select model="name" ref="city_ref"
                                :url="`/admin/cities/search?country_id=${state.branch.country_id}`"
                                :placeholder="translate('select_city')"
                                @option:selected="(data) => state.branch.city_id = data.id"
                                @option:deselected="handleCityDeselected"/>
                    <div class="text-red-500 text-sm">{{ state.errors.city_id }}</div>
                </div>

                <div class="col-span-12">
                    <label for="branch-address">{{ translate('address') }}</label>
                    <textarea class="form-input" id="branch-address" v-model="state.branch.address"></textarea>
                    <div class="text-red-500 text-sm">{{ state.errors.address }}</div>
                </div>

                <div class="col-span-12">
                    <label for="branch-description">{{ translate('description') }}</label>
                    <textarea class="form-input" id="branch-description" v-model="state.branch.description"></textarea>
                    <div class="text-red-500 text-sm">{{ state.errors.description }}</div>
                </div>

                <div class="col-span-12">
                    <label for="logo">{{ translate('logo') }}</label>
                    <input ref="logo_ref" type="file" class="form-input file:py-2 file:px-4 file:border-0 file:font-semibold p-0 file:bg-gray-100 ltr:file:mr-5 rtl:file:ml-5 file:hover:bg-gray-200 cursor-pointer" id="logo" @change="handleFileUpload">
                    <div class="text-red-500 text-sm">{{ state.errors.logo }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/branches')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
