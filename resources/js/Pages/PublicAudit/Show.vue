<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';

const props = defineProps({
    pemilu: Object,
    suaras: Array,
    stats: Object,
    merkleInfo: Object,
});

const searchQuery = ref('');
const currentPage = ref(1);
const perPage = 20;

const filteredVotes = computed(() => {
    if (!searchQuery.value) return props.suaras;
    const q = searchQuery.value.toLowerCase();
    return props.suaras.filter(v =>
        (v.vote_hash && v.vote_hash.toLowerCase().includes(q)) ||
        (v.nullifier_hash && v.nullifier_hash.toLowerCase().includes(q))
    );
});

const paginatedVotes = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredVotes.value.slice(start, start + perPage);
});

const totalPages = computed(() => Math.ceil(filteredVotes.value.length / perPage));

function truncateHash(hash, len = 16) {
    if (!hash) return '—';
    if (hash.length <= len) return hash;
    return hash.substring(0, len) + '...';
}

function getStatusBadge(status) {
    switch (status) {
        case 'TERVERIFIKASI': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'DITOLAK': return 'bg-red-50 text-red-700 border-red-200';
        case 'MASUK': return 'bg-amber-50 text-amber-700 border-amber-200';
        default: return 'bg-gray-50 text-gray-600 border-gray-200';
    }
}

function getProofBadge(status) {
    switch (status) {
        case 'VALID': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'TIDAK_VALID': return 'bg-red-50 text-red-700 border-red-200';
        default: return 'bg-gray-50 text-gray-500 border-gray-200';
    }
}

const verificationRate = computed(() => {
    if (props.stats.total === 0) return 0;
    return Math.round((props.stats.verified / props.stats.total) * 100);
});
</script>

