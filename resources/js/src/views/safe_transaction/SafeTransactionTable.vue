<script setup>

import DataTable from "../components/DataTable/index.vue";
import DeleteButton from "../components/DeleteButton.vue";
import EditButton from "../components/EditButton.vue";
import ViewButton from "../components/ViewButton.vue";
import eventBus from "../../eventBus.js";
import {can, translate} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../utils/apiRequest.js";
import VueSelect from "../components/VueSelect.vue";
import DatePicker from "../components/DatePicker.vue";
import {reactive} from "vue";
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
        label: translate('type'),
        field: "safe_transaction_type",
        width: "12%",
        sortable: true,
        template: true,
    },
    {
        label: translate('safe_and_account'),
        field: "safe",
        width: "15%",
        sortable: true,
        template: true,
    },
    {
        label: translate('amount'),
        field: "amount",
        width: "15%",
        sortable: true,
    },
    {
        label: translate('in_out'),
        field: "in_out",
        width: "8%",
        sortable: true,
        template: true,
    },
    {
        label: translate('note'),
        field: "note",
        width: "25%",
        sortable: false,
        template: true,
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
    router.push(`/admin/safe-transactions/${data.id}/edit`);
}

const handleDelete = async (data) => {

    let safe_transaction_id = data.id;
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
                let url = "/admin/safe-transactions/" + safe_transaction_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadSafeTransactionDatatable');
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
        url="/admin/safe-transactions/data"
        reloadTableEvent="reloadSafeTransactionDatatable"
    >
        <template #filters="{ onFilterChange }">
            <div class="grid gap-4">
                <div class="col-span-3">
                    <label>{{ translate('safe') }}</label>
                    <vue-select model="name" url="/admin/safes/search"
                                :placeholder="translate('search...')"
                                @option:selected="(data) => onFilterChange('safe_id', data.id)"
                                @option:deselected="() => onFilterChange('safe_id', null)"/>
                </div>
                <div class="col-span-3">
                    <label>{{ translate('safe_account') }}</label>
                    <vue-select model="name"
                                url="/admin/safe-accounts/search"
                                :placeholder="translate('search...')"
                                @option:selected="(data) => onFilterChange('safe_account_id', data.id)"
                                @option:deselected="() => onFilterChange('safe_account_id', null)"/>
                </div>
                <div class="col-span-3">
                    <label>{{ translate('date') }}</label>
                    <DatePicker
                        :isRange="true"
                        @update:modelValue="(data) => onFilterChange('date_range', data)"
                    />
                </div>
                <div class="col-span-3">
                    <label>{{ translate('type') }}</label>
                    <vue-select model="name"
                                url="/admin/safe-transaction-types/search"
                                :placeholder="translate('search...')"
                                @option:selected="(data) => onFilterChange('safe_transaction_type_id', data.id)"
                                @option:deselected="() => onFilterChange('safe_transaction_type_id', null)"/>
                </div>
            </div>
        </template>

        <template #safe_transaction_type="{ data }">
            <div class="flex items-center">
                <span v-if="data.value.safe_transaction_type"
                      class="badge badge-outline font-semibold px-3 py-1 rounded-full text-sm"
                      :class="data.value.safe_transaction_type.bg_color">
                    {{ data.value.safe_transaction_type.name }}
                </span>
            </div>
        </template>

        <template #note="{ data }">
            <div class="flex flex-col space-y-2">
                <!-- Note -->
                <div v-if="data.value.note" class="flex items-start">
                    <i class="fas fa-sticky-note text-gray-500 mr-2 mt-1 text-xs" title="Note"></i>
                    <span class="text-sm">{{ data.value.note }}</span>
                </div>

                <!-- Date -->
                <div v-if="data.value.transaction_date" class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-calendar mr-1"></i>
                    <span>{{ data.value.transaction_date }}</span>
                </div>

                <!-- User -->
                <div v-if="data.value.user" class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-user mr-1"></i>
                    <span>{{ data.value.user }}</span>
                </div>

                <!-- Attachment -->
                <div v-if="data.value.attachment" class="flex items-center">
                    <a :href="data.value.attachment" class="text-blue-600 hover:text-blue-800 flex items-center text-xs" target="_blank" title="View Attachment">
                        <i class="fas fa-paperclip mr-1"></i>
                        <span>Attachment</span>
                    </a>
                </div>
            </div>
        </template>
        <template #safe="{ data }">
            <div class="flex flex-col space-y-1">
                <div class="flex items-center">
                    <i class="fas fa-vault text-warning mr-2"></i>
                    <span class="font-medium">{{ data.value.safe }}</span>
                </div>
                <div v-if="data.value.safe_account" class="flex items-center">
                    <i class="fas fa-piggy-bank text-info mr-2"></i>
                    <span class="text-sm text-gray-600">{{ data.value.safe_account }}</span>
                </div>
            </div>
        </template>
        <template #in_out="{ data }">
            <div class="flex items-center">
                <span class="badge badge-sm" :class="data.value.in_out == 1 ? 'badge-outline-success' : 'badge-outline-danger'">
                    <i class="fas mr-1" :class="data.value.in_out == 1 ? 'fa-arrow-down' : 'fa-arrow-up'"></i>
                    {{ data.value.in_out == 1 ? 'In' : 'Out' }}
                </span>
            </div>
        </template>
        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_safe_transaction')"
                             @click="() => router.push({ name: 'view_safe_transaction', params: { id: data.value.id } })"
                />
                <edit-button v-if="can('edit_safe_transaction')" class="ml-1" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_safe_transaction')" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
