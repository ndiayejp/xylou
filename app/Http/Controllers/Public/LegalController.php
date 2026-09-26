<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

// Pages légales : le contenu vit dans vue-i18n (legal.<page>), une seule vue les affiche.
final class LegalController extends Controller
{
    public const array PAGES = ['notice', 'privacy', 'terms', 'accessibility'];

    public function __invoke(string $page): Response
    {
        return Inertia::render('Public/Legal', [
            'page' => $page,
            'contactEmail' => config('xylou.contact_email'),
            'updatedAt' => config('xylou.legal_updated_at'),
        ]);
    }
}
