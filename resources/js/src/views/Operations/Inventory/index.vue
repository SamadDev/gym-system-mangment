<script setup>
import { ref, onMounted } from 'vue';
import { useMeta } from '@/composables/use-meta';

useMeta({ title: 'Inventory' });

const items = ref([]);
const loading = ref(true);

const fetchInventory = async () => {
    try {
        const response = await fetch('/admin/inventory/data');
        const data = await response.json();
        items.value = data.data || [];
    } catch (error) {
        console.error('Error fetching inventory:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchInventory();
});
</script>

<template>
    <div>
        <div class="panel">
            <div class="flex items-center justify-between mb-5">
                <h5 class="font-semibold text-lg dark:text-white-light">Inventory Management</h5>
                <button type="button" class="btn btn-primary">
                    <svg class="w-5 h-5 ltr:mr-2 rtl:ml-2" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M15 12L12 12M12 12L9 12M12 12L12 9M12 12L12 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    Add Item
                </button>
            </div>

            <div v-if="loading" class="text-center py-10">
                <div class="inline-block animate-spin border-4 border-primary border-t-transparent rounded-full w-10 h-10"></div>
            </div>

            <div v-else class="table-responsive">
                <table class="table-hover">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Stock Quantity</th>
                            <th>Cost Price</th>
                            <th>Selling Price</th>
                            <th>Stock Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in items" :key="item.id">
                            <td>{{ item.name }}</td>
                            <td>{{ item.sku }}</td>
                            <td>{{ item.category }}</td>
                            <td>
                                <span :class="item.stock_status === 'Out of Stock' ? 'text-danger' : item.stock_status === 'Low Stock' ? 'text-warning' : ''">
                                    {{ item.quantity_in_stock }}
                                </span>
                            </td>
                            <td>{{ item.cost_price }}</td>
                            <td>{{ item.selling_price }}</td>
                            <td>
                                <span class="badge" :class="item.stock_status === 'In Stock' ? 'badge-outline-success' : item.stock_status === 'Low Stock' ? 'badge-outline-warning' : 'badge-outline-danger'">
                                    {{ item.stock_status }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary">View</button>
                                <button type="button" class="btn btn-sm btn-outline-success">Restock</button>
                                <button type="button" class="btn btn-sm btn-outline-warning">Edit</button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="8" class="text-center py-4">No inventory items found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
