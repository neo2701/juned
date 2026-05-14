# JUNED E-Voting System

A privacy-preserving electronic voting system powered by **zk-SNARKs** (Groth16 zero-knowledge proofs). Voters can prove their eligibility and cast ballots without revealing their identity, while the system guarantees vote integrity and prevents double-voting.

## Key Features

- **Anonymous Voting** — No link between voter identity and ballot (privacy firewall via nullifiers)
- **Zero-Knowledge Proofs** — Client-side Groth16 proof generation using snarkjs + circom
- **Poseidon Merkle Tree** — Voter eligibility proven via cryptographic tree membership
- **Double-Vote Prevention** — Deterministic nullifiers prevent multiple votes per election
- **Proof Verification & Audit** — Admin can re-verify all proofs and audit Merkle Tree integrity
- **Encrypted Ballots** — Candidate selection encrypted before submission

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.3) |
| Frontend | Vue 3 + Inertia.js |
| Styling | Tailwind CSS (dark mode) |
| Database | SQLite (dev) / MySQL (prod) |
| ZKP Circuit | circom 2.0 (BN254/Groth16) |
| ZKP Library | snarkjs |
| Hash Function | Poseidon (circomlibjs) |
| Trusted Setup | Powers of Tau (2^14) |

## Prerequisites

- PHP 8.3+
- Composer
- Node.js 18+ (for snarkjs, circomlibjs, and bridge scripts)
- npm
- circom 2.0 (optional, only for circuit recompilation)

## Installation

### 1. Clone and install dependencies

```bash
git clone <repository-url> juned
cd juned

composer install
npm install
```

### 2. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/juned/database/database.sqlite
```

For SQLite, create the database file:

```bash
touch database/database.sqlite
```

### 3. Run migrations

```bash
php artisan migrate
```

### 4. Compile circuit and generate trusted setup

The zk-SNARK circuit needs a one-time trusted setup:

```bash
bash scripts/compile_circuit.sh
```

This will:
- Compile `circuits/vote.circom` → WASM + R1CS
- Generate Powers of Tau (if not present)
- Run Groth16 setup → proving key + verification key
- Deploy assets to `public/zkp/`

### 5. Build frontend

```bash
npm run build
```

### 6. Start the development server

```bash
php artisan serve
```

Visit `http://localhost:8000`

## Usage

### Admin Workflow

1. **Login** at `/login` with admin credentials
2. **Create an election** at Elections → Create
3. **Add candidates** to the election
4. **Register voters** at Voters → Register (each voter gets a private key — distribute securely)
5. **Generate Merkle Tree** from the election detail page (required before voting starts)
6. **Start the election** (change status to BERJALAN)
7. **Audit votes** from the election Audit page after voting ends

### Voter Workflow

1. **Login** at `/voter/login` with NIK + private key
2. **Select a candidate** and click "Vote"
3. **Enter private key** in the modal (password-masked)
4. The browser will:
   - Fetch the Merkle Tree
   - Compute voter commitment (Poseidon hash)
   - Verify eligibility (commitment in tree)
   - Generate a Groth16 zero-knowledge proof
   - Encrypt the vote
   - Submit proof + encrypted vote to server
5. Server verifies the proof and records the vote

### Audit Workflow

1. Navigate to election → **Audit** page
2. **Verify individual votes** — re-runs zk-SNARK verification
3. **Verify all votes** — batch re-verification
4. **Audit Merkle Tree** — recomputes root from leaves and compares with stored root

## Project Structure

