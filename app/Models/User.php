<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasPublicId, Notifiable;

    public const ADMIN_ROLE_ADMIN = 'admin';
    public const ADMIN_ROLE_OPERATOR = 'operator';
    public const ADMIN_ROLE_CONTENT = 'content';
    public const ADMIN_ROLE_ANALYST = 'analyst';

    protected const ROLE_CAPABILITIES = [
        self::ADMIN_ROLE_ADMIN => ['dashboard', 'requests', 'payments', 'quizzes', 'results', 'callbacks'],
        self::ADMIN_ROLE_OPERATOR => ['requests', 'payments'],
        self::ADMIN_ROLE_CONTENT => ['quizzes'],
        self::ADMIN_ROLE_ANALYST => ['results'],
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'school',
        'city',
        'password',
        'is_admin',
        'admin_role',
        'plan',
        'settings',
        'public_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'admin_role' => 'string',
        'settings' => 'array',
    ];

    protected $appends = [
        'admin_capabilities',
        'has_admin_access',
    ];

    protected $hidden = [
        'id',
        'password',
        'remember_token',
    ];

    public function isAdmin(): bool
    {
        return $this->hasAdminAccess();
    }

    public function hasAdminAccess(): bool
    {
        return $this->resolvedAdminRole() !== null;
    }

    public function adminRole(): ?string
    {
        return $this->resolvedAdminRole();
    }

    public function hasAdminCapability(?string $capability = null): bool
    {
        if (!$this->hasAdminAccess()) {
            return false;
        }

        if (!$capability) {
            return true;
        }

        return in_array($capability, $this->getAdminCapabilitiesAttribute(), true);
    }

    public function canAccessAdminPath(?string $path): bool
    {
        if (!$this->hasAdminAccess()) {
            return false;
        }

        $normalized = '/' . ltrim((string) parse_url((string) $path, PHP_URL_PATH), '/');

        return match (true) {
            $normalized === '/admin', $normalized === '/admin/' => $this->hasAdminCapability('dashboard'),
            str_starts_with($normalized, '/admin/requests') => $this->hasAdminCapability('requests'),
            str_starts_with($normalized, '/admin/payments') => $this->hasAdminCapability('payments'),
            str_starts_with($normalized, '/admin/quizzes') => $this->hasAdminCapability('quizzes'),
            str_starts_with($normalized, '/admin/results') => $this->hasAdminCapability('results'),
            str_starts_with($normalized, '/admin/callbacks') => $this->hasAdminCapability('callbacks'),
            default => $this->hasAdminCapability('dashboard'),
        };
    }

    public function getAdminCapabilitiesAttribute(): array
    {
        return self::ROLE_CAPABILITIES[$this->resolvedAdminRole()] ?? [];
    }

    public function getHasAdminAccessAttribute(): bool
    {
        return $this->hasAdminAccess();
    }

    protected function resolvedAdminRole(): ?string
    {
        if (!empty($this->admin_role) && array_key_exists($this->admin_role, self::ROLE_CAPABILITIES)) {
            return $this->admin_role;
        }

        return $this->is_admin ? self::ADMIN_ROLE_ADMIN : null;
    }

    public function olympiadRequests()
    {
        return $this->hasMany(\App\Models\OlympiadRequest::class);
    }

    public function childProfiles()
    {
        return $this->hasMany(ChildProfile::class, 'parent_id')->orderBy('first_name');
    }

    public function quizResults()
    {
        return $this->hasMany(QuizResult::class);
    }

    public function trainingAttempts()
    {
        return $this->hasMany(TrainingAttempt::class, 'parent_id');
    }

    public function paymentRecords()
    {
        return $this->hasMany(PaymentRecord::class, 'parent_id');
    }

    public function platformNotifications()
    {
        return $this->hasMany(PlatformNotification::class)->latest();
    }
}
