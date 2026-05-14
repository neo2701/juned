import { buildPoseidon } from "circomlibjs";

async function run() {
    const poseidon = await buildPoseidon();
    const input = process.argv[2];

    if (!input) {
        throw new Error("Missing input");
    }

    const commitment = poseidon.F.toObject(poseidon([BigInt(input)]));
    console.log(commitment.toString());
}

run().catch(err => {
    console.error(err);
    process.exit(1);
});
