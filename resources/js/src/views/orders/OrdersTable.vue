<script setup>
import DataTable from "../components/DataTable/index.vue";
import eventBus from "../../eventBus.js";
import {can, formatPrice, translate} from "../../utils/functions.js";
import Swal from 'sweetalert2';
import {axiosRequest} from "../../utils/apiRequest.js";
import MoreActionButton from "../components/MoreActionButton.vue";
import MoreActionButtonItem from "../components/MoreActionButtonItem.vue";
import VueSelect from "../components/VueSelect.vue";
import {ref} from "vue";
import DatePicker from "../components/DatePicker.vue";
import ViewButton from "../components/ViewButton.vue";
import {useRouter} from "vue-router";
import EditButton from "@/views/components/EditButton.vue";

const router = useRouter();

const props = defineProps({
    customerId: {
        type: String,
        default: null,
    },
});

const tableRef = ref(null);
const searchInputRef = ref(null);

const columns = [
    {
        label: translate('id'),
        field: "id",
        name: "id",
        searchable: true,
        width: "3%",
        sortable: true,
        isKey: true,
    },
    {
        label: translate("details"),
        field: "order_number",
        name: "order_number",
        searchable: true,
        width: "25%",
        sortable: false,
        template: true,
    },
    // Only include customer column if props.customerId exists
    ...(!props.customerId ? [{
        label: translate("customer"),
        field: "customer_id",
        name: "customer.name",
        searchable: true,
        width: "20%",
        sortable: false,
        template: true,
    }] : []),
    {
        label: translate("store"),
        field: "store_id",
        name: "store.name",
        searchable: true,
        width: "15%",
        sortable: false,
        template: true,
    },
    {
        label: translate("product"),
        field: "product_name",
        name: "items.product_name",
        searchable: true,
        width: "15%",
        sortable: false,
        template: true,
    },
    {
        label: translate("shipping"),
        field: "shipping",
        width: "10%",
        sortable: false,
        template: true,
    },

    {
        label: translate("note"),
        field: "order_status_id",
        name: "note",
        searchable: true,
        width: "20%",
        sortable: false,
        template: true,
    },
    {
        label: translate('image'),
        field: "image",
        width: "7%",
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

const handleChangeStatus = (data) => {
    eventBus.emit('openOrderChangeStatusModal', data);
}

const handleDelete = async (data) => {

    let order_id = data.id;
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
                let url = "/admin/orders/" + order_id + "/delete";
                const res = await axiosRequest.delete(url, {}, {notify: true});
                eventBus.emit('reloadOrderDatatable');
            } catch (error) {
                console.log(error);
            }
        }
    });
}

const handleExternalFilterChange = (column, value, onFilterChange) => {
    onFilterChange(column, value);
    eventBus.emit('reloadOrderDatatable');
}

const handleAddInvoiceButton = () => {

    const selected = tableRef.value?.selectedRows || [];
    // Extract IDs
    const ids = selected.map(row => row.id);
    // Extract Customer IDs
    const customerIds = [...new Set(selected.map(row => row.customer_id))];

    // Check if multiple customers are selected
    if (customerIds.length > 1) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Selection',
            text: 'Selected orders belong to different customers. Please select orders from the same customer to create an invoice.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
        });
        return; // stop further processing
    }

    const customerId = customerIds[0]; // only one customer since validated

    // Navigate to add_invoice page with params
    router.push({
        name: 'add_invoice',
        query: {
            order_ids: ids.join(','),
            customer_id: customerId
        }
    });
}

// Helper functions for shipping type display
const getShippingTypeBadgeClass = (shippingType) => {
    const classes = {
        'air': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'sea': 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'land': 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'
    };
    return classes[shippingType] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
};

const getShippingTypeIcon = (shippingType) => {
    const icons = {
        'air': 'fas fa-plane',
        'sea': 'fas fa-ship',
        'land': 'fas fa-truck'
    };
    return icons[shippingType] || 'fas fa-circle';
};

