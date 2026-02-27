<script setup>
import {defineProps, computed} from 'vue';
import {translate} from "../../utils/functions.js";
import {useRouter} from "vue-router";

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
    text: {
        type: String,
        default: translate('cancel'),
    },
    type: {
        type: String,
        default: 'button',
    },
    href: {
        type: String,
        default: '',
    },
});

const router = useRouter();

const handleClick = (e) => {

    e.preventDefault();

    if (props.href) {
        if (props.href.startsWith('/')) {
            router.push(props.href);
        }
    }
}

const buttonClass = computed(() => {
    return 'btn btn-danger px-4 py-2' + props.class;
});

</script>
<template>
    <button
        @click="handleClick"
        :type="props.type"
        :class="buttonClass">
        {{ props.text }}
    </button>
</template>
