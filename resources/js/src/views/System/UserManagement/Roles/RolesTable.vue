<script setup>
import DataTable from "../components/DataTable/index.vue";
import DeleteButton from "../components/DeleteButton.vue";
import EditButton from "../components/EditButton.vue";
import eventBus from "../../eventBus.js";
import {can, translate} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import PermissionButton from "../components/PermissionButton.vue";
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
        label: translate('name'),
        field: "name",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('action'),
        field: "action",
        width: "10%",
        sortable: false,
        template: true,
    },
];

const handleEdit = (data) => {
    router.push(`/admin/roles/${data.id}/edit`);
}

const handleEditPermissions = async (data) => {
    eventBus.emit('openRolePermissionsModal', data)
}
const handleDelete = async (data) => {

    let role_id = data.id;
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
                let url = "/admin/roles/" + role_id + "/delete";
                const res = await axiosRequest.delete(url,{}, {notify: true});
                eventBus.emit('reloadRoleDatatable');
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
        url="/admin/roles/data"
        reloadTableEvent="reloadRoleDatatable"
    >
        <template #action="{ data }">
            <div class="flex items-center">
                <edit-button v-if="can('edit_role')" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_role')" class="mx-1" @click="handleDelete(data.value)"/>
                <permission-button v-if="can('edit_role')" class="mx-1" @click="handleEditPermissions(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
