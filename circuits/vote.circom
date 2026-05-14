pragma circom 2.0.0;

include "../node_modules/circomlib/circuits/poseidon.circom";

template HashLeftRight() {
    signal input left;
    signal input right;
    signal output hash;

    component hasher = Poseidon(2);
    hasher.inputs[0] <== left;
    hasher.inputs[1] <== right;
    hash <== hasher.out;
}

template DualMux() {
    signal input in[2];
    signal input s;
    signal output out[2];

    s * (1 - s) === 0;
    out[0] <== (in[1] - in[0])*s + in[0];
    out[1] <== (in[0] - in[1])*s + in[1];
}

template MerkleTreeInclusionProof(levels) {
    signal input leaf;
    signal input pathElements[levels];
    signal input pathIndices[levels];
    signal output root;

    component selectors[levels];
    component hashers[levels];

    for (var i = 0; i < levels; i++) {
        selectors[i] = DualMux();
        selectors[i].in[0] <== i == 0 ? leaf : hashers[i - 1].hash;
        selectors[i].in[1] <== pathElements[i];
        selectors[i].s <== pathIndices[i];

        hashers[i] = HashLeftRight();
        hashers[i].left <== selectors[i].out[0];
        hashers[i].right <== selectors[i].out[1];
    }

    root <== hashers[levels - 1].hash;
}

template Vote(levels) {
    // Private inputs
    signal input privateKey;
    signal input pathElements[levels];
    signal input pathIndices[levels];

    // Public inputs
    signal input root;
    signal input pemiluId;
    signal input kandidatId;

    // Output
    signal output nullifierHash;

    // 1. Calculate voter commitment: Poseidon(privateKey)
    component commitmentHasher = Poseidon(1);
    commitmentHasher.inputs[0] <== privateKey;
    
    // 2. Verify commitment is in the Merkle Tree
    component tree = MerkleTreeInclusionProof(levels);
    tree.leaf <== commitmentHasher.out;
    for (var i = 0; i < levels; i++) {
        tree.pathElements[i] <== pathElements[i];
        tree.pathIndices[i] <== pathIndices[i];
    }
    tree.root === root;

    // 3. Generate Nullifier: Poseidon(privateKey, pemiluId)
    component nullifierHasher = Poseidon(2);
    nullifierHasher.inputs[0] <== privateKey;
    nullifierHasher.inputs[1] <== pemiluId;
    nullifierHash <== nullifierHasher.out;

    // 4. Dummy constraint to bind kandidatId to the proof so it can't be modified by interceptors
    signal dummy;
    dummy <== kandidatId * kandidatId;
}

component main {public [root, pemiluId, kandidatId]} = Vote(10);
