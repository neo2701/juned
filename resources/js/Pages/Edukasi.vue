<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import PublicHeader from '@/Components/PublicHeader.vue';

// Flowchart animation state
const activeNode = ref(-1);
const animationComplete = ref(false);
let animationTimer = null;

function startFlowAnimation() {
    activeNode.value = -1;
    animationComplete.value = false;
    let step = 0;
    const totalSteps = 6;

    animationTimer = setInterval(() => {
        activeNode.value = step;
        step++;
        if (step >= totalSteps) {
            clearInterval(animationTimer);
            animationComplete.value = true;
        }
    }, 800);
}

function resetAnimation() {
    clearInterval(animationTimer);
    startFlowAnimation();
}

// Scroll-triggered sections
const visibleSections = ref(new Set());

onMounted(() => {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    visibleSections.value.add(entry.target.dataset.section);
                    // Start flow animation when flowchart section is visible
                    if (entry.target.dataset.section === 'flow') {
                        startFlowAnimation();
                    }
                }
            });
        },
        { threshold: 0.2 }
    );
    document.querySelectorAll('[data-section]').forEach((el) => observer.observe(el));
});

onUnmounted(() => {
    clearInterval(animationTimer);
});

// Detailed explanation toggle
const expandedNode = ref(null);
function toggleExplain(index) {
    expandedNode.value = expandedNode.value === index ? null : index;
}

const nodes = [
    {
        id: 'register',
        label: 'Registrasi',
        short: 'Admin approve NIK',
        detail: 'Admin meng-approve NIK pemilih. Pemilih kemudian membuka halaman registrasi dan membuat private key langsung di browser mereka. Server tidak pernah melihat private key.',
        color: 'emerald',
    },
    {
        id: 'commitment',
        label: 'Commitment',
        short: 'Poseidon(privateKey)',
        detail: 'Browser menghitung Poseidon Hash dari private key. Hasil hash ini disebut "commitment" — identitas kriptografis yang dikirim ke server tanpa mengungkap private key.',
        color: 'blue',
    },
    {
        id: 'merkle',
        label: 'Merkle Tree',
        short: 'Pohon hash pemilih',
        detail: 'Semua commitment pemilih disusun dalam Merkle Tree (kedalaman 10, maks 1024 pemilih). Root hash menjadi referensi keanggotaan yang tidak bisa dipalsukan.',
        color: 'violet',
    },
    {
        id: 'zkp',
        label: 'ZK Proof',
        short: 'Groth16 proof generation',
        detail: 'Saat voting, browser membuat bukti Zero-Knowledge (Groth16) yang membuktikan: "Saya punya private key yang commitment-nya ada di Merkle Tree" — tanpa mengungkap key atau posisi.',
        color: 'amber',
    },
    {
        id: 'nullifier',
        label: 'Nullifier',
        short: 'Poseidon(key, electionId)',
        detail: 'Setiap suara menghasilkan nullifier unik = Poseidon(privateKey, pemiluId). Server menyimpan nullifier untuk mencegah double-voting, tapi tidak bisa melacak siapa pemiliknya.',
        color: 'rose',
    },
    {
        id: 'verify',
        label: 'Verifikasi',
        short: 'Proof verified on-chain',
        detail: 'Server memverifikasi ZKP proof secara kriptografis. Jika valid dan nullifier belum digunakan, suara dicatat. Siapa pun dapat mengaudit hasilnya secara publik.',
        color: 'juned',
    },
];
</script>

