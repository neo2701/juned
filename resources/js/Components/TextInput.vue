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
        class="bg-white border border-juned-200 focus:border-juned-500 focus:ring-juned-500/20 text-juned-dark placeholder-juned-200 rounded-lg px-4 py-2.5 text-sm transition-colors duration-200"
        v-model="model"
        ref="input"
    />
</template>
