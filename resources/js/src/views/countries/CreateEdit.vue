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
const countryId = route.params.id;
const pageType = route.name === 'edit_country' && countryId ? 'edit' : 'create';

const state = reactive({
    country: {
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
        await fetchEditData(countryId);
    } else {
        resetCountryData();
    }
});

const fetchEditData = async (country_id) => {
    try {
        const res = await axiosRequest.get("/admin/countries/" + country_id, {});
        const country = res.data.data;

        state.country = {
            name: country.name,
        };
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createCountry();
    } else if (pageType == 'edit') {
        await editCountry();
    }

    state.disableSubmit = false;
};

const createCountry = async () => {
    let url = "/admin/countries/create";
    let data = state.country;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/countries');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editCountry = async () => {
    let url = "/admin/countries/" + countryId + "/update";
    let data = state.country;

    try {
        const res = await axiosRequest.put(url, data, {notify: true});
        router.push('/admin/countries');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetCountryData = () => {
    state.country = {
        name: '',
    };
    state.errors = {};
};

const breadcrumbItems = [
    {label: translate('countries'), url: '/admin/countries'},
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
                    <label for="country-name">{{ translate('name') }}</label>
                    <input type="text" class="form-input" id="country-name" v-model="state.country.name">
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/countries')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
