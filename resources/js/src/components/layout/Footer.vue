<template>
    <div class="dark:text-white-dark text-center ltr:sm:text-left rtl:sm:text-right p-6 pt-0 mt-auto hide-footer-on-print">
        © {{ new Date().getFullYear() }}. Vristo All rights reserved.
    </div>
</template>

<style>
@media print {
    .hide-footer-on-print {
        display: none;
    }
}
</style>
