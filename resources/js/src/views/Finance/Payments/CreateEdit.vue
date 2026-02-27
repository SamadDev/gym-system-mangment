<script setup>
import {reactive, onMounted, ref, computed} from 'vue';
import Breadcrumb from "../../components/Breadcrumb.vue";
import VueSelect from "../components/VueSelect.vue";
import {createFormData, formatPrice, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import {useAppStore} from "../../stores";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import moment from "moment";
import {useRoute, useRouter} from 'vue-router';

const store = useAppStore();
const router = useRouter();
const route = useRoute();
const paymentId = route.params.id;
const pageType = route.name === 'edit_payment' && paymentId ? 'edit' : 'create';

const state = reactive({
    payment: {
        customer_id: null,
        payment_date: moment().format('YYYY-MM-DD'),
        payment_number: null,
        currency_type_id: '',
        currency_rate: null,
        payment_method_id: null,
        safe_id: null,
        safe_account_id: null,
        note: null,
        attachment: null,
        subtotal: 0,
        received_amount: 0,
        discount: 0,
        total: 0,
    },
    payment_items: [],
    errors: {},
    disableSubmit: false,
});

const customer_ref = ref(null);
const paymentMethodRef = ref(null);
const safe_ref = ref(null);
const safe_account_ref = ref(null);
const currencyTypes = ref([]);
const mainCurrency = ref({});
const selectedCurrencyType = ref({});

const paymentMethodName = computed(() => paymentMethodRef.value?.getValue()?.toLowerCase());

// Remove an item row by index
function removeItem(index) {
    if (state.payment_items.length > 1) {
        state.payment_items.splice(index, 1);
    }
}

// Computed subtotal (sum of amounts)
const computedSubtotal = computed(() => {
    const sum = state.payment_items.reduce((sum, item) => sum + Number((item.left_amount || 0) * state.payment.currency_rate), 0);
    const result = Number(sum.toFixed(2));
    state.payment.subtotal = result;
    if(pageType === 'create') {
        state.payment.received_amount = result;
    }
    return result;
});

// Computed total (subtotal - discount)
const computedTotal = computed(() => {
    let disc = Number(state.payment.discount || 0);
    const afterDiscount = Math.max(state.payment.received_amount - disc, 0);
    const result = Number(afterDiscount.toFixed(2));
    state.payment.total = result;
    return result;
});

const computedItems = computed(() => {

    return state.payment_items.map(item => {

        let totalAmountText = '';
        if (pageType === 'edit') {
            if (item.invoice) {
                totalAmountText = item.invoice.currency_type_id === mainCurrency.value.id ? formatPrice(item.invoice.total, item.invoice.currency_type) : formatPrice(item.invoice.total / item.invoice.currency_rate, mainCurrency.value) + ' (' + formatPrice(item.invoice.total, item.invoice.currency_type) + ')';
            }
        } else {
            totalAmountText = item.currency_type.id === mainCurrency.value.id ? formatPrice(item.total_amount, item.currency_type) : formatPrice(item.total_amount / item.currency_rate, mainCurrency.value) + ' (' + formatPrice(item.total_amount, item.currency_type) + ')';
        }

        return {
            payment_item_id: item.payment_item_id ? item.payment_item_id : null,
            invoice_id:  item.id ,
            payment_item: item.payment_item,
            total_amount: item.total_amount,
            paid_amount: item.paid_amount,
            left_amount: Number((item.left_amount * state.payment.currency_rate).toFixed(2)),
            total_amount_text: totalAmountText,
            note: item.note,
        };
    });
});

onMounted(async () => {
    currencyTypes.value = store.currencyTypeList;
    mainCurrency.value = currencyTypes.value.find(item => item.is_main === 1);
    selectedCurrencyType.value = mainCurrency.value;

    if (pageType === 'edit') {
        await fetchEditData(paymentId);
    } else {
        resetPaymentData();
    }
});

const toggleLoading = (isLoading) => store.togglePageLoader(isLoading);

const fetchEditData = async (payment_id) => {
    toggleLoading(true);
    state.type = "edit";

    try {
        const res = await axiosRequest.get("/admin/payments/" + payment_id + '/show', {});
        const payment = res.data.data;

        state.payment = {
            customer_id: payment.customer_id,
            payment_date: payment.payment_date,
            payment_number: payment.payment_number,
            currency_type_id: payment.currency_type_id,
            currency_rate: parseFloat(payment.currency_rate),
            payment_method_id: payment.payment_method_id,
            safe_id: payment?.safe?.id,
            safe_account_id: payment?.safe_account?.id,
            note: payment.note,
            subtotal: payment.subtotal || 0,
            received_amount: payment.subtotal || 0,
            discount: payment.discount || 0,
            total: payment.total || 0,
            attachment: null,
        };

        selectedCurrencyType.value = payment.currency_type;

        state.payment_items = payment.items?.map(item => {

            let leftAmount = 0;
            if (item.invoice) {
                leftAmount = item.invoice.total / item.invoice.currency_rate;
            }

            return {
                payment_item_id: item.id,
                id: item.invoice_id,
                payment_item: item.payment_item,
                total_amount: leftAmount,
                paid_amount: item.invoice_paid_amount,
                left_amount: item.invoice_left_amount,
                note: item.note,
                currency_type: item.currency_type,
                invoice: item.invoice,
            }
        });

        customer_ref.value.setValue(payment?.customer.name);
        if (payment?.payment_method) {
            paymentMethodRef.value.setValue(payment.payment_method.name);
        }

        if (payment?.safe) {
            safe_ref.value.setValue(payment.safe.name);
        }
        if (payment?.safe_account) {
            safe_account_ref.value.setValue(payment.safe_account.name);
        }

    } catch (error) {
        // console.log(error);
    } finally {
        toggleLoading(false);
    }

};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createPayment();
    } else if (pageType == 'edit') {
        await editPayment();
    }

    state.disableSubmit = false;
};