<template>
    <Head title="Edukasi — Cara Kerja Sistem" />

    <div class="min-h-screen bg-white font-sans">
        <!-- Header -->
        <PublicHeader active="edukasi" />

        <!-- Hero -->
        <section class="relative bg-juned-800 py-20 lg:py-28 overflow-hidden">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 30% 40%, rgba(108,248,187,0.3) 0%, transparent 50%), radial-gradient(circle at 70% 60%, rgba(108,248,187,0.2) 0%, transparent 50%);"></div>
            <div class="relative max-w-[900px] mx-auto px-6 text-center">
                <div class="inline-flex items-center gap-2 bg-juned-400/20 border border-juned-400/30 rounded-full px-4 py-1.5 mb-6">
                    <svg class="w-4 h-4 text-juned-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <span class="text-xs font-semibold text-juned-400">Panduan Visual</span>
                </div>
                <h1 class="text-4xl lg:text-5xl font-bold text-white leading-tight mb-6">Bagaimana JUNED<br>Melindungi Suara Anda?</h1>
                <p class="text-lg text-white/70 max-w-2xl mx-auto">Ikuti alur diagram interaktif di bawah untuk memahami setiap tahap dari registrasi hingga verifikasi.</p>
            </div>
        </section>

        <!-- Animated Flowchart Section -->
        <section class="py-16 lg:py-24 bg-juned-100 overflow-hidden" data-section="flow">
            <div class="max-w-[1100px] mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-juned-800 mb-3">Alur Voting End-to-End</h2>
                    <p class="text-juned-text mb-4">Diagram animasi menunjukkan perjalanan suara Anda</p>
                    <button @click="resetAnimation" class="inline-flex items-center gap-2 text-sm font-medium text-juned-700 hover:text-juned-800 border border-juned-200 bg-white rounded-lg px-3 py-1.5 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Putar Ulang
                    </button>
                </div>

                <!-- Flowchart Diagram -->
                <div class="relative">
                    <!-- Desktop: Horizontal flow -->
                    <div class="hidden lg:block">
                        <svg class="w-full h-[340px]" viewBox="0 0 1100 340" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Connection lines (animated) -->
                            <path v-for="i in 5" :key="'line-'+i"
                                :d="getLinePath(i-1)"
                                :stroke="activeNode >= i ? '#064e3b' : '#bfc9c3'"
                                stroke-width="2"
                                stroke-dasharray="6 4"
                                :class="activeNode >= i ? 'animate-dash' : ''"
                                fill="none"
                            />

                            <!-- Arrow heads -->
                            <polygon v-for="i in 5" :key="'arrow-'+i"
                                :points="getArrowPoints(i)"
                                :fill="activeNode >= i ? '#064e3b' : '#bfc9c3'"
                                class="transition-colors duration-500"
                            />

                            <!-- Nodes -->
                            <g v-for="(node, i) in nodes" :key="node.id">
                                <!-- Node circle -->
                                <circle
                                    :cx="getNodeX(i)" cy="100" r="36"
                                    :fill="activeNode >= i ? '#064e3b' : '#ffffff'"
                                    :stroke="activeNode >= i ? '#064e3b' : '#bfc9c3'"
                                    stroke-width="2"
                                    class="transition-all duration-500 cursor-pointer"
                                    :class="activeNode === i ? 'animate-pulse-ring' : ''"
                                    @click="toggleExplain(i)"
                                />

                                <!-- Pulse ring when active -->
                                <circle v-if="activeNode === i"
                                    :cx="getNodeX(i)" cy="100" r="44"
                                    fill="none"
                                    stroke="#6cf8bb"
                                    stroke-width="2"
                                    opacity="0.5"
                                    class="animate-ping-slow"
                                />

                                <!-- Step number -->
                                <text :x="getNodeX(i)" y="106" text-anchor="middle"
                                    :fill="activeNode >= i ? '#6cf8bb' : '#404944'"
                                    font-size="18" font-weight="bold"
                                    class="transition-colors duration-500 pointer-events-none"
                                >{{ i + 1 }}</text>

                                <!-- Label below -->
                                <text :x="getNodeX(i)" y="160" text-anchor="middle"
                                    fill="#002113" font-size="13" font-weight="600"
                                    class="pointer-events-none"
                                >{{ node.label }}</text>

                                <!-- Sublabel -->
                                <text :x="getNodeX(i)" y="178" text-anchor="middle"
                                    fill="#404944" font-size="10"
                                    class="pointer-events-none"
                                >{{ node.short }}</text>

                                <!-- Data flow particle -->
                                <circle v-if="activeNode === i"
                                    :cx="getNodeX(i)" cy="100" r="4"
                                    fill="#6cf8bb"
                                    class="animate-particle"
                                />
                            </g>

                            <!-- Legend -->
                            <g transform="translate(20, 240)">
                                <rect x="0" y="0" width="200" height="70" rx="8" fill="white" stroke="#bfc9c3" stroke-width="1" />
                                <circle cx="20" cy="20" r="6" fill="#064e3b" />
                                <text x="34" y="24" fill="#404944" font-size="10">= Tahap selesai</text>
                                <circle cx="20" cy="44" r="6" fill="white" stroke="#bfc9c3" stroke-width="2" />
                                <text x="34" y="48" fill="#404944" font-size="10">= Menunggu</text>
                                <line x1="110" y1="20" x2="140" y2="20" stroke="#064e3b" stroke-width="2" stroke-dasharray="4 3" />
                                <text x="148" y="24" fill="#404944" font-size="10">= Alur data</text>
                            </g>
                        </svg>
                    </div>

                    <!-- Mobile: Vertical flow -->
                    <div class="lg:hidden">
                        <div class="relative pl-10">
                            <!-- Vertical line -->
                            <div class="absolute left-[18px] top-0 bottom-0 w-0.5 bg-juned-200"></div>
                            <div class="absolute left-[18px] top-0 w-0.5 bg-juned-700 transition-all duration-1000" :style="{ height: (activeNode + 1) / nodes.length * 100 + '%' }"></div>

                            <div v-for="(node, i) in nodes" :key="node.id" class="relative pb-10 last:pb-0">
                                <!-- Node dot -->
                                <div
                                    :class="['absolute left-0 w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-all duration-500 cursor-pointer',
                                        activeNode >= i ? 'bg-juned-700 border-juned-700 text-juned-400' : 'bg-white border-juned-200 text-juned-text']"
                                    @click="toggleExplain(i)"
                                >
                                    {{ i + 1 }}
                                </div>

                                <!-- Content -->
                                <div class="ml-6">
                                    <h4 class="font-bold text-juned-800">{{ node.label }}</h4>
                                    <p class="text-xs text-juned-text font-mono mt-0.5">{{ node.short }}</p>
                                    <transition name="expand">
                                        <p v-if="expandedNode === i" class="mt-2 text-sm text-juned-text bg-white rounded-lg p-3 border border-juned-200">
                                            {{ node.detail }}
                                        </p>
                                    </transition>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expanded explanation panel (desktop) -->
                    <transition name="slide-down">
                        <div v-if="expandedNode !== null" class="hidden lg:block mt-6 bg-white rounded-xl border border-juned-200 p-6 shadow-sm">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-lg bg-juned-700 flex items-center justify-center text-juned-400 font-bold flex-shrink-0">
                                    {{ expandedNode + 1 }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-juned-800 text-lg">{{ nodes[expandedNode].label }}</h4>
                                    <p class="mt-1 text-juned-text leading-relaxed">{{ nodes[expandedNode].detail }}</p>
                                </div>
                                <button @click="expandedNode = null" class="ml-auto text-juned-text hover:text-juned-800 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        </div>
                    </transition>

                    <p class="text-center text-xs text-juned-text mt-6">Klik node untuk melihat penjelasan detail</p>
                </div>
            </div>
        </section>

        <!-- Data Flow Diagram -->
        <section class="py-16 lg:py-24 bg-white" data-section="dataflow">
            <div class="max-w-[900px] mx-auto px-6">
                <div class="text-center mb-12" :class="visibleSections.has('dataflow') ? 'animate-fade-in' : 'opacity-0'">
                    <h2 class="text-3xl font-bold text-juned-800 mb-3">Apa yang Dilihat Server?</h2>
                    <p class="text-juned-text">Perbandingan data yang ada di browser vs yang dikirim ke server</p>
                </div>

                <div class="grid lg:grid-cols-2 gap-6" :class="visibleSections.has('dataflow') ? 'animate-fade-in' : 'opacity-0'">
                    <!-- Browser side -->
                    <div class="rounded-2xl border-2 border-emerald-200 bg-emerald-50/50 p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-emerald-800">Browser Anda (Privat)</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 bg-white rounded-lg p-3 border border-emerald-200">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <span class="text-sm font-mono text-juned-800">Private Key</span>
                                <span class="ml-auto text-xs text-emerald-600 font-medium">🔒 Tidak pernah keluar</span>
                            </div>
                            <div class="flex items-center gap-3 bg-white rounded-lg p-3 border border-emerald-200">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <span class="text-sm font-mono text-juned-800">Merkle Proof</span>
                                <span class="ml-auto text-xs text-emerald-600 font-medium">🔒 Dihitung lokal</span>
                            </div>
                            <div class="flex items-center gap-3 bg-white rounded-lg p-3 border border-emerald-200">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <span class="text-sm font-mono text-juned-800">Pilihan Kandidat</span>
                                <span class="ml-auto text-xs text-emerald-600 font-medium">🔒 Terenkripsi</span>
                            </div>
                            <div class="flex items-center gap-3 bg-white rounded-lg p-3 border border-emerald-200">
                                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                <span class="text-sm font-mono text-juned-800">ZKP Circuit</span>
                                <span class="ml-auto text-xs text-emerald-600 font-medium">🔒 Lokal</span>
                            </div>
                        </div>
                    </div>

                    <!-- Server side -->
                    <div class="rounded-2xl border-2 border-blue-200 bg-blue-50/50 p-6">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-blue-800">Server (Publik)</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 bg-white rounded-lg p-3 border border-blue-200">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-sm font-mono text-juned-800">ZKP Proof</span>
                                <span class="ml-auto text-xs text-blue-600 font-medium">Diverifikasi</span>
                            </div>
                            <div class="flex items-center gap-3 bg-white rounded-lg p-3 border border-blue-200">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-sm font-mono text-juned-800">Nullifier Hash</span>
                                <span class="ml-auto text-xs text-blue-600 font-medium">Anti double-vote</span>
                            </div>
                            <div class="flex items-center gap-3 bg-white rounded-lg p-3 border border-blue-200">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-sm font-mono text-juned-800">Encrypted Vote</span>
                                <span class="ml-auto text-xs text-blue-600 font-medium">Tidak bisa dibaca</span>
                            </div>
                            <div class="flex items-center gap-3 bg-white rounded-lg p-3 border border-blue-200">
                                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                                <span class="text-sm font-mono text-juned-800">Merkle Root</span>
                                <span class="ml-auto text-xs text-blue-600 font-medium">Referensi publik</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Arrow between -->
                <div class="hidden lg:flex justify-center my-6">
                    <div class="flex items-center gap-3 bg-juned-100 rounded-full px-5 py-2 border border-juned-200">
                        <svg class="w-4 h-4 text-juned-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        <span class="text-xs font-bold text-juned-700">Server TIDAK BISA mengetahui siapa yang memilih siapa</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Security Guarantees -->
        <section class="py-16 lg:py-20 bg-juned-800" data-section="guarantees">
            <div class="max-w-[900px] mx-auto px-6">
                <div class="text-center mb-12" :class="visibleSections.has('guarantees') ? 'animate-fade-in' : 'opacity-0'">
                    <h2 class="text-3xl font-bold text-white mb-3">Jaminan Kriptografis</h2>
                    <p class="text-white/60">Properti keamanan yang dijamin secara matematis</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-4" :class="visibleSections.has('guarantees') ? 'animate-fade-in' : 'opacity-0'">
                    <div class="flex items-start gap-3 bg-white/5 border border-white/10 rounded-xl p-5">
                        <div class="w-8 h-8 rounded-full bg-juned-400/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-juned-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">Privasi Pemilih</h4>
                            <p class="text-xs text-white/60 mt-1">Tidak ada pihak yang dapat menghubungkan suara dengan identitas.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 bg-white/5 border border-white/10 rounded-xl p-5">
                        <div class="w-8 h-8 rounded-full bg-juned-400/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-juned-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">Anti Manipulasi</h4>
                            <p class="text-xs text-white/60 mt-1">Suara diverifikasi kriptografis — tidak bisa diubah atau dipalsukan.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 bg-white/5 border border-white/10 rounded-xl p-5">
                        <div class="w-8 h-8 rounded-full bg-juned-400/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-juned-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">Satu Orang Satu Suara</h4>
                            <p class="text-xs text-white/60 mt-1">Nullifier menjamin setiap pemilih hanya bisa memilih sekali.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 bg-white/5 border border-white/10 rounded-xl p-5">
                        <div class="w-8 h-8 rounded-full bg-juned-400/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-juned-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-white text-sm">Verifiable & Auditable</h4>
                            <p class="text-xs text-white/60 mt-1">Siapa pun dapat memverifikasi integritas pemilu secara publik.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="py-16 bg-white border-t border-juned-100">
            <div class="max-w-[600px] mx-auto px-6 text-center">
                <h2 class="text-2xl font-bold text-juned-800 mb-4">Siap Berpartisipasi?</h2>
                <p class="text-juned-text mb-8">Daftarkan diri Anda dan rasakan pengalaman voting yang aman dan transparan.</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <Link :href="route('voter.register')" class="inline-flex items-center bg-juned-800 hover:bg-juned-700 text-white font-bold rounded-xl px-6 py-3 transition-all shadow-lg shadow-juned-800/20">Daftar Sekarang</Link>
                    <Link :href="route('public.audit.index')" class="inline-flex items-center border border-juned-200 hover:bg-juned-100 text-juned-800 font-bold rounded-xl px-6 py-3 transition-all">Lihat Audit Publik</Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-juned-100 border-t border-juned-200 py-8">
            <div class="max-w-[1200px] mx-auto px-6 text-center">
                <p class="text-sm text-juned-text">JUNED E-Voting — Platform Demokrasi Digital yang Aman dan Transparan</p>
            </div>
        </footer>
    </div>
</template>

<script>
export default {
    methods: {
        getNodeX(i) {
            return 90 + i * 185;
        },
        getLinePath(i) {
            const x1 = 90 + i * 185 + 36;
            const x2 = 90 + (i + 1) * 185 - 36;
            return `M ${x1} 100 L ${x2} 100`;
        },
        getArrowPoints(i) {
            const x = 90 + i * 185 - 36;
            return `${x-6},95 ${x},100 ${x-6},105`;
        },
    },
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.8s ease-out forwards;
}
.animate-dash {
    animation: dash 1.5s linear infinite;
}
.animate-ping-slow {
    animation: pingSlow 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
.animate-particle {
    animation: particle 1s ease-out infinite;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes dash {
    to { stroke-dashoffset: -20; }
}
@keyframes pingSlow {
    0% { transform-origin: center; opacity: 0.6; r: 44; }
    100% { opacity: 0; r: 56; }
}
@keyframes particle {
    0% { opacity: 1; r: 4; }
    100% { opacity: 0; r: 8; }
}

.slide-down-enter-active { transition: all 0.3s ease-out; }
.slide-down-leave-active { transition: all 0.2s ease-in; }
.slide-down-enter-from, .slide-down-leave-to { opacity: 0; transform: translateY(-10px); }

.expand-enter-active { transition: all 0.3s ease-out; }
.expand-leave-active { transition: all 0.2s ease-in; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
</style>
