<script setup>
import IconCaretDown from '../../../components/icon/icon-caret-down.vue';
import {useAppStore} from "../../../stores";

const props = defineProps({
    columns: {
        type: Array,
        required: true,
    },
});

const store = useAppStore();

</script>
<template>
    <div class="dropdown">
        <Popper :placement="store.rtlClass === 'rtl' ? 'bottom-end' : 'bottom-start'" offsetDistance="0"
                class="align-middle">
            <button
                type="button"
                class="flex items-center border font-semibold border-[#e0e6ed] dark:border-[#253b5c] rounded-md px-3 py-1 text-sm dark:bg-[#1b2e4b] dark:text-white-dark"
            >
                <span class="ltr:mr-1 rtl:ml-1">Columns</span>
                <icon-caret-down class="w-5 h-5"/>
            </button>
            <template #content>
                <ul class="whitespace-nowrap">
                    <template v-for="(col, i) in props.columns" :key="i">
                        <li>
                            <div class="flex items-center px-4 py-1">
                                <label class="cursor-pointer mb-0">
                                    <input
                                        type="checkbox"
                                        class="form-checkbox"
                                        :id="`chk-${i}`"
                                        :value="col.field"
                                        @change="col.hide = !col.hide"
                                        :checked="!col.hide"
                                    />
                                    <span :for="`chk-${i}`" class="ltr:ml-2 rtl:mr-2">{{ col.title }}</span>
                                </label>
                            </div>
                        </li>
                    </template>
                </ul>
            </template>
        </Popper>
    </div>
</template>
