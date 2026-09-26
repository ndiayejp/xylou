<?php

declare(strict_types=1);

namespace App\Domain\Privacy\Enums;

enum ConsentKind: string
{
    // « Je suis le parent ou le responsable légal de l'enfant. »
    case ParentalAuthority = 'parental_authority';
    // « J'accepte les conditions d'utilisation et j'ai lu la politique de confidentialité. »
    case TermsAndPrivacy = 'terms_privacy';
}
