<script setup>
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import {defineProps, reactive, defineEmits, ref, watch, computed} from 'vue';
import {axiosRequest} from "../../utils/apiRequest.js";
import {debounce} from "../../utils/functions.js";

const emit = defineEmits(['option:deselected']);
const props = defineProps({
    modelValue: {
        type: [String, Number, Object],
        default: null,
    },
    class: {
        type: String,
        default: '',
    },
    url: {
        type: String,
        default: '',
        required: true,
    },
    label: {
        type: String,
        default: 'name',
    },
    model: {
        type: String,
        default: null,
        required: true,
    },
    placeholder: {
        type: String,
        default: 'Please Select Item',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const state = reactive({
    value: props.modelValue,
    options: [],
    selected_option: null,
});

// Expose the state to the parent component
const exposedState = ref(state);

// Debounce the fetchData function
const fetchData = async (params) => {
    try {
        const res = await axiosRequest.get(props.url, {params});
        state.options = res.data.data;
    } catch (error) {
        console.log(error);
    }
};

// Debounce the fetchData function
const debouncedFetchData = debounce((query) => {
    fetchData({query});
}, 300);

const onSearch = (query) => {
    debouncedFetchData(query);
};

const onInput = () => {
    if (!state.value || state.value == '' || state.value == null) {
        state.selected_option = null;
        emit('option:deselected');
    }
}

// Watch for changes in the modelValue prop
watch(() => props.modelValue, (newValue) => {
    state.value = newValue;
});

// Expose methods to the parent component
const reset = () => {
    state.value = null;
    state.options = [];
    state.selected_option = null;
};

const refresh = () => {
    fetchData();
};

const getValue = () => {
    return state.value;
};

const setValue = (value, option = null) => {
    state.value = value;
    if (option){
        state.selected_option = option;
    }
};

// Expose the component's public interface
defineExpose({
    state: exposedState,
    reset,
    refresh,
    fetchData,
    getValue,
    setValue
});

const handleOptionSelected = (option) => {
    state.selected_option = option;
}

const vueSelectClass = computed(() => {
    return 'form-input-vue-select ' + props.class;
});

</script>

<template>
    <v-select :disabled="props.disabled" :label="props.label" :options="state.options" :class="vueSelectClass"
              :reduce="item => item[props.model]" @open="fetchData" v-model="state.value" @update:modelValue="onInput"
              @search="onSearch" :placeholder="props.placeholder" @option:selected="handleOptionSelected"
    />
</template>

<style scoped>

.form-input-vue-select.form-input-sm-vue-select ::v-deep(.vs__dropdown-toggle) {
    @apply border border-gray-300 rounded-md text-sm h-8 px-2 py-1;
}

.form-input-sm-vue-select ::v-deep(.vs__dropdown-toggle) {
    @apply text-sm h-8 px-2 py-1;
}

::v-deep(.vs__selected-options) {
    padding: 0;
    margin: 0;
}

::v-deep(.vs__selected) {
    padding: 0;
    margin: 0;
}

.form-input-vue-select ::v-deep(.vs__search) {
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.form-input-sm-vue-select ::v-deep(.vs__search) {
    padding: 0;
    margin: 0;
    font-size: 0.75rem;
    line-height: 1rem;
}

</style>
