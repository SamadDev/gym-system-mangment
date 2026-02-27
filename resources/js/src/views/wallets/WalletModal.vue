<script setup>
import {reactive, onMounted, onUnmounted, ref, nextTick} from 'vue';
import VModal from '../components/VModal.vue';
import eventBus from '../../eventBus.js';
import {createFormData, translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import VueSelect from "../components/VueSelect.vue";
import InputWithCurrencyType from "../components/InputWithCurrencyType.vue";

const props = defineProps({
    titles: {
        type: Array,
        default: null,
        required: true,
    },
    customerId: {
        type: String,
        default: null,
    },
});

const state = reactive({
    wallet: {
        customer_id: props.customerId,
        amount: null,
        currency_type_id: null,
        currency_rate: null,
        transaction_type_id: '',
        note: '',
    },
    wallet_id: '',
    errors: {},
    title: props.titles[0],
    type: "create",
    disableSubmit: false,
});

const modalRef = ref(null);
const amountWithCurrencyRef = ref(null);
const transactionTypeRef = ref(null);

onMounted(() => {
    eventBus.on('openWalletCreateModal', openCreateModal);
    eventBus.on('openWalletEditModal', (data) => openEditModal(data.id));
});

onUnmounted(() => {
    eventBus.off('openWalletCreateModal');
    eventBus.off('openWalletEditModal');
});

const openCreateModal = () => {
    state.title = props.titles[0];
    state.type = "create";
    modalRef.value.openModal();
};

const openEditModal = async (wallet_id) => {

    try {
        const res = await axiosRequest.get("/admin/wallets/" + wallet_id + '/show', {});
        const wallet = res.data.data;

        state.wallet = {
            customer_id: wallet.customer_id,
            amount: parseFloat(wallet.amount),
            currency_type_id: wallet.currency_type_id,
            currency_rate: parseFloat(wallet.currency_rate),
            transaction_type_id: wallet.transaction_type?.id,
            note: wallet.note,
        };

        amountWithCurrencyRef.value?.setCurrencyWithRate(wallet.currency_type_id, wallet.currency_rate);

        // Set transaction type value in VueSelect
        if (wallet.transaction_type) {
            transactionTypeRef.value?.setValue(wallet.transaction_type.name);
        }
    } catch (error) {
        console.log(error.response);
    }

    state.wallet_id = wallet_id;
    state.title = props.titles[1];
    state.type = "edit";
    modalRef.value.openModal();
};

const handleSubmit = async () => {

    state.disableSubmit = true;

    if (state.type == "create") {
        await createWallet();
    } else if (state.type == "edit") {
        await editWallet();
    }

    state.disableSubmit = false;
}

const createWallet = async () => {

    let url = "/admin/wallets/create";
    let formData = createFormData(state.wallet);
    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        eventBus.emit('reloadWalletDatatable');
        modalRef.value.closeModal();
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const editWallet = async () => {

    let url = "/admin/wallets/" + state.wallet_id + "/update";
    let formData = createFormData(state.wallet);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        eventBus.emit('reloadWalletDatatable');
        modalRef.value.closeModal();
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
}

const resetWalletModal = () => {
    // Reset the form data or perform any other cleanup actions here
    state.wallet = {
        customer_id: props.customerId,
        amount: null,
        transaction_type_id: '',
        currency_type_id: null,
        currency_rate: null,
        note: null,
    };

    transactionTypeRef.value?.reset();
    amountWithCurrencyRef.value?.resetCurrency();

    state.wallet_id = '';
    state.errors = {};
};

const handleFormSelectDeselected = (type) => {
    state.wallet[type] = null;
};

const handleAmountChange = (amount) => {
    state.wallet.amount = Number(amount);
}

const handleCurrencyTypeChange = (currency_type) => {
    state.wallet.currency_type_id = currency_type.id;
    state.wallet.currency_rate = parseFloat(currency_type.rate);
}

</script>

<template>
    <v-modal
        :title="state.title"
        :onSubmit="handleSubmit"
        :disableSubmit="state.disableSubmit"
        ref="modalRef"
        @modal:closed="resetWalletModal"
    >
        <div class="grid grid-cols-12 gap-4">

            <div class="col-span-12 lg:col-span-4">
                <label for="amount" class="form-label">{{ translate('transaction_type') }}</label>
                <vue-select
                    model="name"
                    ref="transactionTypeRef"
                    :url="`/admin/safe-transaction-types/search?types[]=deposit&types[]=withdraw`"
                    :placeholder="translate('select_transaction_type')"
                    @option:selected="(data) => state.wallet.transaction_type_id = data.id"
                    @option:deselected="handleFormSelectDeselected('transaction_type_id')"/>
                <div class="text-red-500 text-sm">{{ state.errors.transaction_type_id }}</div>
            </div>

            <div class="col-span-12 lg:col-span-4">
                <label for="amount" class="form-label">{{ translate('amount') }}</label>
                <input-with-currency-type
                    id="amount"
                    :modelValue="state.wallet.amount"
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
                    v-model="state.wallet.currency_rate"
                />
                <div class="text-red-500 text-sm">{{ state.errors.currency_rate }}</div>
            </div>

            <div class="col-span-12">
                <label for="wallet-note" class="form-label">{{ translate('note') }}</label>
                <textarea
                    class="form-input"
                    id="wallet-note"
                    :placeholder="translate('note')"
                    v-model="state.wallet.note"
                ></textarea>
                <div class="text-red-500 text-sm">{{ state.errors.note }}</div>
            </div>

        </div>
    </v-modal>
</template>

<style scoped>
</style>
