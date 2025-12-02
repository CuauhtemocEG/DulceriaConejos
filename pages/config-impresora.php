<?php
$pageTitle = 'Configuración de Impresora';
ob_start();
?>

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                <i class="fas fa-print text-3xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold">Configuración de Impresora Térmica</h1>
                <p class="text-purple-100 mt-1">Configura tu impresora térmica para tickets</p>
            </div>
        </div>
    </div>
    
    <!-- Formulario de configuración -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
        <form id="formConfigImpresora" class="space-y-6">
            <!-- Nombre de la impresora con botón de búsqueda -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-print mr-2 text-purple-600"></i>
                    Nombre de la Impresora
                </label>
                <div class="flex gap-2">
                    <input type="text" id="nombreImpresora" name="nombre_impresora" required
                        placeholder="Ej: STMicroelectronics_POS58_Printer_USB"
                        class="flex-1 px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                    <button type="button" onclick="buscarImpresoras()" 
                        class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-all flex items-center gap-2 shadow-lg whitespace-nowrap">
                        <i class="fas fa-search"></i>
                        <span>Buscar</span>
                    </button>
                </div>
                <p class="text-sm text-gray-500 mt-2">
                    <i class="fas fa-info-circle"></i>
                    Haz clic en "Buscar" para detectar impresoras conectadas automáticamente
                </p>
            </div>
            
            <!-- Estado de la impresora -->
            <div id="estadoImpresora" class="hidden p-4 rounded-lg border-2">
                <div class="flex items-center gap-3">
                    <i class="fas fa-circle-notch fa-spin text-purple-600 text-xl"></i>
                    <div>
                        <p class="font-medium text-gray-800">Verificando impresora...</p>
                        <p class="text-sm text-gray-500">Por favor espera</p>
                    </div>
                </div>
            </div>
            
            <!-- Habilitar impresora -->
            <div class="flex items-center justify-between p-4 bg-purple-50 rounded-lg">
                <div>
                    <label class="text-sm font-medium text-gray-700">
                        <i class="fas fa-power-off mr-2 text-purple-600"></i>
                        Habilitar Impresora Térmica
                    </label>
                    <p class="text-sm text-gray-500 mt-1">Activar/desactivar impresión térmica</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="habilitada" name="habilitada" class="sr-only peer">
                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
                </label>
            </div>
            
            <!-- Auto imprimir -->
            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg">
                <div>
                    <label class="text-sm font-medium text-gray-700">
                        <i class="fas fa-magic mr-2 text-green-600"></i>
                        Impresión Automática
                    </label>
                    <p class="text-sm text-gray-500 mt-1">Imprimir automáticamente en térmica al procesar venta</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="autoImprimir" name="auto_imprimir" class="sr-only peer">
                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600"></div>
                </label>
            </div>
            
            <!-- Número de copias -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-copy mr-2 text-purple-600"></i>
                    Número de Copias
                </label>
                <input type="number" id="copias" name="copias" min="1" max="5" value="1"
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                <p class="text-sm text-gray-500 mt-2">
                    <i class="fas fa-info-circle"></i>
                    Número de tickets térmicos a imprimir por venta
                </p>
            </div>
            
            <!-- Botones de acción -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t">
                <button type="button" onclick="probarImpresora()" 
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-all flex items-center justify-center gap-2 shadow-lg">
                    <i class="fas fa-flask"></i>
                    <span>Probar Impresora</span>
                </button>
                <button type="submit" 
                    class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-medium transition-all flex items-center justify-center gap-2 shadow-lg">
                    <i class="fas fa-save"></i>
                    <span>Guardar Configuración</span>
                </button>
            </div>
        </form>
    </div>
    
    <!-- Panel de impresoras detectadas -->
    <div id="panelImpresoras" class="hidden bg-white rounded-xl shadow-lg border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list text-purple-600"></i>
                Impresoras Detectadas
            </h3>
            <button onclick="cerrarPanelImpresoras()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="listaImpresoras" class="space-y-2"></div>
    </div>
    
    <!-- Información adicional -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Guía rápida -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-bold text-blue-900 mb-3 flex items-center gap-2">
                <i class="fas fa-lightbulb"></i>
                Guía Rápida
            </h3>
            <ol class="space-y-2 text-sm text-blue-800 list-decimal list-inside">
                <li>Enciende tu impresora térmica</li>
                <li>Conecta el cable USB a tu computadora</li>
                <li>Haz clic en el botón <strong>"Buscar"</strong></li>
                <li>Selecciona tu impresora de la lista</li>
                <li>Configura las opciones según necesites</li>
                <li>Haz clic en <strong>"Probar Impresora"</strong></li>
                <li>Si funciona, haz clic en <strong>"Guardar"</strong></li>
            </ol>
        </div>
        
        <!-- Sistema operativo detectado -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
            <h3 class="font-bold text-purple-900 mb-3 flex items-center gap-2">
                <i class="fas fa-desktop"></i>
                Sistema Detectado
            </h3>
            <div id="infoSistema" class="text-sm text-purple-800">
                <div class="flex items-center gap-3 mb-3">
                    <i id="sistemaIcono" class="text-4xl"></i>
                    <div>
                        <p class="font-bold" id="sistemaNombre">Detectando...</p>
                        <p class="text-xs" id="sistemaDetalle"></p>
                    </div>
                </div>
                <div class="bg-white rounded-lg p-3 mt-3">
                    <p class="text-xs font-medium text-gray-600 mb-1">Configuración recomendada:</p>
                    <ul id="recomendaciones" class="text-xs text-gray-700 space-y-1"></ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sistemas operativos soportados -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-6 border border-gray-200">
        <h3 class="font-bold text-gray-800 mb-4 text-center">
            <i class="fas fa-globe mr-2"></i>
            Sistemas Operativos Soportados
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow-md p-4 border-2 border-blue-200 hover:border-blue-400 transition-all">
                <div class="text-center">
                    <i class="fab fa-windows text-5xl text-blue-600 mb-3"></i>
                    <h4 class="font-bold text-gray-800 mb-2">Windows</h4>
                    <p class="text-xs text-gray-600 mb-2">
                        Compatible con Windows 7, 8, 10, 11
                    </p>
                    <div class="text-left mt-3 pt-3 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-700 mb-1">Cómo ver impresoras:</p>
                        <p class="text-xs text-gray-600">Panel de Control → Dispositivos e impresoras</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 border-2 border-gray-300 hover:border-gray-500 transition-all">
                <div class="text-center">
                    <i class="fab fa-apple text-5xl text-gray-700 mb-3"></i>
                    <h4 class="font-bold text-gray-800 mb-2">macOS</h4>
                    <p class="text-xs text-gray-600 mb-2">
                        Compatible con macOS 10.12+
                    </p>
                    <div class="text-left mt-3 pt-3 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-700 mb-1">Comando terminal:</p>
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">lpstat -p</code>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-4 border-2 border-orange-200 hover:border-orange-400 transition-all">
                <div class="text-center">
                    <i class="fab fa-linux text-5xl text-orange-600 mb-3"></i>
                    <h4 class="font-bold text-gray-800 mb-2">Linux</h4>
                    <p class="text-xs text-gray-600 mb-2">
                        Compatible con CUPS
                    </p>
                    <div class="text-left mt-3 pt-3 border-t border-gray-200">
                        <p class="text-xs font-semibold text-gray-700 mb-1">Comando terminal:</p>
                        <code class="text-xs bg-gray-100 px-2 py-1 rounded">lpstat -p -d</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

