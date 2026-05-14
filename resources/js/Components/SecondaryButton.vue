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
        class="inline-flex items-center bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-600 rounded-xl px-6 py-2.5 text-sm font-medium transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:ring-offset-2 focus:ring-offset-gray-900 disabled:opacity-50"
    >
        <slot />
    </button>
</template>
