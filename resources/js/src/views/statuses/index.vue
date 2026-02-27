<script setup>
import StatusesTable from "./StatusesTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('warehouses'), url: '/admin/warehouses'},
    {label: translate('statuses'), url: null}
];

</script>
<template>
    <div>
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>
            <add-button v-if="can('add_status')" :href="'/admin/statuses/create'" :text="translate('add')" class="me-2"/>
        </div>
        <div class="mt-2">
            <statuses-table/>
        </div>
    </div>
</template>
