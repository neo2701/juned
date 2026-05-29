<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Platform Demokrasi Digital" />

    <div class="min-h-screen bg-white font-sans">
        <!-- Header - TopNavBar -->
        <header class="sticky top-0 z-50 w-full bg-white border-b border-juned-200 shadow-sm">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8 flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <Link href="/" class="text-xl font-semibold text-juned-800 tracking-tight" style="letter-spacing: -0.025em;">
                        JUNED
                    </Link>
                </div>

                <!-- Navigation Links -->
                <nav class="hidden lg:flex items-center gap-6 h-full">
                    <a href="/" class="relative h-full flex items-center px-1 text-sm font-bold text-juned-800 border-b-2 border-juned-600">Beranda</a>
                    <Link :href="route('dashboard')" class="h-full flex items-center px-1 text-sm font-medium text-juned-text hover:text-juned-800 transition-colors">Dashboard</Link>
                    <Link :href="route('voter.login')" class="h-full flex items-center px-1 text-sm font-medium text-juned-text hover:text-juned-800 transition-colors">Sistem Voting</Link>
                    <Link :href="route('voter.register')" class="h-full flex items-center px-1 text-sm font-medium text-juned-text hover:text-juned-800 transition-colors">Registrasi</Link>
                    <Link :href="route('public.audit.index')" class="h-full flex items-center px-1 text-sm font-medium text-juned-text hover:text-juned-800 transition-colors">Audit Publik</Link>
                    <a href="#" class="h-full flex items-center px-1 text-sm font-medium text-juned-text hover:text-juned-800 transition-colors">Edukasi</a>
                    <a href="#" class="h-full flex items-center px-1 text-sm font-medium text-juned-text hover:text-juned-800 transition-colors">Komunitas</a>
                </nav>

                <!-- Right Buttons -->
                <div class="flex items-center gap-3">
                    <a href="#" class="text-sm font-medium text-juned-text hover:text-juned-800 transition-colors px-3 py-1 rounded">
                        Bantuan
                    </a>
                    <Link
                        :href="route('voter.register')"
                        class="inline-flex items-center justify-center border border-juned-200 hover:bg-juned-100 text-juned-800 font-bold text-sm rounded-xl px-5 py-1.5 transition-all"
                    >
                        Daftar
                    </Link>
                    <Link
                        :href="route('voter.login')"
                        class="inline-flex items-center justify-center bg-juned-800 hover:bg-juned-700 text-white font-bold text-sm rounded-xl px-6 py-1.5 transition-all"
                    >
                        Masuk
                    </Link>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative w-full overflow-hidden bg-juned-100 py-16 lg:py-24">
            <!-- Background decorative elements -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-10 left-10 w-64 h-64 bg-juned-400/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-10 right-10 w-96 h-96 bg-juned-500/5 rounded-full blur-3xl"></div>
            </div>

            <div class="relative max-w-[1200px] mx-auto px-6 lg:px-8">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Content -->
                    <div class="space-y-6">
                        <!-- ISO Badge -->
                        <div class="inline-flex items-center gap-2 bg-white/80 backdrop-blur-sm border border-juned-200 rounded-full px-4 py-2 shadow-sm">
                            <svg class="w-4 h-4 text-juned-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.403 12.652a3 3 0 000-5.304 3 3 0 00-3.75-3.751 3 3 0 00-5.305 0 3 3 0 00-3.751 3.75 3 3 0 000 5.305 3 3 0 003.75 3.751 3 3 0 005.305 0 3 3 0 003.751-3.75zm-2.546-4.46a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-semibold text-juned-500">Sertifikasi ISO 27001 Terverifikasi</span>
                        </div>

                        <!-- Heading -->
                        <h1 class="text-4xl lg:text-5xl font-bold text-juned-800 leading-tight">
                            JUNED E-Voting
                        </h1>

                        <!-- Description -->
                        <p class="text-lg text-juned-text leading-relaxed max-w-lg">
                            Membangun masa depan demokrasi dengan teknologi enkripsi end-to-end. Partisipasi warga yang terjamin privasinya dan hasil yang dapat diaudit secara publik.
                        </p>

                        <!-- CTA Buttons -->
                        <div class="flex flex-wrap gap-4">
                            <Link
                                :href="route('voter.register')"
                                class="inline-flex items-center justify-center bg-juned-800 hover:bg-juned-700 text-white font-bold rounded-xl px-6 py-3 text-base transition-all shadow-lg shadow-juned-800/25"
                            >
                                Daftar Sekarang
                            </Link>
                            <Link
                                :href="route('voter.login')"
                                class="inline-flex items-center gap-2 bg-transparent hover:bg-juned-800/5 text-juned-800 font-bold rounded-xl px-6 py-3 text-base transition-all border border-juned-200"
                            >
                                Mulai Voting
                            </Link>
                            <Link
                                :href="route('public.audit.index')"
                                class="inline-flex items-center gap-2 bg-transparent hover:bg-juned-800/5 text-juned-800 font-bold rounded-xl px-6 py-3 text-base transition-all border border-juned-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 0l3-3m-3 3l3 3m-8 0a9 9 0 1118 0 9 9 0 01-18 0z" />
                                </svg>
                                Pelajari Audit
                            </Link>
                        </div>

                        <!-- Social proof -->
                        <div class="flex items-center gap-3 pt-4">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 rounded-full border-2 border-white overflow-hidden bg-juned-400/20">
                                    <svg class="w-full h-full text-juned-600" viewBox="0 0 32 32" fill="currentColor"><circle cx="16" cy="12" r="5"/><path d="M6 28c0-5.5 4.5-10 10-10s10 4.5 10 10"/></svg>
                                </div>
                                <div class="w-8 h-8 rounded-full border-2 border-white overflow-hidden bg-juned-500/20">
                                    <svg class="w-full h-full text-juned-700" viewBox="0 0 32 32" fill="currentColor"><circle cx="16" cy="12" r="5"/><path d="M6 28c0-5.5 4.5-10 10-10s10 4.5 10 10"/></svg>
                                </div>
                                <div class="w-8 h-8 rounded-full border-2 border-white overflow-hidden bg-juned-600/20">
                                    <svg class="w-full h-full text-juned-800" viewBox="0 0 32 32" fill="currentColor"><circle cx="16" cy="12" r="5"/><path d="M6 28c0-5.5 4.5-10 10-10s10 4.5 10 10"/></svg>
                                </div>
                            </div>
                            <span class="text-sm font-medium text-juned-text">Bergabung dengan 12.5k+ pemilih aktif bulan ini</span>
                        </div>
                    </div>

                    <!-- Right Content - Hero Illustration -->
                    <div class="relative flex justify-center lg:justify-end">
                        <!-- Decorative background -->
                        <div class="absolute inset-0 bg-gradient-to-br from-juned-400/20 to-juned-500/10 rounded-3xl blur-2xl"></div>

                        <div class="relative w-full max-w-md">
                            <!-- Main Illustration: Shield + Blockchain visual -->
                            <svg class="w-full h-auto" viewBox="0 0 480 420" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <!-- Background circle -->
                                <circle cx="240" cy="210" r="180" fill="#6CF8BB" fill-opacity="0.08"/>
                                <circle cx="240" cy="210" r="140" fill="#6CF8BB" fill-opacity="0.05" stroke="#6CF8BB" stroke-opacity="0.2" stroke-width="1"/>

                                <!-- Blockchain nodes -->
                                <circle cx="120" cy="120" r="8" fill="#003527"/>
                                <circle cx="360" cy="120" r="8" fill="#003527"/>
                                <circle cx="120" cy="300" r="8" fill="#003527"/>
                                <circle cx="360" cy="300" r="8" fill="#003527"/>
                                <circle cx="240" cy="80" r="6" fill="#00714D"/>
                                <circle cx="240" cy="340" r="6" fill="#00714D"/>
                                <circle cx="80" cy="210" r="6" fill="#00714D"/>
                                <circle cx="400" cy="210" r="6" fill="#00714D"/>

                                <!-- Connection lines -->
                                <line x1="120" y1="120" x2="240" y2="80" stroke="#BFC9C3" stroke-width="1" stroke-dasharray="4 4"/>
                                <line x1="360" y1="120" x2="240" y2="80" stroke="#BFC9C3" stroke-width="1" stroke-dasharray="4 4"/>
                                <line x1="120" y1="300" x2="240" y2="340" stroke="#BFC9C3" stroke-width="1" stroke-dasharray="4 4"/>
                                <line x1="360" y1="300" x2="240" y2="340" stroke="#BFC9C3" stroke-width="1" stroke-dasharray="4 4"/>
                                <line x1="80" y1="210" x2="120" y2="120" stroke="#BFC9C3" stroke-width="1" stroke-dasharray="4 4"/>
                                <line x1="80" y1="210" x2="120" y2="300" stroke="#BFC9C3" stroke-width="1" stroke-dasharray="4 4"/>
                                <line x1="400" y1="210" x2="360" y2="120" stroke="#BFC9C3" stroke-width="1" stroke-dasharray="4 4"/>
                                <line x1="400" y1="210" x2="360" y2="300" stroke="#BFC9C3" stroke-width="1" stroke-dasharray="4 4"/>

                                <!-- Central shield -->
                                <path d="M240 130 C240 130 280 140 300 145 C300 145 300 220 300 240 C300 280 240 310 240 310 C240 310 180 280 180 240 C180 220 180 145 180 145 C200 140 240 130 240 130Z" fill="#003527" fill-opacity="0.9"/>
                                <path d="M240 145 C240 145 272 153 288 157 C288 157 288 220 288 235 C288 268 240 293 240 293 C240 293 192 268 192 235 C192 220 192 157 192 157 C208 153 240 145 240 145Z" fill="#00714D"/>

                                <!-- Checkmark inside shield -->
                                <path d="M218 210 L233 225 L262 196" stroke="#6CF8BB" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>

                                <!-- Floating data blocks -->
                                <rect x="310" y="160" width="80" height="32" rx="6" fill="white" stroke="#BFC9C3" stroke-width="1"/>
                                <rect x="318" y="170" width="40" height="4" rx="2" fill="#BFC9C3"/>
                                <rect x="318" y="180" width="28" height="4" rx="2" fill="#6CF8BB"/>

                                <rect x="90" y="240" width="80" height="32" rx="6" fill="white" stroke="#BFC9C3" stroke-width="1"/>
                                <rect x="98" y="250" width="40" height="4" rx="2" fill="#BFC9C3"/>
                                <rect x="98" y="260" width="28" height="4" rx="2" fill="#6CF8BB"/>

                                <rect x="320" y="270" width="70" height="28" rx="6" fill="white" stroke="#BFC9C3" stroke-width="1"/>
                                <rect x="328" y="279" width="35" height="4" rx="2" fill="#BFC9C3"/>
                                <rect x="328" y="287" width="24" height="4" rx="2" fill="#6CF8BB"/>

                                <!-- Lock icon top-right -->
                                <rect x="350" y="60" width="36" height="36" rx="8" fill="white" stroke="#E2E2E2" stroke-width="1"/>
                                <path d="M368 72 C368 72 364 72 362 74 C360 76 360 78 360 78 L360 80 L376 80 L376 78 C376 78 376 76 374 74 C372 72 368 72 368 72Z" stroke="#003527" stroke-width="1.5" fill="none"/>
                                <rect x="358" y="80" width="20" height="14" rx="3" fill="#003527"/>

                                <!-- Vote icon bottom-left -->
                                <rect x="70" cy="330" width="36" height="36" rx="8" fill="white" stroke="#E2E2E2" stroke-width="1" y="320"/>
                                <path d="M82 332 L88 338 L98 328" stroke="#00714D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="80" y="342" width="20" height="3" rx="1.5" fill="#BFC9C3"/>
                                <rect x="80" y="348" width="14" height="3" rx="1.5" fill="#BFC9C3"/>
                            </svg>

                            <!-- Security Status Card overlay -->
                            <div class="absolute bottom-6 left-6 bg-white/90 backdrop-blur-xl border border-juned-200/50 rounded-2xl p-5 shadow-xl">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-juned-400/20 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-juned-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-semibold text-juned-text">Status Keamanan</p>
                                        <p class="text-sm font-bold text-juned-800">100% Terenkripsi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats & Trust Bar -->
        <section class="w-full bg-white py-12 border-y border-juned-100">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                    <div class="text-center">
                        <p class="text-3xl font-semibold text-juned-800">2.4jt+</p>
                        <p class="text-sm font-medium text-juned-text mt-1">Suara Terverifikasi</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-semibold text-juned-800">145</p>
                        <p class="text-sm font-medium text-juned-text mt-1">Lembaga Terdaftar</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-semibold text-juned-800">0.0%</p>
                        <p class="text-sm font-medium text-juned-text mt-1">Margin Eror</p>
                    </div>
                    <div class="text-center">
                        <p class="text-3xl font-semibold text-juned-800">24/7</p>
                        <p class="text-sm font-medium text-juned-text mt-1">Audit Real-time</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Bento Grid Section -->
        <section class="w-full bg-white py-16 lg:py-24">
            <div class="max-w-[1152px] mx-auto px-6 lg:px-8">
                <!-- Section Header -->
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-semibold text-juned-800 mb-2">Sistem Demokrasi Tanpa Celah</h2>
                    <p class="text-base text-juned-text max-w-2xl mx-auto">
                        Dirancang untuk menjamin integritas setiap suara dengan standar keamanan perbankan internasional.
                    </p>
                </div>

                <!-- Bento Grid -->
                <div class="grid lg:grid-cols-3 gap-6">
                    <!-- Main Feature Card - Audit Terbuka (spans 1 col, tall) -->
                    <div class="bg-juned-800 rounded-2xl p-8 text-white flex flex-col justify-between min-h-[400px]">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-juned-400/20 flex items-center justify-center mb-6">
                                <svg class="w-5 h-5 text-juned-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-3">Audit Terbuka</h3>
                            <p class="text-sm font-medium text-white/80 leading-relaxed">
                                Siapa pun dapat memverifikasi integritas sistem melalui portal audit publik tanpa melanggar privasi pemilih.
                            </p>
                        </div>
                        <Link
                            :href="route('public.audit.index')"
                            class="inline-flex items-center justify-center bg-juned-400 hover:bg-juned-300 text-juned-800 font-bold rounded-xl px-5 py-2.5 text-sm transition-all mt-6 w-fit"
                        >
                            Cek Dashboard Audit
                        </Link>
                    </div>

                    <!-- Bottom Bento Row (spans 2 cols) -->
                    <div class="lg:col-span-2 grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Biometrik Multi-Lapis -->
                        <div class="bg-juned-100 rounded-2xl p-6 flex flex-col">
                            <div class="w-10 h-10 rounded-xl bg-juned-500/10 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5 text-juned-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.864 4.243A7.5 7.5 0 0119.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 004.5 10.5a48.667 48.667 0 00-1.418 8.773M6.75 10.5a3.75 3.75 0 117.5 0c0 2.57-.37 5.054-1.057 7.395M12 10.5a48.524 48.524 0 01-1.023 7.55" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-juned-800 mb-2">Biometrik Multi-Lapis</h4>
                            <p class="text-sm font-medium text-juned-text leading-relaxed">
                                Verifikasi suara menggunakan pengenalan wajah atau sidik jari untuk mencegah duplikasi.
                            </p>
                        </div>

                        <!-- Sinkronisasi Instan -->
                        <div class="bg-juned-100 rounded-2xl p-6 flex flex-col">
                            <div class="w-10 h-10 rounded-xl bg-juned-500/10 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5 text-juned-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-juned-800 mb-2">Sinkronisasi Instan</h4>
                            <p class="text-sm font-medium text-juned-text leading-relaxed">
                                Hasil penghitungan suara diperbarui secara real-time ke seluruh node jaringan terdesentralisasi.
                            </p>
                        </div>

                        <!-- Laporan PDF Terenkripsi -->
                        <div class="bg-juned-100 rounded-2xl p-6 flex flex-col">
                            <div class="w-10 h-10 rounded-xl bg-juned-500/10 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5 text-juned-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-juned-800 mb-2">Laporan PDF Terenkripsi</h4>
                            <p class="text-sm font-medium text-juned-text leading-relaxed">
                                Setiap pemilih mendapatkan tanda terima digital unik yang dapat dicek validitasnya.
                            </p>
                        </div>
                    </div>

                    <!-- Perisai Identitas Digital - Full width card -->
                    <div class="lg:col-span-3 bg-juned-100 rounded-2xl p-8 flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="w-10 h-10 rounded-xl bg-juned-500/10 flex items-center justify-center mb-4">
                                <svg class="w-5 h-5 text-juned-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-juned-800 mb-3">Perisai Identitas Digital</h3>
                            <p class="text-base text-juned-text leading-relaxed max-w-xl">
                                Data pribadi Anda dide-identifikasi sebelum dikirim ke server voting. Identitas asli tidak pernah disimpan bersama dengan pilihan suara Anda.
                            </p>
                        </div>
                        <!-- Mini illustration + stat -->
                        <div class="flex flex-col items-center gap-3">
                            <svg class="w-24 h-24" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="48" cy="48" r="40" fill="#6CF8BB" fill-opacity="0.15"/>
                                <path d="M48 20 C48 20 64 26 68 28 C68 28 68 50 68 56 C68 72 48 80 48 80 C48 80 28 72 28 56 C28 50 28 28 28 28 C32 26 48 20 48 20Z" fill="#003527" fill-opacity="0.1" stroke="#003527" stroke-width="2"/>
                                <circle cx="48" cy="42" r="8" fill="none" stroke="#00714D" stroke-width="2"/>
                                <path d="M38 62 C38 56 42 52 48 52 C54 52 58 56 58 62" stroke="#00714D" stroke-width="2" stroke-linecap="round"/>
                                <path d="M42 42 L48 48 L56 38" stroke="#6CF8BB" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" opacity="0"/>
                                <!-- De-identification lines -->
                                <line x1="36" y1="44" x2="60" y2="44" stroke="#6CF8BB" stroke-width="3" stroke-linecap="round" opacity="0.7"/>
                            </svg>
                            <div class="bg-white rounded-xl px-5 py-3 shadow-sm border border-juned-200/50">
                                <p class="text-xs font-bold text-juned-800">85% Kepercayaan Publik</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative w-full py-20 lg:py-28 overflow-hidden">
            <!-- Background -->
            <div class="absolute inset-0 bg-juned-800"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-juned-700/50 to-juned-900/80"></div>
            <!-- Abstract pattern overlay -->
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, rgba(108, 248, 187, 0.3) 0%, transparent 50%), radial-gradient(circle at 80% 50%, rgba(108, 248, 187, 0.2) 0%, transparent 50%);"></div>

            <div class="relative max-w-[800px] mx-auto px-6 lg:px-8 text-center">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">Siap Untuk Berpartisipasi?</h2>
                <p class="text-lg text-white/80 mb-10 max-w-2xl mx-auto">
                    Satu suara Anda sangat berharga bagi kemajuan bangsa. Mari berdemokrasi dengan cara yang lebih modern, aman, dan cerdas bersama JUNED.
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <Link
                        :href="route('voter.register')"
                        class="inline-flex items-center justify-center bg-juned-400 hover:bg-juned-300 text-juned-900 font-bold rounded-xl px-8 py-3.5 text-lg transition-all shadow-lg"
                    >
                        Daftar & Mulai Voting
                    </Link>
                    <Link
                        :href="route('voter.login')"
                        class="inline-flex items-center justify-center bg-transparent hover:bg-white/10 text-white font-bold rounded-xl px-8 py-3.5 text-lg transition-all border border-white/30"
                    >
                        Sudah Terdaftar? Masuk
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="w-full bg-white border-t border-juned-100 py-12">
            <div class="max-w-[1200px] mx-auto px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-10">
                    <!-- Brand -->
                    <div class="lg:col-span-1">
                        <h3 class="text-3xl font-semibold text-juned-800 mb-3">JUNED</h3>
                        <p class="text-base text-juned-text leading-relaxed">
                            Platform demokrasi digital masa depan dengan teknologi audit blockchain terintegrasi.
                        </p>
                    </div>

                    <!-- Tautan Cepat -->
                    <div>
                        <h4 class="text-sm font-bold text-juned-800 mb-4">Tautan Cepat</h4>
                        <ul class="space-y-2.5">
                            <li><a href="#" class="text-xs font-semibold text-juned-text hover:text-juned-500 transition-colors">Tentang Kami</a></li>
                            <li><a href="#" class="text-xs font-semibold text-juned-text hover:text-juned-500 transition-colors">Panduan Memilih</a></li>
                            <li><a href="#" class="text-xs font-semibold text-juned-text hover:text-juned-500 transition-colors">Laporan Transparansi</a></li>
                            <li><a href="#" class="text-xs font-semibold text-juned-text hover:text-juned-500 transition-colors">Kontak Media</a></li>
                        </ul>
                    </div>

                    <!-- Legalitas -->
                    <div>
                        <h4 class="text-sm font-bold text-juned-800 mb-4">Legalitas</h4>
                        <ul class="space-y-2.5">
                            <li><a href="#" class="text-xs font-semibold text-juned-text hover:text-juned-500 transition-colors">Kebijakan Privasi</a></li>
                            <li><a href="#" class="text-xs font-semibold text-juned-text hover:text-juned-500 transition-colors">Syarat & Ketentuan</a></li>
                        </ul>
                    </div>

                    <!-- Ikuti Kami -->
                    <div>
                        <h4 class="text-sm font-bold text-juned-800 mb-4">Ikuti Kami</h4>
                        <div class="flex gap-3">
                            <!-- Twitter/X -->
                            <a href="#" class="w-9 h-9 rounded-lg bg-juned-100 flex items-center justify-center hover:bg-juned-200 transition-colors" aria-label="Twitter">
                                <svg class="w-4 h-4 text-juned-text" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                            <!-- Instagram -->
                            <a href="#" class="w-9 h-9 rounded-lg bg-juned-100 flex items-center justify-center hover:bg-juned-200 transition-colors" aria-label="Instagram">
                                <svg class="w-4 h-4 text-juned-text" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                                </svg>
                            </a>
                            <!-- LinkedIn -->
                            <a href="#" class="w-9 h-9 rounded-lg bg-juned-100 flex items-center justify-center hover:bg-juned-200 transition-colors" aria-label="LinkedIn">
                                <svg class="w-4 h-4 text-juned-text" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bottom bar -->
                <div class="border-t border-juned-100 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <p class="text-xs font-semibold text-juned-text">
                        © 2026 JUNED. Platform Demokrasi Digital Terpercaya. Seluruh hak cipta dilindungi undang-undang.
                    </p>
                    <p class="text-xs font-semibold text-juned-text">
                        Develop by Kelompok 8 Josjis
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
