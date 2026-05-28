<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    pemilu: Object,
});

const form = useForm({
    nomor_urut: '',
    nama_kandidat: '',
    visi_misi: '',
});

const submit = () => {
    form.post(route('admin.pemilu.kandidat.store', props.pemilu.id));
};
</script>

<template>
    <Head title="Add Candidate" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-juned-800 leading-tight">Add Candidate to: {{ pemilu.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white border border-juned-200 rounded-xl shadow-sm p-6">
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
                                autofocus
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
                                class="mt-1 block w-full bg-white border-juned-200 focus:border-juned-500 focus:ring-juned-500/20 text-juned-dark placeholder-juned-200 rounded-lg"
                                v-model="form.visi_misi"
                                rows="8"
                                required
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.visi_misi" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Save Candidate</PrimaryButton>
                            <Link :href="route('admin.pemilu.kandidat.index', pemilu.id)" class="text-sm text-juned-text hover:text-juned-800">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
