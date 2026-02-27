<template>
    <div :class="{ 'dark text-white-dark': store.semidark }">
        <nav
            class="sidebar fixed min-h-screen h-full top-0 bottom-0 w-[260px] shadow-[5px_0_25px_0_rgba(94,92,154,0.1)] z-50 transition-all duration-300">
            <div class="bg-white dark:bg-[#0e1726] h-full">
                <div class="flex justify-between items-center px-4 py-3">
                    <router-link to="/" class="main-logo flex items-center shrink-0">
                        <img class="w-8 ml-[5px] flex-none" src="/assets/images/auth/logo-white.png" alt=""/>
                        <span
                            class="text-2xl ltr:ml-1.5 rtl:mr-1.5 font-semibold align-middle lg:inline dark:text-white-light">VRISTO</span>
                    </router-link>
                    <a
                        href="javascript:;"
                        class="collapse-icon w-8 h-8 rounded-full flex items-center hover:bg-gray-500/10 dark:hover:bg-dark-light/10 dark:text-white-light transition duration-300 rtl:rotate-180 hover:text-primary"
                        @click="store.toggleSidebar()"
                    >
                        <icon-carets-down class="m-auto rotate-90"/>
                    </a>
                </div>
                <perfect-scrollbar
                    :options="{
                        swipeEasing: true,
                        wheelPropagation: false,
                    }"
                    class="h-[calc(100vh-80px)] relative"
                >
                    <ul class="relative font-semibold space-y-0.5 p-4 py-0">
                        <h2 class="py-3 px-7 flex items-center uppercase font-extrabold bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08] -mx-4 mb-1">
                            <icon-minus class="w-4 h-5 flex-none hidden"/>
                            <span>{{ translate('main') }}</span>
                        </h2>
                        <li class="nav-item">
                            <ul>
                                <li class="nav-item">
                                    <router-link to="/admin/dashboard" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-dashboard class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Dashboard</span>
                                        </div>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/members" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-users class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Members</span>
                                        </div>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/memberships" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-shopping-cart class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Memberships</span>
                                        </div>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/membership-plans" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-notes class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Membership Plans</span>
                                        </div>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/attendance" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-datatables class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Attendance</span>
                                        </div>
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                        <h2 class="py-3 px-7 flex items-center uppercase font-extrabold bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08] -mx-4 mb-1">
                            <icon-minus class="w-4 h-5 flex-none hidden"/>
                            <span>Finance</span>
                        </h2>
                        <li class="nav-item">
                            <ul>
                                <li class="nav-item">
                                    <router-link to="/admin/payments" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-invoice class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Payments</span>
                                        </div>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/invoices" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-notes class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Invoices</span>
                                        </div>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/expenses" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-elements class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Expenses</span>
                                        </div>
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                        <h2 class="py-3 px-7 flex items-center uppercase font-extrabold bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08] -mx-4 mb-1">
                            <icon-minus class="w-4 h-5 flex-none hidden"/>
                            <span>Operations</span>
                        </h2>
                        <li class="nav-item">
                            <ul>
                                <li class="nav-item">
                                    <router-link to="/admin/trainers" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-users class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Trainers</span>
                                        </div>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/classes" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-widgets class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Classes</span>
                                        </div>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/equipment" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-menu-elements class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Equipment</span>
                                        </div>
                                    </router-link>
                                </li>
                                <li class="nav-item">
                                    <router-link to="/admin/inventory" class="group" @click="toggleMobileMenu">
                                        <div class="flex items-center">
                                            <icon-shopping-cart class="group-hover:!text-primary shrink-0"/>
                                            <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Inventory</span>
                                        </div>
                                    </router-link>
                                </li>
                            </ul>
                        </li>
                        <h2 class="py-3 px-7 flex items-center uppercase font-extrabold bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08] -mx-4 mb-1">
                            <icon-minus class="w-4 h-5 flex-none hidden"/>
                            <span>Reports</span>
                        </h2>
                        <li class="menu nav-item">
                            <button
                                type="button"
                                class="nav-link group w-full"
                                :class="{ active: activeDropdown === 'reports' }"
                                @click="activeDropdown === 'reports' ? (activeDropdown = null) : (activeDropdown = 'reports')"
                            >
                                <div class="flex items-center">
                                    <icon-menu-charts class="group-hover:!text-primary shrink-0"/>
                                    <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Reports</span>
                                </div>
                                <div :class="{ 'rtl:rotate-90 -rotate-90': activeDropdown !== 'reports' }">
                                    <icon-caret-down/>
                                </div>
                            </button>
                            <vue-collapsible :isOpen="activeDropdown === 'reports'">
                                <ul class="sub-menu text-gray-500">
                                    <li>
                                        <router-link to="/admin/reports/revenue" @click="toggleMobileMenu">Revenue Report</router-link>
                                    </li>
                                    <li>
                                        <router-link to="/admin/reports/members" @click="toggleMobileMenu">Members Report</router-link>
                                    </li>
                                    <li>
                                        <router-link to="/admin/reports/attendance" @click="toggleMobileMenu">Attendance Report</router-link>
                                    </li>
                                </ul>
                            </vue-collapsible>
                        </li>
                        <h2 class="py-3 px-7 flex items-center uppercase font-extrabold bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08] -mx-4 mb-1">
                            <icon-minus class="w-4 h-5 flex-none hidden"/>
                            <span>System</span>
                        </h2>
                        <li class="menu nav-item">
                            <button
                                type="button"
                                class="nav-link group w-full"
                                :class="{ active: activeDropdown === 'user_managements' }"
                                @click="activeDropdown === 'user_managements' ? (activeDropdown = null) : (activeDropdown = 'user_managements')"
                            >
                                <div class="flex items-center">
                                    <icon-menu-authentication class="group-hover:!text-primary shrink-0"/>
                                    <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">User Management</span>
                                </div>
                                <div :class="{ 'rtl:rotate-90 -rotate-90': activeDropdown !== 'user_managements' }">
                                    <icon-caret-down/>
                                </div>
                            </button>
                            <vue-collapsible :isOpen="activeDropdown === 'user_managements'">
                                <ul class="sub-menu text-gray-500">
                                    <li>
                                        <router-link to="/admin/users" @click="toggleMobileMenu">Users</router-link>
                                    </li>
                                    <li>
                                        <router-link to="/admin/roles" @click="toggleMobileMenu">Roles</router-link>
                                    </li>
                                </ul>
                            </vue-collapsible>
                        </li>
                    </ul>
                </perfect-scrollbar>
            </div>
        </nav>
    </div>
