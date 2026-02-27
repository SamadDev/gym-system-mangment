<script setup>
import {reactive, onMounted, onUnmounted, ref} from 'vue';
import VModal from '../components/VModal.vue';
import eventBus from '../../eventBus.js';
import {translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";
import VueSelect from "../components/VueSelect.vue";
import {useAppStore} from "../../stores";

const store = useAppStore();

const props = defineProps({
    titles: {
        type: Array,
        default: null,
        required: true,
    },
});

const state = reactive({
    payment: {
        status_id: '',
        status_note: '',
    },
    payment_id: '',
    errors: {},
    title: props.titles[0],
    disableSubmit: false,
});

const modalRef = ref(null);
const status_ref = ref(null);

onMounted(() => {
    eventBus.on('openPaymentChangeStatusModal', (data) => openChangeStatusModal(data.id));
});

onUnmounted(() => {
    eventBus.off('openPaymentChangeStatusModal');
});

const toggleLoading = (isLoading) => store.togglePageLoader(isLoading);

const openChangeStatusModal = async (payment_id) => {
    toggleLoading(true);
    try {
        const res = await axiosRequest.get("/admin/payments/" + payment_id + '/show', {});
        const payment = res.data.data;

        state.payment = {
            status_id: payment?.status?.status_id,
            status_note: payment?.status?.note,
        };

        state.payment_id = payment_id;

        status_ref.value.setValue(payment?.status?.name);
        modalRef.value.openModal();

    } catch (error) {
        console.log(error);
    } finally {
        toggleLoading(false);
    }

    state.title = props.titles[0];
};

const handleSubmit = async () => {

    state.disableSubmit = true;

    await changeStatus();

    state.disableSubmit = false;
}

const changeStatus = async () => {

    let url = "/admin/payments/" + state.payment_id + "/change-status";

    try {
        await axiosRequest.put(url, state.payment, {notify: true});
        eventBus.emit('reloadPaymentDatatable');
        modalRef.value.closeModal();
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
}

const resetPaymentModal = () => {
    // Reset the form data or perform any other cleanup actions here
    state.payment = {
        status_id: '',
        status_note: '',
    };

    status_ref.value?.reset();

    state.payment_id = '';
    state.errors = {};
};

const handleStatusSelected = (data) => {
    state.payment.status_id = data.id;
    state.payment.status_note = null;
};

</script>

<template>
    <v-modal
        :title="state.title"
        :onSubmit="handleSubmit"
        size="max-w-lg"
        :disableSubmit="state.disableSubmit"
        ref="modalRef"
        @modal:closed="resetPaymentModal"
    >
        <div class="space-y-4">
            <div>
                <label for="payment-status" class="font-semibold text-base">{{ translate('status') }}</label>
                <vue-select model="name" ref="status_ref"
                            url="/admin/statuses/search?status_type=payment"
                            :placeholder="translate('select_status')"
                            @option:selected="handleStatusSelected"
                            @option:deselected="() => state.payment.status_id = null"
                />
                <div class="text-red-500 text-sm">{{ state.errors.status_id }}</div>
            </div>
            <div>
                <label for="status-note" class="font-semibold text-base">{{ translate('note') }}</label>
                <textarea class="form-input" id="status-note"
                          :placeholder="translate('note')"
                          v-model="state.payment.status_note"></textarea>
                <div class="text-red-500 text-sm">{{ state.errors.status_note }}</div>
            </div>
        </div>
    </v-modal>
</template>
