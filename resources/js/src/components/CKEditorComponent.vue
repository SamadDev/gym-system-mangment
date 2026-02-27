<template>
    <div class="ckeditor-wrapper">
        <Ckeditor :editor="editor" v-model="content" :config="editorConfig"></Ckeditor>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const editor = ClassicEditor;
const content = ref(props.modelValue);

const editorConfig = {
    licenseKey: 'GPL',
    toolbar: [
        'heading',
        '|',
        'bold',
        'italic',
        '|',
        'link',
        'bulletedList',
        'numberedList',
        '|',
        'blockQuote',
        '|',
        'undo',
        'redo'
    ],
    language: 'en'
};

watch(content, (newValue) => {
    emit('update:modelValue', newValue);
});

watch(() => props.modelValue, (newValue) => {
    if (content.value !== newValue) {
        content.value = newValue;
    }
});
</script>

<style>
.ckeditor-wrapper {
    border: 1px solid #e0e6ed;
    border-radius: 0.375rem;
    overflow: hidden;
}

.ckeditor-wrapper :deep(.ck-editor__editable) {
    min-height: 300px;
    max-height: 500px;
}

.ckeditor-wrapper :deep(.ck-toolbar) {
    background: #f1f2f3;
    border-bottom: 1px solid #e0e6ed;
}

/* Dark mode */
:global(.dark) .ckeditor-wrapper {
    border-color: #191e3a;
}

:global(.dark) .ckeditor-wrapper :deep(.ck-toolbar) {
    background: #0e1726;
    border-bottom-color: #191e3a;
}

:global(.dark) .ckeditor-wrapper :deep(.ck-editor__editable) {
    background: #0e1726;
    color: #e0e6ed;
}

:global(.dark) .ckeditor-wrapper :deep(.ck) {
    color: #e0e6ed;
}
</style>
