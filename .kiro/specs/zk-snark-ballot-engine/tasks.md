# Implementation Plan: zk-SNARK Ballot Engine

## Overview

This plan implements the zk-SNARK Ballot Engine (Phase 3) for the JUNED E-Voting System. The implementation follows a bottom-up approach: first establishing the Node.js bridge scripts and static assets, then building the server-side services and controllers, and finally wiring the client-side Vue component for proof generation and vote submission.

## Tasks

- [x] 1. Set up project infrastructure and dependencies
  - [x] 1.1 Install npm dependencies and configure test runner
    - Add `snarkjs` to dependencies in `package.json`
    - Add `vitest` and `fast-check` to devDependencies in `package.json`
    - Add `"test:pbt": "vitest --run tests/pbt/"` script to `package.json`
    - Run `npm install`
    - _Requirements: 2.3, 3.6, 10.1_

  - [x] 1.2 Create static asset directory and deploy circuit artifacts
    - Create `public/zkp/` directory
    - Copy `circuits/vote_js/vote.wasm` to `public/zkp/vote.wasm`
    - Generate `vote_final.zkey` from trusted setup and copy to `public/zkp/vote_final.zkey`
    - Export verification key to `public/zkp/vkey.json`
    - Create `scripts/compile_circuit.sh` that automates: compile circom → setup → export vkey → copy assets
    - _Requirements: 2.2, 2.3, 2.7, 8.1, 8.2, 8.3_

  - [x] 1.3 Configure Vite to exclude public/zkp from processing
    - Update `vite.config.js` to exclude `public/zkp/` from Vite asset processing
    - Ensure `.wasm` and `.zkey` files are served as-is without transformation
    - _Requirements: 8.1, 8.2_

- [x] 2. Implement Node.js bridge scripts
  - [x] 2.1 Create `scripts/merkle_tree.js` — Poseidon Merkle Tree builder
    - Read JSON from stdin: `{"leaves": ["commitment1", ...], "depth": 10}`
    - Pad leaves array with `0` values up to 1024 entries
    - Build binary Merkle Tree using Poseidon(left, right) from `circomlibjs`
    - Output JSON to stdout: `{"root": "...", "nodes": [[level0...], [level1...], ...]}`
    - Handle errors: output `{"error": "message"}` and exit with non-zero code
    - _Requirements: 1.1, 1.2, 1.3_

  - [x] 2.2 Create `scripts/verify.js` — Groth16 proof verifier
    - Read JSON from stdin: `{"proof": {...}, "publicSignals": [...], "vkeyPath": "..."}`
    - Validate input has required fields (`proof`, `publicSignals`, `vkeyPath`)
    - Validate `publicSignals` is an array of exactly 4 string elements
    - Load verification key from `vkeyPath`, handle file-not-found
    - Call `snarkjs.groth16.verify(vkey, publicSignals, proof)`
    - Output `{"valid": true/false}` to stdout, exit code 0
    - On error: output `{"error": "message"}` to stdout, exit with non-zero code
    - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

  - [ ]* 2.3 Write property tests for Merkle Tree construction (Property 1)
    - **Property 1: Merkle Tree Construction Invariant**
    - Create `tests/pbt/merkle-tree.test.js`
    - Generate random arrays of BigInt commitments (0–1024 elements)
    - Assert: tree always has exactly 1024 leaves, depth 10, deterministic root
    - **Validates: Requirements 1.1, 1.2**

  - [ ]* 2.4 Write property tests for Merkle Proof round-trip (Property 2)
    - **Property 2: Merkle Proof Round-Trip**
    - Create `tests/pbt/merkle-proof.test.js`
    - Generate random trees + random leaf indices
    - Assert: recomputing root from leaf + path elements + path indices equals stored root
    - **Validates: Requirements 1.5, 3.4**

  - [ ]* 2.5 Write property tests for Proof Verifier I/O contract (Property 11)
    - **Property 11: Proof Verifier I/O Contract**
    - Create `tests/pbt/verifier-io.test.js`
    - Generate random JSON inputs (valid and malformed)
    - Assert: valid inputs → exit 0 + `{valid: bool}`, invalid inputs → non-zero exit + `{error: string}`
    - **Validates: Requirements 10.1, 10.2, 10.3**

  - [ ]* 2.6 Write property tests for Proof Verifier determinism (Property 12)
    - **Property 12: Proof Verifier Determinism**
    - Create `tests/pbt/verifier-determinism.test.js`
    - Use fixed valid inputs, invoke verify.js multiple times
    - Assert: identical results on every invocation
    - **Validates: Requirements 10.5**

