/**
 * Portal del Artesano - Lógica Interactiva Vanilla JS (ES6)
 * - Búsqueda y filtrado en tiempo real en catálogo
 * - Carrito de compras reactivo (cantidades, envoltura de regalo, subtotales)
 * - Validación en cliente con cumplimiento de la Ley N.º 29733
 */

document.addEventListener('DOMContentLoaded', () => {
    // Inicialización de submódulos protegida contra excepciones aisladas
    const modules = [
        { name: 'CurrencySwitcher', fn: initCurrencySwitcher },
        { name: 'CatalogFilters', fn: initCatalogFilters },
        { name: 'CartInteractions', fn: initCartInteractions },
        { name: 'FormValidations', fn: initFormValidations },
        { name: 'QuantitySelectors', fn: initQuantitySelectors },
        { name: 'HeroSlider', fn: initHeroSlider },
        { name: 'HomeCarousel', fn: initHomeCarousel },
        { name: 'ArtisanOrderAlerts', fn: initArtisanOrderAlerts },
        { name: 'PasswordToggles', fn: initPasswordToggles },
        { name: 'MobileNavigation', fn: initMobileNavigation }
    ];

    modules.forEach(m => {
        try {
            if (typeof m.fn === 'function') m.fn();
        } catch (err) {
            console.error(`[Portal Artesano] Error al inicializar ${m.name}:`, err);
        }
    });
});

/**
 * Normaliza y resuelve URLs absolutas y relativas asegurando compatibilidad
 * con subcarpetas en Apache (ej. /artesano/public) y coincidencia de origen (mismo host/puerto).
 */
function resolvePortalUrl(rawUrl, fallbackPath = '/carrito/agregar') {
    let url = rawUrl || fallbackPath;

    // Detectar si la página corre bajo una subcarpeta en el servidor web (ej. /artesano/public)
    const subfolderMatch = window.location.pathname.match(/^(\/[^\/]+\/public)/);
    const basePath = subfolderMatch ? subfolderMatch[1] : '';

    // Si es una ruta relativa a la raíz que empieza con / (pero no //)
    if (url.startsWith('/') && !url.startsWith('//')) {
        if (basePath && !url.startsWith(basePath)) {
            url = `${basePath}${url}`;
        }
        return url;
    }

    // Si es una URL completa (http:// o https://)
    try {
        const parsed = new URL(url, window.location.origin);
        // Ajustar siempre al host, puerto y protocolo actual del navegador para evitar bloqueos CORS
        parsed.protocol = window.location.protocol;
        parsed.host = window.location.host;

        if (basePath && !parsed.pathname.startsWith(basePath)) {
            parsed.pathname = `${basePath}${parsed.pathname}`;
        }
        return parsed.toString();
    } catch (e) {
        return url;
    }
}

// Delegación global activa de inmediato para cualquier botón "+ Carrito", "+", "-", y "✕"
document.addEventListener('click', (e) => {
    // 1. Botones de cantidad en tabla de carrito (+ / -)
    const qtyBtn = e.target.closest('.cart-qty-btn');
    if (qtyBtn) {
        e.preventDefault();
        e.stopPropagation();
        if (qtyBtn.disabled || qtyBtn.classList.contains('btn-loading')) return;

        const id = qtyBtn.getAttribute('data-id');
        const action = qtyBtn.getAttribute('data-action');
        qtyBtn.disabled = true;
        qtyBtn.classList.add('btn-loading');
        actualizarCarrito({ producto_id: parseInt(id, 10), accion: action }, qtyBtn);
        return;
    }

    // 2. Botón de remover producto en tabla de carrito (✕)
    const removeBtn = e.target.closest('.cart-remove-btn');
    if (removeBtn) {
        e.preventDefault();
        e.stopPropagation();
        if (removeBtn.disabled || removeBtn.classList.contains('btn-loading')) return;

        const id = removeBtn.getAttribute('data-id');
        removeBtn.disabled = true;
        removeBtn.classList.add('btn-loading');
        eliminarDelCarrito(parseInt(id, 10), removeBtn);
        return;
    }

    // 3. Botones de añadir al carrito (+ Carrito)
    const btn = e.target.closest('.btn-quick-add, .btn-quick-add-detail');
    if (!btn) return;

    if (btn.disabled || btn.classList.contains('btn-loading')) return;

    e.preventDefault();

    let productId = btn.getAttribute('data-id');
    let cantidad = parseInt(btn.getAttribute('data-cantidad') || '1', 10);

    const form = btn.closest('#form-add-to-cart') || btn.closest('form');
    if (form) {
        const qtyInput = form.querySelector('#product-qty-input') || form.querySelector('input[name="cantidad"]');
        if (qtyInput) {
            cantidad = parseInt(qtyInput.value, 10) || 1;
        }
        if (!productId) {
            const prodInput = form.querySelector('input[name="producto_id"]');
            if (prodInput) productId = prodInput.value;
        }
    }

    if (productId) {
        agregarAlCarrito(productId, cantidad, btn);
    }
});

// Intercepción para formulario de compra en detalle de obra
document.addEventListener('submit', (e) => {
    const form = e.target.closest('#form-add-to-cart');
    if (!form) return;

    e.preventDefault();
    const prodInput = form.querySelector('input[name="producto_id"]');
    const qtyInput = form.querySelector('#product-qty-input') || form.querySelector('input[name="cantidad"]');
    const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('.btn-quick-add');

    const productId = prodInput ? prodInput.value : null;
    const cantidad = qtyInput ? (parseInt(qtyInput.value, 10) || 1) : 1;

    if (productId) {
        agregarAlCarrito(productId, cantidad, submitBtn);
    }
});

/**
 * Módulo 1: Filtro y Búsqueda en Tiempo Real en el Catálogo
 */
