<script setup>
import {defineProps} from 'vue';
import {translate} from "../../utils/functions.js";

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
});

</script>
<template>
    <button
        v-tippy="{ content: translate('delete') }"
        :class="[
      'w-7 h-7 border border-red-500 text-red-500 hover:bg-red-500 hover:text-white rounded-md transition-colors duration-200',
      props.class
    ]"
    >
        <i class="fas fa-trash text-xs"></i>
    </button>
</template>



