<script setup>
import { ref, onMounted } from 'vue';
import { useMeta } from '@/composables/use-meta';

useMeta({ title: 'Classes' });

const classes = ref([]);
const loading = ref(true);

const fetchClasses = async () => {
    try {
        const response = await fetch('/admin/classes/data');
        const data = await response.json();
        classes.value = data.data || [];
    } catch (error) {
        console.error('Error fetching classes:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchClasses();
});
</script>

<template>
    <div>
        <div class="panel">
            <div class="flex items-center justify-between mb-5">
                <h5 class="font-semibold text-lg dark:text-white-light">Classes Management</h5>
                <button type="button" class="btn btn-primary">
                    <svg class="w-5 h-5 ltr:mr-2 rtl:ml-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M15 12L12 12M12 12L9 12M12 12L12 9M12 12L12 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Add Class
                </button>
            </div>

            <div v-if="loading" class="text-center py-10">
                <div class="inline-block animate-spin border-4 border-primary border-t-transparent rounded-full w-10 h-10"></div>
            </div>

            <div v-else class="table-responsive">
                <table class="table-hover">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Trainer</th>
                            <th>Schedule</th>
                            <th>Duration</th>
                            <th>Capacity</th>
                            <th>Enrolled</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="classItem in classes" :key="classItem.id">
                            <td>{{ classItem.name }}</td>
                            <td>{{ classItem.trainer_name }}</td>
                            <td>{{ classItem.schedule }}</td>
                            <td>{{ classItem.duration }} min</td>
                            <td>{{ classItem.capacity }}</td>
                            <td>{{ classItem.enrolled }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary">View</button>
                                <button type="button" class="btn btn-sm btn-outline-warning">Edit</button>
                                <button type="button" class="btn btn-sm btn-outline-danger">Cancel</button>
                            </td>
                        </tr>
                        <tr v-if="classes.length === 0">
                            <td colspan="7" class="text-center py-4">No classes found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
