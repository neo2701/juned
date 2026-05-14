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

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">E-Voting Portal</h1>
                <p class="mt-2 text-sm text-gray-600">Secure. Anonymous. Verifiable.</p>
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
                    />
                    <InputError class="mt-2" :message="form.errors.private_key" />
                </div>

                <div class="flex items-center justify-end mt-6">
                    <PrimaryButton class="ms-4 w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Authenticate & Enter Voting Booth
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
