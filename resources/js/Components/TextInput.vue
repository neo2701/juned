<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="bg-gray-800/50 border border-gray-600 focus:border-cyan-500 focus:ring-cyan-500/20 text-white placeholder-gray-500 rounded-xl px-4 py-2.5 transition-colors duration-200"
        v-model="model"
        ref="input"
    />
</template>
