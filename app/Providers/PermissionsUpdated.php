<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento temporal colocado en app/Providers para facilidad.
 * Idealmente estaría en app/Events, pero algunos entornos no permiten crear
 * directorios desde este asistente. Se recomienda mover a app/Events si es posible.
 */
class PermissionsUpdated
{
    use Dispatchable, SerializesModels;

    /** @var array<int> Lista de role IDs afectados */
    public array $roleIds;

    public function __construct(array $roleIds)
    {
        $this->roleIds = $roleIds;
    }
}
