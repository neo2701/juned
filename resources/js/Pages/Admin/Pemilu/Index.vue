<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pemilus: Array,
});
</script>

<template>
    <Head title="Pemilu Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white leading-tight">Pemilu Management</h2>
                <Link :href="route('admin.pemilu.create')" class="bg-gradient-to-r from-cyan-500 to-violet-500 hover:from-cyan-400 hover:to-violet-400 text-white font-medium rounded-xl px-5 py-2.5 text-sm transition-all shadow-lg shadow-cyan-500/25">
                    + Create Pemilu
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-700/50">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidates</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700/30">
                                    <tr v-for="pemilu in pemilus" :key="pemilu.id" class="hover:bg-gray-800/50 transition-colors group">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-white">{{ pemilu.name }}</div>
                                            <div class="text-sm text-gray-500 truncate max-w-xs">{{ pemilu.description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                                :class="{
                                                    'bg-amber-500/10 text-amber-400 border-amber-500/30': pemilu.status === 'DRAFT',
                                                    'bg-emerald-500/10 text-emerald-400 border-emerald-500/30': pemilu.status === 'BERJALAN',
                                                    'bg-gray-500/10 text-gray-400 border-gray-500/30': pemilu.status === 'SELESAI',
                                                    'bg-violet-500/10 text-violet-400 border-violet-500/30': pemilu.status === 'DIPUBLIKASIKAN'
                                                }">
                                                {{ pemilu.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <Link :href="route('admin.pemilu.kandidat.index', pemilu.id)" class="text-cyan-400 hover:text-cyan-300 font-medium transition-colors">Manage Candidates</Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <Link :href="route('admin.pemilu.show', pemilu.id)" class="text-cyan-400 hover:text-cyan-300 transition-colors">View</Link>
                                            <Link :href="route('admin.pemilu.edit', pemilu.id)" class="text-gray-400 hover:text-gray-200 transition-colors">Edit</Link>
                                            <Link :href="route('admin.pemilu.destroy', pemilu.id)" method="delete" as="button" class="text-rose-400 hover:text-rose-300 transition-colors">Delete</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="pemilus.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No elections found. Create one to get started!</td>
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