function initCatalogFilters() {
    const searchInput = document.querySelector('#catalog-search-input');
    const categorySelect = document.querySelector('#filter-category');
    const sizeSelect = document.querySelector('#filter-size');
    const minPriceInput = document.querySelector('#filter-price-min');
    const maxPriceInput = document.querySelector('#filter-price-max');
    const sortSelect = document.querySelector('#filter-sort');
    const productsGrid = document.querySelector('#catalog-products-grid');
    const resultsCountEl = document.querySelector('#catalog-results-count');

    if (!productsGrid) return;

    let debounceTimer = null;

    const performFilter = () => {
        const queryParams = new URLSearchParams();

        if (searchInput && searchInput.value.trim() !== '') {
            queryParams.append('q', searchInput.value.trim());
        }
        if (categorySelect && categorySelect.value !== 'todas') {
            queryParams.append('categoria', categorySelect.value);
        }
        if (sizeSelect && sizeSelect.value !== 'todos') {
            queryParams.append('tamano', sizeSelect.value);
        }
        if (minPriceInput && minPriceInput.value !== '') {
            queryParams.append('precio_min', minPriceInput.value);
        }
        if (maxPriceInput && maxPriceInput.value !== '') {
            queryParams.append('precio_max', maxPriceInput.value);
        }
        if (sortSelect && sortSelect.value !== '') {
            queryParams.append('sort', sortSelect.value);
        }

        queryParams.append('json', '1');

        // Indicador de carga
        productsGrid.style.opacity = '0.5';

        const rawCatalogUrl = (window.PORTAL_ROUTES && window.PORTAL_ROUTES.catalog) || '/catalogo';
        const catalogUrl = resolvePortalUrl(rawCatalogUrl, '/catalogo');
        fetch(`${catalogUrl}?${queryParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en la respuesta del servidor');
            return response.json();
        })
        .then(res => {
            productsGrid.style.opacity = '1';
            renderProducts(res.data, productsGrid);
            if (resultsCountEl) {
                resultsCountEl.textContent = `${res.count} piezas artesanales encontradas`;
            }
        })
        .catch(error => {
            console.error('Error al filtrar catálogo:', error);
            productsGrid.style.opacity = '1';
        });
    };

    // Escuchadores con debounce de 300ms para teclado
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performFilter, 300);
        });
    }

    [categorySelect, sizeSelect, sortSelect].forEach(select => {
        if (select) {
            select.addEventListener('change', performFilter);
        }
    });

    [minPriceInput, maxPriceInput].forEach(priceInput => {
        if (priceInput) {
            priceInput.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(performFilter, 400);
            });
        }
    });
}

/**
 * Renderizado dinámico de tarjetas de productos en el DOM
 */
function renderProducts(products, container) {
    const rawCatalogBase = (window.PORTAL_ROUTES && window.PORTAL_ROUTES.catalog) || '/catalogo';
    const catalogBase = resolvePortalUrl(rawCatalogBase, '/catalogo');

    if (!products || products.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 48px 20px;">
                <p style="font-size: 1.2rem; color: var(--color-charcoal-muted); margin-bottom: 12px;">
                    No se encontraron piezas artesanales con los filtros seleccionados.
                </p>
                <a href="${catalogBase}" class="btn btn-primary btn-sm">Limpiar filtros</a>
            </div>
        `;
        return;
    }

    container.innerHTML = products.map(prod => `
        <article class="product-card ${prod.en_oferta ? 'is-offer' : ''}" aria-label="Pieza artesanal: ${escapeHtml(prod.nombre_prod)}">
            <div class="product-thumb-wrap">
                <img src="${escapeHtml(prod.imagen_url)}" 
                     alt="Fotografía de ${escapeHtml(prod.nombre_prod)}" 
                     class="product-thumb" 
                     loading="lazy">
                ${prod.en_oferta ? `
                    <span class="product-tag-offer">
                        🔥 -${prod.descuento_porcentaje || 20}%
                    </span>
                ` : ''}
                <span class="product-tag-origin">
                    📍 ${escapeHtml(prod.artesano ? prod.artesano.direccion_arte : 'Cusco')}
                </span>
                <span class="product-tag-cert" title="Certificado de Autenticidad Digital">
                    ✓ Auténtico
                </span>
            </div>
            <div class="product-card-body">
                <div class="product-category-row">
                    <span>${escapeHtml(prod.categoria)}</span>
                    <span class="product-artisan-name">Por ${escapeHtml(prod.artesano ? prod.artesano.nombre + ' ' + prod.artesano.apellido : 'Maestro Artesano')}</span>
                </div>
                <h3 class="product-title">
                    <a href="${catalogBase}/${prod.id}">${escapeHtml(prod.nombre_prod)}</a>
                </h3>
                <p class="product-materials">
                    <strong>Materiales:</strong> ${escapeHtml(prod.materiales_origen || 'Técnicas andinas')}
                </p>
                <div class="product-price-row">
                    <div class="product-price-wrap" 
                         data-price-pen="${parseFloat(prod.precio_oferta && prod.en_oferta ? prod.precio_oferta : prod.precio)}"
                         data-original-price-pen="${prod.en_oferta ? parseFloat(prod.precio) : ''}">
                        <div style="display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap;">
                            ${prod.en_oferta ? `
                                <del class="price-original-del" title="Precio regular">
                                    ${(window.PORTAL_CURRENCY && window.PORTAL_CURRENCY.active === 'USD')
                                        ? '$ ' + (parseFloat(prod.precio) / (window.PORTAL_CURRENCY.rate || 3.41)).toFixed(2)
                                        : 'S/ ' + parseFloat(prod.precio).toFixed(2)}
                                </del>
                            ` : ''}
                            <span class="product-price-amount price-main ${prod.en_oferta ? 'price-offer' : ''}">
                                ${(window.PORTAL_CURRENCY && window.PORTAL_CURRENCY.active === 'USD')
                                    ? '$ ' + (parseFloat(prod.precio_oferta && prod.en_oferta ? prod.precio_oferta : prod.precio) / (window.PORTAL_CURRENCY.rate || 3.41)).toFixed(2)
                                    : 'S/ ' + parseFloat(prod.precio_oferta && prod.en_oferta ? prod.precio_oferta : prod.precio).toFixed(2)}
                            </span>
                            <span class="product-price-currency price-currency">
                                ${(window.PORTAL_CURRENCY && window.PORTAL_CURRENCY.active === 'USD') ? 'USD' : 'PEN'}
                            </span>
                        </div>
                        <span class="price-secondary">
                            ${(window.PORTAL_CURRENCY && window.PORTAL_CURRENCY.active === 'USD')
                                ? '≈ S/ ' + parseFloat(prod.precio_oferta && prod.en_oferta ? prod.precio_oferta : prod.precio).toFixed(2) + ' PEN'
                                : '≈ $ ' + (parseFloat(prod.precio_oferta && prod.en_oferta ? prod.precio_oferta : prod.precio) / (window.PORTAL_CURRENCY.rate || 3.41)).toFixed(2) + ' USD'}
                        </span>
                    </div>
                    <span style="font-size: 0.8rem; color: var(--color-fairtrade-green); font-weight: 600;">
                        ★ ${parseFloat(prod.puntuacion).toFixed(1)}
                    </span>
                </div>
                <div class="product-card-actions">
                    <a href="${catalogBase}/${prod.id}" class="btn btn-secondary btn-sm" aria-label="Ver detalles de ${escapeHtml(prod.nombre_prod)}">
                        Ver Detalles
                    </a>
                    <button type="button" 
                            class="btn btn-accent btn-sm btn-quick-add" 
                            data-id="${prod.id}" 
                            aria-label="Añadir ${escapeHtml(prod.nombre_prod)} al carrito">
                        + Carrito
                    </button>
                </div>
            </div>
        </article>
    `).join('');
}

/**
 * Módulo 2: Interacciones del Carrito de Compras (Shopping Basket)
 */
function initCartInteractions() {
    // Toggle interactivo de envoltura de regalo en tabla de carrito
    const giftWrapCheckbox = document.querySelector('#gift-wrapping-checkbox');
    if (giftWrapCheckbox) {
        giftWrapCheckbox.addEventListener('change', (e) => {
            const isChecked = e.target.checked;
            actualizarCarrito({ envoltura_regalo: isChecked ? 1 : 0 });
        });
    }

    // Nota: Los botones .cart-qty-btn (+ / -) y .cart-remove-btn (✕)
    // se manejan mediante delegación global única con bloqueo de concurrencia
    // para garantizar el incremento estrictamente de 1 en 1.
}

function attachQuickAddEvents() {
    // Delegación global activa en document
}

function agregarAlCarrito(productId, cantidad = 1, triggerButton = null) {
    if (!productId) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const rawUrl = (window.PORTAL_ROUTES && window.PORTAL_ROUTES.cartAdd) || '/carrito/agregar';
    const url = resolvePortalUrl(rawUrl, '/carrito/agregar');

    let originalHtml = '';
    if (triggerButton) {
        originalHtml = triggerButton.innerHTML;
        triggerButton.disabled = true;
        triggerButton.classList.add('btn-loading');
        triggerButton.innerHTML = `<span style="display: inline-block; animation: spin 0.8s linear infinite;">⏳</span> Añadiendo...`;
    }

    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            _token: token,
            producto_id: parseInt(productId, 10),
            cantidad: parseInt(cantidad, 10) || 1
        })
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            const errorMsg = data.message || (response.status === 419 
                ? 'Sesión expirada. Por favor recarga la página para añadir al carrito.' 
                : 'Error al agregar la pieza al carrito.');
            throw new Error(errorMsg);
        }
        return data;
    })
    .then(data => {
        if (data.status === 'success') {
            actualizarBadgesContador(data.cart_count);
            showToast(data.message || 'Pieza añadida a tu carrito con éxito', 'success');

            if (triggerButton) {
                triggerButton.classList.remove('btn-loading');
                triggerButton.classList.add('btn-added-success');
                triggerButton.innerHTML = `✓ ¡Añadido!`;

                setTimeout(() => {
                    triggerButton.classList.remove('btn-added-success');
                    triggerButton.innerHTML = originalHtml;
                    triggerButton.disabled = false;
                }, 1500);
            }
        } else {
            showToast(data.message || 'Error al agregar producto', 'warning');
            if (triggerButton) {
                triggerButton.classList.remove('btn-loading');
                triggerButton.innerHTML = originalHtml;
                triggerButton.disabled = false;
            }
        }
    })
    .catch(err => {
        console.error('Error al agregar al carrito:', err);
        showToast(err.message || 'Error de comunicación con el taller', 'error');
        if (triggerButton) {
            triggerButton.classList.remove('btn-loading');
            triggerButton.innerHTML = originalHtml;
            triggerButton.disabled = false;
        }
    });
}

