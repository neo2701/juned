<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    nik: '',
    nama_pemilih: '',
});

const submit = () => {
    form.post(route('admin.pemilih.store'));
};
</script>

<template>
    <Head title="Register Voter" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Register New Voter</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6">
                    <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
                        <div>
                            <InputLabel for="nik" value="Nomor Induk Kependudukan (NIK)" />
                            <TextInput
                                id="nik"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.nik"
                                maxlength="16"
                                required
                                autofocus
                            />
                            <p class="mt-1 text-sm text-gray-400">Must be exactly 16 characters.</p>
                            <InputError class="mt-2" :message="form.errors.nik" />
                        </div>

                        <div>
                            <InputLabel for="nama_pemilih" value="Voter Name (Nama Pemilih)" />
                            <TextInput
                                id="nama_pemilih"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.nama_pemilih"
                            />
                            <InputError class="mt-2" :message="form.errors.nama_pemilih" />
                        </div>

                        <p class="text-sm text-gray-400">A secure private key will be automatically generated upon registration.</p>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Register & Generate Key</PrimaryButton>
                            <Link :href="route('admin.pemilih.index')" class="text-sm text-gray-400 hover:text-gray-200">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
