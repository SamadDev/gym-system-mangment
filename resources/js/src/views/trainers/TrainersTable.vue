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
        label: translate('photo'),
        field: "photo",
        width: "8%",
        template: true,
        sortable: false,
    },
    {
        label: translate('name'),
        field: "full_name",
        width: "18%",
        sortable: true,
    },
    {
        label: translate('email'),
        field: "email",
        width: "15%",
        sortable: true,
    },
    {
        label: translate('phone'),
        field: "phone",
        width: "12%",
        sortable: true,
    },
    {
        label: translate('specialization'),
        field: "specializations",
        width: "15%",
        template: true,
        sortable: false,
    },
    {
        label: translate('salary'),
        field: "salary",
        width: "10%",
        template: true,
        sortable: true,
    },
    {
        label: translate('status'),
        field: "is_active",
        width: "8%",
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
    router.push(`/admin/trainers/${data.id}`);
}

const handleEdit = (data) => {
    router.push(`/admin/trainers/${data.id}/edit`);
}

const handleDelete = async (data) => {
    let trainer_id = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this trainer!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/trainers/" + trainer_id;
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadTrainerDatatable');
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
        url="/admin/trainers/data"
        reloadTableEvent="reloadTrainerDatatable"
    >
        <template #photo="{ data }">
            <div class="flex items-center justify-center">
                <img 
                    v-if="data.value.photo" 
                    :src="data.value.photo" 
                    :alt="data.value.full_name" 
                    class="w-10 h-10 object-cover rounded-full"
                    @error="($event) => $event.target.style.display = 'none'"
                />
                <div 
                    v-else 
                    class="w-10 h-10 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center text-gray-500 dark:text-gray-400"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </template>

        <template #specializations="{ data }">
            <div class="flex flex-wrap gap-1">
                <span 
                    v-if="data.value.specializations && data.value.specializations.length" 
                    v-for="spec in data.value.specializations.slice(0, 2)" 
                    :key="spec"
                    class="badge badge-outline-primary text-xs"
                >
                    {{ spec }}
                </span>
                <span 
                    v-if="data.value.specializations && data.value.specializations.length > 2"
                    class="text-xs text-gray-500"
                >
                    +{{ data.value.specializations.length - 2 }} more
                </span>
                <span 
                    v-if="!data.value.specializations || !data.value.specializations.length"
                    class="text-gray-400 text-sm"
                >
                    No specialization
                </span>
            </div>
        </template>

        <template #salary="{ data }">
            <span class="font-medium">
                {{ data.value.salary ? '$' + Number(data.value.salary).toLocaleString() : 'N/A' }}
            </span>
        </template>

        <template #is_active="{ data }">
            <span class="badge" :class="data.value.is_active ? 'badge-outline-success' : 'badge-outline-danger'">
                {{ data.value.is_active ? 'Active' : 'Inactive' }}
            </span>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_trainer')" @click="handleView(data.value)" class="mx-1"/>
                <edit-button v-if="can('edit_trainer')" @click="handleEdit(data.value)" class="mx-1"/>
                <delete-button v-if="can('delete_trainer')" @click="handleDelete(data.value)" class="mx-1"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>