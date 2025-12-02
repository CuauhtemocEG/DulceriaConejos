<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - Dulcería POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }
        
        .pulse-slow {
            animation: pulse-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse-slow {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .7;
            }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <!-- Ilustración animada -->
        <div class="text-center mb-8">
            <div class="float-animation inline-block">
                <div class="relative">
                    <!-- Icono principal -->
                    <div class="w-32 h-32 mx-auto bg-gradient-to-br from-red-400 to-pink-500 rounded-full flex items-center justify-center shadow-2xl">
                        <i class="fas fa-ban text-white text-6xl"></i>
                    </div>
                    <!-- Círculo pulsante de fondo -->
                    <div class="absolute inset-0 w-32 h-32 mx-auto bg-red-400 rounded-full pulse-slow -z-10 blur-xl opacity-50"></div>
                </div>
            </div>
        </div>
        
        <!-- Contenido principal -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header con degradado -->
            <div class="bg-gradient-to-r from-red-500 to-pink-500 p-8 text-center text-white">
                <h1 class="text-4xl font-bold mb-2">🚫 Acceso Denegado</h1>
                <p class="text-red-100 text-lg">No tienes permisos para acceder a esta sección</p>
            </div>
            
            <!-- Cuerpo del mensaje -->
            <div class="p-8 space-y-6">
                <!-- Mensaje principal -->
                <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-r-lg">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-500 text-3xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-red-800 mb-2">Permisos Insuficientes</h3>
                            <p class="text-red-700">
                                Tu rol actual no tiene los permisos necesarios para ver o interactuar con este módulo del sistema.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-lg">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-500 text-3xl"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-blue-800 mb-2">¿Necesitas Acceso?</h3>
                            <p class="text-blue-700 mb-3">
                                Si crees que deberías tener acceso a esta sección, contacta con un administrador del sistema para que revise tus permisos.
                            </p>
                            <ul class="space-y-2 text-blue-600">
                                <li class="flex items-center gap-2">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Verifica que tu rol tenga los permisos adecuados</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Solicita acceso al administrador del sistema</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Consulta la documentación de roles y permisos</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Información del usuario -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-700 mb-3">Información de tu Sesión:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user text-purple-500"></i>
                            <span class="text-gray-600">Usuario:</span>
                            <span class="font-semibold text-gray-800" id="userName">Cargando...</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user-tag text-purple-500"></i>
                            <span class="text-gray-600">Rol:</span>
                            <span class="font-semibold text-gray-800" id="userRole">Cargando...</span>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                    <button onclick="window.history.back()" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-gray-600 to-gray-700 text-white rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all shadow-lg flex items-center justify-center gap-2 font-semibold">
                        <i class="fas fa-arrow-left"></i>
                        <span>Volver Atrás</span>
                    </button>
                    <a href="/Dulcería/pages/dashboard.php" 
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 transition-all shadow-lg flex items-center justify-center gap-2 font-semibold">
                        <i class="fas fa-home"></i>
                        <span>Ir al Inicio</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer informativo -->
        <div class="mt-6 text-center text-gray-600 text-sm">
            <p>
                <i class="fas fa-shield-alt text-purple-500 mr-1"></i>
                Este sistema está protegido por un control de acceso basado en roles (RBAC)
            </p>
        </div>
    </div>
    
    <script>
        // Obtener información del usuario
        const user = JSON.parse(localStorage.getItem('usuario') || '{}');
        
        if (user.nombre) {
            document.getElementById('userName').textContent = user.nombre;
        }
        
        if (user.rol_nombre) {
            document.getElementById('userRole').textContent = user.rol_nombre;
        }
        
        // Si no hay usuario en localStorage, redirigir al login
        if (!user.id_usuario) {
            window.location.href = '/Dulcería/pages/login.php';
        }
    </script>
</body>
</html>
