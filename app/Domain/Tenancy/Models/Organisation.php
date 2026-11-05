<?php

declare(strict_types=1);

namespace App\Domain\Tenancy\Models;

use App\Domain\Tenancy\Enums\OrganisationStatus;
use Database\Factories\OrganisationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A tenant: one paying facilities-management provider.
 *
 * `organisations` is a system identity table (AD-4): it is read during active
 * membership resolution, before any tenant is bound, so it carries no RLS
 * policy. It is reachable only through the membership path or through the
 * bound tenant's own `organisation()` relation.
 *
 * @property string $id
 * @property string $name
 * @property OrganisationStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'status'])]
class Organisation extends Model
{
    /** @use HasFactory<OrganisationFactory> */
    use HasFactory, HasUuids;

    /**
     * @var string
     */
    protected $table = 'organisations';

    /**
     * Domain models are namespaced, so the conventional factory name does not
     * resolve; point at it explicitly.
     */
    protected static function newFactory(): OrganisationFactory
    {
        return OrganisationFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrganisationStatus::class,
        ];
    }

    /**
     * The organisation's memberships.
     *
     * `memberships` is RLS-protected, so this relation only ever returns the
     * bound tenant's rows. It is not eager-loadable from outside a tenant.
     *
     * @return HasMany<Membership, $this>
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}
