<script setup>
import SafeTransactionTable from "./SafeTransactionTable.vue";
import AddButton from "../components/AddButton.vue";
import {can, translate} from "../../utils/functions.js";
import {onMounted, ref} from "vue";
import {axiosRequest} from "../../utils/apiRequest.js";
import Breadcrumb from "../components/Breadcrumb.vue";

const safes = ref([]);

const breadcrumbItems = [
    {label: translate('safe_transactions'), url: null}
];

onMounted(() => {
    fetchSafesSummary();
});

const fetchSafesSummary = async () => {
    const res = await axiosRequest.get("/admin/safe-transactions/safes-summary", {});
    safes.value = res.data.data;
}

</script>
<template>
    <!-- Main container -->
    <div>

        <!-- Top row: Add button -->
        <div class="flex items-center justify-between mb-2">
            <Breadcrumb :items="breadcrumbItems"/>

            <add-button v-if="can('add_safe_transaction')"
                        :href="'/admin/safe-transactions/create'"
                        :text="translate('add')" class="me-2"/>
        </div>

        <!-- Safes summary cards -->
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="panel h-full" v-for="(safe, index) in safes" :key="index">
                <div class="flex items-center justify-between dark:text-white-light mb-4">
                    <h5 class="font-semibold text-lg flex items-center">
                        <i class="fas fa-vault text-warning mr-2"></i>
                        {{ safe.name }}
                    </h5>
                    <div class="flex items-center">
                        <i class="fas fa-chart-line text-info"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold my-2" :class="safe.balance > 0 ? 'text-success' : 'text-danger'">
                    <span class="ltr:mr-2 rtl:ml-2">{{ safe.formatted_balance }}</span>
                </div>
                <div class="text-sm text-gray-500">
                    <span v-if="safe.balance > 0" class="text-success">
                        <i class="fas fa-arrow-up mr-1"></i>
                        Positive Balance
                    </span>
                    <span v-else class="text-danger">
                        <i class="fas fa-arrow-down mr-1"></i>
                        Negative Balance
                    </span>
                </div>
            </div>
        </div>

        <!-- Safe transactions table -->
        <div class="mt-2">
            <safe-transaction-table/>
        </div>

    </div>
</template>
