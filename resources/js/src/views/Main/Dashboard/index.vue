<script setup>
import { ref, onMounted, computed } from "vue";
import { axiosRequest } from "../../utils/apiRequest.js";
import { translate, formatPrice } from "../../utils/functions.js";
import { useRouter } from "vue-router";
import VueApexCharts from "vue3-apexcharts";
import IconShoppingCart from "../../components/icon/icon-shopping-cart.vue";
import IconUser from "../../components/icon/icon-user.vue";
import IconDollarSign from "../../components/icon/icon-dollar-sign.vue";
import IconCreditCard from "../../components/icon/icon-credit-card.vue";
import IconMinus from "../../components/icon/icon-minus.vue";
import IconStore from "../../components/icon/icon-store.vue";
import IconTrendingUp from "../../components/icon/icon-trending-up.vue";
import IconTrendingDown from "../../components/icon/icon-trending-down.vue";
import IconArrowLeft from "../../components/icon/icon-arrow-left.vue";
import IconHorizontalDots from "../../components/icon/icon-horizontal-dots.vue";

const router = useRouter();

// Reactive data
const dashboardData = ref(null);
const loading = ref(true);
const error = ref(null);

// Computed properties
const stats = computed(() => dashboardData.value?.stats || {});
const recentOrders = computed(() => dashboardData.value?.recent_orders || []);
const recentCustomers = computed(() => dashboardData.value?.recent_customers || []);
const revenueChartData = computed(() => dashboardData.value?.revenue_chart || []);
const ordersChartData = computed(() => dashboardData.value?.orders_chart || []);
const topStores = computed(() => dashboardData.value?.top_stores || []);
const orderStatusDistribution = computed(() => dashboardData.value?.order_status_distribution || []);
const mainCurrency = computed(() => dashboardData.value?.main_currency || { symbol: '$', precision: 2 });

// Chart configurations
const revenueChartOptions = computed(() => ({
    chart: {
        height: 260,
        type: 'area',
        fontFamily: 'Inter, sans-serif',
        toolbar: { show: false },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800,
            animateGradually: {
                enabled: true,
                delay: 150
            }
        }
    },
    dataLabels: { enabled: false },
    stroke: {
        show: true,
        curve: 'smooth',
        width: 3,
        lineCap: 'round',
    },
    colors: ['#4361ee', '#805dca'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            inverseColors: false,
            opacityFrom: 0.4,
            opacityTo: 0.1,
            stops: [0, 90, 100],
        },
    },
    xaxis: {
        categories: revenueChartData.value.map(item => item.month),
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            style: {
                colors: '#6b7280',
                fontSize: '12px',
                fontFamily: 'Inter, sans-serif',
            }
        }
    },
    yaxis: {
        tickAmount: 5,
        labels: {
            formatter: (value) => `${mainCurrency.value.symbol}${value.toLocaleString()}`,
            style: {
                colors: '#6b7280',
                fontSize: '12px',
                fontFamily: 'Inter, sans-serif',
            }
        },
    },
    grid: {
        borderColor: '#f3f4f6',
        strokeDashArray: 5,
        xaxis: { lines: { show: true } },
        yaxis: { lines: { show: false } },
    },
    tooltip: {
        enabled: true,
        style: {
            fontSize: '12px',
            fontFamily: 'Inter, sans-serif',
        },
        y: {
            formatter: (val) => `${mainCurrency.value.symbol}${val.toLocaleString()}`
        },
    },
    legend: { 
        show: true,
        position: 'top',
        horizontalAlign: 'right',
        fontSize: '12px',
        fontFamily: 'Inter, sans-serif',
        markers: {
            width: 8,
            height: 8,
            radius: 2
        }
    }
}));

const revenueChartSeries = computed(() => [
    {
        name: translate('revenue'),
        data: revenueChartData.value.map(item => item.revenue)
    },
    {
        name: translate('target'),
        data: revenueChartData.value.map(item => item.revenue * 1.2)
    }
]);

