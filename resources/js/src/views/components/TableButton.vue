
<script setup>

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
    icon: {
        type: String,
        default: '',
    },
    text: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        default: '',
    },
});

</script>
<template>
    <button
        v-tippy="{ content: props.title }"
        :class="[
      `w-7 h-7 border border-${props.color} text-${props.color} hover:bg-${props.color} hover:text-white rounded-md transition-colors duration-200`,
      props.class
    ]"
    >
        {{ props.text }}
        <i v-if="props.icon" class="text-xs" :class="props.icon"></i>
    </button>
</template>
