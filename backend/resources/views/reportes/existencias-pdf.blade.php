<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Existencias</title>
<style>body{font-family: sans-serif;font-size: 11px;}h1{text-align:center;margin-bottom:5px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:4px 6px;text-align:left;}th{background:#f0f0f0;}.text-right{text-align:right;}.logo{text-align:center;margin-bottom:10px;}.logo img{height:40px;}</style></head>
<body>
<div class="logo"><img src="{{ public_path('images/logo-datalan.png') }}" alt="DATALAN"></div>
<h1>Reporte de Existencias</h1>
<table><thead><tr><th>Almacén</th><th>Código</th><th>Producto</th><th class="text-right">Actual</th><th class="text-right">Mínimo</th><th class="text-right">Máximo</th></tr></thead>
<tbody>@foreach($existencias as $e)
<tr><td>{{ $e->almacen?->nombre ?? '-' }}</td><td>{{ $e->producto?->codigo ?? '-' }}</td><td>{{ $e->producto?->nombre ?? '-' }}</td><td class="text-right">{{ number_format($e->cantidad_actual, 2) }}</td><td class="text-right">{{ number_format($e->cantidad_minima ?? 0, 2) }}</td><td class="text-right">{{ number_format($e->cantidad_maxima ?? 0, 2) }}</td></tr>
@endforeach</tbody></table>
<p style="text-align:right;margin-top:10px;font-size:10px;color:#666;">Generado: {{ now()->format('d/m/Y H:i') }}</p>
</body></html>
