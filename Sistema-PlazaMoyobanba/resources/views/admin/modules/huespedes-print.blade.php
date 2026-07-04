<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Huéspedes</title>
    <style>
        body { font-family: Arial, sans-serif; color: #0f172a; margin: 24px; }
        h1 { margin: 0 0 8px; font-size: 24px; }
        .meta { font-size: 12px; margin-bottom: 16px; color: #475569; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; }
        .status { font-weight: bold; }
        @media print {
            body { margin: 10mm; }
        }
    </style>
</head>
<body>
    <h1>Registro de Huéspedes</h1>
    <div class="meta">
        Generado: {{ now()->format('d/m/Y H:i') }}
        @if(!empty($filters['q'])) | Búsqueda: "{{ $filters['q'] }}" @endif
        @if(!empty($filters['status'])) | Estado: {{ $filters['status'] === 'hospedado' ? 'Hospedado' : 'Salió' }} @endif
        @if(!empty($filters['date_from'])) | Desde: {{ \Carbon\Carbon::parse($filters['date_from'])->format('d/m/Y') }} @endif
        @if(!empty($filters['date_to'])) | Hasta: {{ \Carbon\Carbon::parse($filters['date_to'])->format('d/m/Y') }} @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Huésped</th>
                <th>Documento</th>
                <th>Nacionalidad</th>
                <th>Habitación</th>
                <th>Ingreso</th>
                <th>Salida</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($registers as $reg)
            <tr>
                <td>{{ $reg->code }}</td>
                <td>{{ $reg->full_name }}</td>
                <td>{{ $reg->document_type }} {{ $reg->document_number }}</td>
                <td>{{ $reg->nationality }}</td>
                <td>Hab. {{ $reg->room->room_number ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($reg->check_in_at)->format('d/m/Y H:i') }}</td>
                <td>{{ $reg->check_out_at ? \Carbon\Carbon::parse($reg->check_out_at)->format('d/m/Y H:i') : '-' }}</td>
                <td class="status">{{ $reg->status === 'hospedado' ? 'Hospedado' : 'Salió' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Sin registros para los filtros seleccionados.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function () { window.print(); };
    </script>
</body>
</html>