const ordersChartOptions = computed(() => ({
    chart: {
        height: 200,
        type: 'bar',
        fontFamily: 'Inter, sans-serif',
        toolbar: { show: false },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800,
            animateGradually: {
                enabled: true,
                delay: 150
            }
        }
    },
    dataLabels: { enabled: false },
    colors: ['#10b981'],
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '60%',
            borderRadius: 8,
            borderRadiusApplication: 'end',
        },
    },
    xaxis: {
        categories: ordersChartData.value.map(item => item.day),
        labels: {
            style: {
                colors: '#6b7280',
                fontSize: '12px',
                fontFamily: 'Inter, sans-serif',
            }
        }
    },
    yaxis: {
        tickAmount: 5,
        labels: {
            style: {
                colors: '#6b7280',
                fontSize: '12px',
                fontFamily: 'Inter, sans-serif',
            }
        }
    },
    grid: {
        borderColor: '#f3f4f6',
        strokeDashArray: 5,
    },
    tooltip: {
        enabled: true,
        style: {
            fontSize: '12px',
            fontFamily: 'Inter, sans-serif',
        },
        y: {
            formatter: (val) => `${val} ${translate('orders')}`
        },
    },
    legend: { show: false }
}));

const ordersChartSeries = computed(() => [{
    name: translate('orders'),
    data: ordersChartData.value.map(item => item.orders)
}]);

// Prepare status data (sorted, capped to top 5 + Others)
const sortedStatuses = computed(() => {
    const items = (orderStatusDistribution.value || []).map(i => ({
        name: i.name,
        count: Number(i.count) || 0,
        color: i.color || '#4361ee',
    })).sort((a, b) => b.count - a.count);
    if (items.length <= 5) return items;
    const top = items.slice(0, 5);
    const othersCount = items.slice(5).reduce((a, b) => a + b.count, 0);
    if (othersCount > 0) top.push({ name: 'Others', count: othersCount, color: '#9ca3af' });
    return top;
});

const statusNames = computed(() => sortedStatuses.value.map(i => i.name));
const statusCounts = computed(() => sortedStatuses.value.map(i => i.count));
const statusColors = computed(() => sortedStatuses.value.map(i => i.color));
const totalStatusCount = computed(() => statusCounts.value.reduce((a, b) => a + b, 0));
const statusPercents = computed(() => {
    const total = totalStatusCount.value || 1;
    return statusCounts.value.map(c => Math.round((c / total) * 100));
});

// Radial bar (compact, clear) for status distribution
const salesByCategoryOptions = computed(() => ({
    chart: {
        height: 260,
        type: 'radialBar',
        fontFamily: 'Inter, sans-serif',
        toolbar: { show: false },
        animations: { enabled: true, speed: 500 },
    },
    colors: statusColors.value,
    labels: statusNames.value,
    plotOptions: {
        radialBar: {
            hollow: { size: '35%' },
            track: { background: '#f3f4f6' },
            dataLabels: {
                name: { fontSize: '12px' },
                value: { fontSize: '14px', formatter: (v) => `${Math.round(Number(v))}%` },
                total: {
                    show: true,
                    label: translate('total'),
                    formatter: () => `${totalStatusCount.value}`,
                },
            },
        },
    },
    legend: {
        show: true,
        position: 'bottom',
        fontSize: '12px',
        fontFamily: 'Inter, sans-serif',
        formatter: (seriesName, opts) => {
            const idx = opts.seriesIndex;
            const count = statusCounts.value[idx] || 0;
            const pct = statusPercents.value[idx] || 0;
            return `${seriesName}: ${count} (${pct}%)`;
        },
        markers: { width: 8, height: 8, radius: 2 },
    },
    tooltip: {
        enabled: true,
        y: {
            formatter: (val, opts) => {
                const idx = opts.seriesIndex;
                const count = statusCounts.value[idx] || 0;
                const pct = Math.round(Number(val)) || 0;
                return `${count} ${translate('orders')} (${pct}%)`;
            },
        },
    },
}));

const salesByCategorySeries = computed(() => statusPercents.value);