</script>
<template>
    <data-table
        :columns="columns"
        :url="`/admin/orders/data${props.customerId ? '?filters[customer_id]=' + props.customerId : ''}`"
        reloadTableEvent="reloadOrderDatatable"
        ref="tableRef"
        :searchInput="searchInputRef"
        :hasCheckbox="true"
    >
        <template #bulk-actions>
            <li v-if="can('add_invoice')">
                <a @click="handleAddInvoiceButton"
                   class="dropdown-item d-flex align-items-center">{{ translate('add_invoice') }}</a>
            </li>
        </template>

        <template #external-filters="{ onFilterChange }">
            <div class="grid grid-cols-3 gap-2">
                <div v-if="!props.customerId">
                    <vue-select model="name"
                                class="form-input-sm-vue-select"
                                url="/admin/customers/search"
                                :placeholder="translate('select_customer')"
                                @option:selected="(data) => handleExternalFilterChange('customer_id', data.id, onFilterChange)"
                                @option:deselected="() => handleExternalFilterChange('customer_id',null, onFilterChange)"
                    />
                </div>
                <div>
                    <vue-select model="name"
                                class="form-input-sm-vue-select"
                                url="/admin/stores/search"
                                :placeholder="translate('select_store')"
                                @option:selected="(data) => handleExternalFilterChange('store_id', data.id, onFilterChange)"
                                @option:deselected="() => handleExternalFilterChange('store_id',null, onFilterChange)"
                    />
                </div>
                <div>
                    <vue-select model="name"
                                class="form-input-sm-vue-select"
                                url="/admin/statuses/search?status_type=order"
                                :placeholder="translate('select_status')"
                                @option:selected="(data) => handleExternalFilterChange('status_id', data.id, onFilterChange)"
                                @option:deselected="() => handleExternalFilterChange('status_id',null, onFilterChange)"
                    />
                </div>
            </div>
        </template>

        <template #filters="{ onFilterChange }">
            <div class="grid gap-4">
                <div class="col-span-3">
                    <label>{{ translate('date') }}</label>
                    <DatePicker
                        :isRange="true"
                        @update:modelValue="(data) => onFilterChange('date_range', data)"
                    />
                </div>
            </div>
        </template>

        <template #order_number="{ data }">
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center">
                        <i class="fas fa-hashtag text-primary text-xs"></i>
                    </div>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ data.value.order_number }}</span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                    <div class="flex items-center space-x-1">
                        <i class="fas fa-calendar-alt text-xs"></i>
                        <span>{{ data.value.created_at }}</span>
                    </div>
                </div>
            </div>
        </template>

        <template #customer_id="{ data }">
            <div class="space-y-2">
                <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600 dark:text-blue-400 text-xs"></i>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">{{ data.value.customer.name }}</span>
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                    <div class="flex items-center space-x-1">
                        <i class="fas fa-envelope text-xs"></i>
                        <span class="truncate">{{ data.value.customer.email }}</span>
                    </div>
                    <div v-if="data.value.customer.phone_number" class="flex items-center space-x-1">
                        <i class="fas fa-phone-alt text-xs"></i>
                        <span>{{ data.value.customer.phone_number }}</span>
                    </div>
                </div>
            </div>
        </template>

        <template #store_id="{ data }">
            <div v-if="data.value.store" class="flex items-center space-x-2">
                <div v-if="data.value.store.image" class="flex-shrink-0">
                    <img :src="data.value.store.image" :alt="data.value.store.name"
                         class="w-8 h-8 rounded-lg object-cover border border-gray-200 dark:border-gray-700"/>
                </div>
                <div v-else class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-store text-purple-600 dark:text-purple-400 text-xs"></i>
                </div>
                <span class="font-medium text-gray-900 dark:text-white truncate">{{ data.value.store.name }}</span>
            </div>
            <div v-else class="text-xs text-gray-400 dark:text-gray-500">
                {{ translate('no_store') }}
            </div>
        </template>

        <template #product_name="{ data }">
            <div class="space-y-1">
                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                    <i class="fas fa-box text-blue-600 mr-2"></i>{{ data.value.items[0].product_name }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    <div class="flex items-center mt-1">
                        <i class="fas fa-tag text-gray-500 mr-1"></i>
                        <span>{{ data.value.items[0].color + (data.value.items[0].color ? ' | ' : '')  }} {{
                                data.value.items[0].size + (data.value.items[0].size ? ' | ' : '')
                            }} {{ formatPrice(data.value.items[0].product_price, data.value.items[0].currency_type) }}</span>
                    </div>
                </div>
                <div v-if="data.value.items.length > 1" class="text-xs text-blue-600 dark:text-blue-400 font-medium">
                    +{{ data.value.items.length - 1 }} {{ translate("more") }}
                </div>
            </div>
        </template>

        <template #shipping="{ data }">
            <div class="space-y-1">

                <!-- Shipping Type -->
                <div v-if="data.value.order_type === 'shipping' && data.value.shipping_type">
            <span
                :class="[
                    'inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold capitalize',
                    getShippingTypeBadgeClass(data.value.shipping_type)
                ]"
            >
                <i :class="['mr-1', getShippingTypeIcon(data.value.shipping_type)]"></i>
                {{ data.value.shipping_type }}
            </span>
                </div>

                <!-- Local Delivery -->
                <div class="flex items-center space-x-1">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                {{ translate('local_delivery') }}:
            </span>

                    <span
                        v-if="data.value.local_delivery"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300"
                    >
                <i class="fas fa-check text-[10px]"></i>
                {{ translate('yes') }}
            </span>

                    <span
                        v-else
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300"
                    >
                <i class="fas fa-times text-[10px]"></i>
                {{ translate('no') }}
            </span>
                </div>

            </div>
        </template>



        <template #order_status_id="{ data }">
            <div class="space-y-1">
                <div class="flex justify-start">
                    <span
                        :style="{ backgroundColor: data.value.status?.color }"
                        class="inline-flex items-center px-3 py-1 rounded-md text-xs font-semibold text-white shadow-sm">
                        {{ data.value.status?.name }}
                    </span>
                </div>
                <div v-if="data.value.note" class="text-xs text-gray-600 dark:text-gray-400">
                    <i class="fas fa-comment-alt mr-1"></i>
                    <span>{{ data.value.note }}</span>
                </div>
            </div>
        </template>

        <template #image="{ data }">
            <div class="flex justify-center items-center">
                <div class="relative">
                    <div v-if="data.value.items && data.value.items.length > 0 && data.value.items[0].image">
                        <img :src="data.value.items[0].image" alt="Product Image"
                             class="w-12 h-12 object-cover rounded-lg border-2 border-white shadow-sm"/>
                        <div
                            class="absolute -top-1 -right-1 w-4 h-4 bg-primary rounded-full border-2 border-white flex items-center justify-center">
                            <i class="fas fa-image text-white text-xs"></i>
                        </div>
                    </div>
                    <div v-else
                         class="w-12 h-12 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                        <i class="fas fa-image text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
        </template>

        <template #action="{ data }">
            <div class="flex items-center">
                <view-button v-if="can('view_order')"
                             @click="() => router.push({ name: 'view_order', params: { id: data.value.id } })"
                />
                <edit-button v-if="can('edit_order')" class="mx-1"
                             @click="() => router.push({ name: 'edit_order', params: { id: data.value.id } })"
                />
                <more-action-button v-if="can('change_status_order') || can('delete_order')">
                    <more-action-button-item v-if="can('change_status_order')" icon="fas fa-sync-alt"
                                             :text="translate('change_status')"
                                             @click="handleChangeStatus(data.value)"/>
                    <more-action-button-item v-if="can('delete_order')" icon="fas fa-trash-alt"
                                             :text="translate('delete')"
                                             @click="handleDelete(data.value)"/>
                </more-action-button>
            </div>
        </template>
    </data-table>
</template>

<style scoped>
</style>
