<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pemilus: Array,
});

function getStatusBadge(status) {
    switch (status) {
        case 'BERJALAN': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'SELESAI': return 'bg-gray-50 text-gray-600 border-gray-200';
        case 'DIPUBLIKASIKAN': return 'bg-blue-50 text-blue-700 border-blue-200';
        default: return 'bg-amber-50 text-amber-700 border-amber-200';
    }
}

function truncateHash(hash, len = 20) {
    if (!hash) return '—';
    if (hash.length <= len) return hash;
    return hash.substring(0, len) + '...';
}
</script>

<template>
    <Head title="Audit Publik - JUNED" />

    <div class="min-h-screen bg-juned-100 font-sans">
        <!-- Header -->
        <header class="bg-white border-b border-juned-200 shadow-sm">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8 flex items-center justify-between h-16">
                <Link href="/" class="text-xl font-semibold text-juned-800" style="letter-spacing: -0.025em;">
                    JUNED
                </Link>
                <nav class="flex items-center gap-6">
                    <Link href="/" class="text-sm font-medium text-juned-text hover:text-juned-800 transition-colors">Beranda</Link>
                    <span class="text-sm font-bold text-juned-800 border-b-2 border-juned-600 pb-4 pt-5">Audit Publik</span>
                </nav>
                <div class="flex items-center gap-3">
                    <Link :href="route('voter.login')" class="inline-flex items-center justify-center bg-juned-800 hover:bg-juned-700 text-white font-bold text-sm rounded-xl px-5 py-1.5 transition-all">
                        Masuk
                    </Link>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-[1200px] mx-auto px-6 lg:px-8 py-12">
            <!-- Page Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-bold text-juned-800">Audit Publik</h1>
                <p class="mt-2 text-base text-juned-text max-w-2xl">
                    Transparansi penuh untuk setiap pemilihan. Verifikasi integritas suara dan Merkle Tree tanpa memerlukan akun.
                </p>
            </div>

            <!-- Stats Overview -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
                <div class="bg-white border border-juned-200 rounded-xl p-6 text-center">
                    <p class="text-3xl font-semibold text-juned-800">{{ pemilus.length }}</p>
                    <p class="text-sm font-medium text-juned-text mt-1">Total Pemilihan</p>
                </div>
                <div class="bg-white border border-juned-200 rounded-xl p-6 text-center">
                    <p class="text-3xl font-semibold text-juned-800">{{ pemilus.reduce((sum, p) => sum + p.total_votes, 0) }}</p>
                    <p class="text-sm font-medium text-juned-text mt-1">Total Suara</p>
                </div>
                <div class="bg-white border border-juned-200 rounded-xl p-6 text-center">
                    <p class="text-3xl font-semibold text-emerald-600">{{ pemilus.reduce((sum, p) => sum + p.verified_votes, 0) }}</p>
                    <p class="text-sm font-medium text-juned-text mt-1">Suara Terverifikasi</p>
                </div>
            </div>

            <!-- Elections List -->
            <div class="space-y-4">
                <div v-for="pemilu in pemilus" :key="pemilu.id"
                    class="bg-white border border-juned-200 rounded-xl p-6 hover:shadow-md transition-shadow">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <!-- Left: Election Info -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h2 class="text-lg font-semibold text-juned-800">{{ pemilu.name }}</h2>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border"
                                    :class="getStatusBadge(pemilu.status)">
                                    {{ pemilu.status }}
                                </span>
                            </div>
                            <p v-if="pemilu.description" class="text-sm text-juned-text mb-3">{{ pemilu.description }}</p>

                            <!-- Merkle Root -->
                            <div v-if="pemilu.merkle_root" class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                                <code class="text-xs text-juned-text font-mono">Root: {{ truncateHash(pemilu.merkle_root) }}</code>
                            </div>
                        </div>

                        <!-- Right: Stats + Action -->
                        <div class="flex items-center gap-6">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <p class="text-lg font-semibold text-juned-800">{{ pemilu.total_votes }}</p>
                                    <p class="text-xs text-juned-text">Suara</p>
                                </div>
                                <div>
                                    <p class="text-lg font-semibold text-emerald-600">{{ pemilu.verified_votes }}</p>
                                    <p class="text-xs text-juned-text">Valid</p>
                                </div>
                                <div>
                                    <p class="text-lg font-semibold text-red-600">{{ pemilu.rejected_votes }}</p>
                                    <p class="text-xs text-juned-text">Ditolak</p>
                                </div>
                            </div>

                            <Link :href="route('public.audit.show', pemilu.id)"
                                class="inline-flex items-center justify-center bg-juned-100 hover:bg-juned-200 text-juned-800 font-semibold text-sm rounded-lg px-4 py-2 transition-all border border-juned-200">
                                Detail →
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-if="pemilus.length === 0" class="bg-white border border-juned-200 rounded-xl p-12 text-center">
                    <p class="text-juned-text">Belum ada pemilihan yang tersedia untuk diaudit.</p>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-juned-200 bg-white py-8 mt-12">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8 text-center">
                <p class="text-xs font-semibold text-juned-text">
                    © 2026 JUNED. Platform Demokrasi Digital Terpercaya. Audit publik tanpa memerlukan autentikasi.
                </p>
            </div>
        </footer>
    </div>
</template>
