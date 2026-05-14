<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="JUNED E-Voting" />

    <div class="min-h-screen bg-[#0a0a0f] relative overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <!-- Grid pattern -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(rgba(6, 182, 212, 0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(6, 182, 212, 0.3) 1px, transparent 1px); background-size: 60px 60px;"></div>
            <!-- Gradient orbs -->
            <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl animate-pulse-glow"></div>
            <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-violet-500/10 rounded-full blur-3xl animate-pulse-glow" style="animation-delay: 1.5s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-emerald-500/5 rounded-full blur-3xl animate-pulse-glow" style="animation-delay: 3s;"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 flex flex-col min-h-screen">
            <!-- Navigation -->
            <nav class="flex justify-end items-center p-6 max-w-7xl mx-auto w-full">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('login')"
                        class="text-gray-400 hover:text-white text-sm font-medium transition-colors"
                    >
                        Admin Login
                    </Link>
                    <Link
                        :href="route('voter.login')"
                        class="bg-gradient-to-r from-cyan-500 to-violet-500 hover:from-cyan-400 hover:to-violet-400 text-white font-medium rounded-xl px-5 py-2 text-sm transition-all shadow-lg shadow-cyan-500/25"
                    >
                        Voter Login
                    </Link>
                </div>
            </nav>

            <!-- Hero Section -->
            <main class="flex-1 flex flex-col items-center justify-center px-6 -mt-16">
                <div class="text-center max-w-4xl mx-auto">
                    <!-- Logo / Title -->
                    <h1 class="text-6xl sm:text-7xl lg:text-8xl font-extrabold tracking-tight mb-6">
                        <span class="bg-gradient-to-r from-cyan-400 via-violet-400 to-emerald-400 bg-clip-text text-transparent animate-gradient" style="background-size: 200% 200%;">
                            JUNED
                        </span>
                        <span class="block text-3xl sm:text-4xl lg:text-5xl text-white mt-2">E-Voting</span>
                    </h1>

                    <!-- Tagline -->
                    <p class="text-xl sm:text-2xl text-gray-400 font-light mb-12 tracking-wide">
                        Secure. Anonymous. Verifiable.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-20">
                        <Link
                            :href="route('voter.login')"
                            class="bg-gradient-to-r from-cyan-500 to-violet-500 hover:from-cyan-400 hover:to-violet-400 text-white font-semibold rounded-xl px-8 py-3.5 text-lg transition-all shadow-lg shadow-cyan-500/25 w-full sm:w-auto text-center"
                        >
                            Enter Voting Booth
                        </Link>
                        <Link
                            :href="route('login')"
                            class="bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-600 rounded-xl px-8 py-3.5 text-lg font-medium transition-all w-full sm:w-auto text-center"
                        >
                            Admin Portal
                        </Link>
                    </div>

                    <!-- Feature Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 max-w-4xl mx-auto">
                        <!-- Zero-Knowledge Proofs -->
                        <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-6 shadow-2xl hover:border-cyan-500/30 transition-all duration-300 group">
                            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center mb-4 mx-auto group-hover:glow-cyan transition-all">
                                <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">Zero-Knowledge Proofs</h3>
                            <p class="text-gray-500 text-sm">Prove your eligibility without revealing your identity using zk-SNARKs.</p>
                        </div>

                        <!-- Anonymous Voting -->
                        <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-6 shadow-2xl hover:border-violet-500/30 transition-all duration-300 group">
                            <div class="w-12 h-12 rounded-xl bg-violet-500/10 flex items-center justify-center mb-4 mx-auto group-hover:glow-violet transition-all">
                                <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">Anonymous Voting</h3>
                            <p class="text-gray-500 text-sm">Your vote is completely unlinkable to your identity through cryptographic nullifiers.</p>
                        </div>

                        <!-- Verifiable Results -->
                        <div class="bg-gray-900/60 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-6 shadow-2xl hover:border-emerald-500/30 transition-all duration-300 group">
                            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 flex items-center justify-center mb-4 mx-auto group-hover:glow-emerald transition-all">
                                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold mb-2">Verifiable Results</h3>
                            <p class="text-gray-500 text-sm">Every vote is cryptographically verified using Merkle Trees and Groth16 proofs.</p>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="py-8 text-center">
                <p class="text-gray-600 text-sm">
                    Powered by <span class="text-gray-500 font-medium">zk-SNARKs</span> · Poseidon Hash · Groth16
                </p>
            </footer>
        </div>
    </div>
</template>
