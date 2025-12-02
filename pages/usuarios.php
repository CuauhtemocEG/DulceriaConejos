<?php
$pageTitle = 'Gestión de Usuarios';
ob_start();
?>

<div class="space-y-6">
    <!-- Acciones -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Usuarios</h2>
                <p class="text-gray-600 text-sm mt-1">Solo el dueño puede gestionar usuarios</p>
            </div>
            <button onclick="abrirModalUsuario()" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700 font-medium">
                + Nuevo usuario
            </button>
        </div>
    </div>
    
    <!-- Tabla de usuarios -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Conexión</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Último acceso</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaUsuarios" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            <div class="logo-spinner-container">
                                <div class="relative">
                                    <img src="/Dulcería/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-12 w-12 object-contain">
                                </div>
                                <p class="mt-2">Cargando usuarios...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de usuario -->
<div id="modalUsuario" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-4 rounded-t-lg">
            <h3 class="text-xl font-bold text-white" id="tituloModal">Nuevo usuario</h3>
        </div>
        
        <form id="formUsuario" class="p-6">
            <input type="hidden" id="idUsuario">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nombre completo</label>
                    <input type="text" id="nombre" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rol</label>
                    <select id="rol" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <option value="">Seleccione un rol...</option>
                    </select>
                </div>
                
                <div id="campoPassword">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <span id="labelPassword">Contraseña</span>
                    </label>
                    <input type="password" id="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1" id="helperPassword">Mínimo 6 caracteres</p>
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="activo" checked class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                    <label for="activo" class="ml-2 block text-sm text-gray-700">Usuario activo</label>
                </div>
                
                <!-- Opciones adicionales solo en modo edición -->
                <div id="opcionesEdicion" class="hidden space-y-3 border-t pt-4">
                    <button type="button" id="btnCerrarSesion" onclick="cerrarSesionUsuario()" 
                        class="w-full px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 flex items-center justify-center gap-2">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar sesión del usuario</span>
                    </button>
                    <p class="text-xs text-gray-500 text-center">
                        <i class="fas fa-info-circle"></i> 
                        Esto forzará al usuario a volver a iniciar sesión
                    </p>
                </div>
            </div>
            
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="cerrarModalUsuario()" 
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" 
                    class="flex-1 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:from-purple-700 hover:to-pink-700">
                    Guardar
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
    let usuarios = [];
    let roles = [];
    let esEdicion = false;
    
    async function cargarDatos() {
        try {
            // Cargar roles
            const rolesData = await apiRequest('/Dulcería/api/usuarios.php/roles');
            if (rolesData.success) {
                roles = rolesData.data;
                const select = document.getElementById('rol');
                rolesData.data.forEach(r => {
                    const option = document.createElement('option');
                    option.value = r.id;
                    option.textContent = r.nombre;
                    select.appendChild(option);
                });
            }
            
            // Cargar usuarios
            await cargarUsuarios();
            
        } catch (error) {
            console.error('Error al cargar datos:', error);
            showNotification('Error al cargar datos', 'error');
        }
    }
    
    async function cargarUsuarios() {
        try {
            const data = await apiRequest('/Dulcería/api/usuarios.php');
            if (data.success) {
                usuarios = data.data;
                mostrarUsuarios(usuarios);
            }
        } catch (error) {
            console.error('Error al cargar usuarios:', error);
            const tbody = document.getElementById('tablaUsuarios');
            if (error.message && error.message.includes('403')) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-red-600">Solo el dueño puede ver los usuarios</td></tr>';
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Error al cargar usuarios</td></tr>';
            }
        }
    }
    
    function mostrarUsuarios(users) {
        const tbody = document.getElementById('tablaUsuarios');
        
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No hay usuarios registrados</td></tr>';
            return;
        }
        
        let html = '';
        users.forEach(u => {
            const estadoBadge = u.activo == 1 ?
                '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Activo</span>' :
                '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Inactivo</span>';
            
            const rolBadge = {
                '1': '<span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">Dueño</span>',
                '2': '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Gerente</span>'
            };
            
            // Estado de conexión con colores
            let conexionBadge = '';
            switch(u.estado_conexion) {
                case 'En línea':
                    conexionBadge = '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800 flex items-center gap-1"><span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>En línea</span>';
                    break;
                case 'Hace poco':
                    conexionBadge = '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">Hace poco</span>';
                    break;
                case 'Hoy':
                    conexionBadge = '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Hoy</span>';
                    break;
                case 'Nunca':
                    conexionBadge = '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Nunca</span>';
                    break;
                default:
                    conexionBadge = `<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">${u.estado_conexion}</span>`;
            }
            
            const ultimoAcceso = u.ultimo_acceso ? 
                new Date(u.ultimo_acceso).toLocaleString('es-MX', {dateStyle: 'short', timeStyle: 'short'}) : 
                'Nunca';
            
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="relative">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold">
                                    ${u.nombre.charAt(0).toUpperCase()}
                                </div>
                                ${u.esta_conectado == 1 ? '<div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-white rounded-full" title="Conectado"></div>' : ''}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${u.nombre}</div>
                                <div class="text-xs text-gray-500 md:hidden">${u.email}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900 hidden md:table-cell">${u.email}</td>
                    <td class="px-4 py-3">${rolBadge[u.rol_id] || u.rol_nombre}</td>
                    <td class="px-4 py-3">${estadoBadge}</td>
                    <td class="px-4 py-3 hidden lg:table-cell">${conexionBadge}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 hidden lg:table-cell">${ultimoAcceso}</td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button onclick='editarUsuario(${JSON.stringify(u)})' class="text-blue-600 hover:text-blue-700" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            ${u.rol_id != 1 ? `
                                <button onclick="eliminarUsuario(${u.id})" class="text-red-600 hover:text-red-700" title="Eliminar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
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
    
    function abrirModalUsuario() {
        esEdicion = false;
        document.getElementById('tituloModal').textContent = 'Nuevo usuario';
        document.getElementById('formUsuario').reset();
        document.getElementById('idUsuario').value = '';
        document.getElementById('campoPassword').style.display = 'block';
        document.getElementById('password').required = true;
        document.getElementById('labelPassword').textContent = 'Contraseña';
        document.getElementById('helperPassword').textContent = 'Mínimo 6 caracteres';
        document.getElementById('opcionesEdicion').classList.add('hidden');
        document.getElementById('modalUsuario').classList.remove('hidden');
    }
    
    function editarUsuario(usuario) {
        esEdicion = true;
        document.getElementById('tituloModal').textContent = 'Editar usuario';
        
        document.getElementById('idUsuario').value = usuario.id;
        document.getElementById('nombre').value = usuario.nombre;
        document.getElementById('email').value = usuario.email;
        document.getElementById('rol').value = usuario.rol_id;
        document.getElementById('activo').checked = usuario.activo == 1;
        
        // Mostrar campo de contraseña pero como opcional
        document.getElementById('campoPassword').style.display = 'block';
        document.getElementById('password').required = false;
        document.getElementById('password').value = '';
        document.getElementById('labelPassword').textContent = 'Nueva contraseña (opcional)';
        document.getElementById('helperPassword').textContent = 'Déjalo en blanco para mantener la contraseña actual';
        
        // Mostrar opciones adicionales solo si no es el dueño
        if (usuario.rol_id != 1) {
            document.getElementById('opcionesEdicion').classList.remove('hidden');
            // Actualizar estado del botón de cerrar sesión
            const btnCerrarSesion = document.getElementById('btnCerrarSesion');
            if (usuario.esta_conectado == 1) {
                btnCerrarSesion.disabled = false;
                btnCerrarSesion.classList.remove('opacity-50', 'cursor-not-allowed');
                btnCerrarSesion.innerHTML = '<i class="fas fa-sign-out-alt"></i><span>Cerrar sesión del usuario</span>';
            } else {
                btnCerrarSesion.disabled = true;
                btnCerrarSesion.classList.add('opacity-50', 'cursor-not-allowed');
                btnCerrarSesion.innerHTML = '<i class="fas fa-times-circle"></i><span>Usuario no conectado</span>';
            }
        } else {
            document.getElementById('opcionesEdicion').classList.add('hidden');
        }
        
        document.getElementById('modalUsuario').classList.remove('hidden');
    }
    
    function cerrarModalUsuario() {
        document.getElementById('modalUsuario').classList.add('hidden');
    }
    
    document.getElementById('formUsuario').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const usuario = {
            nombre: document.getElementById('nombre').value,
            email: document.getElementById('email').value,
            rol_id: parseInt(document.getElementById('rol').value),
            activo: document.getElementById('activo').checked ? 1 : 0
        };
        
        if (!esEdicion || document.getElementById('password').value) {
            usuario.password = document.getElementById('password').value;
        }
        
        try {
            let url = '/Dulcería/api/usuarios.php';
            let method = 'POST';
            
            if (esEdicion) {
                const id = document.getElementById('idUsuario').value;
                url += `/${id}`;
                method = 'PUT';
            }
            
            const data = await apiRequest(url, {
                method: method,
                body: JSON.stringify(usuario)
            });
            
            if (data.success) {
                showNotification(esEdicion ? 'Usuario actualizado' : 'Usuario creado', 'success');
                cerrarModalUsuario();
                await cargarUsuarios();
            } else {
                showNotification(data.message || 'Error al guardar usuario', 'error');
            }
            
        } catch (error) {
            console.error('Error al guardar usuario:', error);
            showNotification('Error al guardar usuario', 'error');
        }
    });
    
    async function eliminarUsuario(id) {
        if (!confirm('¿Está seguro de eliminar este usuario?')) {
            return;
        }
        
        try {
            const data = await apiRequest(`/Dulcería/api/usuarios.php/${id}`, {
                method: 'DELETE'
            });
            
            if (data.success) {
                showNotification('Usuario eliminado', 'success');
                await cargarUsuarios();
            } else {
                showNotification(data.message || 'Error al eliminar usuario', 'error');
            }
            
        } catch (error) {
            console.error('Error al eliminar usuario:', error);
            showNotification('Error al eliminar usuario', 'error');
        }
    }
    
    async function cerrarSesionUsuario() {
        const id = document.getElementById('idUsuario').value;
        
        if (!confirm('¿Está seguro de cerrar la sesión de este usuario? Se le forzará a volver a iniciar sesión.')) {
            return;
        }
        
        try {
            const data = await apiRequest(`/Dulcería/api/usuarios.php/${id}/logout`, {
                method: 'POST'
            });
            
            if (data.success) {
                showNotification('Sesión cerrada exitosamente', 'success');
                cerrarModalUsuario();
                await cargarUsuarios();
            } else {
                showNotification(data.message || 'Error al cerrar sesión', 'error');
            }
            
        } catch (error) {
            console.error('Error al cerrar sesión:', error);
            showNotification('Error al cerrar sesión del usuario', 'error');
        }
    }
    
    // Inicializar
    cargarDatos();
</script>
