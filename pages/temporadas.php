<?php
$pageTitle = 'Gestión de Temporadas';
ob_start();
?>

<div class="space-y-6">
    <!-- Acciones -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Listados de Temporada</h2>
                <p class="text-gray-600 text-sm mt-1">Crea listados de productos especiales para temporadas (Halloween, Navidad, etc.)</p>
                <p class="text-xs text-purple-600 mt-1">💡 Solo los listados activos aparecen en el POS</p>
            </div>
            <button onclick="abrirModalTemporada()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-medium shadow-lg">
                + Nuevo listado
            </button>
        </div>
    </div>
    
    <!-- Grid de temporadas -->
    <div id="gridTemporadas" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="col-span-full text-center py-20">
            <div class="logo-spinner-container">
                <div class="relative">
                    <img src="/Dulcería/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-12 w-12 object-contain">
                </div>
                <p class="mt-2 text-gray-400">Cargando temporadas...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de temporada -->
<div id="modalTemporada" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 rounded-t-lg">
            <h3 class="text-xl font-bold text-white" id="tituloModalTemporada">Nuevo listado de temporada</h3>
        </div>
        
        <form id="formTemporada" class="p-6">
            <input type="hidden" id="idTemporada">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Icono / Emoji</label>
                    <input type="text" id="iconoTemporada" maxlength="2" placeholder="🎃 🎄 ❤️ 🎉"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-center text-3xl">
                    <p class="text-xs text-gray-500 mt-1">Opcional - Usa un emoji para identificar la temporada</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del listado *</label>
                    <input type="text" id="nombreTemporada" required placeholder="Ej: Halloween 2025"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                    <textarea id="descripcionTemporada" rows="2" placeholder="Descripción opcional..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha inicio *</label>
                        <input type="date" id="fechaInicioTemporada" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha fin *</label>
                        <input type="date" id="fechaFinTemporada" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="cerrarModalTemporada()" 
                    class="flex-1 px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-1 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-medium shadow-lg transition-all">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de gestión de productos de temporada -->
