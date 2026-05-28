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
        </div>
    </div>
</template>
