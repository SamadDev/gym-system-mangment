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
        label: translate('code'),
        field: "equipment_code",
        width: "12%",
        template: true,
        sortable: true,
    },
    {
        label: translate('name'),
        field: "name",
        width: "15%",
        sortable: true,
    },
    {
        label: translate('category'),
        field: "category",
        width: "12%",
        template: true,
        sortable: true,
    },
    {
        label: translate('brand'),
        field: "brand",
        width: "12%",
        sortable: true,
    },
    {
        label: translate('condition'),
        field: "condition",
        width: "10%",
        template: true,
        sortable: true,
    },
    {
        label: translate('status'),
        field: "status",
        width: "10%",
        template: true,
        sortable: true,
    },
    {
        label: translate('purchase_price'),
        field: "purchase_price",
        width: "10%",
        template: true,
        sortable: true,
    },
    {
        label: translate('action'),
        field: "action",
        width: "10%",
        sortable: false,
        template: true,
    },
];

const handleView = (data) => {
    router.push(`/admin/equipment/${data.id}`);
}

const handleEdit = (data) => {
    router.push(`/admin/equipment/${data.id}/edit`);
}

const handleDelete = async (data) => {
    let equipment_id = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this equipment!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/equipment/" + equipment_id;
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadEquipmentDatatable');
            } catch (error) {
                console.log(error);
            }
        }
    });
}

// Helper functions
const getConditionClass = (condition) => {
    switch(condition?.toLowerCase()) {
        case 'excellent': return 'bg-green-100 text-green-800';
        case 'good': return 'bg-blue-100 text-blue-800';
        case 'fair': return 'bg-yellow-100 text-yellow-800';
        case 'poor': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusClass = (status) => {
    switch(status?.toLowerCase()) {
        case 'active': 
        case 'available': return 'bg-green-100 text-green-800';
        case 'in_use': return 'bg-blue-100 text-blue-800';
        case 'maintenance': return 'bg-yellow-100 text-yellow-800';
        case 'out_of_order': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const formatStatus = (status) => {
    if (!status) return 'N/A';
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

</script>
<template>
    <data-table
        :columns="columns"
        url="/admin/equipment/data"
        reloadTableEvent="reloadEquipmentDatatable"
    >
        <template #equipment_code="{ data }">
            <span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">{{ data.value.equipment_code }}</span>
        </template>

        <template #category="{ data }">
            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                {{ data.value.category }}
            </span>
        </template>

        <template #condition="{ data }">
            <span 
                :class="getConditionClass(data.value.condition)"
                class="px-2 py-1 rounded-full text-xs font-medium"
            >
                {{ data.value.condition }}
            </span>
        </template>

        <template #status="{ data }">
            <span 
                :class="getStatusClass(data.value.status)"
                class="px-2 py-1 rounded-full text-xs font-medium"
            >
                {{ formatStatus(data.value.status) }}
            </span>
        </template>

        <template #purchase_price="{ data }">
            <span class="font-medium">
                {{ data.value.purchase_price ? '$' + Number(data.value.purchase_price).toLocaleString() : 'N/A' }}
            </span>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_equipment')" @click="handleView(data.value)" class="mx-1"/>
                <edit-button v-if="can('edit_equipment')" @click="handleEdit(data.value)" class="mx-1"/>
                <delete-button v-if="can('delete_equipment')" @click="handleDelete(data.value)" class="mx-1"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>