</template>

<script lang="ts" setup>
import {ref, onMounted} from 'vue';

import {useAppStore} from '../../stores/index';
import VueCollapsible from 'vue-height-collapsible/vue3';
import {translate, can} from '../../utils/functions';
import IconCaretsDown from '../icon/icon-carets-down.vue';
import IconMenuDashboard from '../icon/menu/icon-menu-dashboard.vue';
import IconShoppingCart from '../icon/icon-shopping-cart.vue';
import IconMenuElements from '../icon/menu/icon-menu-elements.vue';
import IconMenuAuthentication from '../icon/menu/icon-menu-authentication.vue';
import IconMenuInvoice from '..//icon/menu/icon-menu-invoice.vue';
import IconMenuDatatables from '..//icon/menu/icon-menu-datatables.vue';
import IconMenuNotes from '../icon/menu/icon-menu-notes.vue';
import IconGlobe from '../icon/icon-globe.vue';
import IconMinus from '../icon/icon-minus.vue';
import IconCaretDown from '../icon/icon-caret-down.vue';
import IconMenuUsers from '../icon/menu/icon-menu-users.vue';
import IconMenuCharts from '../icon/menu/icon-menu-charts.vue';
import IconMenuContacts from '../icon/menu/icon-menu-contacts.vue';
import IconMenuWidgets from '../icon/menu/icon-menu-widgets.vue';
import IconMenuSafe from '../icon/menu/icon-menu-safe.vue';

const store = useAppStore();
const activeDropdown: any = ref('');

onMounted(() => {
    const selector = document.querySelector('.sidebar ul a[href="' + window.location.pathname + '"]');
    if (selector) {
        selector.classList.add('active');
        const ul: any = selector.closest('ul.sub-menu');
        if (ul) {
            let ele: any = ul.closest('li.menu').querySelectorAll('.nav-link') || [];
            if (ele.length) {
                ele = ele[0];
                setTimeout(() => {
                    ele.click();
                });
            }
        }
    }
});

const toggleMobileMenu = () => {
    if (window.innerWidth < 1024) {
        store.toggleSidebar();
    }
};
</script>
