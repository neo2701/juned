<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    pemilu: Object,
    stats: Object,
    tallyResults: Array,
    totalVerified: Number,
    totalPending: Number,
});

import { computed } from 'vue';

const maxVotes = computed(() => {
    if (!props.tallyResults || props.tallyResults.length === 0) return 1;
    return Math.max(...props.tallyResults.map(r => r.votes), 1);
});

const winner = computed(() => {
    if (!props.tallyResults || props.tallyResults.length === 0) return null;
    const top = props.tallyResults[0];
    return top && top.votes > 0 ? top : null;
});

const showResults = computed(() => {
    // Show results if there are verified votes or election is finished/published
    return props.totalVerified > 0 || ['SELESAI', 'DIPUBLIKASIKAN'].includes(props.pemilu.status);
});

const generatingTree = ref(false);
const treeError = ref('');
const treeSuccess = ref('');
const statusUpdating = ref(false);

/**
 * Generate Merkle Tree for this election.
 */
async function generateMerkleTree() {
    generatingTree.value = true;
    treeError.value = '';
    treeSuccess.value = '';

    try {
        const response = await fetch(route('admin.pemilu.generate-tree', props.pemilu.id), {
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
            treeSuccess.value = `Merkle Tree generated successfully. Root: ${data.root?.substring(0, 20)}...`;
            // Reload page to refresh stats
            router.reload();
        } else {
            treeError.value = data.error || 'Failed to generate Merkle Tree.';
        }
    } catch (error) {
        treeError.value = 'Network error. Please try again.';
    } finally {
        generatingTree.value = false;
    }
}

/**
 * Update election status.
 */
function updateStatus(newStatus) {
    if (!confirm(`Are you sure you want to change the status to ${newStatus}?`)) {
        return;
    }

    statusUpdating.value = true;

    router.put(route('admin.pemilu.update', props.pemilu.id), {
        name: props.pemilu.name,
        description: props.pemilu.description,
        status: newStatus,
    }, {
        preserveScroll: true,
        onFinish: () => {
            statusUpdating.value = false;
        },
    });
}

function getXsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function getStatusColor(status) {
    switch (status) {
        case 'DRAFT': return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
        case 'BERJALAN': return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        case 'SELESAI': return 'bg-gray-500/10 text-gray-400 border-gray-500/30';
        default: return 'bg-gray-500/10 text-gray-400 border-gray-500/30';
    }
}
</script>

<template>
    <Head :title="`Election: ${pemilu.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-white leading-tight">{{ pemilu.name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ pemilu.description }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.pemilu.audit', pemilu.id)" class="text-sm text-violet-400 hover:text-violet-300 font-medium transition-colors">
                        Audit
                    </Link>
                    <Link :href="route('admin.pemilu.edit', pemilu.id)" class="text-sm text-cyan-400 hover:text-cyan-300 font-medium transition-colors">
                        Edit
                    </Link>
                    <Link :href="route('admin.pemilu.index')" class="text-sm text-gray-400 hover:text-gray-200 transition-colors">
                        ← Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Status & Controls -->
                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Election Status</h3>

                    <div class="flex items-center gap-4 mb-6">
                        <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold border"
                            :class="getStatusColor(pemilu.status)">
                            {{ pemilu.status }}
                        </span>

                        <!-- Status transition buttons -->
                        <div class="flex gap-2">
                            <button
                                v-if="pemilu.status === 'DRAFT'"
                                @click="updateStatus('BERJALAN')"
                                :disabled="statusUpdating"
                                class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded-xl transition disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Start Election
                            </button>

                            <button
                                v-if="pemilu.status === 'BERJALAN'"
                                @click="updateStatus('SELESAI')"
                                :disabled="statusUpdating"
                                class="inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-sm font-medium rounded-xl transition disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                                </svg>
                                End Election
                            </button>

                            <button
                                v-if="pemilu.status === 'SELESAI'"
                                @click="updateStatus('DRAFT')"
                                :disabled="statusUpdating"
                                class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white text-sm font-medium rounded-xl transition disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset to Draft
                            </button>
                        </div>
                    </div>

                    <!-- Status flow indicator -->
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-3 py-1.5 rounded-lg border transition-all" :class="pemilu.status === 'DRAFT' ? 'bg-amber-500/10 text-amber-400 border-amber-500/30 glow-cyan' : 'bg-gray-800/50 text-gray-500 border-gray-700/50'">DRAFT</span>
                        <span class="text-gray-600">→</span>
                        <span class="px-3 py-1.5 rounded-lg border transition-all" :class="pemilu.status === 'BERJALAN' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30 glow-emerald' : 'bg-gray-800/50 text-gray-500 border-gray-700/50'">BERJALAN</span>
                        <span class="text-gray-600">→</span>
                        <span class="px-3 py-1.5 rounded-lg border transition-all" :class="pemilu.status === 'SELESAI' ? 'bg-gray-500/10 text-gray-300 border-gray-500/30' : 'bg-gray-800/50 text-gray-500 border-gray-700/50'">SELESAI</span>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-6">
                        <div class="text-sm font-medium text-gray-500">Registered Voters</div>
                        <div class="mt-1 text-3xl font-bold text-white">{{ stats.voterCount }}</div>
                    </div>
                    <div class="bg-gray-900/60 backdrop-blur-xl border border-cyan-500/20 rounded-2xl p-6">
                        <div class="text-sm font-medium text-gray-500">Total Votes Cast</div>
                        <div class="mt-1 text-3xl font-bold text-cyan-400">{{ stats.totalVotes }}</div>
                    </div>
                    <div class="bg-gray-900/60 backdrop-blur-xl border border-emerald-500/20 rounded-2xl p-6">
                        <div class="text-sm font-medium text-gray-500">Turnout</div>
                        <div class="mt-1 text-3xl font-bold text-emerald-400">{{ stats.turnoutPercentage }}%</div>
                    </div>
                    <div class="bg-gray-900/60 backdrop-blur-xl border border-violet-500/20 rounded-2xl p-6">
                        <div class="text-sm font-medium text-gray-500">Candidates</div>
                        <div class="mt-1 text-3xl font-bold text-violet-400">{{ pemilu.kandidats?.length || 0 }}</div>
                    </div>
                    <div class="bg-gray-900/60 backdrop-blur-xl border border-amber-500/20 rounded-2xl p-6">
                        <div class="text-sm font-medium text-gray-500">Leading Candidate</div>
                        <div v-if="winner" class="mt-1">
                            <div class="text-lg font-bold text-amber-400 flex items-center gap-1">
                                👑 #{{ winner.nomor_urut }}
                            </div>
                            <div class="text-xs text-gray-400 truncate">{{ winner.nama_kandidat || 'Candidate #' + winner.nomor_urut }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">{{ winner.votes }} votes</div>
                        </div>
                        <div v-else class="mt-1 text-sm text-gray-500">No votes yet</div>
                    </div>
                </div>

                <!-- Vote Results -->
                <div v-if="showResults" class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Vote Results
                        <span class="ml-auto text-sm font-normal text-gray-500">
                            {{ totalVerified }} verified votes
                            <span v-if="totalPending > 0" class="text-amber-400 ml-2">• {{ totalPending }} pending</span>
                        </span>
                    </h3>

                    <div v-if="tallyResults && tallyResults.length > 0 && totalVerified > 0" class="space-y-4">
                        <div v-for="(result, index) in tallyResults" :key="result.id"
                            class="p-4 rounded-xl border transition-all"
                            :class="index === 0 && result.votes > 0 ? 'border-amber-500/30 bg-amber-500/5' : 'border-gray-700/50 bg-gray-800/30'">
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sm font-bold"
                                        :class="index === 0 && result.votes > 0 ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-gray-700/50 text-gray-400 border border-gray-600/50'">
                                        #{{ result.nomor_urut }}
                                    </span>
                                    <span class="text-white font-medium">{{ result.nama_kandidat || 'Candidate #' + result.nomor_urut }}</span>
                                    <span v-if="index === 0 && result.votes > 0" class="text-amber-400 text-lg">👑</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-cyan-400 font-bold">{{ result.votes }}</span>
                                    <span class="text-gray-500 text-sm ml-1">votes</span>
                                    <span v-if="totalVerified > 0" class="text-gray-500 text-sm ml-2">({{ Math.round(result.votes / totalVerified * 100) }}%)</span>
                                </div>
                            </div>
                            <!-- Progress bar -->
                            <div class="w-full bg-gray-800 rounded-full h-3 overflow-hidden">
                                <div class="h-3 rounded-full transition-all duration-500"
                                    :class="index === 0 && result.votes > 0 ? 'bg-gradient-to-r from-amber-500 to-amber-400' : 'bg-gradient-to-r from-cyan-500 to-violet-500'"
                                    :style="{ width: (result.votes / maxVotes * 100) + '%' }"></div>
                            </div>
                            <!-- Pending indicator -->
                            <div v-if="result.pending > 0" class="mt-1 text-xs text-amber-400/70">
                                + {{ result.pending }} awaiting verification
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <p class="text-gray-500 text-sm">No verified votes yet.</p>
                        <p v-if="totalPending > 0" class="text-amber-400/70 text-xs mt-1">{{ totalPending }} votes awaiting verification</p>
                    </div>
                </div>

                <!-- Merkle Tree Section -->
                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        Merkle Tree (zk-SNARK)
                    </h3>

                    <div v-if="stats.merkleTree" class="space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                {{ stats.merkleTree.status }}
                            </span>
                            <span class="text-sm text-gray-500">Generated at {{ stats.merkleTree.created_at }}</span>
                        </div>
                        <div class="bg-gray-800/80 rounded-xl p-4 border border-gray-700/50 font-mono">
                            <div class="text-xs font-medium text-gray-500 mb-1">Root Hash</div>
                            <code class="text-xs text-cyan-400 break-all">{{ stats.merkleTree.root_hash }}</code>
                        </div>
                        <div class="mt-3">
                            <button
                                @click="generateMerkleTree"
                                :disabled="generatingTree"
                                class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-sm font-medium rounded-xl transition disabled:opacity-50"
                            >
                                <svg v-if="generatingTree" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Regenerate Tree
                            </button>
                        </div>
                    </div>

                    <div v-else class="space-y-4">
                        <p class="text-sm text-gray-400">
                            The Merkle Tree has not been generated yet. Generate it to allow voters to create zero-knowledge proofs.
                        </p>
                        <p class="text-xs text-gray-600">
                            This will collect all {{ stats.voterCount }} voter commitments and build a Poseidon-based Merkle Tree (depth 10, max 1024 leaves).
                        </p>
                        <PrimaryButton
                            @click="generateMerkleTree"
                            :disabled="generatingTree || stats.voterCount === 0"
                        >
                            <svg v-if="generatingTree" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Generate Merkle Tree
                        </PrimaryButton>
                        <p v-if="stats.voterCount === 0" class="text-xs text-rose-400">No voters registered. Add voters first.</p>
                    </div>

                    <!-- Tree generation feedback -->
                    <div v-if="treeError" class="mt-4 rounded-xl bg-rose-500/10 border border-rose-500/30 p-4">
                        <p class="text-sm text-rose-400">{{ treeError }}</p>
                    </div>
                    <div v-if="treeSuccess" class="mt-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 p-4">
                        <p class="text-sm text-emerald-400">{{ treeSuccess }}</p>
                    </div>
                </div>

                <!-- Vote Status Breakdown -->
                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Vote Status Breakdown</h3>

                    <div v-if="stats.totalVotes > 0" class="space-y-4">
                        <!-- Progress bar -->
                        <div>
                            <div class="flex justify-between text-sm text-gray-400 mb-2">
                                <span>Turnout</span>
                                <span>{{ stats.totalVotes }} / {{ stats.voterCount }} voters</span>
                            </div>
                            <div class="w-full bg-gray-800 rounded-full h-3 overflow-hidden">
                                <div class="bg-gradient-to-r from-cyan-500 to-violet-500 h-3 rounded-full transition-all" :style="{ width: stats.turnoutPercentage + '%' }"></div>
                            </div>
                        </div>

                        <!-- Status counts -->
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div class="text-center p-4 bg-cyan-500/5 border border-cyan-500/20 rounded-xl">
                                <div class="text-2xl font-bold text-cyan-400">{{ stats.votesByStatus['MASUK'] || 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">MASUK (Received)</div>
                            </div>
                            <div class="text-center p-4 bg-emerald-500/5 border border-emerald-500/20 rounded-xl">
                                <div class="text-2xl font-bold text-emerald-400">{{ stats.votesByStatus['TERVERIFIKASI'] || 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">TERVERIFIKASI (Verified)</div>
                            </div>
                            <div class="text-center p-4 bg-rose-500/5 border border-rose-500/20 rounded-xl">
                                <div class="text-2xl font-bold text-rose-400">{{ stats.votesByStatus['DITOLAK'] || 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">DITOLAK (Rejected)</div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <p class="text-gray-500 text-sm">No votes have been cast yet.</p>
                    </div>
                </div>

                <!-- Candidates -->
                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-white">Candidates</h3>
                        <Link :href="route('admin.pemilu.kandidat.index', pemilu.id)" class="text-sm text-cyan-400 hover:text-cyan-300 font-medium transition-colors">
                            Manage →
                        </Link>
                    </div>

                    <div v-if="pemilu.kandidats && pemilu.kandidats.length > 0" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="kandidat in pemilu.kandidats" :key="kandidat.id" class="border border-gray-700/50 rounded-xl p-4 bg-gray-800/30 hover:border-cyan-500/30 transition-all">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-cyan-500/20 to-violet-500/20 text-cyan-400 font-bold border border-cyan-500/20">
                                    {{ kandidat.nomor_urut }}
                                </span>
                                <div>
                                    <div class="text-sm font-medium text-white">Candidate #{{ kandidat.nomor_urut }}</div>
                                    <div v-if="kandidat.visi_misi" class="text-xs text-gray-500 truncate max-w-[200px]">{{ kandidat.visi_misi }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-6">
                        <p class="text-gray-500 text-sm">No candidates registered.</p>
                        <Link :href="route('admin.pemilu.kandidat.create', pemilu.id)" class="text-sm text-cyan-400 hover:text-cyan-300 mt-2 inline-block transition-colors">
                            + Add Candidate
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