let isUpdatingCart = false;

function actualizarCarrito(payload, triggerButton = null) {
    if (isUpdatingCart) return;
    isUpdatingCart = true;

    if (triggerButton) {
        triggerButton.disabled = true;
        triggerButton.classList.add('btn-loading');
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const rawUrl = (window.PORTAL_ROUTES && window.PORTAL_ROUTES.cartUpdate) || '/carrito/actualizar';
    const url = resolvePortalUrl(rawUrl, '/carrito/actualizar');

    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(Object.assign({ _token: token }, payload))
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Error al actualizar el carrito');
        return data;
    })
    .then(data => {
        if (data.status === 'success') {
            window.location.href = window.location.pathname + '?v=' + Date.now();
        } else {
            isUpdatingCart = false;
            showToast(data.message || 'No se pudo actualizar el carrito', 'warning');
            if (triggerButton) {
                triggerButton.disabled = false;
                triggerButton.classList.remove('btn-loading');
            }
        }
    })
    .catch(err => {
        isUpdatingCart = false;
        console.error('Error al actualizar carrito:', err);
        showToast(err.message || 'Error al actualizar carrito', 'error');
        if (triggerButton) {
            triggerButton.disabled = false;
            triggerButton.classList.remove('btn-loading');
        }
    });
}

