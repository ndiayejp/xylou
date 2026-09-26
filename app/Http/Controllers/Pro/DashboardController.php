<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pro;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Pro/Dashboard/Index');
    }
}
