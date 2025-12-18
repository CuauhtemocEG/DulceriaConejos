<?php
$pageTitle = 'Configuración de Utilidad';
ob_start();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-cog text-blue-600"></i> Configuración de Utilidad
                </h2>
                <p class="text-gray-600 mt-2">Configura los márgenes de utilidad para cada tipo de producto</p>
            </div>
        </div>
    </div>

        <!-- Configuración de Márgenes por Tipo de Producto -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Anaquel -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-green-500">
                <div class="flex items-center mb-4">
                    <i class="fas fa-box text-3xl text-green-500 mr-3"></i>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Anaquel</h2>
                        <p class="text-sm text-gray-500">Productos empaquetados</p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Margen de Utilidad (%)
                    </label>
                    <div class="flex items-center">
                        <input type="number" 
                               id="margenAnaquel" 
                               value="30" 
                               step="0.01"
                               min="0"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <span class="ml-2 text-gray-600 font-bold">%</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Ejemplo: $100 → $<span id="ejemploAnaquel" class="font-bold text-green-600">130.00</span>
                    </p>
                </div>
                <button onclick="guardarMargen('anaquel')" 
                        class="w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">
                    <i class="fas fa-save mr-2"></i>Guardar
                </button>
            </div>

            <!-- Pieza -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-purple-500">
                <div class="flex items-center mb-4">
                    <i class="fas fa-candy-cane text-3xl text-purple-500 mr-3"></i>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Pieza</h2>
                        <p class="text-sm text-gray-500">Venta individual</p>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Margen de Utilidad (%)
                    </label>
                    <div class="flex items-center">
                        <input type="number" 
                               id="margenPieza" 
                               value="50" 
                               step="0.01"
                               min="0"
                               class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <span class="ml-2 text-gray-600 font-bold">%</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Ejemplo: $100 → $<span id="ejemploPieza" class="font-bold text-purple-600">150.00</span>
                    </p>
                </div>
                <button onclick="guardarMargen('pieza')" 
                        class="w-full bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition">
                    <i class="fas fa-save mr-2"></i>Guardar
                </button>
            </div>

            <!-- Granel - Info General -->
            <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500">
                <div class="flex items-center mb-4">
                    <i class="fas fa-weight text-3xl text-blue-500 mr-3"></i>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Granel</h2>
                        <p class="text-sm text-gray-500">Venta por peso</p>
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-gray-700 mb-2">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Los productos a granel tienen márgenes configurables por peso.
                    </p>
                    <p class="text-xs text-gray-500">
                        Configúralos al crear o editar cada producto a granel.
                    </p>
                </div>
            </div>
        </div>

        <!-- Configuración de Márgenes por Peso (Granel) -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-balance-scale text-blue-600 mr-2"></i>
                        Márgenes Predeterminados para Granel
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Estos valores se aplicarán por defecto al crear nuevos productos a granel
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- 100g -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                    <div class="text-center mb-3">
                        <i class="fas fa-weight-hanging text-2xl text-blue-600"></i>
                        <h3 class="text-lg font-bold text-gray-800 mt-2">100 gramos</h3>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Margen (%)
                        </label>
                        <div class="flex items-center">
                            <input type="number" 
                                   id="margenGranel100" 
                                   value="50" 
                                   step="0.01"
                                   min="0"
                                   class="flex-1 px-3 py-2 border border-blue-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="ml-2 text-gray-600 font-bold">%</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 text-center">
                        $10/kg → $<span id="ejemploGranel100" class="font-bold text-blue-700">1.50</span>
                    </p>
                </div>

                <!-- 250g -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                    <div class="text-center mb-3">
                        <i class="fas fa-weight-hanging text-2xl text-green-600"></i>
                        <h3 class="text-lg font-bold text-gray-800 mt-2">250 gramos</h3>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Margen (%)
                        </label>
                        <div class="flex items-center">
                            <input type="number" 
                                   id="margenGranel250" 
                                   value="15" 
                                   step="0.01"
                                   min="0"
                                   class="flex-1 px-3 py-2 border border-green-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <span class="ml-2 text-gray-600 font-bold">%</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 text-center">
                        $10/kg → $<span id="ejemploGranel250" class="font-bold text-green-700">2.88</span>
                    </p>
                </div>

                <!-- 500g -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
                    <div class="text-center mb-3">
                        <i class="fas fa-weight-hanging text-2xl text-orange-600"></i>
                        <h3 class="text-lg font-bold text-gray-800 mt-2">500 gramos</h3>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Margen (%)
                        </label>
                        <div class="flex items-center">
                            <input type="number" 
                                   id="margenGranel500" 
                                   value="10" 
                                   step="0.01"
                                   min="0"
                                   class="flex-1 px-3 py-2 border border-orange-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <span class="ml-2 text-gray-600 font-bold">%</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 text-center">
                        $10/kg → $<span id="ejemploGranel500" class="font-bold text-orange-700">5.50</span>
                    </p>
                </div>

                <!-- 1kg -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-4 border border-red-200">
                    <div class="text-center mb-3">
                        <i class="fas fa-weight-hanging text-2xl text-red-600"></i>
                        <h3 class="text-lg font-bold text-gray-800 mt-2">1 kilogramo</h3>
                    </div>
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-700 mb-1">
                            Margen (%)
                        </label>
                        <div class="flex items-center">
                            <input type="number" 
                                   id="margenGranel1000" 
                                   value="40" 
                                   step="0.01"
                                   min="0"
                                   class="flex-1 px-3 py-2 border border-red-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <span class="ml-2 text-gray-600 font-bold">%</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 text-center">
                        $10/kg → $<span id="ejemploGranel1000" class="font-bold text-red-700">14.00</span>
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button onclick="guardarMargenesGranel()" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-medium">
                    <i class="fas fa-save mr-2"></i>Guardar Márgenes de Granel
                </button>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-3xl mr-4 mt-1"></i>
                <div>
                    <h3 class="text-xl font-bold mb-2">Información sobre Márgenes de Utilidad</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-1"></i>
                            <span><strong>Anaquel:</strong> Se aplica a productos empaquetados que se venden tal cual.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-1"></i>
                            <span><strong>Pieza:</strong> Se aplica a productos vendidos individualmente (caramelos, chocolates sueltos).</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-1"></i>
                            <span><strong>Granel:</strong> Los márgenes predeterminados se aplican al crear nuevos productos. Puedes personalizarlos por producto.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle mr-2 mt-1"></i>
                            <span><strong>Cálculo:</strong> Precio Venta = Precio Compra × (1 + Margen/100)</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cargar configuración al inicio
        document.addEventListener('DOMContentLoaded', function() {
            cargarConfiguracion();
            
            // Actualizar ejemplos en tiempo real
            document.getElementById('margenAnaquel').addEventListener('input', actualizarEjemplos);
            document.getElementById('margenPieza').addEventListener('input', actualizarEjemplos);
            document.getElementById('margenGranel100').addEventListener('input', actualizarEjemplos);
            document.getElementById('margenGranel250').addEventListener('input', actualizarEjemplos);
            document.getElementById('margenGranel500').addEventListener('input', actualizarEjemplos);
            document.getElementById('margenGranel1000').addEventListener('input', actualizarEjemplos);
        });

        /**
         * Cargar configuración desde localStorage o valores por defecto
         */
        function cargarConfiguracion() {
            // Cargar márgenes de anaquel y pieza
            const margenAnaquel = localStorage.getItem('margenAnaquel') || '30';
            const margenPieza = localStorage.getItem('margenPieza') || '50';
            
            document.getElementById('margenAnaquel').value = margenAnaquel;
            document.getElementById('margenPieza').value = margenPieza;
            
            // Cargar márgenes de granel
            const margenesGranel = JSON.parse(localStorage.getItem('margenesGranelDefecto') || '{"100":50,"250":15,"500":10,"1000":40}');
            
            document.getElementById('margenGranel100').value = margenesGranel['100'];
            document.getElementById('margenGranel250').value = margenesGranel['250'];
            document.getElementById('margenGranel500').value = margenesGranel['500'];
            document.getElementById('margenGranel1000').value = margenesGranel['1000'];
            
            actualizarEjemplos();
        }

        /**
         * Actualizar ejemplos de cálculo
         */
        function actualizarEjemplos() {
            // Anaquel
            const margenAnaquel = parseFloat(document.getElementById('margenAnaquel').value) || 0;
            const precioAnaquel = 100 * (1 + margenAnaquel / 100);
            document.getElementById('ejemploAnaquel').textContent = precioAnaquel.toFixed(2);
            
            // Pieza
            const margenPieza = parseFloat(document.getElementById('margenPieza').value) || 0;
            const precioPieza = 100 * (1 + margenPieza / 100);
            document.getElementById('ejemploPieza').textContent = precioPieza.toFixed(2);
            
            // Granel (basado en $10/kg de precio de compra)
            // IMPORTANTE: Usar la misma lógica que la API
            const precioCompra = 10;
            
            // Primero calcular el precio de 1kg con su margen
            const margen1000 = parseFloat(document.getElementById('margenGranel1000').value) || 0;
            const precioVenta1kg = precioCompra * (1 + margen1000 / 100);
            document.getElementById('ejemploGranel1000').textContent = precioVenta1kg.toFixed(2);
            
            // Otros pesos: calcular desde precio de 1kg (NO desde precio de compra)
            const margen100 = parseFloat(document.getElementById('margenGranel100').value) || 0;
            const precioBase100 = (precioVenta1kg / 1000) * 100;
            const precio100 = precioBase100 * (1 + margen100 / 100);
            document.getElementById('ejemploGranel100').textContent = precio100.toFixed(2);
            
            const margen250 = parseFloat(document.getElementById('margenGranel250').value) || 0;
            const precioBase250 = (precioVenta1kg / 1000) * 250;
            const precio250 = precioBase250 * (1 + margen250 / 100);
            document.getElementById('ejemploGranel250').textContent = precio250.toFixed(2);
            
            const margen500 = parseFloat(document.getElementById('margenGranel500').value) || 0;
            const precioBase500 = (precioVenta1kg / 1000) * 500;
            const precio500 = precioBase500 * (1 + margen500 / 100);
            document.getElementById('ejemploGranel500').textContent = precio500.toFixed(2);
        }

        /**
         * Guardar margen individual
         */
        async function guardarMargen(tipo) {
            let margen, mensaje;
            if (tipo === 'anaquel') {
                margen = document.getElementById('margenAnaquel').value;
                localStorage.setItem('margenAnaquel', margen);
                mensaje = 'Margen de Anaquel guardado correctamente';
            } else if (tipo === 'pieza') {
                margen = document.getElementById('margenPieza').value;
                localStorage.setItem('margenPieza', margen);
                mensaje = 'Margen de Pieza guardado correctamente';
            } else if (tipo === 'granel') {
                // Para granel, tomar el margen de 1kg
                margen = document.getElementById('margenGranel1000').value;
                // El guardado real de márgenes de granel ya se hace en guardarMargenesGranel, aquí solo para actualización masiva
                mensaje = 'Margen de Granel guardado correctamente';
            }
            if (confirm('¿Deseas actualizar el margen y precio de todos los productos existentes de tipo ' + tipo + '?')) {
                try {
                    const res = await fetch('/DulceriaConejos/utils/actualizar_margen_global.php?tipo=' + tipo + '&margen=' + margen);
                    if (res.ok) {
                        const txt = await res.text();
                        mostrarNotificacion('Productos actualizados: ' + txt, 'success');
                    } else {
                        mostrarNotificacion('Error actualizando productos', 'error');
                    }
                } catch (e) {
                    mostrarNotificacion('Error de red al actualizar productos', 'error');
                }
            } else {
                mostrarNotificacion(mensaje, 'success');
            }
        }

        /**
         * Guardar márgenes de granel
         */
        async function guardarMargenesGranel() {
            const margenes = {
                100: parseFloat(document.getElementById('margenGranel100').value) || 50,
                250: parseFloat(document.getElementById('margenGranel250').value) || 15,
                500: parseFloat(document.getElementById('margenGranel500').value) || 10,
                1000: parseFloat(document.getElementById('margenGranel1000').value) || 40
            };
            
            localStorage.setItem('margenesGranelDefecto', JSON.stringify(margenes));
            
            // Preguntar si se desea actualizar productos existentes
            if (confirm('¿Deseas actualizar los márgenes y precios de TODOS los productos existentes de tipo granel (todos los pesos: 100g, 250g, 500g, 1kg)?')) {
                try {
                    const res = await fetch('/DulceriaConejos/utils/actualizar_margenes_granel_global.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ margenes: margenes })
                    });
                    
                    if (res.ok) {
                        const data = await res.json();
                        if (data.success) {
                            mostrarNotificacion(data.message, 'success');
                        } else {
                            mostrarNotificacion('Error: ' + data.message, 'error');
                        }
                    } else {
                        mostrarNotificacion('Error actualizando productos granel', 'error');
                    }
                } catch (e) {
                    console.error('Error de red:', e);
                    mostrarNotificacion('Error de red al actualizar productos granel', 'error');
                }
            } else {
                mostrarNotificacion('Márgenes de Granel guardados correctamente en configuración local', 'success');
            }
        }

        /**
         * Mostrar notificación (usa la función del layout)
         */
        function mostrarNotificacion(mensaje, tipo) {
            showNotification(mensaje, tipo);
        }
    </script>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
