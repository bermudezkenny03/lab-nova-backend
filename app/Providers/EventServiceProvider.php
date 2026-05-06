<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\PermissionsUpdated;
use App\Listeners\InvalidatePermissionCache;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     * Mapeo de eventos a listeners (comentado en español)
     * @var array
     */
    protected $listen = [
        PermissionsUpdated::class => [
            InvalidatePermissionCache::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
