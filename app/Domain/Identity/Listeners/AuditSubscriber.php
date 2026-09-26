<?php

declare(strict_types=1);

namespace App\Domain\Identity\Listeners;

use App\Domain\Identity\Enums\AuditEvent;
use App\Domain\Identity\Events\AccountDeleted;
use App\Domain\Identity\Events\KidSessionClosed;
use App\Domain\Identity\Events\KidSessionOpened;
use App\Domain\Identity\Events\ParentCodeRejected;
use App\Domain\Identity\Events\ParentPinChanged;
use App\Domain\Identity\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\Model;
use Laravel\Fortify\Events\RecoveryCodeReplaced;
use Laravel\Fortify\Events\RecoveryCodesGenerated;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationEnabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;

// Journal « security » (ADR 0014) : qui a fait quoi, jamais d'e-mail, de nom ni d'IP (§11.4).
final class AuditSubscriber
{
    /** @return array<class-string, string> */
    public function subscribe(): array
    {
        return [
            Login::class => 'onLogin',
            Logout::class => 'onLogout',
            Failed::class => 'onFailed',
            Lockout::class => 'onLockout',
            PasswordReset::class => 'onPasswordReset',
            AccountDeleted::class => 'onAccountDeleted',
            TwoFactorAuthenticationEnabled::class => 'onTwoFactor',
            TwoFactorAuthenticationConfirmed::class => 'onTwoFactor',
            TwoFactorAuthenticationDisabled::class => 'onTwoFactor',
            RecoveryCodesGenerated::class => 'onTwoFactor',
            ValidTwoFactorAuthenticationCodeProvided::class => 'onTwoFactor',
            TwoFactorAuthenticationFailed::class => 'onTwoFactor',
            RecoveryCodeReplaced::class => 'onTwoFactor',
            KidSessionOpened::class => 'onKidSessionOpened',
            KidSessionClosed::class => 'onKidSessionClosed',
            ParentCodeRejected::class => 'onParentCodeRejected',
            ParentPinChanged::class => 'onParentPinChanged',
        ];
    }

    // La session enfant a ses propres événements : on ne journalise ici que la garde « web ».
    public function onLogin(Login $event): void
    {
        if ($event->guard === 'web' && $event->user instanceof User) {
            $this->record(AuditEvent::Login, $event->user);
        }
    }

    public function onLogout(Logout $event): void
    {
        if ($event->guard === 'web' && $event->user instanceof User) {
            $this->record(AuditEvent::Logout, $event->user);
        }
    }

    // Compte inconnu : aucune trace de l'e-mail saisi.
    public function onFailed(Failed $event): void
    {
        $this->record(AuditEvent::LoginFailed, $event->user instanceof User ? $event->user : null);
    }

    public function onLockout(): void
    {
        $this->record(AuditEvent::Lockout, null);
    }

    public function onPasswordReset(PasswordReset $event): void
    {
        $this->record(AuditEvent::PasswordReset, $event->user instanceof User ? $event->user : null);
    }

    public function onAccountDeleted(AccountDeleted $event): void
    {
        activity(AuditEvent::LOG_NAME)
            ->event(AuditEvent::AccountDeleted->value)
            ->withProperty('user_id', $event->userId)
            ->log(AuditEvent::AccountDeleted->value);
    }

    public function onTwoFactor(object $event): void
    {
        $name = match ($event::class) {
            TwoFactorAuthenticationEnabled::class => AuditEvent::TwoFactorEnabled,
            TwoFactorAuthenticationConfirmed::class => AuditEvent::TwoFactorConfirmed,
            TwoFactorAuthenticationDisabled::class => AuditEvent::TwoFactorDisabled,
            RecoveryCodesGenerated::class => AuditEvent::RecoveryCodesRegenerated,
            ValidTwoFactorAuthenticationCodeProvided::class => AuditEvent::TwoFactorPassed,
            TwoFactorAuthenticationFailed::class => AuditEvent::TwoFactorFailed,
            default => AuditEvent::RecoveryCodeUsed,
        };
        // Les événements Fortify annoncent App\Models\User ; notre modèle vit dans le domaine Identity.
        $user = data_get($event, 'user');

        $this->record($name, $user instanceof User ? $user : null);
    }

    public function onKidSessionOpened(KidSessionOpened $event): void
    {
        $this->record(AuditEvent::KidSessionOpened, $event->parent, $event->child);
    }

    public function onKidSessionClosed(KidSessionClosed $event): void
    {
        $this->record(AuditEvent::KidSessionClosed, $event->parent, $event->child);
    }

    public function onParentCodeRejected(ParentCodeRejected $event): void
    {
        $this->record(
            $event->locked ? AuditEvent::ParentCodeLocked : AuditEvent::ParentCodeRejected,
            $event->parent,
            $event->child,
        );
    }

    public function onParentPinChanged(ParentPinChanged $event): void
    {
        $this->record($event->removed ? AuditEvent::ParentPinRemoved : AuditEvent::ParentPinUpdated, $event->parent);
    }

    private function record(AuditEvent $event, ?User $causer, ?Model $subject = null): void
    {
        $logger = activity(AuditEvent::LOG_NAME)->event($event->value)->causedBy($causer);

        if ($subject instanceof Model) {
            $logger->performedOn($subject);
        }

        $logger->log($event->value);
    }
}
