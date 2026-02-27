<script setup>
import {reactive, onMounted, onUnmounted, ref} from 'vue';
import VModal from '../components/VModal.vue';
import {translate} from '../../utils/functions.js';
import eventBus from '../../eventBus.js';
import {axiosRequest} from "../../utils/apiRequest.js";

const state = reactive({
    categories: [],
    selectedPermissions: [],
    disableSubmit: false,
    role_id: '',
});

const modalRef = ref(null);
const searchQuery = ref('');

onMounted(() => {
    fetchPermissions();
    eventBus.on('openRolePermissionsModal', openPermissionsModal);
});

onUnmounted(() => {
    eventBus.off('openRolePermissionsModal');
});

const openPermissionsModal = async (data) => {
    const role_id = data.id;
    try {
        const res = await axiosRequest.get(`/admin/roles/${role_id}/show`, {params: {with_permission: 1}});
        const role = res.data.data;
        state.selectedPermissions = role.permissions;
        state.role_id = role.id;
        modalRef.value.openModal();
    } catch (error) {
        state.role_id = null;
        console.log(error.response);
    }
};

const handleSubmit = async () => {
    state.disableSubmit = true;
    await savePermissions();
    state.disableSubmit = false;
};

const savePermissions = async () => {
    try {
        const url = `/admin/roles/${state.role_id}/permissions/update`;
        const data = {permissions: state.selectedPermissions};
        await axiosRequest.put(url, data, {notify: true});
        eventBus.emit('reloadRoleDatatable');
        modalRef.value.closeModal();
    } catch (error) {
        console.log(error.response);
    }
};

const fetchPermissions = async () => {
    try {
        const res = await axiosRequest.get('/admin/roles/permission-categories');
        state.categories = res.data.data;
    } catch (error) {
        console.log(error.response);
        state.categories = [];
    }
};

const resetRoleModal = () => {
    state.selectedPermissions = [];
    state.role_id = null;
    searchQuery.value = '';
};

const hasMatchingPermissions = (category) => {
    return category.permissions.some(permission =>
        permission.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
};

const showPermission = (permissionName) => {
    return permissionName.toLowerCase().includes(searchQuery.value.toLowerCase());
};
</script>

<template>
    <v-modal
        :title="translate('permissions')"
        :onSubmit="handleSubmit"
        :disableSubmit="state.disableSubmit"
        ref="modalRef"
        @modal:closed="resetRoleModal"
        size="max-w-4xl"
    >
        <div class="permissions-container">
            <input
                type="text"
                class="form-input mb-4"
                v-model="searchQuery"
                :placeholder="translate('search_permissions')"
            />
            <div
                v-for="category in state.categories"
                :key="category.id"
                class="category-section"
                v-show="hasMatchingPermissions(category)"
            >
                <h3 class="category-title">{{ category.name }}</h3>
                <div class="permissions-grid">
                    <div
                        v-for="permission in category.permissions"
                        :key="permission.id"
                        class="permission-item"
                        v-show="showPermission(permission.name)"
                    >
                        <label class="custom-checkbox">
                            <input type="checkbox" :value="permission.name" v-model="state.selectedPermissions">
                            <span class="checkmark"></span>
                            <span class="permission-name">
                              {{ permission.name.replace(/_/g, ' ').replace(/^./, c => c.toUpperCase()) }}
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </v-modal>
</template>

<style scoped>
.permissions-container {
    padding: 0 10px;
}

.category-section {
    margin-bottom: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
}

.category-title {
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
}

.permission-item {
    margin-bottom: 10px;
}

.custom-checkbox {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 0.9rem;
    user-select: none;
}

.custom-checkbox input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.checkmark {
    height: 20px;
    width: 20px;
    background-color: #edeff1;
    border-radius: 4px;
    margin-right: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s;
    border: 1px solid #c0baba;
}

.custom-checkbox:hover input ~ .checkmark {
    background-color: #dbe4ec;
}

.custom-checkbox input:checked ~ .checkmark {
    background-color: #007bff;
}

.checkmark:after {
    content: "";
    display: none;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.custom-checkbox input:checked ~ .checkmark:after {
    display: block;
}

.permission-name {
    flex: 1;
}
</style>
