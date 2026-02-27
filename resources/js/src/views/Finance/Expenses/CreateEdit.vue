<script setup>
import {reactive, onMounted, ref} from 'vue';
import Breadcrumb from "../../components/Breadcrumb.vue";
import VueSelect from "../components/VueSelect.vue";
import {createFormData, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import InputWithCurrencyType from "../components/InputWithCurrencyType.vue";
import {useAppStore} from "../../stores";
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";
import moment from "moment";
import {useRoute, useRouter} from 'vue-router';

const store = useAppStore();
const router = useRouter();
const route = useRoute();
const expenseId = route.params.id;
const pageType = route.name === 'edit_expense' && expenseId ? 'edit' : 'create';

const state = reactive({
    expense: {
        amount: null,
        expense_category_id: null,
        safe_id: null,
        safe_account_id: null,
        currency_type_id: null,
        currency_rate: null,
        expense_date: moment().format('YYYY-MM-DD'),
        note: null,
        attachment: null,
    },
    errors: {},
    disableSubmit: false,
});

const expense_category_ref = ref(null);
const safe_ref = ref(null);
const safe_account_ref = ref(null);
const amountWithCurrencyRef = ref(null);

onMounted(async () => {
    if (pageType === 'edit') {
        await fetchEditData(expenseId);
    } else {
        resetExpenseData();
    }
});

const toggleLoading = (isLoading) => store.togglePageLoader(isLoading);

const fetchEditData = async (expense_id) => {
    toggleLoading(true);
    try {
        const res = await axiosRequest.get("/admin/expenses/" + expense_id, {});
        const expense = res.data.data;

        state.expense = {
            amount: parseFloat(expense.amount),
            expense_category_id: expense.expense_category_id,
            safe_id: expense.safe?.id,
            safe_account_id: expense.safe_account?.id,
            currency_type_id: expense.currency_type_id,
            currency_rate: expense.currency_rate,
            expense_date: expense.expense_date,
            note: expense.note,
            attachment: null,
        };

        expense_category_ref.value.setValue(expense?.expense_category.name);
        safe_ref.value.setValue(expense?.safe.name);
        safe_account_ref.value.setValue(expense?.safe_account?.name);
        amountWithCurrencyRef.value?.setCurrencyWithRate(expense.currency_type_id, expense.currency_rate);

    } catch (error) {
        console.log(error);
    } finally {
        toggleLoading(false);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;

    if (pageType == 'create') {
        await createExpense();
    } else if (pageType == 'edit') {
        await editExpense();
    }

    state.disableSubmit = false;
};

const createExpense = async () => {
    let url = "/admin/expenses/create";
    let formData = createFormData(state.expense);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/expenses');
    } catch (error) {
        console.log(error.response);
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editExpense = async () => {
    let url = "/admin/expenses/" + expenseId + "/update";
    let formData = createFormData(state.expense);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        router.push('/admin/expenses');
    } catch (error) {
        console.log(error.response);
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetExpenseData = () => {
    state.expense = {
        amount: null,
        expense_category_id: null,
        safe_id: null,
        safe_account_id: null,
        currency_type_id: null,
        currency_rate: null,
        expense_date: moment().format('YYYY-MM-DD'),
        note: null,
        attachment: null,
    };
    expense_category_ref.value?.reset();
    safe_ref.value?.reset();
    safe_account_ref.value?.reset();
    amountWithCurrencyRef.value?.resetCurrency();
    state.errors = {};
};

const handleExpenseCategoryDeselected = () => {
    state.expense['expense_category_id'] = null;
};

const handleSafeDeselected = () => {
    state.expense['safe_id'] = null;
};

const handleSafeAccountDeselected = () => {
    state.expense['safe_account_id'] = null;
};

const handleAmountChange = (amount) => {
    state.expense.amount = Number(amount);
}

const handleCurrencyTypeChange = (currency_type) => {
    state.expense.currency_type_id = currency_type.id;
    state.expense.currency_rate = currency_type.rate;
}

const handleFileUpload = (event) => {
    state.expense.attachment = event.target.files[0];
};

const breadcrumbItems = [
    {label: translate('expenses'), url: '/admin/expenses'},
    {label: pageType === 'create' ? translate('add') : translate('edit'), url: null}
];

</script>

<template>
    <div>
        <!-- Breadcrumb -->
        <div class="flex items-center justify-between mb-6">
            <Breadcrumb :items="breadcrumbItems"/>
        </div>

        <!-- Expense Information -->
        <div class="panel mb-5">
            <h3 class="text-lg font-semibold mb-4">{{ translate('expense_information') }}</h3>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-4">
                    <label for="expense-category-id" class="form-label">{{ translate('expense_category') }}</label>
                    <vue-select 
                        model="name" 
                        ref="expense_category_ref"
                        url="/admin/safe-transaction-categories/search?safe_transaction_type_id=3"
                        :placeholder="translate('search_expense_category')"
                        @option:selected="(data) => state.expense.expense_category_id = data.id"
                        @option:deselected="handleExpenseCategoryDeselected"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.expense_category_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="amount" class="form-label">{{ translate('amount') }}</label>
                    <input-with-currency-type 
                        id="amount" 
                        :modelValue="state.expense.amount"
                        ref="amountWithCurrencyRef"
                        :placeholder="translate('enter_amount')"
                        @update:inputValue="handleAmountChange"
                        @update:selectValue="handleCurrencyTypeChange"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.amount }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="currency-rate" class="form-label">{{ translate('currency_rate') }}</label>
                    <input 
                        type="number" 
                        step="any" 
                        class="form-input"
                        :placeholder="translate('currency_rate')"
                        v-model="state.expense.currency_rate"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.currency_rate }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="safe-id" class="form-label">{{ translate('safe') }}</label>
                    <vue-select 
                        model="name" 
                        ref="safe_ref"
                        url="/admin/safes/search"
                        :placeholder="translate('search_safe')"
                        @option:selected="(data) => state.expense.safe_id = data.id"
                        @option:deselected="handleSafeDeselected"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.safe_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="safe-account-id" class="form-label">{{ translate('safe_account') }}</label>
                    <vue-select 
                        model="name" 
                        ref="safe_account_ref"
                        :url="`/admin/safe-accounts/search?safe_id=${state.expense.safe_id}`"
                        :placeholder="translate('search_safe_account')"
                        @option:selected="(data) => state.expense.safe_account_id = data.id"
                        @option:deselected="handleSafeAccountDeselected"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.safe_account_id }}</div>
                </div>

                <div class="col-span-12 lg:col-span-4">
                    <label for="expense-date" class="form-label">{{ translate('expense_date') }}</label>
                    <input 
                        type="date" 
                        class="form-input"
                        v-model="state.expense.expense_date"
                    />
                    <div class="text-red-500 text-sm">{{ state.errors.expense_date }}</div>
                </div>

                <div class="col-span-12">
                    <label for="expense-note" class="form-label">{{ translate('note') }}</label>
                    <textarea 
                        class="form-input" 
                        id="expense-note" 
                        :placeholder="translate('note')"
                        v-model="state.expense.note"
                    ></textarea>
                    <div class="text-red-500 text-sm">{{ state.errors.note }}</div>
                </div>

                <div class="col-span-12">
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

        <!-- Action Buttons -->
        <div class="flex justify-end items-center mt-8 bg-gray-100 border-t border-gray-300 px-3 py-2">
            <cancel-button href="/admin/expenses"/>
            <submit-button @click.prevent="handleSubmit" :disabled="state.disableSubmit"/>
        </div>
    </div>
</template>

<style scoped>

</style>
