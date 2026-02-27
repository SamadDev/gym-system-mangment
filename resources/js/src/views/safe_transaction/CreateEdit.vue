<script setup>
import {reactive, onMounted, ref, computed} from 'vue';
import Breadcrumb from "../components/Breadcrumb.vue";
import VueSelect from "../components/VueSelect.vue";
import InputWithCurrencyType from "../components/InputWithCurrencyType.vue";
import {createFormData, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import moment from 'moment';
import {useRoute, useRouter} from 'vue-router';

const router = useRouter();
const route = useRoute();
const safeTransactionId = route.params.id;
const pageType = route.name === 'edit_safe_transaction' && safeTransactionId ? 'edit' : 'create';

const state = reactive({
    safe_transaction: {
        safe_transaction_type_id: null,
        safe_transaction_category_id: null,
        safe_id: null,
        safe_account_id: null,
        to_safe_id: null,
        to_safe_account_id: null,
        currency_type_id: null,
        currency_rate: null,
        amount: null,
        to_amount: null,
        to_currency_type_id: null,
        to_currency_rate: null,
        transaction_date: null,
        note: null,
        attachment: null,
    },
    errors: {},
    disableSubmit: false,
    currentStep: 1,
    totalSteps: 1,
});

const transaction_type_ref = ref(null);
const transaction_category_ref = ref(null);
const safe_ref = ref(null);
const to_safe_ref = ref(null);
const safe_account_ref = ref(null);
const to_safe_account_ref = ref(null);
const amount_with_currency_ref = ref(null);
const to_amount_with_currency_ref = ref(null);

const show = reactive({
    category: true,
    to_safe: false,
    to_currency: false,
});

const transactionType = computed(() => {
    return transaction_type_ref.value?.getValue();
});

const isTransfer = computed(() => transactionType.value === 'Transfer');
const isExchange = computed(() => transactionType.value === 'Exchange');
const isSimpleTransaction = computed(() => !isTransfer.value && !isExchange.value);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(safeTransactionId);
    } else {
        resetSafeTransactionData();
    }
});

const fetchEditData = async (safe_transaction_id) => {
    try {
        const res = await axiosRequest.get("/admin/safe-transactions/" + safe_transaction_id + "/show", {});
        const safe_transaction = res.data.data;

        state.safe_transaction = {
            safe_transaction_type_id: safe_transaction.safe_transaction_type_id,
            safe_transaction_category_id: safe_transaction.safe_transaction_category_id,
            safe_id: safe_transaction.safe_id,
            safe_account_id: safe_transaction.safe_account_id,
            amount: parseFloat(safe_transaction.amount) * 100 / 100,
            transaction_date: safe_transaction.transaction_date,
            note: safe_transaction.note,
        };

        transaction_type_ref.value.setValue(safe_transaction?.safe_transaction_type.name);
        transaction_category_ref.value?.setValue(safe_transaction?.safe_transaction_category?.name);
        safe_ref.value.setValue(safe_transaction?.safe?.name);
        safe_account_ref.value.setValue(safe_transaction?.safe_account?.name);
        amount_with_currency_ref.value?.setCurrencyWithRate(safe_transaction.currency_type_id, safe_transaction.currency_rate);

        showHideInputs();
    } catch (error) {
        console.log(error);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createSafeTransaction();
    } else if (pageType == 'edit') {
        await editSafeTransaction();
    }

    state.disableSubmit = false;
};

