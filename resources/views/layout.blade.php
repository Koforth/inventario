<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema de Inventario')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --topbar-height: 72px;
        }

        body {
            background: #f5f7fb;
            overflow: hidden;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            flex: 0 0 260px;
            background: #111827;
            color: #e5e7eb;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
        }

        .main-panel {
            min-width: 0;
            flex: 1 1 auto;
            height: 100vh;
            display: flex;
            flex-direction: column;
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
            min-height: var(--topbar-height);
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
            overflow-y: auto;
            flex: 1 1 auto;
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
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 1030;
                width: min(80vw, 320px);
                flex: 0 0 auto;
                transform: translateX(-100%);
                transition: transform .25s ease;
                box-shadow: 0 0 0 rgba(0, 0, 0, 0);
            }

            .sidebar.open {
                transform: translateX(0);
                box-shadow: 0 8px 28px rgba(0, 0, 0, .35);
            }

            .sidebar-backdrop {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, .35);
                z-index: 1025;
                display: none;
            }

            .sidebar-backdrop.show {
                display: block;
            }

            .topbar {
                height: auto;
                min-height: var(--topbar-height);
                align-items: flex-start !important;
                gap: 1rem;
                padding-top: 1rem;
                padding-bottom: 1rem;
            }

            .sidebar-toggle {
                display: inline-flex !important;
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

            .topbar .d-flex.align-items-center.gap-2 {
                width: 100%;
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

        @media (min-width: 992px) {
            .sidebar-toggle,
            .sidebar-backdrop {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="app-shell d-flex min-vh-100">
        <aside class="sidebar p-4" id="sidebar">
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
                    @can('products.create')
                        <a href="{{ route('products.create') }}" class="nav-link {{ request()->routeIs('products.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-square"></i>
                            <span>Nuevo producto</span>
                        </a>
                    @endcan

                    @can('warehouse.manage')
                        <a href="{{ route('warehouse.index') }}" class="nav-link {{ request()->routeIs('warehouse.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam"></i>
                            <span>Almacen</span>
                        </a>
                    @endcan

                    @can('users.manage')
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span>Usuarios</span>
                        </a>
                    @endcan

                    @can('reports.manage')
                        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart"></i>
                            <span>Reportes</span>
                        </a>
                    @endcan

                    @can('customers.manage')
                        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                            <i class="bi bi-person-vcard"></i>
                            <span>Clientes</span>
                        </a>
                    @endcan
                    @can('inventory.entries.manage')
                        <a href="{{ route('inventory.entries.index') }}" class="nav-link {{ request()->routeIs('inventory.entries.*') ? 'active' : '' }}">
                            <i class="bi bi-box-arrow-in-down"></i>
                            <span>Entradas</span>
                        </a>
                    @endcan
                    @can('sales.manage')
                        <a href="{{ route('inventory.sales.index') }}" class="nav-link {{ request()->routeIs('inventory.sales.*') ? 'active' : '' }}">
                            <i class="bi bi-cart-check"></i>
                            <span>Ventas</span>
                        </a>
                    @endcan
                    @can('kardex.manage')
                        <a href="{{ route('inventory.kardex.index') }}" class="nav-link {{ request()->routeIs('inventory.kardex.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-text"></i>
                            <span>Kardex</span>
                        </a>
                    @endcan
                    @can('layaways.manage')
                        <a href="{{ route('inventory.layaways.index') }}" class="nav-link {{ request()->routeIs('inventory.layaways.*') || request()->routeIs('inventory.credit-sales.*') ? 'active' : '' }}">
                            <i class="bi bi-bookmark-check"></i>
                            <span>Apartados</span>
                        </a>
                    @endcan
                    @can('technicians.manage')
                        <a href="{{ route('inventory.technicians.index') }}" class="nav-link {{ request()->routeIs('inventory.technicians.*') ? 'active' : '' }}">
                            <i class="bi bi-person-gear"></i>
                            <span>Tecnicos</span>
                        </a>
                    @endcan
                    @can('workshop.manage')
                        <a href="{{ route('inventory.workshop.index') }}" class="nav-link {{ request()->routeIs('inventory.workshop.*') ? 'active' : '' }}">
                            <i class="bi bi-tools"></i>
                            <span>Taller</span>
                        </a>
                    @endcan
                    @can('receipts.manage')
                        <a href="{{ route('inventory.receipts.index') }}" class="nav-link {{ request()->routeIs('inventory.receipts.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i>
                            <span>Comprobantes</span>
                        </a>
                    @endcan

                    @can('cash.manage')
                        <a href="{{ route('cash.index') }}" class="nav-link {{ request()->routeIs('cash.*') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i>
                            <span>Caja</span>
                        </a>
                    @endcan

                    @can('quotes.manage')
                        <a href="{{ route('quotes.index') }}" class="nav-link {{ request()->routeIs('quotes.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Cotizaciones</span>
                        </a>
                    @endcan

                    @can('purchases.manage')
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
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary btn-sm sidebar-toggle d-none" id="sidebarToggle" type="button" aria-label="Abrir menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="topbar-title">
                        <div class="fw-bold">@yield('page-title', 'Catalogo de productos')</div>
                        <div class="text-muted small">@yield('page-subtitle', 'Control de stock para productos del hogar')</div>
                    </div>
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
                            } elseif (request()->routeIs('inventory.kardex.*')) {
                                $breadcrumbs[] = ['label' => 'Inventario', 'url' => null];
                                $breadcrumbs[] = ['label' => 'Kardex', 'url' => null];
                            } elseif (request()->routeIs('inventory.layaways.*') || request()->routeIs('inventory.credit-sales.*')) {
                                $breadcrumbs[] = ['label' => 'Inventario', 'url' => null];
                                $breadcrumbs[] = ['label' => 'Apartados', 'url' => null];
                            } elseif (request()->routeIs('inventory.technicians.*')) {
                                $breadcrumbs[] = ['label' => 'Inventario', 'url' => null];
                                $breadcrumbs[] = ['label' => 'Tecnicos', 'url' => null];
                            } elseif (request()->routeIs('inventory.workshop.*')) {
                                $breadcrumbs[] = ['label' => 'Inventario', 'url' => null];
                                $breadcrumbs[] = ['label' => 'Taller', 'url' => null];
                            } elseif (request()->routeIs('inventory.receipts.*')) {
                                $breadcrumbs[] = ['label' => 'Inventario', 'url' => null];
                                $breadcrumbs[] = ['label' => 'Comprobantes', 'url' => null];
                            } elseif (request()->routeIs('customers.*')) {
                                $breadcrumbs[] = ['label' => 'Inventario', 'url' => null];
                                $breadcrumbs[] = ['label' => 'Clientes', 'url' => null];
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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');
            const mobileMedia = window.matchMedia('(max-width: 991.98px)');

            const closeSidebar = () => {
                if (!mobileMedia.matches) return;
                sidebar.classList.remove('open');
                backdrop.classList.remove('show');
            };

            const openSidebar = () => {
                if (!mobileMedia.matches) return;
                sidebar.classList.add('open');
                backdrop.classList.add('show');
            };

            toggle?.addEventListener('click', () => {
                if (sidebar.classList.contains('open')) {
                    closeSidebar();
                    return;
                }
                openSidebar();
            });

            backdrop?.addEventListener('click', closeSidebar);
            sidebar?.querySelectorAll('a.nav-link').forEach((link) => link.addEventListener('click', closeSidebar));

            window.addEventListener('resize', () => {
                if (!mobileMedia.matches) {
                    sidebar.classList.remove('open');
                    backdrop.classList.remove('show');
                }
            });

            document.querySelectorAll('.js-collapse-toggle[data-bs-target]').forEach((button) => {
                const target = document.querySelector(button.getAttribute('data-bs-target'));
                const icon = button.querySelector('i.fas');

                if (!target || !icon) {
                    return;
                }

                const syncIcon = (expanded) => {
                    icon.classList.toggle('fa-plus', !expanded);
                    icon.classList.toggle('fa-minus', expanded);
                };

                syncIcon(target.classList.contains('show'));
                target.addEventListener('shown.bs.collapse', () => syncIcon(true));
                target.addEventListener('hidden.bs.collapse', () => syncIcon(false));
            });
        });
    </script>
</body>
</html>
