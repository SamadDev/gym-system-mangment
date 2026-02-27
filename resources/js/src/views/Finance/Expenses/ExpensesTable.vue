<script setup>
import DataTable from "../components/DataTable/index.vue";
import MoreActionButtonItem from "../components/MoreActionButtonItem.vue";
import DeleteButton from "../components/DeleteButton.vue";
import MoreActionButton from "../components/MoreActionButton.vue";
import EditButton from "../components/EditButton.vue";
import ViewButton from "../components/ViewButton.vue";
import eventBus from "../../eventBus.js";
import {translate, can} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../utils/apiRequest.js";
import VueSelect from "../components/VueSelect.vue";
import DatePicker from "../components/DatePicker.vue";
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
        label: translate('amount'),
        field: "amount",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('expense_category'),
        field: "expense_category",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('date'),
        field: "expense_date",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('note'),
        field: "note",
        width: "10%",
        sortable: true,
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
    router.push(`/admin/expenses/${data.id}/edit`);
}

const handleDelete = async (data) => {

    let expense_id = data.id;

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
                let url = "/admin/expenses/" + expense_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadExpenseDatatable');
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
        url="/admin/expenses/data"
        reloadTableEvent="reloadExpenseDatatable"
    >

        <template #filters="{ onFilterChange }">
            <div class="grid gap-4">
                <div class="col-span-3">
                    <label>{{ translate('category') }}</label>
                    <vue-select model="name"
                                url="/admin/safe-transaction-categories/search?safe_transaction_type_id=3"
                                :placeholder="translate('search...')"
                                @option:selected="(data) => onFilterChange('expense_category_id', data.id)"
                                @option:deselected="() => onFilterChange('expense_category_id', null)"/>
                </div>
                <div class="col-span-3">
                    <label>{{ translate('date') }}</label>
                    <DatePicker
                        :isRange="true"
                        @update:modelValue="(data) => onFilterChange('date_range', data)"
                    />
                </div>
            </div>
        </template>

        <template #note="{ data }">
            <div class="flex flex-col">
                <span v-if="data.value.note" class="mr-2">
                  <i class="fas fa-sticky-note" title="Note"></i>
                  {{ data.value.note }}
                </span>

                <span v-if="data.value.attachment">
                  <a :href="data.value.attachment"
                     class="text-blue-600 text-sm"
                     target="_blank"
                     title="View Attachment">
                    <i class="fas fa-paperclip"></i>
                  </a>
                </span>

                <span v-if="data.value.created_at" class="text-gray-500 text-sm mt-1">
                  <i class="far fa-clock mr-1" title="Created At"></i>
                  {{ data.value.created_at }}
                </span>

                <span v-if="data.value.user" class="text-gray-500 text-sm mt-1">
                  <i class="fas fa-user mr-1" title="Created By"></i>
                  {{ data.value.user }}
                </span>
            </div>
        </template>


        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_expense')"
                             @click="() => router.push({ name: 'view_expense', params: { id: data.value.id } })"
                />
                <edit-button v-if="can('edit_expense')" class="ml-1" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_expense')" class="mx-1" @click="handleDelete(data.value)"/>
                <more-action-button v-if="can('view_expense')">
                    <more-action-button-item v-if="can('view_expense')" icon="fas fa-print" :text="translate('print')"
                                             :href="`/admin/expenses/${data.value.id}`" target="_blank"/>
                </more-action-button>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
