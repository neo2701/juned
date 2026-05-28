<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-juned-100 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <span class="text-2xl font-bold text-juned-800">JUNED</span>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white border border-juned-200 px-6 py-4 shadow-lg sm:max-w-md sm:rounded-2xl"
        >
            <slot />
        </div>
    </div>
</template>
