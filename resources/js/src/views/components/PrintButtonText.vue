<script setup>
import {defineProps, computed} from 'vue';
import {useRouter} from "vue-router";
import {translate} from "../../utils/functions.js";
import IconPrinter from "../../components/icon/icon-printer.vue";

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
    text: {
        type: String,
        default: translate('print'),
    },
    href: {
        type: String,
        default: '',
    },
});

const router = useRouter();

const buttonClass = computed(() => {
    return 'btn btn-outline-primary flex items-center gap-2' + props.class;
});

const handleClick = (e) => {

    e.preventDefault();

    if (props.href) {
        if (props.href.startsWith('/')) {
            router.push(props.href);
        }
    }
}

</script>
<template>
    <button @click="handleClick" :class="buttonClass">
        <IconPrinter class="w-4 h-4" /> {{ props.text }}
    </button>
</template>
