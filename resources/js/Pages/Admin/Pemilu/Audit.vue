<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';

const props = defineProps({
    pemilu: Object,
    suaras: Array,
    stats: Object,
});

const verifyingAll = ref(false);
const auditingTree = ref(false);
const verifyingSingle = ref(null);
const verifyAllResult = ref(null);
const merkleAuditResult = ref(null);

// Local reactive copy of suaras for live updates
const votes = reactive([...props.suaras]);
const localStats = reactive({ ...props.stats });

function getXsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

/**
 * Verify a single vote.
 */
async function verifySingleVote(suaraId) {
    verifyingSingle.value = suaraId;

    try {
        const response = await fetch(route('admin.pemilu.verify-single', [props.pemilu.id, suaraId]), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Update local state
            const vote = votes.find(v => v.id === suaraId);
            if (vote) {
                vote.status = data.valid ? 'TERVERIFIKASI' : 'DITOLAK';
                vote.proof_status = data.status;
                vote.verified_at = new Date().toISOString().replace('T', ' ').substring(0, 19);
            }
            recalculateStats();
        }
    } catch (error) {
        console.error('Verification failed:', error);
    } finally {
        verifyingSingle.value = null;
    }
}

/**
 * Verify all votes in the election.
 */
async function verifyAllVotes() {
    verifyingAll.value = true;
    verifyAllResult.value = null;

    try {
        const response = await fetch(route('admin.pemilu.verify-all', props.pemilu.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (response.ok) {
            verifyAllResult.value = data;
            // Reload page to get fresh data
            window.location.reload();
        }
    } catch (error) {
        console.error('Bulk verification failed:', error);
    } finally {
        verifyingAll.value = false;
    }
}

/**
 * Audit Merkle Tree integrity.
 */
async function auditTree() {
    auditingTree.value = true;
    merkleAuditResult.value = null;

    try {
        const response = await fetch(route('admin.pemilu.audit-tree', props.pemilu.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-XSRF-TOKEN': getXsrfToken(),
            },
            credentials: 'same-origin',
        });

        const data = await response.json();

        if (response.ok) {
            merkleAuditResult.value = data;
        }
    } catch (error) {
        console.error('Merkle Tree audit failed:', error);
    } finally {
        auditingTree.value = false;
    }
}

function recalculateStats() {
    localStats.verified = votes.filter(v => v.status === 'TERVERIFIKASI').length;
    localStats.rejected = votes.filter(v => v.status === 'DITOLAK').length;
    localStats.pending = votes.filter(v => v.status === 'MASUK').length;
}

function truncateHash(hash, len = 16) {
    if (!hash) return '—';
    if (hash.length <= len) return hash;
    return hash.substring(0, len) + '...';
}

function getStatusBadgeClass(status) {
    switch (status) {
        case 'MASUK':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/30 shadow-amber-500/20';
        case 'TERVERIFIKASI':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        case 'DITOLAK':
            return 'bg-rose-500/10 text-red-600 border-rose-500/30 shadow-rose-500/20';
        default:
            return 'bg-gray-500/10 text-juned-text border-gray-500/30';
    }
}

function getProofBadgeClass(status) {
    switch (status) {
        case 'VALID':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        case 'TIDAK_VALID':
            return 'bg-rose-500/10 text-red-600 border-rose-500/30';
        case 'BELUM_DIVERIFIKASI':
        default:
            return 'bg-gray-500/10 text-juned-text border-gray-500/30';
    }
}
</script>

<template>
    <Head :title="`Audit: ${pemilu.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-juned-800 leading-tight">Audit & Verification</h2>
                    <p class="text-sm text-juned-text mt-1">{{ pemilu.name }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a :href="route('admin.export.audit.excel', pemilu.id)" class="inline-flex items-center gap-1.5 rounded-lg border border-juned-200 bg-white px-3 py-1.5 text-xs font-medium text-juned-700 hover:bg-juned-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Excel
                    </a>
                    <a :href="route('admin.export.audit.pdf', pemilu.id)" class="inline-flex items-center gap-1.5 rounded-lg border border-juned-200 bg-white px-3 py-1.5 text-xs font-medium text-juned-700 hover:bg-juned-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        PDF
                    </a>
                    <Link :href="route('admin.pemilu.show', pemilu.id)" class="text-sm text-juned-text hover:text-juned-800 transition-colors">
                        ← Back to Election
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white border border-juned-200 rounded-2xl p-6">
                        <div class="text-sm font-medium text-juned-text">Total Votes</div>
                        <div class="mt-1 text-3xl font-bold text-juned-800">{{ localStats.total }}</div>
                    </div>
                    <div class="bg-white border border-emerald-500/20 rounded-2xl p-6">
                        <div class="text-sm font-medium text-juned-text">Verified</div>
                        <div class="mt-1 text-3xl font-bold text-emerald-400">{{ localStats.verified }}</div>
                    </div>
                    <div class="bg-white border border-rose-500/20 rounded-2xl p-6">
                        <div class="text-sm font-medium text-juned-text">Rejected</div>
                        <div class="mt-1 text-3xl font-bold text-red-600">{{ localStats.rejected }}</div>
                    </div>
                    <div class="bg-white border border-amber-500/20 rounded-2xl p-6">
                        <div class="text-sm font-medium text-juned-text">Pending</div>
                        <div class="mt-1 text-3xl font-bold text-amber-400">{{ localStats.pending }}</div>
                    </div>
                </div>

                <!-- Actions Section -->
                <div class="bg-white border border-juned-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-juned-800 mb-4">Actions</h3>
                    <div class="flex flex-wrap gap-4">
                        <!-- Verify All Button -->
                        <button
                            @click="verifyAllVotes"
                            :disabled="verifyingAll || localStats.total === 0"
                            class="inline-flex items-center px-6 py-3 bg-juned-800 hover:bg-juned-700 text-white text-sm font-semibold rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="verifyingAll" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ verifyingAll ? 'Verifying...' : 'Verify All Votes' }}
                        </button>

                        <!-- Audit Merkle Tree Button -->
                        <button
                            @click="auditTree"
                            :disabled="auditingTree"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-sm font-semibold rounded-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="auditingTree" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            {{ auditingTree ? 'Auditing...' : 'Audit Merkle Tree' }}
                        </button>
                    </div>

                    <!-- Verify All Result -->
                    <div v-if="verifyAllResult" class="mt-4 rounded-xl bg-juned-100 border border-juned-200 p-4">
                        <h4 class="text-sm font-semibold text-juned-800 mb-2">Bulk Verification Result</h4>
                        <div class="grid grid-cols-4 gap-3 text-center">
                            <div>
                                <div class="text-lg font-bold text-juned-800">{{ verifyAllResult.total }}</div>
                                <div class="text-xs text-juned-text">Total</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-emerald-400">{{ verifyAllResult.valid }}</div>
                                <div class="text-xs text-juned-text">Valid</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-red-600">{{ verifyAllResult.invalid }}</div>
                                <div class="text-xs text-juned-text">Invalid</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold text-amber-400">{{ verifyAllResult.errors }}</div>
                                <div class="text-xs text-juned-text">Errors</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Merkle Tree Audit Result -->
                <div v-if="merkleAuditResult" class="bg-white border rounded-xl shadow-sm p-6"
                    :class="merkleAuditResult.valid ? 'border-emerald-500/30' : 'border-rose-500/30'">
                    <div class="flex items-center gap-3 mb-4">
                        <!-- Checkmark or X -->
                        <div v-if="merkleAuditResult.valid" class="flex items-center justify-center w-10 h-10 rounded-full bg-emerald-500/10 border border-emerald-500/30">
                            <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div v-else class="flex items-center justify-center w-10 h-10 rounded-full bg-rose-500/10 border border-rose-500/30">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold" :class="merkleAuditResult.valid ? 'text-emerald-400' : 'text-red-600'">
                                Merkle Tree {{ merkleAuditResult.valid ? 'Integrity Verified' : 'Integrity Failed' }}
                            </h3>
                            <p class="text-sm text-juned-text">
                                {{ merkleAuditResult.valid ? 'The computed root matches the stored root.' : merkleAuditResult.error || 'Root hash mismatch detected.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Hash comparison -->
                    <div v-if="merkleAuditResult.stored_root || merkleAuditResult.computed_root" class="space-y-3">
                        <div class="bg-juned-100 rounded-xl p-4 border border-juned-200 font-mono">
                            <div class="text-xs font-medium text-juned-text mb-1">Stored Root</div>
                            <code class="text-xs text-juned-500 break-all">{{ merkleAuditResult.stored_root || 'N/A' }}</code>
                        </div>
                        <div class="bg-juned-100 rounded-xl p-4 border border-juned-200 font-mono">
                            <div class="text-xs font-medium text-juned-text mb-1">Computed Root</div>
                            <code class="text-xs break-all" :class="merkleAuditResult.valid ? 'text-emerald-400' : 'text-red-600'">{{ merkleAuditResult.computed_root || 'N/A' }}</code>
                        </div>
                    </div>
                </div>

                <!-- Vote Table -->
                <div class="bg-white border border-juned-200 rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-juned-800 mb-4">Vote Records</h3>

                    <div v-if="votes.length > 0" class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-juned-200">
                                    <th class="text-left py-3 px-4 text-juned-text font-medium">#</th>
                                    <th class="text-left py-3 px-4 text-juned-text font-medium">Vote Hash</th>
                                    <th class="text-left py-3 px-4 text-juned-text font-medium">Nullifier</th>
                                    <th class="text-left py-3 px-4 text-juned-text font-medium">Status</th>
                                    <th class="text-left py-3 px-4 text-juned-text font-medium">Proof Status</th>
                                    <th class="text-left py-3 px-4 text-juned-text font-medium">Verified At</th>
                                    <th class="text-left py-3 px-4 text-juned-text font-medium">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(vote, index) in votes" :key="vote.id"
                                    class="border-b border-juned-200 hover:bg-juned-100/50 transition-colors">
                                    <td class="py-3 px-4 text-juned-text">{{ index + 1 }}</td>
                                    <td class="py-3 px-4">
                                        <code class="text-xs text-juned-500 font-mono">{{ truncateHash(vote.vote_hash) }}</code>
                                    </td>
                                    <td class="py-3 px-4">
                                        <code class="text-xs text-juned-500 font-mono">{{ truncateHash(vote.nullifier_hash) }}</code>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border shadow-sm"
                                            :class="getStatusBadgeClass(vote.status)">
                                            {{ vote.status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border"
                                            :class="getProofBadgeClass(vote.proof_status)">
                                            {{ vote.proof_status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-xs text-juned-text">
                                        {{ vote.verified_at || '—' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <button
                                            @click="verifySingleVote(vote.id)"
                                            :disabled="verifyingSingle === vote.id"
                                            class="inline-flex items-center px-3 py-1.5 bg-cyan-600/20 hover:bg-cyan-600/30 text-juned-500 text-xs font-medium rounded-lg border border-cyan-500/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <svg v-if="verifyingSingle === vote.id" class="animate-spin -ml-0.5 mr-1.5 h-3 w-3" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                            </svg>
                                            {{ verifyingSingle === vote.id ? 'Verifying...' : 'Verify' }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-juned-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-4 text-juned-text text-sm">No votes have been cast in this election yet.</p>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
