<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    active: {
        type: String,
        default: '',
    },
});
</script>

<template>
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
                <Link href="/" :class="['h-full flex items-center px-1 text-sm transition-colors', active === 'beranda' ? 'font-bold text-juned-800 border-b-2 border-juned-600' : 'font-medium text-juned-text hover:text-juned-800']">
                    Beranda
                </Link>
                <Link :href="route('voter.login')" :class="['h-full flex items-center px-1 text-sm transition-colors', active === 'voting' ? 'font-bold text-juned-800 border-b-2 border-juned-600' : 'font-medium text-juned-text hover:text-juned-800']">
                    Sistem Voting
                </Link>
                <Link :href="route('voter.register')" :class="['h-full flex items-center px-1 text-sm transition-colors', active === 'register' ? 'font-bold text-juned-800 border-b-2 border-juned-600' : 'font-medium text-juned-text hover:text-juned-800']">
                    Registrasi
                </Link>
                <Link :href="route('public.audit.index')" :class="['h-full flex items-center px-1 text-sm transition-colors', active === 'audit' ? 'font-bold text-juned-800 border-b-2 border-juned-600' : 'font-medium text-juned-text hover:text-juned-800']">
                    Audit Publik
                </Link>
                <Link :href="route('edukasi')" :class="['h-full flex items-center px-1 text-sm transition-colors', active === 'edukasi' ? 'font-bold text-juned-800 border-b-2 border-juned-600' : 'font-medium text-juned-text hover:text-juned-800']">
                    Edukasi
                </Link>
            </nav>

            <!-- Right Buttons -->
            <div class="flex items-center gap-3">
                <Link
                    :href="route('voter.register')"
                    class="hidden sm:inline-flex items-center justify-center border border-juned-200 hover:bg-juned-100 text-juned-800 font-bold text-sm rounded-xl px-5 py-1.5 transition-all"
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
</template>
