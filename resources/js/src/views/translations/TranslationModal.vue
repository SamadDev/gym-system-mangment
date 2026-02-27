<script setup>
import {reactive, onMounted, onUnmounted, ref} from 'vue';
import VModal from '../components/VModal.vue';
import eventBus from '../../eventBus.js';
import {translate} from '../../utils/functions.js';
import {axiosRequest} from "../../utils/apiRequest.js";

const props = defineProps({
    titles: {
        type: Array,
        default: null,
        required: true,
    },
});

const state = reactive({
    translation: {
        keyword: '',
    },
    translation_id: '',
    errors: {},
    title: props.titles[0],
    type: "create",
    disableSubmit: false,
    rules: {
        keyword: 'required|string',
    },
});

const modalRef = ref(null);

onMounted(() => {
    eventBus.on('openTranslationCreateModal', openCreateModal);
});

onUnmounted(() => {
    eventBus.off('openTranslationCreateModal');
});

const openCreateModal = () => {
    state.title = props.titles[0];
    state.type = "create";
    modalRef.value.openModal();
};

const handleSubmit = async () => {

    state.disableSubmit = true;

        await createTranslation();

    state.disableSubmit = false;
}

const createTranslation = async () => {

    let url = "/admin/translations/create";
    let data = state.translation;
    try {
        const res = await axiosRequest.post(url, data, {notify: true});
        eventBus.emit('reloadTranslationDatatable');
        modalRef.value.closeModal();
    } catch (error) {
        if (error.response.status == 422) {
            state.errors = error.response.data.errors;
        }
    }
};

const resetTranslationModal = () => {
    // Reset the form data or perform any other cleanup actions here
    state.translation = {
        keyword: '',
    };
    state.translation_id = '';
    state.errors = {};
};

</script>

<template>
    <v-modal
        :title="state.title"
        :onSubmit="handleSubmit"
        :disableSubmit="state.disableSubmit"
        ref="modalRef"
        @modal:closed="resetTranslationModal"
    >
        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12">
                <label for="translation-keyword">{{ translate('keyword') }}</label>
                <input type="text" class="form-input" id="translation-keyword" :placeholder="translate('keyword')" v-model="state.translation.keyword">
                <div class="text-red-500 text-sm">{{ state.errors.keyword }}</div>
            </div>
        </div>
    </v-modal>
</template>

<style scoped>
</style>
