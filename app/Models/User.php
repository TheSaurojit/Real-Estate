<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'role_id',
        'user_code',
        'name',
        'designation',
        'mobile',
        'email',
        'password',
        'role',
        'assigned_project_ids',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'assigned_project_ids' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->roleRelation) {
            return $this->roleRelation->hasPermission($permissionSlug);
        }

        return false;
    }

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin(): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        if ($this->roleRelation && $this->roleRelation->slug === 'super_admin') {
            return true;
        }

        return false;
    }

    /**
     * Check if user has access to a specific project
     */
    public function hasAccessToProject(int $projectId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $assigned = $this->assigned_project_ids ?? [];
        return in_array($projectId, $assigned) || in_array((string)$projectId, $assigned);
    }
}
