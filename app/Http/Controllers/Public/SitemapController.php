<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

// SEO de base : les pages publiques indexables.
final class SitemapController extends Controller
{
    public const array ROUTES = ['home', 'register', 'login', 'legal.notice', 'legal.privacy', 'legal.terms', 'legal.accessibility'];

    public function __invoke(): Response
    {
        $urls = array_map(fn (string $name): string => '  <url><loc>'.e(route($name)).'</loc></url>', self::ROUTES);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
            .implode("\n", $urls)."\n"
            .'</urlset>'."\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