- [x] 3. Implement MerkleTreeService (PHP + Node.js bridge)
  - [x] 3.1 Create `app/Services/MerkleTreeService.php`
    - Implement `buildTree(int $pemiluId): MerkleTree`
      - Collect `private_key_hash` values from `pemilih` table
      - Reject if count > 1024 (throw exception)
      - Invoke `scripts/merkle_tree.js` via `Symfony\Component\Process\Process` with leaves JSON on stdin
      - Parse output, store root in `merkle_tree` table with status `GENERATED`
      - Store all leaves in `merkle_leaf` table
    - Implement `getProof(int $pemiluId, string $commitment): array`
      - Find leaf index in stored leaves
      - Compute path elements and path indices from stored tree nodes
      - Return array with `pathElements` (10 items) and `pathIndices` (10 items)
      - Throw exception if commitment not found
    - Implement `getTreeData(int $pemiluId): array`
      - Return all leaves (ordered by position) and root hash
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

  - [x] 3.2 Create `app/Http/Controllers/MerkleTreeController.php`
    - Implement `show(int $pemilu)` — GET `/voter/api/merkle-tree/{pemilu}`
      - Validate election exists (404 if not)
      - Validate election status is `BERJALAN` (403 if not)
      - Validate Merkle Tree is generated (404 if not)
      - Return JSON: `{"leaves": [...], "root": "..."}`
    - Implement `generate(int $pemilu)` — POST `/admin/pemilu/{pemilu}/generate-tree`
      - Require admin auth
      - Call `MerkleTreeService::buildTree()`
      - Return success/error response
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

  - [x] 3.3 Register API routes for Merkle Tree endpoints
    - Add `GET /voter/api/merkle-tree/{pemilu}` route with VoterMiddleware
    - Add `POST /admin/pemilu/{pemilu}/generate-tree` route with auth middleware
    - _Requirements: 7.1, 7.2_

- [x] 4. Implement SuaraController and vote submission flow
  - [x] 4.1 Create `app/Http/Controllers/SuaraController.php`
    - Implement `elections()` — GET `/voter/api/elections`
      - Return elections with status `BERJALAN` and their candidates
    - Implement `store(Request $request)` — POST `/voter/api/vote`
      - Validate request payload: proof (object), publicSignals (array of 4 strings), encrypted_vote (string), nullifier_hash (string), pemilu_id (int)
      - Return 422 with field-specific errors if validation fails
      - Verify election status is `BERJALAN` (403 if not)
      - Invoke `scripts/verify.js` via Process with proof data on stdin (10s timeout)
      - Parse verifier output; return 422 if proof invalid
      - Confirm nullifier from publicSignals[0] matches submitted nullifier_hash (422 if mismatch)
      - Check nullifier uniqueness in `nullifier` table (409 if duplicate)
      - Atomic DB transaction: insert `suara` (status MASUK), `zkp_proof`, `nullifier`
      - Return 500 and rollback on transaction failure
      - Return success response on completion
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8, 5.1, 5.2, 5.4, 6.3_

  - [x] 4.2 Refactor `app/Services/NullifierService.php`
    - Remove BLAKE2b hash generation logic
    - Keep only `hasVoted(string $nullifierHash, int $pemiluId): bool` for lookup
    - Keep `store(string $nullifierHash, int $pemiluId): Nullifier` for persistence
    - Nullifier hash is now provided by the client (from circuit output), not computed server-side
    - _Requirements: 5.1, 5.4_

  - [x] 4.3 Register API routes for vote submission
    - Add `GET /voter/api/elections` route with VoterMiddleware
    - Add `POST /voter/api/vote` route with VoterMiddleware
    - _Requirements: 4.1, 3.1_

  - [ ]* 4.4 Write property tests for vote payload validation (Property 7)
    - **Property 7: Vote Submission Payload Validation**
    - Create `tests/Feature/SuaraControllerValidationTest.php`
    - Generate random request payloads (valid and invalid combinations)
    - Assert: valid payloads pass validation, invalid payloads return 422 with correct error fields
    - **Validates: Requirements 4.1, 4.2**

  - [ ]* 4.5 Write property tests for double-vote prevention (Property 8)
    - **Property 8: Double-Vote Prevention via Nullifier Uniqueness**
    - Create `tests/Feature/DoubleVotePreventionTest.php`
    - Generate random valid submissions with duplicate nullifiers
    - Assert: first accepted, second rejected with 409, no extra records persisted
    - **Validates: Requirements 4.6, 5.1, 5.2**

