<script setup>
import RolesTable from "./RolesTable.vue";
import AddButton from "../../../components/AddButton.vue";
import {can, translate} from "../../../../utils/functions.js";
import Permissions from "./Permissions.vue";
import Breadcrumb from "../../../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('roles'), url: null}
];

</script>
<template>
    <!-- Permissions modal -->
    <permissions/>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_role')" :href="'/admin/roles/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Roles table -->
        <div class="mt-2">
            <roles-table/>
        </div>

    </div>
</template>
