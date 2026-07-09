<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RolResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolController extends Controller
{
    /**
     * Lista de roles con sus permisos (para poblar selects en el frontend).
     */
    public function index(): AnonymousResourceCollection
    {
        return RolResource::collection(Role::with('permissions')->orderBy('name')->get());
    }

    /**
     * Catálogo de permisos disponibles (nombres).
     */
    public function permisos(): array
    {
        return ['data' => Permission::orderBy('name')->pluck('name')];
    }
}
