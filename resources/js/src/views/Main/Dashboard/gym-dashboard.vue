<script setup>
import { ref, onMounted } from 'vue';
import { useMeta } from '@/composables/use-meta';

useMeta({ title: 'Dashboard' });

const stats = ref({
    total_members: 0,
    active_members: 0,
    total_revenue: 0,
    attendance_today: 0
});
const loading = ref(true);

const fetchStats = async () => {
    try {
        const response = await fetch('/admin/dashboard/data');
        const data = await response.json();
        if (data.data && data.data.stats) {
            stats.value = data.data.stats;
        }
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
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
        <div class="mb-6">
            <h2 class="text-2xl font-bold">Kurdistan Gym Management System</h2>
            <p class="text-gray-600 dark:text-gray-400">Welcome back! Here's your gym overview.</p>
        </div>

        <div v-if="loading" class="text-center py-10">
            <div class="inline-block animate-spin border-4 border-primary border-t-transparent rounded-full w-10 h-10"></div>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Members -->
            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h5 class="font-semibold text-lg">Total Members</h5>
                        <div class="text-3xl font-bold text-primary mt-2">{{ stats.total_members }}</div>
                    </div>
                    <div class="w-16 h-16 bg-primary-light rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-600">Active: {{ stats.active_members }}</p>
            </div>

            <!-- Total Revenue -->
            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h5 class="font-semibold text-lg">Total Revenue</h5>
                        <div class="text-3xl font-bold text-success mt-2">${{ Number(stats.total_revenue || 0).toLocaleString() }}</div>
                    </div>
                    <div class="w-16 h-16 bg-success-light rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-600">This month: ${{ Number(stats.revenue_this_month || 0).toLocaleString() }}</p>
            </div>

            <!-- Today's Attendance -->
            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h5 class="font-semibold text-lg">Today's Attendance</h5>
                        <div class="text-3xl font-bold text-info mt-2">{{ stats.attendance_today }}</div>
                    </div>
                    <div class="w-16 h-16 bg-info-light rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-600">This week: {{ stats.attendance_this_week }}</p>
            </div>

            <!-- Active Memberships -->
            <div class="panel">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h5 class="font-semibold text-lg">Active Memberships</h5>
                        <div class="text-3xl font-bold text-warning mt-2">{{ stats.active_memberships }}</div>
                    </div>
                    <div class="w-16 h-16 bg-warning-light rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                    </div>
                </div>
                <p class="text-sm text-gray-600">Expiring soon: {{ stats.expiring_soon }}</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-6">
            <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <router-link to="/admin/members" class="panel text-center hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-primary-light rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold">Members</h4>
                </router-link>

                <router-link to="/admin/attendance" class="panel text-center hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-success-light rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold">Attendance</h4>
                </router-link>

                <router-link to="/admin/payments" class="panel text-center hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-info-light rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold">Payments</h4>
                </router-link>

                <router-link to="/admin/trainers" class="panel text-center hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-warning-light rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h4 class="font-semibold">Trainers</h4>
                </router-link>
            </div>
        </div>
    </div>
</template>
