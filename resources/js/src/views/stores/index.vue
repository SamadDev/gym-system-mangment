<script setup>
import StoresTable from "./StoresTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('stores'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_store')" :href="'/admin/stores/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Stores table -->
        <div class="mt-2">
            <stores-table/>
        </div>

    </div>
</template>
