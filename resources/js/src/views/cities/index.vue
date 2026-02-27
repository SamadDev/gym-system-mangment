<script setup>
import CitiesTable from "./CitiesTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('cities'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_city')" :href="'/admin/cities/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Cities table -->
        <div class="mt-2">
            <cities-table/>
        </div>

    </div>
</template>
