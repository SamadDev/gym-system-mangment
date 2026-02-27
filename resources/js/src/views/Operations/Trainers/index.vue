<script setup>
import { ref, onMounted } from 'vue';
import { useMeta } from '@/composables/use-meta';

useMeta({ title: 'Trainers' });

const trainers = ref([]);
const loading = ref(true);

const fetchTrainers = async () => {
    try {
        const response = await fetch('/admin/trainers/data');
        const data = await response.json();
        trainers.value = data.data || [];
    } catch (error) {
        console.error('Error fetching trainers:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchTrainers();
});
</script>

<template>
    <div>
        <div class="panel">
            <div class="flex items-center justify-between mb-5">
                <h5 class="font-semibold text-lg dark:text-white-light">Trainers Management</h5>
                <button type="button" class="btn btn-primary">
                    <svg class="w-5 h-5 ltr:mr-2 rtl:ml-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M15 12L12 12M12 12L9 12M12 12L12 9M12 12L12 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Add Trainer
                </button>
            </div>

            <div v-if="loading" class="text-center py-10">
                <div class="inline-block animate-spin border-4 border-primary border-t-transparent rounded-full w-10 h-10"></div>
            </div>

            <div v-else class="table-responsive">
                <table class="table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Specialization</th>
                            <th>Gender</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="trainer in trainers" :key="trainer.id">
                            <td>{{ trainer.full_name }}</td>
                            <td>{{ trainer.email }}</td>
                            <td>{{ trainer.phone }}</td>
                            <td>{{ trainer.specialization }}</td>
                            <td>{{ trainer.gender }}</td>
                            <td>
                                <span class="badge" :class="trainer.is_active ? 'badge-outline-success' : 'badge-outline-danger'">
                                    {{ trainer.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary">View</button>
                                <button type="button" class="btn btn-sm btn-outline-warning">Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="trainers.length === 0">
                            <td colspan="7" class="text-center py-4">No trainers found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
