<template>
    <div>
        <div class="panel">
            <div class="flex items-center justify-between mb-5">
                <h5 class="font-semibold text-lg dark:text-white-light">Safe Transaction Report</h5>
                <button type="button" class="btn btn-primary">
                    <svg class="w-5 h-5 ltr:mr-2 rtl:ml-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M15 12L12 12M12 12L9 12M12 12L12 9M12 12L12 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Export Report
                </button>
            </div>

            <div class="mb-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="panel h-full">
                        <div class="flex items-center justify-between mb-5">
                            <h6 class="font-semibold text-base">Total Transactions</h6>
                        </div>
                        <div class="text-primary text-3xl font-bold">0</div>
                        <div class="text-success text-sm">+0% from last month</div>
                    </div>
                    
                    <div class="panel h-full">
                        <div class="flex items-center justify-between mb-5">
                            <h6 class="font-semibold text-base">Total Amount</h6>
                        </div>
                        <div class="text-success text-3xl font-bold">$0.00</div>
                        <div class="text-success text-sm">+0% from last month</div>
                    </div>
                    
                    <div class="panel h-full">
                        <div class="flex items-center justify-between mb-5">
                            <h6 class="font-semibold text-base">Average Transaction</h6>
                        </div>
                        <div class="text-info text-3xl font-bold">$0.00</div>
                        <div class="text-success text-sm">+0% from last month</div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center py-4">No safe transaction data available</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useMeta } from '@/composables/use-meta';

useMeta({ title: 'Safe Transaction Report' });

const transactionData = ref([]);
const loading = ref(false);
</script>