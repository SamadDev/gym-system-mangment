<script setup>
import {reactive, onMounted, ref, computed} from 'vue';
import Breadcrumb from "../../components/Breadcrumb.vue";
import VueSelect from "../../components/VueSelect.vue";
import {createFormData, translate} from '../../../utils/functions.js';
import {axiosRequest} from "../../../utils/apiRequest.js";
import {useAppStore} from "../../../stores";
import SubmitButton from "../../components/SubmitButton.vue";
import CancelButton from "../../components/CancelButton.vue";
import moment from "moment";
import {useRoute, useRouter} from 'vue-router';

const store = useAppStore();
const router = useRouter();
const route = useRoute();
const invoiceId = route.params.id;
const pageType = route.name === 'edit_invoice' && invoiceId ? 'edit' : 'create';

const state = reactive({
    invoice: {
        customer_id: null,
        invoice_date: moment().format('YYYY-MM-DD'),
        invoice_number: null,
        currency_type_id: '',
        currency_rate: null,
        note: null,
        attachment: null,
        subtotal: 0,
        discount: 0,
        total: 0,
    },
    invoice_items: [
        {invoice_item_id: null, order_id: null, invoice_item: '', amount: 0, note: ''}
    ],
    errors: {},
    disableSubmit: false,
});

const customer_ref = ref(null);
const currencyTypes = ref([]);

// Add a new item row
function addItem() {
    state.invoice_items.push({invoice_item_id: null, order_id: null, invoice_item: '', amount: 0, note: ''});
}

// Remove an item row by index
function removeItem(index) {
    if (state.invoice_items.length > 1) {
        state.invoice_items.splice(index, 1);
    }
}

// Computed subtotal (sum of amounts)
const subtotal = computed(() => {
    const result = state.invoice_items.reduce((sum, item) => sum + Number(item.amount || 0), 0);
    state.invoice.subtotal = result;
    return result;
});

// Computed total (subtotal - discount)
const total = computed(() => {
    let disc = Number(state.invoice.discount || 0);
    const result = Math.max(subtotal.value - disc, 0);
    state.invoice.total = result;
    return result;
});

onMounted(async () => {
    currencyTypes.value = store.currencyTypeList;
    if (pageType === 'edit') {
        await fetchEditData(invoiceId);
    } else {
        const mainCurrency = currencyTypes.value.find(type => type.is_main === 1);
        if (mainCurrency) {
            state.invoice.currency_type_id = mainCurrency.id;
            state.invoice.currency_rate = parseFloat(mainCurrency.rate);
        }

        const orderIds = route.query.order_ids?.split(',') || [];
        const customerId = route.query.customer_id;
        if (orderIds.length && customerId) {
            fetchCustomerSelectedFromExternal(customerId, orderIds);
        }
    }
});

const toggleLoading = (isLoading) => store.togglePageLoader(isLoading);

const fetchEditData = async (invoice_id) => {
    toggleLoading(true);
    try {
        const res = await axiosRequest.get("/admin/invoices/" + invoice_id + '/show', {});
        const invoice = res.data.data;

        state.invoice = {
            customer_id: invoice.customer_id,
            invoice_date: invoice.invoice_date,
            invoice_number: invoice.invoice_number,
            currency_type_id: invoice.currency_type_id,
            currency_rate: parseFloat(invoice.currency_rate),
            note: invoice.note,
            subtotal: invoice.subtotal || 0,
            discount: invoice.discount || 0,
            total: invoice.total || 0,
            attachment: null,
        };

        state.invoice_items = invoice.items?.map(item => {
            return {
                invoice_item_id: item.id,
                order_id: item.order_id,
                invoice_item: item.invoice_item,
                amount: item.amount,
                note: item.note,
            }
        });

        customer_ref.value.setValue(invoice?.customer.name);

    } catch (error) {
        // console.log(error);
    } finally {
        toggleLoading(false);
    }

};

