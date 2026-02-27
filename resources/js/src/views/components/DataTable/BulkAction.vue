<script setup>
import {useAppStore} from "../../../stores";

const store = useAppStore();

const props = defineProps({
    selectedCount: {
        type: Number,
        default: '',
    },
});


</script>
<template>
    <div class="dropdown">
        <Popper :placement="store.rtlClass === 'rtl' ? 'bottom-end' : 'bottom-start'" offsetDistance="0"
                class="align-middle">
            <button
                type="button"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium
         border border-indigo-500 text-indigo-600 hover:bg-indigo-50
         dark:border-indigo-400 dark:text-indigo-300 dark:hover:bg-indigo-900/30
         transition-all duration-150 shadow-sm"
            >
                <i class="fa-solid fa-layer-group text-base"></i>
                <span
                    class="inline-flex items-center justify-center bg-indigo-500 text-white
           text-xs font-bold rounded-full w-5 h-5"
                >{{ props.selectedCount }}</span>
                <i class="fa-solid fa-caret-down text-[10px] opacity-80"></i>
            </button>


            <template #content>
                <ul class="whitespace-nowrap">
                    <slot></slot>
                </ul>
            </template>
        </Popper>
    </div>
</template>
