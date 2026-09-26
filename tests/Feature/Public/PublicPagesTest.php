<?php

declare(strict_types=1);

use App\Domain\Identity\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('la landing est publique et reçoit l’adresse de contact', function (): void {
    config(['xylou.contact_email' => 'bonjour@xylou.test']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Public/Home')
            ->where('contactEmail', 'bonjour@xylou.test'));
});

test('la landing reste accessible une fois connecté', function (): void {
    $this->actingAs(User::factory()->parent()->create())
        ->get(route('home'))
        ->assertOk();
});

test('chaque page légale est publique', function (string $page, string $uri): void {
    $this->get($uri)
        ->assertOk()
        ->assertInertia(fn (Assert $inertia): Assert => $inertia
            ->component('Public/Legal')
            ->where('page', $page)
            ->has('updatedAt'));
})->with([
    ['notice', '/mentions-legales'],
    ['privacy', '/confidentialite'],
    ['terms', '/conditions-utilisation'],
    ['accessibility', '/accessibilite'],
]);

test('le sitemap liste les pages publiques, sans espace privé', function (): void {
    $response = $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

    $xml = $response->getContent();

    expect($xml)
        ->toContain('<loc>'.route('home').'</loc>')
        ->toContain('<loc>'.route('legal.privacy').'</loc>')
        ->not->toContain('/parent')
        ->not->toContain('/enfant');
});

test('robots.txt écarte les espaces privés', function (): void {
    $robots = (string) file_get_contents(public_path('robots.txt'));

    expect($robots)->toContain('Disallow: /parent')
        ->toContain('Disallow: /enfant')
        ->toContain('Disallow: /pro');
});
