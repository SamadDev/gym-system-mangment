<script setup>
import DataTable from "../../components/DataTable/index.vue";
import DeleteButton from "../../components/DeleteButton.vue";
import EditButton from "../../components/EditButton.vue";
import ViewButton from "../../components/ViewButton.vue";
import eventBus from "../../../eventBus.js";
import {can, translate} from "../../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../../utils/apiRequest.js";
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
        label: translate('member'),
        field: "member_name",
        width: "20%",
        sortable: true,
        template: true,
    },
    {
        label: translate('plan'),
        field: "plan_name",
        width: "15%",
        sortable: true,
    },
    {
        label: translate('start_date'),
        field: "start_date",
        width: "12%",
        sortable: true,
    },
    {
        label: translate('end_date'),
        field: "end_date",
        width: "12%",
        sortable: true,
    },
    {
        label: translate('amount'),
        field: "amount_paid",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('status'),
        field: "status",
        width: "10%",
        sortable: true,
        template: true,
    },
    {
        label: translate('action'),
        field: "action",
        width: "16%",
        sortable: false,
        template: true,
    },
];

const handleView = (data) => {
    router.push(`/admin/memberships/${data.id}`);
}

const handleEdit = (data) => {
    router.push(`/admin/memberships/${data.id}/edit`);
}

const handleDelete = async (data) => {
    let membership_id = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this membership!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/memberships/" + membership_id;
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadMembershipDatatable');
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
        url="/admin/memberships/data"
        reloadTableEvent="reloadMembershipDatatable"
    >
        <template #member_name="{ data }">
            <div class="flex items-center">
                <span class="font-medium">{{ data.value.member_name }}</span>
                <span class="text-xs text-gray-500 ml-2">({{ data.value.member_code }})</span>
            </div>
        </template>

        <template #status="{ data }">
            <span class="badge" :class="data.value.status === 'active' ? 'badge-outline-success' : 'badge-outline-warning'">
                {{ data.value.status }}
            </span>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_membership')" @click="handleView(data.value)" class="mx-1"/>
                <edit-button v-if="can('edit_membership')" @click="handleEdit(data.value)" class="mx-1"/>
                <delete-button v-if="can('delete_membership')" @click="handleDelete(data.value)" class="mx-1"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>