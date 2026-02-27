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
        searchable: true,
        name: "id",
    },
    {
        label: translate('photo'),
        field: "photo",
        width: "8%",
        template: true,
        sortable: false,
        searchable: false,
        name: "photo",
    },
    {
        label: translate('code'),
        field: "member_code",
        width: "10%",
        sortable: true,
        searchable: true,
        name: "member_code",
    },
    {
        label: translate('name'),
        field: "full_name",
        width: "13%",
        sortable: false,
        searchable: false,
        name: "full_name",
    },
    {
        label: translate('email'),
        field: "email",
        width: "13%",
        sortable: true,
        searchable: true,
        name: "email",
    },
    {
        label: translate('phone'),
        field: "phone",
        width: "10%",
        sortable: true,
        searchable: true,
        name: "phone",
    },
    {
        label: translate('gender'),
        field: "gender",
        width: "7%",
        sortable: true,
        searchable: false,
        name: "gender",
        template: true,
    },
    {
        label: translate('membership'),
        field: "membership",
        width: "12%",
        sortable: false,
        searchable: false,
        name: "membership",
        template: true,
    },
    {
        label: 'Status',
        field: "status",
        width: "7%",
        sortable: true,
        searchable: false,
        name: "status",
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

const handleView = (data) => {
    router.push(`/admin/members/${data.id}`);
}

const handleEdit = (data) => {
    router.push(`/admin/members/${data.id}/edit`);
}

const handleDelete = async (data) => {
    let member_id = data.id;
    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this member!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/members/" + member_id;
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadMemberDatatable');
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
        url="/admin/members/data"
        reloadTableEvent="reloadMemberDatatable"
    >
        <template #filters="{ onFilterChange }">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Gender</label>
                    <select class="form-select" @change="(e) => onFilterChange('gender', e.target.value || null)">
                        <option value="">All</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Member Status</label>
                    <select class="form-select" @change="(e) => onFilterChange('status', e.target.value || null)">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Membership</label>
                    <select class="form-select" @change="(e) => onFilterChange('membership_status', e.target.value || null)">
                        <option value="">All</option>
                        <option value="active">Has Active Membership</option>
                        <option value="inactive">No Active Membership</option>
                    </select>
                </div>
            </div>
        </template>
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

        <template #gender="{ data }">
            <span class="badge" :class="data.value.gender === 'male' ? 'badge-outline-info' : 'badge-outline-pink'">
                {{ data.value.gender }}
            </span>
        </template>

        <template #membership="{ data }">
            <span class="badge" :class="data.value.membership && data.value.membership !== 'No Active Membership' ? 'badge-outline-success' : 'badge-outline-warning'">
                {{ data.value.membership || 'No Membership' }}
            </span>
        </template>

        <template #status="{ data }">
            <span class="badge" :class="{
                'badge-outline-success': data.value.status === 'active',
                'badge-outline-danger': data.value.status === 'inactive',
                'badge-outline-warning': data.value.status === 'suspended',
            }">
                {{ data.value.status }}
            </span>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_member')" @click="handleView(data.value)" class="mx-1"/>
                <edit-button v-if="can('edit_member')" @click="handleEdit(data.value)" class="mx-1"/>
                <delete-button v-if="can('delete_member')" @click="handleDelete(data.value)" class="mx-1"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>