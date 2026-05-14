import * as snarkjs from "snarkjs";
import { readFileSync } from "fs";
import { existsSync } from "fs";

/**
 * Groth16 proof verifier script.
 * Reads JSON from stdin, verifies the proof using snarkjs, and outputs the result.
 *
 * Input (stdin): {"proof": {...}, "publicSignals": [...], "vkeyPath": "..."}
 * Output (stdout): {"valid": true/false} on success, {"error": "message"} on failure
 * Exit code: 0 on successful verification (even if proof is invalid), non-zero on error
 */

async function readStdin() {
    return new Promise((resolve, reject) => {
        let data = "";
        process.stdin.setEncoding("utf8");
        process.stdin.on("data", (chunk) => {
            data += chunk;
        });
        process.stdin.on("end", () => {
            resolve(data);
        });
        process.stdin.on("error", (err) => {
            reject(err);
        });
    });
}

async function main() {
    // Read and parse stdin
    let rawInput;
    try {
        rawInput = await readStdin();
    } catch (err) {
        process.stdout.write(JSON.stringify({ error: "Failed to read stdin" }));
        process.exit(1);
    }

    let input;
    try {
        input = JSON.parse(rawInput);
    } catch (err) {
        process.stdout.write(JSON.stringify({ error: "Invalid JSON input" }));
        process.exit(1);
    }

    // Validate required fields
    if (!input.proof) {
        process.stdout.write(JSON.stringify({ error: "Missing required field: proof" }));
        process.exit(1);
    }

    if (!input.publicSignals) {
        process.stdout.write(JSON.stringify({ error: "Missing required field: publicSignals" }));
        process.exit(1);
    }

    if (!input.vkeyPath) {
        process.stdout.write(JSON.stringify({ error: "Missing required field: vkeyPath" }));
        process.exit(1);
    }

    // Validate publicSignals is an array of exactly 4 string elements
    if (!Array.isArray(input.publicSignals)) {
        process.stdout.write(JSON.stringify({ error: "publicSignals must be an array" }));
        process.exit(1);
    }

    if (input.publicSignals.length !== 4) {
        process.stdout.write(
            JSON.stringify({ error: "publicSignals must contain exactly 4 elements" })
        );
        process.exit(1);
    }

    for (let i = 0; i < input.publicSignals.length; i++) {
        if (typeof input.publicSignals[i] !== "string") {
            process.stdout.write(
                JSON.stringify({ error: `publicSignals[${i}] must be a string` })
            );
            process.exit(1);
        }
    }

    // Load verification key from vkeyPath
    if (!existsSync(input.vkeyPath)) {
        process.stdout.write(
            JSON.stringify({ error: `Verification key file not found: ${input.vkeyPath}` })
        );
        process.exit(1);
    }

    let vkey;
    try {
        const vkeyData = readFileSync(input.vkeyPath, "utf8");
        vkey = JSON.parse(vkeyData);
    } catch (err) {
        process.stdout.write(
            JSON.stringify({ error: `Failed to load verification key: ${err.message}` })
        );
        process.exit(1);
    }

    // Verify the proof
    try {
        const valid = await snarkjs.groth16.verify(vkey, input.publicSignals, input.proof);
        process.stdout.write(JSON.stringify({ valid: valid }));
        process.exit(0);
    } catch (err) {
        process.stdout.write(
            JSON.stringify({ error: `Verification error: ${err.message}` })
        );
        process.exit(1);
    }
}

main();
