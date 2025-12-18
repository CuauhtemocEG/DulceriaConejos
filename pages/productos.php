<?php
$pageTitle = 'Gestión de Productos';
ob_start();
?>

<div class="space-y-6">
    <!-- Filtros y acciones -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Productos</h2>
            <button onclick="abrirModalProducto()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-medium">
                + Nuevo producto
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" id="buscarProducto" placeholder="Buscar por nombre o código..." 
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
            
            <select id="filtroCategoria" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">Todas las categorías</option>
            </select>
            
            <select id="filtroTipo" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">Todos los tipos</option>
                <option value="anaquel">Anaquel</option>
                <option value="granel">Granel</option>
                <option value="pieza">Pieza</option>
            </select>
            
            <select id="filtroEstado" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>
        </div>
    </div>
    
    <!-- Tabla de productos -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Imagen</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Categoría</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden xl:table-cell">POS</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaProductos" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                            <div class="logo-spinner-container">
                                <div class="relative">
                                    <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-12 w-12 object-contain">
                                </div>
                                <p class="mt-2">Cargando productos...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de producto -->
<div id="modalProducto" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full my-8">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 rounded-t-lg">
            <h3 class="text-xl font-bold text-white" id="tituloModal">Nuevo producto</h3>
        </div>
        
        <form id="formProducto" class="p-6">
            <input type="hidden" id="idProducto">
            
            <!-- Layout de 3 columnas -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- COLUMNA 1: Información Básica + Categoría -->
                <div class="space-y-6">
                    <!-- Información Básica -->
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Información Básica
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Código UPC *</label>
                                <input type="text" id="codigo" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                    placeholder="7501234567890">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre del producto *</label>
                                <input type="text" id="nombre" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                    placeholder="Gomitas Ácidas">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Descripción</label>
                                <textarea id="descripcion" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                    placeholder="Descripción del producto..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Categoría y Tipo -->
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Categoría y Tipo
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Categoría *</label>
                                <select id="categoria" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipo de producto *</label>
                                <select id="tipo" required onchange="cambiarTipoProducto()"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                                    <option value="">Seleccione...</option>
                                    <option value="anaquel">🏪 Anaquel (+30%)</option>
                                    <option value="granel">⚖️ Granel (peso variable)</option>
                                    <option value="pieza">🎁 Pieza (+50%)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Unidad de medida *</label>
                                <select id="unidadMedida" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm">
                                    <option value="piezas">Piezas</option>
                                    <option value="gramos">Gramos</option>
                                    <option value="kg">Kilogramos (kg)</option>
                                    <option value="litros">Litros</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA 2: Precios + Inventario + Temporada -->
                <div class="space-y-6">
                    <!-- Precios -->
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Precios
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Precio de compra *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500 font-medium text-sm">$</span>
                                    <input type="number" id="precioCompra" step="0.01" required 
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                        placeholder="0.00">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Para granel: precio por 1kg</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Precio de venta *</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500 font-medium text-sm">$</span>
                                    <input type="number" id="precioVenta" step="0.01" required readonly
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-medium text-sm"
                                        placeholder="0.00">
                                </div>
                                <p class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Calculado automáticamente
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Inventario -->
                    <div>
                        <h4 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            Inventario
                        </h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stock inicial *</label>
                                <input type="number" id="stockInicial" step="0.01" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                    placeholder="0">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Stock mínimo *</label>
                                <input type="number" id="stockMinimo" step="0.01" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                    placeholder="0">
                            </div>
                            
                            <div class="pt-2">
                                <label class="flex items-center gap-2 cursor-pointer bg-gray-50 px-3 py-2.5 rounded-lg hover:bg-gray-100 transition-colors">
                                    <input type="checkbox" id="activo" checked class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                    <span class="text-sm font-medium text-gray-700">✓ Producto activo</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Temporada -->
                    <div class="border-t pt-4">
                        <div class="flex items-center mb-3">
                            <input type="checkbox" id="esTemporada" onchange="toggleTemporada()" class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                            <label for="esTemporada" class="ml-2 block text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                Producto de temporada
                            </label>
                        </div>
                        
                        <div id="camposTemporada" class="hidden bg-orange-50 p-3 rounded-lg border border-orange-200 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Temporada</label>
                                <select id="temporada" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Precio de temporada</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500 font-medium text-sm">$</span>
                                    <input type="number" id="precioTemporada" step="0.01" 
                                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm"
                                        placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA 3: Imagen del Producto -->
                <div class="lg:border-l lg:pl-6">
                    <div class="sticky top-6">
                        <h4 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Imagen del Producto
                        </h4>
                        
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 p-6 rounded-xl border-2 border-dashed border-gray-300">
                            <!-- Preview de imagen -->
                            <div class="mb-4">
                                <div class="relative mx-auto" style="width: 200px; height: 200px;">
                                    <img id="previewImagen" src="/DulceriaConejos/public/img/productos/default.png" 
                                        alt="Preview" class="w-full h-full object-cover rounded-xl border-4 border-white shadow-lg">
                                    <div class="absolute -top-3 -right-3 bg-gradient-to-br from-purple-600 to-pink-600 text-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Input de archivo -->
                            <div class="space-y-2">
                                <label for="imagenProducto" class="block text-center">
                                    <span class="inline-flex items-center justify-center px-4 py-2 bg-white border-2 border-gray-300 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors font-medium text-sm text-gray-700">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Seleccionar imagen
                                    </span>
                                </label>
                                <input type="file" id="imagenProducto" accept="image/*" class="hidden">
                                <input type="hidden" id="imagenUrl" value="">
                                
                                <div class="bg-white rounded-lg p-3 text-xs text-gray-600 space-y-1">
                                    <p class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        JPG, PNG, GIF, WEBP
                                    </p>
                                    <p class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Máximo 2MB
                                    </p>
                                    <p class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                        </svg>
                                        Se redimensiona automáticamente
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración para productos a granel (Ancho completo) -->
            <div id="configGranel" class="hidden bg-gradient-to-br from-blue-50 to-indigo-50 p-5 rounded-lg border-2 border-blue-200">
                <h4 class="text-base font-bold text-blue-900 mb-4 flex items-center gap-2">
                    ⚖️ Configuración de Precios por Peso (Granel)
                </h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <label class="block text-xs font-bold text-gray-700 mb-2">100g</label>
                        <div class="flex items-center gap-2 mb-1">
                            <input type="number" id="margen100" value="50" step="1" min="0" max="200"
                                class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-medium">
                            <span class="text-xs text-gray-600 font-semibold">%</span>
                        </div>
                        <p class="text-sm font-bold text-green-600" id="precio100">$0.00</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <label class="block text-xs font-bold text-gray-700 mb-2">250g</label>
                        <div class="flex items-center gap-2 mb-1">
                            <input type="number" id="margen250" value="15" step="1" min="0" max="200"
                                class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-medium">
                            <span class="text-xs text-gray-600 font-semibold">%</span>
                        </div>
                        <p class="text-sm font-bold text-green-600" id="precio250">$0.00</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <label class="block text-xs font-bold text-gray-700 mb-2">500g</label>
                        <div class="flex items-center gap-2 mb-1">
                            <input type="number" id="margen500" value="10" step="1" min="0" max="200"
                                class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-medium">
                            <span class="text-xs text-gray-600 font-semibold">%</span>
                        </div>
                        <p class="text-sm font-bold text-green-600" id="precio500">$0.00</p>
                    </div>
                    <div class="bg-white p-3 rounded-lg shadow-sm">
                        <label class="block text-xs font-bold text-gray-700 mb-2">1kg</label>
                        <div class="flex items-center gap-2 mb-1">
                            <input type="number" id="margen1kg" value="40" step="1" min="0" max="200"
                                class="w-full px-3 py-2 border border-gray-300 rounded text-sm font-medium">
                            <span class="text-xs text-gray-600 font-semibold">%</span>
                        </div>
                        <p class="text-sm font-bold text-green-600" id="precio1kg">$0.00</p>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-3 pt-4 border-t-2 border-gray-200">
                <button type="button" onclick="cerrarModalProducto()" 
                    class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-medium shadow-lg transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Guardar producto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Fleje -->
