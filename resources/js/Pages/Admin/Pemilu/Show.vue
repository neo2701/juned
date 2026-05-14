<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    pemilu: Object,
    stats: Object,
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
        case 'DRAFT': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'BERJALAN': return 'bg-green-100 text-green-800 border-green-200';
        case 'SELESAI': return 'bg-gray-100 text-gray-800 border-gray-200';
        default: return 'bg-gray-100 text-gray-800';
    }
}
</script>

<template>
    <Head :title="`Election: ${pemilu.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ pemilu.name }}</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ pemilu.description }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('admin.pemilu.edit', pemilu.id)" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                        Edit
                    </Link>
                    <Link :href="route('admin.pemilu.index')" class="text-sm text-gray-600 hover:text-gray-900">
                        ← Back
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Status & Controls -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Election Status</h3>

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
                                class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition disabled:opacity-50"
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
                                class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition disabled:opacity-50"
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
                                class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-md transition disabled:opacity-50"
                            >
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset to Draft
                            </button>
                        </div>
                    </div>

                    <!-- Status flow indicator -->
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="px-2 py-1 rounded" :class="pemilu.status === 'DRAFT' ? 'bg-yellow-100 font-bold' : 'bg-gray-50'">DRAFT</span>
                        <span>→</span>
                        <span class="px-2 py-1 rounded" :class="pemilu.status === 'BERJALAN' ? 'bg-green-100 font-bold' : 'bg-gray-50'">BERJALAN</span>
                        <span>→</span>
                        <span class="px-2 py-1 rounded" :class="pemilu.status === 'SELESAI' ? 'bg-gray-200 font-bold' : 'bg-gray-50'">SELESAI</span>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500">Registered Voters</div>
                        <div class="mt-1 text-3xl font-bold text-gray-900">{{ stats.voterCount }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500">Total Votes Cast</div>
                        <div class="mt-1 text-3xl font-bold text-indigo-600">{{ stats.totalVotes }}</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500">Turnout</div>
                        <div class="mt-1 text-3xl font-bold text-green-600">{{ stats.turnoutPercentage }}%</div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="text-sm font-medium text-gray-500">Candidates</div>
                        <div class="mt-1 text-3xl font-bold text-gray-900">{{ pemilu.kandidats?.length || 0 }}</div>
                    </div>
                </div>

                <!-- Merkle Tree Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Merkle Tree (zk-SNARK)</h3>

                    <div v-if="stats.merkleTree" class="space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-green-100 text-green-800">
                                {{ stats.merkleTree.status }}
                            </span>
                            <span class="text-sm text-gray-500">Generated at {{ stats.merkleTree.created_at }}</span>
                        </div>
                        <div class="bg-gray-50 rounded-md p-3">
                            <div class="text-xs font-medium text-gray-500 mb-1">Root Hash</div>
                            <code class="text-xs text-gray-700 break-all">{{ stats.merkleTree.root_hash }}</code>
                        </div>
                        <div class="mt-3">
                            <button
                                @click="generateMerkleTree"
                                :disabled="generatingTree"
                                class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-md transition disabled:opacity-50"
                            >
                                <svg v-if="generatingTree" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Regenerate Tree
                            </button>
                        </div>
                    </div>

                    <div v-else class="space-y-3">
                        <p class="text-sm text-gray-600">
                            The Merkle Tree has not been generated yet. Generate it to allow voters to create zero-knowledge proofs.
                        </p>
                        <p class="text-xs text-gray-500">
                            This will collect all {{ stats.voterCount }} voter commitments and build a Poseidon-based Merkle Tree (depth 10, max 1024 leaves).
                        </p>
                        <button
                            @click="generateMerkleTree"
                            :disabled="generatingTree || stats.voterCount === 0"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition disabled:opacity-50"
                        >
                            <svg v-if="generatingTree" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            Generate Merkle Tree
                        </button>
                        <p v-if="stats.voterCount === 0" class="text-xs text-red-500">No voters registered. Add voters first.</p>
                    </div>

                    <!-- Tree generation feedback -->
                    <div v-if="treeError" class="mt-3 rounded-md bg-red-50 p-3">
                        <p class="text-sm text-red-700">{{ treeError }}</p>
                    </div>
                    <div v-if="treeSuccess" class="mt-3 rounded-md bg-green-50 p-3">
                        <p class="text-sm text-green-700">{{ treeSuccess }}</p>
                    </div>
                </div>

                <!-- Vote Status Breakdown -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Vote Status Breakdown</h3>

                    <div v-if="stats.totalVotes > 0" class="space-y-4">
                        <!-- Progress bar -->
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Turnout</span>
                                <span>{{ stats.totalVotes }} / {{ stats.voterCount }} voters</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-indigo-600 h-3 rounded-full transition-all" :style="{ width: stats.turnoutPercentage + '%' }"></div>
                            </div>
                        </div>

                        <!-- Status counts -->
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-700">{{ stats.votesByStatus['MASUK'] || 0 }}</div>
                                <div class="text-xs text-blue-600 mt-1">MASUK (Received)</div>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold text-green-700">{{ stats.votesByStatus['TERVERIFIKASI'] || 0 }}</div>
                                <div class="text-xs text-green-600 mt-1">TERVERIFIKASI (Verified)</div>
                            </div>
                            <div class="text-center p-3 bg-red-50 rounded-lg">
                                <div class="text-2xl font-bold text-red-700">{{ stats.votesByStatus['DITOLAK'] || 0 }}</div>
                                <div class="text-xs text-red-600 mt-1">DITOLAK (Rejected)</div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-6">
                        <p class="text-gray-500 text-sm">No votes have been cast yet.</p>
                    </div>
                </div>

                <!-- Candidates -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Candidates</h3>
                        <Link :href="route('admin.pemilu.kandidat.index', pemilu.id)" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                            Manage →
                        </Link>
                    </div>

                    <div v-if="pemilu.kandidats && pemilu.kandidats.length > 0" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-for="kandidat in pemilu.kandidats" :key="kandidat.id" class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold">
                                    {{ kandidat.nomor_urut }}
                                </span>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">Candidate #{{ kandidat.nomor_urut }}</div>
                                    <div v-if="kandidat.visi_misi" class="text-xs text-gray-500 truncate max-w-[200px]">{{ kandidat.visi_misi }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-4">
                        <p class="text-gray-500 text-sm">No candidates registered.</p>
                        <Link :href="route('admin.pemilu.kandidat.create', pemilu.id)" class="text-sm text-indigo-600 hover:text-indigo-900 mt-2 inline-block">
                            + Add Candidate
                        </Link>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
