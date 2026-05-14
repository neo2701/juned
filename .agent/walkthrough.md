# Phase 1: Foundation - Walkthrough

I have successfully completed Phase 1 of the JUNED E-Voting System implementation roadmap! Here is a summary of what was accomplished.

## Completed Work

### 1. Project Initialization
- Initialized a brand new Laravel project.
- Installed **Laravel Breeze** with Vue and Inertia.js to serve as the application's starting point and provide a modern Single Page Application (SPA) experience.
- Installed all required Node dependencies (`npm install`) and built the Vite assets.

### 2. Database Schema (3NF)
- Configured **SQLite** for local development as requested.
- Created and successfully ran migrations for all 10 specialized tables according to the 3NF specification:
  - `pemilu`, `kandidat`, `pemilih`, `auditor`, `nullifier`, `suara`, `zkp_proof`, `merkle_tree`, `merkle_leaf`, and `audit_verifikasi`.
- Set up strict foreign key constraints and necessary indexes (e.g., unique constraints on `nik` and the `[pemilu_id, nullifier_hash]` composite).

### 3. KPU Admin Panel (Basic CRUD)
- Generated Eloquent Models and Resource Controllers for `Pemilu` and `Kandidat`.
- Established the `HasMany` / `BelongsTo` relationships between them.
- Registered the necessary routes under the `auth` middleware group with an `/admin/` prefix.
- Created **6 Aesthetic Vue Components** styled with Tailwind CSS, utilizing the Breeze `AuthenticatedLayout` for a seamless look and feel:
  - `Admin/Pemilu/Index.vue`, `Create.vue`, `Edit.vue`
  - `Admin/Kandidat/Index.vue`, `Create.vue`, `Edit.vue`

## Next Steps

Phase 1 is now complete.

---

# Phase 2: Identity & Security - Walkthrough

I have successfully completed Phase 2! Here is what was accomplished:

## Completed Work

### 1. KPU Admin: Voter Management
- Created the **Pemilih** Eloquent Model and `PemilihController`.
- Built the Voter Management Vue interface (`Admin/Pemilih/Index.vue` and `Create.vue`).
- **Cryptographic Hashing:** When an Admin registers a new voter (using their NIK), the system securely generates a random 16-character string as the `private_key`. It is then hashed using `sodium_crypto_pwhash_str()` (Argon2) before being stored in the database. The plain-text key is flashed to the screen **exactly once** for the Admin to distribute.

### 2. Voter Authentication Portal
- Created a separate **E-Voting Portal** at `/voter/login` (`Voter/Login.vue`).
- Implemented `VoterAuthController` to handle authentication.
- **Verification:** When a voter logs in, the system retrieves their hashed key by NIK and verifies it against the inputted `private_key` using `sodium_crypto_pwhash_str_verify()`. If valid, a secure session is initiated.
- Created a placeholder **Voter Dashboard** (`Voter/Dashboard.vue`) to serve as the landing page for the upcoming Phase 3.

### 3. Nullifier Cryptography
- Implemented the `NullifierService` class.
- Uses `sodium_crypto_generichash()` (BLAKE2b) to deterministically hash `privateKey + pemiluId`.
- This ensures that if a voter attempts to vote twice in the same election, the exact same hash will be generated, which the database will reject (due to the unique index), all without the system ever storing a direct link between the Voter and their Ballot.

## Next Steps

We are ready for **Phase 3: The Ballot Engine**.
In this phase, we will:
1. Build the Vue frontend for the actual voting interface on the Voter Dashboard.
2. Implement basic **Zero-Knowledge Proofs (ZKP)** (or mock it as defined) and encrypt the vote choice.
3. Build the Laravel backend to receive the encrypted ballot and submit it to the `suara` table, while simultaneously marking the `nullifier`.
4. Implement the `MerkleTreeService` to generate Merkle Leaves for each verified vote.