- [x] 5. Checkpoint - Ensure server-side components work
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implement Voter Dashboard (Vue 3 client-side proof generation)
  - [x] 6.1 Create `resources/js/Pages/Voter/Dashboard.vue` — election display and voting UI
    - Fetch active elections (status `BERJALAN`) with candidates on mount
    - Display election list with candidate selection
    - Implement private key input (password-masked) with modal/dialog
    - Validate private key: non-empty numeric string, valid BN254 field element (< field order)
    - Disable vote controls when election status is not `BERJALAN`
    - Display loading states during proof generation
    - Display error messages without leaking private key
    - Clear private key from memory and input after submission or failure
    - _Requirements: 3.1, 3.2, 3.3, 3.8, 3.9, 6.5, 9.3_

  - [x] 6.2 Implement client-side Merkle Proof computation in Dashboard
    - Fetch Merkle Tree data from `/voter/api/merkle-tree/{pemilu}`
    - Compute voter commitment: `Poseidon(privateKey)` using circomlibjs
    - Search for commitment in leaves array
    - If not found: display "not registered" error, block proof generation
    - If found: compute Merkle Proof (10 path elements + 10 path indices) locally
    - Handle fetch failure: display retry-able error, preserve private key for retry
    - _Requirements: 3.4, 3.5, 9.1, 9.2, 9.4_

  - [x] 6.3 Implement client-side Groth16 proof generation and vote submission
    - Load `vote.wasm` and `vote_final.zkey` from `/zkp/` public assets
    - Build circuit inputs: `{privateKey, pathElements, pathIndices, root, pemiluId, kandidatId}`
    - Call `snarkjs.groth16.fullProve(inputs, wasmPath, zkeyPath)`
    - Compute nullifier hash locally: `Poseidon(privateKey, pemiluId)`
    - Encrypt vote: `Poseidon(kandidatId, randomSecret)` — generate random secret
    - Submit to `/voter/api/vote`: `{proof, publicSignals, encrypted_vote, nullifier_hash, pemilu_id}`
    - Handle success: display confirmation, clear sensitive data
    - Handle errors: display appropriate messages per error type (409, 422, 500, network)
    - Block submission if encryption fails
    - _Requirements: 3.6, 3.7, 3.8, 3.9, 6.1, 6.2, 6.5_

  - [ ]* 6.4 Write property tests for nullifier determinism (Property 3)
    - **Property 3: Nullifier Determinism and Format Consistency**
    - Create `tests/pbt/nullifier.test.js`
    - Generate random (privateKey, pemiluId, kandidatId) triples
    - Assert: Poseidon(privateKey, pemiluId) produces same decimal string regardless of kandidatId
    - Assert: client-side circomlibjs matches Node.js bridge output
    - **Validates: Requirements 2.5, 3.7, 5.3, 5.4**

  - [ ]* 6.5 Write property tests for private key validation (Property 6)
    - **Property 6: Private Key Field Element Validation**
    - Create `tests/pbt/key-validation.test.js`
    - Generate random strings: numeric, non-numeric, boundary values near BN254 field order
    - Assert: accepts valid field elements, rejects all others
    - **Validates: Requirements 3.3**

  - [ ]* 6.6 Write property tests for vote encryption (Property 9)
    - **Property 9: Vote Encryption Commitment**
    - Create `tests/pbt/encryption.test.js`
    - Generate random (kandidatId, randomSecret) pairs as valid BN254 field elements
    - Assert: Poseidon(kandidatId, randomSecret) is deterministic and collision-resistant
    - **Validates: Requirements 6.1**

  - [ ]* 6.7 Write property tests for voter eligibility verification (Property 10)
    - **Property 10: Voter Eligibility Verification**
    - Create `tests/pbt/eligibility.test.js`
    - Generate random keys, some included in tree, some not
    - Assert: Poseidon(privateKey) found in leaves iff voter was included at build time
    - **Validates: Requirements 9.1, 9.2**

