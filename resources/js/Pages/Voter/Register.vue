<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { buildPoseidon } from 'circomlibjs';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

// BN254 scalar field order
const BN254_FIELD_ORDER = BigInt('21888242871839275222246405745257275088548364400416034343698204186575808495617');

// Steps: 'verify' -> 'generate' -> 'complete'
const step = ref('verify');

// Step 1: NIK verification
const nikInput = ref('');
const nikError = ref('');
const isCheckingNik = ref(false);
const voterName = ref('');

// Step 2: Key generation
const isGeneratingKey = ref(false);
const generatedPrivateKey = ref('');
const generatedCommitment = ref('');

// Step 3: Confirmation & submission
const isSubmitting = ref(false);
const submitError = ref('');
const hasConfirmedBackup = ref(false);
const hasCopiedKey = ref(false);
const hasDownloaded = ref(false);

// Poseidon instance
let poseidonInstance = null;

async function getPoseidon() {
    if (!poseidonInstance) {
        poseidonInstance = await buildPoseidon();
    }
    return poseidonInstance;
}

/**
 * Get XSRF token from cookies.
 */
function getXsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : null;
}

/**
 * Step 1: Check NIK eligibility.
 */
async function checkNik() {
    nikError.value = '';

    if (nikInput.value.length !== 16 || !/^\d{16}$/.test(nikInput.value)) {
        nikError.value = 'NIK harus 16 digit angka.';
        return;
    }

    isCheckingNik.value = true;

    try {
        const xsrfToken = getXsrfToken();
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (xsrfToken) headers['X-XSRF-TOKEN'] = xsrfToken;

        const response = await fetch('/voter/register/check-nik', {
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: JSON.stringify({ nik: nikInput.value }),
        });

        const data = await response.json();

        if (data.eligible) {
            voterName.value = data.nama || '';
            step.value = 'generate';
        } else {
            nikError.value = data.reason;
        }
    } catch (error) {
        nikError.value = 'Gagal memeriksa NIK. Silakan coba lagi.';
    } finally {
        isCheckingNik.value = false;
    }
}

/**
 * Step 2: Generate private key client-side.
 */
async function generateKey() {
    isGeneratingKey.value = true;

    try {
        // Generate a cryptographically secure random private key
        const randomBytes = new Uint8Array(31); // 31 bytes to fit in BN254 field
        crypto.getRandomValues(randomBytes);

        let privKey = 0n;
        for (let i = 0; i < 31; i++) {
            privKey = (privKey << 8n) | BigInt(randomBytes[i]);
        }

        // Ensure it's within the BN254 field
        privKey = privKey % (BN254_FIELD_ORDER - 1n) + 1n;

        // Compute Poseidon commitment
        const poseidon = await getPoseidon();
        const F = poseidon.F;
        const commitment = F.toObject(poseidon([privKey])).toString();

        generatedPrivateKey.value = privKey.toString();
        generatedCommitment.value = commitment;
    } catch (error) {
        nikError.value = 'Gagal membuat kunci. Pastikan browser mendukung Web Crypto API.';
        step.value = 'verify';
    } finally {
        isGeneratingKey.value = false;
    }
}

/**
 * Copy private key to clipboard.
 */
async function copyPrivateKey() {
    try {
        await navigator.clipboard.writeText(generatedPrivateKey.value);
        hasCopiedKey.value = true;
    } catch {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = generatedPrivateKey.value;
        textArea.style.position = 'fixed';
        textArea.style.opacity = '0';
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        hasCopiedKey.value = true;
    }
}

/**
 * Download credential as a text file.
 */
