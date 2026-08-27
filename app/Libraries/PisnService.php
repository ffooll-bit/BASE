<?php

namespace App\Libraries;

/**
 * PISN (Penomoran Ijazah dan Sertifikat Profesi Nasional) eligibility check.
 *
 * The live PISN API is deferred pending LLDIKTI confirmation; this service is the
 * scaffolding seam so the real adapter can be plugged in later without touching
 * the graduation wizard.
 */
class PisnService
{
    /**
     * Checks PISN eligibility for a student.
     *
     * @param array $student The student context (nim, nama, ...).
     *
     * @return array{available: bool, eligible: bool|null, reason: string}
     */
    public function checkEligibility(array $student): array
    {
        // ponytail: API deferred — stable "not available" result so the wizard
        // renders the manual-eligibility step without a live call. Plug the real
        // adapter in here once LLDIKTI confirms the endpoint.
        return [
            'available' => false,
            'eligible'  => null,
            'reason'    => 'PISN API not yet available (awaiting LLDIKTI confirmation); verify eligibility manually.',
        ];
    }
}
