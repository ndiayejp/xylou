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

// Les domaines rangent eux-mêmes leurs Models, Enums, Actions… (ADR 0002).
arch('conventions Laravel')
    ->preset()->laravel()
    ->ignoring('App\Domain');

arch('les domaines ne dépendent pas de la couche HTTP')
    ->expect('App\Domain')
    ->not->toUse('App\Http');

arch('Support ne dépend d\'aucun domaine')
    ->expect('App\Support')
    ->not->toUse('App\Domain');

arch('les Actions des domaines sont des classes finales invocables')
    ->expect(['App\Domain\Identity\Actions', 'App\Domain\Children\Actions', 'App\Domain\Curriculum\Actions', 'App\Domain\Activities\Actions'])
    ->toBeFinal()
    ->toHaveMethod('__invoke');

// Le référentiel est une donnée de base : les autres domaines le lisent, lui ne dépend de personne.
arch('le référentiel ne dépend d’aucun autre domaine')
    ->expect('App\Domain\Curriculum')
    ->not->toUse(['App\Domain\Children', 'App\Domain\Identity', 'App\Domain\Privacy', 'App\Domain\Activities']);

arch('les Policies vivent dans leur domaine')
    ->expect('App\Policies')
    ->not->toBeUsed();
