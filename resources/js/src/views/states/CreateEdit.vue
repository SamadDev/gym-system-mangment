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
const stateId = route.params.id;
const pageType = route.name === 'edit_state' && stateId ? 'edit' : 'create';

const stateReactive = reactive({
    state: {
        name: '',
        country_id: '',
    },
    errors: {},
    disableSubmit: false,
});

const country_ref = ref(null);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(stateId);
    } else {
        resetStateData();
    }
});

const fetchEditData = async (state_id) => {
    try {
        const res = await axiosRequest.get("/admin/states/" + state_id, {});
        const state = res.data.data;
        stateReactive.state = {
            name: state.name,
            country_id: state.country_id,
        };

        country_ref.value.setValue(state?.country.name);
    } catch (error) {
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    stateReactive.disableSubmit = true;

    if (pageType == 'create') {
        await createState();
    } else if (pageType == 'edit') {
        await editState();
    }

    stateReactive.disableSubmit = false;
};

const createState = async () => {
    let url = "/admin/states/create";
    let data = stateReactive.state;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        router.push('/admin/states');
    } catch (error) {
        if (error.response.status == 422) {
            stateReactive.errors = error.response.data.errors;
        }
    }
};

const editState = async () => {
    let url = "/admin/states/" + stateId + "/update";
    let data = stateReactive.state;

    try {
        const res = await axiosRequest.put(url, data, {notify: true});
        router.push('/admin/states');
    } catch (error) {
        if (error.response.status == 422) {
            stateReactive.errors = error.response.data.errors;
        }
    }
};

const resetStateData = () => {
    stateReactive.state = {
        name: '',
        country_id: '',
    };
    country_ref.value?.reset();
    stateReactive.errors = {};
};

const handleCountryDeselected = () => {
    stateReactive.state['country_id'] = null;
};

const breadcrumbItems = [
    {label: translate('states'), url: '/admin/states'},
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
                    <label for="state-name">{{ translate('name') }}</label>
                    <input type="text" class="form-input" id="state-name" v-model="stateReactive.state.name">
                    <div class="text-red-500 text-sm">{{ stateReactive.errors.name }}</div>
                </div>

                <div class="col-span-12">
                    <label>{{ translate('country') }}</label>
                    <vue-select model="name" ref="country_ref"
                                url="/admin/countries/search"
                                :placeholder="translate('select_country')"
                                @option:selected="(data) => stateReactive.state.country_id = data.id"
                                @option:deselected="handleCountryDeselected" />
                    <div class="text-red-500 text-sm">{{ stateReactive.errors.country_id }}</div>
                </div>

                <!-- Action buttons -->
                <div class="col-span-12 flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/states')" />
                    <submit-button :disabled="stateReactive.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
