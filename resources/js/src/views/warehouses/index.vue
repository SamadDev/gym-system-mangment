<script setup>
import WarehousesTable from "./WarehousesTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('warehouses'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_warehouse')" :href="'/admin/warehouses/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Warehouses table -->
        <div class="mt-2">
            <warehouses-table/>
        </div>

    </div>
</template>
