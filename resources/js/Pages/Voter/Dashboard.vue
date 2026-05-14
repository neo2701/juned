<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { buildPoseidon } from 'circomlibjs';
import * as snarkjs from 'snarkjs';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// Inertia props
const props = defineProps({
    elections: {
        type: Array,
        default: () => [],
    },
});

// BN254 scalar field order
const BN254_FIELD_ORDER = BigInt('21888242871839275222246405745257275088548364400416034343698204186575808495617');

// Access shared voter data from Inertia
const page = usePage();

// Component state — initialize from Inertia props if available
const elections = ref(props.elections.length > 0 ? props.elections : []);
const loadingElections = ref(props.elections.length === 0);
const electionsError = ref('');

// Voting modal state
const showVotingModal = ref(false);
const selectedElection = ref(null);
const selectedKandidat = ref(null);
const privateKeyInput = ref('');
const privateKeyError = ref('');

// Voting process state
const isGeneratingProof = ref(false);
const voteSuccess = ref(false);
const voteError = ref('');

// Merkle proof state (computed in task 6.2, used in task 6.3)
const merkleProof = ref(null); // { pathElements: string[], pathIndices: number[], root: string }

// Poseidon instance (cached after first build)
let poseidonInstance = null;

/**
 * Fetch active elections with candidates from the API.
 */
async function fetchElections() {
    loadingElections.value = true;
    electionsError.value = '';

    try {
        const response = await fetch('/voter/api/elections');
        if (!response.ok) {
            throw new Error('Failed to fetch elections');
        }
        elections.value = await response.json();
    } catch (error) {
        electionsError.value = 'Could not load elections. Please try again.';
    } finally {
        loadingElections.value = false;
    }
}

/**
 * Validate private key: non-empty numeric string, valid BN254 field element.
 */
function validatePrivateKey(key) {
    if (!key || key.trim() === '') {
        return 'Private key is required';
    }

    if (!/^\d+$/.test(key)) {
        return 'Private key must be a valid numeric value';
    }

    try {
        const keyBigInt = BigInt(key);
        if (keyBigInt >= BN254_FIELD_ORDER) {
            return 'Private key must be a valid numeric value';
        }
        if (keyBigInt === 0n) {
            return 'Private key must be a valid numeric value';
        }
    } catch {
        return 'Private key must be a valid numeric value';
    }

    return '';
}

/**
 * Open the voting modal for a specific candidate.
 */
function openVotingModal(election, kandidat) {
    selectedElection.value = election;
    selectedKandidat.value = kandidat;
    privateKeyInput.value = '';
    privateKeyError.value = '';
    voteError.value = '';
    voteSuccess.value = false;
    showVotingModal.value = true;
}

/**
 * Close the voting modal and clear sensitive data.
 */
function closeVotingModal() {
    clearPrivateKey();
    showVotingModal.value = false;
    selectedElection.value = null;
    selectedKandidat.value = null;
    voteSuccess.value = false;
    voteError.value = '';
}

/**
 * Clear private key from memory and input.
 */
function clearPrivateKey() {
    privateKeyInput.value = '';
    privateKeyError.value = '';
}

/**
 * Get or build the Poseidon hash function instance.
 */
async function getPoseidon() {
    if (!poseidonInstance) {
        poseidonInstance = await buildPoseidon();
    }
    return poseidonInstance;
}

/**
 * Compute Poseidon hash with a single input and return as decimal string.
 */
async function poseidonHash1(input) {
    const poseidon = await getPoseidon();
    const F = poseidon.F;
    return F.toObject(poseidon([BigInt(input)])).toString();
}

/**
 * Compute Poseidon hash with two inputs and return as decimal string.
 */
async function poseidonHash2(left, right) {
    const poseidon = await getPoseidon();
    const F = poseidon.F;
    return F.toObject(poseidon([BigInt(left), BigInt(right)])).toString();
}

/**
 * Fetch Merkle Tree data from the API.
 * Returns { leaves: string[], root: string } or throws an error.
 */
async function fetchMerkleTree(pemiluId) {
    const response = await fetch(`/voter/api/merkle-tree/${pemiluId}`);
    if (!response.ok) {
        throw new Error('Could not load election data. Please try again.');
    }
    return await response.json();
}

/**
 * Compute Merkle Proof locally from the leaves array.
 * Returns { pathElements: string[], pathIndices: number[], root: string }
 */
