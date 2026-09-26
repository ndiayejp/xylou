<?php

declare(strict_types=1);

return [
    // Adresse affichée sur la landing et les pages légales. [À compléter avant la mise en production]
    'contact_email' => env('XYLOU_CONTACT_EMAIL', 'contact@example.com'),

    // Date de la version en vigueur des pages légales (affichée en tête de page).
    'legal_updated_at' => '2026-09-26',

    // Version des conditions et de la politique acceptées à l'inscription (enregistrée avec le consentement).
    // Une nouvelle version devra être réacceptée (§11.1).
    'consent_version' => '2026-09-26',
];