let isRemovingFromCart = false;

function eliminarDelCarrito(productId, triggerButton = null) {
    if (isRemovingFromCart) return;
    isRemovingFromCart = true;

    if (triggerButton) {
        triggerButton.disabled = true;
        triggerButton.classList.add('btn-loading');
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const rawUrl = (window.PORTAL_ROUTES && window.PORTAL_ROUTES.cartRemove) || '/carrito/eliminar';
    const url = resolvePortalUrl(rawUrl, '/carrito/eliminar');

    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ _token: token, producto_id: parseInt(productId, 10) })
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Error al retirar la pieza');
        return data;
    })
    .then(data => {
        if (data.status === 'success') {
            window.location.href = window.location.pathname + '?v=' + Date.now();
        } else {
            isRemovingFromCart = false;
            showToast(data.message || 'No se pudo retirar del carrito', 'warning');
            if (triggerButton) {
                triggerButton.disabled = false;
                triggerButton.classList.remove('btn-loading');
            }
        }
    })
    .catch(err => {
        isRemovingFromCart = false;
        console.error('Error al retirar del carrito:', err);
        showToast(err.message || 'Error al retirar del carrito', 'error');
        if (triggerButton) {
            triggerButton.disabled = false;
            triggerButton.classList.remove('btn-loading');
        }
    });
}

function actualizarBadgesContador(count) {
    document.querySelectorAll('.cart-counter').forEach(el => {
        el.textContent = count;
        el.classList.remove('bounce');
        void el.offsetWidth; // Reflow para reiniciar la animación
        el.classList.add('bounce');
    });

    const navCart = document.querySelector('.btn-cart-nav');
    if (navCart) {
        navCart.setAttribute('aria-label', `Carrito de compras con ${count} artículos`);
    }
}

/**
 * Módulo 3: Selectores de Cantidad (+ / -) en la Ficha de Detalle
 */
function initQuantitySelectors() {
    const qtySelectors = document.querySelectorAll('.quantity-selector');
    qtySelectors.forEach(selector => {
        const minusBtn = selector.querySelector('.qty-minus');
        const plusBtn = selector.querySelector('.qty-plus');
        const input = selector.querySelector('.qty-input');
        const maxStock = parseInt(input?.getAttribute('max') || '999', 10);

        if (minusBtn && input) {
            minusBtn.addEventListener('click', () => {
                let current = parseInt(input.value, 10) || 1;
                if (current > 1) {
                    input.value = current - 1;
                }
            });
        }

        if (plusBtn && input) {
            plusBtn.addEventListener('click', () => {
                let current = parseInt(input.value, 10) || 1;
                if (current < maxStock) {
                    input.value = current + 1;
                } else {
                    showToast(`Stock máximo disponible: ${maxStock} unidades`, 'warning');
                }
            });
        }
    });
}

/**
 * Módulo 4: Validación en Cliente y Cumplimiento con Ley N.º 29733
 */
function initFormValidations() {
    const checkoutForm = document.querySelector('#checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', (e) => {
            const consentCheckbox = document.querySelector('#consentimiento_datos');
            const direccionInput = document.querySelector('#direccion_envio');
            const telefonoInput = document.querySelector('#telefono_contacto');

            let hasError = false;

            if (consentCheckbox && !consentCheckbox.checked) {
                e.preventDefault();
                showToast('Debe aceptar la cláusula de protección de datos personales (Ley N.º 29733).', 'error');
                consentCheckbox.focus();
                hasError = true;
            }

            if (direccionInput && direccionInput.value.trim().length < 8) {
                e.preventDefault();
                showToast('Por favor ingrese una dirección de envío completa y detallada.', 'error');
                direccionInput.focus();
                hasError = true;
            }

            if (telefonoInput && telefonoInput.value.trim().length < 7) {
                e.preventDefault();
                showToast('Ingrese un número telefónico de contacto válido para el despacho del HUB.', 'error');
                telefonoInput.focus();
                hasError = true;
            }

            if (!hasError) {
                const submitBtn = checkoutForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Procesando certificación y compra segura...';
                }
            }
        });
    }
}

/**
 * Utilidades: Toast flotante accesible
 */
function showToast(message, type = 'success') {
    let toast = document.querySelector('#app-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'app-toast';
        toast.className = 'toast-notification';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        document.body.appendChild(toast);
    }

    toast.textContent = message;
    if (type === 'error') {
        toast.style.borderLeftColor = 'var(--color-danger)';
    } else if (type === 'warning') {
        toast.style.borderLeftColor = 'var(--color-gold)';
    } else {
        toast.style.borderLeftColor = 'var(--color-fairtrade-green)';
    }

    toast.classList.add('active');

    setTimeout(() => {
        toast.classList.remove('active');
    }, 3800);
}

