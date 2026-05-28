<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm leading-5 text-juned-text transition duration-150 ease-in-out hover:bg-juned-100 hover:text-juned-800 focus:bg-juned-100 focus:text-juned-800 focus:outline-none"
    >
        <slot />
    </Link>
</template>
