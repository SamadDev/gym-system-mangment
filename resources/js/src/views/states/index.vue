<script setup>
import StatesTable from "./StatesTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('states'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_state')" :href="'/admin/states/create'" :text="translate('add')" class="me-2"/>
        </div>

        <!-- States table -->
        <div class="mt-2">
            <states-table/>
        </div>

    </div>
</template>