```
juned/
├── app/
│   ├── Http/Controllers/
│   │   ├── SuaraController.php      # Vote submission + verification
│   │   ├── MerkleTreeController.php  # Tree generation + API
│   │   ├── AuditController.php       # Audit & verification UI
│   │   └── PemiluController.php      # Election management
│   ├── Models/                       # Eloquent models
│   └── Services/
│       ├── MerkleTreeService.php     # Poseidon Merkle Tree (PHP + Node.js bridge)
│       ├── NullifierService.php      # Nullifier storage/lookup
│       └── VerificationService.php   # Proof re-verification + tree audit
├── circuits/
│   ├── vote.circom                   # zk-SNARK circuit definition
│   └── vote_js/vote.wasm            # Compiled WASM witness generator
├── scripts/
│   ├── compile_circuit.sh            # Full circuit compilation + setup
│   ├── merkle_tree.js                # Poseidon Merkle Tree builder (Node.js)
│   ├── verify.js                     # Groth16 proof verifier (Node.js)
│   ├── generate_voter.js             # Voter key generation
│   └── poseidon_hash.js              # Poseidon hash utility
├── public/zkp/                       # Static ZKP assets (served to browser)
│   ├── vote.wasm                     # WASM witness generator
│   ├── vote_final.zkey               # Groth16 proving key
│   └── vkey.json                     # Verification key
├── resources/js/Pages/
│   ├── Voter/Dashboard.vue           # Voting booth (client-side proof gen)
│   └── Admin/Pemilu/                 # Election management pages
└── database/migrations/              # Database schema
```

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Voter Browser                              │
│  ┌──────────┐  ┌───────────┐  ┌──────────────────────────┐ │
│  │circomlibjs│  │  snarkjs  │  │   Voter/Dashboard.vue    │ │
│  │ (Poseidon)│  │ (Groth16) │  │  (proof gen + submit)    │ │
│  └──────────┘  └───────────┘  └──────────────────────────┘ │
└─────────────────────────┬───────────────────────────────────┘
                          │ POST /voter/api/vote
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                   Laravel Backend                             │
│  ┌────────────────┐  ┌─────────────────┐  ┌─────────────┐  │
│  │SuaraController │  │MerkleTreeService│  │VerifyService│  │
│  │(validate+store)│  │(build tree)     │  │(re-verify)  │  │
│  └───────┬────────┘  └────────┬────────┘  └──────┬──────┘  │
│          │                    │                   │          │
│          ▼                    ▼                   ▼          │
│  ┌─────────────────────────────────────────────────────────┐│
│  │              Node.js Bridge (scripts/)                   ││
│  │  verify.js (snarkjs)  │  merkle_tree.js (circomlibjs)   ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────┬───────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                      Database                                │
│  pemilu │ kandidat │ pemilih │ suara │ nullifier │ zkp_proof │
│         │          │         │       │           │           │
│  No FK between pemilih ←→ suara (privacy firewall)          │
└─────────────────────────────────────────────────────────────┘
```

## Privacy Design

The system maintains a **privacy firewall** between voter identity and ballots:

- `suara` (ballot) table has **no foreign key** to `pemilih` (voter) table
- `nullifier` table has **no foreign key** to `pemilih` table
- The only shared data is the voter commitment (Poseidon hash of private key), which is a one-way function
- Reconstructing the voter→ballot link requires knowledge of the voter's private key
- The nullifier (`Poseidon(privateKey, electionId)`) prevents double-voting without revealing identity

## Running Tests

```bash
# PHP tests
php artisan test

# Property-based tests (JavaScript)
npm run test:pbt
```

## Deployment Notes

### Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
- [ ] Use MySQL/PostgreSQL instead of SQLite for production
- [ ] Run `php artisan config:cache` and `php artisan route:cache`
- [ ] Run `npm run build` for optimized frontend assets
- [ ] Ensure `public/zkp/` files are served with correct MIME types
- [ ] Set up HTTPS (required for `crypto.getRandomValues()` in browser)
- [ ] Configure proper file permissions for `storage/` and `bootstrap/cache/`
- [ ] Run trusted setup ceremony with multiple contributors for production

### Trusted Setup Security

The proving key (`vote_final.zkey`) is generated from a trusted setup ceremony. For production:

1. Use a multi-party computation (MPC) ceremony with multiple independent contributors
2. Each contributor adds randomness that would need to be compromised for the setup to be broken
3. The development setup uses a single contribution — **not suitable for production**

### Static Asset Serving

The `public/zkp/` directory contains large binary files:
- `vote.wasm` (~2 MB) — Content-Type: `application/wasm`
- `vote_final.zkey` (~3 MB) — Content-Type: `application/octet-stream`
- `vkey.json` (~4 KB) — Content-Type: `application/json`

Configure your web server (Nginx/Apache) to serve these directly without PHP processing.

## License

This project is for educational and research purposes.
