<?php
$pageTitle = 'Gestión de Inventario';
ob_start();
?>

<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Total productos</p>
                    <p id="totalProductos" class="text-3xl font-bold mt-2">0</p>
                </div>
                <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Stock adecuado</p>
                    <p id="stockAdecuado" class="text-3xl font-bold mt-2">0</p>
                </div>
                <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Stock bajo</p>
                    <p id="stockBajo" class="text-3xl font-bold mt-2">0</p>
                </div>
                <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-lg shadow-md p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Sin stock</p>
                    <p id="sinStock" class="text-3xl font-bold mt-2">0</p>
                </div>
                <svg class="w-12 h-12 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Inventario</h2>
            <button onclick="abrirModalAjuste()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-medium">
                + Ajuste de inventario
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" id="buscarProducto" placeholder="Buscar producto..." 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            
            <select id="filtroCategoria" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">Todas las categorías</option>
            </select>
            
            <select id="filtroStock" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">Todos</option>
                <option value="adecuado">Stock adecuado</option>
                <option value="bajo">Stock bajo</option>
                <option value="sin">Sin stock</option>
            </select>
        </div>
    </div>
    
    <!-- Tabla de inventario -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Categoría</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock actual</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Stock mínimo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaInventario" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            <div class="logo-spinner-container">
                                <div class="relative">
                                    <img src="/Dulcería/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-12 w-12 object-contain">
                                </div>
                                <p class="mt-2">Cargando inventario...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de historial -->
<div id="modalHistorial" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 rounded-t-lg flex items-center justify-between">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-history"></i>
                <span id="tituloHistorial">Historial de movimientos</span>
            </h3>
            <button onclick="cerrarModalHistorial()" class="text-white hover:text-gray-200">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
            <div id="contenidoHistorial" class="space-y-3">
                <div class="logo-spinner-container py-10">
                    <div class="relative">
                        <img src="/Dulcería/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-12 w-12 object-contain mx-auto">
                    </div>
                    <p class="text-gray-600 mt-2">Cargando historial...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalAjuste" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 rounded-t-lg">
            <h3 class="text-xl font-bold text-white">Ajuste de inventario</h3>
        </div>
        
        <form id="formAjuste" class="p-6">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Producto</label>
                    <select id="productoAjuste" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Seleccione un producto...</option>
                    </select>
                </div>
                
                <div id="stockActualInfo" class="hidden bg-blue-50 p-3 rounded-lg">
                    <p class="text-sm text-gray-600">Stock actual: <span id="stockActualValor" class="font-bold text-blue-600">0</span></p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de movimiento</label>
                    <select id="tipoMovimiento" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="entrada">Entrada (agregar stock)</option>
                        <option value="salida">Salida (reducir stock)</option>
                        <option value="ajuste">Ajuste (establecer cantidad exacta)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad</label>
                    <input type="number" id="cantidad" step="0.01" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Motivo</label>
                    <textarea id="motivo" rows="2" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="cerrarModalAjuste()" 
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-1 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700">
                    Guardar ajuste
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

