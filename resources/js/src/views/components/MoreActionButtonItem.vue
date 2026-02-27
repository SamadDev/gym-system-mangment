<script setup>
import { defineProps } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
    class: { type: String, default: '' },
    icon: { type: String, default: '' },
    text: { type: String, default: '' },
    href: { type: String, default: '#' },
    target: { type: String, default: '_self' },
});

const router = useRouter();

function handleClick(e) {
    // Internal route (starts with "/")
    if (props.href.startsWith('/')) {
        e.preventDefault();

        if (props.target === '_self') {
            // normal SPA navigation
            router.push(props.href);
        } else if (props.target === '_blank') {
            // open in new tab using router.resolve
            const route = router.resolve(props.href);
            window.open(route.href, '_blank');
        } else {
            // fallback: other targets (_parent, _top…)
            const route = router.resolve(props.href);
            window.open(route.href, props.target);
        }
    }
}
</script>

<template>
    <li>
        <router-link
            class="dropdown-item d-flex align-items-center"
            :to="href"
            :target="target"
            :class="props.class"
            @click="handleClick"
        >
            <i :class="icon"></i>
            {{ text }}
        </router-link>
    </li>
</template>
