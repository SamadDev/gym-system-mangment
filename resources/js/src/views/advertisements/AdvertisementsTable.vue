<script setup>
import DataTable from "../components/DataTable/index.vue";
import DeleteButton from "../components/DeleteButton.vue";
import EditButton from "../components/EditButton.vue";
import eventBus from "../../eventBus.js";
import {translate} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../utils/apiRequest.js";
import {useRouter} from 'vue-router';

const router = useRouter();

const columns = [
    {
        label: translate('id'),
        field: "id",
        width: "3%",
        sortable: true,
        isKey: true,
    },
    {
        label: translate('title'),
        field: "title",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('type'),
        field: "type",
        width: "8%",
        sortable: true,
        template: true,
    },
    {
        label: translate('image'),
        field: "image",
        width: "10%",
        template: true,
        sortable: true,
    },
    {
        label: translate('action'),
        field: "action",
        width: "15%",
        sortable: false,
        template: true,
    },
];

const handleEdit = (data) => {
    router.push(`/admin/advertisements/${data.id}/edit`);
}

const handleDelete = async (data) => {

    let advertisement_id = data.id;
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
                let url = "/admin/advertisements/" + advertisement_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadAdvertisementDatatable');
            } catch (error) {
                console.log(error);
            }
        }
    });
}

</script>
<template>
    <data-table
        :columns="columns"
        url="/admin/advertisements/data"
        reloadTableEvent="reloadAdvertisementDatatable"
    >
        <template #type="{ data }">
            <div class="flex items-center">
                <span v-if="data.value.type === 'link'" class="badge bg-primary">{{ translate('link') }}</span>
                <span v-else class="badge bg-success">{{ translate('content') }}</span>
            </div>
        </template>

        <template #image="{ data }">
            <div class="flex items-center">
                <img :src="data.value.image" alt="Image" class="w-12 h-12 object-cover rounded-md"/>
            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <edit-button @click="handleEdit(data.value)"/>
                <delete-button class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