function downloadCredential() {
    const content = [
        '═══════════════════════════════════════════',
        '       JUNED E-VOTING - CREDENTIAL CARD',
        '═══════════════════════════════════════════',
        '',
        `NIK          : ${nikInput.value}`,
        `Nama         : ${voterName.value || '-'}`,
        `Private Key  : ${generatedPrivateKey.value}`,
        '',
        '───────────────────────────────────────────',
        'PENTING:',
        '• Simpan file ini di tempat yang aman',
        '• Jangan bagikan private key kepada siapapun',
        '• Private key diperlukan untuk login & voting',
        '• Jika hilang, tidak dapat dipulihkan',
        '═══════════════════════════════════════════',
    ].join('\n');

    const blob = new Blob([content], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `juned-credential-${nikInput.value}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    hasDownloaded.value = true;
}

/**
 * Step 3: Submit commitment to server.
 */
async function submitRegistration() {
    if (!hasConfirmedBackup.value) {
        submitError.value = 'Anda harus mengkonfirmasi bahwa private key sudah disimpan.';
        return;
    }

    isSubmitting.value = true;
    submitError.value = '';

    try {
        const xsrfToken = getXsrfToken();
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (xsrfToken) headers['X-XSRF-TOKEN'] = xsrfToken;

        const response = await fetch('/voter/register', {
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: JSON.stringify({
                nik: nikInput.value,
                commitment: generatedCommitment.value,
            }),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            step.value = 'complete';
        } else {
            submitError.value = data.error || 'Registrasi gagal. Silakan coba lagi.';
        }
    } catch (error) {
        submitError.value = 'Koneksi gagal. Silakan coba lagi.';
    } finally {
        isSubmitting.value = false;
    }
}

const canSubmit = computed(() => {
    return hasConfirmedBackup.value && (hasCopiedKey.value || hasDownloaded.value);
});
</script>

<template>
    <Head title="Voter Registration" />

    <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0 bg-juned-100 relative overflow-hidden">
        <!-- Background decorative -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/4 right-1/4 w-64 h-64 bg-juned-400/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/3 left-1/4 w-80 h-80 bg-juned-500/5 rounded-full blur-3xl"></div>
        </div>

        <!-- Progress Steps -->
        <div class="relative z-10 w-full max-w-lg mt-8 mb-6 px-4">
            <div class="flex items-center justify-center gap-2">
                <div class="flex items-center gap-2">
                    <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all', step === 'verify' ? 'bg-juned-700 text-white' : 'bg-juned-400/30 text-juned-700']">1</div>
                    <span class="text-xs text-juned-text hidden sm:inline">Verifikasi</span>
                </div>
                <div class="w-8 h-0.5 bg-juned-200"></div>
                <div class="flex items-center gap-2">
                    <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all', step === 'generate' ? 'bg-juned-700 text-white' : 'bg-juned-200 text-juned-text']">2</div>
                    <span class="text-xs text-juned-text hidden sm:inline">Generate Key</span>
                </div>
                <div class="w-8 h-0.5 bg-juned-200"></div>
                <div class="flex items-center gap-2">
                    <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all', step === 'complete' ? 'bg-juned-700 text-white' : 'bg-juned-200 text-juned-text']">3</div>
                    <span class="text-xs text-juned-text hidden sm:inline">Selesai</span>
                </div>
            </div>
        </div>

        <!-- Step 1: NIK Verification -->
        <div v-if="step === 'verify'" class="relative z-10 w-full sm:max-w-md px-6 py-8 bg-white border border-juned-200 shadow-lg rounded-2xl">
            <div class="mb-6 text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 rounded-xl bg-juned-400/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-juned-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-juned-800">Registrasi Pemilih</h1>
                <p class="mt-2 text-sm text-juned-text">Masukkan NIK Anda untuk memverifikasi kelayakan.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <InputLabel for="nik" value="NIK (Nomor Induk Kependudukan)" />
                    <TextInput
                        id="nik"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="nikInput"
                        maxlength="16"
                        placeholder="Masukkan 16 digit NIK"
                        @keyup.enter="checkNik"
                    />
                    <InputError class="mt-2" :message="nikError" />
                </div>

                <PrimaryButton
                    @click="checkNik"
                    :disabled="isCheckingNik"
                    class="w-full justify-center"
                >
                    <svg v-if="isCheckingNik" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isCheckingNik ? 'Memeriksa...' : 'Verifikasi NIK' }}
                </PrimaryButton>
            </div>

            <div class="mt-6 text-center">
                <Link :href="route('voter.login')" class="text-sm text-juned-700 hover:text-juned-800 font-medium">
                    Sudah terdaftar? Login di sini
                </Link>
            </div>
        </div>

        <!-- Step 2: Key Generation -->
        <div v-else-if="step === 'generate'" class="relative z-10 w-full sm:max-w-lg px-6 py-8 bg-white border border-juned-200 shadow-lg rounded-2xl">
            <div class="mb-6 text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-xl font-bold text-juned-800">Generate Private Key</h1>
                <p class="mt-2 text-sm text-juned-text">
                    Halo <span class="font-medium text-juned-800" v-if="voterName">{{ voterName }}</span>! 
                    Klik tombol di bawah untuk membuat private key Anda.
                </p>
            </div>

            <!-- Before generation -->
            <div v-if="!generatedPrivateKey" class="space-y-4">
                <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <div class="text-sm text-amber-800">
                            <p class="font-medium">Penting!</p>
                            <ul class="mt-1 list-disc list-inside space-y-1 text-xs">
                                <li>Private key dibuat di browser Anda — server tidak pernah melihatnya</li>
                                <li>Anda <strong>harus</strong> menyimpan private key ini</li>
                                <li>Jika hilang, tidak dapat dipulihkan</li>
                                <li>Private key diperlukan untuk login dan voting</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <PrimaryButton
                    @click="generateKey"
                    :disabled="isGeneratingKey"
                    class="w-full justify-center"
                >
                    <svg v-if="isGeneratingKey" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                    </svg>
                    {{ isGeneratingKey ? 'Generating...' : 'Generate Private Key' }}
                </PrimaryButton>
            </div>

            <!-- After generation -->
            <div v-else class="space-y-4">
                <!-- Private Key Display -->
                <div class="rounded-xl border-2 border-juned-400/50 bg-juned-400/5 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-juned-700 uppercase tracking-wider">Private Key Anda</span>
                        <button
                            @click="copyPrivateKey"
                            class="inline-flex items-center gap-1 text-xs font-medium text-juned-700 hover:text-juned-800 transition"
                        >
                            <svg v-if="!hasCopiedKey" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <svg v-else class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ hasCopiedKey ? 'Copied!' : 'Copy' }}
                        </button>
                    </div>
                    <p class="font-mono text-sm text-juned-800 break-all select-all bg-white rounded-lg p-3 border border-juned-200">
                        {{ generatedPrivateKey }}
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="grid grid-cols-2 gap-3">
                    <button
                        @click="copyPrivateKey"
                        :class="['flex items-center justify-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition-all', hasCopiedKey ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-juned-200 bg-white text-juned-700 hover:bg-juned-100']"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        {{ hasCopiedKey ? '✓ Copied' : 'Copy Key' }}
                    </button>
                    <button
                        @click="downloadCredential"
                        :class="['flex items-center justify-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition-all', hasDownloaded ? 'border-emerald-300 bg-emerald-50 text-emerald-700' : 'border-juned-200 bg-white text-juned-700 hover:bg-juned-100']"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ hasDownloaded ? '✓ Downloaded' : 'Download' }}
                    </button>
                </div>

                <!-- Confirmation Checkbox -->
                <label class="flex items-start gap-3 rounded-xl border border-juned-200 p-4 cursor-pointer hover:bg-juned-100/50 transition">
                    <input
                        type="checkbox"
                        v-model="hasConfirmedBackup"
                        class="mt-0.5 rounded border-juned-300 text-juned-700 focus:ring-juned-500"
                    />
                    <span class="text-sm text-juned-text">
                        Saya sudah menyimpan private key dengan aman dan memahami bahwa key ini <strong class="text-juned-800">tidak dapat dipulihkan</strong> jika hilang.
                    </span>
                </label>

                <!-- Submit Error -->
                <div v-if="submitError" class="rounded-xl bg-red-50 border border-red-200 p-3">
                    <p class="text-sm text-red-600">{{ submitError }}</p>
                </div>

                <!-- Submit Button -->
                <PrimaryButton
                    @click="submitRegistration"
                    :disabled="!canSubmit || isSubmitting"
                    class="w-full justify-center"
                    :class="{ 'opacity-50 cursor-not-allowed': !canSubmit }"
                >
                    <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isSubmitting ? 'Mendaftarkan...' : 'Selesaikan Registrasi' }}
                </PrimaryButton>
            </div>
        </div>

        <!-- Step 3: Complete -->
        <div v-else-if="step === 'complete'" class="relative z-10 w-full sm:max-w-md px-6 py-8 bg-white border border-juned-200 shadow-lg rounded-2xl">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-emerald-100 border border-emerald-200 mb-6">
                    <svg class="h-8 w-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-juned-800">Registrasi Berhasil!</h1>
                <p class="mt-3 text-sm text-juned-text">
                    Akun Anda telah terdaftar. Gunakan NIK dan private key untuk login saat pemilu berlangsung.
                </p>

                <div class="mt-6 rounded-xl bg-juned-100 border border-juned-200 p-4 text-left">
                    <p class="text-xs font-medium text-juned-700 uppercase tracking-wider mb-2">Informasi Login</p>
                    <p class="text-sm text-juned-text"><span class="font-medium text-juned-800">NIK:</span> {{ nikInput }}</p>
                    <p class="text-sm text-juned-text mt-1"><span class="font-medium text-juned-800">Private Key:</span> (yang sudah Anda simpan)</p>
                </div>

                <div class="mt-6 flex flex-col gap-3">
                    <Link
                        :href="route('voter.login')"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-juned-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-juned-800"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Login Sekarang
                    </Link>
                    <Link :href="route('voter.login')" class="text-sm text-juned-text hover:text-juned-700">
                        Kembali ke halaman login
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="relative z-10 mt-6 mb-8 text-center text-xs text-juned-text">
            Private key Anda dibuat sepenuhnya di browser — server tidak pernah melihatnya.
        </p>
    </div>
</template>
