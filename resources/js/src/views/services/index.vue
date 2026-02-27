<script setup>
import ServicesTable from "./ServicesTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('services'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_service')" :href="'/admin/services/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Services table -->
        <div class="mt-2">
            <services-table/>
        </div>

    </div>
</template>
