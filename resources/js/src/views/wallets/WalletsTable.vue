<script setup>
import DataTable from "../components/DataTable/index.vue";
import DeleteButton from "../components/DeleteButton.vue";
import EditButton from "../components/EditButton.vue";
import eventBus from "../../eventBus.js";
import {can, translate} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../utils/apiRequest.js";
import {useRouter} from 'vue-router';

const props = defineProps({
    customerId: {
        type: String,
        default: null,
    },
});

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
        label: translate('type'),
        field: "transaction_type",
        width: "12%",
        sortable: true,
        template: true,
    },
    {
        label: translate('amount'),
        field: "amount",
        width: "10%",
        sortable: true,
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
        width: "15%",
        sortable: false,
        template: true,
    },
];

const handleEdit = (data) => {
    eventBus.emit('openWalletEditModal', data);
}

const handleDelete = async (data) => {

    let wallet_id = data.id;
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
                let url = "/admin/wallets/" + wallet_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadWalletDatatable');
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
        :url="`/admin/wallets/data${props.customerId ? '?filters[customer_id]=' + props.customerId : ''}`"
        reloadTableEvent="reloadWalletDatatable"
    >
        <template #transaction_type="{ data }">
            <div class="flex items-center">
                <span v-if="data.value.transaction_type"
                      class="badge badge-outline font-semibold px-3 py-1 rounded-full text-sm"
                      :class="data.value.transaction_type.bg_color">
                    {{ data.value.transaction_type.name }}
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

                <!-- User -->
                <div v-if="data.value.user" class="flex items-center text-xs text-gray-500">
                    <i class="fas fa-user mr-1"></i>
                    <span>{{ data.value.user }}</span>
                </div>

            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <edit-button v-if="can('edit_wallet')" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_wallet')" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
