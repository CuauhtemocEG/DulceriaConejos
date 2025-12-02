<?php
$pageTitle = 'Punto de Venta';
ob_start();
?>

<div class="space-y-6">
    <!-- Búsqueda de productos -->
    <div class="bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-lg border border-purple-100 p-6">
        <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-barcode mr-2"></i>Código de barras o nombre
                </label>
                <input type="text" id="buscarProducto" placeholder="Escanea código o escribe nombre..." 
                    autofocus
                    class="w-full px-4 py-3 border-2 border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                <p class="text-xs text-purple-600 mt-2 flex items-center gap-1">
                    <i class="fas fa-info-circle"></i> 
                    <span>Productos a granel: códigos terminan en 01, 02, 03 o 04 según peso</span>
                </p>
            </div>
            <div class="w-full md:w-56">
                <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                <select id="filtroCategoria" class="w-full px-4 py-3 border-2 border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                    <option value="">Todas las categorías</option>
                </select>
            </div>
        </div>
        
        <!-- Resultados de búsqueda -->
        <div id="resultadosBusqueda" class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 max-h-72 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-purple-400 scrollbar-track-purple-100"></div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Carrito de compra -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- Header del carrito -->
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-white text-lg"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-white">Carrito de Compra</h2>
                                <p class="text-purple-100 text-sm">Artículos seleccionados</p>
                            </div>
                        </div>
                        <button id="btnLimpiarCarrito" onclick="limpiarCarrito()" 
                            class="hidden px-4 py-2 bg-white bg-opacity-20 hover:bg-opacity-30 text-white rounded-lg text-sm font-medium transition-all flex items-center gap-2 backdrop-blur-sm">
                            <i class="fas fa-trash-alt"></i>
                            <span>Limpiar</span>
                        </button>
                    </div>
                </div>
                
                <!-- Items del carrito -->
                <div class="p-6">
                    <div id="carritoItems" class="space-y-3 mb-6 h-[400px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-purple-400 scrollbar-track-purple-100">
                        <div class="flex flex-col items-center justify-center h-full text-center">
                            <div class="w-24 h-24 bg-purple-50 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-shopping-bag text-purple-300 text-4xl"></i>
                            </div>
                            <p class="text-gray-400 font-medium">El carrito está vacío</p>
                            <p class="text-gray-400 text-sm mt-1">Agrega productos para comenzar</p>
                        </div>
                    </div>
                    
                    <!-- Total -->
                    <div class="border-t-2 border-purple-100 pt-4">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-700">Total a pagar:</span>
                            <div class="text-right">
                                <span id="totalVenta" class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">$0.00</span>
                                <p class="text-xs text-gray-500 mt-1">IVA incluido</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Panel de pago -->
        <div class="lg:col-span-1">
            <div class="bg-gradient-to-br from-purple-600 via-purple-500 to-pink-500 rounded-xl shadow-xl p-6 text-white sticky top-20 border border-purple-400">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-cash-register text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold">Finalizar Venta</h2>
                        <p class="text-purple-100 text-sm">Procesar pago</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <!-- Método de pago -->
                    <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4">
                        <label class="block text-sm font-medium mb-2 flex items-center gap-2">
                            <i class="fas fa-credit-card"></i>
                            Método de pago
                        </label>
                        <select id="metodoPago" class="w-full px-4 py-3 bg-white text-gray-800 rounded-lg focus:ring-2 focus:ring-purple-300 font-medium">
                            <option value="1">💵 Efectivo</option>
                            <option value="2">💳 Tarjeta</option>
                        </select>
                    </div>
                    
                    <!-- Efectivo recibido -->
                    <div id="pagoEfectivoDiv" class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4">
                        <label class="block text-sm font-medium mb-2 flex items-center gap-2">
                            <i class="fas fa-money-bill-wave"></i>
                            Efectivo recibido
                        </label>
                        <input type="number" id="efectivoRecibido" step="0.01" placeholder="0.00"
                            class="w-full px-4 py-3 bg-white text-gray-800 rounded-lg focus:ring-2 focus:ring-purple-300 font-medium text-lg">
                        <div class="mt-3 flex justify-between items-center text-sm">
                            <span>Cambio:</span>
                            <span id="cambio" class="font-bold text-xl">$0.00</span>
                        </div>
                    </div>
                    
                    <!-- Botones de procesamiento -->
                    <div class="grid grid-cols-2 gap-2">
                        <button onclick="procesarVenta()" id="btnProcesar" 
                            class="bg-white text-purple-600 font-bold py-4 rounded-lg hover:bg-gray-100 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2" 
                            disabled>
                            <i class="fas fa-check-circle text-lg"></i>
                            <span class="text-sm">Procesar</span>
                        </button>
                        <button onclick="procesarVentaConTermica()" id="btnProcesarTermica" 
                            class="bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-4 rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2" 
                            disabled
                            title="Procesar e imprimir en impresora térmica">
                            <i class="fas fa-print text-lg"></i>
                            <span class="text-sm">+ Térmica</span>
                        </button>
                    </div>
                    
                    <!-- Total de artículos -->
                    <div class="text-center bg-white bg-opacity-10 backdrop-blur-sm rounded-lg py-3">
                        <p class="text-sm opacity-90">Total de artículos</p>
                        <p class="text-2xl font-bold" id="totalArticulos">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para productos a granel -->
