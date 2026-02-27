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
        v-tippy="{ content: translate('permissions') }"
        :class="[
      'w-7 h-7 border border-green-500 text-green-500 hover:bg-green-500 hover:text-white rounded-md transition-colors duration-200',
      props.class
    ]"
    >
        <i class="fas fa-unlock text-xs"></i>
    </button>
</template>