- [x] 7. Checkpoint - Ensure client-side and server-side integration works
  - Ensure all tests pass, ask the user if questions arise.

- [x] 8. Wire end-to-end flow and integration testing
  - [x] 8.1 Create Suara and ZkpProof Eloquent models
    - Create `app/Models/Suara.php` with fillable fields and relationships
    - Create `app/Models/ZkpProof.php` with fillable fields and relationships
    - Create `app/Models/MerkleTree.php` with fillable fields and relationships
    - Create `app/Models/MerkleLeaf.php` with fillable fields and relationships
    - Create `app/Models/Nullifier.php` (update existing if needed) with unique constraint
    - _Requirements: 4.7, 1.4_

  - [x] 8.2 Wire Inertia props for Voter Dashboard
    - Update route definitions to pass election data via Inertia
    - Ensure VoterMiddleware gates access to voter API routes
    - Verify `HandleInertiaRequests` middleware shares necessary data
    - _Requirements: 3.1, 9.3_

  - [ ]* 8.3 Write integration tests for end-to-end vote flow
    - Create `tests/Feature/VoteFlowIntegrationTest.php`
    - Test: generate key → build tree → generate proof → submit → verify stored records
    - Test: Node.js bridge communication via Process
    - Test: database transaction atomicity (forced failure rollback)
    - _Requirements: 4.7, 4.8, 5.1_

  - [ ]* 8.4 Write property tests for proof binding to candidate (Property 5)
    - **Property 5: Proof Binding to Candidate Selection**
    - Create `tests/pbt/proof-binding.test.js`
    - Generate valid proofs, then verify with altered kandidatId
    - Assert: verification returns `valid: false` when kandidatId is changed
    - **Validates: Requirements 2.6**

- [x] 9. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Unit tests validate specific examples and edge cases
- The Node.js bridge pattern (scripts/) is used because PHP lacks native Poseidon/BN254 support
- Static assets in `public/zkp/` are served without auth — security comes from the private witness
- The `NullifierService` is refactored to remove BLAKE2b; nullifiers now come from the circuit's Poseidon output
- `snarkjs` is needed both as a runtime dependency (browser + verify.js) and dev dependency (tests)

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.3"] },
    { "id": 1, "tasks": ["2.1", "2.2", "8.1"] },
    { "id": 2, "tasks": ["2.3", "2.4", "2.5", "2.6", "3.1"] },
    { "id": 3, "tasks": ["3.2", "3.3", "4.2"] },
    { "id": 4, "tasks": ["4.1", "4.3"] },
    { "id": 5, "tasks": ["4.4", "4.5", "6.1"] },
    { "id": 6, "tasks": ["6.2"] },
    { "id": 7, "tasks": ["6.3"] },
    { "id": 8, "tasks": ["6.4", "6.5", "6.6", "6.7", "8.2"] },
    { "id": 9, "tasks": ["8.3", "8.4"] }
  ]
}
```
