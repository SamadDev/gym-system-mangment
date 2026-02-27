<script setup>
import {defineProps} from 'vue';
import {RouterLink} from 'vue-router';
import IconHome from '../../components/icon/icon-home.vue';

const props = defineProps({
    items: {
        type: Array,
        default: [],
    },
});
</script>

<template>
    <div class="breadcrumb-container">
        <ol class="flex text-gray-500 font-semibold dark:text-white-dark">
            <!-- Home icon -->
            <li>
                <router-link to="/" class="hover:text-gray-500/70 dark:hover:text-white-dark/70">
                    <icon-home class="w-4 h-4"/>
                </router-link>
            </li>

            <!-- Dynamic breadcrumb items -->
            <li
                v-for="(item, index) in items"
                :key="index"
                class="flex items-center before:content-['/'] before:px-1.5"
            >
                <router-link
                    :to="item.url || '#'"
                    :class="[
            index === items.length - 1
              ? 'text-black dark:text-white-light hover:text-black/70 dark:hover:text-white-light/70'
              : 'hover:text-gray-500/70 dark:hover:text-white-dark/70'
          ]"
                >
                    {{ item.label }}
                </router-link>
            </li>
        </ol>
    </div>
</template>

<style >
@media print {
    div:has(> .breadcrumb-container) {
        display: none;;
    }
}
</style>
