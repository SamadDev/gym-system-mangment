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
        label: translate('name'),
        field: "name",
        width: "20%",
        sortable: true,
    },
    {
        label: translate('duration'),
        field: "duration_days",
        width: "12%",
        sortable: true,
        template: true,
    },
    {
        label: translate('male_price'),
        field: "price_male",
        width: "12%",
        sortable: true,
        template: true,
    },
    {
        label: translate('female_price'),
        field: "price_female",
        width: "12%",
        sortable: true,
        template: true,
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
    router.push(`/admin/membership-plans/${data.id}`);
}

const handleEdit = (data) => {
    router.push(`/admin/membership-plans/${data.id}/edit`);
}

const handleDelete = async (data) => {
    let plan_id = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this membership plan!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/membership-plans/" + plan_id;
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadMembershipPlanDatatable');
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
        url="/admin/membership-plans/data"
        reloadTableEvent="reloadMembershipPlanDatatable"
    >
        <template #duration_days="{ data }">
            <span class="badge badge-outline-info">{{ data.value.duration_days }} days</span>
        </template>

        <template #price_male="{ data }">
            <span class="font-medium text-primary">${{ data.value.price_male }}</span>
        </template>

        <template #price_female="{ data }">
            <span class="font-medium text-pink-600">${{ data.value.price_female }}</span>
        </template>

        <template #status="{ data }">
            <span class="badge" :class="data.value.status === 'active' ? 'badge-outline-success' : 'badge-outline-warning'">
                {{ data.value.status }}
            </span>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_membership_plan')" @click="handleView(data.value)" class="mx-1"/>
                <edit-button v-if="can('edit_membership_plan')" @click="handleEdit(data.value)" class="mx-1"/>
                <delete-button v-if="can('delete_membership_plan')" @click="handleDelete(data.value)" class="mx-1"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>