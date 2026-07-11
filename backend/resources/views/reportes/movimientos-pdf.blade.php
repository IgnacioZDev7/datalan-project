<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Movimientos</title>
<style>body{font-family: sans-serif;font-size: 11px;}h1{text-align:center;margin-bottom:5px;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:4px 6px;text-align:left;}th{background:#f0f0f0;}.logo{text-align:center;margin-bottom:10px;}.logo img{height:40px;}.anulado{color:#999;text-decoration:line-through;}</style></head>
<body>
<div class="logo"><img src="{{ public_path('images/logo-datalan.png') }}" alt="DATALAN"></div>
<h1>Reporte de Movimientos</h1>
<table><thead><tr><th>Código</th><th>Tipo</th><th>Fecha</th><th>Origen</th><th>Destino</th><th>Documento</th></tr></thead>
<tbody>@foreach($movimientos as $m)
<tr class="{{ $m->anulado ? 'anulado' : '' }}"><td>{{ $m->codigo }}</td><td>{{ $m->tipo }}</td><td>{{ $m->fecha }}</td><td>{{ $m->almacen_origen_id ?? '-' }}</td><td>{{ $m->almacen_destino_id ?? '-' }}</td><td>{{ $m->documento_referencia ?? '-' }}</td></tr>
@endforeach</tbody></table>
<p style="text-align:right;margin-top:10px;font-size:10px;color:#666;">Generado: {{ now()->format('d/m/Y H:i') }}</p>
</body></html>
