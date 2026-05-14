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
    name: props.pemilu.name,
    tahun: props.pemilu.tahun,
    tanggal_mulai: props.pemilu.tanggal_mulai ? props.pemilu.tanggal_mulai.slice(0, 16) : '',
    tanggal_selesai: props.pemilu.tanggal_selesai ? props.pemilu.tanggal_selesai.slice(0, 16) : '',
    description: props.pemilu.description,
    status: props.pemilu.status,
});

const submit = () => {
    form.put(route('admin.pemilu.update', props.pemilu.id));
};
</script>

<template>
    <Head title="Edit Pemilu" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-white leading-tight">Edit Election: {{ pemilu.name }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-6">
                    <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
                        <div>
                            <InputLabel for="name" value="Election Name" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="tahun" value="Year (Tahun)" />
                            <TextInput
                                id="tahun"
                                type="number"
                                min="2000"
                                max="2100"
                                class="mt-1 block w-full"
                                v-model="form.tahun"
                            />
                            <InputError class="mt-2" :message="form.errors.tahun" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="tanggal_mulai" value="Start Date" />
                                <TextInput
                                    id="tanggal_mulai"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                    v-model="form.tanggal_mulai"
                                />
                                <InputError class="mt-2" :message="form.errors.tanggal_mulai" />
                            </div>
                            <div>
                                <InputLabel for="tanggal_selesai" value="End Date" />
                                <TextInput
                                    id="tanggal_selesai"
                                    type="datetime-local"
                                    class="mt-1 block w-full"
                                    v-model="form.tanggal_selesai"
                                />
                                <InputError class="mt-2" :message="form.errors.tanggal_selesai" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                class="mt-1 block w-full bg-gray-800/50 border-gray-600 focus:border-cyan-500 focus:ring-cyan-500/20 text-white placeholder-gray-500 rounded-xl"
                                v-model="form.description"
                                rows="4"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div>
                            <InputLabel for="status" value="Status" />
                            <select
                                id="status"
                                v-model="form.status"
                                class="mt-1 block w-full bg-gray-800/50 border-gray-600 focus:border-cyan-500 focus:ring-cyan-500/20 text-white rounded-xl"
                            >
                                <option value="DRAFT">DRAFT</option>
                                <option value="BERJALAN">BERJALAN (Ongoing)</option>
                                <option value="SELESAI">SELESAI (Completed)</option>
                                <option value="DIPUBLIKASIKAN">DIPUBLIKASIKAN (Published)</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.status" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Save Changes</PrimaryButton>
                            <Link :href="route('admin.pemilu.index')" class="text-sm text-gray-400 hover:text-gray-200">Cancel</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