async function computeMerkleProof(leaves, leafIndex) {
    const depth = 10;
    // Build the tree level by level
    let currentLevel = [...leaves];
    const pathElements = [];
    const pathIndices = [];

    let currentIndex = leafIndex;

    for (let level = 0; level < depth; level++) {
        // Determine path direction: 0 = leaf is on the left, 1 = leaf is on the right
        const isRight = currentIndex % 2;
        pathIndices.push(isRight);

        // Get sibling
        const siblingIndex = isRight ? currentIndex - 1 : currentIndex + 1;
        pathElements.push(currentLevel[siblingIndex]);

        // Compute next level
        const nextLevel = [];
        for (let i = 0; i < currentLevel.length; i += 2) {
            const hash = await poseidonHash2(currentLevel[i], currentLevel[i + 1]);
            nextLevel.push(hash);
        }

        currentLevel = nextLevel;
        currentIndex = Math.floor(currentIndex / 2);
    }

    // The final currentLevel[0] should be the root
    const computedRoot = currentLevel[0];

    return { pathElements, pathIndices, root: computedRoot };
}

/**
 * Generate a random BigInt less than BN254_FIELD_ORDER using crypto.getRandomValues().
 */
function generateRandomSecret() {
    // Generate 32 random bytes (256 bits)
    const randomBytes = new Uint8Array(32);
    crypto.getRandomValues(randomBytes);

    // Convert to BigInt
    let randomBigInt = 0n;
    for (let i = 0; i < 32; i++) {
        randomBigInt = (randomBigInt << 8n) | BigInt(randomBytes[i]);
    }

    // Reduce modulo BN254_FIELD_ORDER to ensure it's a valid field element
    return (randomBigInt % (BN254_FIELD_ORDER - 1n)) + 1n;
}

/**
 * Get the XSRF token from cookies for Laravel CSRF protection.
 */
function getXsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    if (match) {
        return decodeURIComponent(match[1]);
    }
    return null;
}

/**
 * Submit vote: validate private key, fetch Merkle tree, compute proof,
 * generate Groth16 proof, and submit to server.
 */
