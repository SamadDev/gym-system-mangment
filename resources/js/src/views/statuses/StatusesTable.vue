<script setup>
import DataTable from "../components/DataTable/index.vue";
import DeleteButton from "../components/DeleteButton.vue";
import EditButton from "../components/EditButton.vue";
import ViewButton from "../components/ViewButton.vue";
import eventBus from "../../eventBus.js";
import {can, translate} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../utils/apiRequest.js";
import {useRouter} from 'vue-router';

const router = useRouter();

const columns = [
    {
        label: translate('id'),
        field: "id",
        width: "5%",
        sortable: true,
        isKey: true,
    },
    {
        label: translate('name'),
        field: "color_badge",
        width: "35%",
        sortable: false,
        template: true,
    },
    {
        label: translate('created_by'),
        field: "user",
        width: "15%",
        sortable: false,
        template: true,
    },
    {
        label: translate('action'),
        field: "action",
        width: "20%",
        sortable: false,
        template: true,
    },
];

const handleEdit = (data) => {
    router.push(`/admin/statuses/${data.id}/edit`);
}

const handleView = (data) => {
    router.push(`/admin/statuses/${data.id}/view`);
}

const handleDelete = async (data) => {
    // Check if status is protected
    if (data.is_protected) {
        Swal.fire({
            title: translate('error'),
            text: translate('cannot_delete_protected_status'),
            icon: "error",
            confirmButtonText: translate('ok')
        });
        return;
    }

    let status_id = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this status!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/statuses/" + status_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadStatusDatatable');
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
        url="/admin/statuses/data"
        reloadTableEvent="reloadStatusDatatable"
    >
        <template #color_badge="{ data }">
            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium"
                  :style="`background-color: ${data.value.color}20; color: ${data.value.color};`">
                <span class="w-2.5 h-2.5 rounded-full mr-2" :style="`background-color: ${data.value.color};`"></span>
                {{ data.value.name }}
            </span>
        </template>


        <template #user="{ data }">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-gray-600 dark:text-gray-400 text-xs"></i>
                </div>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ data.value.user.name }}</span>
            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_status')" @click="handleView(data.value)"/>
                <edit-button v-if="can('edit_status')" class="mx-1" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_status') && !data.value.is_protected" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>
