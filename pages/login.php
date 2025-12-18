<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dulcería POS</title>
    
    <meta name="application-name" content="Dulcería POS">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#9333EA">
    
    <link rel="manifest" href="/DulceriaConejos/manifest.json">
    <link rel="icon" type="image/png" href="/DulceriaConejos/icons/icon-192x192.png">
    <link rel="apple-touch-icon" href="/DulceriaConejos/icons/apple-touch-icon.png">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        
        /* Animación de fondo gradiente */
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .animated-gradient {
            background: linear-gradient(-45deg, #9333EA, #C026D3, #EC4899, #9333EA);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        /* Glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Logo animado */
        .logo-bounce {
            animation: logoBounce 2s ease-in-out infinite;
        }
        
        @keyframes logoBounce {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-10px) scale(1.05); }
        }
        
        /* Spinner personalizado */
        .logo-spinner {
            animation: logoSpin 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }
        
        @keyframes logoSpin {
            0% { transform: rotate(0deg) scale(1); opacity: 0.8; }
            50% { transform: rotate(180deg) scale(1.1); opacity: 1; }
            100% { transform: rotate(360deg) scale(1); opacity: 0.8; }
        }
        
        /* Efecto de brillo en inputs */
        .input-glow:focus {
            box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
        }
        
        /* Partículas decorativas */
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            pointer-events: none;
            animation: float 20s infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); opacity: 0.3; }
            50% { transform: translate(100px, -100px) rotate(180deg); opacity: 0.6; }
        }
        
        .particle:nth-child(1) { width: 80px; height: 80px; top: 10%; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; top: 60%; left: 80%; animation-delay: 4s; }
        .particle:nth-child(3) { width: 100px; height: 100px; top: 80%; left: 20%; animation-delay: 8s; }
    </style>
</head>
<body class="animated-gradient min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Partículas decorativas -->
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    
    <div class="w-full max-w-md relative z-10">
        <!-- Logo y título -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-32 h-32 bg-white rounded-3xl shadow-2xl mb-6 logo-bounce p-4">
                <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Dulcería Conejos" class="w-full h-full object-contain">
            </div>
            <h1 class="text-5xl font-black text-white mb-3 drop-shadow-lg">Dulcería Conejos</h1>
            <p class="text-purple-100 text-lg font-medium flex items-center justify-center gap-2">
                <i class="fas fa-store"></i>
                Sistema de Inventario y Punto de Venta
            </p>
        </div>
        
        <!-- Formulario de login -->
        <div class="glass rounded-3xl shadow-2xl p-8 transform transition-all hover:scale-[1.02]">
            <div id="errorMessage" class="hidden mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-lg text-sm flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-lg mt-0.5"></i>
                <span id="errorText"></span>
            </div>
            
            <form id="loginForm" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <i class="fas fa-envelope text-purple-600"></i>
                        Correo electrónico
                    </label>
                    <input type="email" id="email" name="email" value="admin@dulceria.com" required
                        class="input-glow w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-purple-500 transition-all text-gray-700 font-medium">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <i class="fas fa-lock text-purple-600"></i>
                        Contraseña
                    </label>
                    <input type="password" id="password" name="password" value="admin123" required
                        class="input-glow w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-0 focus:border-purple-500 transition-all text-gray-700 font-medium">
                </div>
                
                <button type="submit" id="loginBtn"
                    class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold py-4 rounded-xl hover:from-purple-700 hover:to-pink-700 transform hover:-translate-y-1 transition-all shadow-lg hover:shadow-2xl flex items-center justify-center gap-2 text-lg">
                    <span id="loginBtnText">Iniciar Sesión</span>
                </button>
            </form>
            
        </div>
        
        <div class="text-center mt-6 space-y-2">
            <p class="text-white text-sm font-medium flex items-center justify-center gap-2">
                <i class="fas fa-shield-alt"></i>
                Sistema seguro y confiable
            </p>
            <p class="text-purple-100 text-xs">
                © 2025 Dulcería Conejos - Todos los derechos reservados
            </p>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            const loginBtn = document.getElementById('loginBtn');
            const loginBtnText = document.getElementById('loginBtnText');
            
            // Ocultar mensaje de error previo
            errorDiv.classList.add('hidden');
            
            // Mostrar spinner de carga
            loginBtn.disabled = true;
            loginBtn.classList.add('opacity-75', 'cursor-not-allowed');
            loginBtnText.innerHTML = `
                <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-6 w-6 object-contain inline-block">
                <span>Iniciando sesión...</span>
            `;
            
            try {
                const response = await fetch('/DulceriaConejos/api/auth.php/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ email, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Guardar el token
                    localStorage.setItem('token', data.data.token);
                    localStorage.setItem('usuario', JSON.stringify({
                        id_usuario: data.data.usuario.id,
                        nombre: data.data.usuario.nombre,
                        email: data.data.usuario.email,
                        rol_nombre: data.data.usuario.rol,
                        id_rol: data.data.usuario.rol === 'dueño' ? 1 : 2,
                        permisos: data.data.usuario.permisos
                    }));
                    
                    // Mostrar mensaje de éxito
                    loginBtnText.innerHTML = `
                        <i class="fas fa-check-circle"></i>
                        <span>¡Bienvenido!</span>
                    `;
                    loginBtn.classList.remove('from-purple-600', 'to-pink-600');
                    loginBtn.classList.add('from-green-500', 'to-green-600');
                    
                    // Redirigir al POS después de un breve delay
                    setTimeout(() => {
                        window.location.href = '/DulceriaConejos/pages/pos.php';
                    }, 800);
                } else {
                    // Mostrar error
                    errorText.textContent = data.message || 'Credenciales incorrectas. Verifica tu email y contraseña.';
                    errorDiv.classList.remove('hidden');
                    
                    // Restaurar botón
                    loginBtn.disabled = false;
                    loginBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                    loginBtnText.innerHTML = `
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Iniciar Sesión</span>
                    `;
                }
            } catch (error) {
                console.error('Error al iniciar sesión:', error);
                errorText.textContent = 'Error de conexión. Por favor, verifica que el servidor esté funcionando.';
                errorDiv.classList.remove('hidden');
                
                // Restaurar botón
                loginBtn.disabled = false;
                loginBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                loginBtnText.innerHTML = `
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Iniciar Sesión</span>
                `;
            }
        });
        
        // Verificar si ya hay sesión activa
        const token = localStorage.getItem('token');
        if (token) {
            // Verificar si el token es válido
            fetch('/DulceriaConejos/api/auth.php/me', {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ya hay sesión activa, redirigir al POS
                    window.location.href = '/DulceriaConejos/pages/pos.php';
                }
            })
            .catch(() => {
                // Token inválido, limpiar
                localStorage.removeItem('token');
                localStorage.removeItem('usuario');
            });
        }
    </script>
</body>
</html>
