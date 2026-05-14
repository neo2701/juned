<?php

namespace App\Http\Middleware;

use App\Models\Pemilih;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'voter' => fn() => $this->getVoterData($request),
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'new_private_key' => fn() => $request->session()->get('new_private_key'),
                'new_voter_nik' => fn() => $request->session()->get('new_voter_nik'),
            ],
        ];
    }

    /**
     * Get the authenticated voter's session data.
     *
     * @return array<string, mixed>|null
     */
    private function getVoterData(Request $request): ?array
    {
        $voterId = $request->session()->get('voter_id');

        if (!$voterId) {
            return null;
        }

        $pemilih = Pemilih::find($voterId);

        if (!$pemilih) {
            return null;
        }

        return [
            'id' => $pemilih->id,
            'nik' => $pemilih->nik,
        ];
    }
}
