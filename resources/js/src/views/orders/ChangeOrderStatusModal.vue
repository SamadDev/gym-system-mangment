<script setup>
import {reactive, onMounted, onUnmounted, ref} from 'vue';
import VModal from '../components/VModal.vue';
import eventBus from '../../eventBus.js';
import {createFormData, translate} from '../../utils/functions.js';
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

const modalRef = ref(null);
const status_ref = ref(null);

const state = reactive({
    order: {
        status_id: '',
        status_note: '',
    },
    order_id: '',
    errors: {},
    title: props.titles[0],
    type: "create",
    disableSubmit: false,
});

onMounted(() => {
    eventBus.on('openOrderChangeStatusModal', (data) => openChangeStatusModal(data.id));
});

onUnmounted(() => {
    eventBus.off('openOrderChangeStatusModal');
});

const toggleLoading = (isLoading) => store.togglePageLoader(isLoading);
const openChangeStatusModal = async (order_id) => {
    toggleLoading(true);
    try {
        const res = await axiosRequest.get("/admin/orders/" + order_id, {});
        const order = res.data.data;

        state.order = {
            status_id: order?.status?.status_id,
            status_note: order?.status?.note,
        };

        state.order_id = order_id;
        status_ref.value.setValue(order?.status?.name);

        state.title = props.titles[0];
        state.type = "view";
        modalRef.value.openModal();

    } catch (error) {
        console.log(error);
    } finally {
        toggleLoading(false);
    }

};

const handleSubmit = async () => {

    state.disableSubmit = true;

    await changeStatus();

    state.disableSubmit = false;
}

const changeStatus = async () => {

    let url = "/admin/orders/" + state.order_id + "/change-status";
    let formData = createFormData(state.order);

    try {
        const res = await axiosRequest.post(url, formData, {notify: true});
        eventBus.emit('reloadOrderDatatable');
        modalRef.value.closeModal();
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
}

const resetOrderModal = () => {
    // Reset the form data or perform any other cleanup actions here
    state.order = {
        status_id: '',
        status_note: '',
    };

    status_ref.value?.reset();

    state.order_id = '';
    state.errors = {};
};

const handleOrderStatusDeselected = () => {
    state.order['status_id'] = null;
}
</script>

<template>
    <v-modal
        :title="state.title"
        :onSubmit="handleSubmit"
        size="max-w-md"
        :disableSubmit="state.disableSubmit"
        ref="modalRef"
        @modal:closed="resetOrderModal"
    >
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12">
                <label for="order-status" class="block text-sm font-semibold text-gray-900 mb-2">{{
                        translate('order_status')
                    }}</label>
                <vue-select model="name" ref="status_ref"
                            url="/admin/statuses/search?status_type=order"
                            :placeholder="translate('select_status')"
                            @option:selected="(data) => state.order.status_id = data.id"
                            @option:deselected="handleOrderStatusDeselected"/>
                <div class="text-red-500 text-sm">{{ state.errors.status_id }}</div>
            </div>
            <div class="col-span-12">
                <label for="status-note" class="block text-sm font-semibold text-gray-900 mb-2">{{
                        translate('note')
                    }}</label>
                <textarea class="form-input" id="status-note"
                          :placeholder="translate('note')"
                          v-model="state.order.status_note"></textarea>
                <div class="text-red-500 text-sm">{{
                        state.errors.status_note
                    }}
                </div>
            </div>
        </div>
    </v-modal>
</template>
