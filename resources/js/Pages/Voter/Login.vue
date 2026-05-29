<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, onUnmounted } from 'vue';
import { Html5Qrcode } from 'html5-qrcode';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    nik: '',
    private_key: '',
});

// QR Scanner state
const showScanner = ref(false);
const scannerError = ref('');
const scanSuccess = ref(false);
let html5QrCode = null;

const submit = () => {
    form.post(route('voter.login'));
};

/**
 * Start QR code scanner using device camera.
 */
async function startScanner() {
    showScanner.value = true;
    scannerError.value = '';
    scanSuccess.value = false;

    // Wait for DOM to render the scanner container
    await new Promise(resolve => setTimeout(resolve, 100));

    try {
        html5QrCode = new Html5Qrcode('qr-reader');

        await html5QrCode.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 },
            },
            onScanSuccess,
            () => {} // ignore scan failures (no QR in frame)
        );
    } catch (err) {
        // Try with user-facing camera if environment fails
        try {
            await html5QrCode.start(
                { facingMode: 'user' },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                },
                onScanSuccess,
                () => {}
            );
        } catch (err2) {
            scannerError.value = 'Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.';
            showScanner.value = false;
        }
    }
}

/**
 * Handle successful QR scan.
 */
function onScanSuccess(decodedText) {
    try {
        const data = JSON.parse(decodedText);

        if (data.type === 'juned-voter' && data.nik && data.key) {
            form.nik = data.nik;
            form.private_key = data.key;
            scanSuccess.value = true;
            stopScanner();
        } else {
            scannerError.value = 'QR code tidak valid. Gunakan QR dari registrasi JUNED.';
        }
    } catch {
        scannerError.value = 'QR code tidak dikenali. Gunakan QR dari registrasi JUNED.';
    }
}

/**
 * Stop QR scanner and release camera.
 */
async function stopScanner() {
    if (html5QrCode) {
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
        } catch {
            // Ignore stop errors
        }
        html5QrCode = null;
    }
    showScanner.value = false;
}

/**
 * Handle file-based QR scan (upload image).
 */
async function handleFileUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    scannerError.value = '';
    scanSuccess.value = false;

    try {
        const tempScanner = new Html5Qrcode('qr-file-reader');
        const result = await tempScanner.scanFile(file, true);
        const data = JSON.parse(result);

        if (data.type === 'juned-voter' && data.nik && data.key) {
            form.nik = data.nik;
            form.private_key = data.key;
            scanSuccess.value = true;
        } else {
            scannerError.value = 'QR code tidak valid.';
        }

        tempScanner.clear();
    } catch {
        scannerError.value = 'Gagal membaca QR dari file. Pastikan gambar berisi QR code yang valid.';
    }

    // Reset file input
    event.target.value = '';
}

// Cleanup on unmount
onUnmounted(() => {
    if (html5QrCode) {
        html5QrCode.stop().catch(() => {});
    }
});
</script>

<template>
    <Head title="Voter Login" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-juned-100 relative overflow-hidden">
        <!-- Background decorative -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/4 right-1/4 w-64 h-64 bg-juned-400/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 left-1/4 w-64 h-64 bg-juned-500/5 rounded-full blur-3xl"></div>
        </div>

        <!-- Card -->
        <div class="relative z-10 w-full sm:max-w-md mt-6 px-6 py-8 bg-white border border-juned-200 shadow-lg rounded-2xl">
            <!-- Header -->
            <div class="mb-8 text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 rounded-xl bg-juned-400/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-juned-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-juned-800">JUNED E-Voting</h1>
                <p class="mt-2 text-sm text-juned-text">Secure. Anonymous. Verifiable.</p>
            </div>

            <!-- QR Scanner Section -->
            <div v-if="showScanner" class="mb-6">
                <div class="rounded-xl overflow-hidden border border-juned-200">
                    <div id="qr-reader" class="w-full"></div>
                </div>
                <div class="mt-3 flex justify-center">
                    <button
                        @click="stopScanner"
                        class="inline-flex items-center gap-2 text-sm font-medium text-red-600 hover:text-red-700 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Tutup Scanner
                    </button>
                </div>
            </div>

            <!-- Scan Success Message -->
            <div v-if="scanSuccess" class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 p-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-emerald-700 font-medium">QR berhasil dipindai! Klik "Masuk Voting" untuk melanjutkan.</p>
                </div>
            </div>

            <!-- Scanner Error -->
            <div v-if="scannerError" class="mb-4 rounded-xl bg-red-50 border border-red-200 p-3">
                <p class="text-sm text-red-600">{{ scannerError }}</p>
            </div>

            <!-- QR Login Buttons -->
            <div v-if="!showScanner" class="mb-6">
                <div class="grid grid-cols-2 gap-3">
                    <button
                        @click="startScanner"
                        class="flex items-center justify-center gap-2 rounded-xl border border-juned-200 bg-juned-100/50 px-4 py-3 text-sm font-medium text-juned-700 hover:bg-juned-100 transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                        </svg>
                        Scan QR
                    </button>
                    <label class="flex items-center justify-center gap-2 rounded-xl border border-juned-200 bg-juned-100/50 px-4 py-3 text-sm font-medium text-juned-700 hover:bg-juned-100 transition-all cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H3.75A2.25 2.25 0 001.5 6.75v12a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        Upload QR
                        <input type="file" accept="image/*" class="hidden" @change="handleFileUpload" />
                    </label>
                </div>
                <p class="mt-2 text-center text-xs text-juned-text">Gunakan QR code dari registrasi untuk login cepat</p>
            </div>

            <!-- Divider -->
            <div class="relative mb-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-juned-200"></div>
                </div>
                <div class="relative flex justify-center text-xs">
                    <span class="bg-white px-3 text-juned-text">atau masuk manual</span>
                </div>
            </div>

            <!-- Manual Login Form -->
            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="nik" value="NIK (Nomor Induk Kependudukan)" />
                    <TextInput
                        id="nik"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.nik"
                        required
                        placeholder="Masukkan NIK Anda"
                    />
                    <InputError class="mt-2" :message="form.errors.nik" />
                </div>

                <div class="mt-4">
                    <InputLabel for="private_key" value="Private Key" />
                    <TextInput
                        id="private_key"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.private_key"
                        required
                        placeholder="Masukkan private key Anda"
                    />
                    <InputError class="mt-2" :message="form.errors.private_key" />
                </div>

                <div class="mt-8">
                    <PrimaryButton class="w-full justify-center text-base" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Masuk Voting
                    </PrimaryButton>
                </div>
            </form>

            <!-- Footer hint -->
            <p class="mt-6 text-center text-xs text-juned-text">
                Private key Anda tidak pernah meninggalkan browser.
            </p>

            <!-- Registration link -->
            <div class="mt-4 text-center">
                <Link :href="route('voter.register')" class="text-sm text-juned-700 hover:text-juned-800 font-medium">
                    Belum terdaftar? Registrasi di sini
                </Link>
            </div>
        </div>

        <!-- Hidden element for file-based QR scanning -->
        <div id="qr-file-reader" class="hidden"></div>
    </div>
</template>
