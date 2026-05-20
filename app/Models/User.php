<?php

namespace App\Models;

// I needed the User to be an Authenticatable and extend BaseModule at the same time. Since PHP does not allow multiple Inheritance, I needed to improvise.
// By Manually importing Authenticatable's contracts and traits I can extend BaseModule while keeping User as an Authenticatable

// Auth Interfaces (Contracts)
use Database\Factories\UserFactory;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
// Auth Traits that fulfill the Contracts above
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;

//  Extend BaseModule and implement the Auth Contracts
class User extends BaseModule implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    // 5. Use the Auth Traits inside the class
    use Authenticatable, Authorizable, CanResetPassword;

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_admin',
        'username',
    ];

    public function toSearchResult(): array
    {
        return array_merge(parent::toSearchResult(), [
            'label' => $this->name,
            'sublabel' => $this->username,
        ]);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Get paginated users for linking panels.
     * Supports search by name or email.
     */
    public static function getRecordsForLinking(int $perPage, ?string $search = null): LengthAwarePaginator
    {
        $query = static::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function ($user) {
            if ($user->isDirty('first_name') || $user->isDirty('last_name')) {
                $user->name = $user->first_name.' '.$user->last_name;
            }
        });
    }

    public function isRoot(): bool
    {
        return (bool) $this->is_root;
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->is_root && $this->status === 'active';
    }
}
