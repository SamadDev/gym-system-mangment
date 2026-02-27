<script setup>
import {defineProps, computed} from 'vue';
import {useRouter} from "vue-router";

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
    text: {
        type: String,
        default: 'Add',
    },
    href: {
        type: String,
        default: '',
    },
});

const router = useRouter();

const buttonClass = computed(() => {
    return 'btn btn-dark px-3 py-1' + props.class;
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
        <i class="fa fa-plus" aria-hidden="true"></i> {{ props.text }}
    </button>
</template>
