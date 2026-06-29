<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComplaintRequest;
use App\Services\LegalService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function __construct(
        private readonly LegalService $legal,
    ) {}

    public function terms(): Response
    {
        return Inertia::render('Legal/Terms', [
            'legal' => $this->legal->clientConfig(),
        ]);
    }

    public function privacy(): Response
    {
        return Inertia::render('Legal/Privacy', [
            'legal' => $this->legal->clientConfig(),
        ]);
    }

    public function refunds(): Response
    {
        return Inertia::render('Legal/Refunds', [
            'legal' => $this->legal->clientConfig(),
        ]);
    }

    public function notice(): Response
    {
        return Inertia::render('Legal/Notice', [
            'legal' => $this->legal->clientConfig(),
        ]);
    }

    public function complaints(): Response
    {
        return Inertia::render('Legal/Complaints', [
            'legal' => $this->legal->clientConfig(),
        ]);
    }

    public function storeComplaint(StoreComplaintRequest $request): RedirectResponse
    {
        $complaint = $this->legal->submitComplaint(
            $request->validated(),
            $request->user(),
        );

        return back()->with(
            'status',
            "Tu {$complaint->complaint_type} fue registrado con el número {$complaint->complaint_number}. Te responderemos en un plazo máximo de {$this->legal->clientConfig()['complaint_response_days']} días calendario.",
        );
    }
}
