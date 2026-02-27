<script setup>
import TranslationsTable from "./TranslationsTable.vue";
import TranslationModal from "./TranslationModal.vue";
import AddButton from "../components/AddButton.vue";
import eventBus from "../../eventBus.js";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('translations'), url: null}
];

</script>
<template>
    <!-- Translation modal -->
    <translation-modal
        v-if="can('add_translation')"
        :titles="[translate('add_translation'), translate('edit_translation')]"
    />
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_translation')" @click="() => eventBus.emit('openTranslationCreateModal', )"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Translations table -->
        <div class="mt-2">
            <translations-table/>
        </div>

    </div>
</template>
