<template>
    <header class="z-40 hide-header-on-print" :class="{ dark: store.semidark && store.menu === 'horizontal' }">
        <div class="shadow-sm">
            <div class="relative bg-white flex w-full items-center px-5 py-2.5 dark:bg-[#0e1726]">
                <div class="horizontal-logo flex lg:hidden justify-between items-center ltr:mr-2 rtl:ml-2">
                    <router-link to="/" class="main-logo flex items-center shrink-0">
                        <img class="w-8 ltr:-ml-1 rtl:-mr-1 inline" src="/assets/images/auth/logo-white.png" alt=""/>
                        <span
                            class="text-2xl ltr:ml-1.5 rtl:mr-1.5 font-semibold align-middle hidden md:inline dark:text-white-light transition-all duration-300"
                        >VRISTO</span
                        >
                    </router-link>

                    <a
                        href="javascript:;"
                        class="collapse-icon flex-none dark:text-[#d0d2d6] hover:text-primary dark:hover:text-primary flex lg:hidden ltr:ml-2 rtl:mr-2 p-2 rounded-full bg-white-light/40 dark:bg-dark/40 hover:bg-white-light/90 dark:hover:bg-dark/60"
                        @click="store.toggleSidebar()"
                    >
                        <icon-menu class="w-5 h-5"/>
                    </a>
                </div>

                <div
                    class="sm:flex-1 ltr:sm:ml-0 ltr:ml-auto sm:rtl:mr-0 rtl:mr-auto flex items-center space-x-1.5 lg:space-x-2 rtl:space-x-reverse dark:text-[#d0d2d6]"
                >
                    <div class="sm:ltr:mr-auto sm:rtl:ml-auto"></div>
                    <div>
                        <a
                            href="javascript:;"
                            v-show="store.theme === 'light'"
                            class="flex items-center p-2 rounded-full bg-white-light/40 dark:bg-dark/40 hover:text-primary hover:bg-white-light/90 dark:hover:bg-dark/60"
                            @click="store.toggleTheme('dark')"
                        >
                            <icon-sun/>
                        </a>
                        <a
                            href="javascript:;"
                            v-show="store.theme === 'dark'"
                            class="flex items-center p-2 rounded-full bg-white-light/40 dark:bg-dark/40 hover:text-primary hover:bg-white-light/90 dark:hover:bg-dark/60"
                            @click="store.toggleTheme('system')"
                        >
                            <icon-moon/>
                        </a>
                        <a
                            href="javascript:;"
                            v-show="store.theme === 'system'"
                            class="flex items-center p-2 rounded-full bg-white-light/40 dark:bg-dark/40 hover:text-primary hover:bg-white-light/90 dark:hover:bg-dark/60"
                            @click="store.toggleTheme('light')"
                        >
                            <icon-laptop/>
                        </a>
                    </div>

                    <div class="dropdown shrink-0">
                        <Popper :placement="store.rtlClass === 'rtl' ? 'bottom-end' : 'bottom-start'" offsetDistance="8"
                                class="align-middle">
                            <button
                                type="button"
                                class="block p-2 rounded-full bg-white-light/40 dark:bg-dark/40 hover:text-primary hover:bg-white-light/90 dark:hover:bg-dark/60"
                            >
                                <img :src="currentFlag" alt="flag" class="w-5 h-5 object-cover rounded-full"/>
                            </button>
                            <template #content="{ close }">
                                <ul class="!px-2 text-dark dark:text-white-dark grid grid-cols-2 gap-2 font-semibold dark:text-white-light/90 w-[280px]">
                                    <template v-for="item in store.languageList" :key="item.code">
                                        <li>
                                            <button
                                                type="button"
                                                class="w-full hover:text-primary"
                                                :class="{ 'bg-primary/10 text-primary': store.locale === item.code }"
                                                @click="changeLanguage(item), close()"
                                            >
                                                <img
                                                    class="w-5 h-5 object-cover rounded-full"
                                                    :src="item.image"
                                                    alt=""
                                                />
                                                <span class="ltr:ml-3 rtl:mr-3">{{ item.name }}</span>
                                            </button>
                                        </li>
                                    </template>
                                </ul>
                            </template>
                        </Popper>
                    </div>

                    <div class="dropdown shrink-0">
                        <Popper :placement="store.rtlClass === 'rtl' ? 'bottom-end' : 'bottom-start'" offsetDistance="8"
                                class="!block align-middle">
                            <button type="button" class="relative group block">
                                <img
                                    class="w-9 h-9 rounded-full object-cover saturate-50 group-hover:saturate-100"
                                    src="/assets/images/user-profile.jpeg"
                                    alt=""
                                />
                            </button>
                            <template #content="{ close }">
                                <ul class="text-dark dark:text-white-dark !py-0 w-[230px] font-semibold dark:text-white-light/90">
                                    <li>
                                        <div class="flex items-center px-4 py-4">
                                            <div class="flex-none">
                                                <img class="rounded-md w-10 h-10 object-cover"
                                                     src="/assets/images/user-profile.jpeg" alt=""/>
                                            </div>
                                            <div class="ltr:pl-4 rtl:pr-4 truncate">
                                                <h4 class="text-base">{{ store.user.name }}<span
                                                    class="text-xs bg-success-light rounded text-success px-1 ltr:ml-2 rtl:ml-2">Pro</span>
                                                </h4>
                                                <a class="text-black/60 hover:text-primary dark:text-dark-light/60 dark:hover:text-white"
                                                   href="javascript:;"
                                                >{{ store.user.email }}</a
                                                >
                                            </div>
                                        </div>
                                    </li>

                                    <li class="border-t border-white-light dark:border-white-light/10">
                                        <button type="button" class="text-danger !py-3" @click="handleLogout">
                                            <icon-logout class="w-4.5 h-4.5 ltr:mr-2 rtl:ml-2 rotate-90 shrink-0"/>
                                            {{ translate('logout') }}
                                        </button>
                                    </li>
                                </ul>
                            </template>
                        </Popper>
                    </div>
                </div>
            </div>

            <!-- horizontal menu -->
            <ul
                class="horizontal-menu hidden py-1.5 font-semibold px-6 lg:space-x-1.5 xl:space-x-8 rtl:space-x-reverse bg-white border-t border-[#ebedf2] dark:border-[#191e3a] dark:bg-[#0e1726] text-black dark:text-white-dark"
            >
                <li class="menu nav-item relative">
                    <a href="javascript:;" class="nav-link">
                        <div class="flex items-center">
                            <icon-menu-dashboard class="shrink-0"/>

                            <span class="px-2">{{ translate('dashboard') }}</span>
                        </div>
                        <div class="right_arrow">
                            <icon-caret-down/>
                        </div>
                    </a>
                    <ul class="sub-menu">
                        <li>
                            <router-link to="/">{{ translate('sales') }}</router-link>
                        </li>

                    </ul>
                </li>
            </ul>
        </div>
    </header>