const totalOrdersOptions = computed(() => ({
    chart: {
        height: 290,
        type: 'line',
        fontFamily: 'Inter, sans-serif',
        toolbar: { show: false },
        sparkline: { enabled: true }
    },
    stroke: {
        curve: 'smooth',
        width: 3,
        colors: ['#10b981']
    },
    colors: ['#10b981'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            inverseColors: false,
            opacityFrom: 0.5,
            opacityTo: 0.1,
            stops: [0, 90, 100],
        },
    },
    tooltip: {
        enabled: false
    },
    grid: { show: false },
    xaxis: { labels: { show: false } },
    yaxis: { labels: { show: false } },
    markers: {
        size: 0
    }
}));

const totalOrdersSeries = computed(() => [{
    name: translate('orders'),
    data: [28, 40, 36, 52, 38, 60, 55, 67, 58, 74, 68, 82, 75, 89, 92, 88, 95, 102, 98, 105, 110, 115, 120, 125]
}]);

// Helpers for mini sparklines
const getLastN = (arr, n) => (arr || []).slice(-n);

const sparkBase = (color) => ({
    chart: {
        type: 'area',
        height: 70,
        sparkline: { enabled: true },
        toolbar: { show: false },
        animations: { enabled: true, speed: 400 }
    },
    stroke: { curve: 'smooth', width: 2 },
    fill: { type: 'gradient', gradient: { shadeIntensity: 0.8, opacityFrom: 0.35, opacityTo: 0.05 } },
    colors: [color],
    tooltip: { enabled: false }
});

const visitsSparkOptions = computed(() => sparkBase('#4361ee'));
const visitsSparkSeries = computed(() => [{ data: getLastN(ordersChartData.value.map(i => i.orders), 12) }]);

const paidOrdersSparkOptions = computed(() => sparkBase('#10b981'));
const paidOrdersSparkSeries = computed(() => [{ data: getLastN(ordersChartData.value.map(i => i.orders * 0.7 + 5), 12) }]);

const revenueSparkOptions = computed(() => sparkBase('#f59e0b'));
const revenueSparkSeries = computed(() => [{ data: getLastN(revenueChartData.value.map(i => i.revenue), 12) }]);

const storesSparkOptions = computed(() => sparkBase('#8b5cf6'));
const storesSparkSeries = computed(() => [{ data: getLastN(topStores.value.map((s) => s.orders_count), 5) }]);

// Methods
const fetchDashboardData = async () => {
    try {
        loading.value = true;
        error.value = null;
        const res = await axiosRequest.get('/admin/dashboard/data', {});
        dashboardData.value = res.data.data;
    } catch (err) {
        error.value = 'Failed to load dashboard data';
        console.error('Dashboard data fetch error:', err);
    } finally {
        loading.value = false;
    }
};

const navigateToOrders = () => {
    router.push({ name: 'orders' });
};

const navigateToCustomers = () => {
    router.push({ name: 'customers' });
};

const navigateToStores = () => {
    router.push({ name: 'stores' });
};

const viewOrder = (orderId) => {
    router.push({ name: 'view_order', params: { id: orderId } });
};

const viewCustomer = (customerId) => {
    router.push({ name: 'view_customer', params: { id: customerId } });
};

const getGrowthIcon = (growth) => {
    return growth >= 0 ? IconTrendingUp : IconTrendingDown;
};

const getGrowthColor = (growth) => {
    return growth >= 0 ? 'text-success' : 'text-danger';
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-warning-light dark:bg-warning text-warning dark:text-warning-light',
        processing: 'bg-info-light dark:bg-info text-info dark:text-info-light',
        shipped: 'bg-secondary-light dark:bg-secondary text-secondary dark:text-secondary-light',
        delivered: 'bg-success-light dark:bg-success text-success dark:text-success-light',
        cancelled: 'bg-danger-light dark:bg-danger text-danger dark:text-danger-light',
    };
    if (!status) {
        return 'bg-dark-light dark:bg-dark text-dark dark:text-dark-light';
    }
    const key = String(status).toLowerCase();
    return colors[key] || 'bg-dark-light dark:bg-dark text-dark dark:text-dark-light';
};