<script>
    // Detectar sistema operativo
    function detectarSistemaOperativo() {
        const userAgent = navigator.userAgent.toLowerCase();
        const plataforma = navigator.platform.toLowerCase();
        
        let sistema = {
            nombre: 'Desconocido',
            icono: 'fas fa-desktop',
            clase: 'text-gray-600',
            detalle: '',
            recomendaciones: []
        };
        
        if (userAgent.indexOf('win') !== -1 || plataforma.indexOf('win') !== -1) {
            sistema = {
                nombre: 'Windows',
                icono: 'fab fa-windows',
                clase: 'text-blue-600',
                detalle: 'Sistema operativo Microsoft Windows',
                recomendaciones: [
                    '✓ Usa nombres de impresora del Panel de Control',
                    '✓ Verifica drivers en Dispositivos e impresoras',
                    '✓ Asegúrate que la impresora esté "Lista"'
                ]
            };
        } else if (userAgent.indexOf('mac') !== -1 || plataforma.indexOf('mac') !== -1) {
            sistema = {
                nombre: 'macOS',
                icono: 'fab fa-apple',
                clase: 'text-gray-700',
                detalle: 'Sistema operativo Apple macOS',
                recomendaciones: [
                    '✓ Usa nombres del sistema CUPS',
                    '✓ Ejecuta: lpstat -p en Terminal',
                    '✓ Verifica impresora en Preferencias del Sistema'
                ]
            };
        } else if (userAgent.indexOf('linux') !== -1 || plataforma.indexOf('linux') !== -1) {
            sistema = {
                nombre: 'Linux',
                icono: 'fab fa-linux',
                clase: 'text-orange-600',
                detalle: 'Sistema operativo Linux',
                recomendaciones: [
                    '✓ Usa nombres del sistema CUPS',
                    '✓ Ejecuta: lpstat -p -d en Terminal',
                    '✓ Verifica servicio CUPS activo'
                ]
            };
        }
        
        // Actualizar interfaz
        document.getElementById('sistemaIcono').className = `${sistema.icono} ${sistema.clase} text-4xl`;
        document.getElementById('sistemaNombre').textContent = sistema.nombre;
        document.getElementById('sistemaDetalle').textContent = sistema.detalle;
        
        const recomendacionesHTML = sistema.recomendaciones.map(r => `<li>${r}</li>`).join('');
        document.getElementById('recomendaciones').innerHTML = recomendacionesHTML;
        
        return sistema;
    }
    
    // Cargar configuración actual
    async function cargarConfiguracion() {
        try {
            const data = await apiRequest('/Dulcería/api/configuracion.php/impresora');
            
            if (data.success && data.data) {
                document.getElementById('nombreImpresora').value = data.data.nombre_impresora;
                document.getElementById('habilitada').checked = data.data.habilitada == 1;
                document.getElementById('autoImprimir').checked = data.data.auto_imprimir == 1;
                document.getElementById('copias').value = data.data.copias;
                
                // Validar impresora automáticamente si hay un nombre
                if (data.data.nombre_impresora) {
                    validarImpresoraEnSegundoPlano(data.data.nombre_impresora);
                }
            }
        } catch (error) {
            console.error('Error al cargar configuración:', error);
            showNotification('Error al cargar configuración', 'error');
        }
    }
    
    // Validar impresora en segundo plano (sin notificaciones)
    async function validarImpresoraEnSegundoPlano(nombre) {
        try {
            const validacion = await apiRequest(
                `/Dulcería/api/validar-impresora.php?accion=validar&nombre=${encodeURIComponent(nombre)}`
            );
            
            mostrarEstadoImpresora(validacion.conectada, nombre);
        } catch (error) {
            console.log('No se pudo validar impresora en segundo plano');
        }
    }
    
    // Mostrar estado de la impresora
    function mostrarEstadoImpresora(conectada, nombre) {
        const div = document.getElementById('estadoImpresora');
        div.classList.remove('hidden');
        
        if (conectada) {
            div.className = 'p-4 rounded-lg border-2 border-green-500 bg-green-50';
            div.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    <div>
                        <p class="font-medium text-green-800">Impresora conectada</p>
                        <p class="text-sm text-green-600">${nombre} está lista para usar</p>
                    </div>
                </div>
            `;
        } else {
            div.className = 'p-4 rounded-lg border-2 border-red-500 bg-red-50';
            div.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    <div>
                        <p class="font-medium text-red-800">Impresora no detectada</p>
                        <p class="text-sm text-red-600">${nombre} no está conectada o no está disponible</p>
                    </div>
                </div>
            `;
        }
    }
    
    // Buscar impresoras (nueva función unificada)
    async function buscarImpresoras() {
        try {
            showNotification('🔍 Buscando impresoras conectadas...', 'info');
            
            const data = await apiRequest('/Dulcería/api/validar-impresora.php?accion=listar');
            
            if (data.success && data.impresoras && data.impresoras.length > 0) {
                mostrarImpresoras(data.impresoras);
                showNotification(`✅ ${data.impresoras.length} impresora(s) encontrada(s)`, 'success');
            } else {
                showNotification('⚠️ No se detectaron impresoras conectadas', 'warning');
                mostrarMensajeSinImpresoras();
            }
        } catch (error) {
            console.error('Error al buscar impresoras:', error);
            showNotification('❌ Error al buscar impresoras', 'error');
        }
    }
    
    // Mostrar mensaje cuando no hay impresoras
    function mostrarMensajeSinImpresoras() {
        const panel = document.getElementById('panelImpresoras');
        const lista = document.getElementById('listaImpresoras');
        
        lista.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-circle text-6xl text-yellow-500 mb-4"></i>
                <h4 class="font-bold text-gray-800 mb-2">No se encontraron impresoras</h4>
                <p class="text-sm text-gray-600 mb-4">Verifica que:</p>
                <ul class="text-sm text-gray-600 text-left max-w-md mx-auto space-y-2">
                    <li>✓ La impresora esté encendida</li>
                    <li>✓ El cable USB esté correctamente conectado</li>
                    <li>✓ Los drivers estén instalados</li>
                    <li>✓ La impresora aparezca en configuración del sistema</li>
                </ul>
            </div>
        `;
        
        panel.classList.remove('hidden');
    }
    
    // Cerrar panel de impresoras
    function cerrarPanelImpresoras() {
        document.getElementById('panelImpresoras').classList.add('hidden');
    }
    
    // Guardar configuración
    document.getElementById('formConfigImpresora').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nombreImpresora = document.getElementById('nombreImpresora').value;
        
        // Validar que la impresora esté conectada antes de guardar
        showNotification('Validando impresora...', 'info');
        
        const validacion = await apiRequest(
            `/Dulcería/api/validar-impresora.php?accion=validar&nombre=${encodeURIComponent(nombreImpresora)}`
        );
        
        if (!validacion.success || !validacion.conectada) {
            showNotification('⚠️ Advertencia: La impresora no está conectada. Puedes guardar pero no funcionará hasta que la conectes.', 'warning');
        }
        
        const config = {
            nombre_impresora: nombreImpresora,
            habilitada: document.getElementById('habilitada').checked ? 1 : 0,
            auto_imprimir: document.getElementById('autoImprimir').checked ? 1 : 0,
            copias: parseInt(document.getElementById('copias').value)
        };
        
        try {
            const data = await apiRequest('/Dulcería/api/configuracion.php/impresora', {
                method: 'PUT',
                body: JSON.stringify(config)
            });
            
            if (data.success) {
                showNotification('✅ Configuración guardada correctamente', 'success');
                mostrarEstadoImpresora(validacion.conectada, nombreImpresora);
            } else {
                showNotification(data.message || 'Error al guardar', 'error');
            }
        } catch (error) {
            console.error('Error al guardar configuración:', error);
            showNotification('Error al guardar configuración', 'error');
        }
    });
    
    // Probar impresora
    async function probarImpresora() {
        const nombreImpresora = document.getElementById('nombreImpresora').value;
        
        if (!nombreImpresora) {
            showNotification('⚠️ Ingresa el nombre de la impresora primero', 'error');
            return;
        }
        
        try {
            // Primero validar que esté conectada
            showNotification('🔍 Validando conexión...', 'info');
            
            const validacion = await apiRequest(
                `/Dulcería/api/validar-impresora.php?accion=validar&nombre=${encodeURIComponent(nombreImpresora)}`
            );
            
            if (!validacion.success || !validacion.conectada) {
                const mensaje = validacion.sugerencia 
                    ? `❌ Impresora '${nombreImpresora}' no conectada.\n\n${validacion.sugerencia}` 
                    : `❌ Impresora '${nombreImpresora}' no conectada.`;
                
                showNotification(mensaje, 'error');
                mostrarEstadoImpresora(false, nombreImpresora);
                return;
            }
            
            showNotification('🖨️ Impresora conectada. Enviando ticket de prueba...', 'info');
            mostrarEstadoImpresora(true, nombreImpresora);
            
            const data = await apiRequest('/Dulcería/api/imprimir-termica.php', {
                method: 'POST',
                body: JSON.stringify({
                    tipo: 'prueba',
                    impresora: nombreImpresora
                })
            });
            
            if (data.success) {
                showNotification('✅ Ticket de prueba enviado correctamente. Verifica tu impresora.', 'success');
            } else {
                showNotification('❌ Error: ' + (data.error || data.message), 'error');
            }
        } catch (error) {
            console.error('Error al probar impresora:', error);
            showNotification('❌ Error al enviar ticket de prueba', 'error');
        }
    }
    
    // Detectar impresoras (función legacy, llama a buscarImpresoras)
    async function detectarImpresoras() {
        await buscarImpresoras();
    }
    
    // Mostrar lista de impresoras detectadas
    function mostrarImpresoras(impresoras) {
        const panel = document.getElementById('panelImpresoras');
        const lista = document.getElementById('listaImpresoras');
        
        // Categorizar impresoras
        const termicas = [];
        const normales = [];
        
        impresoras.forEach(imp => {
            const nombreLower = imp.nombre.toLowerCase();
            const esTermica = 
                nombreLower.includes('pos') || 
                nombreLower.includes('thermal') ||
                nombreLower.includes('termica') ||
                nombreLower.includes('receipt') ||
                nombreLower.includes('tm-') ||
                nombreLower.includes('tsp') ||
                nombreLower.includes('gprinter') ||
                nombreLower.includes('stmicro');
            
            if (esTermica) {
                termicas.push(imp);
            } else {
                normales.push(imp);
            }
        });
        
        let html = '';
        
        // Mostrar impresoras térmicas primero
        if (termicas.length > 0) {
            html += `
                <div class="mb-4">
                    <h4 class="font-bold text-purple-800 mb-3 flex items-center gap-2">
                        <i class="fas fa-receipt"></i>
                        Impresoras Térmicas (Recomendadas para tickets)
                    </h4>
            `;
            
            termicas.forEach((imp, index) => {
                const esLaConfigurada = imp.nombre === 'STMicroelectronics_POS58_Printer_USB' || 
                                       imp.nombre === 'Gprinter_Termica';
                
                html += `
                    <div class="flex items-center justify-between p-4 mb-3 border-2 ${esLaConfigurada ? 'border-green-500 bg-green-50' : 'border-purple-300 bg-purple-50'} rounded-lg hover:shadow-md transition-all animate-fadeIn" style="animation-delay: ${index * 0.05}s">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <i class="fas fa-receipt text-3xl ${esLaConfigurada ? 'text-green-600' : 'text-purple-600'} flex-shrink-0"></i>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-800 truncate" title="${imp.nombre}">${imp.nombre}</p>
                                <div class="flex items-center gap-2 mt-1 flex-wrap">
                                    <i class="fas fa-circle text-green-500 text-xs"></i> 
                                    <span class="text-sm text-gray-500">${imp.estado}</span>
                                    ${esLaConfigurada ? '<span class="px-2 py-0.5 bg-green-600 text-white text-xs rounded-full font-bold">✓ Configurada</span>' : ''}
                                    <span class="px-2 py-0.5 bg-purple-600 text-white text-xs rounded-full">Térmica 54mm</span>
                                    ${imp.so ? `<span class="text-xs text-gray-500">${imp.so}</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <button onclick="seleccionarImpresora('${imp.nombre.replace(/'/g, "\\'")}', true)" 
                            class="ml-3 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all flex items-center gap-2 shadow-lg flex-shrink-0">
                            <i class="fas fa-check"></i>
                            <span class="hidden sm:inline">Usar</span>
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
        }
        
        // Mostrar otras impresoras
        if (normales.length > 0) {
            html += `
                <div>
                    <h4 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-print"></i>
                        Otras Impresoras
                    </h4>
            `;
            
            normales.forEach((imp, index) => {
                html += `
                    <div class="flex items-center justify-between p-4 mb-3 border-2 border-gray-200 bg-gray-50 rounded-lg hover:shadow-md transition-all animate-fadeIn" style="animation-delay: ${(termicas.length + index) * 0.05}s">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <i class="fas fa-print text-3xl text-gray-600 flex-shrink-0"></i>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-800 truncate" title="${imp.nombre}">${imp.nombre}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <i class="fas fa-circle text-green-500 text-xs"></i> 
                                    <span class="text-sm text-gray-500">${imp.estado}</span>
                                    <span class="px-2 py-0.5 bg-gray-400 text-white text-xs rounded-full">No recomendada</span>
                                    ${imp.so ? `<span class="text-xs text-gray-500">${imp.so}</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <button onclick="seleccionarImpresora('${imp.nombre.replace(/'/g, "\\'")}', true)" 
                            class="ml-3 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all flex items-center gap-2 shadow-lg flex-shrink-0">
                            <i class="fas fa-check"></i>
                            <span class="hidden sm:inline">Usar</span>
                        </button>
                    </div>
                `;
            });
            
            html += '</div>';
        }
        
        // Mensaje informativo
        html += `
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Consejo:</strong> Las impresoras térmicas POS58 (54mm) son ideales para tickets de venta.
                </p>
            </div>
        `;
        
        lista.innerHTML = html;
        panel.classList.remove('hidden');
        
        // Scroll suave al panel
        setTimeout(() => {
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }, 100);
    }
    
    // Seleccionar impresora desde la lista
    async function seleccionarImpresora(nombre, validar = false) {
        document.getElementById('nombreImpresora').value = nombre;
        showNotification(`✅ Impresora seleccionada: ${nombre}`, 'success');
        
        // Cerrar panel
        cerrarPanelImpresoras();
        
        // Scroll hacia arriba para ver el formulario
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Validar automáticamente
        if (validar) {
            await validarImpresoraEnSegundoPlano(nombre);
        }
    }
    
    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', () => {
        detectarSistemaOperativo();
        cargarConfiguracion();
    });
    
    // Estilos CSS para animación
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.3s ease-out forwards;
            opacity: 0;
        }
    `;
    document.head.appendChild(style);
</script>