const handleSubmit = async () => {
    state.disableSubmit = true;


    if (pageType == 'create') {
        await createInvoice();
    } else if (pageType == 'edit') {
        await editInvoice();
    }

    state.disableSubmit = false;
};

const createInvoice = async () => {
    let url = "/admin/invoices/create";

    let formData = createFormData(state.invoice);
    formData.append('items', JSON.stringify(state.invoice_items));

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push({name: 'invoices'});
    } catch (error) {
        console.log(error.response);
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editInvoice = async () => {
    let url = "/admin/invoices/" + invoiceId + "/update";
    let formData = createFormData(state.invoice);
    formData.append('items', JSON.stringify(state.invoice_items));

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push({name: 'invoices'});
    } catch (error) {
        console.log(error.response);
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const handleCustomerSelected = (data) => {

    state.invoice.customer_id = data.id;
    state.invoice_items = [];

    if (data && data.orders.length && pageType === 'create') {
        data.orders.forEach(item => {
            state.invoice_items.push({
                invoice_item_id: null,
                order_id: item.id,
                invoice_item: item.order_number,
                amount: item.order_total,
                note: ''
            });
        });
    } else {
        state.invoice_items.push({invoice_item_id: null, order_id: null, invoice_item: '', amount: 0, note: ''});
    }
};

const handleCustomerDeselected = () => {
    state.invoice.customer_id = null;
};

const handleCurrencyTypeChange = (event) => {
    const selectedType = currencyTypes.value.find(type => type.id == event.target.value);
    if (selectedType) {
        state.invoice.currency_rate = selectedType.rate;
    }
};

const fetchCustomerSelectedFromExternal = async (customerdId, orderIds) => {
    try {
        const res = await axiosRequest.get("/admin/invoices/search-customer/", {
            customer_id: customerdId,
            order_ids: orderIds,
        });
        if(res.data.data.length === 1){
            const customer = res.data.data[0];
            state.invoice.customer_id = customer.id;
            customer_ref.value.setValue(customer.name);
            state.invoice_items = [];

            customer.orders.forEach(item => {
                state.invoice_items.push({
                    invoice_item_id: null,
                    order_id: item.id,
                    invoice_item: item.order_number,
                    amount: item.order_total,
                    note: ''
                });
            });
        }
    } catch (error) {
        // console.log(error);
    }
};

const handleFileUpload = (event) => {
    state.invoice.attachment = event.target.files[0];
};

const breadcrumbItems = [
    {label: translate('invoices'), url: '/admin/invoices'},
    {label: translate(pageType), url: null}
];

</script>

<template>
    <div>
        <!-- Breadcrumb -->
        <div class="flex items-center justify-between mb-6">
            <Breadcrumb :items="breadcrumbItems"/>
        </div>

        <!-- Invoice Information -->
        <div class="panel mb-5">
            <h3 class="text-lg font-semibold mb-4">{{ translate('invoice_information') }}</h3>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-4">
                    <label for="customer-id" class="form-label">{{ translate('customer') }}</label>
                    <vue-select
                        model="name"
                        ref="customer_ref"
                        url="/admin/invoices/search-customer"
                        :placeholder="translate('search_customer')"
                        @option:selected="handleCustomerSelected"
                        @option:deselected="handleCustomerDeselected"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.customer_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="invoice-number" class="form-label">{{ translate('invoice_number') }}</label>
                    <input
                        type="text"
                        class="form-input"
                        :placeholder="translate('invoice_number')"
                        v-model="state.invoice.invoice_number"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.invoice_number }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="invoice-date" class="form-label">{{ translate('invoice_date') }}</label>
                    <input
                        type="date"
                        class="form-input"
                        v-model="state.invoice.invoice_date"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.invoice_date }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="currency-type-id" class="form-label">{{ translate('currency_type') }}</label>
                    <select
                        v-model="state.invoice.currency_type_id"
                        class="form-select"
                        id="currency-type-id"
                        @change="handleCurrencyTypeChange"
                    >
                        <option value="" disabled>{{ translate('select_currency') }}</option>
                        <option
                            v-for="type in currencyTypes"
                            :key="type.id"
                            :value="type.id"
                        >
                            {{ type.symbol }} - {{ type.name }}
                        </option>
                    </select>
                    <div class="text-red-500 text-sm">{{ state.errors.currency_type_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="currency-rate" class="form-label">{{ translate('currency_rate') }}</label>
                    <input
                        type="number"
                        step="any"
                        class="form-input"
                        :placeholder="translate('currency_rate')"
                        v-model="state.invoice.currency_rate"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.currency_rate }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="attachment" class="form-label">{{ translate('attachment') }}</label>
                    <input
                        type="file"
                        class="form-input file:py-2 file:px-4 file:border-0 file:font-semibold p-0 file:bg-gray-100 ltr:file:mr-5 rtl:file:ml-5 file:hover:bg-gray-200 cursor-pointer"
                        id="attachment"
                        @change="handleFileUpload"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.attachment }}</div>
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="panel">
            <h3 class="text-lg font-semibold mb-4">{{ translate('invoice_items') }}</h3>
            <div class="grid grid-cols-12 gap-4">
                <!-- Invoice Items Table -->
                <div class="col-span-12">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full border border-gray-200">
                            <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-200 px-4 py-2 text-left">#</th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{
                                        translate('invoice_item')
                                    }}
                                </th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{ translate('amount') }}</th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{ translate('note') }}</th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{ translate('actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, index) in state.invoice_items" :key="index"
                                :class="item.order_id ? 'bg-blue-50' : ''">
                                <td class="border border-gray-200 px-4 py-2">{{ index + 1 }}</td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <input :disabled="item.order_id" type="text" class="form-input w-full"
                                           v-model="item.invoice_item"
                                           :placeholder="translate('invoice_item')">
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <input type="number" min="0" step="any" class="form-input w-full"
                                           v-model="item.amount"
                                           :disabled="item.order_id"
                                           :placeholder="translate('amount')">
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <input type="text" class="form-input w-full" v-model="item.note"
                                           :placeholder="translate('note')">
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <button class="btn btn-danger btn-sm" type="button" @click="removeItem(index)"
                                            :disabled="state.invoice_items.length === 1">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-span-12 grid grid-cols-12 gap-4">
                    <div class="col-span-9">
                        <div class="flex flex-col h-full">
                            <div class="mb-3">
                                <button class="btn btn-primary btn-sm" type="button" @click="addItem">
                                    {{ translate('add_row') }}
                                </button>
                            </div>
                            <div class="col-span-12 mb-3">
                                <label for="invoice-note" class="form-label">{{ translate('note') }}</label>
                                <textarea class="form-input" id="invoice-note" :placeholder="translate('note')"
                                          v-model="state.invoice.note"></textarea>
                                <div class="text-red-500 text-sm">{{ state.errors.note }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-3">
                        <div class="flex flex-col gap-3">
                            <div>
                                <label class="form-label">{{ translate('subtotal') }}</label>
                                <input type="number" class="form-input" v-model="subtotal" readonly>
                            </div>
                            <div>
                                <label class="form-label">{{ translate('discount') }}</label>
                                <input type="number" min="0" step="any" class="form-input"
                                       v-model="state.invoice.discount" :placeholder="translate('discount')">
                            </div>
                            <div>
                                <label class="form-label">{{ translate('total') }}</label>
                                <input type="number" v-model="total" class="form-input" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end items-center mt-8 bg-gray-100 border-t border-gray-300 px-3 py-2">
            <cancel-button href="/admin/invoices"/>
            <submit-button @click.prevent="handleSubmit" :disabled="state.disableSubmit"/>
        </div>
    </div>
</template>
