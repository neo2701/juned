<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pemilihs: Object,
});
</script>

<template>
    <Head title="Voter Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-white leading-tight">Voter (Pemilih) Management</h2>
                <Link :href="route('admin.pemilih.create')" class="bg-gradient-to-r from-cyan-600 to-violet-600 hover:from-cyan-500 hover:to-violet-500 text-white font-semibold py-2 px-4 rounded-xl shadow-lg shadow-cyan-500/20 transition-all">
                    + Register Voter
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-6 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Success!</strong>
                    <span class="block sm:inline"> {{ $page.props.flash.success }}</span>
                </div>

                <div v-if="$page.props.flash?.new_private_key" class="mb-6 bg-amber-500/10 border border-amber-500/30 text-amber-300 p-4 shadow-md" role="alert">
                    <p class="font-bold">IMPORTANT: Securely distribute these credentials to the voter.</p>
                    <p class="mt-2 text-lg">NIK: <span class="font-mono bg-gray-800 px-2 py-1 rounded">{{ $page.props.flash.new_voter_nik }}</span></p>
                    <p class="mt-1 text-lg">Private Key: <span class="font-mono bg-gray-800 px-2 py-1 rounded">{{ $page.props.flash.new_private_key }}</span></p>
                    <p class="mt-2 text-sm italic">This is the ONLY time the Private Key will be displayed. It is securely hashed in the database.</p>
                </div>

                <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl">
                    <div class="p-6 text-white">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-800/50">
                                <thead class="bg-gray-800/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">NIK</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Registered At</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800/50">
                                    <tr v-for="pemilih in pemilihs.data" :key="pemilih.id" class="hover:bg-gray-800/30 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ pemilih.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-white">{{ pemilih.nik }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-300">{{ pemilih.nama_pemilih || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                            {{ new Date(pemilih.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.pemilih.destroy', pemilih.id)" method="delete" as="button" class="text-rose-400 hover:text-rose-300">Revoke / Delete</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="pemilihs.data.length === 0">
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No voters registered yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="mt-4 flex gap-2" v-if="pemilihs.links.length > 3">
                                <Link v-for="link in pemilihs.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 border border-gray-700 rounded-lg text-gray-400" :class="{'bg-cyan-600 text-white border-cyan-500': link.active, 'text-gray-600': !link.url}"></Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
