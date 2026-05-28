<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center justify-center bg-white hover:bg-juned-100 text-juned-800 border border-juned-200 rounded-lg px-6 py-2.5 text-sm font-medium transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-juned-500/50 focus:ring-offset-2 disabled:opacity-50"
    >
        <slot />
    </button>
</template>
