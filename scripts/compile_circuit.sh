#!/bin/bash
set -e

# =============================================================================
# compile_circuit.sh
# Automates the full circom compilation, trusted setup, and asset deployment
# for the JUNED E-Voting zk-SNARK Ballot Engine.
#
# Usage: bash scripts/compile_circuit.sh
# Run from the project root directory.
# =============================================================================

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CIRCUITS_DIR="$PROJECT_ROOT/circuits"
PUBLIC_ZKP_DIR="$PROJECT_ROOT/public/zkp"
PTAU_FILE="$CIRCUITS_DIR/pot14_final.ptau"

echo "=== zk-SNARK Circuit Compilation & Trusted Setup ==="
echo ""

# Step 1: Compile the circom circuit
echo "[1/6] Compiling vote.circom..."
if command -v circom &> /dev/null; then
    circom "$CIRCUITS_DIR/vote.circom" --r1cs --wasm --sym -o "$CIRCUITS_DIR/"
elif [ -f "$HOME/.cargo/bin/circom" ]; then
    "$HOME/.cargo/bin/circom" "$CIRCUITS_DIR/vote.circom" --r1cs --wasm --sym -o "$CIRCUITS_DIR/"
else
    echo "ERROR: circom not found. Install from https://docs.circom.io/getting-started/installation/"
    exit 1
fi
echo "  -> Generated: vote.r1cs, vote_js/vote.wasm, vote.sym"

# Step 2: Ensure Powers of Tau file exists
echo "[2/6] Checking Powers of Tau ceremony file..."
if [ ! -s "$PTAU_FILE" ]; then
    echo "  -> pot14_final.ptau not found or empty. Generating..."
    npx snarkjs powersoftau new bn128 14 "$CIRCUITS_DIR/pot14_0000.ptau" -v
    npx snarkjs powersoftau contribute "$CIRCUITS_DIR/pot14_0000.ptau" "$CIRCUITS_DIR/pot14_0001.ptau" \
        --name="First contribution" -v -e="random entropy for development setup"
    npx snarkjs powersoftau prepare phase2 "$CIRCUITS_DIR/pot14_0001.ptau" "$PTAU_FILE" -v
    # Clean up intermediate files
    rm -f "$CIRCUITS_DIR/pot14_0000.ptau" "$CIRCUITS_DIR/pot14_0001.ptau"
    echo "  -> Generated: pot14_final.ptau"
else
    echo "  -> pot14_final.ptau exists ($(du -h "$PTAU_FILE" | cut -f1))"
fi

# Step 3: Groth16 trusted setup (Phase 1 → initial zkey)
echo "[3/6] Running Groth16 setup..."
npx snarkjs groth16 setup "$CIRCUITS_DIR/vote.r1cs" "$PTAU_FILE" "$CIRCUITS_DIR/vote_0000.zkey"
echo "  -> Generated: vote_0000.zkey"

# Step 4: Phase 2 contribution → final zkey
echo "[4/6] Contributing to phase 2 ceremony..."
npx snarkjs zkey contribute "$CIRCUITS_DIR/vote_0000.zkey" "$CIRCUITS_DIR/vote_final.zkey" \
    --name="Development contribution" -v -e="random entropy for zkey contribution"
# Clean up intermediate zkey
rm -f "$CIRCUITS_DIR/vote_0000.zkey"
echo "  -> Generated: vote_final.zkey"

# Step 5: Export verification key
echo "[5/6] Exporting verification key..."
npx snarkjs zkey export verificationkey "$CIRCUITS_DIR/vote_final.zkey" "$CIRCUITS_DIR/vkey.json"
echo "  -> Generated: vkey.json"

# Step 6: Deploy assets to public/zkp/
echo "[6/6] Deploying assets to public/zkp/..."
mkdir -p "$PUBLIC_ZKP_DIR"
cp "$CIRCUITS_DIR/vote_js/vote.wasm" "$PUBLIC_ZKP_DIR/vote.wasm"
cp "$CIRCUITS_DIR/vote_final.zkey" "$PUBLIC_ZKP_DIR/vote_final.zkey"
cp "$CIRCUITS_DIR/vkey.json" "$PUBLIC_ZKP_DIR/vkey.json"
echo "  -> Copied: vote.wasm, vote_final.zkey, vkey.json"

# Summary
echo ""
echo "=== Setup Complete ==="
echo "Static assets deployed to: $PUBLIC_ZKP_DIR/"
ls -lh "$PUBLIC_ZKP_DIR/"
echo ""
echo "Circuit info:"
npx snarkjs r1cs info "$CIRCUITS_DIR/vote.r1cs"
