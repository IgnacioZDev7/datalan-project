<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Kardex</title>
<style>body{font-family: sans-serif;font-size: 11px;}h1{text-align:center;margin-bottom:5px;}h2{text-align:center;margin-bottom:10px;color:#555;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ccc;padding:4px 6px;text-align:left;}th{background:#f0f0f0;}.text-right{text-align:right;}.logo{text-align:center;margin-bottom:10px;}.logo img{height:40px;}</style></head>
<body>
<div class="logo"><img src="{{ public_path('images/logo-datalan.png') }}" alt="DATALAN"></div>
<h1>Kardex</h1>
<h2>{{ $producto['codigo'] }} — {{ $producto['nombre'] }}</h2>
<table><thead><tr><th>Fecha</th><th>Movimiento</th><th>Tipo</th><th class="text-right">Entrada</th><th class="text-right">Salida</th><th class="text-right">Saldo</th></tr></thead>
<tbody>@foreach($movimientos as $l)
<tr><td>{{ $l['fecha'] }}</td><td>{{ $l['movimiento'] }}</td><td>{{ $l['tipo'] }}</td><td class="text-right">{{ $l['entrada'] ? number_format($l['entrada'], 2) : '-' }}</td><td class="text-right">{{ $l['salida'] ? number_format($l['salida'], 2) : '-' }}</td><td class="text-right">{{ number_format($l['saldo'], 2) }}</td></tr>
@endforeach</tbody></table>
<p style="text-align:right;margin-top:10px;font-size:10px;color:#666;">Saldo final: {{ number_format($saldoFinal, 2) }} — Generado: {{ now()->format('d/m/Y H:i') }}</p>
</body></html>
