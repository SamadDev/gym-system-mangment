<script setup>
import {defineProps, computed} from 'vue';
import {useRouter} from "vue-router";
import {translate} from "../../utils/functions.js";
import IconEdit from "../../components/icon/icon-edit.vue";

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
    text: {
        type: String,
        default: translate('edit'),
    },
    href: {
        type: String,
        default: '',
    },
});

const router = useRouter();

const buttonClass = computed(() => {
    return 'btn btn-primary flex items-center gap-2' + props.class;
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
        <IconEdit class="w-4 h-4" /> {{ props.text }}
    </button>
</template>