function escapeHtml(string) {
    if (!string) return '';
    return String(string)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/**
 * Módulo 5: Selector de Divisa Dual (PEN / USD) y Tipo de Cambio en Vivo
 */
function initCurrencySwitcher() {
    const config = window.PORTAL_CURRENCY || { active: 'PEN', rate: 3.75, changeUrl: '/cambiar-moneda', rateUrl: '/api/tipo-cambio' };
    const toggleBtns = document.querySelectorAll('.currency-toggle-btn');
    const rateChip = document.querySelector('#currency-rate-chip');

    // Comprobar preferencia guardada en localStorage
    const savedCurrency = localStorage.getItem('portal_currency');
    if (savedCurrency && ['PEN', 'USD'].includes(savedCurrency) && savedCurrency !== config.active) {
        applyCurrency(savedCurrency, false);
    }

    // Consulta en segundo plano para verificar si la tasa de cambio del dólar se actualizó
    if (config.rateUrl) {
        fetch(config.rateUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data && data.rate_usd_to_pen) {
                config.rate = parseFloat(data.rate_usd_to_pen);
                if (rateChip) {
                    rateChip.textContent = `T.C. $1 ≈ S/ ${config.rate.toFixed(2)}`;
                }
                updateAllPricesInDom(config.active, config.rate);
            }
        })
        .catch(() => {
            // Silencioso: conserva tasa en caché
        });
    }

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const selectedCurrency = btn.getAttribute('data-currency');
            if (!selectedCurrency || selectedCurrency === config.active) return;
            applyCurrency(selectedCurrency, true);
        });
    });

    function applyCurrency(newCurrency, notify = true) {
        config.active = newCurrency;
        localStorage.setItem('portal_currency', newCurrency);

        // Actualizar botones activos en navbar
        toggleBtns.forEach(b => {
            if (b.getAttribute('data-currency') === newCurrency) {
                b.classList.add('active');
            } else {
                b.classList.remove('active');
            }
        });

        // Actualizar todos los precios visibles en el DOM
        updateAllPricesInDom(newCurrency, config.rate);

        // Persistir en sesión backend
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        fetch(config.changeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify({ moneda: newCurrency })
        }).catch(err => console.error('Error al sincronizar moneda en servidor:', err));

        if (notify) {
            const label = newCurrency === 'USD' ? 'Dólares estadounidenses ($ USD)' : 'Soles peruanos (S/ PEN)';
            showToast(`Moneda cambiada a ${label}`);
        }
    }

    function updateAllPricesInDom(currency, rate) {
        const priceWraps = document.querySelectorAll('.product-price-wrap');
        priceWraps.forEach(wrap => {
            const rawPrice = wrap.getAttribute('data-price-pen');
            if (rawPrice === null) return;
            const pen = parseFloat(rawPrice);
            const usd = pen / rate;

            const mainEl = wrap.querySelector('.price-main');
            const currEl = wrap.querySelector('.price-currency');
            const secEl = wrap.querySelector('.price-secondary');
            const origEl = wrap.querySelector('.price-original-del');
            const rawOrig = wrap.getAttribute('data-original-price-pen');

            if (currency === 'USD') {
                if (mainEl) mainEl.textContent = `$ ${usd.toFixed(2)}`;
                if (currEl) currEl.textContent = 'USD';
                if (secEl) {
                    secEl.textContent = wrap.classList.contains('detail-price-box') 
                        ? `(≈ S/ ${pen.toFixed(2)} PEN)` 
                        : `≈ S/ ${pen.toFixed(2)} PEN`;
                }
                if (origEl && rawOrig) {
                    const origPen = parseFloat(rawOrig);
                    origEl.textContent = `$ ${(origPen / rate).toFixed(2)}`;
                }
            } else {
                if (mainEl) mainEl.textContent = `S/ ${pen.toFixed(2)}`;
                if (currEl) currEl.textContent = 'PEN';
                if (secEl) {
                    secEl.textContent = wrap.classList.contains('detail-price-box') 
                        ? `(≈ $ ${usd.toFixed(2)} USD)` 
                        : `≈ $ ${usd.toFixed(2)} USD`;
                }
                if (origEl && rawOrig) {
                    const origPen = parseFloat(rawOrig);
                    origEl.textContent = `S/ ${origPen.toFixed(2)}`;
                }
            }
        });
    }
}

/**
 * Módulo 6: Carrusel Interactivo de Todas las Obras y Ofertas de Temporada
 */
function initHomeCarousel() {
    const track = document.getElementById('home-carousel-track');
    if (!track) return;

    const prevBtn = document.getElementById('carousel-prev-btn');
    const nextBtn = document.getElementById('carousel-next-btn');
    const filterBtns = document.querySelectorAll('.carousel-filter-btn');
    const countLabel = document.getElementById('carousel-items-count');
    const cards = track.querySelectorAll('.carousel-card');

    if (!cards.length) return;

    // Desplazamiento con botones
    const scrollAmount = 320;

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
    }

    // Actualizar estado de botones (deshabilitar al inicio/fin)
    function updateNavButtonsState() {
        if (!prevBtn || !nextBtn) return;
        const maxScroll = track.scrollWidth - track.clientWidth - 5;
        prevBtn.disabled = track.scrollLeft <= 5;
        nextBtn.disabled = track.scrollLeft >= maxScroll;
    }

    track.addEventListener('scroll', updateNavButtonsState, { passive: true });
    updateNavButtonsState();

    // Filtros por Categoría y "Solo Ofertas"
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.getAttribute('data-filter');
            if (!filter) return;

            filterBtns.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-selected', 'false');
            });
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');

            let visibleCount = 0;
            cards.forEach(card => {
                const isOffer = card.getAttribute('data-is-offer') === 'true';
                const category = card.getAttribute('data-category');

                let match = false;
                if (filter === 'all') {
                    match = true;
                } else if (filter === 'ofertas') {
                    match = isOffer;
                } else if (category && category.toLowerCase() === filter.toLowerCase()) {
                    match = true;
                }

                if (match) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (countLabel) {
                countLabel.textContent = `${visibleCount} ${visibleCount === 1 ? 'obra' : 'obras'}`;
            }

            track.scrollTo({ left: 0, behavior: 'smooth' });
            setTimeout(updateNavButtonsState, 200);
        });
    });

    // Soporte para Arrastrar con Mouse (Mouse Drag to Scroll)
    let isDown = false;
    let startX;
    let scrollLeft;

    track.addEventListener('mousedown', (e) => {
        // Ignorar si el clic fue en un botón o enlace
        if (e.target.closest('button, a, input')) return;
        isDown = true;
        track.style.cursor = 'grabbing';
        startX = e.pageX - track.offsetLeft;
        scrollLeft = track.scrollLeft;
    });

    track.addEventListener('mouseleave', () => {
        isDown = false;
        track.style.cursor = '';
    });

    track.addEventListener('mouseup', () => {
        isDown = false;
        track.style.cursor = '';
    });

    track.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - track.offsetLeft;
        const walk = (x - startX) * 1.5;
        track.scrollLeft = scrollLeft - walk;
    });
}

