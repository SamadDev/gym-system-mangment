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
        label: translate('type'),
        field: "safe_transaction_type",
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
    router.push(`/admin/safe-transaction-categories/${data.id}/edit`);
}

const handleDelete = async (data) => {

    let safe_transaction_category_id = data.id;
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
                let url = "/admin/safe-transaction-categories/" + safe_transaction_category_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadSafeTransactionCategoryDatatable');
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
        url="/admin/safe-transaction-categories/data"
        reloadTableEvent="reloadSafeTransactionCategoryDatatable"
    >
        <template #safe_transaction_type="{ data }">
            {{ data.value.safe_transaction_type ? data.value.safe_transaction_type.name : null }}
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <edit-button v-if="can('edit_safe_transaction_category')" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_safe_transaction_category')" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
