<script setup>
import { ref, onMounted } from 'vue';
import { useMeta } from '@/composables/use-meta';

useMeta({ title: 'Equipment' });

const equipment = ref([]);
const loading = ref(true);

const fetchEquipment = async () => {
    try {
        const response = await fetch('/admin/equipment/data');
        const data = await response.json();
        equipment.value = data.data || [];
    } catch (error) {
        console.error('Error fetching equipment:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchEquipment();
});
</script>

<template>
    <div>
        <div class="panel">
            <div class="flex items-center justify-between mb-5">
                <h5 class="font-semibold text-lg dark:text-white-light">Equipment Management</h5>
                <button type="button" class="btn btn-primary">
                    <svg class="w-5 h-5 ltr:mr-2 rtl:ml-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M15 12L12 12M12 12L9 12M12 12L12 9M12 12L12 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Add Equipment
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
                            <th>Category</th>
                            <th>Serial Number</th>
                            <th>Condition</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in equipment" :key="item.id">
                            <td>{{ item.name }}</td>
                            <td>{{ item.category }}</td>
                            <td>{{ item.serial_number }}</td>
                            <td>
                                <span class="badge" :class="item.condition === 'good' ? 'badge-outline-success' : item.condition === 'fair' ? 'badge-outline-warning' : 'badge-outline-danger'">
                                    {{ item.condition }}
                                </span>
                            </td>
                            <td>{{ item.location }}</td>
                            <td>
                                <span class="badge" :class="item.is_active ? 'badge-outline-success' : 'badge-outline-danger'">
                                    {{ item.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary">View</button>
                                <button type="button" class="btn btn-sm btn-outline-warning">Maintain</button>
                            </td>
                        </tr>
                        <tr v-if="equipment.length === 0">
                            <td colspan="7" class="text-center py-4">No equipment found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
