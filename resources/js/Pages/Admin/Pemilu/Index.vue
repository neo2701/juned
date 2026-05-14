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
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pemilu Management</h2>
                <Link :href="route('admin.pemilu.create')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                    + Create Pemilu
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
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidates</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="pemilu in pemilus" :key="pemilu.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ pemilu.name }}</div>
                                            <div class="text-sm text-gray-500 truncate max-w-xs">{{ pemilu.description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800': pemilu.status === 'DRAFT',
                                                    'bg-green-100 text-green-800': pemilu.status === 'BERJALAN',
                                                    'bg-gray-100 text-gray-800': pemilu.status === 'SELESAI',
                                                    'bg-blue-100 text-blue-800': pemilu.status === 'DIPUBLIKASIKAN'
                                                }">
                                                {{ pemilu.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <Link :href="route('admin.pemilu.kandidat.index', pemilu.id)" class="text-indigo-600 hover:text-indigo-900 font-medium">Manage Candidates</Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.pemilu.show', pemilu.id)" class="text-indigo-600 hover:text-indigo-900 mr-3">View</Link>
                                            <Link :href="route('admin.pemilu.edit', pemilu.id)" class="text-gray-600 hover:text-gray-900 mr-3">Edit</Link>
                                            <Link :href="route('admin.pemilu.destroy', pemilu.id)" method="delete" as="button" class="text-red-600 hover:text-red-900">Delete</Link>
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
