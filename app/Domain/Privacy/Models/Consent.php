<?php

declare(strict_types=1);

namespace App\Domain\Privacy\Models;

use App\Domain\Identity\Models\User;
use App\Domain\Privacy\Enums\ConsentKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property ConsentKind $kind
 * @property bool $granted
 * @property string $version
 */
class Consent extends Model
{
    protected $fillable = ['kind', 'granted', 'version', 'granted_at', 'ip_hash'];

    protected function casts(): array
    {
        return [
            'kind' => ConsentKind::class,
            'granted' => 'boolean',
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
