<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    stats: Object,
    recentElections: Array,
    recentVotes: Array,
});

// Animated counters
const animatedStats = ref({
    totalPemilu: 0,
    activePemilu: 0,
    totalPemilih: 0,
    totalSuara: 0,
    verifiedSuara: 0,
    turnoutPercentage: 0,
});

function animateValue(key, target, duration = 1000) {
    const start = 0;
    const startTime = performance.now();
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        animatedStats.value[key] = Math.round(start + (target - start) * eased);
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

onMounted(() => {
    animateValue('totalPemilu', props.stats.totalPemilu);
    animateValue('activePemilu', props.stats.activePemilu);
    animateValue('totalPemilih', props.stats.totalPemilih);
    animateValue('totalSuara', props.stats.totalSuara);
    animateValue('verifiedSuara', props.stats.verifiedSuara);
    animateValue('turnoutPercentage', props.stats.turnoutPercentage, 1200);
});

const verificationRate = computed(() => {
    if (props.stats.totalSuara === 0) return 0;
    return Math.round((props.stats.verifiedSuara / props.stats.totalSuara) * 100);
});

function statusColor(status) {
    const colors = {
        DRAFT: 'bg-gray-100 text-gray-700',
        BERJALAN: 'bg-emerald-100 text-emerald-700',
        SELESAI: 'bg-blue-100 text-blue-700',
        DIPUBLIKASIKAN: 'bg-purple-100 text-purple-700',
        MASUK: 'bg-yellow-100 text-yellow-700',
        TERVERIFIKASI: 'bg-emerald-100 text-emerald-700',
        DITOLAK: 'bg-red-100 text-red-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
}

function statusLabel(status) {
    const labels = {
        DRAFT: 'Draft',
        BERJALAN: 'Active',
        SELESAI: 'Completed',
        DIPUBLIKASIKAN: 'Published',
        MASUK: 'Pending',
        TERVERIFIKASI: 'Verified',
        DITOLAK: 'Rejected',
    };
    return labels[status] || status;
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-tight text-juned-800">
                        Dashboard
                    </h2>
                    <p class="mt-1 text-sm text-juned-text">
                        Overview of your e-voting system
                    </p>
                </div>
                <Link
                    :href="route('admin.pemilu.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-juned-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-juned-800 focus:outline-none focus:ring-2 focus:ring-juned-500 focus:ring-offset-2"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Election
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Elections -->
                    <div class="group relative overflow-hidden rounded-xl border border-juned-200 bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-juned-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-juned-text">Total Elections</p>
                                <p class="mt-2 text-3xl font-bold text-juned-800">{{ animatedStats.totalPemilu }}</p>
                                <p class="mt-1 text-xs text-juned-text">
                                    <span class="inline-flex items-center gap-1 text-emerald-600 font-medium">
                                        {{ stats.activePemilu }} active
                                    </span>
                                </p>
                            </div>
                            <div class="rounded-xl bg-juned-700/10 p-3">
                                <svg class="h-6 w-6 text-juned-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Voters -->
                    <div class="group relative overflow-hidden rounded-xl border border-juned-200 bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-juned-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-juned-text">Registered Voters</p>
                                <p class="mt-2 text-3xl font-bold text-juned-800">{{ animatedStats.totalPemilih }}</p>
                                <p class="mt-1 text-xs text-juned-text">
                                    <span class="inline-flex items-center gap-1 text-juned-700 font-medium">
                                        {{ stats.turnoutPercentage }}% turnout
                                    </span>
                                </p>
                            </div>
                            <div class="rounded-xl bg-blue-50 p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Votes -->
                    <div class="group relative overflow-hidden rounded-xl border border-juned-200 bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-juned-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-juned-text">Total Votes Cast</p>
                                <p class="mt-2 text-3xl font-bold text-juned-800">{{ animatedStats.totalSuara }}</p>
                                <p class="mt-1 text-xs text-juned-text">
                                    <span class="inline-flex items-center gap-1 text-emerald-600 font-medium">
                                        {{ stats.verifiedSuara }} verified
                                    </span>
                                    <span class="mx-1">·</span>
                                    <span class="text-yellow-600 font-medium">{{ stats.pendingSuara }} pending</span>
                                </p>
                            </div>
                            <div class="rounded-xl bg-emerald-50 p-3">
                                <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Verification Rate -->
                    <div class="group relative overflow-hidden rounded-xl border border-juned-200 bg-white p-6 shadow-sm transition-all hover:shadow-md hover:border-juned-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-juned-text">Verification Rate</p>
                                <p class="mt-2 text-3xl font-bold text-juned-800">{{ verificationRate }}%</p>
                                <div class="mt-2 h-1.5 w-24 rounded-full bg-juned-100 overflow-hidden">
                                    <div
                                        class="h-full rounded-full bg-juned-700 transition-all duration-1000"
                                        :style="{ width: verificationRate + '%' }"
                                    ></div>
                                </div>
                            </div>
                            <div class="rounded-xl bg-purple-50 p-3">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Recent Elections -->
                    <div class="lg:col-span-2 rounded-xl border border-juned-200 bg-white shadow-sm">
                        <div class="flex items-center justify-between border-b border-juned-200 px-6 py-4">
                            <h3 class="text-base font-semibold text-juned-800">Recent Elections</h3>
                            <Link
                                :href="route('admin.pemilu.index')"
                                class="text-sm font-medium text-juned-700 hover:text-juned-800 transition"
                            >
                                View all →
                            </Link>
                        </div>
                        <div class="divide-y divide-juned-100">
                            <div
                                v-for="election in recentElections"
                                :key="election.id"
                                class="flex items-center justify-between px-6 py-4 hover:bg-juned-100/50 transition-colors"
                            >
                                <div class="min-w-0 flex-1">
                                    <Link
                                        :href="route('admin.pemilu.show', election.id)"
                                        class="text-sm font-medium text-juned-800 hover:text-juned-700 truncate block"
                                    >
                                        {{ election.name }}
                                    </Link>
                                    <div class="mt-1 flex items-center gap-3 text-xs text-juned-text">
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ election.kandidats_count }} candidates
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            {{ election.suaras_count }} votes
                                        </span>
                                        <span v-if="election.tanggal_mulai" class="inline-flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            {{ election.tanggal_mulai }}
                                        </span>
                                    </div>
                                </div>
                                <span :class="[statusColor(election.status), 'ml-4 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium']">
                                    {{ statusLabel(election.status) }}
                                </span>
                            </div>
                            <div v-if="recentElections.length === 0" class="px-6 py-8 text-center text-sm text-juned-text">
                                No elections yet. Create your first election to get started.
                            </div>
                        </div>
                    </div>

                    <!-- Recent Votes Activity -->
                    <div class="rounded-xl border border-juned-200 bg-white shadow-sm">
                        <div class="border-b border-juned-200 px-6 py-4">
                            <h3 class="text-base font-semibold text-juned-800">Recent Activity</h3>
                            <p class="mt-0.5 text-xs text-juned-text">Latest vote submissions</p>
                        </div>
                        <div class="divide-y divide-juned-100 max-h-[400px] overflow-y-auto">
                            <div
                                v-for="vote in recentVotes"
                                :key="vote.id"
                                class="px-6 py-3 hover:bg-juned-100/50 transition-colors"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-juned-800 truncate">
                                            {{ vote.pemilu_name || 'Unknown Election' }}
                                        </p>
                                        <p class="text-xs text-juned-text mt-0.5">{{ vote.waktu_suara }}</p>
                                    </div>
                                    <span :class="[statusColor(vote.status), 'ml-3 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium']">
                                        {{ statusLabel(vote.status) }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="recentVotes.length === 0" class="px-6 py-8 text-center text-sm text-juned-text">
                                No votes recorded yet.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-base font-semibold text-juned-800 mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <Link
                            :href="route('admin.pemilu.create')"
                            class="group flex items-center gap-4 rounded-xl border border-juned-200 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-juned-300"
                        >
                            <div class="rounded-lg bg-juned-700/10 p-2.5 group-hover:bg-juned-700/20 transition">
                                <svg class="h-5 w-5 text-juned-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-juned-800">Create Election</p>
                                <p class="text-xs text-juned-text">Set up a new election</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.pemilih.index')"
                            class="group flex items-center gap-4 rounded-xl border border-juned-200 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-juned-300"
                        >
                            <div class="rounded-lg bg-blue-50 p-2.5 group-hover:bg-blue-100 transition">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-juned-800">Manage Voters</p>
                                <p class="text-xs text-juned-text">Add or view voters</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('admin.pemilu.index')"
                            class="group flex items-center gap-4 rounded-xl border border-juned-200 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-juned-300"
                        >
                            <div class="rounded-lg bg-emerald-50 p-2.5 group-hover:bg-emerald-100 transition">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-juned-800">View Results</p>
                                <p class="text-xs text-juned-text">Check election results</p>
                            </div>
                        </Link>

                        <Link
                            :href="route('public.audit.index')"
                            class="group flex items-center gap-4 rounded-xl border border-juned-200 bg-white p-4 shadow-sm transition-all hover:shadow-md hover:border-juned-300"
                        >
                            <div class="rounded-lg bg-purple-50 p-2.5 group-hover:bg-purple-100 transition">
                                <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-juned-800">Public Audit</p>
                                <p class="text-xs text-juned-text">Transparency portal</p>
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- System Status -->
                <div class="mt-8 rounded-xl border border-juned-200 bg-white p-6 shadow-sm">
                    <h3 class="text-base font-semibold text-juned-800 mb-4">System Status</h3>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            <div>
                                <p class="text-sm font-medium text-juned-800">ZKP Engine</p>
                                <p class="text-xs text-juned-text">Operational</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            <div>
                                <p class="text-sm font-medium text-juned-800">Merkle Tree</p>
                                <p class="text-xs text-juned-text">Operational</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></div>
                            <div>
                                <p class="text-sm font-medium text-juned-800">Vote Verification</p>
                                <p class="text-xs text-juned-text">Operational</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
