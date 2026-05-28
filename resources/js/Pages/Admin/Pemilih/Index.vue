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
                <h2 class="font-semibold text-xl text-juned-800 leading-tight">Voter (Pemilih) Management</h2>
                <Link :href="route('admin.pemilih.create')" class="bg-juned-800 hover:bg-juned-700 text-white font-bold py-2 px-4 rounded-lg transition-all">
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
                    <p class="mt-2 text-lg">NIK: <span class="font-mono bg-juned-100 px-2 py-1 rounded">{{ $page.props.flash.new_voter_nik }}</span></p>
                    <p class="mt-1 text-lg">Private Key: <span class="font-mono bg-juned-100 px-2 py-1 rounded">{{ $page.props.flash.new_private_key }}</span></p>
                    <p class="mt-2 text-sm italic">This is the ONLY time the Private Key will be displayed. It is securely hashed in the database.</p>
                </div>

                <div class="bg-white border border-juned-200 rounded-xl shadow-sm">
                    <div class="p-6 text-juned-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-juned-200">
                                <thead class="bg-juned-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">NIK</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Registered At</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-juned-text uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-juned-200">
                                    <tr v-for="pemilih in pemilihs.data" :key="pemilih.id" class="hover:bg-juned-100/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-juned-text">
                                            {{ pemilih.id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-juned-800">{{ pemilih.nik }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-juned-text">{{ pemilih.nama_pemilih || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-juned-text">
                                            {{ new Date(pemilih.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.pemilih.destroy', pemilih.id)" method="delete" as="button" class="text-red-600 hover:text-red-500">Revoke / Delete</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="pemilihs.data.length === 0">
                                        <td colspan="5" class="px-6 py-8 text-center text-juned-text">No voters registered yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="mt-4 flex gap-2" v-if="pemilihs.links.length > 3">
                                <Link v-for="link in pemilihs.links" :key="link.label" :href="link.url || '#'" v-html="link.label" class="px-3 py-1 border border-juned-200 rounded-lg text-juned-text" :class="{'bg-juned-500 text-white border-juned-500': link.active, 'text-juned-text': !link.url}"></Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
