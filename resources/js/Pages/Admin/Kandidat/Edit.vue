<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    pemilu: Object,
    kandidat: Object,
});

const form = useForm({
    nomor_urut: props.kandidat.nomor_urut,
    nama_kandidat: props.kandidat.nama_kandidat || '',
    visi_misi: props.kandidat.visi_misi,
});

const submit = () => {
    form.put(route('admin.pemilu.kandidat.update', [props.pemilu.id, props.kandidat.id]));
};
</script>

<template>
    <Head title="Edit Candidate" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Edit Candidate #{{ kandidat.nomor_urut }} in: {{ pemilu.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6">
                    <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
                        <div>
                            <InputLabel for="nomor_urut" value="Candidate Number (Nomor Urut)" />
                            <TextInput
                                id="nomor_urut"
                                type="number"
                                min="1"
                                class="mt-1 block w-full"
                                v-model="form.nomor_urut"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.nomor_urut" />
                        </div>

                        <div>
                            <InputLabel for="nama_kandidat" value="Candidate Name (Nama Kandidat)" />
                            <TextInput
                                id="nama_kandidat"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.nama_kandidat"
                            />
                            <InputError class="mt-2" :message="form.errors.nama_kandidat" />
                        </div>

                        <div>
                            <InputLabel for="visi_misi" value="Visi & Misi" />
                            <textarea
                                id="visi_misi"
                                class="mt-1 block w-full bg-gray-800/50 border-gray-600 focus:border-cyan-500 focus:ring-cyan-500/20 text-white placeholder-gray-500 rounded-xl"
                                v-model="form.visi_misi"
                                rows="8"
                                required
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.visi_misi" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Update Candidate</PrimaryButton>
                            <Link :href="route('admin.pemilu.kandidat.index', pemilu.id)" class="text-sm text-gray-400 hover:text-gray-200">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
