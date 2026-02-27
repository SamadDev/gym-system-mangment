<script setup>
import PaymentsTable from "./PaymentsTable.vue";
import AddButton from "../../components/AddButton.vue";
import {can, translate} from "../../../utils/functions.js";
import ChangePaymentStatusModal from "./ChangePaymentStatusModal.vue";
import Breadcrumb from "../../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('payments'), url: null}
];

</script>
<template>
    <!-- Change status modal -->
    <change-payment-status-modal
        v-if="can('change_status_payment')"
        :titles="[translate('status')]"
    />
    
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_payment')" :href="'/admin/payments/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Payments table -->
        <div class="mt-2">
            <payments-table/>
        </div>

    </div>
</template>