<div id="modalFleje" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 rounded-t-lg flex justify-between items-center">
            <h3 class="text-xl font-bold text-white" id="tituloFleje">Fleje de Precios</h3>
            <button onclick="imprimirFleje()" class="text-white hover:text-gray-200" title="Imprimir">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
            </button>
        </div>
        
        <div id="contenidoFleje" class="p-6"></div>
        
        <div class="px-6 pb-6">
            <button onclick="cerrarModalFleje()" 
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

<style>
@media print {
    /* Resetear todo */
    * {
        margin: 0;
        padding: 0;
    }
    
    /* Ocultar todo excepto el contenido del fleje */
    body * {
        visibility: hidden;
    }
    #contenidoFleje, #contenidoFleje * {
        visibility: visible;
    }
    #contenidoFleje {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 0 !important;
    }
    
    /* Contenedor compacto - UNA SOLA PÁGINA */
    .fleje-content {
        width: 100% !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    .fleje-content > div {
        max-width: 350px !important;
        width: 350px !important;
        margin: 0 !important;
        padding: 0.5cm !important;
        page-break-inside: avoid !important;
    }
    
    /* Título compacto - sin separación */
    .fleje-content h2 {
        font-size: 1.2rem !important;
        margin: 0 0 0.3cm 0 !important;
        padding: 0 0 0.2cm 0 !important;
        line-height: 1.2 !important;
    }
    
    /* Tabla compacta */
    #contenidoFleje table {
        font-size: 0.6rem !important;
        width: 100% !important;
        margin: 0 !important;
    }
    
    #contenidoFleje th {
        padding: 0.15cm !important;
        font-size: 0.55rem !important;
        line-height: 1 !important;
    }
    
    #contenidoFleje td {
        padding: 0.15cm !important;
        line-height: 1 !important;
    }
    
    /* Tamaños de texto ajustados */
    #contenidoFleje .text-base {
        font-size: 0.65rem !important;
    }
    
    #contenidoFleje .text-lg {
        font-size: 0.75rem !important;
    }
    
    #contenidoFleje .text-3xl {
        font-size: 1.2rem !important;
    }
    
    /* Códigos de barras compactos */
    #contenidoFleje svg {
        max-width: 100% !important;
        height: auto !important;
        max-height: 0.8cm !important;
    }
    
    /* Asegurar que todo quepa en una página */
    table {
        page-break-inside: avoid !important;
    }
    
    tr {
        page-break-inside: avoid !important;
    }
    
    /* Bordes */
    .border-black {
        border-color: #000 !important;
        border-width: 1.5px !important;
    }
    
    .border-gray-400 {
        border-color: #666 !important;
        border-width: 1px !important;
    }
    
    .border-b-3 {
        border-bottom-width: 1px !important;
    }
    
    /* Fondo para el encabezado de la tabla */
    .bg-gray-100 {
        background-color: #f3f4f6 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Colores de texto */
    .text-green-600 {
        color: #059669 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* Tamaño de página A4 o carta */
    @page {
        size: A4;
        margin: 0.5cm;
    }
}

/* Estilos para vista normal */
.border-3 {
    border-width: 3px;
}

/* Asegurar que la tabla sea responsive en pantalla */
@media screen and (max-width: 768px) {
    #contenidoFleje table {
        font-size: 0.875rem;
    }
    
    #contenidoFleje th,
    #contenidoFleje td {
        padding: 0.5rem !important;
    }
}
</style>

