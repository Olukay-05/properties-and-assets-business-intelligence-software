<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\Models;

use App\Domain\Tenancy\Enums\MembershipRole;
use App\Domain\Tenancy\Enums\MembershipStatus;
use App\Infrastructure\Tenancy\HasTenant;
use App\Infrastructure\Tenancy\TenantContextService;
use App\Models\User;
use Database\Factories\MembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Links a user to an organisation with a role set, a client scope and a status.
 *
 * `memberships` is the first RLS-protected tenant table created by Story 1.2
 * (AD-4). It is protected twice: PostgreSQL `FORCE ROW LEVEL SECURITY` and the
 * {@see HasTenant} global scope + create stamp.
 *
 * The pre-tenant bootstrap read (resolving which organisation an
 * authenticated user belongs to) never goes through this model; it goes
 * through the `resolve_user_memberships()` `SECURITY DEFINER` function via
 * {@see TenantContextService}, which returns only
 * the columns needed to build a context and is deliberately not a policy on
 * this table.
 *
 * `organisation_id` is never fillable: it is stamped from the bound context.
 *
 * @property string $id
 * @property string $organisation_id
 * @property string $user_id
 * @property list<string> $roles
 * @property list<string>|null $client_scope
 * @property MembershipStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'roles', 'client_scope', 'status'])]
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory, HasTenant, HasUuids;

    /**
     * @var string
     */
    protected $table = 'memberships';

    /**
     * Domain models are namespaced, so the conventional factory name does not
     * resolve; point at it explicitly.
     */
    protected static function newFactory(): MembershipFactory
    {
        return MembershipFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'roles' => 'array',
            'client_scope' => 'array',
            'status' => MembershipStatus::class,
        ];
    }

    /**
     * The member user. `users` is a system identity table, so this relation is
     * not tenant-scoped.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return list<MembershipRole>
     */
    public function roleEnums(): array
    {
        return array_values(array_filter(array_map(
            fn (string $role): ?MembershipRole => MembershipRole::tryFrom($role),
            $this->roles,
        )));
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}
