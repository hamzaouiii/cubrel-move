<?php

namespace App\Models;

// I needed the User to be an Authenticatable and extend BaseModule at the same time. Since PHP does not allow multiple Inheritance, I needed to improvise.
// By Manually importing Authenticatable's contracts and traits I can extend BaseModule while keeping User as an Authenticatable

// Auth Interfaces (Contracts)
use App\Notifications\ResetPasswordNotification;
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
            'is_root' => 'boolean',
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

    /**
     * Deliberately skips BaseModule::booted() — that hook auto-fills owner_id
     * on every model, but `users` has no owner_id column (a user can't own
     * itself the way a Lead/Deal/etc. is owned by a user). Calling it here
     * crashes every User::create() with "Unknown column 'owner_id'".
     */
    protected static function booted(): void
    {
        static::saving(function ($user) {
            if ($user->isDirty('first_name') || $user->isDirty('last_name')) {
                $user->name = $user->first_name.' '.$user->last_name;
            }
        });

        static::bootAuditObserver();
    }

    public function isRoot(): bool
    {
        return (bool) $this->is_root;
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->is_root && $this->status === 'active';
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Build a User from a self-service account form (invite acceptance,
     * setup bootstrap, etc.) — shared so those flows don't duplicate the
     * field mapping. $overrides goes through forceFill so privileged flags
     * like is_admin/is_root can be set regardless of $fillable, without
     * making them mass-assignable from arbitrary form input.
     */
    public static function createFromAccountForm(array $data, array $overrides = []): self
    {
        $user = new self([
            'username'   => $data['username'],
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'] ?? null,
            'password'   => $data['password'],
        ]);

        $user->forceFill($overrides);
        $user->save();

        return $user;
    }
}
