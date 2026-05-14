import { buildPoseidon } from "circomlibjs";
import crypto from "crypto";

async function run() {
    const poseidon = await buildPoseidon();

    // Generate a random 31-byte string to safely fit inside the BN254 scalar field
    const privKeyBuffer = crypto.randomBytes(31);
    const privKey = BigInt("0x" + privKeyBuffer.toString("hex"));

    // Compute Poseidon hash of the private key
    // This acts as the public commitment we store in the database
    const commitment = poseidon.F.toObject(poseidon([privKey]));

    console.log(JSON.stringify({
        private_key: privKey.toString(),
        commitment: commitment.toString()
    }));
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