<div id="modalGranel" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full overflow-hidden">
        <!-- Header del modal -->
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-balance-scale text-white text-lg"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Producto a Granel</h3>
            </div>
        </div>
        
        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                    <i class="fas fa-weight"></i>
                    Peso (gramos)
                </label>
                <select id="pesoGranel" class="w-full px-4 py-3 border-2 border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                    <option value="100">100g</option>
                    <option value="250">250g</option>
                    <option value="500">500g</option>
                    <option value="1000">1kg</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                    <i class="fas fa-shopping-bag"></i>
                    Cantidad de bolsas
                </label>
                <input type="number" id="cantidadBolsas" value="1" min="1" 
                    class="w-full px-4 py-3 border-2 border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
            </div>
            
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-4 rounded-xl border-2 border-purple-200">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Precio:</span>
                    <span id="precioGranelModal" class="text-lg font-bold text-purple-600">$0.00</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-purple-200">
                    <span class="text-sm font-medium text-gray-700">Subtotal:</span>
                    <span id="subtotalGranel" class="text-xl font-bold text-pink-600">$0.00</span>
                </div>
            </div>
            
            <div class="flex gap-3 pt-2">
                <button onclick="cerrarModalGranel()" 
                    class="flex-1 px-4 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-times"></i>
                    <span>Cancelar</span>
                </button>
                <button onclick="agregarGranelAlCarrito()" 
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-medium shadow-lg transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-cart-plus"></i>
                    <span>Agregar</span>
                </button>
            </div>
        </div>
    </div>

<style>
    /* Scrollbar personalizado */
    .scrollbar-thin {
        scrollbar-width: thin;
    }
    
    .scrollbar-thin::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    .scrollbar-thumb-purple-400::-webkit-scrollbar-thumb {
        background-color: rgb(192, 132, 252);
        border-radius: 10px;
    }
    
    .scrollbar-thumb-purple-400::-webkit-scrollbar-thumb:hover {
        background-color: rgb(168, 85, 247);
    }
    
    .scrollbar-track-purple-100::-webkit-scrollbar-track {
        background-color: rgb(243, 232, 255);
        border-radius: 10px;
    }
    
    /* Animaciones */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #carritoItems > div {
        animation: slideIn 0.3s ease-out;
    }
    
    /* Efectos hover mejorados */
    button:active {
        transform: scale(0.95);
    }
    
    /* Badge de stock con pulse */
    .bg-yellow-100 {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .8;
        }
    }
</style>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

