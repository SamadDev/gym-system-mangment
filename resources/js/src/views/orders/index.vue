<script setup>
import OrdersTable from "./OrdersTable.vue";
import ChangeOrderStatusModal from "./ChangeOrderStatusModal.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('orders'), url: null}
];

</script>
<template>
    <change-order-status-modal v-if="can('edit_order')" :titles="[translate('status')]"/>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>
            <add-button href="/admin/orders/create" v-if="can('add_order')"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Orders table -->
        <div class="mt-2">
            <orders-table/>
        </div>

    </div>
</template>
