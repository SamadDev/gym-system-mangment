<template>
    <div>
        <div class="flex items-center gap-3 mb-5">
            <button @click="router.back()" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> {{ translate('back') }}
            </button>
            <h2 class="text-xl font-semibold">{{ isEdit ? translate('edit') + ' Member' : translate('add') + ' Member' }}</h2>
        </div>

        <div v-if="state.loading" class="flex items-center justify-center h-64">
            <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>

        <form v-else @submit.prevent="handleSubmit" class="panel">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- First Name -->
                <div>
                    <label class="block text-sm font-medium mb-1">First Name <span class="text-red-500">*</span></label>
                    <input v-model="form.first_name" type="text" placeholder="First name" class="form-input"/>
                    <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">{{ errors.first_name }}</p>
                </div>

                <!-- Last Name -->
                <div>
                    <label class="block text-sm font-medium mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input v-model="form.last_name" type="text" placeholder="Last name" class="form-input"/>
                    <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">{{ errors.last_name }}</p>
                </div>

                <!-- Phone -->
                <div>
                    <label class="block text-sm font-medium mb-1">{{ translate('phone') }} <span class="text-red-500">*</span></label>
                    <input v-model="form.phone" type="text" placeholder="+964..." class="form-input"/>
                    <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium mb-1">{{ translate('email') }}</label>
                    <input v-model="form.email" type="email" placeholder="email@example.com" class="form-input"/>
                    <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</p>
                </div>

                <!-- Date of Birth -->
                <div>
                    <label class="block text-sm font-medium mb-1">Date of Birth <span class="text-red-500">*</span></label>
                    <input v-model="form.date_of_birth" type="date" class="form-input"/>
                    <p v-if="errors.date_of_birth" class="text-red-500 text-xs mt-1">{{ errors.date_of_birth }}</p>
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-sm font-medium mb-1">{{ translate('gender') }} <span class="text-red-500">*</span></label>
                    <select v-model="form.gender" class="form-select">
                        <option value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                    <p v-if="errors.gender" class="text-red-500 text-xs mt-1">{{ errors.gender }}</p>
                </div>

                <!-- Blood Type -->
                <div>
                    <label class="block text-sm font-medium mb-1">Blood Type</label>
                    <select v-model="form.blood_type" class="form-select">
                        <option value="">Select blood type</option>
                        <option v-for="bt in bloodTypes" :key="bt" :value="bt">{{ bt }}</option>
                    </select>
                    <p v-if="errors.blood_type" class="text-red-500 text-xs mt-1">{{ errors.blood_type }}</p>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium mb-1">{{ translate('status') }} <span class="text-red-500">*</span></label>
                    <select v-model="form.status" class="form-select">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <p v-if="errors.status" class="text-red-500 text-xs mt-1">{{ errors.status }}</p>
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">{{ translate('address') }}</label>
                    <textarea v-model="form.address" rows="2" placeholder="Address..." class="form-textarea"></textarea>
                    <p v-if="errors.address" class="text-red-500 text-xs mt-1">{{ errors.address }}</p>
                </div>

                <!-- Emergency Contact -->
                <div>
                    <label class="block text-sm font-medium mb-1">Emergency Contact <span class="text-red-500">*</span></label>
                    <input v-model="form.emergency_contact" type="text" placeholder="Contact name" class="form-input"/>
                    <p v-if="errors.emergency_contact" class="text-red-500 text-xs mt-1">{{ errors.emergency_contact }}</p>
                </div>

                <!-- Emergency Phone -->
                <div>
                    <label class="block text-sm font-medium mb-1">Emergency Phone <span class="text-red-500">*</span></label>
                    <input v-model="form.emergency_phone" type="text" placeholder="+964..." class="form-input"/>
                    <p v-if="errors.emergency_phone" class="text-red-500 text-xs mt-1">{{ errors.emergency_phone }}</p>
                </div>

                <!-- Photo -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Photo</label>
                    <input type="file" accept="image/*" @change="handlePhoto" class="form-input"/>
                    <p v-if="errors.photo" class="text-red-500 text-xs mt-1">{{ errors.photo }}</p>
                    <img v-if="state.photoPreview" :src="state.photoPreview" class="mt-2 w-24 h-24 rounded-full object-cover"/>
                </div>

            </div>

            <!-- General error -->
            <div v-if="state.error" class="mt-4 text-red-500 text-sm">{{ state.error }}</div>

            <!-- Actions -->
            <div class="flex justify-end gap-3 mt-6 border-t pt-5">
                <button type="button" @click="router.back()" class="btn btn-outline-secondary">{{ translate('cancel') }}</button>
                <button type="submit" class="btn btn-primary" :disabled="state.saving">
                    <span v-if="state.saving"><i class="fas fa-spinner fa-spin mr-1"></i> Saving...</span>
                    <span v-else>{{ isEdit ? translate('edit') : translate('add') }}</span>
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useMeta } from '@/composables/use-meta';
import { axiosRequest } from '@/utils/apiRequest';
import { translate } from '@/utils/functions';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);

useMeta({ title: isEdit.value ? 'Edit Member' : 'Create Member' });

const bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

const form = reactive({
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    date_of_birth: '',
    gender: '',
    blood_type: '',
    status: 'active',
    address: '',
    emergency_contact: '',
    emergency_phone: '',
    photo: null,
});

const errors = reactive({});
const state = reactive({
    loading: isEdit.value,
    saving: false,
    error: null,
    photoPreview: null,
});

onMounted(async () => {
    if (isEdit.value) {
        try {
            const res = await axiosRequest.get(`/admin/members/${route.params.id}`);
            const m = res.data.data;
            form.first_name = m.first_name || '';
            form.last_name = m.last_name || '';
            form.phone = m.phone || '';
            form.email = m.email || '';
            form.date_of_birth = m.date_of_birth ? m.date_of_birth.slice(0, 10) : '';
            form.gender = m.gender || '';
            form.blood_type = m.blood_type || '';
            form.status = m.status || 'active';
            form.address = m.address || '';
            form.emergency_contact = m.emergency_contact || '';
            form.emergency_phone = m.emergency_phone || '';
            if (m.photo) state.photoPreview = `/storage/${m.photo}`;
        } catch (e) {
            state.error = 'Failed to load member data.';
        } finally {
            state.loading = false;
        }
    }
});

const handlePhoto = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.photo = file;
    state.photoPreview = URL.createObjectURL(file);
};

const handleSubmit = async () => {
    state.saving = true;
    state.error = null;
    Object.keys(errors).forEach(k => delete errors[k]);

    const data = new FormData();
    Object.entries(form).forEach(([k, v]) => {
        if (v !== null && v !== '') data.append(k, v);
    });
    if (isEdit.value) data.append('_method', 'PUT');

    try {
        const url = isEdit.value ? `/admin/members/${route.params.id}` : '/admin/members';
        await axiosRequest.post(url, data, { headers: { 'Content-Type': 'multipart/form-data' } });
        router.push('/admin/members');
    } catch (e) {
        if (e.response?.status === 422) {
            Object.assign(errors, e.response.data.errors);
        } else {
            state.error = e.response?.data?.message || 'Something went wrong.';
        }
    } finally {
        state.saving = false;
    }
};
</script>
