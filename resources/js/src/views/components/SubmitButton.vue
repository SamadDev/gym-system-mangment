<script setup>
import {defineProps, computed} from 'vue';
import {translate} from "../../utils/functions.js";

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
    text: {
        type: String,
        default: translate('save'),
    },
    type: {
        type: String,
        default: 'submit',
    },
});

const buttonClass = computed(() => {
    return 'btn btn-primary ltr:ml-4 rtl:mr-4 px-4 py-2' + props.class;
});

</script>
<template>
    <button
        :type="props.type"
        :class="buttonClass">
        <i class="fas fa-paper-plane me-2"></i>
        {{ props.text }}
    </button>
</template>
