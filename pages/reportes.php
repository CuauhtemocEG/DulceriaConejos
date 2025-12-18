<?php
$pageTitle = 'Reportes';
ob_start();
?>

<div class="space-y-6">
    <!-- Selector de reporte -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Reportes y Análisis</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <button onclick="mostrarReporte('ventas')" 
                class="p-4 border-2 border-purple-600 rounded-lg hover:bg-purple-50 transition text-left">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Ventas</p>
                        <p class="text-xs text-gray-500">Análisis por periodo</p>
                    </div>
                </div>
            </button>
            
            <button onclick="mostrarReporte('productos')" 
                class="p-4 border-2 border-green-600 rounded-lg hover:bg-green-50 transition text-left">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-green-100 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Productos</p>
                        <p class="text-xs text-gray-500">Top/menos vendidos</p>
                    </div>
                </div>
            </button>
            
            <button onclick="mostrarReporte('inventario')" 
                class="p-4 border-2 border-yellow-600 rounded-lg hover:bg-yellow-50 transition text-left">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-yellow-100 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Inventario</p>
                        <p class="text-xs text-gray-500">Stock bajo</p>
                    </div>
                </div>
            </button>
            
            <button onclick="mostrarReporte('corte')" 
                class="p-4 border-2 border-red-600 rounded-lg hover:bg-red-50 transition text-left">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-red-100 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">Corte de Caja</p>
                        <p class="text-xs text-gray-500">Cierre de turno</p>
                    </div>
                </div>
            </button>
        </div>
    </div>
    
    <!-- Reporte de Ventas -->
    <div id="reporteVentas" class="hidden space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Reporte de Ventas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <select id="periodoVentas" onchange="cargarReporteVentas()" 
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="diario">Hoy</option>
                    <option value="semanal">Esta semana</option>
                    <option value="mensual">Este mes</option>
                </select>
                
                <input type="date" id="fechaVentas" onchange="cargarReporteVentas()"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                
                <button onclick="exportarReporte('ventas')" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Exportar
                </button>
            </div>
            
            <div id="resumenVentas" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6"></div>
            
            <div id="tablaVentas" class="overflow-x-auto"></div>
        </div>
    </div>
    
    <!-- Reporte de Productos -->
    <div id="reporteProductos" class="hidden space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Reporte de Productos</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <select id="tipoProductos" onchange="cargarReporteProductos()"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    <option value="top">Más vendidos</option>
                    <option value="menos">Menos vendidos</option>
                </select>
                
                <input type="number" id="limiteProductos" value="10" min="5" max="50" onchange="cargarReporteProductos()"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                    placeholder="Cantidad a mostrar">
            </div>
            
            <div id="tablaProductos" class="overflow-x-auto"></div>
        </div>
    </div>
    
    <!-- Reporte de Inventario -->
    <div id="reporteInventario" class="hidden space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Reporte de Stock Bajo</h3>
            <div id="tablaStockBajo" class="overflow-x-auto"></div>
        </div>
    </div>
    
    <!-- Corte de Caja -->
    <div id="reporteCorte" class="hidden space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Corte de Caja</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <input type="datetime-local" id="fechaInicioCorte"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                
                <input type="datetime-local" id="fechaFinCorte"
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
            </div>
            
            <button onclick="generarCorte()" 
                class="w-full md:w-auto px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white rounded-lg hover:from-red-700 hover:to-pink-700 font-medium mb-6">
                Generar Corte de Caja
            </button>
            
            <div id="resultadoCorte"></div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

