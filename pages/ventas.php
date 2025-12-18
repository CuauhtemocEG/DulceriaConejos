<?php
$pageTitle = 'Historial de Ventas';
ob_start();
?>

<div class="space-y-6">
    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Ventas</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" id="buscarFolio" placeholder="Buscar por folio..." 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            
            <input type="date" id="fechaInicio" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            
            <input type="date" id="fechaFin" 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            
            <select id="filtroEstado" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">Todos los estados</option>
                <option value="completada">Completadas</option>
                <option value="cancelada">Canceladas</option>
            </select>
        </div>
    </div>
    
    <!-- Tabla de ventas -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folio</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Fecha</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Vendedor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaVentas" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            <div class="logo-spinner-container">
                                <div class="relative">
                                    <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-12 w-12 object-contain">
                                </div>
                                <p class="mt-2">Cargando ventas...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal detalle de venta -->
<div id="modalDetalle" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full my-8">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 rounded-t-lg">
            <h3 class="text-xl font-bold text-white">Detalle de venta</h3>
        </div>
        
        <div id="contenidoDetalle" class="p-6"></div>
        
        <div class="px-6 pb-6">
            <button onclick="cerrarModalDetalle()" 
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                Cerrar
            </button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

<script>
    let ventas = [];
    
    async function cargarVentas() {
        try {
            const data = await apiRequest('/DulceriaConejos/api/pos.php/ventas');
            if (data.success) {
                ventas = data.data;
                aplicarFiltros();
            }
        } catch (error) {
            console.error('Error al cargar ventas:', error);
            showNotification('Error al cargar ventas', 'error');
        }
    }
    
    function aplicarFiltros() {
        const folio = document.getElementById('buscarFolio').value.toLowerCase();
        const fechaInicio = document.getElementById('fechaInicio').value;
        const fechaFin = document.getElementById('fechaFin').value;
        const estado = document.getElementById('filtroEstado').value;
        
        let filtradas = ventas.filter(v => {
            const matchFolio = !folio || v.folio.toLowerCase().includes(folio);
            const matchEstado = !estado || v.estado === estado;
            
            let matchFecha = true;
            if (fechaInicio) {
                matchFecha = matchFecha && v.created_at >= fechaInicio;
            }
            if (fechaFin) {
                matchFecha = matchFecha && v.created_at <= fechaFin + ' 23:59:59';
            }
            
            return matchFolio && matchEstado && matchFecha;
        });
        
        mostrarVentas(filtradas);
    }
    
    function mostrarVentas(ventas) {
        const tbody = document.getElementById('tablaVentas');
        
        if (ventas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">No se encontraron ventas</td></tr>';
            return;
        }
        
        let html = '';
        ventas.forEach(v => {
            const fecha = new Date(v.created_at).toLocaleString('es-MX', {dateStyle: 'short', timeStyle: 'short'});
            const estado = v.estado === 'completada' ?
                '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completada</span>' :
                '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Cancelada</span>';
            
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${v.folio}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell">${fecha}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 hidden lg:table-cell">${v.vendedor_nombre}</td>
                    <td class="px-4 py-3 text-sm font-medium text-green-600">$${parseFloat(v.total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="px-4 py-3">${estado}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button onclick="verDetalle(${v.id})" class="text-blue-600 hover:text-blue-700" title="Ver detalle">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <button onclick="imprimirTicket(${v.id})" class="text-purple-600 hover:text-purple-700" title="Imprimir ticket PDF">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                            </button>
                            <button onclick="imprimirTicketTermica(${v.id})" class="text-green-600 hover:text-green-700" title="Imprimir en térmica">
                                <i class="fas fa-print"></i>
                            </button>
                            ${v.estado === 'completada' ? `
                                <button onclick="cancelarVenta(${v.id})" class="text-red-600 hover:text-red-700" title="Cancelar venta">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    async function verDetalle(idVenta) {
        try {
            const data = await apiRequest(`/DulceriaConejos/api/pos.php/venta/${idVenta}`);
            if (data.success) {
                const venta = data.data;
                
                let html = `
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Folio</p>
                            <p class="font-semibold">${venta.folio}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Fecha</p>
                            <p class="font-semibold">${new Date(venta.created_at).toLocaleString('es-MX')}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Vendedor</p>
                            <p class="font-semibold">${venta.vendedor_nombre}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Método de pago</p>
                            <p class="font-semibold">${venta.metodo_pago}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Estado</p>
                            <p class="font-semibold">${venta.estado === 'completada' ? 
                                '<span class="text-green-600">Completada</span>' : 
                                '<span class="text-red-600">Cancelada</span>'}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total</p>
                            <p class="font-bold text-lg text-purple-600">$${parseFloat(venta.total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</p>
                        </div>
                    </div>
                    
                    ${venta.pdf_ticket ? `
                        <div class="mb-6 p-3 bg-blue-50 rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Ticket PDF</p>
                                    <p class="text-xs text-gray-500">Disponible para descarga</p>
                                </div>
                            </div>
                            <a href="/DulceriaConejos/${venta.pdf_ticket}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                Ver PDF
                            </a>
                        </div>
                    ` : ''}
                    
                    ${venta.observaciones ? `
                        <div class="mb-6 p-3 bg-yellow-50 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Observaciones</p>
                            <p>${venta.observaciones}</p>
                        </div>
                    ` : ''}
                    
                    <div class="border-t pt-4">
                        <h4 class="font-semibold mb-3">Productos</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cant.</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                `;
                
                venta.productos.forEach(p => {
                    let nombreProducto = p.nombre_producto;
                    if (p.peso_gramos) {
                        nombreProducto += ` (${p.peso_gramos}g)`;
                    }
                    
                    html += `
                        <tr>
                            <td class="px-4 py-2 text-sm">${nombreProducto}</td>
                            <td class="px-4 py-2 text-sm">${p.cantidad}</td>
                            <td class="px-4 py-2 text-sm">$${parseFloat(p.precio_unitario).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                            <td class="px-4 py-2 text-sm font-medium">$${parseFloat(p.subtotal).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                document.getElementById('contenidoDetalle').innerHTML = html;
                document.getElementById('modalDetalle').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error al ver detalle:', error);
            showNotification('Error al cargar detalle', 'error');
        }
    }
    
    function cerrarModalDetalle() {
        document.getElementById('modalDetalle').classList.add('hidden');
    }
    
    async function imprimirTicket(idVenta) {
        try {
            const data = await apiRequest(`/DulceriaConejos/api/tickets.php/${idVenta}`);
            if (data.success) {
                const ventana = window.open('', '_blank');
                ventana.document.write(data.data.ticket_html);
                ventana.document.close();
                ventana.print();
            }
        } catch (error) {
            console.error('Error al imprimir ticket:', error);
            showNotification('Error al imprimir ticket', 'error');
        }
    }
    
    async function imprimirTicketTermica(idVenta) {
        try {
            showNotification('Enviando a impresora térmica...', 'info');
            
            // Obtener configuración de impresora
            const configData = await apiRequest('/DulceriaConejos/api/configuracion.php/impresora');
            
            if (!configData.success || !configData.data.habilitada) {
                showNotification('Impresora térmica no configurada o deshabilitada', 'warning');
                return;
            }
            
            const config = configData.data;
            
            // Llamar API de impresión térmica
            const data = await apiRequest('/DulceriaConejos/api/imprimir-termica.php', {
                method: 'POST',
                body: JSON.stringify({
                    tipo: 'ticket',
                    venta_id: idVenta,
                    impresora: config.nombre_impresora
                })
            });
            
            if (data.success) {
                showNotification('Ticket enviado a impresora térmica', 'success');
            } else {
                showNotification('Error: ' + (data.error || data.message), 'error');
            }
        } catch (error) {
            console.error('Error al imprimir en térmica:', error);
            showNotification('Error al imprimir en impresora térmica', 'error');
        }
    }
    
    async function cancelarVenta(idVenta) {
        if (!confirm('¿Está seguro de cancelar esta venta? Esta acción no se puede deshacer.')) {
            return;
        }
        
        const motivo = prompt('Ingrese el motivo de cancelación:');
        if (!motivo || motivo.trim() === '') {
            showNotification('Debe especificar un motivo', 'error');
            return;
        }
        
        try {
            const data = await apiRequest(`/DulceriaConejos/api/pos.php/${idVenta}/cancelar`, {
                method: 'POST',
                body: JSON.stringify({ motivo: motivo })
            });
            
            if (data.success) {
                showNotification('Venta cancelada correctamente', 'success');
                await cargarVentas();
            } else {
                showNotification(data.message || 'Error al cancelar venta', 'error');
            }
        } catch (error) {
            console.error('Error al cancelar venta:', error);
            showNotification('Error al cancelar venta', 'error');
        }
    }
    
    // Event listeners
    document.getElementById('buscarFolio').addEventListener('input', aplicarFiltros);
    document.getElementById('fechaInicio').addEventListener('change', aplicarFiltros);
    document.getElementById('fechaFin').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);
    
    // Inicializar
    cargarVentas();
</script>
