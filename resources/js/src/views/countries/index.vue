<script setup>
import CountriesTable from "./CountriesTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('countries'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_country')" :href="'/admin/countries/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Countries table -->
        <div class="mt-2">
            <countries-table/>
        </div>

    </div>
</template>
