<?php

namespace Tests\Feature;

use App\Models\Pemilih;
use App\Models\Pemilu;
use App\Services\MerkleTreeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoterRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_voter_registration_regenerates_merkle_tree(): void
    {
        Pemilu::create([
            'name' => 'Test Election',
            'description' => 'Test',
            'status' => 'BERJALAN',
        ]);

        Pemilih::create([
            'nik' => '1234567890123456',
            'nama_pemilih' => 'Test Voter',
            'registration_status' => 'APPROVED',
            'registration_token' => 'test-token',
        ]);

        $this->mock(MerkleTreeService::class, function ($mock) {
            $mock->shouldReceive('regenerateTreesForEligibleElections')
                ->once()
                ->andReturn([]);
        });

        $response = $this->post(route('voter.register.store'), [
            'nik' => '1234567890123456',
            'commitment' => '987654321',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('pemilih', [
            'nik' => '1234567890123456',
            'private_key_hash' => '987654321',
            'registration_status' => 'REGISTERED',
        ]);
    }
}