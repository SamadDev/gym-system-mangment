<script setup>
import BranchesTable from "./BranchesTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('branches'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_branch')" :href="'/admin/branches/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Branches table -->
        <div class="mt-2">
            <branches-table/>
        </div>

    </div>
</template>
