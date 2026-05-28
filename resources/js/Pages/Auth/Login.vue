<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Admin Login" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-juned-100 relative overflow-hidden">
        <!-- Background decorative -->
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-juned-400/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 right-1/4 w-64 h-64 bg-juned-500/5 rounded-full blur-3xl"></div>
        </div>

        <!-- Card -->
        <div class="relative z-10 w-full sm:max-w-md mt-6 px-6 py-8 bg-white border border-juned-200 shadow-lg rounded-2xl">
            <!-- Header -->
            <div class="mb-8 text-center">
                <div class="flex items-center justify-center mb-4">
                    <div class="w-12 h-12 rounded-xl bg-juned-400/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-juned-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-juned-800">Admin Portal</h1>
                <p class="mt-2 text-sm text-juned-text">JUNED E-Voting Management</p>
            </div>

            <div v-if="status" class="mb-4 text-sm font-medium text-juned-500 text-center">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="admin@example.com"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="mt-4">
                    <InputLabel for="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="mt-4 block">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-juned-text">Remember me</span>
                    </label>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-juned-text hover:text-juned-500 transition-colors"
                    >
                        Forgot password?
                    </Link>

                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Log in
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>
