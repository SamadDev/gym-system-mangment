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
        field: "name",
        width: "15%",
        sortable: true,
    },
    {
        label: translate('code'),
        field: "code",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('description'),
        field: "description",
        width: "20%",
        sortable: true,
    },
    {
        label: translate('status'),
        field: "is_active",
        width: "10%",
        template: true,
        sortable: true,
    },
    {
        label: translate('image'),
        field: "image",
        width: "10%",
        template: true,
        sortable: true,
    },
    {
        label: translate('sort_order'),
        field: "sort_order",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('action'),
        field: "action",
        width: "20%",
        sortable: false,
        template: true,
    },
];

const handleView = (data) => {
    router.push(`/admin/payment-methods/${data.id}`);
}

const handleEdit = (data) => {
    router.push(`/admin/payment-methods/${data.id}/edit`);
}

const handleDelete = async (data) => {
    let paymentMethodId = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this payment method!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/payment-methods/" + paymentMethodId + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadPaymentMethodDatatable');
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
        url="/admin/payment-methods/data"
        reloadTableEvent="reloadPaymentMethodDatatable"
    >
        <template #is_active="{ data }">
            <span v-if="data.value.is_active" class="badge bg-success">Active</span>
            <span v-else class="badge bg-danger">Inactive</span>
        </template>

        <template #image="{ data }">
            <div class="flex items-center">
                <img v-if="data.value.image" :src="data.value.image" alt="Image" class="w-12 h-12 object-cover rounded-md"/>
                <div v-else class="w-12 h-12 bg-gray-200 rounded-md flex items-center justify-center">
                    <i class="fas fa-image text-gray-400"></i>
                </div>
            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_payment_method')" @click="handleView(data.value)"/>
                <edit-button v-if="can('edit_payment_method')" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_payment_method')" class="mx-1" @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