const createPayment = async () => {
    let url = "/admin/payments/create";

    let formData = createFormData(state.payment);
    const createPaymentItems = computedItems.value.map(item => {
        return {
            payment_item_id: item.payment_item_id,
            invoice_id: item.invoice_id,
            payment_item: item.payment_item,
            amount: item.left_amount,
            note: item.note,
        };
    });

    formData.append('items', JSON.stringify(createPaymentItems));

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/payments');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editPayment = async () => {
    let url = "/admin/payments/" + paymentId + "/update";

    let formData = createFormData(state.payment);
    const createPaymentItems = computedItems.value.map(item => {
        return {
            payment_item_id: item.payment_item_id,
            invoice_id: item.invoice_id,
            payment_item: item.payment_item,
            amount: item.left_amount,
            note: item.note,
        };
    });

    formData.append('items', JSON.stringify(createPaymentItems));

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/payments');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetPaymentData = () => {
    state.payment = {
        customer_id: null,
        payment_date: moment().format('YYYY-MM-DD'),
        payment_number: null,
        currency_type_id: mainCurrency.value.id,
        currency_rate: mainCurrency.value.rate,
        payment_method_id: null,
        safe_id: null,
        safe_account_id: null,
        note: null,
        attachment: null,
        subtotal: 0,
        received_amount: 0,
        discount: 0,
        total: 0,
    };
    resetPaymentItems();
    customer_ref.value?.reset();
    paymentMethodRef.value?.reset();
    safe_ref.value?.reset();
    safe_account_ref.value?.reset();
    state.errors = {};
};

const handleCustomerSelected = (data) => {
    state.payment.customer_id = data.id;
    if (pageType == 'create' && data.invoices.length > 0) {
        state.payment_items = data.invoices;
    } else {
        resetPaymentItems();
    }
};

const handleCustomerDeselected = () => {
    state.payment['customer_id'] = null;
    resetPaymentItems();
};

const handleCurrencyTypeChange = (e) => {
    const id = e.target.value;
    selectedCurrencyType.value = currencyTypes.value.find(item => item.id == id);
    if (selectedCurrencyType.value) {
        state.payment.currency_rate = selectedCurrencyType.value.rate;
    }
};

const handleFileUpload = (event) => {
    state.payment.attachment = event.target.files[0];
};

const handlePaymentMethodDeselected = () => {
    state.payment['payment_method_id'] = null;
};

const handleSafeDeselected = () => {
    state.payment['safe_id'] = null;
    state.payment['safe_account_id'] = null;
    safe_account_ref.value?.reset();
};

const handleSafeAccountDeselected = () => {
    state.payment['safe_account_id'] = null;
};

const resetPaymentItems = () => {
    state.payment_items = [];
}

const breadcrumbItems = [
    {label: translate('payments'), url: '/admin/payments'},
    {label: pageType === 'create' ? translate('add') : translate('edit'), url: null}
];

</script>

