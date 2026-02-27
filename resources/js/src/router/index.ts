import {createRouter, createWebHistory, RouteRecordRaw} from 'vue-router';
import {useAppStore} from '../stores/index';
import appSetting from '../app-setting';

const routes: RouteRecordRaw[] = [
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import( '../views/pages/error404.vue'),
        meta: {layout: 'auth'},
    },
    {
        path: '/',
        name: '/',
        redirect: '/admin/dashboard',
    },
    {
        path: '/login',
        name: 'login',
        component: () => import( '../views/auth/login.vue'),
        meta: {layout: 'auth', guestOnly: true},
    },
    {
        path: '/admin',
        name: 'admin',
        children: [
            {
                path: 'dashboard',
                name: 'dashboard',
                component: () => import( '../views/Main/Dashboard/gym-dashboard.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_dashboard',
                },
            },
            // Gym Management Routes
            {
                path: 'members',
                name: 'members',
                component: () => import( '../views/members/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'members.view',
                },
            },
            {
                path: 'members/create',
                name: 'create_member',
                component: () => import( '../views/members/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_member',
                },
            },
            {
                path: 'members/:id',
                name: 'view_member',
                component: () => import( '../views/members/View.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_member',
                },
            },
            {
                path: 'members/:id/edit',
                name: 'edit_member',
                component: () => import( '../views/members/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_member',
                },
            },
            {
                path: 'memberships',
                name: 'memberships',
                component: () => import( '../views/members/Memberships/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'memberships.view',
                },
            },
            {
                path: 'membership-plans',
                name: 'membership-plans',
                component: () => import( '../views/members/MembershipPlans/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'memberships.view',
                },
            },
            {
                path: 'attendance',
                name: 'attendance',
                component: () => import( '../views/attendance/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'attendance.view',
                },
            },
            {
                path: 'payments',
                name: 'payments',
                component: () => import( '../views/Finance/Payments/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'payments.view',
                },
            },
            {
                path: 'invoices',
                name: 'invoices',
                component: () => import( '../views/Finance/Invoices/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'payments.view',
                },
            },
            {
                path: 'expenses',
                name: 'expenses',
                component: () => import( '../views/Finance/Expenses/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'expenses.view',
                },
            },
            {
                path: 'trainers',
                name: 'trainers',
                component: () => import( '../views/Operations/Trainers/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'trainers.view',
                },
            },
            {
                path: 'classes',
                name: 'classes',
                component: () => import( '../views/Operations/Classes/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'classes.view',
                },
            },
            {
                path: 'equipment',
                name: 'equipment',
                component: () => import( '../views/Operations/Equipment/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'equipment.view',
                },
            },
            {
                path: 'inventory',
                name: 'inventory',
                component: () => import( '../views/Operations/Inventory/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'inventory.view',
                },
            },
            {
                path: 'reports/revenue',
                name: 'reports_revenue',
                component: () => import( '../views/Reports/revenue.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'reports.view',
                },
            },
            {
                path: 'reports/members',
                name: 'reports_members',
                component: () => import( '../views/Reports/members.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'reports.view',
                },
            },
            {
                path: 'reports/attendance',
                name: 'reports_attendance',
                component: () => import( '../views/Reports/attendance.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'reports.view',
                },
            },
            {
                path: 'users',
                name: 'users',
                component: () => import( '../views/System/UserManagement/Users/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'users.view',
                },
            },
            {
                path: 'roles',
                name: 'roles',
                component: () => import( '../views/System/UserManagement/Roles/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'roles.view',
                },
            },
            {
                path: 'branches',
                name: 'branches',
                component: () => import( '../views/branches/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_branch',
                },
            },
            {
                path: 'branches/create',
                name: 'add_branch',
                component: () => import( '../views/branches/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_branch',
                },
            },
            {
                path: 'branches/:id/edit',
                name: 'edit_branch',
                component: () => import( '../views/branches/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_branch',
                },
            },
            {
                path: 'services',
                name: 'services',
                component: () => import( '../views/services/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_service',
                },
            },
            {
                path: 'services/create',
                name: 'add_service',
                component: () => import( '../views/services/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_service',
                },
            },
            {
                path: 'services/:id/edit',
                name: 'edit_service',
                component: () => import( '../views/services/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_service',
                },
            },
            {
                path: 'warehouses',
                name: 'warehouses',
                component: () => import( '../views/warehouses/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_warehouse',
                },
            },
            {
                path: 'warehouses/create',
                name: 'add_warehouse',
                component: () => import( '../views/warehouses/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_warehouse',
                },
            },
            {
                path: 'warehouses/:id/edit',
                name: 'edit_warehouse',
                component: () => import( '../views/warehouses/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_warehouse',
                },
            },
            {
                path: 'warehouses/:id/view',
                name: 'view_warehouse',
                component: () => import( '../views/warehouses/View.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_warehouse',
                },
            },
            {
                path: 'statuses',
                name: 'statuses',
                component: () => import( '../views/statuses/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_status',
                },
            },
            {
                path: 'statuses/create',
                name: 'add_status',
                component: () => import( '../views/statuses/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_status',
                },
            },
            {
                path: 'statuses/:id/edit',
                name: 'edit_status',
                component: () => import( '../views/statuses/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_status',
                },
            },
            {
                path: 'statuses/:id/view',
                name: 'view_status',
                component: () => import( '../views/statuses/View.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_status',
                },
            },
            {
                path: 'customers',
                name: 'customers',
                component: () => import( '../views/customers/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_customer',
                },
            },
            {
                path: 'customers/create',
                name: 'add_customer',
                component: () => import( '../views/customers/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_customer',
                },
            },
            {
                path: 'customers/:id/edit',
                name: 'edit_customer',
                component: () => import( '../views/customers/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_customer',
                },
            },
            {
                path: 'customers/:id/view',
                name: 'view_customer',
                component: () => import( '../views/customers/view.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_customer',
                },
            },
            {
                path: 'countries',
                name: 'countries',
                component: () => import( '../views/countries/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_country',
                },
            },
            {
                path: 'countries/create',
                name: 'add_country',
                component: () => import( '../views/countries/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_country',
                },
            },
            {
                path: 'countries/:id/edit',
                name: 'edit_country',
                component: () => import( '../views/countries/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_country',
                },
            },
            {
                path: 'states',
                name: 'states',
                component: () => import( '../views/states/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_state',
                },
            },
            {
                path: 'states/create',
                name: 'add_state',
                component: () => import( '../views/states/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_state',
                },
            },
            {
                path: 'states/:id/edit',
                name: 'edit_state',
                component: () => import( '../views/states/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_state',
                },
            },
            {
                path: 'cities',
                name: 'cities',
                component: () => import( '../views/cities/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_city',
                },
            },
            {
                path: 'cities/create',
                name: 'add_city',
                component: () => import( '../views/cities/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_city',
                },
            },
            {
                path: 'cities/:id/edit',
                name: 'edit_city',
                component: () => import( '../views/cities/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_city',
                },
            },
            {
                path: 'invoices',
                name: 'invoices',
                component: () => import( '../views/Finance/Invoices/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_invoice',
                },
            },
            {
                path: 'invoices/create',
                name: 'add_invoice',
                component: () => import( '../views/Finance/Invoices/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_invoice',
                },
            },
            {
                path: 'invoices/:id',
                name: 'view_invoice',
                component: () => import( '../views/Finance/Invoices/View.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_invoice',
                },
            },
            {
                path: 'invoices/:id/edit',
                name: 'edit_invoice',
                component: () => import( '../views/Finance/Invoices/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_invoice',
                },
            },
            {
                path: 'payments',
                name: 'payments',
                component: () => import( '../views/Finance/Payments/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_payment',
                },
            },
            {
                path: 'payments/create',
                name: 'add_payment',
                component: () => import( '../views/Finance/Payments/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_payment',
                },
            },
            {
                path: 'payments/:id/edit',
                name: 'edit_payment',
                component: () => import( '../views/Finance/Payments/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_payment',
                },
            },
            {
                path: 'payments/:id',
                name: 'view_payment',
                component: () => import( '../views/Finance/Payments/View.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_payment',
                },
            },
            {
                path: 'orders',
                name: 'orders',
                component: () => import( '../views/orders/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_order',
                },
            },
            {
                path: 'orders/create',
                name: 'add_order',
                component: () => import( '../views/orders/Create.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_order',
                },
            },
            {
                path: 'orders/:id/view',
                name: 'view_order',
                component: () => import( '../views/orders/View.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_order',
                },
            },
            {
                path: 'orders/:id/edit',
                name: 'edit_order',
                component: () => import( '../views/orders/Edit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_order',
                },
            },
            {
                path: 'stores',
                name: 'stores',
                component: () => import( '../views/stores/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_store',
                },
            },
            {
                path: 'stores/create',
                name: 'add_store',
                component: () => import( '../views/stores/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_store',
                },
            },
            {
                path: 'stores/:id/view',
                name: 'view_store',
                component: () => import( '../views/stores/View.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_store',
                },
            },
            {
                path: 'stores/:id/edit',
                name: 'edit_store',
                component: () => import( '../views/stores/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_store',
                },
            },
            {
                path: 'subscriptions',
                name: 'subscriptions',
                component: () => import( '../views/subscriptions/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_subscription',
                },
            },
            {
                path: 'subscriptions/create',
                name: 'add_subscription',
                component: () => import( '../views/subscriptions/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_subscription',
                },
            },
            {
                path: 'subscriptions/:id/view',
                name: 'view_subscription',
                component: () => import( '../views/subscriptions/View.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_subscription',
                },
            },
            {
                path: 'subscriptions/:id/edit',
                name: 'edit_subscription',
                component: () => import( '../views/subscriptions/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_subscription',
                },
            },
            {
                path: 'payment-methods',
                name: 'payment_methods',
                component: () => import( '../views/payment_methods/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_payment_method',
                },
            },
            {
                path: 'payment-methods/create',
                name: 'add_payment_method',
                component: () => import( '../views/payment_methods/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_payment_method',
                },
            },
            {
                path: 'payment-methods/:id/edit',
                name: 'edit_payment_method',
                component: () => import( '../views/payment_methods/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_payment_method',
                },
            },
            {
                path: 'payment-methods/:id',
                name: 'view_payment_method',
                component: () => import( '../views/payment_methods/view.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_payment_method',
                },
            },
            {
                path: 'advertisements',
                name: 'advertisements',
                component: () => import( '../views/advertisements/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_advertisement',
                },
            },
            {
                path: 'advertisements/create',
                name: 'add_advertisement',
                component: () => import( '../views/advertisements/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_advertisement',
                },
            },
            {
                path: 'advertisements/:id/edit',
                name: 'edit_advertisement',
                component: () => import( '../views/advertisements/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_advertisement',
                },
            },
            {
                path: 'reports',
                name: 'reports',
                children: [
                    {
                        path: 'safe-transaction',
                        name: 'reports.safe_transaction',
                        component: () => import( '../views/Reports/safe_transaction.vue'),
                        meta: {
                            requiresAuth: true,
                            permission: 'safe_transaction_report',
                        },
                    },
                    {
                        path: 'revenue',
                        name: 'reports.revenue',
                        component: () => import( '../views/Reports/revenue.vue'),
                        meta: {
                            requiresAuth: true,
                            permission: 'revenue_report',
                        },
                    },
                ],
            },
            {
                path: 'safe-transactions',
                name: 'safe-transactions',
                component: () => import( '../views/safe_transaction/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_safe_transaction',
                },
            },
            {
                path: 'safe-transactions/create',
                name: 'add_safe_transaction',
                component: () => import( '../views/safe_transaction/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_safe_transaction',
                },
            },
            {
                path: 'safe-transactions/:id/edit',
                name: 'edit_safe_transaction',
                component: () => import( '../views/safe_transaction/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_safe_transaction',
                },
            },
            {
                path: 'safe-transactions/:id',
                name: 'view_safe_transaction',
                component: () => import( '../views/safe_transaction/View.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_safe_transaction',
                },
            },
            {
                path: 'safes',
                name: 'safes',
                component: () => import( '../views/safes/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_safe',
                },
            },
            {
                path: 'safes/create',
                name: 'add_safe',
                component: () => import( '../views/safes/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_safe',
                },
            },
            {
                path: 'safes/:id/edit',
                name: 'edit_safe',
                component: () => import( '../views/safes/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_safe',
                },
            },
            {
                path: 'safe-accounts',
                name: 'safes-accounts',
                component: () => import( '../views/safe_accounts/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_safe_account',
                },
            },
            {
                path: 'safe-accounts/create',
                name: 'add_safe_account',
                component: () => import( '../views/safe_accounts/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_safe_account',
                },
            },
            {
                path: 'safe-accounts/:id/edit',
                name: 'edit_safe_account',
                component: () => import( '../views/safe_accounts/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_safe_account',
                },
            },
            {
                path: 'safe-transaction-categories',
                name: 'safe-transaction-categories',
                component: () => import( '../views/safe_transaction_categories/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_safe_transaction_category',
                },
            },
            {
                path: 'safe-transaction-categories/create',
                name: 'add_safe_transaction_category',
                component: () => import( '../views/safe_transaction_categories/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'add_safe_transaction_category',
                },
            },
            {
                path: 'safe-transaction-categories/:id/edit',
                name: 'edit_safe_transaction_category',
                component: () => import( '../views/safe_transaction_categories/CreateEdit.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'edit_safe_transaction_category',
                },
            },
            {
                path: 'localizations',
                name: 'localizations',
                component: () => import( '../views/translations/index.vue'),
                meta: {
                    requiresAuth: true,
                    permission: 'view_translation',
                },
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    linkExactActiveClass: 'active',
    routes,
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        } else {
            return {left: 0, top: 0};
        }
    },
});

router.beforeEach((to, from, next) => {
    const store = useAppStore();

    if (to?.meta?.layout == 'auth') {
        store.setMainLayout('auth');
    } else {
        store.setMainLayout('app');
    }

    // Auth checks
    if (to.meta.requiresAuth && !store.isAuthenticated) {
        // User not logged in → redirect to login
        return next({name: 'login'});
    }

    if (to.meta.guestOnly && store.isAuthenticated) {
        // User already logged in → redirect to home
        console.log('here')
        return next({name: 'dashboard'});
    }

    if (store.isAuthenticated && store.isTemplateDataFetched === false) {
        store.fetchTemplateDate();
    }

    next(true);
});
router.afterEach((to, from, next) => {
    appSetting.changeAnimation();
});
export default router;
