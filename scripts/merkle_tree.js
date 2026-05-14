import { buildPoseidon } from "circomlibjs";

const TREE_DEPTH = 10;
const MAX_LEAVES = 1024; // 2^10

async function run() {
    let input = "";

    for await (const chunk of process.stdin) {
        input += chunk;
    }

    if (!input.trim()) {
        throw new Error("No input provided on stdin");
    }

    let data;
    try {
        data = JSON.parse(input);
    } catch {
        throw new Error("Invalid JSON input");
    }

    if (!data.leaves || !Array.isArray(data.leaves)) {
        throw new Error("Missing or invalid 'leaves' array");
    }

    const depth = data.depth ?? TREE_DEPTH;

    if (depth !== TREE_DEPTH) {
        throw new Error(`Unsupported tree depth: ${depth}. Only depth ${TREE_DEPTH} is supported`);
    }

    if (data.leaves.length > MAX_LEAVES) {
        throw new Error(
            `Voter count (${data.leaves.length}) exceeds maximum tree capacity of ${MAX_LEAVES}`
        );
    }

    const poseidon = await buildPoseidon();
    const F = poseidon.F;

    // Pad leaves to exactly 1024 entries with zero values
    const leaves = new Array(MAX_LEAVES);
    for (let i = 0; i < MAX_LEAVES; i++) {
        if (i < data.leaves.length) {
            leaves[i] = BigInt(data.leaves[i]);
        } else {
            leaves[i] = 0n;
        }
    }

    // Build the Merkle Tree level by level
    // nodes[0] = leaves (1024), nodes[1] = 512 nodes, ..., nodes[10] = root (1 node)
    const nodes = [];
    nodes.push(leaves.map((l) => l.toString()));

    let currentLevel = leaves;

    for (let level = 1; level <= TREE_DEPTH; level++) {
        const nextLevel = [];
        for (let i = 0; i < currentLevel.length; i += 2) {
            const left = currentLevel[i];
            const right = currentLevel[i + 1];
            const hash = F.toObject(poseidon([left, right]));
            nextLevel.push(hash);
        }
        nodes.push(nextLevel.map((n) => n.toString()));
        currentLevel = nextLevel;
    }

    const root = currentLevel[0].toString();

    process.stdout.write(JSON.stringify({ root, nodes }));
}

run().catch((err) => {
    process.stdout.write(JSON.stringify({ error: err.message }));
    process.exit(1);
});