<script>
    let carrito = [];
    let productos = [];
    let productosFiltrados = []; // Array para mantener los productos filtrados actualmente
    let categorias = [];
    let productoGranelSeleccionado = null;
    let usuarioActual = null;
    
    // Cargar datos iniciales
    async function inicializarPOS() {
        try {
            console.log('🚀 Inicializando POS...');
            
            // Obtener usuario actual (buscar en 'usuario' que es donde lo guarda el login)
            const userData = localStorage.getItem('usuario') || localStorage.getItem('user');
            console.log('📦 Datos de usuario en localStorage:', userData);
            
            if (userData) {
                usuarioActual = JSON.parse(userData);
                console.log('👤 Usuario actual parseado:', usuarioActual);
                console.log('🎭 Rol del usuario:', usuarioActual.rol_nombre);
                
                // Mostrar botón limpiar carrito solo para dueños
                // Verificar rol (normalizar comparación ignorando mayúsculas/minúsculas y acentos)
                const rolNormalizado = usuarioActual.rol_nombre ? 
                    usuarioActual.rol_nombre.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") : '';
                
                console.log('🔄 Rol normalizado:', rolNormalizado);
                console.log('🔍 Comparando con: "dueno" o "dueño"');
                
                if (rolNormalizado === 'dueno' || rolNormalizado === 'dueño') {
                    const btnLimpiar = document.getElementById('btnLimpiarCarrito');
                    console.log('🔘 Botón limpiar carrito encontrado:', btnLimpiar);
                    if (btnLimpiar) {
                        btnLimpiar.classList.remove('hidden');
                        console.log('✅ Usuario es dueño - botón limpiar carrito visible');
                    } else {
                        console.error('❌ ERROR: No se encontró el botón btnLimpiarCarrito');
                    }
                } else {
                    console.log('❌ Usuario NO es dueño - botón limpiar carrito oculto');
                    console.log('   Rol recibido:', usuarioActual.rol_nombre);
                    console.log('   Rol normalizado:', rolNormalizado);
                }
            } else {
                console.warn('⚠️ No se encontró usuario en localStorage');
                console.warn('💡 Sugerencia: Cierra sesión y vuelve a iniciar sesión');
            }
            
            // Cargar carrito guardado (persistencia)
            cargarCarritoGuardado();
            
            // Cargar categorías
            const catData = await apiRequest('/Dulcería/api/categorias.php');
            if (catData.success) {
                categorias = catData.data;
                const select = document.getElementById('filtroCategoria');
                catData.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id_categoria;
                    option.textContent = cat.nombre;
                    select.appendChild(option);
                });
            }
            
            // Cargar productos
            await buscarProductos();
            
        } catch (error) {
            console.error('Error al inicializar POS:', error);
            showNotification('Error al cargar datos', 'error');
        }
    }
    
    /**
     * Cargar carrito guardado desde localStorage
     */
    function cargarCarritoGuardado() {
        try {
            const carritoGuardado = localStorage.getItem('carrito_pos');
            if (carritoGuardado) {
                carrito = JSON.parse(carritoGuardado);
                console.log('Carrito cargado desde localStorage:', carrito);
                actualizarCarrito();
                showNotification('Carrito restaurado', 'info');
            }
        } catch (error) {
            console.error('Error al cargar carrito:', error);
            localStorage.removeItem('carrito_pos');
        }
    }
    
    /**
     * Guardar carrito en localStorage
     */
    function guardarCarrito() {
        try {
            localStorage.setItem('carrito_pos', JSON.stringify(carrito));
            console.log('Carrito guardado en localStorage');
        } catch (error) {
            console.error('Error al guardar carrito:', error);
        }
    }
    
    async function buscarProductos() {
        try {
            const data = await apiRequest('/Dulcería/api/productos.php?activo=1');
            if (data.success) {
                productos = data.data;
                aplicarFiltros();
            }
        } catch (error) {
            console.error('Error al buscar productos:', error);
        }
    }
    
    /**
     * Aplicar filtros de búsqueda y categoría
     */
    function aplicarFiltros() {
        const termino = document.getElementById('buscarProducto').value.toLowerCase();
        const categoriaId = document.getElementById('filtroCategoria').value;
        
        let filtrados = productos.filter(p => {
            // Filtro por texto (nombre, código UPC, categoría)
            const matchTexto = !termino || 
                              p.nombre.toLowerCase().includes(termino) || 
                              (p.upc && p.upc.toLowerCase().includes(termino)) ||
                              p.categoria_nombre.toLowerCase().includes(termino);
            
            // Filtro por categoría - convertir ambos a número para comparación exacta
            const matchCategoria = !categoriaId || categoriaId === '' || 
                                  parseInt(p.categoria_id) === parseInt(categoriaId);
            
            return matchTexto && matchCategoria;
        });
        
        mostrarResultados(filtrados);
    }
    
    function mostrarResultados(prods) {
        productosFiltrados = prods; // Guardar los productos filtrados para poder acceder por índice
        const container = document.getElementById('resultadosBusqueda');
        
        if (prods.length === 0) {
            container.innerHTML = '<p class="col-span-full text-center text-gray-400 py-8">No se encontraron productos</p>';
            return;
        }
        
        console.log('🔎 Mostrando resultados. Usuario actual:', usuarioActual);
        console.log('🎭 Rol del usuario:', usuarioActual ? usuarioActual.rol_nombre : 'No hay usuario');
        
        const esDueno = usuarioActual && usuarioActual.rol_nombre && 
            (usuarioActual.rol_nombre.toLowerCase() === 'dueño' || 
             usuarioActual.rol_nombre.toLowerCase() === 'dueno');
        
        console.log('👑 ¿Es dueño?', esDueno);
        
        let html = '';
        prods.forEach((p, index) => {
            let precio = parseFloat(p.precio_venta);
            if (p.es_temporada == 1 && parseFloat(p.precio_temporada) > precio) {
                precio = parseFloat(p.precio_temporada);
            }
            
            let precioTexto = (p.tipo_producto === 'granel' || p.tipo === 'granel') ? 'Desde $' : '$';
            precioTexto += precio.toLocaleString('es-MX', {minimumFractionDigits: 2});
            
            const imagenUrl = p.imagen_url || '/Dulcería/public/img/productos/default.png';
            
            // Stock badge - solo visible para dueño
            const stockBadge = esDueno ? `
                <span class="text-xs px-2 py-1 rounded-full whitespace-nowrap ${p.stock_actual > p.stock_minimo ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                    <i class="fas fa-box mr-1"></i>${p.stock_actual} ${p.unidad_medida}
                </span>
            ` : '';
            
            html += `
                <div class="bg-white border-2 border-gray-200 rounded-xl p-4 hover:border-purple-400 hover:shadow-lg transition-all cursor-pointer transform hover:-translate-y-1" 
                    onclick="seleccionarProductoPorIndice(${index})">
                    <div class="flex gap-3 items-start">
                        <img src="${imagenUrl}" alt="${p.nombre}" 
                            class="w-20 h-20 object-cover rounded-lg border-2 border-purple-100 shadow-sm" 
                            onerror="this.src='/Dulcería/public/img/productos/default.png'">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-800 truncate mb-1">${p.nombre}</h4>
                            <p class="text-xs text-gray-500 mb-2">
                                <i class="fas fa-tag mr-1"></i>${p.categoria_nombre}
                            </p>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-lg font-bold text-purple-600">${precioTexto}</p>
                                ${stockBadge}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function seleccionarProductoPorIndice(index) {
        const producto = productosFiltrados[index];
        if (!producto) {
            console.error('Producto no encontrado en índice:', index);
            return;
        }
        seleccionarProducto(producto);
    }
    
    /**
     * Procesar código de barras escaneado
     * Detecta si es un código con sufijo de peso (granel)
     */
    async function procesarCodigoBarras(codigo) {
        console.log('=== PROCESANDO CÓDIGO DE BARRAS ===');
        console.log('Código escaneado:', codigo);
        console.log('Total de productos en memoria:', productos.length);
        
        // Verificar si el código tiene sufijo de peso (últimos 2 dígitos: 01, 02, 03, 04)
        const sufijos = ['01', '02', '03', '04'];
        const ultimosDosDigitos = codigo.slice(-2);
        
        console.log('Últimos 2 dígitos:', ultimosDosDigitos);
        
        let codigoBase = codigo;
        let pesoGramos = null;
        
        if (sufijos.includes(ultimosDosDigitos)) {
            // Es un código con sufijo - producto a granel
            codigoBase = codigo.slice(0, -2);
            
            // Mapear sufijo a peso
            const mapaPesos = {
                '01': 100,
                '02': 250,
                '03': 500,
                '04': 1000
            };
            
            pesoGramos = mapaPesos[ultimosDosDigitos];
            console.log('✅ Código de granel detectado!');
            console.log('   Código base:', codigoBase);
            console.log('   Peso detectado:', pesoGramos, 'gramos');
        } else {
            console.log('ℹ️  Código sin sufijo - buscando como UPC normal');
        }
        
        // Buscar producto por UPC (código base)
        console.log('🔍 Buscando producto con UPC:', codigoBase);
        const producto = productos.find(p => p.upc === codigoBase);
        
        if (!producto) {
            console.error('❌ Producto NO encontrado con UPC:', codigoBase);
            console.log('📋 Productos disponibles:', productos.map(p => ({nombre: p.nombre, upc: p.upc})));
            showNotification('Producto no encontrado: ' + codigoBase, 'error');
            return;
        }
        
        console.log('✅ Producto encontrado:', {
            id: producto.id || producto.id_producto,
            nombre: producto.nombre,
            tipo: producto.tipo_producto || producto.tipo,
            upc: producto.upc
        });
        
        // Si es granel y detectamos peso, agregar directamente
        if ((producto.tipo_producto === 'granel' || producto.tipo === 'granel') && pesoGramos) {
            console.log('📦 Agregando producto a granel con peso:', pesoGramos);
            await agregarGranelDirecto(producto, pesoGramos);
        } else if ((producto.tipo_producto === 'granel' || producto.tipo === 'granel') && !pesoGramos) {
            // Granel sin sufijo - agregar automáticamente 1kg (código base = 1kg)
            console.log('� Código base detectado - agregando 1kg automáticamente');
            await agregarGranelDirecto(producto, 1000);
        } else {
            // Producto normal
            console.log('🛒 Agregando producto normal al carrito');
            agregarAlCarrito(producto, 1);
        }
        
        console.log('=== FIN PROCESAMIENTO ===\n');
    }
    
    /**
     * Agregar producto a granel directamente con peso específico
     */
    async function agregarGranelDirecto(producto, pesoGramos) {
        try {
            console.log('=== AGREGAR GRANEL DIRECTO ===');
            console.log('Producto:', producto);
            console.log('Peso:', pesoGramos);
            
            // FIX: Usar id o id_producto según lo que devuelva la API
            const productoId = producto.id_producto || producto.id;
            console.log('ID del producto:', productoId);
            
            const urlPrecio = `/Dulcería/api/productos.php/${productoId}/precio-granel?peso=${pesoGramos}`;
            console.log('🔗 Llamando API:', urlPrecio);
            
            // Obtener precio para ese peso específico
            const data = await apiRequest(urlPrecio);
            console.log('Respuesta API:', data);
            
            if (data.success) {
                const precio = parseFloat(data.data.precio);
                console.log('Precio obtenido:', precio);
                
                // Crear objeto temporal con el precio correcto
                const productoConPrecio = {
                    ...producto,
                    precio_venta: precio,
                    precio_temporada: producto.precio_temporada
                };
                
                console.log('Producto con precio:', productoConPrecio);
                agregarAlCarrito(productoConPrecio, 1, pesoGramos);
                
                showNotification(`${producto.nombre} ${pesoGramos}g agregado - $${precio.toFixed(2)}`, 'success');
            } else {
                console.error('❌ Error en respuesta API:', data.message);
                showNotification('Error: ' + (data.message || 'No se pudo obtener el precio'), 'error');
            }
        } catch (error) {
            console.error('❌ Error al obtener precio granel:', error);
            showNotification('Error al procesar producto a granel: ' + error.message, 'error');
        }
        console.log('=== FIN AGREGAR GRANEL DIRECTO ===\n');
    }
    
    function seleccionarProducto(producto) {
        if (producto.tipo_producto === 'granel') {
            abrirModalGranel(producto);
        } else {
            agregarAlCarrito(producto, 1);
        }
    }
    
    function abrirModalGranel(producto) {
        productoGranelSeleccionado = producto;
        document.getElementById('modalGranel').classList.remove('hidden');
        actualizarPrecioGranel();
    }
    
    function cerrarModalGranel() {
        document.getElementById('modalGranel').classList.add('hidden');
        productoGranelSeleccionado = null;
    }
    
    async function actualizarPrecioGranel() {
        const peso = parseInt(document.getElementById('pesoGranel').value);
        const cantidad = parseInt(document.getElementById('cantidadBolsas').value) || 1;
        
        try {
            // FIX: Usar id o id_producto según lo que devuelva la API
            const productoId = productoGranelSeleccionado.id_producto || productoGranelSeleccionado.id;
            
            const urlPrecio = `/Dulcería/api/productos.php/${productoId}/precio-granel?peso=${peso}`;
            console.log('🔗 Llamando API (modal):', urlPrecio);
            
            const data = await apiRequest(urlPrecio);
            if (data.success) {
                const precio = parseFloat(data.data.precio);
                document.getElementById('precioGranelModal').textContent = '$' + precio.toLocaleString('es-MX', {minimumFractionDigits: 2});
                document.getElementById('subtotalGranel').textContent = '$' + (precio * cantidad).toLocaleString('es-MX', {minimumFractionDigits: 2});
            }
        } catch (error) {
            console.error('Error al obtener precio granel:', error);
        }
    }
    
    async function agregarGranelAlCarrito() {
        const peso = parseInt(document.getElementById('pesoGranel').value);
        const cantidad = parseInt(document.getElementById('cantidadBolsas').value) || 1;
        
        // Obtener el precio correcto para este peso desde la API
        try {
            const productoId = productoGranelSeleccionado.id_producto || productoGranelSeleccionado.id;
            const urlPrecio = `/Dulcería/api/productos.php/${productoId}/precio-granel?peso=${peso}`;
            
            const data = await apiRequest(urlPrecio);
            
            if (data.success) {
                const precio = parseFloat(data.data.precio);
                
                // Crear objeto temporal con el precio correcto
                const productoConPrecio = {
                    ...productoGranelSeleccionado,
                    precio_venta: precio,
                    precio_temporada: productoGranelSeleccionado.precio_temporada
                };
                
                agregarAlCarrito(productoConPrecio, cantidad, peso);
                cerrarModalGranel();
                showNotification(`${productoGranelSeleccionado.nombre} ${peso}g agregado`, 'success');
            } else {
                showNotification('Error: ' + (data.message || 'No se pudo obtener el precio'), 'error');
            }
        } catch (error) {
            console.error('Error al obtener precio granel:', error);
            showNotification('Error al procesar producto a granel', 'error');
        }
    }
    
    function agregarAlCarrito(producto, cantidad = 1, peso = null) {
        console.log('=== AGREGAR AL CARRITO ===');
        console.log('Producto recibido:', producto);
        
        // FIX: La API devuelve 'id' pero necesitamos 'id_producto'
        const idProducto = producto.id_producto || producto.id;
        const tipoProducto = producto.tipo_producto || producto.tipo;
        
        console.log('ID del producto:', idProducto);
        console.log('Nombre del producto:', producto.nombre);
        console.log('Tipo del producto:', tipoProducto);
        console.log('Cantidad:', cantidad);
        console.log('Peso:', peso);
        console.log('Carrito antes de agregar:', JSON.parse(JSON.stringify(carrito)));
        
        // Verificar stock
        const stockDisponible = parseFloat(producto.stock_actual);
        
        // Calcular cantidad requerida en la misma unidad que el stock
        // Para granel: el stock está en kg, así que convertimos gramos a kg
        // Para otros productos: usamos la cantidad directamente
        let cantidadRequerida;
        if (peso) {
            // Producto a granel: convertir gramos a kg
            cantidadRequerida = (peso / 1000) * cantidad;
            console.log(`📊 Stock check granel: ${peso}g × ${cantidad} bolsas = ${cantidadRequerida} kg`);
        } else {
            // Producto normal: cantidad directa
            cantidadRequerida = cantidad;
            console.log(`📊 Stock check normal: ${cantidad} unidades`);
        }
        
        console.log(`📦 Stock disponible: ${stockDisponible} ${producto.unidad_medida || 'unidades'}`);
        console.log(`🛒 Stock requerido: ${cantidadRequerida} ${peso ? 'kg' : 'unidades'}`);
        
        // Calcular cuánto ya está en el carrito para este producto
        const enCarrito = carrito
            .filter(item => {
                if (peso) {
                    // Para granel, solo contar el mismo peso
                    return item.id_producto == idProducto && item.peso == peso;
                } else {
                    // Para normales, contar todos sin peso
                    return item.id_producto == idProducto && !item.peso;
                }
            })
            .reduce((total, item) => {
                if (item.peso) {
                    return total + ((item.peso / 1000) * item.cantidad);
                } else {
                    return total + item.cantidad;
                }
            }, 0);
        
        const totalRequerido = enCarrito + cantidadRequerida;
        
        console.log(`🔢 Ya en carrito: ${enCarrito}`);
        console.log(`🔢 Total requerido: ${totalRequerido}`);
        
        if (totalRequerido > stockDisponible) {
            const disponible = stockDisponible - enCarrito;
            showNotification(
                `Stock insuficiente. Disponible: ${disponible.toFixed(3)} ${peso ? 'kg' : 'unidades'}`, 
                'error'
            );
            return;
        }
        
        // Buscar si el producto ya está en el carrito
        const itemExistente = carrito.find(item => {
            console.log(`Comparando: item.id_producto (${item.id_producto}) == idProducto (${idProducto})`);
            console.log(`  item.peso: ${item.peso}, peso buscado: ${peso}`);
            
            if (peso) {
                // Para productos a granel, debe coincidir ID y peso
                const match = item.id_producto == idProducto && item.peso == peso;
                console.log(`  Granel match: ${match}`);
                return match;
            }
            // Para otros productos, solo el ID
            const match = item.id_producto == idProducto && !item.peso;
            console.log(`  Normal match: ${match} (item.peso: ${item.peso})`);
            return match;
        });
        
        console.log('Item existente encontrado:', itemExistente);
        
        if (itemExistente) {
            console.log('*** INCREMENTANDO cantidad del item existente ***');
            itemExistente.cantidad += cantidad;
            // No mostrar notificación aquí si viene desde agregarGranelDirecto o agregarGranelAlCarrito
            // porque ellos ya muestran su propia notificación
            if (!peso) {
                showNotification(`Cantidad actualizada: ${itemExistente.nombre}`, 'success');
            }
        } else {
            console.log('*** AGREGANDO nuevo item al carrito ***');
            let precio = parseFloat(producto.precio_venta);
            if (producto.es_temporada == 1 && parseFloat(producto.precio_temporada) > precio) {
                precio = parseFloat(producto.precio_temporada);
            }
            
            const nuevoItem = {
                id_producto: idProducto,
                nombre: producto.nombre,
                tipo: tipoProducto,
                cantidad: cantidad,
                peso: peso,
                precio_unitario: precio,
                stock_disponible: stockDisponible,
                imagen_url: producto.imagen_url || '/Dulcería/public/img/productos/default.png'
            };
            
            console.log('Nuevo item a agregar:', nuevoItem);
            carrito.push(nuevoItem);
            
            // No mostrar notificación aquí si viene desde agregarGranelDirecto o agregarGranelAlCarrito
            // porque ellos ya muestran su propia notificación
            if (!peso) {
                showNotification(`Producto agregado: ${producto.nombre}`, 'success');
            }
        }
        
        console.log('Carrito después de agregar:', JSON.parse(JSON.stringify(carrito)));
        console.log('=== FIN AGREGAR AL CARRITO ===\n');
        actualizarCarrito();
        guardarCarrito(); // Persistir cambios
    }
    
    function actualizarCarrito() {
        const container = document.getElementById('carritoItems');
        
        if (carrito.length === 0) {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full text-center">
                    <div class="w-24 h-24 bg-purple-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-bag text-purple-300 text-4xl"></i>
                    </div>
                    <p class="text-gray-400 font-medium">El carrito está vacío</p>
                    <p class="text-gray-400 text-sm mt-1">Agrega productos para comenzar</p>
                </div>
            `;
            document.getElementById('btnProcesar').disabled = true;
            document.getElementById('btnProcesarTermica').disabled = true;
            document.getElementById('totalVenta').textContent = '$0.00';
            document.getElementById('totalArticulos').textContent = '0';
            return;
        }
        
        // Verificar si es dueño para mostrar botón eliminar
        const esDueno = usuarioActual && usuarioActual.rol_nombre && 
            (usuarioActual.rol_nombre.toLowerCase() === 'dueño' || 
             usuarioActual.rol_nombre.toLowerCase() === 'dueno');
        
        let html = '';
        let total = 0;
        let totalArticulos = 0;
        
        carrito.forEach((item, index) => {
            let subtotal = item.precio_unitario * item.cantidad;
            
            if (item.tipo === 'granel') {
                subtotal = item.precio_unitario * item.cantidad; // El precio ya viene calculado para el peso
            }
            
            total += subtotal;
            totalArticulos += item.cantidad;
            
            let descripcion = item.nombre;
            if (item.peso) {
                descripcion += ` <span class="text-purple-600">(${item.peso}g)</span>`;
            }
            
            const imagenUrl = item.imagen_url || '/Dulcería/public/img/productos/default.png';
            
            // Botón eliminar - solo visible para dueño
            const btnEliminar = esDueno ? `
                <button onclick="eliminarItem(${index})" 
                    class="w-10 h-10 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition-colors flex items-center justify-center"
                    title="Eliminar producto">
                    <i class="fas fa-trash-alt"></i>
                </button>
            ` : '';
            
            html += `
                <div class="bg-gradient-to-r from-white to-purple-50 border-2 border-purple-200 rounded-xl p-3 hover:shadow-md transition-all">
                    <div class="flex items-center gap-3">
                        <img src="${imagenUrl}" alt="${item.nombre}" 
                            class="w-16 h-16 object-cover rounded-lg border-2 border-purple-300 shadow-sm"
                            onerror="this.src='/Dulcería/public/img/productos/default.png'">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-800">${descripcion}</h4>
                            <p class="text-sm text-purple-600">
                                <i class="fas fa-dollar-sign"></i>
                                ${item.precio_unitario.toLocaleString('es-MX', {minimumFractionDigits: 2})} c/u
                            </p>
                        </div>
                        <div class="flex items-center gap-2 bg-white rounded-lg shadow-sm p-1">
                            ${esDueno ? `
                                <button onclick="cambiarCantidad(${index}, -1)" 
                                    class="w-8 h-8 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition-colors flex items-center justify-center font-bold">
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                            ` : ''}
                            <span class="w-10 text-center font-bold text-gray-800">${item.cantidad}</span>
                            ${esDueno ? `
                                <button onclick="cambiarCantidad(${index}, 1)" 
                                    class="w-8 h-8 bg-green-100 text-green-600 hover:bg-green-200 rounded-lg transition-colors flex items-center justify-center font-bold">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            ` : ''}
                        </div>
                        <div class="text-right min-w-[90px]">
                            <p class="font-bold text-lg text-gray-800">$${subtotal.toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                        </div>
                        ${btnEliminar}
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        document.getElementById('totalVenta').textContent = '$' + total.toLocaleString('es-MX', {minimumFractionDigits: 2});
        document.getElementById('totalArticulos').textContent = totalArticulos;
        document.getElementById('btnProcesar').disabled = false;
        document.getElementById('btnProcesarTermica').disabled = false;
        
        actualizarCambio();
    }
    
    function cambiarCantidad(index, delta) {
        const item = carrito[index];
        const nuevaCantidad = item.cantidad + delta;
        
        if (nuevaCantidad <= 0) {
            eliminarItem(index);
            return;
        }
        
        // Calcular cantidad requerida correctamente según el tipo de producto
        let cantidadRequerida;
        if (item.peso) {
            // Producto a granel: convertir gramos a kg
            cantidadRequerida = (item.peso / 1000) * nuevaCantidad;
            console.log(`📊 Cambio cantidad granel: ${item.peso}g × ${nuevaCantidad} bolsas = ${cantidadRequerida} kg`);
        } else {
            // Producto normal: cantidad directa
            cantidadRequerida = nuevaCantidad;
            console.log(`📊 Cambio cantidad normal: ${nuevaCantidad} unidades`);
        }
        
        console.log(`📦 Stock disponible: ${item.stock_disponible}`);
        console.log(`🛒 Stock requerido: ${cantidadRequerida}`);
        
        if (cantidadRequerida > item.stock_disponible) {
            const disponible = item.stock_disponible;
            const maxBolsas = item.peso ? Math.floor(disponible / (item.peso / 1000)) : disponible;
            showNotification(
                `Stock insuficiente. Máximo: ${maxBolsas} ${item.peso ? 'bolsas' : 'unidades'}`, 
                'error'
            );
            return;
        }
        
        item.cantidad = nuevaCantidad;
        actualizarCarrito();
        guardarCarrito(); // Persistir cambios
    }
    
    function eliminarItem(index) {
        carrito.splice(index, 1);
        actualizarCarrito();
        guardarCarrito(); // Persistir cambios
        showNotification('Producto eliminado del carrito', 'info');
    }
    
    function limpiarCarrito() {
        if (confirm('⚠️ ¿Está seguro de limpiar todo el carrito?\n\nEsta acción no se puede deshacer.')) {
            carrito = [];
            actualizarCarrito();
            guardarCarrito(); // Persistir cambios
            showNotification('Carrito limpiado', 'success');
        }
    }
    
    function actualizarCambio() {
        const metodoPago = document.getElementById('metodoPago').value;
        const efectivoDiv = document.getElementById('pagoEfectivoDiv');
        
        if (metodoPago == '1') { // Efectivo
            efectivoDiv.style.display = 'block';
            
            const totalTexto = document.getElementById('totalVenta').textContent.replace('$', '').replace(',', '');
            const total = parseFloat(totalTexto) || 0;
            const efectivo = parseFloat(document.getElementById('efectivoRecibido').value) || 0;
            const cambio = efectivo - total;
            
            document.getElementById('cambio').textContent = '$' + (cambio >= 0 ? cambio : 0).toLocaleString('es-MX', {minimumFractionDigits: 2});
        } else {
            efectivoDiv.style.display = 'none';
        }
    }
    
    async function procesarVenta() {
        await procesarVentaInterno(false);
    }
    
    /**
     * Solo imprimir ticket térmico de preview (SIN guardar venta)
     */
    async function procesarVentaConTermica() {
        if (carrito.length === 0) {
            showNotification('El carrito está vacío', 'error');
            return;
        }
        
        const btn = document.getElementById('btnProcesarTermica');
        const btnProcesar = document.getElementById('btnProcesar');
        
        try {
            btn.disabled = true;
            btnProcesar.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i><span class="text-sm">Imprimiendo...</span>';
            
            // Obtener configuración de impresora
            const configData = await apiRequest('/Dulcería/api/configuracion.php/impresora');
            
            if (!configData.success || !configData.data.habilitada) {
                showNotification('⚠️ Impresora térmica no configurada', 'warning');
                return;
            }
            
            const nombreImpresora = configData.data.nombre_impresora;
            console.log('🖨️ Imprimiendo preview en:', nombreImpresora);
            
            // Validar que la impresora esté conectada
            const validacion = await apiRequest(
                `/Dulcería/api/validar-impresora.php?accion=validar&nombre=${encodeURIComponent(nombreImpresora)}`
            );
            
            if (!validacion.success || !validacion.conectada) {
                showNotification('❌ Impresora térmica no conectada: ' + nombreImpresora, 'error');
                return;
            }
            
            // Generar ticket de preview con datos del carrito
            const totalTexto = document.getElementById('totalVenta').textContent.replace('$', '').replace(',', '');
            const total = parseFloat(totalTexto) || 0;
            const metodoPago = document.getElementById('metodoPago');
            const metodoPagoNombre = metodoPago.options[metodoPago.selectedIndex].text;
            
            const productosFormateados = carrito.map(item => ({
                nombre: item.nombre,
                cantidad: item.cantidad,
                subtotal: item.subtotal,
                peso_gramos: item.peso || null
            }));
            
            // Enviar a imprimir preview
            const printData = await apiRequest('/Dulcería/api/imprimir-termica.php', {
                method: 'POST',
                body: JSON.stringify({
                    tipo: 'preview',
                    impresora: nombreImpresora,
                    datos: {
                        productos: productosFormateados,
                        total: total,
                        metodo_pago: metodoPagoNombre,
                        fecha: new Date().toISOString()
                    }
                })
            });
            
            if (printData.success) {
                showNotification('✅ Ticket de preview impreso. Procesa la venta cuando estés listo.', 'success');
            } else {
                showNotification('❌ Error al imprimir: ' + (printData.error || printData.mensaje), 'error');
            }
            
        } catch (error) {
            console.error('Error al imprimir preview:', error);
            showNotification('❌ Error al imprimir ticket de preview', 'error');
        } finally {
            btn.disabled = false;
            btnProcesar.disabled = false;
            btn.innerHTML = '<i class="fas fa-print text-lg"></i><span class="text-sm">+ Térmica</span>';
        }
    }
    
    async function procesarVentaInterno(imprimirTermica = false) {
        if (carrito.length === 0) {
            showNotification('El carrito está vacío', 'error');
            return;
        }
        
        const totalTexto = document.getElementById('totalVenta').textContent.replace('$', '').replace(',', '');
        const total = parseFloat(totalTexto) || 0;
        const metodoPago = document.getElementById('metodoPago').value;
        const efectivo = parseFloat(document.getElementById('efectivoRecibido').value) || 0;
        
        // Validar pago en efectivo
        if (metodoPago == '1' && efectivo < total) {
            showNotification('El efectivo recibido es insuficiente', 'error');
            return;
        }
        
        const productos = carrito.map(item => ({
            producto_id: item.id_producto,
            cantidad: item.cantidad,
            peso_gramos: item.peso || null,
            precio_unitario: item.precio_unitario
        }));
        
        const venta = {
            metodo_pago_id: parseInt(metodoPago),
            productos: productos
        };
        
        // Agregar pago_recibido si es efectivo
        if (metodoPago == '1') {
            venta.pago_recibido = efectivo;
        }
        
        try {
            const btn = document.getElementById('btnProcesar');
            const btnTermica = document.getElementById('btnProcesarTermica');
            
            btn.disabled = true;
            btnTermica.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin text-lg"></i><span class="text-sm">Procesando...</span>';
            
            const data = await apiRequest('/Dulcería/api/pos.php/venta', {
                method: 'POST',
                body: JSON.stringify(venta)
            });
            
            if (data.success) {
                showNotification('✅ Venta procesada correctamente', 'success');
                
                const ventaId = data.data.id;
                console.log('✅ Venta creada con ID:', ventaId);
                console.log('📄 Datos de la venta:', data.data);
                
                // SOLO imprimir ticket térmico (sin PDF)
                await imprimirTicketTermica(ventaId);
                
                // Limpiar carrito y localStorage
                carrito = [];
                localStorage.removeItem('carrito_pos');
                actualizarCarrito();
                document.getElementById('efectivoRecibido').value = '';
            } else {
                showNotification(data.message || 'Error al procesar la venta', 'error');
            }
            
        } catch (error) {
            console.error('Error al procesar venta:', error);
            showNotification('❌ Error al procesar la venta', 'error');
        } finally {
            const btn = document.getElementById('btnProcesar');
            const btnTermica = document.getElementById('btnProcesarTermica');
            
            btn.disabled = false;
            btnTermica.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle text-lg"></i><span class="text-sm">Procesar</span>';
        }
    }
    
    /**
     * Imprimir ticket en impresora térmica
     */
    async function imprimirTicketTermica(ventaId) {
        try {
            console.log('=== IMPRIMIR TICKET TÉRMICA ===');
            console.log('ID de venta:', ventaId);
            
            // Obtener configuración de impresora
            const configData = await apiRequest('/Dulcería/api/configuracion.php/impresora');
            
            if (!configData.success || !configData.data.habilitada) {
                showNotification('Impresora térmica no configurada', 'warning');
                return;
            }
            
            const config = configData.data;
            console.log('📋 Configuración impresora:', config);
            
            // VALIDAR que la impresora esté conectada
            console.log('🔍 Validando conexión de impresora...');
            const validacionData = await apiRequest(
                `/Dulcería/api/validar-impresora.php?accion=validar&nombre=${encodeURIComponent(config.nombre_impresora)}`
            );
            
            console.log('📄 Resultado validación:', validacionData);
            
            if (!validacionData.success || !validacionData.conectada) {
                const mensaje = validacionData.sugerencia 
                    ? `Impresora '${config.nombre_impresora}' no conectada.\n\n${validacionData.sugerencia}` 
                    : `Impresora '${config.nombre_impresora}' no conectada.`;
                
                showNotification(mensaje, 'error');
                console.error('❌ Impresora no conectada');
                return;
            }
            
            console.log('✅ Impresora conectada y disponible');
            
            // Llamar API de impresión térmica
            const url = '/Dulcería/api/imprimir-termica.php';
            console.log('🔗 Llamando API térmica:', url);
            
            const data = await apiRequest(url, {
                method: 'POST',
                body: JSON.stringify({
                    tipo: 'ticket',
                    venta_id: ventaId,
                    impresora: config.nombre_impresora
                })
            });
            
            console.log('📄 Respuesta API térmica:', data);
            
            if (data.success) {
                console.log('✅ Ticket térmico enviado correctamente');
                showNotification('Ticket enviado a impresora térmica', 'success');
            } else {
                console.error('❌ Error al imprimir en térmica:', data.error);
                showNotification('Error al imprimir en térmica: ' + (data.error || data.message), 'error');
            }
            
            console.log('=== FIN IMPRIMIR TICKET TÉRMICA ===\n');
            
        } catch (error) {
            console.error('❌ Error al imprimir ticket térmico:', error);
            showNotification('Error al imprimir en térmica: ' + error.message, 'error');
        }
    }
    
    async function imprimirTicket(idVenta, copias = 1) {
        try {
            console.log('=== IMPRIMIR TICKET ===');
            console.log('ID de venta:', idVenta);
            console.log('Número de copias:', copias);
            
            const url = `/Dulcería/api/tickets.php/${idVenta}`;
            console.log('🔗 Llamando API:', url);
            
            const data = await apiRequest(url);
            console.log('📄 Respuesta API tickets:', data);
            
            if (data.success) {
                console.log('✅ Ticket generado exitosamente');
                console.log('📁 Ruta PDF:', data.data.pdf_path);
                console.log('🎫 Folio:', data.data.folio);
                
                // Imprimir las copias solicitadas
                for (let i = 0; i < copias; i++) {
                    const ventana = window.open('', '_blank');
                    ventana.document.write(data.data.ticket_html);
                    ventana.document.close();
                    
                    // Esperar un momento antes de imprimir
                    setTimeout(() => {
                        ventana.print();
                        if (i === copias - 1) {
                            // Cerrar la última ventana después de imprimir
                            setTimeout(() => ventana.close(), 500);
                        }
                    }, 500 * i);
                }
                
                console.log('=== FIN IMPRIMIR TICKET ===\n');
            } else {
                console.error('❌ Error al generar ticket:', data.message);
                showNotification('Error al generar ticket: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('❌ Error al imprimir ticket:', error);
            showNotification('Error al imprimir ticket', 'error');
        }
    }
    
    // Event listeners
    let timeoutBusqueda;
    let codigoBarrasBuffer = '';
    let ultimaTecla = Date.now();
    
    /**
     * Detectar escaneo de código de barras
     * Los escáneres escriben muy rápido y terminan con Enter
     */
    document.getElementById('buscarProducto').addEventListener('keydown', function(e) {
        const ahora = Date.now();
        
        // Si pasan más de 100ms entre teclas, reiniciar buffer
        if (ahora - ultimaTecla > 100) {
            codigoBarrasBuffer = '';
        }
        
        ultimaTecla = ahora;
        
        // Detectar Enter (fin de escaneo)
        if (e.key === 'Enter') {
            e.preventDefault();
            const valor = this.value.trim();
            
            if (valor.length >= 8) {
                // Parece un código de barras (mínimo 8 caracteres)
                console.log('Código de barras detectado:', valor);
                procesarCodigoBarras(valor);
                this.value = ''; // Limpiar campo
                codigoBarrasBuffer = '';
            } else {
                // Es una búsqueda normal
                aplicarFiltros();
            }
        } else if (e.key.length === 1) {
            // Acumular caracteres
            codigoBarrasBuffer += e.key;
        }
    });
    
    /**
     * Aplicar filtros cuando el usuario escribe (con delay)
     */
    document.getElementById('buscarProducto').addEventListener('input', function(e) {
        clearTimeout(timeoutBusqueda);
        
        // Esperar 300ms después de que el usuario deje de escribir
        timeoutBusqueda = setTimeout(() => {
            aplicarFiltros();
        }, 300);
    });
    
    /**
     * Filtro por categoría
     */
    document.getElementById('filtroCategoria').addEventListener('change', function() {
        aplicarFiltros();
    });
    
    document.getElementById('metodoPago').addEventListener('change', actualizarCambio);
    document.getElementById('efectivoRecibido').addEventListener('input', actualizarCambio);
    
    document.getElementById('pesoGranel').addEventListener('change', actualizarPrecioGranel);
    document.getElementById('cantidadBolsas').addEventListener('input', actualizarPrecioGranel);
    
    // Inicializar
    inicializarPOS();
</script>
