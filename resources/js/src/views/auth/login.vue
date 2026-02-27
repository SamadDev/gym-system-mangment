<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">

        <!-- Subtle background pattern -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-100 rounded-full opacity-60 blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-100 rounded-full opacity-60 blur-3xl"></div>
        </div>

        <!-- Card -->
        <div class="relative w-full max-w-md mx-4">
            <div class="bg-white border border-gray-200 rounded-3xl shadow-xl shadow-gray-200/80 p-8 md:p-10">

                <!-- Logo -->
                <div class="flex justify-center mb-8">
                    <router-link to="/">
                        <img src="/assets/images/auth/logo-white.png" alt="Logo" class="h-16 object-contain"/>
                    </router-link>
                </div>

                <!-- Heading -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-800 mb-1">Welcome back</h1>
                    <p class="text-gray-400 text-sm">Sign in to your account to continue</p>
                </div>

                <!-- Form -->
                <form class="space-y-5" @submit.prevent="handleLogin">

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <icon-mail :fill="true"/>
                            </span>
                            <input
                                v-model="state.email"
                                id="Email"
                                type="email"
                                placeholder="you@example.com"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-800 placeholder-gray-300 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"
                            />
                        </div>
                        <p v-if="state.errors.email" class="mt-1 text-xs text-red-500">{{ state.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <icon-lock-dots :fill="true"/>
                            </span>
                            <input
                                id="Password"
                                v-model="state.password"
                                type="password"
                                placeholder="••••••••"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-800 placeholder-gray-300 rounded-xl pl-10 pr-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent transition"
                            />
                        </div>
                        <p v-if="state.errors.password" class="mt-1 text-xs text-red-500">{{ state.errors.password }}</p>
                    </div>

                    <!-- Error message -->
                    <div v-if="state.error_message" class="bg-red-50 border border-red-200 text-red-500 text-sm rounded-xl px-4 py-3">
                        {{ state.error_message }}
                    </div>

                    <!-- Submit -->
                    <button
                        type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-semibold py-3 rounded-xl shadow-md shadow-indigo-200 transition-all duration-200 mt-2"
                    >
                        <loader-icon v-if="state.is_loading" :height="16" :width="16"/>
                        <span>{{ state.is_loading ? 'Signing in...' : 'Sign In' }}</span>
                    </button>
                </form>

                <!-- Footer -->
                <p class="text-center text-gray-300 text-xs mt-8">
                    © {{ new Date().getFullYear() }} Jiasaz. All Rights Reserved.
                </p>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import {useAppStore} from '../../stores/index';
import {useRouter} from 'vue-router';
import {useMeta} from '../../composables/use-meta';
import IconMail from '../../components/icon/icon-mail.vue';
import IconLockDots from '../../components/icon/icon-lock-dots.vue';
import {reactive} from "vue";
import {axiosRequest} from '../../utils/apiRequest';
import LoaderIcon from "../../components/icon/loader-icon.vue";

useMeta({title: 'Login'});
const router = useRouter();
const store = useAppStore();

const state = reactive({
    email: '',
    password: '',
    errors: {},
    is_loading: false,
    error_message: '',
});

const handleLogin = async (e) => {
    e.preventDefault();

    if (state.is_loading === true) return;

    state.is_loading = true;
    state.errors = {};

    try {
        const res = await axiosRequest.post('/login', {
            email: state.email,
            password: state.password,
        });
        handleSuccessLogin(res);
    } catch (error) {
        handleFailLogin(error);
    }

    setTimeout(() => {
        state.is_loading = false;
    }, 500);
};

const handleSuccessLogin = (res) => {
    store.setUser(res.data.data.user);
    router.push({name: 'dashboard'});
}

const handleFailLogin = (error) => {
    store.removeUser();
    router.push({name: 'login'});

    if (error && error.response.status == 401) {
        state.error_message = error.response.data.message;
    }

    if (error.response.status == 422) {
        state.errors = error.response.data.errors;
    }
}
</script>
