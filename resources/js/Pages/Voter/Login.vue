<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    nik: '',
    private_key: '',
});

const submit = () => {
    form.post(route('voter.login'));
};
</script>

<template>
    <Head title="Voter Login" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-[#0a0a0f] relative overflow-hidden">
        <!-- Background effects -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(rgba(6, 182, 212, 0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(6, 182, 212, 0.3) 1px, transparent 1px); background-size: 60px 60px;"></div>
            <div class="absolute top-1/3 left-1/3 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl animate-pulse-glow"></div>
            <div class="absolute bottom-1/3 right-1/3 w-80 h-80 bg-violet-500/10 rounded-full blur-3xl animate-pulse-glow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Card -->
        <div class="relative z-10 w-full sm:max-w-md mt-6 px-6 py-8 bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 shadow-2xl rounded-2xl">
            <!-- Header -->
            <div class="mb-8 text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-gradient">JUNED E-Voting</h1>
                <p class="mt-2 text-sm text-gray-500">Secure. Anonymous. Verifiable.</p>
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="nik" value="NIK (Nomor Induk Kependudukan)" />
                    <TextInput
                        id="nik"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.nik"
                        required
                        autofocus
                        placeholder="Enter your NIK"
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
                        placeholder="Enter your private key"
                    />
                    <InputError class="mt-2" :message="form.errors.private_key" />
                </div>

                <div class="mt-8">
                    <PrimaryButton class="w-full justify-center text-base" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Enter Voting Booth
                    </PrimaryButton>
                </div>
            </form>

            <!-- Footer hint -->
            <p class="mt-6 text-center text-xs text-gray-600">
                Your private key never leaves your browser.
            </p>
        </div>
    </div>
</template>
