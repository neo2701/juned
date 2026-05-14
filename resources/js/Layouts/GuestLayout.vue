<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-[#0a0a0f] pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <span class="text-2xl font-bold text-gradient">JUNED</span>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 px-6 py-4 shadow-2xl sm:max-w-md sm:rounded-2xl"
        >
            <slot />
        </div>
    </div>
</template>