const createSafeTransaction = async () => {
    let url = "/admin/safe-transactions/create";
    let formData = createFormData(state.safe_transaction);
    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/safe-transactions');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editSafeTransaction = async () => {
    let url = "/admin/safe-transactions/" + safeTransactionId + "/update";
    let formData = createFormData(state.safe_transaction);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/safe-transactions');
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetSafeTransactionData = () => {
    state.safe_transaction = {
        safe_transaction_type_id: null,
        safe_transaction_category_id: null,
        safe_id: null,
        safe_account_id: null,
        to_safe_account_id: null,
        currency_type_id: null,
        currency_rate: null,
        amount: null,
        to_amount: null,
        transaction_date: moment().format('YYYY-MM-DD'),
        note: null,
        attachment: null,
    };
    transaction_type_ref.value?.reset();
    transaction_category_ref.value?.reset();
    safe_ref.value?.reset();
    to_safe_ref.value?.reset();
    safe_account_ref.value?.reset();
    to_safe_account_ref.value?.reset();
    amount_with_currency_ref.value?.resetCurrency();
    to_amount_with_currency_ref.value?.resetCurrency();
    state.errors = {};
    showHideInputs();
};

const showHideInputs = () => {
    const type = transaction_type_ref.value?.getValue();

    if (type == 'Withdraw' || type == 'Deposit') {
        show.category = true;
        show.to_safe = false;
        show.to_currency = false;
    } else if (type == 'Transfer') {
        show.category = false;
        show.to_safe = true;
        show.to_currency = false;
    } else if (type == 'Exchange') {
        show.category = false;
        show.to_safe = false;
        show.to_currency = true;
    }

    if (pageType == "edit") {
        show.category = true;
        show.to_safe = false;
        show.to_currency = false;
    }
};

const handleTransactionTypeChange = (data) => {
    state.safe_transaction.safe_transaction_type_id = data.id;
    state.safe_transaction.safe_transaction_category_id = null;
    transaction_category_ref.value?.reset();

    showHideInputs();
};

const handleSafeSelected = (data) => {
    state.safe_transaction.safe_id = data.id;
    state.safe_transaction.safe_account_id = null;
    safe_account_ref.value?.reset();
};

const handleToSafeSelected = (data) => {
    state.safe_transaction.to_safe_id = data.id;
    state.safe_transaction.to_safe_account_id = null;
    to_safe_account_ref.value?.reset();
};

const calculateExchangeAmount = () => {
    if (transaction_type_ref.value.getValue() == 'Exchange') {
        let amount = state.safe_transaction.amount / state.safe_transaction.currency_rate;
        let exchangeAmount = amount * state.safe_transaction.to_currency_rate;
        state.safe_transaction.to_amount = Math.round(exchangeAmount * 100) / 100;
    }
};

const handleAmountChange = (amount) => {
    state.safe_transaction.amount = Number(amount);
    calculateExchangeAmount();
};

const handleCurrencyTypeChange = (currency_type) => {
    state.safe_transaction.currency_type_id = currency_type.id;
    state.safe_transaction.currency_rate = currency_type.rate;
    calculateExchangeAmount();
};

const handleToAmountChange = (to_amount) => {
    state.safe_transaction.to_amount = Number(to_amount);
    calculateExchangeAmount();
};

const handleToCurrencyTypeChange = (to_currency_type) => {
    state.safe_transaction.to_currency_type_id = to_currency_type.id;
    state.safe_transaction.to_currency_rate = to_currency_type.rate;
    calculateExchangeAmount();
};

const handleFileUpload = (event) => {
    state.safe_transaction.attachment = event.target.files[0];
};

const handleFormSelectDeselected = (type) => {
    state.safe_transaction[type] = null;
};

const breadcrumbItems = [
    {label: translate('safe_transactions'), url: '/admin/safe-transactions'},
    {label: pageType === 'create' ? translate('add') : translate('edit'), url: null}
];

</script>

<template>
    <!-- Main container -->
    <div>
        <!-- Top row: Breadcrumb -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>
        </div>

        <form @submit.prevent="handleSubmit">
            <div class="space-y-6">
                <!-- Transaction Type Selection Card -->
                <div class="panel">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ translate('transaction_type') }}</h3>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-cog text-primary"></i>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('type') }}</label>
                            <vue-select ref="transaction_type_ref" model="name"
                                        url="/admin/safe-transaction-types/search"
                                        :placeholder="translate('select_safe_transaction_type')"
                                        @option:selected="handleTransactionTypeChange"
                                        @option:deselected="handleFormSelectDeselected('safe_transaction_type_id')"/>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.safe_transaction_type_id }}</div>
                        </div>

                        <div v-if="show.category">
                            <label class="block text-sm font-medium mb-2">{{ translate('category') }}</label>
                            <vue-select model="name" ref="transaction_category_ref"
                                        :url="`/admin/safe-transaction-categories/search?safe_transaction_type_id=${state.safe_transaction.safe_transaction_type_id}`"
                                        :placeholder="translate('select_safe_transaction_category')"
                                        :disabled="!show.category"
                                        @option:selected="(data) => state.safe_transaction.safe_transaction_category_id = data.id"
                                        @option:deselected="handleFormSelectDeselected('safe_transaction_category_id')"/>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.safe_transaction_category_id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Transaction Details Card -->
                <div class="panel">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ translate('transaction_details') }}</h3>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-info-circle text-info"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Date -->
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('date') }}</label>
                            <input type="date" class="form-input"
                                   v-model="state.safe_transaction.transaction_date">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.transaction_date }}</div>
                        </div>

                        <!-- Amount -->
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('amount') }}</label>
                            <input-with-currency-type id="amount" :modelValue="state.safe_transaction.amount"
                                                      ref="amount_with_currency_ref"
                                                      :placeholder="translate('enter_amount')"
                                                      @update:inputValue="handleAmountChange"
                                                      @update:selectValue="handleCurrencyTypeChange"/>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.amount }}</div>
                        </div>

                        <!-- Currency Rate -->
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('currency_rate') }}</label>
                            <input type="number" step="any" :placeholder="translate('currency_rate')" class="form-input"
                                   @input="calculateExchangeAmount" v-model="state.safe_transaction.currency_rate">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.currency_rate }}</div>
                        </div>
                    </div>
                </div>

                <!-- From Safe Card -->
                <div class="panel">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ translate('from_safe') }}</h3>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-vault text-warning"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('safe') }}</label>
                            <vue-select model="name" ref="safe_ref" url="/admin/safes/search"
                                        :placeholder="translate('select_safe')"
                                        @option:selected="handleSafeSelected"
                                        @option:deselected="handleFormSelectDeselected('safe_id')"/>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.safe_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('safe_account') }}</label>
                            <vue-select model="name" ref="safe_account_ref"
                                        :url="`/admin/safe-accounts/search?safe_id=${state.safe_transaction.safe_id}`"
                                        :placeholder="translate('select_safe_account')"
                                        @option:selected="(data) => state.safe_transaction.safe_account_id = data.id"
                                        @option:deselected="handleFormSelectDeselected('safe_account_id')"/>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.safe_account_id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Transfer Details Card (Only for Transfer) -->
                <div v-if="isTransfer" class="panel border-l-4 border-l-primary">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ translate('transfer_details') }}</h3>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-exchange-alt text-primary"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('to_safe') }}</label>
                            <vue-select model="name" ref="to_safe_ref" url="/admin/safes/search"
                                        :placeholder="translate('select_safe')"
                                        @option:selected="handleToSafeSelected"
                                        @option:deselected="handleFormSelectDeselected('to_safe_id')"/>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.to_safe_id }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('to_safe_account') }}</label>
                            <vue-select model="name" ref="to_safe_account_ref"
                                        :url="`/admin/safe-accounts/search?safe_id=${state.safe_transaction.to_safe_id}`"
                                        :placeholder="translate('select_safe_account')"
                                        @option:selected="(data) => state.safe_transaction.to_safe_account_id = data.id"
                                        @option:deselected="handleFormSelectDeselected('to_safe_account_id')"/>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.to_safe_account_id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Exchange Details Card (Only for Exchange) -->
                <div v-if="isExchange" class="panel border-l-4 border-l-success">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ translate('exchange_details') }}</h3>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-coins text-success"></i>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('to_amount') }}</label>
                            <input-with-currency-type id="to-amount" :disabledInput="true"
                                                      :placeholder="translate('enter_to_amount')"
                                                      ref="to_amount_with_currency_ref"
                                                      :modelValue="state.safe_transaction.to_amount"
                                                      @update:inputValue="handleToAmountChange"
                                                      @update:selectValue="handleToCurrencyTypeChange"/>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.to_amount }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('to_currency_rate') }}</label>
                            <input type="number" step="any" id="to-currency-rate"
                                   :placeholder="translate('to_currency_rate')"
                                   class="form-input" @input="calculateExchangeAmount"
                                   v-model="state.safe_transaction.to_currency_rate">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.to_currency_rate }}</div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information Card -->
                <div class="panel">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ translate('additional_information') }}</h3>
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-plus-circle text-info"></i>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('note') }}</label>
                            <textarea class="form-input" id="safe-transaction-note"
                                      v-model="state.safe_transaction.note"
                                      :placeholder="translate('enter_note_optional')"></textarea>
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.note }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2">{{ translate('attachment') }}</label>
                            <input type="file" class="form-input file:py-2 file:px-4 file:border-0 file:font-semibold p-0 file:bg-gray-100 ltr:file:mr-5 rtl:file:ml-5 file:hover:bg-gray-200 cursor-pointer"
                                   id="attachment" @change="handleFileUpload"
                                   accept=".jpeg,.png,.jpg,.gif,.pdf">
                            <div class="text-red-500 text-sm mt-1">{{ state.errors.attachment }}</div>
                        </div>
                    </div>
                </div>

                <!-- Action buttons -->
                <div class="flex justify-end gap-3 mt-6">
                    <cancel-button @click="router.push('/admin/safe-transactions')" />
                    <submit-button :disabled="state.disableSubmit" />
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>

</style>
