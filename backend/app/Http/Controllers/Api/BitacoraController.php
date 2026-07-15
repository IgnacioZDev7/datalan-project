<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\Activitylog\Models\Activity;

class BitacoraController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Activity::query()->with('causer');

        if ($usuarioId = $request->query('usuario_id')) {
            $query->where('causer_id', $usuarioId);
        }

        if ($desde = $request->query('desde')) {
            $query->whereDate('created_at', '>=', $desde);
        }

        if ($hasta = $request->query('hasta')) {
            $query->whereDate('created_at', '<=', $hasta);
        }

        if ($buscar = $request->query('buscar')) {
            $query->where('description', 'like', "%{$buscar}%");
        }

        $perPage = (int) $request->query('per_page', 20);

        return \App\Http\Resources\BitacoraResource::collection(
            $query->orderByDesc('created_at')->paginate($perPage)
        );
    }
}
