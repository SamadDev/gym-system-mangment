<script setup>
import InvoicesTable from "./InvoicesTable.vue";
import AddButton from "../../components/AddButton.vue";
import {can, translate} from "../../../utils/functions.js";
import ChangeInvoiceStatusModal from "./ChangeInvoiceStatusModal.vue";
import Breadcrumb from "../../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('invoices'), url: null}
];

</script>
<template>
    <!-- Change status modal -->
    <change-invoice-status-modal
        v-if="can('change_status_invoice')"
        :titles="[translate('status')]"
    />

    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_invoice')" href="/admin/invoices/create"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Invoices table -->
        <div class="mt-2">
            <invoices-table/>
        </div>

    </div>
</template>
