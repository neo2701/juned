<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-sm font-semibold text-juned-800">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
