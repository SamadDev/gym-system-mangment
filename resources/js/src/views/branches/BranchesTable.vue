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
        label: translate('address'),
        field: "city",
        width: "15%",
        sortable: false,
        template: true,
    },
    {
        label: translate('logo'),
        field: "logo",
        width: "10%",
        sortable: true,
        template: true,
    },
    {
        label: translate('is_main'),
        field: "is_main",
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

const handleEdit = (data) => {
    router.push(`/admin/branches/${data.id}/edit`);
}

const handleDelete = async (data) => {

    let branch_id = data.id;
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
                let url = "/admin/branches/" + branch_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadBranchDatatable');
            } catch (error) {
                console.log(error);
            }
        }
    });
}

const handleIsMainChange = async (data) => {
    let branch_id = data.id;
    let checkbox = document.getElementById(branch_id + `-is-main`);
    let is_main = checkbox.checked;
    checkbox.checked = !checkbox.checked;

    Swal.fire({
        title: translate('are_you_sure'),
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/branches/" + branch_id + "/toggle-is-main";
                const res = await axiosRequest.put(url, {is_main: is_main}, {notify: true});
                eventBus.emit('reloadBranchDatatable');
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
        url="/admin/branches/data"
        reloadTableEvent="reloadBranchDatatable"
    >
        <template #email="{ data }">
            <div class="flex flex-col space-y-1">
                <div class="flex items-center">
                    <i class="fas fa-envelope text-gray-500 mr-2"></i>
                    <span>{{ data.value.email }}</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-phone text-gray-500 mr-2"></i>
                    <span>{{ data.value.phone_number ? data.value.phone_number : '---' }}</span>
                </div>
                <div v-if="data.value.website" class="flex items-center">
                    <i class="fas fa-globe text-gray-500 mr-2"></i>
                    <a :href="data.value.website" target="_blank" rel="noopener noreferrer"
                       class="text-blue-600 hover:text-blue-800">
                        {{ data.value.website }}
                    </a>
                </div>
            </div>
        </template>

        <template #city="{ data }">
            <div class="flex flex-col space-y-1">
                <div class="flex items-center">
                    <i class="fas fa-flag text-gray-500 mr-2"></i>
                    <span>{{ data.value.country }}</span>
                </div>
                <div class="flex items-center">
                    <i class="fas fa-city text-gray-500 mr-2"></i>
                    <span>{{ data.value.city ? data.value.city : '---' }}</span>
                </div>
                <div v-if="data.value.address" class="flex items-center">
                    <i class="fas fa-map-marker-alt text-gray-500 mr-2"></i>
                    <span>{{ data.value.address }}</span>
                </div>
            </div>
        </template>

        <template #logo="{ data }">
            <div class="flex items-center">
                <img :src="data.value.logo" alt="Logo" class="w-12 h-12 object-cover rounded-md"/>
            </div>
        </template>

        <template #is_main="{ data }">
            <label class="w-12 h-6 relative">
                <input
                    :checked="data.value.is_main == 1 ? true : false"
                    @change="handleIsMainChange(data.value)"
                    type="checkbox"
                    class="custom_switch absolute w-full h-full opacity-0 z-10 cursor-pointer peer"
                    :id="data.value.id + `-is-main`"
                />
                <span :for="data.value.id + `-is-main`"
                      class="bg-[#ebedf2] dark:bg-dark block h-full rounded-full before:absolute before:left-1 before:bg-white dark:before:bg-white-dark dark:peer-checked:before:bg-white before:bottom-1 before:w-4 before:h-4 before:rounded-full peer-checked:before:left-7 peer-checked:bg-primary before:transition-all before:duration-300"></span>
            </label>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <edit-button v-if="can('edit_branch')" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_branch')" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>
