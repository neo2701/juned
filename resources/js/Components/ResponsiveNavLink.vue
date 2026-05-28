<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-juned-500 text-start text-base font-medium text-juned-800 bg-juned-100 focus:outline-none focus:text-juned-800 focus:bg-juned-100 focus:border-juned-600 transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-juned-text hover:text-juned-800 hover:bg-juned-100 hover:border-juned-200 focus:outline-none focus:text-juned-800 focus:bg-juned-100 focus:border-juned-200 transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