async function submitVote() {
    // Validate private key
    const validationError = validatePrivateKey(privateKeyInput.value);
    if (validationError) {
        privateKeyError.value = validationError;
        return;
    }

    privateKeyError.value = '';
    voteError.value = '';
    merkleProof.value = null;
    isGeneratingProof.value = true;

    try {
        const privateKey = privateKeyInput.value;
        const pemiluId = selectedElection.value.id;
        const kandidatId = selectedKandidat.value.id;

        // Step 1: Fetch Merkle Tree data
        let treeData;
        try {
            treeData = await fetchMerkleTree(pemiluId);
        } catch (fetchError) {
            // Preserve private key for retry on fetch failure
            voteError.value = 'Could not load election data. Please try again.';
            isGeneratingProof.value = false;
            return;
        }

        const { leaves, root } = treeData;

        // Step 2: Compute voter commitment: Poseidon(privateKey)
        const commitment = await poseidonHash1(privateKey);

        // Step 3: Search for commitment in leaves
        const leafIndex = leaves.indexOf(commitment);

        if (leafIndex === -1) {
            // Not registered - clear private key and block proof generation
            voteError.value = 'You are not registered for this election.';
            clearPrivateKey();
            isGeneratingProof.value = false;
            return;
        }

        // Step 4: Compute Merkle Proof locally
        const proof = await computeMerkleProof(leaves, leafIndex);

        // Verify computed root matches the fetched root
        if (proof.root !== root) {
            voteError.value = 'Merkle tree verification failed. Please try again.';
            clearPrivateKey();
            isGeneratingProof.value = false;
            return;
        }

        // Store Merkle proof
        merkleProof.value = proof;

        // Step 5: Build circuit inputs
        const circuitInputs = {
            privateKey: privateKey,
            pathElements: merkleProof.value.pathElements,
            pathIndices: merkleProof.value.pathIndices,
            root: merkleProof.value.root,
            pemiluId: pemiluId.toString(),
            kandidatId: kandidatId.toString(),
        };

        // Step 6: Generate Groth16 proof
        const wasmPath = '/zkp/vote.wasm';
        const zkeyPath = '/zkp/vote_final.zkey';

        const { proof: groth16Proof, publicSignals } = await snarkjs.groth16.fullProve(
            circuitInputs,
            wasmPath,
            zkeyPath
        );

        // Step 7: Compute nullifier hash locally: Poseidon(privateKey, pemiluId)
        const nullifierHash = await poseidonHash2(privateKey, pemiluId.toString());

        // Step 8: Encrypt vote: Poseidon(kandidatId, randomSecret)
        let encryptedVote;
        try {
            const randomSecret = generateRandomSecret();
            encryptedVote = await poseidonHash2(kandidatId.toString(), randomSecret.toString());
        } catch (encryptionError) {
            voteError.value = 'Vote encryption failed. Please try again.';
            clearPrivateKey();
            isGeneratingProof.value = false;
            return;
        }

        // Step 9: Submit vote to server
        const payload = {
            proof: groth16Proof,
            publicSignals: publicSignals,
            encrypted_vote: encryptedVote,
            nullifier_hash: nullifierHash,
            pemilu_id: pemiluId,
        };

        const xsrfToken = getXsrfToken();
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (xsrfToken) {
            headers['X-XSRF-TOKEN'] = xsrfToken;
        }

        const response = await fetch('/voter/api/vote', {
            method: 'POST',
            headers: headers,
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        if (response.ok) {
            // Success: display confirmation, clear sensitive data
            voteSuccess.value = true;
            clearPrivateKey();
        } else if (response.status === 409) {
            voteError.value = 'You have already voted in this election.';
            clearPrivateKey();
        } else if (response.status === 422) {
            const errorData = await response.json();
            voteError.value = errorData.error || errorData.message || 'Vote submission was rejected. Please try again.';
            clearPrivateKey();
        } else {
            voteError.value = 'Vote could not be recorded. Please try again.';
            clearPrivateKey();
        }
    } catch (error) {
        // Network error or proof generation failure
        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            voteError.value = 'Could not submit vote. Please try again.';
        } else {
            voteError.value = error.message || 'An error occurred during proof generation.';
        }
        clearPrivateKey();
    } finally {
        isGeneratingProof.value = false;
    }
}

// Fetch elections on mount (only if not already provided via Inertia props)
onMounted(() => {
    if (elections.value.length === 0) {
        fetchElections();
    }
});

// Ensure private key is cleared if component is unmounted
onUnmounted(() => {
    clearPrivateKey();
});
</script>

<template>
    <Head title="Voter Dashboard" />

    <div class="min-h-screen bg-[#0a0a0f] relative">
        <!-- Subtle animated background -->
        <div class="fixed inset-0 pointer-events-none">
            <div class="absolute inset-0 opacity-[0.02]" style="background-image: linear-gradient(rgba(6, 182, 212, 0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(6, 182, 212, 0.3) 1px, transparent 1px); background-size: 60px 60px;"></div>
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl animate-pulse-glow"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-violet-500/5 rounded-full blur-3xl animate-pulse-glow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Navigation -->
        <nav class="relative z-10 border-b border-gray-800 bg-gray-900/80 backdrop-blur-xl">
            <div class="h-[2px] bg-gradient-to-r from-cyan-500 via-violet-500 to-cyan-500"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <span class="font-bold text-xl text-gradient">JUNED E-Voting</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span v-if="page.props.voter" class="text-sm text-gray-400 font-mono">
                            {{ page.props.voter.nik }}
                        </span>
                        <Link :href="route('voter.logout')" method="post" as="button" class="text-sm text-gray-500 hover:text-rose-400 transition-colors">
                            Log Out
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="relative z-10">
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <!-- Page Header -->
                    <div class="mb-8 px-4 sm:px-0">
                        <h1 class="text-2xl font-bold text-white">Voting Booth</h1>
                        <p class="mt-1 text-sm text-gray-500">Select a candidate and cast your vote securely using zero-knowledge proofs.</p>
                    </div>

                    <!-- Loading State -->
                    <div v-if="loadingElections" class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
                        <div class="p-8 text-center">
                            <div class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-cyan-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-gray-400">Loading elections...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="electionsError" class="bg-gray-900/60 backdrop-blur-xl border border-rose-500/30 rounded-2xl shadow-2xl glow-rose">
                        <div class="p-8 text-center">
                            <p class="text-rose-400 mb-4">{{ electionsError }}</p>
                            <PrimaryButton @click="fetchElections">
                                Retry
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- No Elections -->
                    <div v-else-if="elections.length === 0" class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
                        <div class="p-8 text-center">
                            <p class="text-gray-500">No active elections at this time.</p>
                        </div>
                    </div>

                    <!-- Elections List -->
                    <div v-else class="space-y-6">
                        <div
                            v-for="election in elections"
                            :key="election.id"
                            class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden"
                        >
                            <!-- Election Header -->
                            <div class="p-6 border-b border-gray-700/50">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h2 class="text-lg font-semibold text-white">{{ election.name }}</h2>
                                        <p v-if="election.description" class="mt-1 text-sm text-gray-500">{{ election.description }}</p>
                                    </div>
                                    <span
                                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium border"
                                        :class="{
                                            'bg-emerald-500/10 text-emerald-400 border-emerald-500/30': election.status === 'BERJALAN',
                                            'bg-gray-500/10 text-gray-400 border-gray-500/30': election.status !== 'BERJALAN'
                                        }"
                                    >
                                        {{ election.status }}
                                    </span>
                                </div>
                            </div>

                            <!-- Candidates -->
                            <div class="p-6">
                                <h3 class="text-sm font-medium text-gray-400 mb-4">Candidates</h3>

                                <div v-if="!election.kandidats || election.kandidats.length === 0" class="text-sm text-gray-600">
                                    No candidates registered for this election.
                                </div>

                                <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                    <div
                                        v-for="kandidat in [...election.kandidats].sort((a, b) => a.nomor_urut - b.nomor_urut)"
                                        :key="kandidat.id"
                                        class="border border-gray-700/50 rounded-xl p-4 flex flex-col justify-between bg-gray-800/30 hover:border-cyan-500/40 transition-all duration-300 group"
                                    >
                                        <div>
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-cyan-500/20 to-violet-500/20 text-cyan-400 text-sm font-bold border border-cyan-500/20">
                                                    {{ kandidat.nomor_urut }}
                                                </span>
                                                <span class="text-sm font-medium text-white">#{{ kandidat.nomor_urut }} - {{ kandidat.nama_kandidat }}</span>
                                            </div>
                                            <p v-if="kandidat.visi_misi" class="text-xs text-gray-500 mb-3">{{ kandidat.visi_misi }}</p>
                                        </div>

                                        <PrimaryButton
                                            @click="openVotingModal(election, kandidat)"
                                            :disabled="election.status !== 'BERJALAN'"
                                            class="w-full justify-center mt-2"
                                            :class="{ 'opacity-30 cursor-not-allowed': election.status !== 'BERJALAN' }"
                                        >
                                            Vote
                                        </PrimaryButton>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Voting Modal -->
        <Modal :show="showVotingModal" max-width="md" @close="closeVotingModal">
            <div class="p-6">
                <!-- Modal Header -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-white">Cast Your Vote</h3>
                    <p class="mt-1 text-sm text-gray-400">
                        Voting for <span class="text-cyan-400 font-medium">Candidate #{{ selectedKandidat?.nomor_urut }}</span>
                        in <span class="text-white font-medium">{{ selectedElection?.name }}</span>
                    </p>
                </div>

                <!-- Success State -->
                <div v-if="voteSuccess" class="text-center py-6">
                    <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-emerald-500/10 border border-emerald-500/30 mb-4 glow-emerald">
                        <svg class="h-7 w-7 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <p class="text-sm text-emerald-400 font-medium">Vote recorded successfully!</p>
                    <p class="text-xs text-gray-500 mt-1">Your vote has been submitted anonymously.</p>
                    <SecondaryButton @click="closeVotingModal" class="mt-6">
                        Close
                    </SecondaryButton>
                </div>

                <!-- Generating Proof State -->
                <div v-else-if="isGeneratingProof" class="text-center py-10">
                    <div class="relative mx-auto w-16 h-16 mb-6">
                        <div class="absolute inset-0 rounded-full border-2 border-cyan-500/20"></div>
                        <div class="absolute inset-0 rounded-full border-2 border-transparent border-t-cyan-400 animate-spin"></div>
                        <div class="absolute inset-2 rounded-full border-2 border-transparent border-t-violet-400 animate-spin" style="animation-direction: reverse; animation-duration: 1.5s;"></div>
                        <div class="absolute inset-0 rounded-full glow-cyan animate-pulse"></div>
                    </div>
                    <p class="text-sm text-white font-medium">Generating zero-knowledge proof...</p>
                    <p class="text-xs text-gray-500 mt-2">This may take a few moments. Please do not close this window.</p>
                </div>

                <!-- Private Key Input Form -->
                <div v-else>
                    <!-- Error Display -->
                    <div v-if="voteError" class="mb-4 rounded-xl bg-rose-500/10 border border-rose-500/30 p-4 glow-rose">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-rose-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-rose-400">{{ voteError }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Private Key Input -->
                    <div class="mb-4">
                        <InputLabel for="voter-private-key" value="Private Key" />
                        <p class="text-xs text-gray-500 mt-1 mb-2">Enter your private key to generate a zero-knowledge proof. Your key will not be sent to the server.</p>
                        <TextInput
                            id="voter-private-key"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="privateKeyInput"
                            placeholder="Enter your numeric private key"
                            @keyup.enter="submitVote"
                        />
                        <InputError class="mt-2" :message="privateKeyError" />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 mt-6">
                        <SecondaryButton @click="closeVotingModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton @click="submitVote">
                            Generate Proof & Vote
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
</template>
