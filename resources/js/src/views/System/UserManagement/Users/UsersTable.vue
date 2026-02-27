<script setup>
import DataTable from "../components/DataTable/index.vue";
import DeleteButton from "../components/DeleteButton.vue";
import EditButton from "../components/EditButton.vue";
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
        label: translate('contact'),
        field: "email",
        width: "15%",
        sortable: false,
        template: true,
    },
    {
        label: translate('role'),
        field: "role",
        width: "15%",
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

const handleEdit = (data) => {
    router.push(`/admin/users/${data.id}/edit`);
}

const handleDelete = async (data) => {

    let user_id = data.id;
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
                let url = "/admin/users/" + user_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadUserDatatable');
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
        url="/admin/users/data"
        reloadTableEvent="reloadUserDatatable"
    >
        <template #email="{ data }">
            <div class="flex flex-col gap-0.5 text-xs text-gray-700">
                <!-- Email -->
                <div v-if="data.value.email" class="flex items-center gap-1 px-1 py-0.5 bg-blue-50 rounded-md">
      <span class="inline-flex items-center justify-center w-3 h-3 bg-blue-100 text-blue-600 rounded-full">
        <i class="fas fa-envelope text-[10px]"></i>
      </span>
                    <span class="font-medium truncate">{{ data.value.email }}</span>
                </div>

                <!-- Phone -->
                <div v-if="data.value.phone_number" class="flex items-center gap-1 px-1 py-0.5 bg-green-50 rounded-md">
      <span class="inline-flex items-center justify-center w-3 h-3 bg-green-100 text-green-600 rounded-full">
        <i class="fas fa-phone text-[10px]"></i>
      </span>
                    <span class="font-medium truncate">{{ data.value.phone_number }}</span>
                </div>
            </div>
        </template>

        <template #role="{ data }">
  <span
      class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium
           bg-blue-50 text-blue-700 border border-blue-200 shadow-sm
           hover:bg-blue-100 transition"
  >
    {{ data.value.role }}
  </span>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <edit-button v-if="can('edit_user')" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_user')" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