// Lifecycle
onMounted(() => {
    fetchDashboardData();
});
</script>

<template>
    <div>
        <!-- Breadcrumb -->
        <ul class="flex space-x-2 rtl:space-x-reverse">
            <li>
                <a href="javascript:;" class="text-primary hover:underline">{{ translate('dashboard') }}</a>
            </li>
        </ul>

        <div class="pt-4">
            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-white-dark dark:text-gray-400">{{ translate('loading_dashboard') }}</p>
                </div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="flex items-center justify-center py-20">
                <div class="text-center">
                    <div class="w-12 h-12 bg-danger-light dark:bg-danger rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-danger dark:text-danger-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white-dark dark:text-white mb-2">{{ translate('something_went_wrong') }}</h3>
                    <p class="text-white-dark dark:text-gray-400 mb-4">{{ error }}</p>
                    <button 
                        @click="fetchDashboardData" 
                        class="btn btn-primary"
                    >
                        {{ translate('try_again') }}
                    </button>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div v-else>
                <!-- Metric Cards (compact, with mini charts) - moved to top -->
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Total Visits -->
                    <div class="panel h-full">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="text-[11px] text-white-dark dark:text-gray-400">{{ translate('total_visits') }}</div>
                                <div class="text-xl font-bold mt-0.5 dark:text-white">{{ stats.total_customers?.toLocaleString() || 0 }}</div>
                            </div>
                            <div class="flex items-center px-1.5 py-0.5 rounded-full bg-primary/10 text-primary text-[11px] font-semibold">
                                <component :is="getGrowthIcon(stats.customers_growth)" class="w-3 h-3 mr-0.5" />
                                {{ stats.customers_growth || 0 }}%
                            </div>
                        </div>
                        <VueApexCharts :options="visitsSparkOptions" :series="visitsSparkSeries" height="70" />
                    </div>

                    <!-- Paid Orders -->
                    <div class="panel h-full">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="text-[11px] text-white-dark dark:text-gray-400">{{ translate('paid_orders') }}</div>
                                <div class="text-xl font-bold mt-0.5 dark:text-white">{{ stats.total_orders?.toLocaleString() || 0 }}</div>
                            </div>
                            <div class="flex items-center px-1.5 py-0.5 rounded-full bg-success/10 text-success text-[11px] font-semibold">
                                <component :is="getGrowthIcon(stats.orders_growth)" class="w-3 h-3 mr-0.5" />
                                {{ stats.orders_growth || 0 }}%
                            </div>
                        </div>
                        <VueApexCharts :options="paidOrdersSparkOptions" :series="paidOrdersSparkSeries" height="70" />
                    </div>

                    <!-- Total Revenue -->
                    <div class="panel h-full">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="text-[11px] text-white-dark dark:text-gray-400">{{ translate('total_revenue') }}</div>
                                <div class="text-xl font-bold mt-0.5 dark:text-white">{{ formatPrice(stats.total_revenue || 0, mainCurrency) }}</div>
                            </div>
                            <div class="flex items-center px-1.5 py-0.5 rounded-full bg-warning/10 text-warning text-[11px] font-semibold">
                                <component :is="getGrowthIcon(stats.revenue_growth)" class="w-3 h-3 mr-0.5" />
                                {{ stats.revenue_growth || 0 }}%
                            </div>
                        </div>
                        <VueApexCharts :options="revenueSparkOptions" :series="revenueSparkSeries" height="70" />
                    </div>

                    <!-- Total Stores -->
                    <div class="panel h-full">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="text-[11px] text-white-dark dark:text-gray-400">{{ translate('total_stores') }}</div>
                                <div class="text-xl font-bold mt-0.5 dark:text-white">{{ stats.total_stores?.toLocaleString() || 0 }}</div>
                            </div>
                            <div class="flex items-center px-1.5 py-0.5 rounded-full bg-secondary/10 text-secondary text-[11px] font-semibold">
                                {{ translate('active') }}
                            </div>
                        </div>
                        <VueApexCharts :options="storesSparkOptions" :series="storesSparkSeries" height="70" />
                    </div>
                </div>

                <!-- Main Charts Section -->
                <div class="grid xl:grid-cols-3 gap-4 mb-4">
                    <!-- Revenue Chart -->
                    <div class="panel h-full xl:col-span-2">
                        <div class="flex items-center justify-between dark:text-white-light mb-3">
                            <h5 class="font-semibold text-lg">{{ translate('revenue_overview') }}</h5>
                            <div class="dropdown ltr:ml-auto rtl:mr-auto">
                                <button type="button" class="hover:!text-primary">
                                    <IconHorizontalDots class="text-black/70 dark:text-white/70 hover:!text-primary" />
                                </button>
                            </div>
                        </div>
                        <p class="text-base dark:text-white-light/90">
                            {{ translate('total_revenue') }} 
                            <span class="text-primary ml-2">{{ formatPrice(stats.total_revenue || 0, mainCurrency) }}</span>
                        </p>
                        <div class="relative">
                            <div v-if="revenueChartData.length > 0" class="h-64">
                                <VueApexCharts 
                                    :options="revenueChartOptions" 
                                    :series="revenueChartSeries" 
                                    height="260"
                                    class="bg-white dark:bg-black rounded-lg overflow-hidden"
                                />
                            </div>
                            <div v-else class="h-64 flex items-center justify-center bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08] rounded-lg">
                                <div class="text-center text-white-dark dark:text-gray-400">
                                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <p>{{ translate('no_data_available') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Status Distribution -->
                    <div class="panel h-full">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="font-semibold text-lg dark:text-white-light">{{ translate('order_status_distribution') }}</h5>
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-primary rounded-full"></div>
                                <span class="text-xs text-white-dark dark:text-gray-400">{{ translate('orders') }}</span>
                            </div>
                        </div>
                        <div v-if="orderStatusDistribution.length > 0" class="flex items-center">
                            <VueApexCharts 
                                :options="salesByCategoryOptions" 
                                :series="salesByCategorySeries" 
                                height="320"
                                class="bg-white dark:bg-black rounded-lg overflow-hidden flex-1"
                            />
                        </div>
                        <div v-else class="text-center py-12 text-white-dark dark:text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <p>{{ translate('no_status_data') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-4">
                    <!-- Daily Orders -->
                    <div class="panel h-full sm:col-span-2 xl:col-span-1">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h5 class="font-semibold text-base dark:text-white-light">
                                    {{ translate('daily_orders') }}
                                </h5>
                                <p class="text-white-dark text-xs font-normal">{{ translate('last_7_days_overview') }}</p>
                            </div>
                            <div class="ltr:ml-auto rtl:mr-auto relative">
                                <div class="w-9 h-9 text-success bg-success-light dark:bg-success dark:text-success-light grid place-content-center rounded-full">
                                    <IconShoppingCart />
                                </div>
                            </div>
                        </div>
                        <div v-if="ordersChartData.length > 0">
                            <VueApexCharts 
                                :options="ordersChartOptions" 
                                :series="ordersChartSeries" 
                                height="160"
                                class="bg-white dark:bg-black rounded-lg overflow-hidden"
                            />
                        </div>
                        <div v-else class="h-40 flex items-center justify-center bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08] rounded-lg">
                            <div class="text-center text-white-dark dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <p class="text-sm">{{ translate('no_data_available') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="panel h-full">
                        <div class="flex items-center dark:text-white-light mb-3">
                            <h5 class="font-semibold text-lg">{{ translate('summary') }}</h5>
                            <div class="dropdown ltr:ml-auto rtl:mr-auto">
                                <button type="button" class="hover:!text-primary">
                                    <IconHorizontalDots class="w-5 h-5 text-black/70 dark:text-white/70 hover:!text-primary" />
                                </button>
                            </div>
                        </div>
                        <div class="space-y-9">
                            <!-- Revenue -->
                            <div class="flex items-center">
                                <div class="w-9 h-9 ltr:mr-3 rtl:ml-3">
                                    <div class="bg-primary-light dark:bg-primary text-primary dark:text-primary-light rounded-full w-9 h-9 grid place-content-center">
                                        <IconDollarSign />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex font-semibold text-white-dark mb-2">
                                        <h6>{{ translate('total_revenue') }}</h6>
                                        <p class="ltr:ml-auto rtl:mr-auto">{{ formatPrice(stats.total_revenue || 0, mainCurrency) }}</p>
                                    </div>
                                    <div class="rounded-full h-2 bg-dark-light dark:bg-[#1b2e4b] shadow">
                                        <div class="bg-gradient-to-r from-[#7579ff] to-[#b224ef] w-11/12 h-full rounded-full"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customers -->
                            <div class="flex items-center">
                                <div class="w-9 h-9 ltr:mr-3 rtl:ml-3">
                                    <div class="bg-success-light dark:bg-success text-success dark:text-success-light rounded-full w-9 h-9 grid place-content-center">
                                        <IconUser />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex font-semibold text-white-dark mb-2">
                                        <h6>{{ translate('total_customers') }}</h6>
                                        <p class="ltr:ml-auto rtl:mr-auto">{{ stats.total_customers?.toLocaleString() }}</p>
                                    </div>
                                    <div class="w-full rounded-full h-2 bg-dark-light dark:bg-[#1b2e4b] shadow">
                                        <div class="bg-gradient-to-r from-[#3cba92] to-[#0ba360] w-full h-full rounded-full" style="width: 75%"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Expenses -->
                            <div class="flex items-center">
                                <div class="w-9 h-9 ltr:mr-3 rtl:ml-3">
                                    <div class="bg-warning-light dark:bg-warning text-warning dark:text-warning-light rounded-full w-9 h-9 grid place-content-center">
                                        <IconMinus />
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex font-semibold text-white-dark mb-2">
                                        <h6>{{ translate('total_expenses') }}</h6>
                                        <p class="ltr:ml-auto rtl:mr-auto">{{ formatPrice(stats.total_expenses || 0, mainCurrency) }}</p>
                                    </div>
                                    <div class="w-full rounded-full h-2 bg-dark-light dark:bg-[#1b2e4b] shadow">
                                        <div class="bg-gradient-to-r from-[#f09819] to-[#ff5858] w-full h-full rounded-full" style="width: 60%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Orders Sparkline -->
                    <div class="panel h-full p-0">
                        <div class="flex items-center justify-between w-full p-4 absolute">
                            <div class="relative">
                                <div class="text-success dark:text-success-light bg-success-light dark:bg-success w-9 h-9 rounded-lg flex items-center justify-center">
                                    <IconShoppingCart />
                                </div>
                            </div>
                            <h5 class="font-semibold text-xl ltr:text-right rtl:text-left dark:text-white-light">
                                {{ stats.total_orders?.toLocaleString() }}
                                <span class="block text-sm font-normal">{{ translate('total_orders') }}</span>
                            </h5>
                        </div>
                        <VueApexCharts 
                            :options="totalOrdersOptions" 
                            :series="totalOrdersSeries" 
                            height="220"
                            class="bg-white dark:bg-black rounded-lg overflow-hidden"
                        />
                    </div>
                </div>

                <!-- duplicate metric cards removed -->

                <!-- Recent Activities -->
                <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4 mb-2">
                    <!-- Recent Orders -->
                    <div class="panel h-full sm:col-span-2 xl:col-span-1 pb-0">
                        <div class="flex items-center justify-between mb-5">
                            <h5 class="font-semibold text-lg dark:text-white-light">{{ translate('recent_orders') }}</h5>
                            <button 
                                @click="navigateToOrders" 
                                class="flex items-center space-x-1 text-primary hover:text-primary-dark transition-colors text-sm"
                            >
                                <span>{{ translate('view_all') }}</span>
                                <IconArrowLeft class="w-3 h-3 rotate-180" />
                            </button>
                        </div>
                        <div class="text-sm cursor-pointer">
                            <div v-if="recentOrders.length > 0">
                                <div 
                                    v-for="order in recentOrders.slice(0,6)" 
                                    :key="order.id"
                                    class="flex items-center py-2 relative group hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg px-2 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                    @click="viewOrder(order.id)"
                                >
                                    <div class="w-8 h-8 bg-primary-light dark:bg-primary rounded-lg flex items-center justify-center ltr:mr-2 rtl:ml-2">
                                        <IconShoppingCart class="w-4 h-4 text-primary dark:text-primary-light" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-sm text-white-dark dark:text-white">{{ order.order_number }}</div>
                                        <div class="text-xs text-white-dark dark:text-gray-400">{{ order.customer_name }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-semibold text-sm text-white-dark dark:text-white">{{ order.currency_symbol }}{{ order.total?.toLocaleString() }}</div>
                                        <span 
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                            :class="getStatusColor(order.status)"
                                        >
                                            {{ order.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-white-dark dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <p class="text-sm">{{ translate('no_recent_orders') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Customers -->
                    <div class="panel h-full">
                        <div class="flex items-center justify-between mb-5">
                            <h5 class="font-semibold text-lg dark:text-white-light">{{ translate('recent_customers') }}</h5>
                            <button 
                                @click="navigateToCustomers" 
                                class="flex items-center space-x-1 text-success hover:text-success-dark transition-colors text-sm"
                            >
                                <span>{{ translate('view_all') }}</span>
                                <IconArrowLeft class="w-3 h-3 rotate-180" />
                            </button>
                        </div>
                        <div class="text-sm cursor-pointer">
                            <div v-if="recentCustomers.length > 0">
                                <div 
                                    v-for="customer in recentCustomers.slice(0,6)" 
                                    :key="customer.id"
                                    class="flex items-center py-2 relative group hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg px-2 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                    @click="viewCustomer(customer.id)"
                                >
                                    <div class="w-8 h-8 bg-success-light dark:bg-success rounded-lg flex items-center justify-center ltr:mr-2 rtl:ml-2">
                                        <IconUser class="w-4 h-4 text-success dark:text-success-light" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-sm text-white-dark dark:text-white">{{ customer.name }}</div>
                                        <div class="text-xs text-white-dark dark:text-gray-400">{{ customer.email }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[11px] text-white-dark dark:text-gray-500">{{ customer.created_at }}</div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-white-dark dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                <p class="text-sm">{{ translate('no_recent_customers') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Top Stores -->
                    <div class="panel h-full">
                        <div class="flex items-center justify-between mb-5">
                            <h5 class="font-semibold text-lg dark:text-white-light">{{ translate('top_stores') }}</h5>
                            <button 
                                @click="navigateToStores" 
                                class="flex items-center space-x-1 text-secondary hover:text-secondary-dark transition-colors text-sm"
                            >
                                <span>{{ translate('view_all') }}</span>
                                <IconArrowLeft class="w-3 h-3 rotate-180" />
                            </button>
                        </div>
                        <div class="text-sm cursor-pointer">
                            <div v-if="topStores.length > 0">
                                <div 
                                    v-for="(store, index) in topStores" 
                                    :key="index"
                                    class="flex items-center py-4 relative group hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg px-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                                >
                                    <div class="w-10 h-10 bg-secondary-light dark:bg-secondary rounded-lg flex items-center justify-center ltr:mr-3 rtl:ml-3">
                                        <span class="text-secondary dark:text-secondary-light font-bold text-sm">{{ index + 1 }}</span>
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-white-dark dark:text-white">{{ store.name }}</div>
                                        <div class="text-xs text-white-dark dark:text-gray-400">{{ store.orders_count }} {{ translate('orders') }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-white-dark dark:text-white">
                                            {{ Math.round((store.orders_count / topStores[0].orders_count) * 100) }}%
                                        </div>
                                        <div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 mt-1">
                                            <div 
                                                class="bg-secondary h-1.5 rounded-full transition-all duration-300" 
                                                :style="{ width: `${(store.orders_count / topStores[0].orders_count) * 100}%` }"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12 text-white-dark dark:text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <p class="text-sm">{{ translate('no_store_data') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.group:hover .group-hover\:scale-105 {
    transform: scale(1.05);
}
</style>