<script>
    let reporteActual = null;
    
    function mostrarReporte(tipo) {
        // Ocultar todos
        document.getElementById('reporteVentas').classList.add('hidden');
        document.getElementById('reporteProductos').classList.add('hidden');
        document.getElementById('reporteInventario').classList.add('hidden');
        document.getElementById('reporteCorte').classList.add('hidden');
        
        reporteActual = tipo;
        
        // Mostrar el seleccionado
        switch(tipo) {
            case 'ventas':
                document.getElementById('reporteVentas').classList.remove('hidden');
                document.getElementById('fechaVentas').value = new Date().toISOString().split('T')[0];
                cargarReporteVentas();
                break;
            case 'productos':
                document.getElementById('reporteProductos').classList.remove('hidden');
                cargarReporteProductos();
                break;
            case 'inventario':
                document.getElementById('reporteInventario').classList.remove('hidden');
                cargarReporteInventario();
                break;
            case 'corte':
                document.getElementById('reporteCorte').classList.remove('hidden');
                const ahora = new Date();
                const inicio = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate(), 0, 0);
                document.getElementById('fechaInicioCorte').value = inicio.toISOString().slice(0, 16);
                document.getElementById('fechaFinCorte').value = ahora.toISOString().slice(0, 16);
                break;
        }
    }
    
    async function cargarReporteVentas() {
        try {
            const periodo = document.getElementById('periodoVentas').value;
            const fecha = document.getElementById('fechaVentas').value;
            
            const data = await apiRequest(`/DulceriaConejos/api/reportes.php/ventas?periodo=${periodo}&fecha=${fecha}`);
            
            if (data.success) {
                const resumen = data.data.resumen;
                const metodosPago = data.data.metodos_pago || [];
                
                // Resumen de ventas
                let htmlResumen = `
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg text-white">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm opacity-90">Total Ventas</p>
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold">$${(resumen.total_ventas || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm opacity-90">Número de Ventas</p>
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold">${resumen.num_ventas || 0}</p>
                        <p class="text-xs opacity-75 mt-1">${resumen.ventas_canceladas || 0} canceladas</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm opacity-90">Ticket Promedio</p>
                            <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <p class="text-3xl font-bold">$${(resumen.promedio_venta || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                    </div>
                `;
                
                document.getElementById('resumenVentas').innerHTML = htmlResumen;
                
                // Tabla de métodos de pago
                let htmlMetodos = '';
                if (metodosPago.length > 0) {
                    htmlMetodos = `
                        <div class="mt-6 bg-gradient-to-br from-amber-50 to-orange-50 p-6 rounded-xl border border-amber-200">
                            <h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                Ventas por Método de Pago
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-${Math.min(metodosPago.length, 3)} gap-4">
                    `;
                    
                    metodosPago.forEach(metodo => {
                        const porcentaje = resumen.total_ventas > 0 ? (metodo.total / resumen.total_ventas * 100).toFixed(1) : 0;
                        htmlMetodos += `
                            <div class="bg-white p-4 rounded-lg shadow-sm border border-amber-100">
                                <p class="text-sm text-gray-600 mb-1">${metodo.metodo_pago}</p>
                                <p class="text-2xl font-bold text-amber-600">$${parseFloat(metodo.total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                                <div class="mt-2">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-amber-500 h-2 rounded-full" style="width: ${porcentaje}%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">${porcentaje}% del total • ${metodo.num_ventas} ventas</p>
                                </div>
                            </div>
                        `;
                    });
                    
                    htmlMetodos += `
                            </div>
                        </div>
                    `;
                }
                
                document.getElementById('tablaVentas').innerHTML = htmlMetodos;
            }
        } catch (error) {
            console.error('Error al cargar reporte:', error);
            showNotification('Error al cargar reporte de ventas', 'error');
        }
    }
    
    async function cargarReporteProductos() {
        try {
            const tipo = document.getElementById('tipoProductos').value;
            const limite = document.getElementById('limiteProductos').value;
            
            const endpoint = tipo === 'top' ? 'productos-top' : 'productos-menos-vendidos';
            const data = await apiRequest(`/DulceriaConejos/api/reportes.php/${endpoint}?limite=${limite}`);
            
            if (data.success) {
                const productos = data.data;
                
                if (productos.length === 0) {
                    document.getElementById('tablaProductos').innerHTML = `
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-gray-500 text-lg font-medium">No hay datos de ventas disponibles</p>
                            <p class="text-gray-400 text-sm mt-2">Realiza algunas ventas para ver estadísticas</p>
                        </div>
                    `;
                    return;
                }
                
                const colorClass = tipo === 'top' ? 'from-green-500 to-emerald-600' : 'from-red-500 to-rose-600';
                const iconColor = tipo === 'top' ? 'text-green-600' : 'text-red-600';
                
                let html = `
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                `;
                
                productos.forEach((p, index) => {
                    const posicion = index + 1;
                    const medalla = posicion === 1 ? '🥇' : posicion === 2 ? '🥈' : posicion === 3 ? '🥉' : `#${posicion}`;
                    
                    html += `
                        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow border border-gray-100 overflow-hidden">
                            <div class="bg-gradient-to-r ${colorClass} px-4 py-2 flex items-center justify-between">
                                <span class="text-white font-bold text-lg">${medalla}</span>
                                <span class="text-white text-xs opacity-90">${p.categoria}</span>
                            </div>
                            <div class="p-4">
                                <h4 class="font-bold text-gray-800 mb-2 line-clamp-2">${p.nombre}</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Ventas:</span>
                                        <span class="font-semibold ${iconColor}">${p.num_ventas}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Cantidad:</span>
                                        <span class="font-semibold">${parseFloat(p.cantidad_total).toFixed(2)}</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2 border-t">
                                        <span class="text-gray-600 font-medium">Total:</span>
                                        <span class="font-bold text-lg ${iconColor}">$${parseFloat(p.total_vendido).toLocaleString('es-MX', {minimumFractionDigits: 2})}</span>
                                    </div>
                                    ${p.stock_actual !== null ? `
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-gray-500">Stock:</span>
                                            <span class="text-gray-700">${parseFloat(p.stock_actual).toFixed(2)}</span>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                document.getElementById('tablaProductos').innerHTML = html;
            }
        } catch (error) {
            console.error('Error al cargar reporte:', error);
            showNotification('Error al cargar reporte de productos', 'error');
        }
    }
    
    async function cargarReporteInventario() {
        try {
            const data = await apiRequest('/DulceriaConejos/api/reportes.php/stock-bajo');
            
            if (data.success) {
                const productos = data.data;
                
                if (productos.length === 0) {
                    document.getElementById('tablaStockBajo').innerHTML = `
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-green-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-green-600 text-lg font-medium">¡Excelente! No hay productos con stock bajo</p>
                            <p class="text-gray-400 text-sm mt-2">Todos los productos tienen inventario suficiente</p>
                        </div>
                    `;
                    return;
                }
                
                let html = `
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="font-bold text-red-800">¡Atención! ${productos.length} producto(s) con stock bajo</p>
                                <p class="text-sm text-red-600">Se recomienda realizar un pedido pronto</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                `;
                
                productos.forEach(p => {
                    const porcentajeStock = p.stock_minimo > 0 ? (p.stock_actual / p.stock_minimo * 100) : 0;
                    const nivelUrgencia = porcentajeStock < 25 ? 'critical' : porcentajeStock < 50 ? 'warning' : 'low';
                    
                    const colorClass = {
                        'critical': 'from-red-500 to-red-600',
                        'warning': 'from-orange-500 to-orange-600',
                        'low': 'from-yellow-500 to-yellow-600'
                    }[nivelUrgencia];
                    
                    const badgeText = {
                        'critical': '⚠️ Crítico',
                        'warning': '⚡ Urgente',
                        'low': '📊 Bajo'
                    }[nivelUrgencia];
                    
                    html += `
                        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                            <div class="bg-gradient-to-r ${colorClass} px-4 py-2">
                                <span class="text-white font-bold text-sm">${badgeText}</span>
                            </div>
                            <div class="p-4">
                                <h4 class="font-bold text-gray-800 mb-3">${p.nombre}</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Stock actual:</span>
                                        <span class="font-bold text-red-600">${parseFloat(p.stock_actual).toFixed(2)} ${p.unidad_medida}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Stock mínimo:</span>
                                        <span class="font-semibold text-gray-700">${parseFloat(p.stock_minimo).toFixed(2)} ${p.unidad_medida}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm pt-2 border-t">
                                        <span class="text-gray-600">Faltante:</span>
                                        <span class="font-bold text-orange-600">${parseFloat(p.cantidad_faltante).toFixed(2)} ${p.unidad_medida}</span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-gradient-to-r ${colorClass} h-2 rounded-full transition-all" style="width: ${Math.min(porcentajeStock, 100)}%"></div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 text-right">${porcentajeStock.toFixed(0)}% del stock mínimo</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                document.getElementById('tablaStockBajo').innerHTML = html;
            }
        } catch (error) {
            console.error('Error al cargar reporte:', error);
            showNotification('Error al cargar reporte de inventario', 'error');
        }
    }
    
    async function generarCorte() {
        try {
            const fechaInicio = document.getElementById('fechaInicioCorte').value;
            const fechaFin = document.getElementById('fechaFinCorte').value;
            
            if (!fechaInicio || !fechaFin) {
                showNotification('Debe seleccionar ambas fechas', 'error');
                return;
            }
            
            console.log('Generando corte...', {fechaInicio, fechaFin});
            
            // Mostrar mensaje de carga
            document.getElementById('resultadoCorte').innerHTML = `
                <div class="logo-spinner-container py-12">
                    <div class="relative">
                        <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-16 w-16 object-contain mx-auto">
                    </div>
                    <p class="text-gray-600 text-lg mt-4">Generando corte de caja...</p>
                </div>
            `;
            
            const data = await apiRequest(`/DulceriaConejos/api/reportes.php/corte-caja?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`);
            
            console.log('Respuesta del servidor:', data);
            
            if (data.success) {
                const corte = data.data;
                const resumen = corte.resumen;
                const productosGranel = corte.productos_granel || [];
                const productosPieza = corte.productos_pieza || [];
                const productosAnaquel = corte.productos_anaquel || [];
                
                console.log('Datos del corte:', {resumen, productosGranel, productosPieza, productosAnaquel});
                
                let html = `
                    <!-- Resumen Principal -->
                    <div class="bg-gradient-to-br from-purple-600 to-pink-600 p-6 rounded-2xl mb-6 shadow-xl text-white">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-2xl font-bold flex items-center gap-2">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Resumen del Corte de Caja
                            </h4>
                            <div class="text-right text-sm opacity-90">
                                <p>${new Date(fechaInicio).toLocaleString('es-MX', {dateStyle: 'short', timeStyle: 'short'})}</p>
                                <p>${new Date(fechaFin).toLocaleString('es-MX', {dateStyle: 'short', timeStyle: 'short'})}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-white/20 backdrop-blur-sm p-4 rounded-xl">
                                <p class="text-sm opacity-90 mb-1">Total Ventas</p>
                                <p class="text-3xl font-bold">$${parseFloat(resumen.total_ventas || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm p-4 rounded-xl">
                                <p class="text-sm opacity-90 mb-1">Transacciones</p>
                                <p class="text-3xl font-bold">${resumen.num_transacciones || 0}</p>
                            </div>
                            <div class="bg-white/20 backdrop-blur-sm p-4 rounded-xl">
                                <p class="text-sm opacity-90 mb-1">Ticket Promedio</p>
                                <p class="text-3xl font-bold">$${(resumen.num_transacciones > 0 ? resumen.total_ventas / resumen.num_transacciones : 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Métodos de Pago -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-5 shadow-md">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Efectivo
                                </h5>
                            </div>
                            <p class="text-3xl font-bold text-green-600">$${parseFloat(resumen.total_efectivo || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                            <p class="text-sm text-gray-600 mt-2">${resumen.total_ventas > 0 ? ((resumen.total_efectivo / resumen.total_ventas) * 100).toFixed(1) : 0}% del total</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-5 shadow-md">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    Tarjeta
                                </h5>
                            </div>
                            <p class="text-3xl font-bold text-blue-600">$${parseFloat(resumen.total_tarjeta || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                            <p class="text-sm text-gray-600 mt-2">${resumen.total_ventas > 0 ? ((resumen.total_tarjeta / resumen.total_ventas) * 100).toFixed(1) : 0}% del total</p>
                        </div>
                        
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-xl p-5 shadow-md">
                            <div class="flex items-center justify-between mb-3">
                                <h5 class="font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                    </svg>
                                    Otros
                                </h5>
                            </div>
                            <p class="text-3xl font-bold text-purple-600">$${parseFloat(resumen.total_otros || 0).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                            <p class="text-sm text-gray-600 mt-2">${resumen.total_ventas > 0 ? ((resumen.total_otros / resumen.total_ventas) * 100).toFixed(1) : 0}% del total</p>
                        </div>
                    </div>
                `;
                
                // Productos a granel
                if (productosGranel && productosGranel.length > 0) {
                    html += `
                        <div class="bg-white border-2 border-amber-200 rounded-xl p-6 mb-6 shadow-md">
                            <h5 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Productos a Granel Vendidos
                            </h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    `;
                    
                    productosGranel.forEach(g => {
                        html += `
                            <div class="bg-amber-50 rounded-lg p-4 border border-amber-100">
                                <p class="font-bold text-gray-800 mb-3">${g.nombre}</p>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    ${g.bolsas_100g > 0 ? `<div class="flex justify-between"><span class="text-gray-600">100g:</span><span class="font-semibold">${g.bolsas_100g} bolsas</span></div>` : ''}
                                    ${g.bolsas_250g > 0 ? `<div class="flex justify-between"><span class="text-gray-600">250g:</span><span class="font-semibold">${g.bolsas_250g} bolsas</span></div>` : ''}
                                    ${g.bolsas_500g > 0 ? `<div class="flex justify-between"><span class="text-gray-600">500g:</span><span class="font-semibold">${g.bolsas_500g} bolsas</span></div>` : ''}
                                    ${g.bolsas_1kg > 0 ? `<div class="flex justify-between"><span class="text-gray-600">1kg:</span><span class="font-semibold">${g.bolsas_1kg} bolsas</span></div>` : ''}
                                </div>
                                <div class="mt-3 pt-3 border-t border-amber-200 flex justify-between items-center">
                                    <span class="text-gray-600">Total:</span>
                                    <span class="font-bold text-lg text-amber-600">$${parseFloat(g.total_venta).toLocaleString('es-MX', {minimumFractionDigits: 2})}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">${parseFloat(g.total_kg_vendidos).toFixed(2)} kg vendidos</p>
                            </div>
                        `;
                    });
                    
                    html += `
                            </div>
                        </div>
                    `;
                }
                
                // Productos por pieza
                if (productosPieza && productosPieza.length > 0) {
                    let totalPieza = 0;
                    let cantidadPieza = 0;
                    
                    html += `
                        <div class="bg-white border-2 border-green-200 rounded-xl p-6 mb-6 shadow-md">
                            <h5 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                Productos por Pieza
                            </h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-green-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Producto</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Categoría</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Cantidad</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                    `;
                    
                    productosPieza.forEach(p => {
                        totalPieza += parseFloat(p.total_venta);
                        cantidadPieza += parseInt(p.cantidad_vendida);
                        
                        html += `
                            <tr class="hover:bg-green-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">${p.nombre}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">${p.categoria || 'Sin categoría'}</td>
                                <td class="px-4 py-3 text-sm text-center font-semibold text-green-600">${parseFloat(p.cantidad_vendida).toFixed(0)} pzas</td>
                                <td class="px-4 py-3 text-sm text-right font-bold text-gray-800">$${parseFloat(p.total_venta).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                        <tr class="bg-green-100 border-t-2 border-green-300">
                                            <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-800 text-right">TOTAL POR PIEZA:</td>
                                            <td class="px-4 py-3 text-sm text-center font-bold text-green-700">${cantidadPieza.toFixed(0)} pzas</td>
                                            <td class="px-4 py-3 text-sm text-right font-bold text-green-700">$${totalPieza.toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }
                
                // Productos de anaquel
                if (productosAnaquel && productosAnaquel.length > 0) {
                    let totalAnaquel = 0;
                    let cantidadAnaquel = 0;
                    
                    html += `
                        <div class="bg-white border-2 border-blue-200 rounded-xl p-6 mb-6 shadow-md">
                            <h5 class="font-bold text-gray-800 mb-4 flex items-center gap-2 text-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                </svg>
                                Productos de Anaquel
                            </h5>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead class="bg-blue-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Producto</th>
                                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Categoría</th>
                                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Cantidad</th>
                                            <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                    `;
                    
                    productosAnaquel.forEach(p => {
                        totalAnaquel += parseFloat(p.total_venta);
                        cantidadAnaquel += parseInt(p.cantidad_vendida);
                        
                        html += `
                            <tr class="hover:bg-blue-50">
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">${p.nombre}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">${p.categoria || 'Sin categoría'}</td>
                                <td class="px-4 py-3 text-sm text-center font-semibold text-blue-600">${parseFloat(p.cantidad_vendida).toFixed(0)} unidades</td>
                                <td class="px-4 py-3 text-sm text-right font-bold text-gray-800">$${parseFloat(p.total_venta).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                            </tr>
                        `;
                    });
                    
                    html += `
                                        <tr class="bg-blue-100 border-t-2 border-blue-300">
                                            <td colspan="2" class="px-4 py-3 text-sm font-bold text-gray-800 text-right">TOTAL ANAQUEL:</td>
                                            <td class="px-4 py-3 text-sm text-center font-bold text-blue-700">${cantidadAnaquel.toFixed(0)} unidades</td>
                                            <td class="px-4 py-3 text-sm text-right font-bold text-blue-700">$${totalAnaquel.toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }
                
                // Botones de acción
                html += `
                    <div class="flex gap-3">
                        <button onclick="imprimirCorte()" class="flex-1 px-6 py-4 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-xl hover:from-purple-700 hover:to-purple-800 font-bold shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Imprimir Corte
                        </button>
                        <button onclick="guardarCorte()" class="flex-1 px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl hover:from-green-700 hover:to-green-800 font-bold shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Guardar y Cerrar Caja
                        </button>
                    </div>
                `;
                
                console.log('HTML generado, longitud:', html.length);
                document.getElementById('resultadoCorte').innerHTML = html;
                console.log('HTML asignado correctamente');
            } else {
                console.error('Error en respuesta:', data);
                showNotification(data.message || 'Error al generar corte de caja', 'error');
            }
        } catch (error) {
            console.error('Error al generar corte:', error);
            showNotification('Error al generar corte de caja', 'error');
        }
    }
    
    async function imprimirCorte() {
        try {
            const fechaInicio = document.getElementById('fechaInicioCorte').value;
            const fechaFin = document.getElementById('fechaFinCorte').value;
            
            if (!fechaInicio || !fechaFin) {
                showNotification('Debe seleccionar ambas fechas', 'error');
                return;
            }
            
            // Obtener el token del localStorage
            const token = localStorage.getItem('token');
            
            // Crear formulario temporal para enviar con POST (mantiene la sesión)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/DulceriaConejos/api/generar-pdf-corte.php';
            form.target = '_blank';
            
            // Agregar campos ocultos
            const campoToken = document.createElement('input');
            campoToken.type = 'hidden';
            campoToken.name = 'token';
            campoToken.value = token;
            form.appendChild(campoToken);
            
            const campoFechaInicio = document.createElement('input');
            campoFechaInicio.type = 'hidden';
            campoFechaInicio.name = 'fecha_inicio';
            campoFechaInicio.value = fechaInicio;
            form.appendChild(campoFechaInicio);
            
            const campoFechaFin = document.createElement('input');
            campoFechaFin.type = 'hidden';
            campoFechaFin.name = 'fecha_fin';
            campoFechaFin.value = fechaFin;
            form.appendChild(campoFechaFin);
            
            // Agregar al DOM, enviar y remover
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
            showNotification('Abriendo PDF del corte de caja...', 'info');
        } catch (error) {
            console.error('Error al imprimir:', error);
            showNotification('Error al generar PDF', 'error');
        }
    }
    
    async function guardarCorte() {
        if (!confirm('¿Está seguro de guardar y cerrar la caja? Esta acción no se puede deshacer.')) {
            return;
        }
        
        try {
            const fechaInicio = document.getElementById('fechaInicioCorte').value;
            const fechaFin = document.getElementById('fechaFinCorte').value;
            
            const data = await apiRequest('/DulceriaConejos/api/reportes.php/guardar-corte', {
                method: 'POST',
                body: JSON.stringify({
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin
                })
            });
            
            if (data.success) {
                showNotification('Corte de caja guardado correctamente', 'success');
                document.getElementById('resultadoCorte').innerHTML = '';
            } else {
                showNotification(data.message || 'Error al guardar corte', 'error');
            }
        } catch (error) {
            console.error('Error al guardar corte:', error);
            showNotification('Error al guardar corte de caja', 'error');
        }
    }
    
    function exportarReporte(tipo) {
        showNotification('Función de exportación en desarrollo', 'info');
    }
    
    // Mostrar ventas por defecto
    mostrarReporte('ventas');
</script>