<template>
    <Head :title="`Audit: ${pemilu.name} - JUNED`" />

    <div class="min-h-screen bg-juned-100 font-sans">
        <!-- Header -->
        <PublicHeader active="audit" />

        <!-- Main Content -->
        <main class="max-w-[1200px] mx-auto px-6 lg:px-8 py-12">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <Link :href="route('public.audit.index')" class="text-sm text-juned-text hover:text-juned-800 transition-colors">
                    ← Kembali ke Daftar Audit
                </Link>
            </div>

            <!-- Election Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-juned-800">{{ pemilu.name }}</h1>
                <p v-if="pemilu.description" class="mt-2 text-base text-juned-text">{{ pemilu.description }}</p>
                <div class="mt-3 flex items-center gap-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border"
                        :class="{
                            'bg-emerald-50 text-emerald-700 border-emerald-200': pemilu.status === 'BERJALAN',
                            'bg-gray-50 text-gray-600 border-gray-200': pemilu.status === 'SELESAI',
                            'bg-amber-50 text-amber-700 border-amber-200': pemilu.status === 'DRAFT',
                        }">
                        {{ pemilu.status }}
                    </span>
                    <span v-if="pemilu.tahun" class="text-sm text-juned-text">Tahun {{ pemilu.tahun }}</span>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="bg-white border border-juned-200 rounded-xl p-5">
                    <p class="text-2xl font-bold text-juned-800">{{ stats.total }}</p>
                    <p class="text-xs font-medium text-juned-text mt-1">Total Suara</p>
                </div>
                <div class="bg-white border border-emerald-200 rounded-xl p-5">
                    <p class="text-2xl font-bold text-emerald-600">{{ stats.verified }}</p>
                    <p class="text-xs font-medium text-juned-text mt-1">Terverifikasi</p>
                </div>
                <div class="bg-white border border-red-200 rounded-xl p-5">
                    <p class="text-2xl font-bold text-red-600">{{ stats.rejected }}</p>
                    <p class="text-xs font-medium text-juned-text mt-1">Ditolak</p>
                </div>
                <div class="bg-white border border-amber-200 rounded-xl p-5">
                    <p class="text-2xl font-bold text-amber-600">{{ stats.pending }}</p>
                    <p class="text-xs font-medium text-juned-text mt-1">Menunggu</p>
                </div>
                <div class="bg-white border border-juned-200 rounded-xl p-5">
                    <p class="text-2xl font-bold text-juned-500">{{ verificationRate }}%</p>
                    <p class="text-xs font-medium text-juned-text mt-1">Tingkat Verifikasi</p>
                </div>
            </div>

            <!-- Merkle Tree Info -->
            <div v-if="merkleInfo" class="bg-white border border-juned-200 rounded-xl p-6 mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-juned-400/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-juned-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-juned-800">Merkle Tree</h3>
                        <p class="text-sm text-juned-text">Integritas data pemilih diverifikasi melalui Poseidon-based Merkle Tree</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div class="bg-juned-100 rounded-lg p-4">
                        <p class="text-xs font-semibold text-juned-text uppercase tracking-wide mb-1">Status</p>
                        <p class="text-sm font-bold text-emerald-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.403 12.652a3 3 0 000-5.304 3 3 0 00-3.75-3.751 3 3 0 00-5.305 0 3 3 0 00-3.751 3.75 3 3 0 000 5.305 3 3 0 003.75 3.751 3 3 0 005.305 0 3 3 0 003.751-3.75zm-2.546-4.46a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                            {{ merkleInfo.status }}
                        </p>
                    </div>
                    <div class="bg-juned-100 rounded-lg p-4">
                        <p class="text-xs font-semibold text-juned-text uppercase tracking-wide mb-1">Total Leaf</p>
                        <p class="text-sm font-bold text-juned-800">{{ merkleInfo.total_leaf }} pemilih</p>
                    </div>
                    <div class="bg-juned-100 rounded-lg p-4">
                        <p class="text-xs font-semibold text-juned-text uppercase tracking-wide mb-1">Dibuat</p>
                        <p class="text-sm font-bold text-juned-800">{{ merkleInfo.created_at }}</p>
                    </div>
                </div>

                <!-- Root Hash -->
                <div class="mt-4 bg-juned-100 rounded-lg p-4 font-mono">
                    <p class="text-xs font-semibold text-juned-text uppercase tracking-wide mb-1">Root Hash (Poseidon)</p>
                    <code class="text-xs text-juned-500 break-all select-all">{{ merkleInfo.root_hash }}</code>
                </div>
            </div>

            <div v-else class="bg-white border border-amber-200 rounded-xl p-6 mb-8">
                <p class="text-sm text-amber-700">Merkle Tree belum di-generate untuk pemilihan ini.</p>
            </div>

            <!-- Vote Records -->
            <div class="bg-white border border-juned-200 rounded-xl shadow-sm">
                <div class="p-6 border-b border-juned-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <h3 class="text-lg font-semibold text-juned-800">Catatan Suara</h3>
                        <!-- Search -->
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari hash suara atau nullifier..."
                                class="w-full sm:w-72 bg-juned-100 border border-juned-200 rounded-lg px-4 py-2 text-sm text-juned-dark placeholder-juned-text/50 focus:border-juned-500 focus:ring-juned-500/20"
                                @input="currentPage = 1"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="paginatedVotes.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-juned-200 bg-juned-100/50">
                                <th class="text-left py-3 px-4 text-xs font-semibold text-juned-text uppercase tracking-wide">#</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-juned-text uppercase tracking-wide">Vote Hash</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-juned-text uppercase tracking-wide">Nullifier</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-juned-text uppercase tracking-wide">Status</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-juned-text uppercase tracking-wide">Proof</th>
                                <th class="text-left py-3 px-4 text-xs font-semibold text-juned-text uppercase tracking-wide">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(vote, index) in paginatedVotes" :key="vote.id"
                                class="border-b border-juned-200/50 hover:bg-juned-100/30 transition-colors">
                                <td class="py-3 px-4 text-juned-text">{{ (currentPage - 1) * perPage + index + 1 }}</td>
                                <td class="py-3 px-4">
                                    <code class="text-xs text-juned-500 font-mono" :title="vote.vote_hash">{{ truncateHash(vote.vote_hash) }}</code>
                                </td>
                                <td class="py-3 px-4">
                                    <code class="text-xs text-juned-500 font-mono" :title="vote.nullifier_hash">{{ truncateHash(vote.nullifier_hash) }}</code>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold border"
                                        :class="getStatusBadge(vote.status)">
                                        {{ vote.status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold border"
                                        :class="getProofBadge(vote.proof_status)">
                                        {{ vote.proof_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-xs text-juned-text">
                                    {{ vote.created_at || '—' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-12 text-center">
                    <p class="text-juned-text text-sm">
                        {{ searchQuery ? 'Tidak ditemukan suara yang cocok dengan pencarian.' : 'Belum ada suara yang tercatat.' }}
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="p-4 border-t border-juned-200 flex items-center justify-between">
                    <p class="text-xs text-juned-text">
                        Menampilkan {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredVotes.length) }} dari {{ filteredVotes.length }} suara
                    </p>
                    <div class="flex gap-1">
                        <button
                            @click="currentPage = Math.max(1, currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="px-3 py-1 text-sm border border-juned-200 rounded-lg text-juned-text hover:bg-juned-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            ←
                        </button>
                        <span class="px-3 py-1 text-sm text-juned-800 font-medium">{{ currentPage }} / {{ totalPages }}</span>
                        <button
                            @click="currentPage = Math.min(totalPages, currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="px-3 py-1 text-sm border border-juned-200 rounded-lg text-juned-text hover:bg-juned-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            →
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-juned-200 bg-white py-8 mt-12">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8 text-center">
                <p class="text-xs font-semibold text-juned-text">
                    © 2026 JUNED. Platform Demokrasi Digital Terpercaya. Data bersifat publik dan anonim.
                </p>
            </div>
        </footer>
    </div>
</template>
