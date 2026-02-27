<script setup>
import DataTable from "../components/DataTable/index.vue";
import DeleteButton from "../components/DeleteButton.vue";
import TableButton from "../components/TableButton.vue";
import eventBus from "../../eventBus.js";
import {translate} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../utils/apiRequest.js";
import {reactive, ref} from "vue";

const translationValues = reactive(new Map());
const selected_language_id = ref(null);
const languages = ref([]);

const columns = [
    {
        label: translate('id'),
        field: "id",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('keyword'),
        field: "keyword",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('value'),
        field: "value",
        width: "10%",
        sortable: true,
        template: true,
    },
    {
        label: translate('action'),
        field: "action",
        width: "15%",
        sortable: false,
        template: true,
    },
];

const handleSave = async (data) => {

    let value = translationValues[data.id]

    let formData = {
        translation_id: data.translation_id,
        language_id: data.language_id,
        keyword_id: data.id,
        value: value,
    };

    if (!formData.language_id) {
        formData.language_id = selected_language_id.value;
    }

    try {
        let url = "/admin/translations/" + data.id + "/update";
        const res = await axiosRequest.post(url, formData, {notify: true});
    } catch (error) {
        console.log(error);
    }
}

const handleDelete = async (data) => {

    let keyword_id = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this imaginary file!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/translations/" + keyword_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadTranslationDatatable');
            } catch (error) {
                console.log(error);
            }
        }
    });
}

const datatableDraw = (data) => {
    languages.value = data.languages;
    const selectedLanguage = data.languages.find(lang => lang.is_selected);
    if (selectedLanguage) {
        selected_language_id.value = selectedLanguage.id;
    }
}

const handleLanguageChange = (selected_language) => {
    // console.log('handleLanguageChange');
    eventBus.emit('reloadTranslationDatatable', {selected_language});
}

</script>
<template>
    <data-table
        :columns="columns"
        url="/admin/translations/data"
        reloadTableEvent="reloadTranslationDatatable"
        :defaultOrder="false"
        @datatable:draw="datatableDraw"
    >
        <template #filters="{ onFilterChange }">
            <div class="grid gap-4">
                <div class="col-span-3">
                    <label>{{ translate('language') }}</label>
                    <select v-model="selected_language_id" @change="e => handleLanguageChange(e.target.value)"
                            class="form-select">
                        <option v-for="language in languages" :value="language.id">{{ language.name }}</option>
                    </select>
                </div>
            </div>
        </template>

        <template #value="{ data }">
            <div class="flex">
                <input
                    type="text"
                    class="form-input"
                    :placeholder="translate('value')"
                    :value="translationValues[data.value.id] = data.value.value"
                    v-model="translationValues[data.value.id]"
                >
            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <table-button :title="translate('save')" icon="fa fa-save" @click="handleSave(data.value)"/>
                <delete-button class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
