<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone',
        'status',
        'role_id',
    ];

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
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function userDetail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public static function createUser($validated)
    {
        $user = self::create([
            'name' => $validated['name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'],
            'status' => $validated['status'],
            'role_id' => $validated['role_id'],
        ]);

        return $user;
    }

    public function updateUser($validated)
    {
        $this->update(array_filter($validated, fn($key) => in_array($key, $this->getFillable()), ARRAY_FILTER_USE_KEY));
    }

    public function scopeWhereNotAssigned($query, $assignedUserIds)
    {
        return $query->whereNotIn('id', $assignedUserIds);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id', 'desc');
    }

    public function hasModule($moduleSlug)
    {
        return $this->role->hasModule($moduleSlug) ?? false;
    }

    public function isStaff(): bool
    {
        if (! $this->status || ! $this->role) {
            return false;
        }

        return in_array($this->role->name, [
            'Super Admin',
            'Admin',
        ]);
    }

    public function getModules()
    {
        $modules = collect($this->role?->getModules() ?? []);

        if ($this->isStaff() && ! $modules->contains('dashboard')) {
            $modules->prepend('dashboard');
        }

        return $modules->values()->toArray();
    }

    public function getModulesWithInfo()
    {
        $modules = collect($this->role?->getModulesWithInfo() ?? []);
        if ($this->isStaff()) {

            $dashboard = Module::query()
                ->where('slug', 'dashboard')
                ->where('is_active', true)
                ->first();

            if ($dashboard && ! $modules->contains(fn($m) => $m['slug'] === 'dashboard')) {
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

        return $modules->sortBy('sort_order')->values()->toArray();
    }

    /**
     * Check if user has a specific permission for a module.
     */
    public function hasPermission(string $moduleSlug, string $permissionSlug): bool
    {
        if (! $this->role) {
            return false;
        }

        return RoleModulePermission::where('role_id', $this->role->id)
            ->whereHas('module', fn($q) => $q->where('slug', $moduleSlug)->where('is_active', true))
            ->whereHas('permission', fn($q) => $q->where('slug', $permissionSlug))
            ->exists();
    }

    /**
     * Return a permission map for provided modules and typical actions.
     * Example: ['equipment' => ['view' => true, 'create' => false, ...], ...]
     */
    public function getPermissionMap(array $moduleSlugs = []): array
    {
        $actions = ['view', 'create', 'edit', 'delete'];

        // Create cache key suffix for a specific module list
        $mapKeySuffix = '';
        if (! empty($moduleSlugs)) {
            $mapKeySuffix = ':' . md5(implode(',', $moduleSlugs));
        }

        $cacheKey = "user_permissions:{$this->id}{$mapKeySuffix}";
        // Asegurar que TTL es numérico — puede venir como string desde .env
        $ttl = (int) config('dashboard.cache_ttl_minutes', 60);

        $self = $this;
        return Cache::remember($cacheKey, now()->addMinutes($ttl), function () use ($self, $moduleSlugs, $actions) {
            $map = [];

            if (empty($moduleSlugs) && $self->role) {
                // derive from modules assigned to role
                $moduleSlugs = Module::where('is_active', true)->pluck('slug')->toArray();
            }

            foreach ($moduleSlugs as $slug) {
                $map[$slug] = [];
                foreach ($actions as $act) {
                    $map[$slug][$act] = $self->hasPermission($slug, $act);
                }
            }

            // Compute dynamic dashboard visibility: if dashboard not explicitly enabled,
            // enable it when the user has 'view' on a set of relevant modules.
            $dashboardKey = 'dashboard';
            if (! array_key_exists($dashboardKey, $map)) {
                $relevant = config('dashboard.relevant_modules', ['reports','reservations','equipment','users','categories']);
                $dashboardView = false;
                foreach ($relevant as $r) {
                    if (isset($map[$r]) && ($map[$r]['view'] ?? false)) {
                        $dashboardView = true;
                        break;
                    }
                }

                $map[$dashboardKey] = [
                    'view' => $dashboardView,
                    'create' => false,
                    'edit' => false,
                    'delete' => false,
                ];
            } else {
                // ensure dashboard has basic keys
                foreach (['view','create','edit','delete'] as $a) {
                    $map[$dashboardKey][$a] = $map[$dashboardKey][$a] ?? false;
                }
            }

            return $map;
        });
    }

    /**
     * Limpiar la caché del mapa de permisos para este usuario.
     * Clear cached permission map for this user.
     */
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

