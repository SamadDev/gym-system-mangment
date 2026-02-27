<template>
    <div>
        <div class="panel">
            <div class="flex items-center justify-between mb-5">
                <h5 class="font-semibold text-lg dark:text-white-light">Membership Plans</h5>
                <button type="button" class="btn btn-primary" @click="addPlan">
                    <svg class="w-5 h-5 ltr:mr-2 rtl:ml-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M15 12L12 12M12 12L9 12M12 12L12 9M12 12L12 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Add Plan
                </button>
            </div>

            <MembershipPlansTable />
        </div>
    </div>
</template>

<script setup>
import { useMeta } from '@/composables/use-meta';
import { useRouter } from 'vue-router';
import MembershipPlansTable from './MembershipPlansTable.vue';

useMeta({ title: 'Membership Plans' });

const router = useRouter();

const addPlan = () => {
    router.push('/admin/membership-plans/create');
};
</script>