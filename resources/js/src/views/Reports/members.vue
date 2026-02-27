<script setup>
import { ref, onMounted } from 'vue';
import { useMeta } from '@/composables/use-meta';

useMeta({ title: 'Members Report' });

const stats = ref({
    total: 0,
    active: 0,
    inactive: 0,
    newThisMonth: 0
});
const loading = ref(true);

const fetchStats = async () => {
    try {
        // Mock data for now
        stats.value = {
            total: 150,
            active: 120,
            inactive: 30,
            newThisMonth: 15
        };
    } catch (error) {
        console.error('Error fetching stats:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchStats();
});
</script>

<template>
    <div>
        <div class="mb-5">
            <h2 class="text-xl font-bold">Members Report</h2>
        </div>

        <div v-if="loading" class="text-center py-10">
            <div class="inline-block animate-spin border-4 border-primary border-t-transparent rounded-full w-10 h-10"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <h5 class="font-semibold text-lg">Total Members</h5>
                </div>
                <div class="text-3xl font-bold text-primary">{{ stats.total }}</div>
            </div>

            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <h5 class="font-semibold text-lg">Active Members</h5>
                </div>
                <div class="text-3xl font-bold text-success">{{ stats.active }}</div>
            </div>

            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <h5 class="font-semibold text-lg">Inactive Members</h5>
                </div>
                <div class="text-3xl font-bold text-danger">{{ stats.inactive }}</div>
            </div>

            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <h5 class="font-semibold text-lg">New This Month</h5>
                </div>
                <div class="text-3xl font-bold text-info">{{ stats.newThisMonth }}</div>
            </div>
        </div>

        <div class="panel">
            <div class="mb-5">
                <h5 class="font-semibold text-lg">Member Growth Chart</h5>
            </div>
            <div class="text-center py-10 text-gray-500">
                Chart placeholder - Member growth over time
            </div>
        </div>
    </div>
</template>
