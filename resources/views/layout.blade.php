<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema de Inventario')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
            display: flex;
            align-items: center;
            gap: .65rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #1f2937;
            color: #ffffff;
        }

        .sidebar .nav-link i {
            width: 1.1rem;
            flex: 0 0 1.1rem;
            text-align: center;
            color: #93c5fd;
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
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i>
                    <span>Home</span>
                </a>
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index', 'products.show', 'products.edit') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i>
                    <span>Catalogo</span>
                </a>

                @auth
                    @can('create-products')
                        <a href="{{ route('products.create') }}" class="nav-link {{ request()->routeIs('products.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-square"></i>
                            <span>Nuevo producto</span>
                        </a>
                    @endcan

                    @can('manage-products')
                        <a href="{{ route('warehouse.index') }}" class="nav-link {{ request()->routeIs('warehouse.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam"></i>
                            <span>Almacen</span>
                        </a>
                    @endcan

                    @can('manage-users')
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span>Usuarios</span>
                        </a>
                    @endcan

                    @can('view-reports')
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart"></i>
                            <span>Reportes</span>
                        </a>
                    @endcan

                    @can('manage-inventory')
                        <a href="{{ route('inventory.entries.index') }}" class="nav-link {{ request()->routeIs('inventory.entries.*') ? 'active' : '' }}">
                            <i class="bi bi-box-arrow-in-down"></i>
                            <span>Entradas</span>
                        </a>
                        <a href="{{ route('inventory.sales.index') }}" class="nav-link {{ request()->routeIs('inventory.sales.*') ? 'active' : '' }}">
                            <i class="bi bi-cart-check"></i>
                            <span>Ventas</span>
                        </a>
                    @endcan

                    @can('manage-cash')
                        <a href="{{ route('cash.index') }}" class="nav-link {{ request()->routeIs('cash.*') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i>
                            <span>Caja</span>
                        </a>
                    @endcan

                    @can('manage-quotes')
                        <a href="{{ route('quotes.index') }}" class="nav-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Cotizaciones</span>
                        </a>
                    @endcan

                    @can('manage-purchases')
                        <a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.*') || request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="bi bi-bag-check"></i>
                            <span>Compras</span>
                        </a>
                    @endcan
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
                            @if (auth()->user()->primaryRoleName())
                                <div class="text-muted small">{{ auth()->user()->primaryRoleName() }}</div>
                            @endif
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
                    @php
                        $routeName = request()->route()?->getName();
                        $breadcrumbs = [
                            ['label' => 'Home', 'url' => route('home')],
                        ];

                        if ($routeName) {
                            if (request()->routeIs('warehouse.*')) {
                                $breadcrumbs[] = ['label' => 'Almacen', 'url' => route('warehouse.index')];

                                if (request()->routeIs('warehouse.categories.*')) {
                                    $breadcrumbs[] = ['label' => 'Categorias', 'url' => null];
                                } elseif (request()->routeIs('warehouse.brands.*')) {
                                    $breadcrumbs[] = ['label' => 'Marcas', 'url' => null];
                                } elseif (request()->routeIs('warehouse.presentations.*')) {
                                    $breadcrumbs[] = ['label' => 'Presentaciones', 'url' => null];
                                } elseif (request()->routeIs('warehouse.perishables')) {
                                    $breadcrumbs[] = ['label' => 'Productos perecederos', 'url' => null];
                                }
                            } elseif (request()->routeIs('products.create')) {
                                $breadcrumbs[] = ['label' => 'Nuevo producto', 'url' => null];
                            } elseif (request()->routeIs('products.*')) {
                                $breadcrumbs[] = ['label' => 'Catalogo', 'url' => route('products.index')];

                                if (request()->routeIs('products.edit')) {
                                    $breadcrumbs[] = ['label' => 'Editar producto', 'url' => null];
                                } elseif (request()->routeIs('products.show')) {
                                    $breadcrumbs[] = ['label' => 'Detalle', 'url' => null];
                                }
                            } elseif (request()->routeIs('inventory.entries.*')) {
                                $breadcrumbs[] = ['label' => 'Inventario', 'url' => null];
                                $breadcrumbs[] = ['label' => 'Entradas', 'url' => null];
                            } elseif (request()->routeIs('inventory.sales.*')) {
                                $breadcrumbs[] = ['label' => 'Inventario', 'url' => null];
                                $breadcrumbs[] = ['label' => 'Ventas', 'url' => null];
                            } elseif (request()->routeIs('cash.*')) {
                                $breadcrumbs[] = ['label' => 'Caja', 'url' => null];
                            } elseif (request()->routeIs('reports.*')) {
                                $breadcrumbs[] = ['label' => 'Reportes', 'url' => null];
                            } elseif (request()->routeIs('quotes.*')) {
                                $breadcrumbs[] = ['label' => 'Cotizaciones', 'url' => route('quotes.index')];

                                if (request()->routeIs('quotes.create')) {
                                    $breadcrumbs[] = ['label' => 'Generar', 'url' => null];
                                } elseif (request()->routeIs('quotes.show')) {
                                    $breadcrumbs[] = ['label' => 'Detalle', 'url' => null];
                                }
                            } elseif (request()->routeIs('purchases.*') || request()->routeIs('suppliers.*')) {
                                $breadcrumbs[] = ['label' => 'Compras', 'url' => route('purchases.index')];

                                if (request()->routeIs('purchases.create')) {
                                    $breadcrumbs[] = ['label' => 'Realizar compra', 'url' => null];
                                } elseif (request()->routeIs('purchases.credits')) {
                                    $breadcrumbs[] = ['label' => 'Compras al credito', 'url' => null];
                                } elseif (request()->routeIs('purchases.price-history')) {
                                    $breadcrumbs[] = ['label' => 'Historial de precios', 'url' => null];
                                } elseif (request()->routeIs('purchases.show')) {
                                    $breadcrumbs[] = ['label' => 'Detalle', 'url' => null];
                                } elseif (request()->routeIs('suppliers.*')) {
                                    $breadcrumbs[] = ['label' => 'Proveedores', 'url' => null];
                                }
                            } elseif (request()->routeIs('users.*')) {
                                $breadcrumbs[] = ['label' => 'Usuarios', 'url' => route('users.index')];

                                if (request()->routeIs('users.edit')) {
                                    $breadcrumbs[] = ['label' => 'Editar roles', 'url' => null];
                                }
                            }
                        }
                    @endphp

                    @if (count($breadcrumbs) > 1)
                        <nav aria-label="breadcrumb" class="mb-3">
                            <ol class="breadcrumb mb-0 small">
                                @foreach ($breadcrumbs as $breadcrumb)
                                    @if ($loop->last || ! $breadcrumb['url'])
                                        <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['label'] }}</li>
                                    @else
                                        <li class="breadcrumb-item">
                                            <a href="{{ $breadcrumb['url'] }}" class="text-decoration-none">{{ $breadcrumb['label'] }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            </ol>
                        </nav>
                    @endif

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
