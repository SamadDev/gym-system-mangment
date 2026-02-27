<script setup>
import Vue3Datatable from '@bhplugin/vue3-datatable';
import {defineEmits, onMounted, onUnmounted, reactive, ref, watch} from "vue";
import eventBus from "../../../eventBus.js";
import {debounce, translate} from '../../../utils/functions.js';
import {axiosRequest} from "../../../utils/apiRequest.js";
import FilterTemplate from "./FilterTemplate.vue";
import ColumnChooser from "./ColumnChooser.vue";
import BulkAction from "./BulkAction.vue";

const emit = defineEmits(['datatable:draw', 'rowSelect']);

const props = defineProps({
    url: {
        type: String,
        default: '',
        required: true,
    },
    columns: {
        type: Array,
        default: [],
    },
    reloadTableEvent: {
        type: String,
        default: '',
    },
    defaultOrder: {
        type: Boolean,
        default: true,
    },
    hasCheckbox: {
        type: Boolean,
        default: false,
    },
});

const tableFilterRef = ref(null);

// Debounce the fetchData function
const handleSearchInputChange = debounce((event) => {
    event.preventDefault();

    const query = event.target.value;
    requestData.search = {value: query, regex: false}
    fetchData();
}, 300);


const yajraColumns = props.columns.map((col, index) => ({
    data: col.field,
    name: col.name ? col.name : null,
    searchable: col.searchable ? col.searchable : false,
    orderable: col.sortable,
    search: {value: '', regex: false}
}));

const requestData = reactive({
    draw: 1,
    columns: yajraColumns,
    order: [
        {column: 0, dir: 'desc'}
    ],
    start: 0,
    length: 10,
    search: {value: "", regex: false},
    filters: {},
});

const vue3DatatableTableColumns = props.columns.map((col, index) => ({
    title: col.label,
    field: col.field,
    isUnique: col.isKey,
    sort: col.sortable,
    hide: col.hide ? col.hide : false,
}));

const table = reactive({
    isLoading: false,
    columns: vue3DatatableTableColumns,
    rows: [],
    totalRecordCount: 0,
    sort_column: 'id',
    sort_direction: 'desc',
});

// Filter drawer state and form model

onMounted(() => {
    eventBus.on(props.reloadTableEvent, (filters) => {
        requestData.filters = {...requestData.filters, ...filters};
        fetchData();
    });
});

onUnmounted(() => {
    eventBus.off(props.reloadTableEvent);
});
const fetchData = async () => {
    try {
        table.isLoading = true;
        const res = await axiosRequest.get(props.url, {params: requestData});
        table.rows = res.data.data;
        table.totalRecordCount = res.data.recordsTotal;
        if (props.defaultOrder === true) {
            table.sort_column = props.columns[res.data.input.order[0].column].field;
            table.sort_direction = res.data.input.order[0].dir;
        }
        emit('datatable:draw', res.data);

    } catch (error) {
        console.log(error);
    }
    table.isLoading = false;
}
fetchData();
const templateColumns = props.columns.filter(column => column.template === true);
const changeServer = (data) => {
    table.current_page = data.current_page;
    table.pagesize = data.pagesize;

    const columnIndex = props.columns.findIndex(column => column.field === data.sort_column);
    requestData.order = [{column: columnIndex, dir: data.sort_direction}];
    requestData.length = data.pagesize;
    requestData.start = data.offset;
    table.sort_column = data.sort_column;
    table.sort_direction = data.sort_direction;

    fetchData();
};

const onFilterChange = (name, value) => {
    requestData.filters[name] = value;
}

const applyFilters = () => {
    fetchData();
    tableFilterRef.value.closeFilter();
}

const selectedRows = ref([]);
const handleRowSelect = (data) => {
    // emit('rowSelect', data);
    selectedRows.value = data;
}

// Expose the component's public interface
defineExpose({
    selectedRows
});


</script>
<template>
    <div class="panel pb-0 mt-1">
        <div class="flex md:items-center md:flex-row flex-col mb-5 gap-5">
            <div class="flex w-full">
                <div class="mr-3">
                    <input type="text" @input="handleSearchInputChange"
                           class="form-input form-input-sm min-w-[200px] py-2"
                           placeholder="Search..."/>
                </div>

                <div class="w-full">
                    <slot name="external-filters" :onFilterChange="onFilterChange"></slot>
                </div>
            </div>

            <div class="flex items-center gap-5 ltr:ml-auto rtl:mr-auto">
                <BulkAction v-if="selectedRows.length" :selectedCount="selectedRows.length">
                    <slot name="bulk-actions"></slot>
                </BulkAction>
                <ColumnChooser :columns="table.columns"/>
                <button
                    @click="() => tableFilterRef.openFilter()"
                    class="flex items-center gap-1 px-3 py-1 border border-gray-400 text-gray-700 rounded-md hover:bg-gray-100 hover:border-gray-500 transition-colors duration-200"
                >
                    <i class="fas fa-filter text-sm"></i>
                    <span class="text-sm font-medium">Filter</span>
                </button>
            </div>
        </div>

        <FilterTemplate
            ref="tableFilterRef"
            @filter:applied="applyFilters"
        >
            <slot name="filters" :onFilterChange="onFilterChange"></slot>
        </FilterTemplate>

        <div class="datatable">
            <vue3-datatable
                :rows="table.rows"
                :columns="table.columns"
                :loading="table.isLoading"
                :totalRows="table.totalRecordCount"
                :isServerMode="true"
                :sortable="true"
                :sortColumn="table.sort_column"
                :sortDirection="table.sort_direction"
                @change="changeServer"
                @rowSelect="handleRowSelect"
                :hasCheckbox="props.hasCheckbox"
                skin="whitespace-nowrap bh-table-hover"
                firstArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M13 19L7 12L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M16.9998 19L10.9998 12L16.9998 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
                lastArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M11 19L17 12L11 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> <path opacity="0.5" d="M6.99976 19L12.9998 12L6.99976 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg> '
                previousArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M15 5L9 12L15 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
                nextArrow='<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-4.5 h-4.5 rtl:rotate-180"> <path d="M9 5L15 12L9 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/> </svg>'
            >
                <template v-for="column in templateColumns" v-slot:[column.field]="data">
                    <slot :name="column.field" :data="data"></slot>
                </template>
            </vue3-datatable>
        </div>
    </div>
</template>

<style scoped>
.datatable {
    position: relative;
}

::v-deep(.bh-table-responsive) {
    position: unset !important;
}

</style>
