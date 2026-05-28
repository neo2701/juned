<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    pemilihs: Object,
});

function statusColor(status) {
    return status === 'REGISTERED'
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-amber-100 text-amber-700';
}

function statusLabel(status) {
    return status === 'REGISTERED' ? 'Registered' : 'Awaiting Registration';
}
</script>

<template>
    <Head title="Voter Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-juned-800 leading-tight">Voter (Pemilih) Management</h2>
                <Link :href="route('admin.pemilih.create')" class="inline-flex items-center gap-2 bg-juned-700 hover:bg-juned-800 text-white font-medium py-2 px-4 rounded-lg transition-all text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Register Voters
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Success Flash -->
                <div v-if="$page.props.flash?.success" class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4" role="alert">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-emerald-700">{{ $page.props.flash.success }}</p>
                    </div>
                </div>

                <!-- Import Errors -->
                <div v-if="$page.props.flash?.import_errors?.length" class="mb-6 rounded-xl bg-amber-50 border border-amber-200 p-4" role="alert">
                    <p class="text-sm font-medium text-amber-700 mb-2">Import warnings:</p>
                    <ul class="list-disc list-inside text-xs text-amber-600 space-y-1">
                        <li v-for="(err, i) in $page.props.flash.import_errors" :key="i">{{ err }}</li>
                    </ul>
                </div>

                <!-- Registration Link Info -->
                <div class="mb-6 rounded-xl bg-juned-100 border border-juned-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-juned-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.86-2.556a4.5 4.5 0 00-1.242-7.244l4.5-4.5a4.5 4.5 0 016.364 6.364l-1.757 1.757" />
                            </svg>
                            <div class="text-sm text-juned-text">
                                <span class="font-medium text-juned-800">Self-Registration URL:</span>
                                <code class="ml-2 bg-white px-2 py-0.5 rounded border border-juned-200 text-juned-700 text-xs">/voter/register</code>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a
                                :href="route('admin.export.voters.excel')"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-juned-200 bg-white px-3 py-1.5 text-xs font-medium text-juned-700 hover:bg-juned-100 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel
                            </a>
                            <a
                                :href="route('admin.export.voters.pdf')"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-juned-200 bg-white px-3 py-1.5 text-xs font-medium text-juned-700 hover:bg-juned-100 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                PDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white border border-juned-200 rounded-xl shadow-sm">
                    <div class="p-6 text-juned-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-juned-200">
                                <thead class="bg-juned-100">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">NIK</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-juned-text uppercase tracking-wider">Status</th>
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
                                            <div class="text-sm font-mono font-medium text-juned-800">{{ pemilih.nik }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-juned-text">{{ pemilih.nama_pemilih || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[statusColor(pemilih.registration_status), 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium']">
                                                {{ statusLabel(pemilih.registration_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-juned-text">
                                            {{ pemilih.registered_at ? new Date(pemilih.registered_at).toLocaleDateString() : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('admin.pemilih.destroy', pemilih.id)" method="delete" as="button" class="text-red-600 hover:text-red-500 transition">
                                                Delete
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="pemilihs.data.length === 0">
                                        <td colspan="6" class="px-6 py-8 text-center text-juned-text">No voters registered yet.</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="mt-4 flex gap-2" v-if="pemilihs.links.length > 3">
                                <Link
                                    v-for="link in pemilihs.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="px-3 py-1 border border-juned-200 rounded-lg text-sm"
                                    :class="{'bg-juned-700 text-white border-juned-700': link.active, 'text-juned-text hover:bg-juned-100': !link.active && link.url, 'text-juned-200 cursor-default': !link.url}"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
