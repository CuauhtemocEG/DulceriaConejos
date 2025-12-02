<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - Dulcería POS</title>
    
    <meta name="application-name" content="Dulcería POS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Dulcería">
    <meta name="description" content="Sistema de Punto de Venta para Dulcería - Gestión de inventario, ventas y reportes">
    <meta name="theme-color" content="#9333EA">
    
    <link rel="manifest" href="/Dulcería/manifest.json">
    
    <link rel="icon" type="image/png" sizes="32x32" href="/Dulcería/icons/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/Dulcería/icons/icon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/Dulcería/icons/icon-180x180.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/Dulcería/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/Dulcería/icons/icon-144x144.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap');
        
        body {
            font-family: 'Montserrat', sans-serif;
        }
        
        .sidebar-enter {
            transform: translateX(-100%);
        }
        
        .sidebar-enter-active {
            transform: translateX(0);
            transition: transform 0.3s ease-out;
        }
        
        /* Estilos elegantes para el sidebar */
        .nav-link {
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: linear-gradient(to bottom, #fff, #fbbf24);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .nav-link:hover::before,
        .nav-link.active::before {
            transform: scaleY(1);
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(4px);
        }
        
        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }
        
        .section-title {
            position: relative;
            padding-left: 12px;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: linear-gradient(to bottom, #fbbf24, #f59e0b);
            border-radius: 2px;
        }
        
        /* Animación suave para los items del menú */
        .menu-item {
            animation: fadeInLeft 0.3s ease forwards;
            opacity: 0;
        }
        
        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Efecto de brillo en el logo */
        .logo-container {
            position: relative;
            animation: pulse 3s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 20px rgba(251, 191, 36, 0.3);
            }
            50% {
                box-shadow: 0 0 30px rgba(251, 191, 36, 0.5);
            }
        }
        
        /* Scrollbar personalizado */
        aside::-webkit-scrollbar {
            width: 6px;
        }
        
        aside::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        aside::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        aside::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Spinner personalizado con logo DulceriaConejos */
        .logo-spinner {
            display: inline-block;
            animation: logoSpin 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }
        
        @keyframes logoSpin {
            0% {
                transform: rotate(0deg) scale(1);
                opacity: 0.8;
            }
            50% {
                transform: rotate(180deg) scale(1.1);
                opacity: 1;
            }
            100% {
                transform: rotate(360deg) scale(1);
                opacity: 0.8;
            }
        }
        
        .logo-spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        /* Pulso debajo del logo */
        .logo-spinner::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60%;
            height: 4px;
            background: linear-gradient(to right, transparent, #9333EA, transparent);
            border-radius: 2px;
            animation: pulse-line 1.5s ease-in-out infinite;
        }
        
        @keyframes pulse-line {
            0%, 100% {
                opacity: 0.3;
                width: 60%;
            }
            50% {
                opacity: 0.8;
                width: 80%;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile menu overlay -->
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden" onclick="toggleMobileMenu()"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 w-64 bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 text-white transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out z-50 overflow-y-auto shadow-2xl">
        <!-- Header con logo -->
        <div class="p-6 border-b border-white/10 bg-black/20 backdrop-blur-sm">
            <div class="flex items-center justify-center mb-3 logo-container bg-white/10 rounded-2xl p-4 backdrop-blur-md">
                <img src="/Dulcería/public/img/DulceriaConejos.png" alt="Dulcería Conejos" class="h-16 w-auto drop-shadow-lg">
            </div>
            <div class="text-center">
                <p class="text-sm text-amber-300 font-semibold tracking-wide">Dulcería Conejos</p>
                <div class="mt-2 h-1 w-16 bg-gradient-to-r from-transparent via-amber-400 to-transparent mx-auto rounded-full"></div>
            </div>
        </div>
        
        <nav class="p-4 pb-24">
            <ul class="space-y-1">
                <!-- Inicio -->
                <li id="menuDashboard" class="hidden menu-item" style="animation-delay: 0.05s">
                    <a href="/Dulcería/pages/dashboard.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                        <i class="fas fa-home text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                        <span class="text-sm font-medium">Inicio</span>
                    </a>
                </li>
                
                <!-- Catálogo e Inventario -->
                <li id="seccionCatalogo" class="hidden">
                    <div class="px-4 py-3 mt-6 mb-2">
                        <p class="section-title text-xs font-bold text-amber-300 uppercase tracking-widest">Catálogo e Inventario</p>
                    </div>
                    <ul class="space-y-1">
                        <li id="menuInventario" class="hidden menu-item" style="animation-delay: 0.1s">
                            <a href="/Dulcería/pages/inventario.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-warehouse text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Gestión de Inventario</span>
                            </a>
                        </li>
                        <li id="menuProductos" class="hidden menu-item" style="animation-delay: 0.15s">
                            <a href="/Dulcería/pages/productos.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-boxes text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Gestión de Productos</span>
                            </a>
                        </li>
                        <li id="menuTemporadas" class="hidden menu-item" style="animation-delay: 0.2s">
                            <a href="/Dulcería/pages/temporadas.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-calendar-alt text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Lista de Temporadas</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Ventas -->
                <li id="seccionVentas" class="hidden">
                    <div class="px-4 py-3 mt-6 mb-2">
                        <p class="section-title text-xs font-bold text-amber-300 uppercase tracking-widest">Ventas</p>
                    </div>
                    <ul class="space-y-1">
                        <li id="menuPos" class="menu-item" style="animation-delay: 0.25s">
                            <a href="/Dulcería/pages/pos.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-cash-register text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Punto de Venta (POS)</span>
                            </a>
                        </li>
                        <li id="menuVentas" class="hidden menu-item" style="animation-delay: 0.3s">
                            <a href="/Dulcería/pages/ventas.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-shopping-cart text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Gestión de Ordenes</span>
                            </a>
                        </li>
                        <li id="menuReportes" class="hidden menu-item" style="animation-delay: 0.35s">
                            <a href="/Dulcería/pages/reportes.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-chart-line text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Reportes de Sistema</span>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Configuración de Sistema -->
                <li id="seccionConfiguracion" class="hidden">
                    <div class="px-4 py-3 mt-6 mb-2">
                        <p class="section-title text-xs font-bold text-amber-300 uppercase tracking-widest">Configuración de Sistema</p>
                    </div>
                    <ul class="space-y-1">
                        <li id="menuUsuarios" class="hidden menu-item" style="animation-delay: 0.4s">
                            <a href="/Dulcería/pages/usuarios.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-users text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Gestión de Usuarios</span>
                            </a>
                        </li>
                        <li id="menuRoles" class="hidden menu-item" style="animation-delay: 0.45s">
                            <a href="/Dulcería/pages/roles.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-user-shield text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Roles y Permisos</span>
                            </a>
                        </li>
                        <li id="menuConfiguracion" class="hidden menu-item" style="animation-delay: 0.5s">
                            <a href="/Dulcería/pages/configuracion.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-cog text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Configuración</span>
                            </a>
                        </li>
                        <li id="menuConfigImpresora" class="hidden menu-item" style="animation-delay: 0.55s">
                            <a href="/Dulcería/pages/config-impresora.php" class="nav-link flex items-center px-4 py-3 rounded-xl hover:bg-white/20 transition-all duration-200 group">
                                <i class="fas fa-print text-xl mr-3 transform transition-transform group-hover:scale-110"></i>
                                <span class="text-sm font-medium">Config. Impresora</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        
        <!-- Footer del sidebar -->
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-black/20 backdrop-blur-sm border-t border-white/10">
            <div class="text-center">
                <p class="text-xs text-white/60">Dulcería Conejos</p>
                <p class="text-xs text-white/40 mt-1">© 2025 Inventory and POS System</p>
            </div>
        </div>
    </aside>
    
    <!-- Main content -->
    <div class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Top navbar -->
        <header class="bg-white shadow-sm sticky top-0 z-30">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-3">
                    <button onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h2 class="text-xl font-semibold text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h2>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="hidden sm:block text-right">
                        <div class="text-sm font-semibold text-gray-800" id="userName"></div>
                        <div class="text-xs text-gray-500" id="userEmail"></div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white font-bold" id="userAvatar"></div>
                    <button onclick="logout()" class="hidden sm:block px-4 py-2 bg-red-500 text-white text-sm rounded-lg hover:bg-red-600 transition-colors">
                        Salir
                    </button>
                    <button onclick="logout()" class="sm:hidden p-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </header>
        
        <!-- Page content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <?php echo $content ?? ''; ?>
        </main>
    </div>
    
    <script>
        // Obtener datos del usuario
        const user = JSON.parse(localStorage.getItem('usuario') || '{}');
        const token = localStorage.getItem('token');
        
        if (!token || !user.id_usuario) {
            window.location.href = '/Dulcería/pages/login.php';
        }
        
        // Mostrar información del usuario
        document.getElementById('userName').textContent = user.nombre || '';
        document.getElementById('userEmail').textContent = user.email || '';
        document.getElementById('userAvatar').textContent = (user.nombre || 'U').charAt(0).toUpperCase();
        
        // Mostrar menús según permisos del usuario
        mostrarMenusSegunPermisos(user);
        
        // Marcar link activo
        const currentPath = window.location.pathname;
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('bg-white/20');
            }
        });
        
        // Toggle mobile menu
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        // Mostrar menús según permisos del usuario
        function mostrarMenusSegunPermisos(user) {
            const permisos = user.permisos || {};
            const visibilidad = user.visibilidad_menu || {};
            
            // Función helper para verificar si tiene algún permiso en un módulo
            const tienePermisoEn = (modulo) => {
                return permisos[modulo] && Array.isArray(permisos[modulo]) && permisos[modulo].length > 0;
            };
            
            // Función helper para verificar si el menú debe ser visible
            const menuVisible = (modulo) => {
                // Si no tiene permisos, no se muestra
                if (!tienePermisoEn(modulo)) return false;
                // Si tiene permisos, verificar visibilidad (por defecto true si no está especificado)
                return visibilidad[modulo] !== false;
            };
            
            // Contadores para secciones
            let catalogoVisible = false;
            let ventasVisible = false;
            let configuracionVisible = false;
            
            // === INICIO ===
            // Dashboard siempre visible para dueño, o según permisos para otros roles
            if (menuVisible('dashboard') || (user.id_rol == 1 && visibilidad['dashboard'] !== false)) {
                document.getElementById('menuDashboard').classList.remove('hidden');
            }
            
            // === CATÁLOGO E INVENTARIO ===
            
            // Gestión de Inventario
            if (menuVisible('inventario') || (user.id_rol == 1 && visibilidad['inventario'] !== false)) {
                document.getElementById('menuInventario').classList.remove('hidden');
                catalogoVisible = true;
            }
            
            // Gestión de Productos
            if (menuVisible('productos') || (user.id_rol == 1 && visibilidad['productos'] !== false)) {
                document.getElementById('menuProductos').classList.remove('hidden');
                catalogoVisible = true;
            }
            
            // Lista de Temporadas
            if (menuVisible('temporadas') || (user.id_rol == 1 && visibilidad['temporadas'] !== false)) {
                document.getElementById('menuTemporadas').classList.remove('hidden');
                catalogoVisible = true;
            }
            
            // Mostrar sección si hay al menos un item visible
            if (catalogoVisible) {
                document.getElementById('seccionCatalogo').classList.remove('hidden');
            }
            
            // === VENTAS ===
            
            // Punto de Venta (POS) - siempre visible (todos pueden vender)
            document.getElementById('menuPos').classList.remove('hidden');
            ventasVisible = true;
            
            // Gestión de Ordenes (antes Ventas)
            if (menuVisible('ventas') || (user.id_rol == 1 && visibilidad['ventas'] !== false)) {
                document.getElementById('menuVentas').classList.remove('hidden');
                ventasVisible = true;
            }
            
            // Reportes de Sistema
            if (menuVisible('reportes') || (user.id_rol == 1 && visibilidad['reportes'] !== false)) {
                document.getElementById('menuReportes').classList.remove('hidden');
                ventasVisible = true;
            }
            
            // Mostrar sección Ventas (siempre visible porque POS siempre está)
            if (ventasVisible) {
                document.getElementById('seccionVentas').classList.remove('hidden');
            }
            
            // === CONFIGURACIÓN DE SISTEMA ===
            
            // Gestión de Usuarios - verificar permisos y visibilidad
            if (menuVisible('usuarios') || (user.id_rol == 1 && visibilidad['usuarios'] !== false)) {
                document.getElementById('menuUsuarios').classList.remove('hidden');
                configuracionVisible = true;
            }
            
            // Roles y Permisos - solo para dueño Y verificar visibilidad
            if (user.id_rol == 1) {
                // Para el dueño, roles siempre está disponible pero verificar visibilidad
                // Si no está definido en visibilidad, se muestra por defecto
                document.getElementById('menuRoles').classList.remove('hidden');
                configuracionVisible = true;
            }
            
            // Configuración - disponible para usuarios con permisos
            if (menuVisible('configuracion') || (user.id_rol == 1 && visibilidad['configuracion'] !== false)) {
                document.getElementById('menuConfiguracion').classList.remove('hidden');
                configuracionVisible = true;
            }
            
            // Configuración de Impresora - disponible para usuarios con permisos o dueño
            if (menuVisible('configuracion') || (user.id_rol == 1 && visibilidad['configuracion'] !== false)) {
                document.getElementById('menuConfigImpresora').classList.remove('hidden');
                configuracionVisible = true;
            }
            
            // Mostrar sección si hay al menos un item visible
            if (configuracionVisible) {
                document.getElementById('seccionConfiguracion').classList.remove('hidden');
            }
        }
        
        // Cerrar menú mobile al hacer click en un link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    toggleMobileMenu();
                }
            });
        });
        
        // Función para cerrar sesión
        async function logout() {
            if (!confirm('¿Está seguro de cerrar sesión?')) {
                return;
            }
            
            try {
                await apiRequest('/Dulcería/api/auth.php/logout', {
                    method: 'POST'
                });
            } catch (error) {
                console.error('Error al cerrar sesión:', error);
            }
            
            localStorage.clear();
            window.location.href = '/Dulcería/pages/login.php';
        }
        
        // Función helper para hacer peticiones a la API
        async function apiRequest(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                }
            };
            
            const mergedOptions = {
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...(options.headers || {})
                }
            };
            
            const response = await fetch(url, mergedOptions);
            const data = await response.json();
            
            if (response.status === 401) {
                // Mostrar mensaje personalizado si la sesión fue cerrada remotamente
                if (data.message && data.message.includes('cerrada por un administrador')) {
                    showNotification('Tu sesión ha sido cerrada por un administrador', 'warning');
                    setTimeout(() => {
                        logout();
                    }, 2000);
                } else {
                    alert('Sesión expirada. Por favor inicie sesión nuevamente.');
                    logout();
                }
                return;
            }
            
            return data;
        }
        
        // Helper para mostrar notificaciones
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-4 z-50 px-6 py-4 rounded-lg shadow-lg text-white transform transition-all duration-300 translate-x-full ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                type === 'warning' ? 'bg-yellow-500' : 'bg-blue-500'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // ═══════════════════════════════════════════════════════════════════════
        // PWA - SERVICE WORKER REGISTRATION
        // ═══════════════════════════════════════════════════════════════════════
        
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/Dulcería/sw.js')
                    .then((registration) => {
                        console.log('✅ Service Worker registrado:', registration.scope);
                        
                        // Verificar actualizaciones
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // Nueva versión disponible
                                    showUpdateNotification();
                                }
                            });
                        });
                    })
                    .catch((error) => {
                        console.error('❌ Error al registrar Service Worker:', error);
                    });
            });
        }

        // Mostrar notificación de actualización disponible
        function showUpdateNotification() {
            const updateBanner = document.createElement('div');
            updateBanner.className = 'fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-4 rounded-lg shadow-2xl z-[100] max-w-md w-full mx-4';
            updateBanner.innerHTML = `
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-sync-alt text-2xl"></i>
                        <div>
                            <p class="font-bold">Nueva versión disponible</p>
                            <p class="text-sm opacity-90">Actualiza para obtener las últimas mejoras</p>
                        </div>
                    </div>
                    <button onclick="location.reload()" class="px-4 py-2 bg-white text-purple-600 rounded-lg font-semibold hover:bg-gray-100 transition-all">
                        Actualizar
                    </button>
                </div>
            `;
            document.body.appendChild(updateBanner);
        }

        // Detectar cuando la app está offline/online
        window.addEventListener('offline', () => {
            showNotification('Sin conexión a internet. Trabajando en modo offline.', 'warning');
        });

        window.addEventListener('online', () => {
            showNotification('Conexión restablecida', 'success');
        });

        // PWA - Prompt de instalación
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallPromo();
        });

        function showInstallPromo() {
            // Verificar si ya se mostró antes
            if (localStorage.getItem('pwa-install-dismissed')) {
                return;
            }

            const installBanner = document.createElement('div');
            installBanner.id = 'install-banner';
            installBanner.className = 'fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-4 rounded-lg shadow-2xl z-[100] max-w-md w-full mx-4 animate-bounce';
            installBanner.innerHTML = `
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-download text-2xl"></i>
                        <div>
                            <p class="font-bold">Instalar Dulcería POS</p>
                            <p class="text-sm opacity-90">Acceso rápido desde tu pantalla de inicio</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="dismissInstall()" class="px-3 py-2 text-white hover:bg-white hover:bg-opacity-20 rounded-lg transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                        <button onclick="installPWA()" class="px-4 py-2 bg-white text-purple-600 rounded-lg font-semibold hover:bg-gray-100 transition-all">
                            Instalar
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(installBanner);
        }

        async function installPWA() {
            if (!deferredPrompt) {
                return;
            }

            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('✅ PWA instalada');
                showNotification('¡App instalada correctamente!', 'success');
            }
            
            deferredPrompt = null;
            document.getElementById('install-banner')?.remove();
        }

        function dismissInstall() {
            localStorage.setItem('pwa-install-dismissed', 'true');
            document.getElementById('install-banner')?.remove();
        }

        // Detectar cuando la app fue instalada
        window.addEventListener('appinstalled', () => {
            console.log('✅ PWA instalada exitosamente');
            showNotification('¡Bienvenido a Dulcería POS!', 'success');
            deferredPrompt = null;
        });
    </script>
</body>
</html>
