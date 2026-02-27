<script setup>
import UsersTable from "./UsersTable.vue";
import AddButton from "../../../components/AddButton.vue";
import {can, translate} from "../../../../utils/functions.js";
import Breadcrumb from "../../../components/Breadcrumb.vue";

const breadcrumbItems = [
    {label: translate('users'), url: null}
];

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_user')" :href="'/admin/users/create'" :text="translate('add')" class="me-2"/>
        </div>

        <!-- Users table -->
        <div class="mt-2">
            <users-table/>
        </div>

    </div>
</template>
