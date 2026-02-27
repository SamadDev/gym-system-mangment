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
        width: "3%",
        sortable: true,
        isKey: true,
    },
    {
        label: translate('name'),
        field: "name",
        width: "15%",
        sortable: true,
    },
    {
        label: translate('address'),
        field: "address",
        width: "20%",
        sortable: false,
        template: true,
    },
    {
        label: translate('contact'),
        field: "contact",
        width: "15%",
        sortable: false,
        template: true,
    },
    {
        label: translate('location'),
        field: "location",
        width: "15%",
        sortable: false,
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
    router.push(`/admin/warehouses/${data.id}/edit`);
}

const handleView = (data) => {
    router.push(`/admin/warehouses/${data.id}/view`);
}

const handleDelete = async (data) => {
    let warehouse_id = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this warehouse!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/warehouses/" + warehouse_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadWarehouseDatatable');
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
        url="/admin/warehouses/data"
        reloadTableEvent="reloadWarehouseDatatable"
    >
        <template #address="{ data }">
            <div class="flex flex-col gap-1 text-xs text-gray-700">
                <div class="font-medium">{{ data.value.address_line_1 }}</div>
                <div v-if="data.value.address_line_2" class="text-gray-500">{{ data.value.address_line_2 }}</div>
                <div v-if="data.value.street" class="text-gray-500">{{ data.value.street }}</div>
                <div v-if="data.value.postal_code" class="text-gray-500">{{ data.value.postal_code }}</div>
            </div>
        </template>

        <template #contact="{ data }">
            <div class="flex flex-col gap-1 text-xs text-gray-700">
                <!-- Phone -->
                <div v-if="data.value.phone_number" class="flex items-center gap-1 px-1 py-0.5 bg-blue-50 rounded-md">
                    <span class="inline-flex items-center justify-center w-3 h-3 bg-blue-100 text-blue-600 rounded-full">
                        <i class="fas fa-phone text-[10px]"></i>
                    </span>
                    <span class="font-medium truncate">{{ data.value.phone_number }}</span>
                </div>

                <!-- Email -->
                <div v-if="data.value.email" class="flex items-center gap-1 px-1 py-0.5 bg-green-50 rounded-md">
                    <span class="inline-flex items-center justify-center w-3 h-3 bg-green-100 text-green-600 rounded-full">
                        <i class="fas fa-envelope text-[10px]"></i>
                    </span>
                    <span class="font-medium truncate">{{ data.value.email }}</span>
                </div>
            </div>
        </template>

        <template #location="{ data }">
            <div class="flex flex-col gap-1 text-xs text-gray-700">
                <div class="flex items-center gap-1 px-1 py-0.5 bg-purple-50 rounded-md">
                    <span class="inline-flex items-center justify-center w-3 h-3 bg-purple-100 text-purple-600 rounded-full">
                        <i class="fas fa-map-marker-alt text-[10px]"></i>
                    </span>
                    <span class="font-medium">{{ data.value.country }}</span>
                </div>
                <div class="flex items-center gap-1 px-1 py-0.5 bg-orange-50 rounded-md">
                    <span class="inline-flex items-center justify-center w-3 h-3 bg-orange-100 text-orange-600 rounded-full">
                        <i class="fas fa-city text-[10px]"></i>
                    </span>
                    <span class="font-medium">{{ data.value.city }}</span>
                </div>
            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button @click="handleView(data.value)"/>
                <edit-button v-if="can('edit_warehouse')" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_warehouse')" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