<template>
    <div>
        <!-- Breadcrumb -->
        <div class="flex items-center justify-between mb-6">
            <Breadcrumb :items="breadcrumbItems"/>
        </div>

        <!-- Payment Information -->
        <div class="panel mb-5">
            <h3 class="text-lg font-semibold mb-4">{{ translate('payment_information') }}</h3>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-4">
                    <label for="customer-id" class="form-label">{{ translate('customer') }}</label>
                    <vue-select
                        model="name"
                        ref="customer_ref"
                        url="/admin/payments/search-customer"
                        :placeholder="translate('search_customer')"
                        @option:selected="handleCustomerSelected"
                        @option:deselected="handleCustomerDeselected"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.customer_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="payment-number" class="form-label">{{ translate('payment_number') }}</label>
                    <input
                        type="text"
                        class="form-input"
                        :placeholder="translate('payment_number')"
                        v-model="state.payment.payment_number"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.payment_number }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="payment-date" class="form-label">{{ translate('payment_date') }}</label>
                    <input
                        type="date"
                        class="form-input"
                        v-model="state.payment.payment_date"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.payment_date }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="currency-type-id" class="form-label">{{ translate('currency_type') }}</label>
                    <select
                        v-model="state.payment.currency_type_id"
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
                        v-model="state.payment.currency_rate"
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

        <!-- Payment Items -->
        <div class="panel">
            <h3 class="text-lg font-semibold mb-4">{{ translate('payment_items') }}</h3>
            <div class="grid grid-cols-12 gap-4">
                <!-- Payment Items Table -->
                <div class="col-span-12">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full border border-gray-200">
                            <thead>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-200 px-4 py-2 text-left">#</th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{ translate('payment_item') }}</th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{ translate('total_amount') }}</th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{ translate('paid_amount') }}</th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{ translate('left_amount') }}</th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{ translate('note') }}</th>
                                <th class="border border-gray-200 px-4 py-2 text-left">{{ translate('actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="(item, index) in computedItems" :key="index">
                                <td class="border border-gray-200 px-4 py-2">{{ index + 1 }}</td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <input type="text" class="form-input w-full" v-model="item.payment_item"
                                           :placeholder="translate('payment_item')">
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <div class="form-input">{{ item.total_amount_text }}</div>
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <div class="form-input">{{
                                            formatPrice(item.paid_amount, mainCurrency)
                                        }}
                                    </div>
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <div class="form-input">{{
                                            formatPrice(item.left_amount, selectedCurrencyType)
                                        }}
                                    </div>
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <input type="text" class="form-input w-full" v-model="item.note"
                                           :placeholder="translate('note')">
                                </td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <button class="btn btn-danger btn-sm" type="button" @click="removeItem(index)"
                                            :disabled="state.payment_items.length === 1">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class="font-bold text-sm border border-gray-200 px-4 py-2">{{ translate('subtotal') }}:</td>
                                <td class="font-bold text-sm border border-gray-200 px-4 py-2">{{ formatPrice(computedSubtotal, selectedCurrencyType) }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="col-span-12 grid grid-cols-12 gap-4">
                    <div class="col-span-9">
                        <div class="flex flex-col h-full">
                            <div class="col-span-12 mb-3">
                                <label for="payment-note" class="form-label">{{ translate('note') }}</label>
                                <textarea class="form-input" id="payment-note" :placeholder="translate('note')"
                                          v-model="state.payment.note"></textarea>
                                <div class="text-red-500 text-sm">{{ state.errors.note }}</div>
                            </div>

                            <!-- Safe Transaction Fields -->
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-12 lg:col-span-4">
                                    <label for="payment-method-id" class="form-label">{{ translate('payment_method') }}</label>
                                    <vue-select
                                        model="name"
                                        ref="paymentMethodRef"
                                        url="/admin/payment-methods/search"
                                        :placeholder="translate('search_payment_method')"
                                        @option:selected="(data) => state.payment.payment_method_id = data.id"
                                        @option:deselected="handlePaymentMethodDeselected"
                                    />
                                    <div class="text-red-500 text-sm">{{ state.errors.payment_method_id }}</div>
                                </div>

                                <div v-if="paymentMethodName != 'wallet'" class="col-span-12 lg:col-span-4">
                                    <label for="safe-id" class="form-label">{{ translate('safe') }}</label>
                                    <vue-select
                                        model="name"
                                        ref="safe_ref"
                                        url="/admin/safes/search"
                                        :placeholder="translate('search_safe')"
                                        @option:selected="(data) => state.payment.safe_id = data.id"
                                        @option:deselected="handleSafeDeselected"
                                    />
                                    <div class="text-red-500 text-sm">{{ state.errors.safe_id }}</div>
                                </div>

                                <div v-if="paymentMethodName != 'wallet'" class="col-span-12 lg:col-span-4">
                                    <label for="safe-account-id" class="form-label">{{ translate('safe_account') }}</label>
                                    <vue-select
                                        model="name"
                                        ref="safe_account_ref"
                                        :url="`/admin/safe-accounts/search?safe_id=${state.payment.safe_id}`"
                                        :placeholder="translate('search_safe_account')"
                                        @option:selected="(data) => state.payment.safe_account_id = data.id"
                                        @option:deselected="handleSafeAccountDeselected"
                                    />
                                    <div class="text-red-500 text-sm">{{ state.errors.safe_account_id }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-3">
                        <div class="flex flex-col gap-3">
                            <div>
                                <label class="form-label">{{ translate('received_amount') }}</label>
                                <input step="any" type="number" class="form-input"
                                       v-model="state.payment.received_amount">
                            </div>
                            <div>
                                <label class="form-label">{{ translate('discount') }}</label>
                                <input type="number" min="0" step="any" class="form-input"
                                       v-model="state.payment.discount" :placeholder="translate('discount')">
                            </div>
                            <div>
                                <label class="form-label">{{ translate('total') }}</label>
                                <div class="form-input" readonly>{{ formatPrice(computedTotal, selectedCurrencyType) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end items-center mt-8 bg-gray-100 border-t border-gray-300 px-3 py-2">
            <cancel-button href="/admin/payments"/>
            <submit-button @click.prevent="handleSubmit" :disabled="state.disableSubmit"/>
        </div>
    </div>
</template>
