<script setup>
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
        v-tippy="{ content: translate('edit') }"
        :class="[
      'w-7 h-7 border border-violet-600 text-violet-600 hover:bg-violet-600 hover:text-white rounded-md transition-colors duration-200',
      props.class
    ]"
    >
        <i class="fas fa-pencil text-xs"></i>
    </button>
</template>






