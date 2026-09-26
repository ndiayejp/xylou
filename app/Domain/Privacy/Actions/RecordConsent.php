<?php

declare(strict_types=1);

namespace App\Domain\Privacy\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Privacy\Enums\ConsentKind;
use App\Domain\Privacy\Models\Consent;

// L'IP est pseudonymisée (HMAC avec la clé de l'application) : preuve du consentement sans donnée en clair.
final class RecordConsent
{
    public function __invoke(User $user, ConsentKind $kind, string $version, ?string $ip = null): Consent
    {
        $consent = new Consent([
            'kind' => $kind,
            'granted' => true,
            'version' => $version,
            'granted_at' => now(),
            'ip_hash' => $ip === null ? null : hash_hmac('sha256', $ip, (string) config('app.key')),
        ]);
        $consent->user()->associate($user);
        $consent->save();

        return $consent;
    }
}
