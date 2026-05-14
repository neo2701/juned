# Phase 3: The Ballot Engine (zk-SNARK Integration)

- [ ] Install `circom` and `snarkjs` toolchain
- [ ] Initialize `circomlib` in the project
- [ ] Refactor `PemilihController` to use `sha256` for `voter_commitment` instead of Argon2 (since Argon2 cannot be verified in a SNARK circuit efficiently).
- [ ] Create `MerkleTreeService` in PHP to build a Merkle Tree of eligible voters.
- [ ] Write `circuits/vote.circom` (Proves: Knowledge of private key -> matches commitment -> commitment is in Merkle Tree -> generates Nullifier).
- [ ] Compile `vote.circom`, run setup (trusted setup), generate `zkey` and `vkey.json`.
- [ ] Write `snarkjs` verification script (`verify.js`) to be called by Laravel.
- [ ] Update `Voter/Dashboard.vue` to:
  - Download eligible voters & build Merkle Proof.
  - Generate Groth16 proof using `snarkjs` in the browser.
  - Submit `proof`, `nullifier`, and encrypted `kandidat_id`.
- [ ] Update `SuaraController` to verify proof, check nullifier, and save encrypted vote.