</template>

<script lang="ts" setup>
import {onMounted, computed, reactive, watch} from 'vue';
import {useI18n} from 'vue-i18n';

import appSetting from '../../app-setting';
import {useRoute, useRouter} from 'vue-router';
import {useAppStore} from '../../stores/index';
import {translate} from '../../utils/functions';

import IconMenu from '../../components/icon/icon-menu.vue';
import IconSun from '../../components/icon/icon-sun.vue';
import IconMoon from '../../components/icon/icon-moon.vue';
import IconLaptop from '../../components/icon/icon-laptop.vue';
import IconLogout from '../../components/icon/icon-logout.vue';
import IconMenuDashboard from '../../components/icon/menu/icon-menu-dashboard.vue';
import IconCaretDown from '../../components/icon/icon-caret-down.vue';
import {axiosRequest} from '../../utils/apiRequest';

const store = useAppStore();
const route = useRoute();
const router = useRouter();

// multi language
const changeLanguage = async (item: any) => {
    const languageId = item.id;

    try {
        const res = await axiosRequest.put('/admin/translations/switch-language', {language_id: languageId});
        store.user.language_id = languageId;
        store.setUser(store.user);
        store.setTranslations(res.data.data);
        store.toggleLocale(item);
        location.reload();
    } catch (error) {
        console.log(error);
    }
};
const currentFlag = computed(() => {
    try {
        const selectedLanguage = store.languageList.find((item) => item.id === store.user.language_id);
        if (selectedLanguage) {
            store.locale = selectedLanguage.code;
        }
        return selectedLanguage.image;
    } catch (e) {
        return null;
    }
});

const handleLogout = async (e) => {
    e.preventDefault();
    try {
        const res = await axiosRequest.post('/logout', {});
        store.removeUser();
    } catch (error) {
        store.removeUser();
    } finally {
        router.push({name: 'login'});
    }
}

onMounted(() => {
    setActiveDropdown();
});

watch(route, (to, from) => {
    setActiveDropdown();
});

const setActiveDropdown = () => {
    const selector = document.querySelector('ul.horizontal-menu a[href="' + window.location.pathname + '"]');
    if (selector) {
        selector.classList.add('active');
        const all: any = document.querySelectorAll('ul.horizontal-menu .nav-link.active');
        for (let i = 0; i < all.length; i++) {
            all[0]?.classList.remove('active');
        }
        const ul: any = selector.closest('ul.sub-menu');
        if (ul) {
            let ele: any = ul.closest('li.menu').querySelectorAll('.nav-link');
            if (ele) {
                ele = ele[0];
                setTimeout(() => {
                    ele?.classList.add('active');
                });
            }
        }
    }
};

</script>

<style>
@media print {
    .hide-header-on-print {
        display: none;
    }
}
</style>
