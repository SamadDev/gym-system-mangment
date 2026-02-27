<script setup>
import { ref, onMounted } from 'vue';
import { useMeta } from '@/composables/use-meta';

useMeta({ title: 'Attendance Report' });

const stats = ref({
    today: 0,
    thisWeek: 0,
    thisMonth: 0,
    avgPerDay: 0
});
const loading = ref(true);

const fetchStats = async () => {
    try {
        // Mock data for now
        stats.value = {
            today: 45,
            thisWeek: 280,
            thisMonth: 1150,
            avgPerDay: 38
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
            <h2 class="text-xl font-bold">Attendance Report</h2>
        </div>

        <div v-if="loading" class="text-center py-10">
            <div class="inline-block animate-spin border-4 border-primary border-t-transparent rounded-full w-10 h-10"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <h5 class="font-semibold text-lg">Today's Check-ins</h5>
                </div>
                <div class="text-3xl font-bold text-primary">{{ stats.today }}</div>
            </div>

            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <h5 class="font-semibold text-lg">This Week</h5>
                </div>
                <div class="text-3xl font-bold text-success">{{ stats.thisWeek }}</div>
            </div>

            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <h5 class="font-semibold text-lg">This Month</h5>
                </div>
                <div class="text-3xl font-bold text-info">{{ stats.thisMonth }}</div>
            </div>

            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <h5 class="font-semibold text-lg">Daily Average</h5>
                </div>
                <div class="text-3xl font-bold text-warning">{{ stats.avgPerDay }}</div>
            </div>
        </div>

        <div class="panel">
            <div class="mb-5">
                <h5 class="font-semibold text-lg">Attendance Trend</h5>
            </div>
            <div class="text-center py-10 text-gray-500">
                Chart placeholder - Daily attendance over time
            </div>
        </div>

        <div class="panel mt-6">
            <div class="mb-5">
                <h5 class="font-semibold text-lg">Peak Hours</h5>
            </div>
            <div class="text-center py-10 text-gray-500">
                Chart placeholder - Busiest hours of the day
            </div>
        </div>
    </div>
</template>
