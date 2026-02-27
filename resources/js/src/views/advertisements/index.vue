<script setup>
import AdvertisementsTable from "./AdvertisementsTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('advertisements'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_advertisement')" :href="'/admin/advertisements/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Advertisements table -->
        <div class="mt-2">
            <advertisements-table/>
        </div>

    </div>
</template>
