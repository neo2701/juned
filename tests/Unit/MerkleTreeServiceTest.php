<?php

namespace Tests\Unit;

use App\Models\MerkleLeaf;
use App\Models\MerkleTree;
use App\Models\Pemilih;
use App\Models\Pemilu;
use App\Services\MerkleTreeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MerkleTreeServiceTest extends TestCase
{
    use RefreshDatabase;

    private MerkleTreeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MerkleTreeService();
    }

    public function test_build_tree_with_voters(): void
    {
        $pemilu = Pemilu::create([
            'name' => 'Test Election',
            'description' => 'Test',
            'status' => 'BERJALAN',
        ]);

        // Create a few voters with known commitments
        Pemilih::create(['nik' => '1234567890', 'private_key_hash' => '12345']);
        Pemilih::create(['nik' => '0987654321', 'private_key_hash' => '67890']);

        $tree = $this->service->buildTree($pemilu->id);

        $this->assertInstanceOf(MerkleTree::class, $tree);
        $this->assertEquals($pemilu->id, $tree->pemilu_id);
        $this->assertEquals('FINAL', $tree->status);
        $this->assertNotNull($tree->root_hash);
        $this->assertNotEmpty($tree->root_hash);
        $this->assertNotNull($tree->nodes_data);

        // Should have 1024 leaves stored
        $leafCount = MerkleLeaf::where('merkle_tree_id', $tree->id)->count();
        $this->assertEquals(1024, $leafCount);
    }

    public function test_build_tree_rejects_more_than_1024_voters(): void
    {
        $pemilu = Pemilu::create([
            'name' => 'Test Election',
            'description' => 'Test',
            'status' => 'BERJALAN',
        ]);

        // Create 1025 voters
        for ($i = 0; $i < 1025; $i++) {
            Pemilih::create([
                'nik' => str_pad((string) $i, 16, '0', STR_PAD_LEFT),
                'private_key_hash' => (string) ($i + 1),
            ]);
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('exceeds maximum tree capacity of 1024');

        $this->service->buildTree($pemilu->id);
    }

    public function test_get_proof_returns_valid_proof(): void
    {
        $pemilu = Pemilu::create([
            'name' => 'Test Election',
            'description' => 'Test',
            'status' => 'BERJALAN',
        ]);

        $commitment = '12345';
        Pemilih::create(['nik' => '1234567890', 'private_key_hash' => $commitment]);

        $tree = $this->service->buildTree($pemilu->id);

        $proof = $this->service->getProof($pemilu->id, $commitment);

        $this->assertArrayHasKey('pathElements', $proof);
        $this->assertArrayHasKey('pathIndices', $proof);
        $this->assertCount(10, $proof['pathElements']);
        $this->assertCount(10, $proof['pathIndices']);

        // Path indices should be 0 or 1
        foreach ($proof['pathIndices'] as $index) {
            $this->assertContains($index, [0, 1]);
        }
    }

    public function test_get_proof_throws_for_unknown_commitment(): void
    {
        $pemilu = Pemilu::create([
            'name' => 'Test Election',
            'description' => 'Test',
            'status' => 'BERJALAN',
        ]);

        Pemilih::create(['nik' => '1234567890', 'private_key_hash' => '12345']);
        $this->service->buildTree($pemilu->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not found');

        $this->service->getProof($pemilu->id, 'nonexistent_commitment');
    }

    public function test_get_tree_data_returns_leaves_and_root(): void
    {
        $pemilu = Pemilu::create([
            'name' => 'Test Election',
            'description' => 'Test',
            'status' => 'BERJALAN',
        ]);

        Pemilih::create(['nik' => '1234567890', 'private_key_hash' => '12345']);
        Pemilih::create(['nik' => '0987654321', 'private_key_hash' => '67890']);

        $tree = $this->service->buildTree($pemilu->id);

        $data = $this->service->getTreeData($pemilu->id);

        $this->assertArrayHasKey('leaves', $data);
        $this->assertArrayHasKey('root', $data);
        $this->assertCount(1024, $data['leaves']);
        $this->assertEquals($tree->root_hash, $data['root']);

        // First two leaves should be the voter commitments
        $this->assertEquals('12345', $data['leaves'][0]);
        $this->assertEquals('67890', $data['leaves'][1]);
    }

    public function test_get_tree_data_throws_when_tree_not_generated(): void
    {
        $pemilu = Pemilu::create([
            'name' => 'Test Election',
            'description' => 'Test',
            'status' => 'BERJALAN',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not available');

        $this->service->getTreeData($pemilu->id);
    }
}
