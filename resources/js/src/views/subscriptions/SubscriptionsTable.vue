<script setup>
import DataTable from "../components/DataTable/index.vue";
import DeleteButton from "../components/DeleteButton.vue";
import EditButton from "../components/EditButton.vue";
import ViewButton from "../components/ViewButton.vue";
import eventBus from "../../eventBus.js";
import {can, translate, formatPrice} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../utils/apiRequest.js";
import {useRouter} from 'vue-router';
import {useAppStore} from "../../stores";

const router = useRouter();
const store = useAppStore();

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
        label: translate('description'),
        field: "description",
        width: "20%",
        sortable: false,
        template: true,
    },
    {
        label: translate('monthly_price'),
        field: "monthly_price",
        width: "12%",
        sortable: true,
        template: true,
    },
    {
        label: translate('annual_price'),
        field: "annual_price",
        width: "12%",
        sortable: true,
        template: true,
    },
    {
        label: translate('status'),
        field: "is_active",
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

const handleView = (data) => {
    router.push({ name: 'view_subscription', params: { id: data.id } });
}

const handleEdit = (data) => {
    router.push(`/admin/subscriptions/${data.id}/edit`);
}

const handleDelete = async (data) => {
    let subscription_id = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this subscription!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/subscriptions/" + subscription_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadSubscriptionDatatable');
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
        url="/admin/subscriptions/data"
        reloadTableEvent="reloadSubscriptionDatatable"
    >
        <template #description="{ data }">
            <div v-if="data.value.description" class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                {{ data.value.description }}
            </div>
            <span v-else class="text-gray-400 text-xs">-</span>
        </template>

        <template #monthly_price="{ data }">
            <div class="flex items-center gap-1">
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ data.value.currency_type?.symbol || '$' }}{{ parseFloat(data.value.monthly_price || 0).toFixed(2) }}
                </span>
            </div>
        </template>

        <template #annual_price="{ data }">
            <div class="flex items-center gap-1">
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ data.value.currency_type?.symbol || '$' }}{{ parseFloat(data.value.annual_price || 0).toFixed(2) }}
                </span>
            </div>
        </template>

        <template #is_active="{ data }">
            <div v-if="data.value.is_active" class="flex items-center gap-1 px-2 py-1 bg-green-50 dark:bg-green-900/20 rounded-md text-xs w-fit">
                <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i>
                <span class="font-medium text-green-700 dark:text-green-300">{{ translate('active') }}</span>
            </div>
            <div v-else class="flex items-center gap-1 px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded-md text-xs w-fit">
                <i class="fas fa-times-circle text-gray-400"></i>
                <span class="text-gray-500 dark:text-gray-400">{{ translate('inactive') }}</span>
            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_subscription')"
                             @click="handleView(data.value)"
                />
                <edit-button v-if="can('edit_subscription')" class="mx-1" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_subscription')" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>

