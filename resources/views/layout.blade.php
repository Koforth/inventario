<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema de Inventario')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fb;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            flex: 0 0 260px;
            background: #111827;
            color: #e5e7eb;
        }

        .main-panel {
            min-width: 0;
            flex: 1 1 auto;
        }

        .sidebar .nav-link {
            color: #cbd5e1;
            border-radius: .5rem;
            font-weight: 600;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #1f2937;
            color: #ffffff;
        }

        .topbar {
            min-height: 72px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .topbar-title {
            min-width: 0;
        }

        .topbar-title .fw-bold,
        .topbar-title .small {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-block {
            flex: 0 0 auto;
            max-width: 360px;
        }

        .user-block form {
            display: inline-block;
            margin: 0 !important;
        }

        .user-actions {
            min-width: 0;
        }

        .content-area {
            padding: 2rem;
        }

        .content-container {
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
        }

        .metric-card {
            border: 1px solid #e5e7eb;
            border-radius: .75rem;
            background: #ffffff;
        }

        .table-card {
            border: 1px solid #e5e7eb;
            border-radius: .75rem;
            background: #ffffff;
            overflow: hidden;
        }

        @media (max-width: 991.98px) {
            .app-shell {
                flex-direction: column;
            }

            .sidebar {
                position: sticky;
                top: 0;
                z-index: 1030;
                width: 100%;
                min-height: auto;
                flex: 0 0 auto;
            }

            .sidebar .brand-block {
                margin-bottom: 1rem !important;
            }

            .sidebar .nav {
                flex-direction: row !important;
                gap: .5rem !important;
                overflow-x: auto;
                padding-bottom: .25rem;
                white-space: nowrap;
            }

            .topbar {
                height: auto;
                min-height: 72px;
                align-items: flex-start !important;
                gap: 1rem;
                padding-top: 1rem;
                padding-bottom: 1rem;
            }

            .content-area {
                padding: 1rem;
            }

            .content-container {
                max-width: 100%;
            }
        }

        @media (max-width: 575.98px) {
            .sidebar {
                padding: 1rem !important;
            }

            .topbar {
                flex-direction: column;
            }

            .topbar .user-block {
                width: 100%;
                max-width: none;
                text-align: left !important;
                flex-direction: column;
                align-items: stretch !important;
            }

            .topbar .user-block form {
                margin-top: .5rem !important;
            }

            .topbar .user-block .btn {
                width: 100%;
            }

            .content-area {
                padding: .75rem;
            }
        }

        @media (min-width: 576px) {
            .w-sm-auto {
                width: auto !important;
            }
        }
    </style>
</head>
<body>
    <div class="app-shell d-flex min-vh-100">
        <aside class="sidebar p-4">
            <div class="brand-block mb-4">
                <div class="text-uppercase text-info fw-bold small">sistema</div>
                <h1 class="h4 mb-0 text-white">Inventario</h1>
            </div>

            <nav class="nav flex-column gap-2">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">Catalogo</a>

                @auth
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('products.create') }}" class="nav-link">Nuevo producto</a>
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">Usuarios</a>
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">Reportes</a>
                    @endif

                    @if (auth()->user()->role === 'empleado')
                        <a href="{{ route('inventory.entries.index') }}" class="nav-link {{ request()->routeIs('inventory.entries.*') ? 'active' : '' }}">Entradas</a>
                        <a href="{{ route('inventory.sales.index') }}" class="nav-link {{ request()->routeIs('inventory.sales.*') ? 'active' : '' }}">Ventas</a>
                    @endif
                @endauth
            </nav>
        </aside>

        <div class="main-panel">
            <header class="topbar d-flex align-items-center justify-content-between px-4">
                <div class="topbar-title">
                    <div class="fw-bold">@yield('page-title', 'Catalogo de productos')</div>
                    <div class="text-muted small">@yield('page-subtitle', 'Control de stock para productos del hogar')</div>
                </div>
                <div class="user-block d-flex align-items-center justify-content-end gap-3 text-end">
                    @auth
                        <div class="user-actions">
                            <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                            <div class="text-muted small text-capitalize">{{ auth()->user()->role }}</div>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-secondary btn-sm">Cerrar sesion</button>
                        </form>
                    @else
                        <div class="fw-semibold">Invitado</div>
                        <div class="text-muted small">Solo lectura</div>
                    @endauth
                </div>
            </header>

            <main class="content-area">
                <div class="container-fluid content-container px-0">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
