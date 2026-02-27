<script setup>

import {ref, defineEmits} from "vue";
import {translate} from "../../../utils/functions.js";
import IconX from "../../../components/icon/icon-x.vue";

const emit = defineEmits(['filter:applied']);
const isFilterOpen = ref(false);

const openFilter = () => {
    isFilterOpen.value = true;
};

const closeFilter = () => {
    isFilterOpen.value = false;
};

const applyFilters = () => {
    emit('filter:applied');
};

// Expose the component's public interface
defineExpose({
    openFilter,
    closeFilter
});

</script>
<template>
    <div>
        <div
            class="fixed inset-0 bg-[black]/60 z-[60] px-4 hidden transition-[display]"
            :class="{ '!block': isFilterOpen }"
            @click="closeFilter"
        ></div>

        <nav
            class="bg-white fixed ltr:-right-[420px] rtl:-left-[420px] top-0 bottom-0 w-full max-w-[420px] shadow-[5px_0_25px_0_rgba(94,92,154,0.1)] transition-[right] duration-300 z-[61] dark:bg-[#0e1726] flex flex-col"
            :class="{ 'ltr:!right-0 rtl:!left-0': isFilterOpen }"
        >
            <div class="p-4 border-b border-gray-200 dark:border-[#253b5c] flex items-center justify-between">
                <h3 class="font-semibold text-gray-700 dark:text-white">Filters</h3>
                <div class="text-center relative pb-5">
                    <a
                        href="javascript:;"
                        class="absolute top-0 ltr:right-0 rtl:left-0 opacity-30 hover:opacity-100 dark:text-white"
                        @click="closeFilter"
                    >
                        <icon-x class="w-5 h-5"/>
                    </a>
                </div>
            </div>
            <div class="p-4 overflow-y-auto grow ltr:pr-3 rtl:pl-3 ltr:-mr-3 rtl:-ml-3">
                <slot></slot>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-[#253b5c] flex items-center gap-2">
                <button @click="applyFilters" class="btn btn-primary ltr:ml-auto rtl:mr-auto">Apply</button>
            </div>
        </nav>
    </div>
</template>
