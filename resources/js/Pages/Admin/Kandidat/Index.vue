<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pemilu: Object,
    kandidats: Array,
});
</script>

<template>
    <Head :title="`Candidates for ${pemilu.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-juned-800 leading-tight">Candidates: {{ pemilu.name }}</h2>
                    <Link :href="route('admin.pemilu.index')" class="text-sm text-juned-500 hover:text-juned-600">&larr; Back to Elections</Link>
                </div>
                <Link :href="route('admin.pemilu.kandidat.create', pemilu.id)" class="bg-juned-800 hover:bg-juned-700 text-white font-bold py-2 px-4 rounded-lg transition-all">
                    + Add Candidate
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white border border-juned-200 rounded-xl shadow-sm">
                    <div class="p-6 text-juned-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-juned-200">
                                <thead class="bg-juned-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Visi & Misi</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-juned-text uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-juned-200">
                                    <tr v-for="kandidat in kandidats" :key="kandidat.id" class="hover:bg-juned-100/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-2xl font-bold text-juned-800">#{{ kandidat.nomor_urut }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-juned-800">{{ kandidat.nama_kandidat || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-juned-text max-w-2xl whitespace-pre-line">{{ kandidat.visi_misi }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                                            <Link :href="route('admin.pemilu.kandidat.edit', [pemilu.id, kandidat.id])" class="text-juned-500 hover:text-juned-600 mr-3">Edit</Link>
                                            <Link :href="route('admin.pemilu.kandidat.destroy', [pemilu.id, kandidat.id])" method="delete" as="button" class="text-red-600 hover:text-red-500">Delete</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="kandidats.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-juned-text">No candidates added yet. Add one!</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
