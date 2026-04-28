@extends('layout')

@section('title', 'Productos | Sistema de Inventario')
@section('page-title', 'Catalogo de productos')
@section('page-subtitle', 'Consulta de existencias, stock minimo, marca y categoria')

@section('content')
    @php
        $userCanManageProducts = auth()->user()->can('manage-products');
        $userCanUseCarousel = ! $userCanManageProducts || auth()->user()->hasRole('empleado');
    @endphp

    @can('view-reports')
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="metric-card p-4">
                    <div class="text-muted fw-semibold small text-uppercase">Productos registrados</div>
                    <div class="display-6 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card p-4">
                    <div class="text-muted fw-semibold small text-uppercase">Bajo stock</div>
                    <div class="display-6 fw-bold text-warning">{{ $stats['low_stock'] }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="metric-card p-4">
                    <div class="text-muted fw-semibold small text-uppercase">Valor del inventario</div>
                    <div class="display-6 fw-bold">C$ {{ number_format((float) $stats['inventory_value'], 2) }}</div>
                </div>
            </div>
        </div>
    @endcan

    <div class="table-card">
        <div class="border-bottom p-3 p-md-4">
            <form method="GET" action="{{ route('products.index') }}" class="row g-3 align-items-end">
                <div class="col-md-7">
                    <label for="search" class="form-label fw-semibold">Buscar producto</label>
                    <input
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="SKU, codigo de barras, nombre, marca, categoria o proveedor"
                    >
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label fw-semibold">Estado de stock</label>
                    <select id="status" name="status" class="form-select">
                        <option value="all" @selected($status === 'all')>Todos</option>
                        <option value="low" @selected($status === 'low')>Bajo stock</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">Buscar</button>
                </div>
            </form>
        </div>

        <div class="p-3 p-md-4">
            <div class="row g-3">
                @forelse ($products as $product)
                    @php
                        $coverImage = $product->images->first();
                    @endphp
                    <div class="col-sm-6 col-lg-4 col-xl-3">
                        <article class="card h-100 border-0 shadow-sm">
                            @if ($coverImage)
                                <img
                                    src="{{ asset('storage/' . $coverImage->path) }}"
                                    alt="{{ $product->nombre }}"
                                    class="card-img-top"
                                    style="aspect-ratio: 1 / 1; object-fit: cover;"
                                >
                            @else
                                <div
                                    class="bg-light border-bottom d-flex align-items-center justify-content-center text-muted fw-semibold"
                                    style="aspect-ratio: 1 / 1;"
                                >
                                    JPG
                                </div>
                            @endif

                            <div class="card-body d-flex flex-column gap-3">
                                @if (! $userCanUseCarousel)
                                    <a href="{{ route('products.edit', $product) }}" class="h6 mb-0 fw-bold text-decoration-none text-dark text-truncate">
                                        {{ $product->nombre }}
                                    </a>
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary btn-sm mt-auto">
                                        Editar
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        class="btn btn-link h6 mb-0 p-0 fw-bold text-decoration-none text-dark text-start text-truncate"
                                        data-bs-toggle="modal"
                                        data-bs-target="#productsCarouselModal"
                                        data-product-index="{{ $loop->index }}"
                                    >
                                        {{ $product->nombre }}
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-sm mt-auto"
                                        data-bs-toggle="modal"
                                        data-bs-target="#productsCarouselModal"
                                        data-product-index="{{ $loop->index }}"
                                    >
                                        Ver
                                    </button>
                                @endif
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="py-5 text-center text-muted">
                            No hay productos para mostrar.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="border-top px-3 px-md-4 py-3">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>

    @if ($userCanUseCarousel && $products->count() > 0)
        <style>
            #productsCarousel > .carousel-inner > .carousel-item {
                transition: none;
            }

            #productsCarouselModal .modal-dialog {
                width: min(1140px, calc(100vw - 2rem));
            }

            #productsCarouselModal .modal-content {
                max-height: calc(100vh - 2rem);
            }

            #productsCarouselModal .carousel-product-layout {
                height: min(680px, calc(100vh - 2rem));
                min-height: 520px;
            }

            #productsCarouselModal .product-media-panel {
                height: 100%;
                min-height: 520px;
                overflow: hidden;
            }

            #productsCarouselModal .product-image-carousel,
            #productsCarouselModal .product-image-carousel .carousel-inner,
            #productsCarouselModal .product-image-carousel .carousel-item {
                height: 100%;
            }

            #productsCarouselModal .product-image {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            #productsCarouselModal .product-empty-image {
                height: 100%;
            }

            .product-image-carousel {
                position: relative;
            }

            .product-image-carousel .product-image-control {
                top: 50%;
                bottom: auto;
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: #ffffff;
                box-shadow: 0 .5rem 1rem rgba(17, 24, 39, .18);
                opacity: 1;
                transform: translateY(-50%);
            }

            .product-image-carousel .carousel-control-prev.product-image-control {
                left: 1rem;
            }

            .product-image-carousel .carousel-control-next.product-image-control {
                right: 1rem;
            }

            .product-image-carousel .carousel-control-prev-icon,
            .product-image-carousel .carousel-control-next-icon {
                width: 1.25rem;
                height: 1.25rem;
                filter: invert(20%) sepia(95%) saturate(2630%) hue-rotate(335deg) brightness(92%) contrast(95%);
            }

            .product-image-carousel .carousel-indicators {
                right: auto;
                bottom: 1rem;
                left: 50%;
                width: auto;
                margin: 0;
                padding: .35rem .5rem;
                border-radius: 999px;
                background: rgba(255, 255, 255, .88);
                transform: translateX(-50%);
            }

            .product-image-carousel .carousel-indicators [data-bs-target] {
                width: .5rem;
                height: .5rem;
                border: 1px solid #cbd5e1;
                border-radius: 50%;
                background: transparent;
                opacity: 1;
            }

            .product-image-carousel .carousel-indicators .active {
                background: #111827;
                border-color: #111827;
            }

            #productsCarousel .product-image-carousel .carousel-control-prev-icon,
            #productsCarousel .product-image-carousel .carousel-control-next-icon {
                filter: invert(20%) sepia(95%) saturate(2630%) hue-rotate(335deg) brightness(92%) contrast(95%);
            }

            @media (max-width: 991.98px) {
                #productsCarouselModal .modal-dialog {
                    width: calc(100vw - 1rem);
                    margin: .5rem auto;
                }

                #productsCarouselModal .modal-content {
                    max-height: calc(100vh - 1rem);
                    overflow-y: auto;
                }

                #productsCarouselModal .carousel-product-layout,
                #productsCarouselModal .product-media-panel,
                #productsCarouselModal .product-image,
                #productsCarouselModal .product-empty-image {
                    height: auto;
                    min-height: 0;
                    max-height: none;
                }

                #productsCarouselModal .product-media-panel {
                    aspect-ratio: 4 / 3;
                }

                #productsCarouselModal .product-image-carousel,
                #productsCarouselModal .product-image-carousel .carousel-inner,
                #productsCarouselModal .product-image-carousel .carousel-item {
                    height: 100%;
                }

                #productsCarouselModal .product-image,
                #productsCarouselModal .product-empty-image {
                    height: 100%;
                }
            }

            @media (max-width: 575.98px) {
                #productsCarouselModal .modal-dialog {
                    width: 100vw;
                    min-height: 100vh;
                    margin: 0;
                }

                #productsCarouselModal .modal-content {
                    min-height: 100vh;
                    max-height: none;
                }

                #productsCarouselModal .product-media-panel {
                    aspect-ratio: 1 / 1;
                }

                .product-image-carousel .product-image-control {
                    width: 40px;
                    height: 40px;
                }

                .product-image-carousel .carousel-control-prev.product-image-control {
                    left: .75rem;
                }

                .product-image-carousel .carousel-control-next.product-image-control {
                    right: .75rem;
                }
            }
        </style>

        <div class="modal fade" id="productsCarouselModal" tabindex="-1" aria-label="Detalle de producto" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content border-0 rounded-0 overflow-hidden">
                    <div class="modal-body p-0">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-4 z-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>

                        <div id="productsCarousel" class="carousel slide" data-bs-interval="false" data-bs-touch="false">
                            <div class="carousel-inner">
                                @foreach ($products as $product)
                                    @php
                                        $isLowStock = $product->stock <= $product->stock_minimo;
                                        $productImages = $product->images;
                                    @endphp
                                    <div class="carousel-item @if ($loop->first) active @endif">
                                        <div class="row g-0 carousel-product-layout">
                                            <div class="col-lg-7 bg-light product-media-panel">
                                                @if ($productImages->isNotEmpty())
                                                    <div id="productImagesCarousel{{ $product->id }}" class="carousel slide h-100 product-image-carousel" data-bs-interval="false" data-bs-touch="true">
                                                        <div class="carousel-inner h-100">
                                                            @foreach ($productImages as $image)
                                                                <div class="carousel-item h-100 @if ($loop->first) active @endif">
                                                                    <img
                                                                        src="{{ asset('storage/' . $image->path) }}"
                                                                        alt="{{ $product->nombre }}"
                                                                        class="product-image"
                                                                    >
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        @if ($productImages->count() > 1)
                                                            <div class="carousel-indicators">
                                                                @foreach ($productImages as $image)
                                                                    <button
                                                                        type="button"
                                                                        data-bs-target="#productImagesCarousel{{ $product->id }}"
                                                                        data-bs-slide-to="{{ $loop->index }}"
                                                                        class="@if ($loop->first) active @endif"
                                                                        @if ($loop->first) aria-current="true" @endif
                                                                        aria-label="Imagen {{ $loop->iteration }}"
                                                                    ></button>
                                                                @endforeach
                                                            </div>
                                                            <button class="carousel-control-prev product-image-control" type="button" data-bs-target="#productImagesCarousel{{ $product->id }}" data-bs-slide="prev">
                                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                                <span class="visually-hidden">Imagen anterior</span>
                                                            </button>
                                                            <button class="carousel-control-next product-image-control" type="button" data-bs-target="#productImagesCarousel{{ $product->id }}" data-bs-slide="next">
                                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                                <span class="visually-hidden">Imagen siguiente</span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="product-empty-image d-flex align-items-center justify-content-center text-muted fw-semibold">
                                                        Sin imagen
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="col-lg-5">
                                                <div class="p-4 p-md-5 h-100 d-flex flex-column">
                                                    <div class="mb-4 pe-5">
                                                        <div class="text-muted small fw-semibold text-uppercase">Producto</div>
                                                        <h2 class="h3 fw-bold mb-2">{{ $product->nombre }}</h2>
                                                        <span class="badge {{ $isLowStock ? 'text-bg-warning' : 'text-bg-success' }}">
                                                            {{ $isLowStock ? 'Bajo stock' : 'Disponible' }}
                                                        </span>
                                                    </div>

                                                    <div class="row g-3 mb-4">
                                                        <div class="col-6">
                                                            <div class="text-muted small fw-semibold text-uppercase">SKU</div>
                                                            <div class="fw-semibold">{{ $product->sku }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted small fw-semibold text-uppercase">Precio</div>
                                                            <div class="fw-bold">C$ {{ number_format((float) $product->sale_price_1, 2) }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted small fw-semibold text-uppercase">Stock</div>
                                                            <div class="fw-semibold">{{ $product->stock }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted small fw-semibold text-uppercase">Minimo</div>
                                                            <div class="fw-semibold">{{ $product->stock_minimo }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted small fw-semibold text-uppercase">Marca</div>
                                                            <div>{{ $product->brand?->nombre ?? 'Sin marca' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted small fw-semibold text-uppercase">Categoria</div>
                                                            <div>{{ $product->category?->nombre ?? 'Sin categoria' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted small fw-semibold text-uppercase">Presentacion</div>
                                                            <div>{{ $product->presentation?->nombre ?? 'Sin presentacion' }}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted small fw-semibold text-uppercase">Compra</div>
                                                            <div>C$ {{ number_format((float) $product->purchase_price, 2) }}</div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="text-muted small fw-semibold text-uppercase">Codigo de barras</div>
                                                            <div>{{ $product->barcode ?: 'Sin codigo' }}</div>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="text-muted small fw-semibold text-uppercase">Proveedores</div>
                                                            <div>{{ $product->supplierNames() }}</div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-4">
                                                        <div class="text-muted small fw-semibold text-uppercase">Descripcion</div>
                                                        <p class="mb-0">{{ $product->descripcion ?: 'Sin descripcion registrada.' }}</p>
                                                    </div>

                                                    <div class="d-flex flex-wrap gap-2 mb-4">
                                                        <span class="badge {{ $product->taxable ? 'text-bg-info' : 'text-bg-secondary' }}">
                                                            {{ $product->taxable ? 'Impuesto' : 'Sin impuesto' }}
                                                        </span>
                                                        <span class="badge {{ $product->perishable ? 'text-bg-warning' : 'text-bg-secondary' }}">
                                                            {{ $product->perishable ? 'Perecedero' : 'No perecedero' }}
                                                        </span>
                                                        <span class="badge {{ $product->inventoryable ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                            {{ $product->inventoryable ? 'Inventariable' : 'No inventariable' }}
                                                        </span>
                                                    </div>

                                                    <div class="d-grid d-sm-flex gap-2 mt-auto">
                                                        @can('manage-products')
                                                            <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Editar</a>
                                                        @endcan
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById('productsCarouselModal');
                const carousel = document.getElementById('productsCarousel');

                modal.addEventListener('show.bs.modal', (event) => {
                    const index = Number(event.relatedTarget?.getAttribute('data-product-index') || 0);
                    const items = carousel.querySelectorAll(':scope > .carousel-inner > .carousel-item');

                    items.forEach((item, itemIndex) => {
                        item.classList.toggle('active', itemIndex === index);
                    });
                });

                const updateImageControls = (imageCarousel) => {
                    const items = [...imageCarousel.querySelectorAll('.carousel-inner > .carousel-item')];
                    const activeIndex = items.findIndex((item) => item.classList.contains('active'));
                    const previous = imageCarousel.querySelector('.carousel-control-prev.product-image-control');
                    const next = imageCarousel.querySelector('.carousel-control-next.product-image-control');

                    if (previous) {
                        previous.classList.toggle('d-none', activeIndex <= 0);
                    }

                    if (next) {
                        next.classList.toggle('d-none', activeIndex === items.length - 1);
                    }
                };

                const imageCarousels = [...modal.querySelectorAll('.product-image-carousel')];

                imageCarousels.forEach((imageCarousel) => {
                    updateImageControls(imageCarousel);

                    imageCarousel.addEventListener('slid.bs.carousel', () => {
                        updateImageControls(imageCarousel);
                    });
                });

                modal.addEventListener('hidden.bs.modal', () => {
                    carousel.querySelectorAll('.carousel.slide').forEach((nestedCarousel) => {
                        bootstrap.Carousel.getOrCreateInstance(nestedCarousel, { interval: false }).to(0);
                        updateImageControls(nestedCarousel);
                    });
                });

                modal.querySelectorAll('.product-image-control, .product-image-carousel .carousel-indicators button').forEach((control) => {
                    control.addEventListener('click', (event) => {
                        event.stopPropagation();
                    });
                });
            });
        </script>
    @endif
@endsection