/**
 * Módulo 7: Hero Slider Carousel en la cabecera principal
 */
function initHeroSlider() {
    const container = document.getElementById('hero-slider-container');
    const track = document.getElementById('hero-slides-track');
    if (!container || !track) return;

    const slides = track.querySelectorAll('.hero-slide');
    if (!slides.length) return;

    const prevBtn = document.getElementById('hero-prev-btn');
    const nextBtn = document.getElementById('hero-next-btn');
    const dotsContainer = document.getElementById('hero-dots-container');
    const dots = dotsContainer ? dotsContainer.querySelectorAll('.hero-dot') : [];

    let currentIndex = 0;
    const totalSlides = slides.length;
    let autoPlayTimer = null;

    function goToSlide(index) {
        if (index < 0) {
            currentIndex = totalSlides - 1;
        } else if (index >= totalSlides) {
            currentIndex = 0;
        } else {
            currentIndex = index;
        }

        track.style.transform = `translateX(-${currentIndex * 100}%)`;

        dots.forEach((dot, i) => {
            if (i === currentIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            goToSlide(currentIndex + 1);
            resetAutoPlay();
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            goToSlide(currentIndex - 1);
            resetAutoPlay();
        });
    }

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            goToSlide(i);
            resetAutoPlay();
        });
    });

    function startAutoPlay() {
        if (totalSlides <= 1) return;
        autoPlayTimer = setInterval(() => {
            goToSlide(currentIndex + 1);
        }, 5500);
    }

    function resetAutoPlay() {
        if (autoPlayTimer) clearInterval(autoPlayTimer);
        startAutoPlay();
    }

    container.addEventListener('mouseenter', () => {
        if (autoPlayTimer) clearInterval(autoPlayTimer);
    });

    container.addEventListener('mouseleave', () => {
        startAutoPlay();
    });

    // Touch Swipe en Móviles
    let touchStartX = 0;
    let touchEndX = 0;

    container.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    container.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diff = touchStartX - touchEndX;
        if (Math.abs(diff) > 40) {
            if (diff > 0) {
                goToSlide(currentIndex + 1);
            } else {
                goToSlide(currentIndex - 1);
            }
            resetAutoPlay();
        }
    }, { passive: true });

    startAutoPlay();
}

/**
 * Módulo 8: Alertas en Tiempo Real con Sonido de Campana para Nuevos Pedidos del Artesano
 */
