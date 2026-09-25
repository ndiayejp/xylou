<?php

declare(strict_types=1);

arch('le code applicatif déclare strict_types')
    ->expect('App')
    ->toUseStrictTypes();

arch('aucune fonction de débogage')
    ->expect(['dd', 'ddd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('aucune fonction PHP à éviter')
    ->preset()->php();

arch('aucune fonction PHP dangereuse')
    ->preset()->security();

arch('conventions Laravel')
    ->preset()->laravel();

arch('les domaines ne dépendent pas de la couche HTTP')
    ->expect('App\Domain')
    ->not->toUse('App\Http');

arch('Support ne dépend d\'aucun domaine')
    ->expect('App\Support')
    ->not->toUse('App\Domain');
