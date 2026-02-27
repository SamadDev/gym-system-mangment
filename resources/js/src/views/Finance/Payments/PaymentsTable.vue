<script setup>
import DataTable from "../components/DataTable/index.vue";
import MoreActionButtonItem from "../components/MoreActionButtonItem.vue";
import DeleteButton from "../components/DeleteButton.vue";
import MoreActionButton from "../components/MoreActionButton.vue";
import EditButton from "../components/EditButton.vue";
import ViewButton from "../components/ViewButton.vue";
import eventBus from "../../eventBus.js";
import {can, translate} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../utils/apiRequest.js";
import VueSelect from "../components/VueSelect.vue";
import DatePicker from "../components/DatePicker.vue";
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
    // Only include customer column if props.customerId exists
    ...(!props.customerId ? [{
        label: translate('customer'),
        field: "customer_id",
        width: "10%",
        sortable: true,
        template: true,
    }] : []),

    {
        label: translate('amount'),
        field: "amount",
        width: "10%",
        sortable: true,
    },
    {
        label: translate('payment_method'),
        field: "payment_method",
        width: "10%",
        sortable: true,
        template: true,
    },
    {
        label: translate('date'),
        field: "payment_date",
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
        label: translate('note'),
        field: "note",
        width: "20%",
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
    router.push(`/admin/payments/${data.id}/edit`);
}

const handleChangeStatus = (data) => {
    eventBus.emit('openPaymentChangeStatusModal', data);
}

const handleDelete = async (data) => {

    let payment_id = data.id;
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
                let url = "/admin/payments/" + payment_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadPaymentDatatable');
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
        :url="`/admin/payments/data${props.customerId ? '?filters[customer_id]=' + props.customerId : ''}`"
        reloadTableEvent="reloadPaymentDatatable"
    >

        <template #filters="{ onFilterChange }">
            <div class="grid gap-4">
                <div class="col-span-3" v-if="!props.customerId">
                    <label>{{ translate('customer') }}</label>
                    <vue-select model="name"
                                url="/admin/customers/search"
                                :placeholder="translate('search...')"
                                @option:selected="(data) => onFilterChange('customer_id', data.id)"
                                @option:deselected="() => onFilterChange('customer_id', null)"/>
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

        <template #customer_id="{ data }">
            <div class="customer-info">
                <div class="customer-details">
                    <i class="fas fa-user text-blue-600 mr-1"></i> <span>{{ data.value.customer.name }}</span> <br/>
                    <i v-if="data.value.customer.email" class="fas fa-envelope text-gray-500 mr-1"></i>
                    <span>{{ data.value.customer.email }}</span> <br/>
                    <div v-if="data.value.customer.phone_number">
                        <i class="fas fa-phone-alt text-gray-700"></i> <span>{{ data.value.customer.phone_number }}</span>
                    </div>
                </div>
            </div>
        </template>

        <template #payment_method="{ data }">
            <div class="payment-method-info">
                <div class="flex items-center gap-2">
                    <i class="fas fa-credit-card text-purple-600"></i>
                    <span class="font-medium">{{ data.value.payment_method?.name || 'N/A' }}</span>
                </div>
            </div>
        </template>

        <template #status="{ data }">
          <span
              class="badge font-bold px-2 py-1 rounded text-white"
              :style="{ backgroundColor: data.value.status.color }"
          >
            {{ data.value.status.name }}
          </span>
        </template>


        <template #note="{ data }">
            <div class="flex flex-col">
                <span v-if="data.value.note" class="mr-2">
                  <i class="fas fa-sticky-note" title="Note"></i>
                  {{ data.value.note }}
                </span>
                <span v-if="data.value.attachment">
                  <a :href="data.value.attachment" class="text-blue-600" target="_blank" title="View Attachment">
                    <i class="fas fa-paperclip"></i>
                  </a>
                </span>
                <span v-if="data.value.user_name" class="mr-2">
                  <i class="fas fa-user" title="User"></i>
                  {{ data.value.user_name }}
                </span>
                <span v-if="data.value.created_at" class="text-gray-500 text-sm">
                  <i class="fas fa-clock" title="Created At"></i>
                  {{ data.value.created_at }}
                </span>

            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_payment')"
                             @click="() => router.push({ name: 'view_payment', params: { id: data.value.id } })"
                />
                <edit-button v-if="can('edit_payment')" class="ml-1" @click="handleEdit(data.value)"/>
                <delete-button v-if="can('delete_payment')" class="mx-1" @click="handleDelete(data.value)"/>
                <more-action-button v-if="can('change_status_payment')">
                    <more-action-button-item v-if="can('change_status_payment')" icon="fas fa-sync-alt" :text="translate('change_status')"
                                             @click="handleChangeStatus(data.value)"/>
                </more-action-button>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
