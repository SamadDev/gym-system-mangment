<template>
    <div>
        <!-- Loading -->
        <div v-if="state.loading" class="flex items-center justify-center h-64">
            <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>

        <!-- Error -->
        <div v-else-if="state.error" class="panel text-center text-red-500 py-10">
            {{ state.error }}
        </div>

        <template v-else-if="state.member">
            <!-- Header -->
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <button @click="router.back()" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i> {{ translate('back') }}
                    </button>
                    <h2 class="text-xl font-semibold">{{ state.member.first_name }} {{ state.member.last_name }}</h2>
                </div>
                <div class="flex gap-2">
                    <router-link :to="`/admin/members/${state.member.id}/edit`" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit mr-1"></i> {{ translate('edit') }}
                    </router-link>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Profile Card -->
                <div class="panel flex flex-col items-center text-center p-6">
                    <div class="mb-4">
                        <img v-if="state.member.photo"
                             :src="`/storage/${state.member.photo}`"
                             :alt="state.member.first_name"
                             class="w-28 h-28 rounded-full object-cover ring-4 ring-primary/20"/>
                        <div v-else class="w-28 h-28 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center ring-4 ring-primary/20">
                            <i class="fas fa-user text-4xl text-gray-400"></i>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold">{{ state.member.first_name }} {{ state.member.last_name }}</h3>
                    <p class="text-gray-500 text-sm mb-2">{{ state.member.member_code }}</p>
                    <span class="badge" :class="state.member.status === 'active' ? 'badge-outline-success' : 'badge-outline-danger'">
                        {{ state.member.status }}
                    </span>

                    <div class="w-full mt-5 space-y-3 text-left text-sm">
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-envelope w-4 text-primary"></i>
                            <span>{{ state.member.email || '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-phone w-4 text-primary"></i>
                            <span>{{ state.member.phone || '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-map-marker-alt w-4 text-primary"></i>
                            <span>{{ state.member.address || '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-birthday-cake w-4 text-primary"></i>
                            <span>{{ state.member.date_of_birth ? state.member.date_of_birth.slice(0,10) : '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-venus-mars w-4 text-primary"></i>
                            <span class="capitalize">{{ state.member.gender || '—' }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                            <i class="fas fa-tint w-4 text-primary"></i>
                            <span>{{ state.member.blood_type || '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="lg:col-span-2 space-y-5">
                    <!-- Current Membership -->
                    <div class="panel">
                        <h4 class="text-base font-semibold mb-4 flex items-center gap-2">
                            <i class="fas fa-id-card text-primary"></i> {{ translate('membership') }}
                        </h4>
                        <div v-if="state.member.current_membership" class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="text-gray-400">{{ translate('plan') }}</p>
                                <p class="font-semibold">{{ state.member.current_membership.membership_plan?.name || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400">{{ translate('start_date') }}</p>
                                <p class="font-semibold">{{ state.member.current_membership.start_date?.slice(0,10) || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400">{{ translate('end_date') }}</p>
                                <p class="font-semibold">{{ state.member.current_membership.end_date?.slice(0,10) || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400">{{ translate('status') }}</p>
                                <span class="badge badge-outline-success">{{ state.member.current_membership.status }}</span>
                            </div>
                        </div>
                        <p v-else class="text-gray-400 text-sm">{{ translate('no_active_membership') || 'No active membership' }}</p>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="panel">
                        <h4 class="text-base font-semibold mb-4 flex items-center gap-2">
                            <i class="fas fa-ambulance text-primary"></i> Emergency Contact
                        </h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-400">Name</p>
                                <p class="font-semibold">{{ state.member.emergency_contact || '—' }}</p>
                            </div>
                            <div>
                                <p class="text-gray-400">{{ translate('phone') }}</p>
                                <p class="font-semibold">{{ state.member.emergency_phone || '—' }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useMeta } from '@/composables/use-meta';
import { axiosRequest } from '@/utils/apiRequest';
import { translate } from '@/utils/functions';

useMeta({ title: 'Member Details' });

const route = useRoute();
const router = useRouter();

const state = reactive({
    member: null,
    loading: true,
    error: null,
});

onMounted(async () => {
    try {
        const res = await axiosRequest.get(`/admin/members/${route.params.id}`);
        state.member = res.data.data;
    } catch (e) {
        state.error = 'Failed to load member details.';
    } finally {
        state.loading = false;
    }
});
</script>
