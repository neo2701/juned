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
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Candidates: {{ pemilu.name }}</h2>
                    <Link :href="route('admin.pemilu.index')" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Elections</Link>
                </div>
                <Link :href="route('admin.pemilu.kandidat.create', pemilu.id)" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    + Add Candidate
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visi & Misi</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="kandidat in kandidats" :key="kandidat.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-2xl font-bold text-gray-900">#{{ kandidat.nomor_urut }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ kandidat.nama_kandidat || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-700 max-w-2xl whitespace-pre-line">{{ kandidat.visi_misi }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium align-top">
                                            <Link :href="route('admin.pemilu.kandidat.edit', [pemilu.id, kandidat.id])" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</Link>
                                            <Link :href="route('admin.pemilu.kandidat.destroy', [pemilu.id, kandidat.id])" method="delete" as="button" class="text-red-600 hover:text-red-900">Delete</Link>
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
