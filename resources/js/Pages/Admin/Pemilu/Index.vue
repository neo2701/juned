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
                <h2 class="font-semibold text-xl text-juned-800 leading-tight">Pemilu Management</h2>
                <Link :href="route('admin.pemilu.create')" class="bg-juned-800 hover:bg-juned-700 text-white font-bold rounded-lg px-5 py-2.5 text-sm transition-all">
                    + Create Pemilu
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white border border-juned-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-juned-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Candidates</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-juned-text uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-juned-200">
                                    <tr v-for="pemilu in pemilus" :key="pemilu.id" class="hover:bg-juned-100 transition-colors group">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-juned-800">{{ pemilu.name }}</div>
                                            <div class="text-sm text-juned-text truncate max-w-xs">{{ pemilu.description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border"
                                                :class="{
                                                    'bg-amber-500/10 text-amber-400 border-amber-500/30': pemilu.status === 'DRAFT',
                                                    'bg-emerald-500/10 text-emerald-400 border-emerald-500/30': pemilu.status === 'BERJALAN',
                                                    'bg-gray-500/10 text-juned-text border-gray-500/30': pemilu.status === 'SELESAI',
                                                    'bg-violet-500/10 text-juned-500 border-violet-500/30': pemilu.status === 'DIPUBLIKASIKAN'
                                                }">
                                                {{ pemilu.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <Link :href="route('admin.pemilu.kandidat.index', pemilu.id)" class="text-juned-500 hover:text-juned-600 font-medium transition-colors">Manage Candidates</Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <Link :href="route('admin.pemilu.show', pemilu.id)" class="text-juned-500 hover:text-juned-600 transition-colors">View</Link>
                                            <Link :href="route('admin.pemilu.edit', pemilu.id)" class="text-juned-text hover:text-juned-800 transition-colors">Edit</Link>
                                            <Link :href="route('admin.pemilu.destroy', pemilu.id)" method="delete" as="button" class="text-red-600 hover:text-red-500 transition-colors">Delete</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="pemilus.length === 0">
                                        <td colspan="4" class="px-6 py-8 text-center text-juned-text">No elections found. Create one to get started!</td>
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
