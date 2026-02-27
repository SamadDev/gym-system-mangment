<script setup>

import DataTable from "../components/DataTable/index.vue";
import DeleteButton from "../components/DeleteButton.vue";
import EditButton from "../components/EditButton.vue";
import eventBus from "../../eventBus.js";
import {translate} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import VueSelect from "../components/VueSelect.vue";
import {useRouter} from "vue-router";
import {axiosRequest} from "../../utils/apiRequest.js";
import ViewButton from "../components/ViewButton.vue";

const router = useRouter();

const columns = [
    {
        label: translate('id'),
        field: "id",
        width: "3%",
        name: "id",
        searchable: true,
        sortable: true,
        isKey: true,
        template: true,
    },
    {
        label: translate('name'),
        field: "name",
        name: "name",
        searchable: true,
        width: "10%",
        sortable: true,
    },
    {
        label: translate('contact'),
        field: "email",
        name: "email",
        searchable: true,
        width: "15%",
        sortable: true,
        template: true,
    },
    {
        label: translate('address'),
        field: "address",
        name: "details.city.address",
        searchable: true,
        width: "20%",
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
    router.push(`/admin/customers/${data.id}/edit`);
}

const handleDelete = async (data) => {

    let customer_id = data.id;

    Swal.fire({
        title: translate('are_you_sure'),
        text: "Once deleted, you will not be able to recover this imaginary file!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: translate('yes_delete_it')
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let url = "/admin/customers/" + customer_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadCustomerDatatable');
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
        url="/admin/customers/data"
        reloadTableEvent="reloadCustomerDatatable"
    >
        <template #filters="{ onFilterChange }">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label>{{ translate('select_country') }}</label>
                    <vue-select model="name"
                                url="/admin/countries/search"
                                :placeholder="translate('search...')"
                                @option:selected="(data) => onFilterChange('country_id', data.id)"
                                @option:deselected="() => onFilterChange('country_id', null)"/>
                </div>
                <div>
                    <label>{{ translate('select_city') }}</label>
                    <vue-select model="name"
                                url="/admin/cities/search"
                                :placeholder="translate('search...')"
                                @option:selected="(data) => onFilterChange('city_id', data.id)"
                                @option:deselected="() => onFilterChange('city_id', null)"/>
                </div>
            </div>
        </template>

        <template #id="{ data }">
            <router-link :to="{ name: 'view_customer', params: { id: data.value.id } }">{{
                    data.value.id
                }}
            </router-link>
        </template>
        <template #email="{ data }">
            <div class="flex flex-col gap-0.5 text-xs text-gray-700">
                <!-- Email -->
                <div v-if="data.value.email" class="flex items-center gap-1 px-1 py-0.5 bg-blue-50 rounded-md">
      <span class="inline-flex items-center justify-center w-3 h-3 bg-blue-100 text-blue-600 rounded-full">
        <i class="fas fa-envelope text-[10px]"></i>
      </span>
                    <span class="font-medium truncate">{{ data.value.email }}</span>
                </div>

                <!-- Phone -->
                <div v-if="data.value.phone_number" class="flex items-center gap-1 px-1 py-0.5 bg-green-50 rounded-md">
      <span class="inline-flex items-center justify-center w-3 h-3 bg-green-100 text-green-600 rounded-full">
        <i class="fas fa-phone text-[10px]"></i>
      </span>
                    <span class="font-medium truncate">{{ data.value.phone_number }}</span>
                </div>
            </div>
        </template>


        <template #address="{ data }">
            <div class="flex flex-col gap-0.5 text-xs text-gray-700">
                <!-- Country -->
                <div v-if="data.value.country" class="flex items-center gap-1 px-1 py-0.5 bg-blue-50 rounded-md">
                    <i class="fas fa-globe text-blue-500 w-3 h-3"></i>
                    <span class="font-medium">Country:</span>
                    <span>{{ data.value.country }}</span>
                </div>

                <!-- City -->
                <div v-if="data.value.city" class="flex items-center gap-1 px-1 py-0.5 bg-green-50 rounded-md">
                    <i class="fas fa-city text-green-500 w-3 h-3"></i>
                    <span class="font-medium">City:</span>
                    <span>{{ data.value.city }}</span>
                </div>

                <!-- Address -->
                <div v-if="data.value.address" class="flex items-center gap-1 px-1 py-0.5 bg-yellow-50 rounded-md">
                    <i class="fas fa-map-marker-alt text-yellow-500 w-3 h-3"></i>
                    <span class="font-medium">Address:</span>
                    <span>{{ data.value.address }}</span>
                </div>
            </div>
        </template>


        <template #action="{ data }">
            <div class="flex items-center gap-1">
                <view-button @click="() => router.push({ name: 'view_customer', params: { id: data.value.id } })"/>
                <edit-button @click="handleEdit(data.value)"/>
                <delete-button @click="handleDelete(data.value)"/>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
