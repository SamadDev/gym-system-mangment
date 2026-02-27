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
const cityId = route.params.id;
const pageType = route.name === 'edit_city' && cityId ? 'edit' : 'create';

const state = reactive({
    city: {
        name: '',
        country_id: '',
        state_id: '',
    },
    errors: {},
    disableSubmit: false,
    rules: {
        name: 'required|string',
        country_id: 'required',
        state_id: 'required',
    },
});

const state_ref = ref(null);
const country_ref = ref(null);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(cityId);
    } else {
        resetCityData();
    }
});

const fetchEditData = async (city_id) => {
    try {
        const res = await axiosRequest.get("/admin/cities/" + city_id, {});
        const city = res.data.data;

        state.city = {
            name: city.name,
            country_id: city.country_id,
            state_id: city.state_id,
        };

        country_ref.value.setValue(city?.country.name);
        state_ref.value.setValue(city?.state.name);
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createCity();
    } else if (pageType == 'edit') {
        await editCity();
    }

    state.disableSubmit = false;
};

const createCity = async () => {
    let url = "/admin/cities/create";
    let data = state.city;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/cities');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editCity = async () => {
    let url = "/admin/cities/" + cityId + "/update";
    let data = state.city;

    try {
        const res = await axiosRequest.put(url, data, {notify: true});
        router.push('/admin/cities');
    } catch (error) {
        console.log(error.response);
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetCityData = () => {
    state.city = {
        name: '',
        country_id: '',
        state_id: '',
    };
    country_ref.value?.reset();
    state_ref.value?.reset();
    state.errors = {};
};

const handleCountryDeselected = () => {
    state.city['country_id'] = null;
};

const handleStateDeselected = () => {
    state.city['state_id'] = null;
};

const breadcrumbItems = [
    {label: translate('cities'), url: '/admin/cities'},
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
                    <label for="city-name">{{ translate('name') }}</label>
                    <input type="text" class="form-input" id="city-name" v-model="state.city.name">
                    <div class="text-red-500 text-sm">{{ state.errors.name }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <label for="user-role">{{ translate('country') }}</label>
                    <vue-select model="name" ref="country_ref"
                                url="/admin/countries/search"
                                :placeholder="translate('select_country')"
                                @option:selected="(data) => state.city.country_id = data.id"
                                @option:deselected="handleCountryDeselected"/>
                    <div class="text-red-500 text-sm">{{ state.errors.country_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                    <label>{{ translate('state') }}</label>
                    <vue-select model="name" ref="state_ref"
                                :url="`/admin/states/search?country_id=${state.city.country_id}`"
                                :placeholder="translate('select_state')"
                                @option:selected="(data) => state.city.state_id = data.id"
                                @option:deselected="handleStateDeselected"/>
                    <div class="text-red-500 text-sm">{{ state.errors.state_id }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/cities')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
