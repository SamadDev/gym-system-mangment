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
        width: "10%",
        sortable: true,
    },
    {
        label: translate('country'),
        field: "country",
        width: "10%",
        sortable: true,
        template: true,
    },
    {
        label: translate('website_url'),
        field: "website_url",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('automatic_pricing'),
        field: "auto_pricing",
        width: "12%",
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

const handleView = (data) => {
    router.push({ name: 'view_store', params: { id: data.id } });
}

const handleEdit = (data) => {
    router.push(`/admin/stores/${data.id}/edit`);
}

const handleDelete = async (data) => {

    let store_id = data.id;
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
                let url = "/admin/stores/" + store_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadStoreDatatable');
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
        url="/admin/stores/data"
        reloadTableEvent="reloadStoreDatatable"
    >
        <template #country="{ data }">
            <div v-if="data.value.country" class="flex items-center gap-1 px-2 py-1 bg-blue-50 rounded-md text-xs">
                <i class="fas fa-globe text-blue-500"></i>
                <span class="font-medium">{{ data.value.country }}</span>
            </div>
            <span v-else class="text-gray-400 text-xs">-</span>
        </template>

        <template #image="{ data }">
            <div class="flex items-center">
                <img :src="data.value.image" alt="Image" class="w-12 h-12 object-cover rounded-md"/>
            </div>
        </template>

        <template #auto_pricing="{ data }">
            <div v-if="data.value.auto_pricing_enabled" class="flex flex-col gap-1">
                <div class="flex items-center gap-1 px-2 py-1 bg-green-50 dark:bg-green-900/20 rounded-md text-xs w-fit">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                    <span class="font-medium text-green-700 dark:text-green-300">{{ translate('enabled') }}</span>
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    <span v-if="data.value.pricing_method === 'weight_based'">
                        {{ data.value.currency_type?.symbol || '$' }}{{ parseFloat(data.value.profit_per_weight_unit || 0).toFixed(2) }}/{{ data.value.weight_type || 'kg' }}
                    </span>
                    <span v-else-if="data.value.pricing_method === 'percentage'">
                        {{ parseFloat(data.value.profit_percentage || 0).toFixed(2) }}%
                    </span>
                    <span v-else-if="data.value.pricing_method === 'cbm'">
                        {{ data.value.currency_type?.symbol || '$' }}{{ parseFloat(data.value.profit_per_cbm || 0).toFixed(2) }}/m³
                    </span>
                    <span v-if="data.value.base_fee > 0" class="text-gray-500 dark:text-gray-400">
                        + {{ data.value.currency_type?.symbol || '$' }}{{ parseFloat(data.value.base_fee || 0).toFixed(2) }}
                    </span>
                </div>
            </div>
            <div v-else class="flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-md text-xs w-fit">
                <i class="fas fa-times-circle text-gray-400"></i>
                <span class="text-gray-500 dark:text-gray-400">{{ translate('disabled') }}</span>
            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_store')"
                             @click="handleView(data.value)"
                />
                <edit-button v-if="can('edit_store')" class="mx-1" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_store')" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
