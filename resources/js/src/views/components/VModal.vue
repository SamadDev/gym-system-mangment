<script setup>
import {defineProps, ref, watch, defineEmits} from 'vue';
import {TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogOverlay} from '@headlessui/vue';
import IconX from '../../components/icon/icon-x.vue';
import SubmitButton from "../components/SubmitButton.vue";
import CancelButton from "../components/CancelButton.vue";

const emit = defineEmits(['modal:closed']);

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
    size: {
        type: String,
        default: 'max-w-5xl',
    },
    onSubmit: {
        type: Function,
        required: false,
    },
    disableSubmit: {
        type: Boolean,
        default: false,
    },
});

const showModal = ref(false);

const openModal = () => {
    showModal.value = true;
}
// Expose the component's public interface
defineExpose({
    openModal,
    closeModal: () => showModal.value = false,
});

// Watch for changes in the modelValue prop
watch(() => showModal.value, (newValue) => {
    if (newValue === false) {
        emit('modal:closed');
    }
});

</script>

<template>
    <!-- Modal -->
    <TransitionRoot :unmount="false" appear :show="showModal" as="template">
        <Dialog :unmount="false" as="div" @close="showModal = false" class="relative z-[51]"
        >
            <TransitionChild
                :unmount="false"
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <DialogOverlay class="fixed inset-0 bg-[black]/60"/>
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-start justify-center px-4 py-8">
                    <TransitionChild
                        :unmount="false"
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            :unmount="false"
                            class="panel border-0 p-0 rounded-lg overflow-hidden w-full text-black dark:text-white-dark"
                            :class="props.size"
                        >
                            <button
                                type="button"
                                class="absolute top-4 ltr:right-4 rtl:left-4 text-gray-400 hover:text-gray-800 dark:hover:text-gray-600 outline-none"
                                @click="showModal = false"
                            >
                                <icon-x/>
                            </button>
                            <div
                                class="text-lg font-bold bg-gray-100 border-b border-gray-300 dark:bg-[#121c2c] ltr:pl-5 rtl:pr-5 py-3 ltr:pr-[50px] rtl:pl-[50px]"
                            >
                                {{ props.title }}
                            </div>
                            <div class="p-5">
                                <form>
                                    <slot></slot>
                                </form>
                            </div>
                            <div
                                class="flex justify-end items-center mt-8 bg-gray-100 border-t border-gray-300 px-3 py-2">
                                <cancel-button @click="showModal = false"/>
                                <submit-button @click.prevent="props.onSubmit" :disabled="props.disableSubmit"/>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>


