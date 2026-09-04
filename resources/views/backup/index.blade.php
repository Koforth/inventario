@extends('layout')

@section('title', 'Backup y Restauracion | SmartZone')
@section('page-title', 'Backup y Restauracion')
@section('page-subtitle', 'Respaldo y restauracion de la base de datos')

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Backups disponibles</div>
                <div class="display-6 fw-bold">{{ $storage['count'] }}</div>
                <div class="text-muted small">Archivos .sql en el servidor</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Tamano total</div>
                <div class="display-6 fw-bold">{{ $storage['size'] }}</div>
                <div class="text-muted small">Almacenamiento ocupado</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4 h-100">
                <div class="text-muted fw-semibold small text-uppercase">Tablas</div>
                <div class="display-6 fw-bold">{{ count($tables) }}</div>
                <div class="text-muted small">Tablas en la base de datos</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4">
                    <h2 class="h5 mb-0">Crear nuevo backup</h2>
                    <div class="text-muted small">Genera una copia de seguridad completa de la base de datos.</div>
                </div>
                <div class="p-3 p-md-4">
                    <form method="POST" action="{{ route('backup.create') }}">
                        @csrf
                        <p class="small text-muted">
                            Se generara un archivo <code>.sql</code> con todas las tablas, datos, procedimientos y triggers de la base de datos <strong>{{ config('database.connections.' . config('database.default') . '.database') }}</strong>.
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Ultima generacion: {{ !empty($backups[0]) ? $backups[0]['modified']->format('d/m/Y h:i A') : 'Nunca' }}</span>
                            <button class="btn btn-primary">
                                <i class="bi bi-database-down"></i> Generar backup
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="table-card h-100">
                <div class="border-bottom p-3 p-md-4">
                    <h2 class="h5 mb-0">Restaurar desde archivo</h2>
                    <div class="text-muted small">Sube un archivo .sql para restaurar la base de datos.</div>
                </div>
                <div class="p-3 p-md-4">
                    <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label fw-semibold">Archivo SQL</label>
                            <input type="file" id="file" name="file" accept=".sql,.txt" class="form-control" required>
                            <div class="form-text">Maximo 50 MB. Esta operacion sustituye el contenido actual de la base de datos.</div>
                        </div>
                        <div class="alert alert-warning small mb-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Advertencia:</strong> restaurar reemplazara todos los datos actuales. Se recomienda generar un backup antes.
                        </div>
                        <button class="btn btn-warning w-100" onclick="return confirm('¿Seguro que deseas restaurar la base de datos? Se perderan los datos actuales.');">
                            <i class="bi bi-arrow-counterclockwise"></i> Restaurar desde archivo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="border-bottom p-3 p-md-4">
            <h2 class="h5 mb-1">Backups guardados</h2>
            <div class="text-muted small">Listado de copias de seguridad generadas en el servidor.</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Archivo</th>
                        <th class="text-end">Tamano</th>
                        <th>Fecha de generacion</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backups as $backup)
                        <tr>
                            <td class="fw-semibold">
                                <i class="bi bi-file-earmark-zip text-primary me-2"></i>
                                {{ $backup['filename'] }}
                            </td>
                            <td class="text-end">{{ $backup['size'] }}</td>
                            <td>{{ $backup['modified']->format('d/m/Y h:i A') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('backup.download', $backup['filename']) }}" class="btn btn-sm btn-outline-primary" title="Descargar">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-warning"
                                        title="Restaurar"
                                        data-bs-toggle="modal"
                                        data-bs-target="#restoreModal-{{ $loop->index }}"
                                    >
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                    <form method="POST" action="{{ route('backup.destroy', $backup['filename']) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar este backup?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="restoreModal-{{ $loop->index }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('backup.restore-file', $backup['filename']) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Restaurar backup</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Seguro que deseas restaurar desde <strong>{{ $backup['filename'] }}</strong>?</p>
                                            <div class="alert alert-danger small mb-0">
                                                Esta accion reemplazara <strong>todos</strong> los datos actuales de la base de datos.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restaurar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="py-5 text-center text-muted">
                                No hay backups guardados. Genera el primero en la seccion superior.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
