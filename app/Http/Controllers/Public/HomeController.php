<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Public/Home', [
            'contactEmail' => config('xylou.contact_email'),
        ]);
    }
}
