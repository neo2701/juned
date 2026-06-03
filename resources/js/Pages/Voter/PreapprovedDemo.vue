<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';

const props = defineProps({
    preapprovedNik: {
        type: Array,
        default: () => [],
    },
});

const query = ref('');
const copiedNik = ref('');

const filteredNik = computed(() => {
    if (!query.value) return props.preapprovedNik;
    const q = query.value.toLowerCase();
    return props.preapprovedNik.filter(item => {
        return String(item.nik).includes(q)
            || (item.nama_pemilih || '').toLowerCase().includes(q);
    });
});

async function copyNik(nik) {
    try {
        await navigator.clipboard.writeText(nik);
        copiedNik.value = nik;
        setTimeout(() => {
            if (copiedNik.value === nik) copiedNik.value = '';
        }, 1500);
    } catch {
        copiedNik.value = '';
    }
}
</script>

<template>
    <Head title="Demo: NIK Pre-Approved" />

    <div class="min-h-screen bg-white text-juned-800">
        <PublicHeader active="register" />

        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-juned-100 via-white to-juned-400/20"></div>
            <div class="absolute -top-16 -right-24 h-64 w-64 rounded-full bg-juned-500/10 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-16 h-64 w-64 rounded-full bg-juned-400/10 blur-3xl"></div>

            <div class="relative mx-auto max-w-[1200px] px-6 py-12 lg:px-10 lg:py-16">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-juned-600">Demo Voter Onboarding</p>
                        <h1 class="mt-3 text-3xl font-semibold text-juned-800 lg:text-4xl">
                            Daftar Cepat dengan NIK yang Sudah Disetujui
                        </h1>
                        <p class="mt-3 text-sm text-juned-text">
                            Pilih NIK yang sudah pre-approved, lalu lanjutkan ke halaman registrasi untuk membuat private key dan mendaftar.
                        </p>
                    </div>

                    <div class="w-full max-w-md">
                        <div class="flex items-center gap-2 rounded-2xl border border-juned-200 bg-white/80 px-4 py-3 shadow-sm">
                            <svg class="h-4 w-4 text-juned-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" />
                            </svg>
                            <input
                                v-model="query"
                                type="text"
                                placeholder="Cari NIK atau nama pemilih"
                                class="w-full border-0 bg-transparent text-sm text-juned-800 placeholder:text-juned-400 focus:ring-0"
                            />
                        </div>
                        <div class="mt-2 text-xs text-juned-text">
                            Menampilkan {{ filteredNik.length }} dari {{ props.preapprovedNik.length }} NIK tersedia.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-[1200px] px-6 pb-16 lg:px-10">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    v-for="item in filteredNik"
                    :key="item.nik"
                    class="group relative overflow-hidden rounded-2xl border border-juned-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-juned-400/60 hover:shadow-lg"
                >
                    <div class="absolute right-0 top-0 h-20 w-20 -translate-y-1/2 translate-x-1/2 rounded-full bg-juned-500/10 blur-2xl"></div>

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-juned-500">NIK</p>
                            <p class="mt-1 text-lg font-semibold tracking-wider text-juned-800">{{ item.nik }}</p>
                            <p class="mt-2 text-sm text-juned-text">
                                {{ item.nama_pemilih || 'Nama pemilih belum tersedia' }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="rounded-full bg-juned-100 px-3 py-1 text-[11px] font-semibold text-juned-700">APPROVED</span>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg border border-juned-200 px-3 py-1.5 text-xs font-semibold text-juned-700 transition hover:border-juned-400 hover:text-juned-800"
                                @click="copyNik(item.nik)"
                            >
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 17H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v2" />
                                    <rect x="8" y="8" width="12" height="12" rx="2" ry="2" />
                                </svg>
                                <span>{{ copiedNik === item.nik ? 'Tersalin' : 'Salin' }}</span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between">
                        <div class="text-xs text-juned-text">
                            Siap untuk registrasi mandiri
                        </div>
                        <Link
                            :href="route('voter.register', { nik: item.nik })"
                            class="inline-flex items-center justify-center rounded-xl bg-juned-800 px-4 py-2 text-xs font-semibold text-white transition hover:bg-juned-700"
                        >
                            Gunakan NIK
                        </Link>
                    </div>
                </div>
            </div>

            <div v-if="filteredNik.length === 0" class="mt-10 rounded-2xl border border-dashed border-juned-200 bg-white p-8 text-center">
                <p class="text-sm font-semibold text-juned-800">Tidak ada NIK yang cocok.</p>
                <p class="mt-1 text-xs text-juned-text">Coba kata kunci lain atau kosongkan pencarian.</p>
            </div>

            <div class="mt-12 flex flex-col items-start gap-3 rounded-2xl bg-juned-100/70 p-6 text-sm text-juned-700">
                <p class="font-semibold text-juned-800">Petunjuk Demo</p>
                <ol class="list-decimal space-y-1 pl-4">
                    <li>Pilih salah satu NIK yang tersedia.</li>
                    <li>Klik tombol "Gunakan NIK" untuk membuka halaman registrasi.</li>
                    <li>Lengkapi proses pembuatan private key dan simpan dengan aman.</li>
                </ol>
                <Link
                    :href="route('voter.register')"
                    class="mt-2 inline-flex items-center gap-2 text-xs font-semibold text-juned-700 hover:text-juned-800"
                >
                    Buka Registrasi Manual
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" />
                    </svg>
                </Link>
            </div>
        </section>
    </div>
</template>
