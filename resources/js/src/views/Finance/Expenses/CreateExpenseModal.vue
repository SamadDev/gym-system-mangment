<script setup>
import { ref } from 'vue';

const props = defineProps({
    show: Boolean
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
    category: '',
    description: '',
    amount: '',
    expense_date: new Date().toISOString().split('T')[0],
    payment_method: 'cash'
});

const errors = ref({});
const submitting = ref(false);

const handleSubmit = async () => {
    errors.value = {};
    submitting.value = true;

    try {
        const response = await fetch('/admin/expenses', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(form.value)
        });

        const data = await response.json();

        if (response.ok) {
            emit('submit', data.data);
            resetForm();
        } else {
            errors.value = data.errors || {};
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to create expense');
    } finally {
        submitting.value = false;
    }
};

const resetForm = () => {
    form.value = {
        category: '',
        description: '',
        amount: '',
        expense_date: new Date().toISOString().split('T')[0],
        payment_method: 'cash'
    };
    errors.value = {};
};

const handleClose = () => {
    resetForm();
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" @click="handleClose"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full p-6 z-10">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-xl font-semibold">Add Expense</h3>
                    <button @click="handleClose" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Category *</label>
                        <input v-model="form.category" type="text" class="form-input" placeholder="e.g., Utilities, Rent, Equipment" required />
                        <span v-if="errors.category" class="text-danger text-xs">{{ errors.category[0] }}</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Description *</label>
                        <textarea v-model="form.description" class="form-textarea" rows="3" placeholder="Expense details..." required></textarea>
                        <span v-if="errors.description" class="text-danger text-xs">{{ errors.description[0] }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Amount *</label>
                            <input v-model="form.amount" type="number" step="0.01" min="0" class="form-input" placeholder="0.00" required />
                            <span v-if="errors.amount" class="text-danger text-xs">{{ errors.amount[0] }}</span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Date *</label>
                            <input v-model="form.expense_date" type="date" class="form-input" required />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Payment Method *</label>
                        <select v-model="form.payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-2 mt-6">
                        <button type="button" @click="handleClose" class="btn btn-outline-danger">Cancel</button>
                        <button type="submit" :disabled="submitting" class="btn btn-primary">
                            <span v-if="submitting">Creating...</span>
                            <span v-else>Create Expense</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
