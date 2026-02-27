<script setup>
import PaymentMethodsTable from "./PaymentMethodsTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('payment_methods'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_payment_method')" :href="'/admin/payment-methods/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Payment Methods table -->
        <div class="mt-2">
            <payment-methods-table/>
        </div>

    </div>
</template>