function initArtisanOrderAlerts() {
    // Solo se activa si el usuario es artesano o admin del HUB
    if (!window.PORTAL_USER || !window.PORTAL_USER.isArtesano) {
        return;
    }

    const checkUrl = window.PORTAL_ROUTES && window.PORTAL_ROUTES.checkArtesanoOrders;
    if (!checkUrl) return;

    let ultimoPedidoId = window.ARTESANO_INITIAL_ORDER_ID !== undefined 
        ? window.ARTESANO_INITIAL_ORDER_ID 
        : 0;

    // Helper global para que el artesano pueda probar el sonido manualmente
    window.probarSonidoAlerta = function () {
        playArtisanOrderChime();
        mostrarNotificacionAlerta({
            id: 'DEMO',
            codigo_seguimiento: 'HUB-DEMO-TEST',
            cliente_nombre: 'Comprador de Prueba',
            pais_destino: 'Cusco, Perú',
            piezas: '1x Demostración de Alerta Sonora en Taller',
            total: '120.00',
            envoltura_regalo: true,
            mensaje_regalo: '¡Sonido y alerta funcionando con éxito en tu taller artesanal!'
        }, true);
    };

    // Polling cada 6 segundos para chequear nuevos pedidos
    setInterval(() => {
        verificarNuevosPedidosServidor();
    }, 6000);

    function verificarNuevosPedidosServidor() {
        fetch(`${checkUrl}?ultimo_id=${ultimoPedidoId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (!res.ok) return null;
            return res.json();
        })
        .then(data => {
            if (!data) return;

            // Actualizar contador si estamos en el dashboard
            const badgeCounter = document.getElementById('contador-pedidos-pendientes');
            if (badgeCounter && data.total_pendientes !== undefined) {
                badgeCounter.textContent = `${data.total_pendientes} pendientes de preparación`;
                if (data.total_pendientes > 0) {
                    badgeCounter.className = 'badge badge-warning';
                }
            }

            if (data.hay_nuevos && data.pedidos && data.pedidos.length > 0) {
                // Actualizar último ID registrado
                if (data.max_id > ultimoPedidoId) {
                    ultimoPedidoId = data.max_id;
                }

                // Disparar sonido y alertas para cada pedido nuevo
                data.pedidos.forEach(pedido => {
                    playArtisanOrderChime();
                    mostrarNotificacionAlerta(pedido);
                    agregarFilaPedidoEnTabla(pedido);
                });
            }
        })
        .catch(err => {
            console.debug('Polling artesano status:', err);
        });
    }

    /**
     * Síntesis de sonido de campana melódica andina de notificación
     * Utiliza Web Audio API pura, garantizando cero fallos de 404 o códec.
     */
    function playArtisanOrderChime() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();

            if (ctx.state === 'suspended') {
                ctx.resume();
            }

            const now = ctx.currentTime;

            // Arpegio armónico ascendente de campana: Mi5 (659Hz) -> Sol#5 (830Hz) -> Si5 (987Hz) -> Mi6 (1318Hz)
            const notas = [
                { f: 659.25, t: 0.00, d: 0.60, g: 0.35, tipo: 'sine' },
                { f: 830.61, t: 0.12, d: 0.70, g: 0.40, tipo: 'triangle' },
                { f: 987.77, t: 0.24, d: 0.85, g: 0.45, tipo: 'sine' },
                { f: 1318.51, t: 0.38, d: 1.30, g: 0.50, tipo: 'sine' }
            ];

            notas.forEach(nota => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();

                osc.type = nota.tipo;
                osc.frequency.setValueAtTime(nota.f, now + nota.t);

                // Ataque rápido y decaimiento exponencial natural de campana
                gain.gain.setValueAtTime(0.001, now + nota.t);
                gain.gain.linearRampToValueAtTime(nota.g, now + nota.t + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + nota.t + nota.d);

                osc.connect(gain);
                gain.connect(ctx.destination);

                osc.start(now + nota.t);
                osc.stop(now + nota.t + nota.d);
            });
        } catch (e) {
            console.warn('AudioContext bloqueado o no disponible:', e);
        }
    }

    /**
     * Muestra la tarjeta flotante y hace parpadear el título del navegador
     */
    function mostrarNotificacionAlerta(pedido, esPrueba = false) {
        // Parpadeo del título de la pestaña para que no pase desapercibido si el artesano está en otra ventana
        const originalTitle = document.title;
        let count = 0;
        const blinkInterval = setInterval(() => {
            document.title = (count % 2 === 0) ? '🔔 ¡NUEVO PEDIDO EN TALLER!' : originalTitle;
            count++;
            if (count >= 12) {
                clearInterval(blinkInterval);
                document.title = originalTitle;
            }
        }, 750);

        // Contenedor principal de alertas flotantes
        let container = document.getElementById('artesano-alerts-root');
        if (!container) {
            container = document.createElement('div');
            container.id = 'artesano-alerts-root';
            container.style.cssText = `
                position: fixed;
                top: 24px;
                right: 24px;
                z-index: 999999;
                display: flex;
                flex-direction: column;
                gap: 14px;
                max-width: 420px;
                width: calc(100% - 48px);
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }

        const card = document.createElement('div');
        card.style.cssText = `
            pointer-events: auto;
            background: linear-gradient(135deg, #FFFDF8 0%, #FFF4D9 100%);
            border: 2px solid #C88A2C;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 12px 30px rgba(184, 134, 11, 0.35);
            color: #2F1E0F;
            animation: slideInRightAlert 0.4s ease-out forwards;
            position: relative;
        `;

        card.innerHTML = `
            <div style="display: flex; gap: 14px; align-items: flex-start;">
                <div style="font-size: 2.2rem; line-height: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));">
                    🔔
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 800; color: #8A4614; letter-spacing: 1px;">
                            ${esPrueba ? '✦ Alerta de Prueba' : '✦ ¡Nueva Orden en tu Taller!'}
                        </span>
                        <button type="button" style="background: none; border: none; font-size: 1.1rem; color: #8C735E; cursor: pointer; padding: 0 4px;" onclick="this.closest('[style*=\\'pointer-events: auto\\']').remove()">✕</button>
                    </div>

                    <div style="font-family: var(--font-serif, serif); font-size: 1.15rem; font-weight: 700; color: #2C1E11; margin-top: 4px;">
                        Pedido #${pedido.id} <span style="font-family: monospace; font-size: 0.85rem; color: #C88A2C;">(${pedido.codigo_seguimiento || ''})</span>
                    </div>

                    <div style="font-size: 0.88rem; color: #4A3320; margin-top: 4px;">
                        Comprador: <strong>${pedido.cliente_nombre}</strong> (${pedido.pais_destino})
                    </div>

                    <div style="font-size: 0.82rem; color: #7A5826; margin-top: 3px; background: #FAF5EB; padding: 6px 10px; border-radius: 6px; border-left: 3px solid #C88A2C;">
                        Obras: <strong>${pedido.piezas}</strong>
                    </div>

                    ${pedido.envoltura_regalo ? `
                        <div style="margin-top: 6px; font-size: 0.78rem; background: #FFF9E6; border: 1px dashed #D4A359; border-radius: 6px; padding: 6px 8px; color: #8C531B;">
                            🎁 <strong>Requiere Empaque de Regalo con Tarjeta QR</strong>
                            ${pedido.mensaje_regalo ? `<div style="font-style: italic; margin-top: 2px;">«${pedido.mensaje_regalo}»</div>` : ''}
                        </div>
                    ` : ''}

                    <div style="display: flex; gap: 8px; margin-top: 12px; align-items: center;">
                        ${!esPrueba ? `
                            <a href="/artesano/dashboard" class="btn btn-primary btn-sm" style="font-size: 0.8rem; padding: 5px 12px;">
                                Ver Pedido en Taller
                            </a>
                        ` : ''}
                        <button type="button" class="btn btn-secondary btn-sm" style="font-size: 0.8rem; padding: 5px 12px;" onclick="this.closest('[style*=\\'pointer-events: auto\\']').remove()">
                            Aceptar
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(card);

        // Desaparición automática tras 18 segundos
        setTimeout(() => {
            if (card.parentNode) {
                card.style.opacity = '0';
                card.style.transform = 'translateX(30px)';
                card.style.transition = 'all 0.5s ease';
                setTimeout(() => card.remove(), 500);
            }
        }, 18000);
    }

    /**
     * Si la tabla de pedidos del artesano está en pantalla, inserta la nueva fila arriba
     */
    function agregarFilaPedidoEnTabla(p) {
        const tbody = document.getElementById('tabla-pedidos-artesano-body');
        if (!tbody) return;

        // Remover fila de "no tienes órdenes" si existe
        const emptyRow = tbody.querySelector('td[colspan]');
        if (emptyRow) {
            emptyRow.closest('tr').remove();
        }

        // Crear nueva fila resaltada
        const tr = document.createElement('tr');
        tr.style.backgroundColor = '#FFF8E1';
        tr.style.transition = 'background-color 2s ease';

        const regaloHtml = p.envoltura_regalo ? `
            <div style="margin-top: 10px; background: #FFF9E6; border: 1.5px dashed #D4A359; border-radius: 8px; padding: 10px 12px; font-size: 0.82rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                    <strong style="color: #8C531B; display: flex; align-items: center; gap: 4px;">
                        🎁 Empaque de Regalo + QR Digital
                    </strong>
                    <span class="badge" style="background: #E8F5E9; color: #2E6B38; font-size: 0.7rem;">
                        + S/ 12.00
                    </span>
                </div>
                <div style="display: flex; gap: 12px; align-items: center; margin-top: 8px;">
                    <a href="${p.url_dedicatoria}" target="_blank" title="Ver dedicatoria digital">
                        <img src="${p.qr_url}" alt="QR Dedicatoria" style="width: 55px; height: 55px; border-radius: 4px; border: 1px solid #D4A359; background: #FFF; padding: 2px;">
                    </a>
                    <div style="flex: 1;">
                        <div style="font-family: var(--font-serif); font-style: italic; color: #3B2613; line-height: 1.3;">
                            «${p.mensaje_regalo || 'Sin dedicatoria manuscrita.'}»
                        </div>
                        <div style="margin-top: 6px; display: flex; gap: 6px; flex-wrap: wrap;">
                            <a href="${p.url_dedicatoria}" target="_blank" class="btn btn-outline-earth btn-sm" style="font-size: 0.72rem; padding: 2px 8px;">
                                📱 Ver Tarjeta QR
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        ` : '';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        tr.innerHTML = `
            <td><strong>#${p.id}</strong></td>
            <td>${p.fecha}</td>
            <td>${p.cliente_nombre} (${p.pais_destino})</td>
            <td>
                <div>• ${p.piezas}</div>
                ${regaloHtml}
            </td>
            <td>
                <span class="badge badge-warning">Pendiente de confirmación</span>
            </td>
            <td>
                <form action="${p.url_confirmar}" method="POST" style="display: inline;">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <button type="submit" class="btn btn-primary btn-sm">
                        Confirmar Preparación en Taller
                    </button>
                </form>
            </td>
        `;

        tbody.insertBefore(tr, tbody.firstChild);

        // Desvanecer el resaltado amarillo después de 4 segundos
        setTimeout(() => {
            tr.style.backgroundColor = '';
        }, 4000);
    }
}

/**
 * Módulo: Alternador de Visibilidad de Contraseñas (Ojito)
 */
function initPasswordToggles() {
    document.querySelectorAll('.btn-toggle-password').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const wrapper = button.closest('.password-input-wrapper');
            if (!wrapper) return;
            const input = wrapper.querySelector('input');
            if (!input) return;

            const eyeOpen = button.querySelector('.eye-open');
            const eyeClosed = button.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                button.setAttribute('aria-label', 'Ocultar contraseña');
                button.setAttribute('title', 'Ocultar contraseña');
                if (eyeOpen) eyeOpen.style.display = 'none';
                if (eyeClosed) eyeClosed.style.display = 'inline-block';
            } else {
                input.type = 'password';
                button.setAttribute('aria-label', 'Mostrar contraseña');
                button.setAttribute('title', 'Mostrar contraseña');
                if (eyeOpen) eyeOpen.style.display = 'inline-block';
                if (eyeClosed) eyeClosed.style.display = 'none';
            }
            input.focus();
        });
    });
}

/**
 * Módulo: Menú Hamburguesa y Navegación Móvil Deslizable (Drawer)
 * - Control de apertura/cierre reactivo
 * - Cierre al pulsar overlay, botón cerrar o presionar tecla Escape
 * - Bloqueo de scroll de fondo para accesibilidad
 */
function initMobileNavigation() {
    const toggleBtn = document.getElementById('mobile-nav-toggle');
    const drawer = document.getElementById('mobile-nav-drawer');
    const overlay = document.getElementById('mobile-drawer-overlay');
    const closeBtn = document.getElementById('mobile-drawer-close');

    if (!toggleBtn || !drawer || !overlay) return;

    function openDrawer() {
        drawer.classList.add('active');
        overlay.classList.add('active');
        toggleBtn.classList.add('open');
        toggleBtn.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function closeDrawer() {
        drawer.classList.remove('active');
        overlay.classList.remove('active');
        toggleBtn.classList.remove('open');
        toggleBtn.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    toggleBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        if (drawer.classList.contains('active')) {
            closeDrawer();
        } else {
            openDrawer();
        }
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            closeDrawer();
        });
    }

    overlay.addEventListener('click', () => {
        closeDrawer();
    });

    // Cerrar al pulsar Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('active')) {
            closeDrawer();
            toggleBtn.focus();
        }
    });

    // Cerrar al pulsar enlaces de navegación interna dentro del drawer
    drawer.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            closeDrawer();
        });
    });

    // Cerrar si se redimensiona a pantalla de escritorio (> 991px)
    window.addEventListener('resize', () => {
        if (window.innerWidth > 991 && drawer.classList.contains('active')) {
            closeDrawer();
        }
    });
}
