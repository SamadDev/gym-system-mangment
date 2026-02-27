<script setup>
import { translate } from "../../utils/functions.js";

const props = defineProps({
    class: {
        type: String,
        default: "",
    },
});
</script>

<template>
    <button
        v-tippy="{ content: translate('view') }"
        :class="[
      'w-7 h-7 border border-sky-600 text-sky-600 hover:bg-sky-600 hover:text-white rounded-md transition-colors duration-200',
      props.class
    ]"
    >
        <i class="fas fa-eye text-xs"></i>
    </button>
</template>
