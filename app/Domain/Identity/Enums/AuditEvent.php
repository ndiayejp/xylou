<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

// Événements du journal « security » (§11.4 : identifiants et type d'action, jamais de donnée personnelle).
enum AuditEvent: string
{
    case Login = 'login';
    case LoginFailed = 'login_failed';
    case Logout = 'logout';
    case Lockout = 'lockout';
    case PasswordReset = 'password_reset';
    case AccountDeleted = 'account_deleted';

    case TwoFactorEnabled = 'two_factor_enabled';
    case TwoFactorConfirmed = 'two_factor_confirmed';
    case TwoFactorDisabled = 'two_factor_disabled';
    case RecoveryCodesRegenerated = 'recovery_codes_regenerated';
    case TwoFactorPassed = 'two_factor_passed';
    case TwoFactorFailed = 'two_factor_failed';
    case RecoveryCodeUsed = 'recovery_code_used';

    case KidSessionOpened = 'kid_session_opened';
    case KidSessionClosed = 'kid_session_closed';
    case ParentCodeRejected = 'parent_code_rejected';
    case ParentCodeLocked = 'parent_code_locked';
    case ParentPinUpdated = 'parent_pin_updated';
    case ParentPinRemoved = 'parent_pin_removed';

    public const string LOG_NAME = 'security';
}
