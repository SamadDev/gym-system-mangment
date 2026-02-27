<script setup>
import SafeTransactionCategoriesTable from "./SafeTransactionCategoriesTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('safe_transaction_categories'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_safe_transaction_category')" :href="'/admin/safe-transaction-categories/create'" :text="translate('add')" class="me-2"/>
        </div>

        <!-- Safe transaction categories table -->
        <div class="mt-2">
            <safe-transaction-categories-table/>
        </div>

    </div>
</template>
