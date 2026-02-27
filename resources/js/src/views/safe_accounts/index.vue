<script setup>
import SafeAccountsTable from "./SafeAccountsTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('safe_accounts'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_safe_account')" :href="'/admin/safe-accounts/create'" :text="translate('add')" class="me-2"/>
        </div>

        <!-- Safe accounts table -->
        <div class="mt-2">
            <safe-accounts-table/>
        </div>

    </div>
</template>
