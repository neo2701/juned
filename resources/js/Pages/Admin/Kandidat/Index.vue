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
                    <h2 class="font-semibold text-xl text-white leading-tight">Candidates: {{ pemilu.name }}</h2>
                    <Link :href="route('admin.pemilu.index')" class="text-sm text-cyan-400 hover:text-cyan-300">&larr; Back to Elections</Link>
                </div>
                <Link :href="route('admin.pemilu.kandidat.create', pemilu.id)" class="bg-gradient-to-r from-cyan-600 to-violet-600 hover:from-cyan-500 hover:to-violet-500 text-white font-semibold py-2 px-4 rounded-xl shadow-lg shadow-cyan-500/20 transition-all">
                    + Add Candidate
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
                    <div class="p-6 text-white">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-800/50">
                                <thead class="bg-gray-800/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Visi & Misi</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800/50">
                                    <tr v-for="kandidat in kandidats" :key="kandidat.id" class="hover:bg-gray-800/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-2xl font-bold text-white">#{{ kandidat.nomor_urut }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white">{{ kandidat.nama_kandidat || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-300 max-w-2xl whitespace-pre-line">{{ kandidat.visi_misi }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                                            <Link :href="route('admin.pemilu.kandidat.edit', [pemilu.id, kandidat.id])" class="text-cyan-400 hover:text-cyan-300 mr-3">Edit</Link>
                                            <Link :href="route('admin.pemilu.kandidat.destroy', [pemilu.id, kandidat.id])" method="delete" as="button" class="text-rose-400 hover:text-rose-300">Delete</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="kandidats.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No candidates added yet. Add one!</td>
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