<div id="modalProductos" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full my-8">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 rounded-t-lg flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-white" id="tituloProductos">Gestionar productos</h3>
                <p class="text-sm text-white opacity-90 mt-1">Agrega o quita productos de este listado</p>
            </div>
            <button onclick="cerrarModalProductos()" class="text-white hover:text-gray-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="p-6">
            <!-- Tabs -->
            <div class="flex gap-2 mb-6 border-b">
                <button id="tabEnListado" onclick="cambiarTab('enListado')" 
                    class="px-6 py-3 font-medium border-b-2 border-purple-600 text-purple-600">
                    En listado (<span id="countEnListado">0</span>)
                </button>
                <button id="tabDisponibles" onclick="cambiarTab('disponibles')" 
                    class="px-6 py-3 font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                    Disponibles (<span id="countDisponibles">0</span>)
                </button>
            </div>
            
            <!-- Buscador -->
            <div class="mb-4">
                <div class="relative">
                    <input type="text" id="buscarProductoTemp" placeholder="Buscar producto..." 
                        class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Contenido de productos -->
            <div id="contenidoProductos" class="max-h-[60vh] overflow-y-auto"></div>
        </div>
        
        <div class="px-6 pb-6 border-t pt-4 bg-gray-50">
            <button onclick="cerrarModalProductos()" 
                class="w-full px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors">
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
    let temporadas = [];
    let esEdicion = false;
    let temporadaActual = null;
    let todosProductos = [];
    let productosEnListado = [];
    let tabActual = 'enListado';
    
    async function cargarTemporadas() {
        try {
            const data = await apiRequest('/Dulcería/api/temporadas.php');
            if (data.success) {
                temporadas = data.data;
                mostrarTemporadas(temporadas);
            }
        } catch (error) {
            console.error('Error al cargar temporadas:', error);
            showNotification('Error al cargar temporadas', 'error');
        }
    }
    
    function mostrarTemporadas(temps) {
        const container = document.getElementById('gridTemporadas');
        
        if (temps.length === 0) {
            container.innerHTML = `
                <div class="col-span-full text-center py-20 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-lg font-medium">No hay listados de temporada</p>
                    <p class="text-sm mt-2">Crea tu primer listado para gestionar productos especiales</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        temps.forEach(t => {
            const activa = t.activa == 1;
            const fechaInicio = new Date(t.fecha_inicio).toLocaleDateString('es-MX', {day: '2-digit', month: 'short', year: 'numeric'});
            const fechaFin = new Date(t.fecha_fin).toLocaleDateString('es-MX', {day: '2-digit', month: 'short', year: 'numeric'});
            const numProductos = parseInt(t.num_productos) || 0;
            
            html += `
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 ${activa ? 'border-green-500 ring-2 ring-green-200' : 'border-gray-200'} hover:shadow-xl transition-all">
                    <!-- Header con gradiente -->
                    <div class="bg-gradient-to-r ${activa ? 'from-green-500 to-emerald-600' : 'from-gray-400 to-gray-500'} px-6 py-5 relative overflow-hidden">
                        <div class="absolute top-0 right-0 opacity-10 text-8xl">
                            ${t.icono || '🎉'}
                        </div>
                        <div class="relative z-10">
                            <h3 class="text-2xl font-bold text-white mb-1">${t.icono || '🎉'} ${t.nombre}</h3>
                            <p class="text-white text-sm opacity-90">${fechaInicio} - ${fechaFin}</p>
                        </div>
                        ${activa ? '<div class="absolute top-2 right-2 bg-white text-green-600 px-3 py-1 rounded-full text-xs font-bold">ACTIVO EN POS</div>' : ''}
                    </div>
                    
                    <div class="p-6">
                        ${t.descripcion ? `<p class="text-gray-600 text-sm mb-5 italic">"${t.descripcion}"</p>` : ''}
                        
                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4 mb-5">
                            <div class="bg-purple-50 rounded-lg p-3 text-center">
                                <div class="text-2xl font-bold text-purple-600">${numProductos}</div>
                                <div class="text-xs text-gray-600 mt-1">Productos</div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3 text-center">
                                <div class="text-sm font-bold ${activa ? 'text-green-600' : 'text-gray-500'}">${activa ? '✓ Visible' : '✕ Oculto'}</div>
                                <div class="text-xs text-gray-600 mt-1">En POS</div>
                            </div>
                        </div>
                        
                        <!-- Botón de gestión de productos -->
                        <button onclick="gestionarProductos(${t.id}, '${t.nombre.replace(/'/g, "\\'")}')" 
                            class="w-full mb-3 px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-lg font-medium shadow-md transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Gestionar productos (${numProductos})
                        </button>
                        
                        <!-- Botones de acción -->
                        <div class="flex gap-2">
                            <button onclick='editarTemporada(${JSON.stringify(t)})' 
                                class="flex-1 px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Editar
                            </button>
                            ${activa ? 
                                `<button onclick="desactivarTemporada(${t.id})" 
                                    class="flex-1 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                    Desactivar
                                </button>` :
                                `<button onclick="activarTemporada(${t.id})" 
                                    class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Activar
                                </button>`
                            }
                        </div>
                        
                        <!-- Botón eliminar -->
                        <button onclick="eliminarTemporada(${t.id})" 
                            class="w-full mt-2 px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-xs transition-colors">
                            Eliminar listado
                        </button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function abrirModalTemporada() {
        esEdicion = false;
        document.getElementById('tituloModalTemporada').textContent = 'Nuevo listado de temporada';
        document.getElementById('formTemporada').reset();
        document.getElementById('idTemporada').value = '';
        document.getElementById('modalTemporada').classList.remove('hidden');
    }
    
    function editarTemporada(temporada) {
        esEdicion = true;
        document.getElementById('tituloModalTemporada').textContent = 'Editar listado de temporada';
        
        document.getElementById('idTemporada').value = temporada.id;
        document.getElementById('iconoTemporada').value = temporada.icono || '';
        document.getElementById('nombreTemporada').value = temporada.nombre;
        document.getElementById('descripcionTemporada').value = temporada.descripcion || '';
        document.getElementById('fechaInicioTemporada').value = temporada.fecha_inicio;
        document.getElementById('fechaFinTemporada').value = temporada.fecha_fin;
        
        document.getElementById('modalTemporada').classList.remove('hidden');
    }
    
    function cerrarModalTemporada() {
        document.getElementById('modalTemporada').classList.add('hidden');
    }
    
    document.getElementById('formTemporada').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const temporada = {
            icono: document.getElementById('iconoTemporada').value || null,
            nombre: document.getElementById('nombreTemporada').value,
            descripcion: document.getElementById('descripcionTemporada').value || null,
            fecha_inicio: document.getElementById('fechaInicioTemporada').value,
            fecha_fin: document.getElementById('fechaFinTemporada').value
        };
        
        try {
            let url = '/Dulcería/api/temporadas.php';
            let method = 'POST';
            
            if (esEdicion) {
                const id = document.getElementById('idTemporada').value;
                url += `/${id}`;
                method = 'PUT';
            }
            
            const data = await apiRequest(url, {
                method: method,
                body: JSON.stringify(temporada)
            });
            
            if (data.success) {
                showNotification(esEdicion ? 'Listado actualizado correctamente' : 'Listado creado correctamente', 'success');
                cerrarModalTemporada();
                await cargarTemporadas();
            } else {
                showNotification(data.message || 'Error al guardar listado', 'error');
            }
            
        } catch (error) {
            console.error('Error al guardar listado:', error);
            showNotification('Error al guardar listado', 'error');
        }
    });
    
    async function activarTemporada(id) {
        if (!confirm('¿Activar este listado? Será visible en el POS. Se desactivarán los demás automáticamente.')) {
            return;
        }
        
        try {
            const data = await apiRequest(`/Dulcería/api/temporadas.php/${id}/activar`, {
                method: 'PUT'
            });
            
            if (data.success) {
                showNotification('Listado activado correctamente - Visible en POS', 'success');
                await cargarTemporadas();
            } else {
                showNotification(data.message || 'Error al activar listado', 'error');
            }
            
        } catch (error) {
            console.error('Error al activar:', error);
            showNotification('Error al activar listado', 'error');
        }
    }
    
    async function desactivarTemporada(id) {
        if (!confirm('¿Desactivar este listado? Dejará de verse en el POS.')) {
            return;
        }
        
        try {
            const data = await apiRequest(`/Dulcería/api/temporadas.php/${id}/desactivar`, {
                method: 'PUT'
            });
            
            if (data.success) {
                showNotification('Listado desactivado - Oculto del POS', 'success');
                await cargarTemporadas();
            } else {
                showNotification(data.message || 'Error al desactivar listado', 'error');
            }
            
        } catch (error) {
            console.error('Error al desactivar:', error);
            showNotification('Error al desactivar listado', 'error');
        }
    }
    
    async function eliminarTemporada(id) {
        if (!confirm('¿Eliminar este listado? Esta acción no se puede deshacer.')) {
            return;
        }
        
        try {
            const data = await apiRequest(`/Dulcería/api/temporadas.php/${id}`, {
                method: 'DELETE'
            });
            
            if (data.success) {
                showNotification('Listado eliminado correctamente', 'success');
                await cargarTemporadas();
            } else {
                showNotification(data.message || 'Error al eliminar listado', 'error');
            }
            
        } catch (error) {
            console.error('Error al eliminar:', error);
            showNotification('Error al eliminar listado', 'error');
        }
    }
    
    async function gestionarProductos(idTemporada, nombreTemporada) {
        temporadaActual = idTemporada;
        document.getElementById('tituloProductos').textContent = `Gestionar productos: ${nombreTemporada}`;
        
        try {
            // Cargar todos los productos activos
            const dataProductos = await apiRequest('/Dulcería/api/productos.php?activo=1');
            if (!dataProductos.success) throw new Error('Error al cargar productos');
            
            // Cargar productos de la temporada
            const dataTemp = await apiRequest(`/Dulcería/api/temporadas.php/${idTemporada}/productos`);
            if (!dataTemp.success) throw new Error('Error al cargar productos de temporada');
            
            todosProductos = dataProductos.data;
            productosEnListado = dataTemp.data.map(p => p.id);
            
            // Abrir modal y mostrar tab por defecto
            document.getElementById('modalProductos').classList.remove('hidden');
            cambiarTab('enListado');
            
        } catch (error) {
            console.error('Error al cargar productos:', error);
            showNotification('Error al cargar productos', 'error');
        }
    }
    
    function cambiarTab(tab) {
        tabActual = tab;
        
        // Actualizar estilos de tabs
        document.getElementById('tabEnListado').className = tab === 'enListado' 
            ? 'px-6 py-3 font-medium border-b-2 border-purple-600 text-purple-600'
            : 'px-6 py-3 font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700';
            
        document.getElementById('tabDisponibles').className = tab === 'disponibles' 
            ? 'px-6 py-3 font-medium border-b-2 border-purple-600 text-purple-600'
            : 'px-6 py-3 font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700';
        
        // Mostrar productos
        mostrarProductosModal();
    }
    
    function mostrarProductosModal() {
        const busqueda = document.getElementById('buscarProductoTemp').value.toLowerCase();
        
        let productosAMostrar = [];
        if (tabActual === 'enListado') {
            productosAMostrar = todosProductos.filter(p => productosEnListado.includes(p.id));
        } else {
            productosAMostrar = todosProductos.filter(p => !productosEnListado.includes(p.id));
        }
        
        // Filtrar por búsqueda
        if (busqueda) {
            productosAMostrar = productosAMostrar.filter(p => 
                p.nombre.toLowerCase().includes(busqueda) || 
                (p.upc || '').toLowerCase().includes(busqueda)
            );
        }
        
        // Actualizar contadores
        document.getElementById('countEnListado').textContent = todosProductos.filter(p => productosEnListado.includes(p.id)).length;
        document.getElementById('countDisponibles').textContent = todosProductos.filter(p => !productosEnListado.includes(p.id)).length;
        
        const container = document.getElementById('contenidoProductos');
        
        if (productosAMostrar.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    <p>${tabActual === 'enListado' ? 'No hay productos en este listado' : 'No hay productos disponibles para agregar'}</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';
        productosAMostrar.forEach(p => {
            const precioVenta = parseFloat(p.precio_venta);
            const imagenUrl = p.imagen_url || '/Dulcería/public/img/productos/default.png';
            
            html += `
                <div class="bg-white border-2 border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex gap-4">
                        <!-- Imagen -->
                        <img src="${imagenUrl}" alt="${p.nombre}" 
                            class="w-20 h-20 object-cover rounded-lg border border-gray-200"
                            onerror="this.src='/Dulcería/public/img/productos/default.png'">
                        
                        <!-- Info -->
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 mb-1">${p.nombre}</h4>
                            <p class="text-xs text-gray-500 mb-2">${p.categoria_nombre}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-green-600">$${precioVenta.toFixed(2)}</span>
                                ${tabActual === 'enListado' 
                                    ? `<button onclick="quitarProducto(${p.id})" 
                                        class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-medium transition-colors">
                                        Quitar
                                    </button>`
                                    : `<button onclick="agregarProducto(${p.id})" 
                                        class="px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-xs font-medium transition-colors">
                                        Agregar
                                    </button>`
                                }
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        container.innerHTML = html;
    }
    
    async function agregarProducto(idProducto) {
        try {
            const data = await apiRequest(`/Dulcería/api/temporadas.php/${temporadaActual}/productos/${idProducto}`, {
                method: 'POST'
            });
            
            if (data.success) {
                showNotification('Producto agregado al listado', 'success');
                productosEnListado.push(idProducto);
                mostrarProductosModal();
                await cargarTemporadas(); // Actualizar contador
            } else {
                showNotification(data.message || 'Error al agregar producto', 'error');
            }
        } catch (error) {
            console.error('Error al agregar producto:', error);
            showNotification('Error al agregar producto', 'error');
        }
    }
    
    async function quitarProducto(idProducto) {
        try {
            const data = await apiRequest(`/Dulcería/api/temporadas.php/${temporadaActual}/productos/${idProducto}`, {
                method: 'DELETE'
            });
            
            if (data.success) {
                showNotification('Producto quitado del listado', 'success');
                productosEnListado = productosEnListado.filter(id => id !== idProducto);
                mostrarProductosModal();
                await cargarTemporadas(); // Actualizar contador
            } else {
                showNotification(data.message || 'Error al quitar producto', 'error');
            }
        } catch (error) {
            console.error('Error al quitar producto:', error);
            showNotification('Error al quitar producto', 'error');
        }
    }
    
    // Event listener para búsqueda
    document.addEventListener('DOMContentLoaded', function() {
        const buscador = document.getElementById('buscarProductoTemp');
        if (buscador) {
            buscador.addEventListener('input', mostrarProductosModal);
        }
    });
    
    function cerrarModalProductos() {
        document.getElementById('modalProductos').classList.add('hidden');
        temporadaActual = null;
    }
    
    // Inicializar
    cargarTemporadas();
</script>
