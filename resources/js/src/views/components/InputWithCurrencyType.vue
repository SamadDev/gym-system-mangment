<script setup>
import {ref, onMounted} from 'vue';
import {useAppStore} from '../../stores';

const store = useAppStore();

const emit = defineEmits(["update:inputValue", "update:selectValue"]);

const props = defineProps({
    name: {
        type: String,
        default: "",
    },
    modelValue: {
        type: Number,
        default: null,
    },
    disabledInput: {
        type: Boolean,
        default: false,
    },
    placeholder: {
        type: String,
        default: '',
    },
});

const currencies = ref([]);
const selectedCurrencyId = ref(null);

onMounted(() => {
    currencies.value = store.currencyTypeList;
    selectedCurrencyId.value = currencies.value[0].id;
    emit('update:selectValue', currencies.value[0]);
});

const update_select_value = (currency_id) => {
    const item = currencies.value.find((item) => item.id == currency_id);
    return item ? item : null;
}

const resetCurrency = () => {
    currencies.value = store.currencyTypeList;
    selectedCurrencyId.value = currencies.value[0].id;
    emit('update:selectValue', currencies.value[0]);
};

const setCurrencyWithRate = (currency_type_id, currency_rate) => {
    currencies.value = store.currencyTypeList;
    selectedCurrencyId.value = currency_type_id;

    let selected_currency = currencies.value.find(currency => currency.id === currency_type_id);
    selected_currency.rate = parseFloat(currency_rate);

    emit('update:selectValue', selected_currency);
};

defineExpose({
    resetCurrency,
    setCurrencyWithRate,
});
</script>
<template>
    <div>
        <div class="flex">
            <input
                type="number"
                class="form-input ltr:rounded-r-none rtl:rounded-l-none flex-1 ltr:rounded-l-md rtl:rounded-r-md"
                :name="name"
                :value="modelValue"
                :disabled="disabledInput"
                :placeholder="placeholder"
                step="any"
                @input="emit('update:inputValue', $event.target.value)"
            />
            <div
                class="bg-[#eee] flex justify-center items-center ltr:rounded-r-md rtl:rounded-l-md font-semibold border ltr:border-l-0 rtl:border-r-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]"
            >
                <select
                    @change="emit('update:selectValue', update_select_value($event.target.value))"
                    v-model="selectedCurrencyId"
                    class="form-select border-0 bg-[#eee]">
                    <option v-for="currency in currencies" :key="currency.id" :value="currency.id">
                        {{ currency.symbol }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>

<style scoped>
select {
    -webkit-appearance: none; /* Safari */
    -moz-appearance: none; /* Firefox */
    appearance: none; /* Standard */
    background: none; /* Remove arrow background */
}
</style>

