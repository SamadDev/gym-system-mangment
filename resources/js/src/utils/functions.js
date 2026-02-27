import {useAppStore} from '../stores/index';

export const translate = (key) => {
    const store = useAppStore();
    let translations = store.translationList;
    return translations[key] || key;
};

export const can = (key) => {
    const store = useAppStore();
    let permissions = store.user ? store.user.permissions : [];
    if (!permissions) return false;
    // Super Admin wildcard
    if (permissions.includes('*')) return true;
    return permissions.includes(key);
};

export const debounce = (func, wait = 300) => {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
};

export const formatPrice = (amount, currencyType) => {
    const roundedAmount = Number(parseFloat(amount).toFixed(currencyType.precision));

    // Check if the rounded amount has any decimal part
    const hasDecimal = roundedAmount % 1 !== 0;

    // Format the number, using precision only if there's a decimal part
    const formattedPrice = roundedAmount.toLocaleString(undefined, {
        minimumFractionDigits: hasDecimal ? currencyType.precision : 0,
        maximumFractionDigits: currencyType.precision,
        decimalSeparator: currencyType.decimal_sep,
        thousandsSeparator: currencyType.thousand_sep
    });

    return `${currencyType.symbol} ${formattedPrice}`;
}

export const createFormData = (data) => {
    const formData = new FormData();

    // Helper function to recursively append properties to FormData
    const appendFormData = (formData, key, value) => {
        if (value === null) {
            // Replace null values with an empty string
            formData.append(key, '');
        } else if (value instanceof File) {
            // If value is a file, append it directly
            formData.append(key, value);
        } else if (Array.isArray(value)) {
            // If value is an array, loop through and append each item with array syntax
            value.forEach((item, index) => {
                appendFormData(formData, `${key}[${index}]`, item);
            });
        } else if (typeof value === 'object' && value !== null) {
            // If value is an object, recursively call the function to handle nested objects
            for (const nestedKey in value) {
                appendFormData(formData, `${key}.${nestedKey}`, value[nestedKey]);
            }
        } else {
            // For primitive values (string, number, etc.), append them directly
            formData.append(key, value !== undefined ? value : '');  // Handle undefined as well
        }
    };

    // Loop through the data object and append each field to the FormData
    for (const key in data) {
        if (Object.prototype.hasOwnProperty.call(data, key)) {
            appendFormData(formData, key, data[key]);
        }
    }

    return formData;
};
