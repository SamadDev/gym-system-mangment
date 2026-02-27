<script setup>
import CustomersTable from "./CustomersTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('customers'), url: null}
];

</script>

<template>
    <!-- Main container -->
    <div>
        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button
                v-if="can('add_customer')"
                :href="'/admin/customers/create'"
                :text="translate('add')"
            />
        </div>

        <!-- Customers table -->
        <div class="mt-2">
            <customers-table/>
        </div>
    </div>
</template>
