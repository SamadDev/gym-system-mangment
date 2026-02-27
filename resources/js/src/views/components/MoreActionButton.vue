<script setup>
import {useAppStore} from "../../stores";
import IconHorizontalDots from "../../components/icon/icon-horizontal-dots.vue";

const store = useAppStore();

</script>

<template>
    <div class="dropdown contents">
        <Popper :placement="store.rtlClass === 'rtl' ? 'bottom-start' : 'bottom-end'" offsetDistance="0"
                class="align-middle">
            <button class="mt-1 w-7 h-7 border border-slate-700 text-slate-700 hover:bg-slate-700 hover:text-white rounded-md transition-colors duration-200 -rotate-90">
                <icon-horizontal-dots class="opacity-70 m-auto"/>
            </button>
            <template #content="{ close }">
                <ul @click="close()">
                    <slot></slot>
                </ul>
            </template>
        </Popper>
    </div>
</template>