<script>
    let productos = [];
    let categorias = [];
    let temporadas = [];
    let esEdicion = false;
    
    async function cargarDatos() {
        try {
            // Cargar categorías
            const catData = await apiRequest('/DulceriaConejos/api/categorias.php');
            if (catData.success) {
                categorias = catData.data;
                
                const selectFiltro = document.getElementById('filtroCategoria');
                const selectForm = document.getElementById('categoria');
                
                catData.data.forEach(cat => {
                    const optFiltro = document.createElement('option');
                    optFiltro.value = cat.id;
                    optFiltro.textContent = cat.nombre;
                    selectFiltro.appendChild(optFiltro);
                    
                    const optForm = document.createElement('option');
                    optForm.value = cat.id;
                    optForm.textContent = cat.nombre;
                    selectForm.appendChild(optForm);
                });
            }
            
            // Cargar temporadas
            const tempData = await apiRequest('/DulceriaConejos/api/temporadas.php');
            if (tempData.success) {
                temporadas = tempData.data;
                
                const select = document.getElementById('temporada');
                tempData.data.forEach(temp => {
                    const option = document.createElement('option');
                    option.value = temp.id;
                    option.textContent = temp.nombre;
                    select.appendChild(option);
                });
            }
            
            // Cargar productos
            await cargarProductos();
            
        } catch (error) {
            console.error('Error al cargar datos:', error);
            showNotification('Error al cargar datos', 'error');
        }
    }
    
    async function cargarProductos() {
        try {
            const data = await apiRequest('/DulceriaConejos/api/productos.php');
            if (data.success) {
                productos = data.data;
                aplicarFiltros();
            }
        } catch (error) {
            console.error('Error al cargar productos:', error);
        }
    }
    
    function aplicarFiltros() {
        const busqueda = document.getElementById('buscarProducto').value.toLowerCase();
        const categoria = document.getElementById('filtroCategoria').value;
        const tipo = document.getElementById('filtroTipo').value;
        const estado = document.getElementById('filtroEstado').value;
        
        let filtrados = productos.filter(p => {
            const matchBusqueda = p.nombre.toLowerCase().includes(busqueda) || (p.upc || '').toLowerCase().includes(busqueda);
            const matchCategoria = !categoria || p.categoria_id == categoria;
            const matchTipo = !tipo || p.tipo_producto === tipo;
            const matchEstado = estado === '' || parseInt(p.activo) === parseInt(estado);
            
            return matchBusqueda && matchCategoria && matchTipo && matchEstado;
        });
        
        mostrarProductos(filtrados);
    }
    
    function mostrarProductos(prods) {
        const tbody = document.getElementById('tablaProductos');
        
        if (prods.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="px-4 py-10 text-center text-gray-400">No se encontraron productos</td></tr>';
            return;
        }
        
        let html = '';
        prods.forEach(p => {
            let precio = parseFloat(p.precio_venta);
            if (p.es_temporada == 1 && parseFloat(p.precio_temporada) > precio) {
                precio = parseFloat(p.precio_temporada);
            }
            
            const tipoBadge = {
                'anaquel': '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Anaquel</span>',
                'granel': '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Granel</span>',
                'pieza': '<span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Pieza</span>'
            };
            
            // Determinar visibilidad en POS
            let visibleEnPOS = false;
            let motivoOculto = '';
            
            if (p.activo == 0) {
                motivoOculto = 'Producto inactivo';
            } else if (p.es_temporada == 1) {
                if (p.temporada_activa == 1) {
                    visibleEnPOS = true;
                } else {
                    motivoOculto = `Temporada "${p.temporada_nombre}" inactiva`;
                }
            } else {
                // Producto normal activo
                visibleEnPOS = true;
            }
            
            // Badge de visibilidad en POS
            const visibilidadBadge = visibleEnPOS ? 
                `<span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-emerald-100 text-emerald-800 font-medium">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                    </svg>
                    Visible en POS
                </span>` :
                `<span class="inline-flex items-center gap-1 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800 font-medium" title="${motivoOculto}">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                        <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                    </svg>
                    Oculto en POS
                </span>`;
            
            const estadoBadge = p.activo == 1 ? 
                '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activo</span>' :
                '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactivo</span>';
            
            const stockClass = p.stock_actual <= p.stock_minimo ? 'text-red-600' : 'text-gray-900';
            
            // Obtener URL de imagen
            const imagenUrl = p.imagen_url || '/DulceriaConejos/public/img/productos/default.png';
            
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <img src="${imagenUrl}" alt="${p.nombre}" 
                            class="w-12 h-12 object-cover rounded-lg border-2 border-gray-200"
                            onerror="this.src='/DulceriaConejos/public/img/productos/default.png'">
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900">${p.nombre}</div>
                        <div class="text-xs text-gray-500">${p.upc || 'Sin código'}</div>
                        ${p.es_temporada == 1 ? `<div class="text-xs text-purple-600 mt-1">🎉 ${p.temporada_nombre || 'Temporada'}</div>` : ''}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900 hidden md:table-cell">${p.categoria_nombre}</td>
                    <td class="px-4 py-3">${tipoBadge[p.tipo_producto]}</td>
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">$${precio.toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="px-4 py-3 text-sm ${stockClass}">${p.stock_actual} ${p.unidad_medida}</td>
                    <td class="px-4 py-3 hidden lg:table-cell">${estadoBadge}</td>
                    <td class="px-4 py-3 hidden xl:table-cell">${visibilidadBadge}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button onclick="mostrarFleje(${p.id})" class="text-purple-600 hover:text-purple-700" title="Mostrar Fleje">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                            <button onclick='editarProducto(${JSON.stringify(p)})' class="text-blue-600 hover:text-blue-700" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <button onclick="eliminarProducto(${p.id})" class="text-red-600 hover:text-red-700" title="Eliminar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
    }
    
    function abrirModalProducto() {
        esEdicion = false;
        document.getElementById('tituloModal').textContent = 'Nuevo producto';
        document.getElementById('formProducto').reset();
        document.getElementById('idProducto').value = '';
        
        // Resetear preview de imagen
        document.getElementById('previewImagen').src = '/DulceriaConejos/public/img/productos/default.png';
        document.getElementById('imagenUrl').value = '';
        
        // Cargar márgenes predeterminados de granel desde localStorage
        const margenesGranel = JSON.parse(localStorage.getItem('margenesGranelDefecto') || '{"100":50,"250":15,"500":10,"1000":40}');
        document.getElementById('margen100').value = margenesGranel['100'];
        document.getElementById('margen250').value = margenesGranel['250'];
        document.getElementById('margen500').value = margenesGranel['500'];
        document.getElementById('margen1kg').value = margenesGranel['1000'];
        
        document.getElementById('modalProducto').classList.remove('hidden');
    }
    
    function editarProducto(producto) {
        esEdicion = true;
        document.getElementById('tituloModal').textContent = 'Editar producto';
        
        document.getElementById('idProducto').value = producto.id;
        document.getElementById('codigo').value = producto.upc || '';
        document.getElementById('nombre').value = producto.nombre;
        document.getElementById('categoria').value = producto.categoria_id;
        document.getElementById('tipo').value = producto.tipo_producto;
        document.getElementById('precioCompra').value = producto.precio_compra;
        document.getElementById('precioVenta').value = producto.precio_venta;
        document.getElementById('stockInicial').value = producto.stock_actual;
        document.getElementById('stockMinimo').value = producto.stock_minimo;
        document.getElementById('unidadMedida').value = producto.unidad_medida;
        document.getElementById('activo').checked = producto.activo == 1;
        document.getElementById('descripcion').value = producto.descripcion || '';
        
        // Cargar imagen del producto
        const imagenUrl = producto.imagen_url || '/DulceriaConejos/public/img/productos/default.png';
        document.getElementById('previewImagen').src = imagenUrl;
        document.getElementById('imagenUrl').value = imagenUrl;
        
        // Cargar configuración de granel si aplica
        if (producto.tipo_producto === 'granel') {
            document.getElementById('configGranel').classList.remove('hidden');
            
            if (producto.precios_granel && producto.precios_granel.length > 0) {
                producto.precios_granel.forEach(p => {
                    if (p.peso_gramos === 100) {
                        document.getElementById('margen100').value = p.margen_adicional;
                    } else if (p.peso_gramos === 250) {
                        document.getElementById('margen250').value = p.margen_adicional;
                    } else if (p.peso_gramos === 500) {
                        document.getElementById('margen500').value = p.margen_adicional;
                    } else if (p.peso_gramos === 1000) {
                        document.getElementById('margen1kg').value = p.margen_adicional;
                    }
                });
            }
            calcularPreciosGranel();
        } else {
            document.getElementById('configGranel').classList.add('hidden');
        }
        
        if (producto.es_temporada == 1) {
            document.getElementById('esTemporada').checked = true;
            toggleTemporada();
            document.getElementById('temporada').value = producto.temporada_id || '';
            document.getElementById('precioTemporada').value = producto.precio_temporada || '';
        }
        
        document.getElementById('modalProducto').classList.remove('hidden');
    }
    
    function cerrarModalProducto() {
        document.getElementById('modalProducto').classList.add('hidden');
    }
    
    function cambiarTipoProducto() {
        const tipo = document.getElementById('tipo').value;
        const precioCompra = parseFloat(document.getElementById('precioCompra').value) || 0;
        let precioVenta = 0;
        
        // Mostrar/ocultar configuración de granel
        const configGranel = document.getElementById('configGranel');
        if (tipo === 'granel') {
            configGranel.classList.remove('hidden');
            // Leer margen de 1kg desde localStorage (configuración)
            const margenesGranel = JSON.parse(localStorage.getItem('margenesGranelDefecto') || '{"100":50,"250":15,"500":10,"1000":40}');
            const margen1kg = margenesGranel['1000'] || 40;
            precioVenta = Math.round(precioCompra * (1 + margen1kg / 100)); // Base 1kg con margen configurado
            calcularPreciosGranel();
        } else {
            configGranel.classList.add('hidden');
            
            // Leer márgenes desde localStorage
            const margenAnaquel = parseFloat(localStorage.getItem('margenAnaquel') || '30');
            const margenPieza = parseFloat(localStorage.getItem('margenPieza') || '50');
            
            switch(tipo) {
                case 'anaquel':
                    precioVenta = Math.round(precioCompra * (1 + margenAnaquel / 100));
                    break;
                case 'pieza':
                    precioVenta = Math.round(precioCompra * (1 + margenPieza / 100));
                    break;
            }
        }
        
        document.getElementById('precioVenta').value = precioVenta.toFixed(2);
    }
    
    function calcularPreciosGranel() {
    // Leer márgenes predeterminados de granel desde localStorage
    const margenesGranel = JSON.parse(localStorage.getItem('margenesGranelDefecto') || '{"100":50,"250":15,"500":10,"1000":40}');
    const margen100 = parseFloat(document.getElementById('margen100')?.value) || margenesGranel['100'] || 50;
    const margen250 = parseFloat(document.getElementById('margen250')?.value) || margenesGranel['250'] || 15;
    const margen500 = parseFloat(document.getElementById('margen500')?.value) || margenesGranel['500'] || 10;
    const margen1kg = parseFloat(document.getElementById('margen1kg')?.value) || margenesGranel['1000'] || 40;

    // El precio_venta (1kg) ya tiene el margen base calculado
    const precioVenta = parseFloat(document.getElementById('precioVenta').value) || 0;
    
    // Los otros gramajes se calculan proporcionalmente desde precio_venta, aplicando su margen adicional
    const precio100 = Math.round((precioVenta / 1000 * 100) * (1 + margen100/100));
    const precio250 = Math.round((precioVenta / 1000 * 250) * (1 + margen250/100));
    const precio500 = Math.round((precioVenta / 1000 * 500) * (1 + margen500/100));
    
    // El 1kg es el precio_venta directo, SIN margen adicional (ya lo tiene incluido)
    const precio1kg = Math.round(precioVenta);
    
    document.getElementById('precio100').textContent = '$' + precio100;
    document.getElementById('precio250').textContent = '$' + precio250;
    document.getElementById('precio500').textContent = '$' + precio500;
    document.getElementById('precio1kg').textContent = '$' + precio1kg;
    }
    
    // Event listeners para recalcular precios
    document.getElementById('precioCompra').addEventListener('input', cambiarTipoProducto);
    document.getElementById('margen100').addEventListener('input', calcularPreciosGranel);
    document.getElementById('margen250').addEventListener('input', calcularPreciosGranel);
    document.getElementById('margen500').addEventListener('input', calcularPreciosGranel);
    document.getElementById('margen1kg').addEventListener('input', calcularPreciosGranel);
    
    function toggleTemporada() {
        const esTemporada = document.getElementById('esTemporada').checked;
        const campos = document.getElementById('camposTemporada');
        
        if (esTemporada) {
            campos.classList.remove('hidden');
        } else {
            campos.classList.add('hidden');
        }
    }
    
    async function mostrarFleje(productoId) {
        try {
            const data = await apiRequest(`/DulceriaConejos/api/productos.php/${productoId}`);
            if (data.success) {
                const producto = data.data;
                document.getElementById('tituloFleje').textContent = `Fleje: ${producto.nombre}`;
                
                let html = '<div class="fleje-content">';
                
                if (producto.tipo_producto === 'granel') {
                    // Fleje tipo tabla para productos a granel, calculando igual que en el modal
                    html += `
                        <div class="border-3 border-black bg-white p-4" style="max-width: 900px; margin: 0 auto;">
                            <div style="display: flex; align-items: center; gap: 10px; padding-bottom: 12px; border-bottom: 3px solid #9ca3af; margin-bottom: 12px;">
                                <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Logo" style="width: 100px; height: 100px; object-fit: contain; flex-shrink: 0;">
                                <h2 class="text-3xl font-bold text-center text-gray-900" style="flex: 1; margin: 0;">${producto.nombre}</h2>
                                <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Logo" style="width: 100px; height: 100px; object-fit: contain; flex-shrink: 0;">
                            </div>
                            <table class="w-full border-collapse" style="table-layout: fixed;">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border-2 border-gray-400 px-2 py-2 text-left text-base font-bold" style="width: 18%;">Gramaje</th>
                                        <th class="border-2 border-gray-400 px-2 py-2 text-left text-base font-bold" style="width: 27%;">Precio</th>
                                        <th class="border-2 border-gray-400 px-2 py-2 text-center text-base font-bold" style="width: 55%;">Código de Barras</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;
                    // Márgenes desde localStorage o producto
                    const margenesGranel = JSON.parse(localStorage.getItem('margenesGranelDefecto') || '{"100":50,"250":15,"500":10,"1000":40}');
                    const margen100 = producto.margenes_granel?.[100] ?? margenesGranel['100'] ?? 50;
                    const margen250 = producto.margenes_granel?.[250] ?? margenesGranel['250'] ?? 15;
                    const margen500 = producto.margenes_granel?.[500] ?? margenesGranel['500'] ?? 10;
                    const margen1kg = producto.margenes_granel?.[1000] ?? margenesGranel['1000'] ?? 40;
                    const precioVenta = parseFloat(producto.precio_venta) || 0;
                    const precios = [
                        {peso_gramos: 100, margen: margen100},
                        {peso_gramos: 250, margen: margen250},
                        {peso_gramos: 500, margen: margen500},
                        {peso_gramos: 1000, margen: margen1kg}
                    ];
                    // Siempre mostrar los 4 gramajes, con su div de código de barras
                    precios.forEach((p, idx) => {
                        const peso = p.peso_gramos === 1000 ? '1 kg' : `${p.peso_gramos} gr`;
                        const barcodeId = `barcode-${idx}`;
                        let precioCalculado;
                        
                        // El 1kg es el precio_venta directo (ya tiene su margen incluido)
                        // Los demás gramajes se calculan proporcionalmente y se les aplica su margen adicional
                        if (p.peso_gramos === 1000) {
                            precioCalculado = Math.round(precioVenta); // Redondear precio de 1kg
                        } else {
                            precioCalculado = Math.round((precioVenta / 1000 * p.peso_gramos) * (1 + p.margen/100));
                        }
                        
                        html += `
                            <tr style="page-break-inside: avoid;">
                                <td class="border-2 border-gray-400 px-2 py-2 text-base font-semibold align-middle">${peso}</td>
                                <td class="border-2 border-gray-400 px-2 py-2 text-lg font-bold text-green-600 align-middle">$${precioCalculado}</td>
                                <td class="border-2 border-gray-400 px-1 py-1 align-middle" style="overflow: hidden;">
                                    <div class="flex justify-center items-center" style="width: 100%; overflow: hidden;">
                                        <svg id="${barcodeId}" style="max-width: 100%; height: auto;"></svg>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;
                } else {
                    // Fleje para productos de anaquel o pieza
                    let precioFinal = parseFloat(producto.precio_venta);
                    if (producto.es_temporada == 1 && parseFloat(producto.precio_temporada || 0) > precioFinal) {
                        precioFinal = parseFloat(producto.precio_temporada);
                    }
                    
                    html += `
                        <div class="border-3 border-black rounded-lg p-6 bg-white" style="page-break-inside: avoid;">
                            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">${producto.nombre}</h2>
                            <div class="text-center mb-4">
                                <div class="text-5xl font-bold text-green-600">$${precioFinal.toFixed(2)}</div>
                                <div class="text-sm text-gray-500 mt-1">${producto.tipo_producto === 'pieza' ? 'Por pieza' : 'Por unidad'}</div>
                            </div>
                            <div class="bg-white p-2 rounded">
                                <svg id="barcode-single" class="w-full"></svg>
                            </div>
                        </div>
                    `;
                }
                
                html += '</div>';
                
                document.getElementById('contenidoFleje').innerHTML = html;
                
                // Generar códigos de barras
                setTimeout(() => {
                    const upcBase = producto.upc || '000000000000';
                    
                    if (producto.tipo_producto === 'granel') {
                        // Generar código de barras para cada gramaje, usando el código base sin sufijo para 1kg
                        [100, 250, 500, 1000].forEach((gramaje, idx) => {
                            const barcodeElement = document.getElementById(`barcode-${idx}`);
                            if (barcodeElement) {
                                try {
                                    let codigoUnico;
                                    switch(gramaje) {
                                        case 100:
                                            codigoUnico = upcBase + '01'; // Sufijo 01 para 100g
                                            break;
                                        case 250:
                                            codigoUnico = upcBase + '02'; // Sufijo 02 para 250g
                                            break;
                                        case 500:
                                            codigoUnico = upcBase + '03'; // Sufijo 03 para 500g
                                            break;
                                        case 1000:
                                            codigoUnico = upcBase; // Sin sufijo para 1kg
                                            break;
                                        default:
                                            codigoUnico = upcBase + '00';
                                    }
                                    JsBarcode(barcodeElement, codigoUnico, {
                                        format: "CODE128",
                                        width: 1.2,
                                        height: 35,
                                        displayValue: true,
                                        fontSize: 10,
                                        margin: 1,
                                        marginTop: 2,
                                        marginBottom: 2,
                                        textMargin: 1
                                    });
                                } catch (error) {
                                    console.error('Error generando código de barras:', error);
                                    barcodeElement.innerHTML = `<div class="text-center py-2 text-xs text-gray-500">UPC: ${upcBase}-${gramaje}g</div>`;
                                }
                            }
                        });
                    } else {
                        // Generar código de barras único para anaquel/pieza
                        const barcodeElement = document.getElementById('barcode-single');
                        if (barcodeElement) {
                            try {
                                JsBarcode(barcodeElement, upcBase, {
                                    format: "CODE128",
                                    width: 2,
                                    height: 80,
                                    displayValue: true,
                                    fontSize: 16,
                                    margin: 10
                                });
                            } catch (error) {
                                console.error('Error generando código de barras:', error);
                                barcodeElement.parentElement.innerHTML = `<div class="text-center py-4 text-gray-500">UPC: ${upcBase}</div>`;
                            }
                        }
                    }
                }, 100);
                
                document.getElementById('modalFleje').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error al cargar fleje:', error);
            showNotification('Error al cargar fleje', 'error');
        }
    }
    
    function cerrarModalFleje() {
        document.getElementById('modalFleje').classList.add('hidden');
    }
    
    async function imprimirFleje() {
        try {
            // Importar html2canvas si no está cargado
            if (typeof html2canvas === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
                document.head.appendChild(script);
                await new Promise(resolve => script.onload = resolve);
            }
            
            // Obtener el contenido del fleje
            const contenido = document.getElementById('contenidoFleje');
            
            if (!contenido) {
                showNotification('No hay contenido para descargar', 'error');
                return;
            }
            
            // Mostrar mensaje de carga
            showNotification('Generando imagen...', 'info');
            
            // Generar canvas del contenido
            const canvas = await html2canvas(contenido, {
                backgroundColor: '#ffffff',
                scale: 2, // Mayor calidad
                logging: false,
                useCORS: true,
                allowTaint: true
            });
            
            // Convertir canvas a blob
            canvas.toBlob(function(blob) {
                // Crear URL del blob
                const url = URL.createObjectURL(blob);
                
                // Crear enlace de descarga
                const link = document.createElement('a');
                const tituloFleje = document.getElementById('tituloFleje').textContent.replace('Fleje: ', '');
                const fecha = new Date().toISOString().split('T')[0];
                link.download = `fleje-${tituloFleje.replace(/\s+/g, '-')}-${fecha}.png`;
                link.href = url;
                
                // Hacer clic en el enlace para descargar
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Liberar URL
                URL.revokeObjectURL(url);
                
                showNotification('Imagen descargada correctamente', 'success');
            }, 'image/png');
            
        } catch (error) {
            console.error('Error al generar imagen:', error);
            showNotification('Error al generar la imagen', 'error');
        }
    }
    
    document.getElementById('formProducto').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            // 1. Subir imagen si se seleccionó una nueva
            let imagenUrl = document.getElementById('imagenUrl').value || '/DulceriaConejos/public/img/productos/default.png';
            const fileInput = document.getElementById('imagenProducto');
            
            if (fileInput.files && fileInput.files[0]) {
                const formData = new FormData();
                formData.append('imagen', fileInput.files[0]);
                
                const uploadResponse = await fetch('/DulceriaConejos/api/upload.php', {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    body: formData
                });
                
                const uploadData = await uploadResponse.json();
                
                if (uploadData.success) {
                    imagenUrl = uploadData.data.imagen_url;
                } else {
                    showNotification('Error al subir imagen: ' + uploadData.message, 'error');
                    return;
                }
            }
            
            // 2. Preparar datos del producto
            const producto = {
                upc: document.getElementById('codigo').value,
                nombre: document.getElementById('nombre').value,
                descripcion: document.getElementById('descripcion').value || null,
                imagen_url: imagenUrl,
                categoria_id: parseInt(document.getElementById('categoria').value),
                tipo_producto: document.getElementById('tipo').value,
                precio_compra: parseFloat(document.getElementById('precioCompra').value),
                precio_venta: parseFloat(document.getElementById('precioVenta').value),
                stock_actual: parseFloat(document.getElementById('stockInicial').value),
                stock_minimo: parseFloat(document.getElementById('stockMinimo').value),
                unidad_medida: document.getElementById('unidadMedida').value,
                activo: document.getElementById('activo').checked ? 1 : 0
            };
            
            if (document.getElementById('esTemporada').checked) {
                producto.es_temporada = 1;
                producto.temporada_id = parseInt(document.getElementById('temporada').value) || null;
                producto.precio_temporada = parseFloat(document.getElementById('precioTemporada').value) || null;
            }
            
            // Si es producto a granel, agregar márgenes personalizados
            if (producto.tipo_producto === 'granel') {
                producto.margenes_granel = {
                    100: parseFloat(document.getElementById('margen100').value) || 50,
                    250: parseFloat(document.getElementById('margen250').value) || 15,
                    500: parseFloat(document.getElementById('margen500').value) || 10,
                    1000: parseFloat(document.getElementById('margen1kg').value) || 40
                };
            }
            
            // 3. Guardar producto
            let url = '/DulceriaConejos/api/productos.php';
            let method = 'POST';
            
            if (esEdicion) {
                const id = document.getElementById('idProducto').value;
                url += `/${id}`;
                method = 'PUT';
            }
            
            const data = await apiRequest(url, {
                method: method,
                body: JSON.stringify(producto)
            });
            
            if (data.success) {
                showNotification(esEdicion ? 'Producto actualizado' : 'Producto creado', 'success');
                cerrarModalProducto();
                await cargarProductos();
            } else {
                showNotification(data.message || 'Error al guardar producto', 'error');
            }
            
        } catch (error) {
            console.error('Error al guardar producto:', error);
            showNotification('Error al guardar producto', 'error');
        }
    });
    
    async function eliminarProducto(id) {
        if (!confirm('¿Está seguro de eliminar este producto?')) {
            return;
        }
        
        try {
            const data = await apiRequest(`/DulceriaConejos/api/productos.php/${id}`, {
                method: 'DELETE'
            });
            
            if (data.success) {
                showNotification('Producto eliminado', 'success');
                await cargarProductos();
            } else {
                showNotification(data.message || 'Error al eliminar producto', 'error');
            }
            
        } catch (error) {
            console.error('Error al eliminar producto:', error);
            showNotification('Error al eliminar producto', 'error');
        }
    }
    
    // Event listeners para filtros
    document.getElementById('buscarProducto').addEventListener('input', aplicarFiltros);
    document.getElementById('filtroCategoria').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroTipo').addEventListener('change', aplicarFiltros);
    document.getElementById('filtroEstado').addEventListener('change', aplicarFiltros);
    
    // Event listener para previsualización de imagen
    document.getElementById('imagenProducto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validar tamaño (máx 2MB)
            if (file.size > 2 * 1024 * 1024) {
                showNotification('La imagen no debe superar 2MB', 'error');
                e.target.value = '';
                return;
            }
            
            // Validar tipo
            if (!file.type.startsWith('image/')) {
                showNotification('Solo se permiten archivos de imagen', 'error');
                e.target.value = '';
                return;
            }
            
            // Mostrar preview
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImagen').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Inicializar
    cargarDatos();
</script>
