<script setup>
import {ref, defineEmits, defineProps} from "vue";
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import {useAppStore} from "../../stores";
import {translate} from "../../utils/functions.js";

const props = defineProps({
    dateFormat: {
        type: String,
        default: 'Y-m-d', // Default format
    },
    isRange: {
        type: Boolean,
        default: false,
    },
    modelValue: {
        type: [String, Array], // Support single date or array for range
        default: '',
    },
    placeholder: {
        type: String, // Support single date or array for range
        default: translate('select_date'),
    }
});

const emit = defineEmits(['update:modelValue']);
const store = useAppStore();

const value = ref(Array.isArray(props.modelValue) ? props.modelValue.join(' to ') : props.modelValue);

const rangeCalendar = ref({
    dateFormat: props.dateFormat,
    position: store.rtlClass === 'rtl' ? 'auto right' : 'auto left',
});

if (props.isRange && props.isRange == true) {
    rangeCalendar.value.mode = 'range';
}
const handleUpdate = (e) => {
    const value = e.target.value;
    if (!value) {
        emit('update:modelValue', ""); // Emit empty if cleared
        return;
    }

    const pickerData = value.split(' to ');

    emit('update:modelValue', pickerData.length > 1 ? pickerData : pickerData[0]);

}

</script>
<template>
    <flat-pickr :placeholder="props.placeholder" @change="handleUpdate" v-model="value" class="form-input" :config="rangeCalendar"></flat-pickr>
</template>


