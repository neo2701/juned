<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

// Tab state
const activeTab = ref('single');

// Single registration form
const form = useForm({
    nik: '',
    nama_pemilih: '',
});

const submit = () => {
    form.post(route('admin.pemilih.store'));
};

// Bulk import form
const bulkForm = useForm({
    csv_file: null,
});

const fileInput = ref(null);
const fileName = ref('');

function handleFileChange(event) {
    const file = event.target.files[0];
    if (file) {
        bulkForm.csv_file = file;
        fileName.value = file.name;
    }
}

function submitBulk() {
    bulkForm.post(route('admin.pemilih.bulk-import'), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Register Voters" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-juned-800 leading-tight">Register Voters</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Info Banner -->
                <div class="mb-6 rounded-xl bg-blue-50 border border-blue-200 p-4">
                    <div class="flex gap-3">
                        <svg class="h-5 w-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-medium">Self-Registration Flow</p>
                            <p class="mt-1 text-blue-700">Pre-approve voter NIKs here. Voters will then self-register at <code class="bg-blue-100 px-1 rounded">/voter/register</code> where their private key is generated client-side. The server never sees the private key.</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white border border-juned-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="border-b border-juned-200">
                        <nav class="flex">
                            <button
                                @click="activeTab = 'single'"
                                :class="['px-6 py-3 text-sm font-medium border-b-2 transition-colors', activeTab === 'single' ? 'border-juned-700 text-juned-800' : 'border-transparent text-juned-text hover:text-juned-800']"
                            >
                                Single Registration
                            </button>
                            <button
                                @click="activeTab = 'bulk'"
                                :class="['px-6 py-3 text-sm font-medium border-b-2 transition-colors', activeTab === 'bulk' ? 'border-juned-700 text-juned-800' : 'border-transparent text-juned-text hover:text-juned-800']"
                            >
                                Bulk Import (CSV)
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        <!-- Single Registration -->
                        <form v-if="activeTab === 'single'" @submit.prevent="submit" class="space-y-6 max-w-2xl">
                            <div>
                                <InputLabel for="nik" value="Nomor Induk Kependudukan (NIK)" />
                                <TextInput
                                    id="nik"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.nik"
                                    maxlength="16"
                                    required
                                    autofocus
                                    placeholder="16 digit NIK"
                                />
                                <p class="mt-1 text-sm text-juned-text">Must be exactly 16 digits.</p>
                                <InputError class="mt-2" :message="form.errors.nik" />
                            </div>

                            <div>
                                <InputLabel for="nama_pemilih" value="Voter Name (Optional)" />
                                <TextInput
                                    id="nama_pemilih"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.nama_pemilih"
                                    placeholder="Nama pemilih"
                                />
                                <InputError class="mt-2" :message="form.errors.nama_pemilih" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">
                                    Pre-Approve NIK
                                </PrimaryButton>
                                <Link :href="route('admin.pemilih.index')" class="text-sm text-juned-text hover:text-juned-800">Cancel</Link>
                            </div>
                        </form>

                        <!-- Bulk Import -->
                        <div v-else class="max-w-2xl">
                            <div class="mb-6">
                                <h3 class="text-base font-medium text-juned-800">Bulk Import from CSV</h3>
                                <p class="mt-1 text-sm text-juned-text">Upload a CSV file with voter NIKs. Expected format:</p>
                                <div class="mt-3 rounded-lg bg-juned-100 border border-juned-200 p-3 font-mono text-xs text-juned-text">
                                    <p>NIK,Name</p>
                                    <p>1234567890123456,John Doe</p>
                                    <p>9876543210987654,Jane Smith</p>
                                    <p class="text-juned-text/60 mt-1"># Name column is optional</p>
                                </div>
                            </div>

                            <form @submit.prevent="submitBulk" class="space-y-6">
                                <div>
                                    <InputLabel value="CSV File" />
                                    <div
                                        @click="fileInput?.click()"
                                        class="mt-1 flex items-center justify-center rounded-xl border-2 border-dashed border-juned-200 p-8 cursor-pointer hover:border-juned-400 hover:bg-juned-100/50 transition-all"
                                    >
                                        <div class="text-center">
                                            <svg class="mx-auto h-10 w-10 text-juned-text/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                            </svg>
                                            <p v-if="fileName" class="mt-2 text-sm font-medium text-juned-800">{{ fileName }}</p>
                                            <p v-else class="mt-2 text-sm text-juned-text">Click to select CSV file</p>
                                            <p class="mt-1 text-xs text-juned-text/70">CSV or TXT, max 2MB</p>
                                        </div>
                                    </div>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept=".csv,.txt"
                                        class="hidden"
                                        @change="handleFileChange"
                                    />
                                    <InputError class="mt-2" :message="bulkForm.errors.csv_file" />
                                </div>

                                <div class="flex items-center gap-4">
                                    <PrimaryButton :disabled="bulkForm.processing || !bulkForm.csv_file">
                                        <svg v-if="bulkForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ bulkForm.processing ? 'Importing...' : 'Import Voters' }}
                                    </PrimaryButton>
                                    <Link :href="route('admin.pemilih.index')" class="text-sm text-juned-text hover:text-juned-800">Cancel</Link>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