<script>
    let productos = [];
    let categorias = [];
    
    async function cargarDatos() {
        try {
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
            await cargarInventario();
            
        } catch (error) {
            console.error('Error al cargar datos:', error);
            showNotification('Error al cargar datos', 'error');
        }
    }
    
    async function cargarInventario() {
        try {
            const data = await apiRequest('/Dulcería/api/productos.php');
            if (data.success) {
                productos = data.data;
                actualizarEstadisticas();
                aplicarFiltros();
                
                // Cargar productos en select de ajuste
                const select = document.getElementById('productoAjuste');
                select.innerHTML = '<option value="">Seleccione un producto...</option>';
                data.data.forEach(p => {
                    const option = document.createElement('option');
                    option.value = p.id; // Usar p.id en lugar de p.id_producto
                    option.textContent = `${p.nombre} (${p.stock_actual} ${p.unidad_medida})`;
                    option.dataset.stock = p.stock_actual;
                    option.dataset.unidad = p.unidad_medida;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error al cargar inventario:', error);
        }
    }
    
    function actualizarEstadisticas() {
        const total = productos.length;
        const sinStock = productos.filter(p => parseFloat(p.stock_actual) === 0).length;
        const stockBajo = productos.filter(p => parseFloat(p.stock_actual) > 0 && parseFloat(p.stock_actual) <= parseFloat(p.stock_minimo)).length;
        const adecuado = total - sinStock - stockBajo;
        
        document.getElementById('totalProductos').textContent = total;
        document.getElementById('sinStock').textContent = sinStock;
        document.getElementById('stockBajo').textContent = stockBajo;
        document.getElementById('stockAdecuado').textContent = adecuado;
    }
    
    function aplicarFiltros() {
        const busqueda = document.getElementById('buscarProducto').value.toLowerCase();
        const categoria = document.getElementById('filtroCategoria').value;
        const filtroStock = document.getElementById('filtroStock').value;
        
        let filtrados = productos.filter(p => {
            const matchBusqueda = p.nombre.toLowerCase().includes(busqueda) || 
                                  (p.upc && p.upc.toLowerCase().includes(busqueda));
            const matchCategoria = !categoria || p.categoria_id == categoria;
            
            let matchStock = true;
            if (filtroStock === 'sin') {
                matchStock = parseFloat(p.stock_actual) === 0;
            } else if (filtroStock === 'bajo') {
                matchStock = parseFloat(p.stock_actual) > 0 && parseFloat(p.stock_actual) <= parseFloat(p.stock_minimo);
            } else if (filtroStock === 'adecuado') {
                matchStock = parseFloat(p.stock_actual) > parseFloat(p.stock_minimo);
            }
            
            return matchBusqueda && matchCategoria && matchStock;
        });
        
        mostrarInventario(filtrados);
    }
    
    function mostrarInventario(prods) {
        const tbody = document.getElementById('tablaInventario');
        
        if (prods.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No se encontraron productos</td></tr>';
            return;
        }
        
        let html = '';
        prods.forEach(p => {
            const stockActual = parseFloat(p.stock_actual);
            const stockMinimo = parseFloat(p.stock_minimo);
            
            let estadoBadge = '';
            let stockClass = '';
            
            if (stockActual === 0) {
                estadoBadge = '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Sin stock</span>';
                stockClass = 'text-red-600 font-bold';
            } else if (stockActual <= stockMinimo) {
                estadoBadge = '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Stock bajo</span>';
                stockClass = 'text-yellow-600 font-semibold';
            } else {
                estadoBadge = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Adecuado</span>';
                stockClass = 'text-green-600';
            }
            
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900">${p.nombre}</div>
                        <div class="text-xs text-gray-500">${p.upc}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900 hidden md:table-cell">${p.categoria_nombre}</td>
                    <td class="px-4 py-3 text-sm ${stockClass}">${stockActual} ${p.unidad_medida}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 hidden lg:table-cell">${stockMinimo} ${p.unidad_medida}</td>
                    <td class="px-4 py-3">${estadoBadge}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button onclick="ajustarRapido(${p.id}, '${p.nombre}')" 
                                class="text-purple-600 hover:text-purple-700" title="Ajustar inventario">
                                <i class="fas fa-boxes"></i>
                            </button>
                            <button onclick="verHistorial(${p.id}, '${p.nombre}')" 
                                class="text-blue-600 hover:text-blue-700" title="Ver historial">
                                <i class="fas fa-history"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function ajustarRapido(idProducto, nombre) {
        document.getElementById('productoAjuste').value = idProducto;
        document.getElementById('productoAjuste').dispatchEvent(new Event('change'));
        abrirModalAjuste();
    }
    
    function abrirModalAjuste() {
        document.getElementById('modalAjuste').classList.remove('hidden');
    }
    
    function cerrarModalAjuste() {
        document.getElementById('modalAjuste').classList.add('hidden');
        document.getElementById('formAjuste').reset();
        document.getElementById('stockActualInfo').classList.add('hidden');
    }
    
    async function verHistorial(idProducto, nombreProducto) {
        document.getElementById('tituloHistorial').innerHTML = `
            <span>Historial: ${nombreProducto}</span>
        `;
        document.getElementById('modalHistorial').classList.remove('hidden');
        
        // Mostrar spinner
        document.getElementById('contenidoHistorial').innerHTML = `
            <div class="logo-spinner-container py-10">
                <div class="relative">
                    <img src="/Dulcería/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-12 w-12 object-contain mx-auto">
                </div>
                <p class="text-gray-600 mt-2">Cargando historial...</p>
            </div>
        `;
        
        try {
            const data = await apiRequest(`/Dulcería/api/productos.php/${idProducto}/historial`);
            
            if (data.success && data.data.length > 0) {
                let html = '<div class="space-y-3">';
                
                data.data.forEach(mov => {
                    const fecha = new Date(mov.created_at).toLocaleString('es-MX', {
                        dateStyle: 'medium',
                        timeStyle: 'short'
                    });
                    
                    let tipoIcon = '';
                    let tipoClass = '';
                    let tipoTexto = '';
                    
                    switch(mov.tipo_movimiento) {
                        case 'entrada':
                            tipoIcon = 'fa-arrow-up';
                            tipoClass = 'bg-green-100 text-green-800';
                            tipoTexto = 'Entrada';
                            break;
                        case 'salida':
                            tipoIcon = 'fa-arrow-down';
                            tipoClass = 'bg-red-100 text-red-800';
                            tipoTexto = 'Salida';
                            break;
                        case 'ajuste':
                            tipoIcon = 'fa-sync-alt';
                            tipoClass = 'bg-blue-100 text-blue-800';
                            tipoTexto = 'Ajuste';
                            break;
                        case 'venta':
                            tipoIcon = 'fa-shopping-cart';
                            tipoClass = 'bg-purple-100 text-purple-800';
                            tipoTexto = 'Venta';
                            break;
                        case 'cancelacion':
                            tipoIcon = 'fa-undo';
                            tipoClass = 'bg-orange-100 text-orange-800';
                            tipoTexto = 'Cancelación';
                            break;
                    }
                    
                    html += `
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${tipoClass}">
                                            <i class="fas ${tipoIcon}"></i> ${tipoTexto}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            <i class="fas fa-clock"></i> ${fecha}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-3 gap-4 mb-3">
                                        <div>
                                            <p class="text-xs text-gray-500">Cantidad</p>
                                            <p class="font-bold text-lg ${mov.tipo_movimiento === 'entrada' ? 'text-green-600' : 'text-red-600'}">
                                                ${mov.tipo_movimiento === 'entrada' ? '+' : ''}${parseFloat(mov.cantidad).toFixed(2)}
                                            </p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Stock anterior</p>
                                            <p class="font-semibold text-gray-700">${parseFloat(mov.stock_anterior).toFixed(2)}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Stock nuevo</p>
                                            <p class="font-semibold text-gray-700">${parseFloat(mov.stock_nuevo).toFixed(2)}</p>
                                        </div>
                                    </div>
                                    
                                    ${mov.justificacion ? `
                                        <div class="bg-gray-50 rounded p-2 mb-2">
                                            <p class="text-xs text-gray-500 mb-1"><i class="fas fa-comment"></i> Motivo:</p>
                                            <p class="text-sm text-gray-700">${mov.justificacion}</p>
                                        </div>
                                    ` : ''}
                                    
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-user"></i> ${mov.usuario_nombre}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                document.getElementById('contenidoHistorial').innerHTML = html;
                
            } else {
                document.getElementById('contenidoHistorial').innerHTML = `
                    <div class="text-center py-10">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No hay movimientos registrados para este producto</p>
                    </div>
                `;
            }
            
        } catch (error) {
            console.error('Error al cargar historial:', error);
            document.getElementById('contenidoHistorial').innerHTML = `
                <div class="text-center py-10 text-red-600">
                    <i class="fas fa-exclamation-triangle text-6xl mb-4"></i>
                    <p>Error al cargar el historial</p>
                </div>
            `;
        }
    }
    
    function cerrarModalHistorial() {
        document.getElementById('modalHistorial').classList.add('hidden');
    }
    
    document.getElementById('productoAjuste').addEventListener('change', function(e) {
        const option = e.target.selectedOptions[0];
        if (option.value) {
            const stock = option.dataset.stock;
            const unidad = option.dataset.unidad;
            document.getElementById('stockActualValor').textContent = `${stock} ${unidad}`;
            document.getElementById('stockActualInfo').classList.remove('hidden');
        } else {
            document.getElementById('stockActualInfo').classList.add('hidden');
        }
    });
    
    document.getElementById('formAjuste').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const movimiento = {
            id_producto: parseInt(document.getElementById('productoAjuste').value),
            tipo_movimiento: document.getElementById('tipoMovimiento').value,
            cantidad: parseFloat(document.getElementById('cantidad').value),
            motivo: document.getElementById('motivo').value
        };
        
        try {
            const data = await apiRequest('/Dulcería/api/productos.php/movimiento-inventario', {
                method: 'POST',
                body: JSON.stringify(movimiento)
            });
            
            if (data.success) {
                showNotification('Ajuste de inventario realizado', 'success');
                cerrarModalAjuste();
                await cargarInventario();
            } else {
                showNotification(data.message || 'Error al realizar ajuste', 'error');
            }
            
        } catch (error) {
            console.error('Error al realizar ajuste:', error);
            showNotification('Error al realizar ajuste', 'error');
        }
    });
    
    // Event listeners
    document.getElementById('buscarProducto').addEventListener('input', aplicarFiltros);
    document.getElementById('filtroCategoria').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroStock').addEventListener('change', aplicarFiltros);
    
    // Inicializar
    cargarDatos();
</script>
