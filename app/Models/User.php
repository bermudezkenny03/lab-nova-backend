<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone',
        'status',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    /** Relationships */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'user_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'user_id');
    }

    /** User helpers */
    public static function createUser(array $validated): self
    {
        return self::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'] ?? true,
            'role_id' => $validated['role_id'],
        ]);
    }

    public function updateUser(array $validated): void
    {
        $this->update(
            array_filter(
                $validated,
                fn($key) => in_array($key, $this->getFillable(), true),
                ARRAY_FILTER_USE_KEY
            )
        );
    }

    /** Query scopes */
    public function scopeWhereNotAssigned($query, array $assignedUserIds)
    {
        return $query->whereNotIn('id', $assignedUserIds);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('status', false);
    }

    public function scopeByRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /** Role helpers */
    public function isSuperAdmin(): bool
    {
        return (int) $this->role_id === (int) config('dashboard.roles.super_admin');
    }

    public function isAdmin(): bool
    {
        return (int) $this->role_id === (int) config('dashboard.roles.admin');
    }

    public function isLabManager(): bool
    {
        return (int) $this->role_id === (int) config('dashboard.roles.lab_manager');
    }

    public function isTeacher(): bool
    {
        return (int) $this->role_id === (int) config('dashboard.roles.teacher');
    }

    public function isStudent(): bool
    {
        return (int) $this->role_id === (int) config('dashboard.roles.student');
    }

    public function isStaff(): bool
    {
        return $this->status && in_array(
            (int) $this->role_id,
            config('dashboard.staff_role_ids', []),
            true
        );
    }

    /** Module access */
    public function hasModule(string $moduleSlug): bool
    {
        return $this->role?->hasModule($moduleSlug) ?? false;
    }

    public function getModules(): array
    {
        $modules = collect($this->role?->getModules() ?? []);

        if ($this->isStaff() && ! $modules->contains('dashboard')) {
            $modules->prepend('dashboard');
        }

        return $modules->values()->toArray();
    }

    public function getModulesWithInfo(): array
    {
        $modules = collect($this->role?->getModulesWithInfo() ?? []);

        if ($this->isStaff()) {
            $dashboard = Module::query()
                ->where('slug', 'dashboard')
                ->where('is_active', true)
                ->first();

            if ($dashboard && ! $modules->contains(fn($module) => $module['slug'] === 'dashboard')) {
                $modules->prepend([
                    'slug' => $dashboard->slug,
                    'name' => $dashboard->name,
                    'icon' => $dashboard->icon,
                    'route' => $dashboard->route,
                    'sort_order' => 1,
                    'children' => [],
                ]);
            }
        }

        return $modules
            ->sortBy('sort_order')
            ->values()
            ->toArray();
    }

    /** Permission access */
    public function hasPermission(string $moduleSlug, string $permissionSlug): bool
    {
        if (! $this->role) {
            return false;
        }

        return RoleModulePermission::where('role_id', $this->role->id)
            ->whereHas('module', function ($query) use ($moduleSlug) {
                $query->where('slug', $moduleSlug)
                    ->where('is_active', true);
            })
            ->whereHas('permission', function ($query) use ($permissionSlug) {
                $query->where('slug', $permissionSlug);
            })
            ->exists();
    }

    /** Cached permission map */
    public function getPermissionMap(array $moduleSlugs = []): array
    {
        $actions = ['view', 'create', 'edit', 'delete'];
        $mapKeySuffix = '';

        if (! empty($moduleSlugs)) {
            $mapKeySuffix = ':' . md5(implode(',', $moduleSlugs));
        }

        $cacheKey = "user_permissions:{$this->id}{$mapKeySuffix}";
        $ttl = (int) config('dashboard.cache_ttl_minutes', 60);

        return Cache::remember($cacheKey, now()->addMinutes($ttl), function () use ($moduleSlugs, $actions) {
            $map = [];

            if (empty($moduleSlugs)) {
                $moduleSlugs = Module::query()
                    ->where('is_active', true)
                    ->pluck('slug')
                    ->toArray();
            }

            foreach ($moduleSlugs as $slug) {
                $map[$slug] = [];

                foreach ($actions as $action) {
                    $map[$slug][$action] = $this->hasPermission($slug, $action);
                }
            }

            $this->ensureDashboardPermission($map);

            return $map;
        });
    }

    /** Dashboard permission fallback */
    private function ensureDashboardPermission(array &$map): void
    {
        $dashboardKey = 'dashboard';

        if (array_key_exists($dashboardKey, $map)) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $map[$dashboardKey][$action] = $map[$dashboardKey][$action] ?? false;
            }

            return;
        }

        $relevantModules = config('dashboard.relevant_modules', [
            'reports',
            'reservations',
            'equipment',
            'users',
            'categories',
        ]);

        $canViewDashboard = false;

        foreach ($relevantModules as $moduleSlug) {
            if (($map[$moduleSlug]['view'] ?? false) === true) {
                $canViewDashboard = true;
                break;
            }
        }

        $map[$dashboardKey] = [
            'view' => $canViewDashboard || $this->isStaff(),
            'create' => false,
            'edit' => false,
            'delete' => false,
        ];
    }

    /** Cache helpers */
    public function clearPermissionCache(array $moduleSlugs = []): void
    {
        $mapKeySuffix = '';

        if (! empty($moduleSlugs)) {
            $mapKeySuffix = ':' . md5(implode(',', $moduleSlugs));
        }

        $cacheKey = "user_permissions:{$this->id}{$mapKeySuffix}";

        Cache::forget($cacheKey);
    }
}
