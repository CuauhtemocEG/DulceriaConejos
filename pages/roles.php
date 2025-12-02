<?php
$pageTitle = 'Roles y Permisos';
ob_start();
?>

<style>
    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .module-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        padding: 1.5rem;
        color: white;
        transition: transform 0.2s;
    }
    
    .module-card:hover {
        transform: translateY(-2px);
    }
    
    .permission-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
    }
    
    .permission-checkbox:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .permission-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">🔐 Roles y Permisos</h1>
            <p class="text-gray-600 mt-1">Gestiona los roles y permisos de los usuarios del sistema</p>
        </div>
        <button onclick="abrirModalNuevoRol()" 
            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:shadow-lg transition-all flex items-center gap-2">
            <i class="fas fa-plus"></i>
            Nuevo Rol
        </button>
    </div>
    
    <!-- Lista de roles -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-purple-600 to-pink-600 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Rol</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Descripción</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Usuarios</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Permisos</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaRoles" class="divide-y divide-gray-200">
                    <!-- Loading state -->
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="logo-spinner-container">
                                <div class="relative">
                                    <img src="/Dulcería/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-12 w-12 object-contain">
                                </div>
                                <p class="text-lg font-semibold mt-2">Cargando roles...</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal crear/editar rol -->
<div id="modalRol" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-6 rounded-t-2xl">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold" id="modalTitulo">Nuevo Rol</h3>
                <button onclick="cerrarModalRol()" class="text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>
        
        <form id="formRol" class="p-6 space-y-6">
            <input type="hidden" id="rolId" name="id">
            
            <!-- Información básica -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag text-purple-600 mr-2"></i>
                        Nombre del Rol
                    </label>
                    <input type="text" id="rolNombre" name="nombre" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                        placeholder="Ej: Vendedor, Supervisor">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-users text-purple-600 mr-2"></i>
                        Total de Usuarios
                    </label>
                    <input type="text" id="rolUsuarios" readonly
                        class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-600"
                        placeholder="0 usuarios">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-align-left text-purple-600 mr-2"></i>
                    Descripción
                </label>
                <textarea id="rolDescripcion" name="descripcion" required rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    placeholder="Describe las responsabilidades de este rol"></textarea>
            </div>
            
            <!-- Permisos -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="text-lg font-bold text-gray-800">
                        <i class="fas fa-shield-alt text-purple-600 mr-2"></i>
                        Permisos del Rol
                    </label>
                    <div class="flex gap-2">
                        <button type="button" onclick="seleccionarTodosPermisos()" 
                            class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
                            <i class="fas fa-check-double mr-1"></i>
                            Seleccionar Todos
                        </button>
                        <button type="button" onclick="deseleccionarTodosPermisos()" 
                            class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
                            <i class="fas fa-times mr-1"></i>
                            Limpiar Todo
                        </button>
                    </div>
                </div>
                <div id="permisosContainer" class="permission-grid">
                    <!-- Módulos de permisos se cargan aquí -->
                </div>
            </div>
            
            <!-- Botones -->
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit" 
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-lg hover:shadow-lg transition-all font-semibold">
                    <i class="fas fa-save mr-2"></i>
                    Guardar Rol
                </button>
                <button type="button" onclick="cerrarModalRol()" 
                    class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let roles = [];
    let modulos = {};
    let rolEditando = null;
    
    // Cargar datos iniciales
    async function cargarDatos() {
        try {
            console.log('🔄 Iniciando carga de datos...');
            
            // Cargar roles
            console.log('📡 Llamando a API de roles...');
            const rolesData = await apiRequest('/Dulcería/api/roles.php');
            console.log('📦 Respuesta roles:', rolesData);
            
            if (rolesData.success) {
                roles = rolesData.data;
                console.log('✅ Roles cargados:', roles.length);
                renderizarTablaRoles();
            } else {
                console.error('❌ Error en respuesta de roles:', rolesData.message);
                showNotification(rolesData.message || 'Error al cargar roles', 'error');
            }
            
            // Cargar módulos disponibles
            console.log('📡 Llamando a API de módulos...');
            const modulosData = await apiRequest('/Dulcería/api/roles.php/modulos');
            console.log('📦 Respuesta módulos:', modulosData);
            
            if (modulosData.success) {
                modulos = modulosData.data;
                console.log('✅ Módulos cargados:', Object.keys(modulos).length);
            } else {
                console.error('❌ Error en respuesta de módulos:', modulosData.message);
            }
            
        } catch (error) {
            console.error('❌ Error al cargar datos:', error);
            showNotification('Error al cargar los datos: ' + error.message, 'error');
        }
    }
    
    // Renderizar tabla de roles
    function renderizarTablaRoles() {
        const tbody = document.getElementById('tablaRoles');
        
        console.log('🎨 Renderizando tabla de roles. Total:', roles.length);
        
        if (roles.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-users-slash text-4xl mb-4 block text-gray-300"></i>
                        No hay roles registrados
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = roles.map(rol => {
            console.log('📋 Procesando rol:', rol.nombre, 'Permisos:', rol.permisos);
            
            const totalPermisos = Object.values(rol.permisos || {})
                .reduce((acc, perms) => acc + (Array.isArray(perms) ? perms.length : 0), 0);
            
            const esDueno = rol.nombre === 'dueño' || rol.id == 1;
            
            return `
                <tr class="hover:bg-purple-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                ${rol.nombre.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800">${rol.nombre}</div>
                                ${esDueno ? '<span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">🔒 Protegido</span>' : ''}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-gray-600">${rol.descripcion || ''}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                            <i class="fas fa-users mr-1"></i>
                            ${rol.total_usuarios || 0}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm font-semibold">
                            <i class="fas fa-shield-alt mr-1"></i>
                            ${totalPermisos}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="verUsuariosRol(${rol.id}, '${rol.nombre}')" 
                                class="px-3 py-2 bg-green-100 text-green-600 rounded-lg hover:bg-green-200 transition-colors"
                                title="Ver usuarios">
                                <i class="fas fa-users"></i>
                            </button>
                            <button onclick="verDetalleRol(${rol.id})" 
                                class="px-3 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition-colors"
                                title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${!esDueno ? `
                                <button onclick="editarRol(${rol.id})" 
                                    class="px-3 py-2 bg-yellow-100 text-yellow-600 rounded-lg hover:bg-yellow-200 transition-colors"
                                    title="Editar rol">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="eliminarRol(${rol.id}, '${rol.nombre}')" 
                                    class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors"
                                    title="Eliminar rol">
                                    <i class="fas fa-trash"></i>
                                </button>
                            ` : '<span class="text-xs text-gray-400">No editable</span>'}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
        
        console.log('✅ Tabla renderizada correctamente');
    }
    
    // Abrir modal para nuevo rol
    function abrirModalNuevoRol() {
        rolEditando = null;
        document.getElementById('modalTitulo').textContent = 'Nuevo Rol';
        document.getElementById('formRol').reset();
        document.getElementById('rolId').value = '';
        document.getElementById('rolUsuarios').value = '0 usuarios';
        renderizarPermisosModulos();
        document.getElementById('modalRol').classList.remove('hidden');
        document.getElementById('modalRol').classList.add('flex');
    }
    
    // Ver detalle de rol
    async function verDetalleRol(id) {
        const rol = roles.find(r => r.id == id);
        if (!rol) return;
        
        let permisosHTML = '<div class="space-y-4">';
        for (const [modulo, permisos] of Object.entries(rol.permisos || {})) {
            if (Array.isArray(permisos) && permisos.length > 0) {
                const moduloInfo = modulos[modulo] || { nombre: modulo };
                permisosHTML += `
                    <div class="bg-purple-50 rounded-lg p-4">
                        <h4 class="font-bold text-purple-800 mb-2">📦 ${moduloInfo.nombre}</h4>
                        <div class="flex flex-wrap gap-2">
                            ${permisos.map(p => `
                                <span class="px-3 py-1 bg-white text-purple-700 rounded-full text-sm border border-purple-200">
                                    <i class="fas fa-check text-green-500 mr-1"></i>${p}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        }
        permisosHTML += '</div>';
        
        const html = `
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold text-2xl">
                        ${rol.nombre.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800">${rol.nombre}</h3>
                        <p class="text-gray-600">${rol.descripcion}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-users mr-1"></i>${rol.total_usuarios || 0} usuarios con este rol
                        </p>
                    </div>
                </div>
                <div class="border-t pt-4">
                    <h4 class="font-bold text-lg mb-4 text-gray-800">🔐 Permisos Asignados:</h4>
                    ${permisosHTML}
                </div>
            </div>
        `;
        
        // Crear modal temporal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-6 rounded-t-2xl flex justify-between items-center">
                    <h3 class="text-2xl font-bold">Detalles del Rol</h3>
                    <button onclick="this.closest('.fixed').remove()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                ${html}
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Editar rol
    async function editarRol(id) {
        const rol = roles.find(r => r.id == id);
        if (!rol) return;
        
        rolEditando = rol;
        document.getElementById('modalTitulo').textContent = 'Editar Rol';
        document.getElementById('rolId').value = rol.id;
        document.getElementById('rolNombre').value = rol.nombre;
        document.getElementById('rolDescripcion').value = rol.descripcion;
        document.getElementById('rolUsuarios').value = `${rol.total_usuarios || 0} usuarios`;
        
        // Extraer visibilidad del menú de los permisos
        const visibilidad = rol.visibilidad_menu || {};
        
        renderizarPermisosModulos(rol.permisos || {}, visibilidad);
        
        document.getElementById('modalRol').classList.remove('hidden');
        document.getElementById('modalRol').classList.add('flex');
    }
    
    // Renderizar módulos de permisos
    function renderizarPermisosModulos(permisosActuales = {}, visibilidadActual = {}) {
        const container = document.getElementById('permisosContainer');
        
        container.innerHTML = Object.entries(modulos).map(([moduloKey, moduloInfo]) => {
            // Solo mostrar módulos que tienen menú
            if (!moduloInfo.tiene_menu) return '';
            
            const permisosModulo = permisosActuales[moduloKey] || [];
            const menuVisible = visibilidadActual[moduloKey] !== false; // Por defecto visible si no se especifica
            
            return `
                <div class="module-card">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-lg">${moduloInfo.nombre}</h4>
                        <label class="flex items-center gap-2 bg-white/20 px-3 py-1.5 rounded-lg cursor-pointer hover:bg-white/30 transition-colors">
                            <input type="checkbox" 
                                name="visibilidad_menu[${moduloKey}]" 
                                value="1"
                                ${menuVisible ? 'checked' : ''}
                                class="w-4 h-4 cursor-pointer">
                            <span class="text-xs font-semibold">👁️ Visible en menú</span>
                        </label>
                    </div>
                    <p class="text-sm text-white/80 mb-4">${moduloInfo.descripcion}</p>
                    <div class="space-y-2">
                        ${moduloInfo.permisos.map(permiso => `
                            <label class="permission-checkbox">
                                <input type="checkbox" 
                                    name="permisos[${moduloKey}][]" 
                                    value="${permiso}"
                                    ${permisosModulo.includes(permiso) ? 'checked' : ''}>
                                <span class="text-sm font-medium">${permiso}</span>
                            </label>
                        `).join('')}
                    </div>
                </div>
            `;
        }).join('');
    }
    
    // Seleccionar todos los permisos
    function seleccionarTodosPermisos() {
        document.querySelectorAll('#permisosContainer input[type="checkbox"]').forEach(cb => {
            cb.checked = true;
        });
    }
    
    // Deseleccionar todos los permisos
    function deseleccionarTodosPermisos() {
        document.querySelectorAll('#permisosContainer input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
        });
    }
    
    // Cerrar modal
    function cerrarModalRol() {
        document.getElementById('modalRol').classList.add('hidden');
        document.getElementById('modalRol').classList.remove('flex');
        rolEditando = null;
    }
    
    // Guardar rol
    document.getElementById('formRol').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const rolId = document.getElementById('rolId').value;
        
        // Construir objeto de permisos
        const permisos = {};
        document.querySelectorAll('#permisosContainer input[type="checkbox"][name^="permisos"]:checked').forEach(cb => {
            const [, modulo] = cb.name.match(/permisos\[([^\]]+)\]/);
            if (!permisos[modulo]) {
                permisos[modulo] = [];
            }
            permisos[modulo].push(cb.value);
        });
        
        // Construir objeto de visibilidad de menú
        const visibilidad_menu = {};
        document.querySelectorAll('#permisosContainer input[type="checkbox"][name^="visibilidad_menu"]').forEach(cb => {
            const [, modulo] = cb.name.match(/visibilidad_menu\[([^\]]+)\]/);
            visibilidad_menu[modulo] = cb.checked;
        });
        
        const data = {
            nombre: formData.get('nombre'),
            descripcion: formData.get('descripcion'),
            permisos: permisos,
            visibilidad_menu: visibilidad_menu
        };
        
        try {
            const url = rolId 
                ? `/Dulcería/api/roles.php/${rolId}`
                : '/Dulcería/api/roles.php';
            
            const method = rolId ? 'PUT' : 'POST';
            
            const response = await apiRequest(url, {
                method: method,
                body: JSON.stringify(data)
            });
            
            if (response.success) {
                showNotification(response.message, 'success');
                cerrarModalRol();
                cargarDatos();
            } else {
                showNotification(response.message || 'Error al guardar el rol', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al guardar el rol', 'error');
        }
    });
    
    // Eliminar rol
    async function eliminarRol(id, nombre) {
        if (!confirm(`¿Está seguro de eliminar el rol "${nombre}"?\n\nEsta acción no se puede deshacer.`)) {
            return;
        }
        
        try {
            const response = await apiRequest(`/Dulcería/api/roles.php/${id}`, {
                method: 'DELETE'
            });
            
            if (response.success) {
                showNotification(response.message, 'success');
                cargarDatos();
            } else {
                showNotification(response.message || 'Error al eliminar el rol', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al eliminar el rol', 'error');
        }
    }
    
    // Ver usuarios de un rol
    async function verUsuariosRol(rolId, nombreRol) {
        try {
            // Obtener usuarios del rol
            const responseUsuarios = await apiRequest(`/Dulcería/api/roles.php/${rolId}/usuarios`);
            // Obtener todos los roles para el selector
            const responseRoles = await apiRequest('/Dulcería/api/roles.php');
            
            if (!responseUsuarios.success || !responseRoles.success) {
                showNotification('Error al cargar usuarios', 'error');
                return;
            }
            
            const usuarios = responseUsuarios.data;
            const todosRoles = responseRoles.data;
            
            let usuariosHTML = '';
            if (usuarios.length === 0) {
                usuariosHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-users-slash text-4xl mb-3 block text-gray-300"></i>
                        <p>No hay usuarios asignados a este rol</p>
                        <p class="text-sm mt-2">Ve a la sección de Usuarios para asignar roles</p>
                    </div>
                `;
            } else {
                usuariosHTML = `
                    <div class="space-y-3">
                        ${usuarios.map(usuario => `
                            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-white font-bold">
                                            ${usuario.nombre.charAt(0).toUpperCase()}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-800">${usuario.nombre}</div>
                                            <div class="text-sm text-gray-500">${usuario.email}</div>
                                            ${usuario.ultimo_acceso ? `
                                                <div class="text-xs text-gray-400 mt-1">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Último acceso: ${new Date(usuario.ultimo_acceso).toLocaleDateString('es-MX')}
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold ${usuario.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                            ${usuario.activo ? '✓ Activo' : '✗ Inactivo'}
                                        </span>
                                        <select 
                                            onchange="cambiarRolUsuario(${usuario.id}, this.value, '${usuario.nombre}')"
                                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500">
                                            ${todosRoles.map(r => `
                                                <option value="${r.id}" ${r.id == rolId ? 'selected' : ''}>
                                                    ${r.nombre}
                                                </option>
                                            `).join('')}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
            
            const html = `
                <div class="p-6">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">
                            <i class="fas fa-users text-purple-600 mr-2"></i>
                            Usuarios con rol: ${nombreRol}
                        </h3>
                        <p class="text-gray-600">
                            Total: ${usuarios.length} usuario${usuarios.length !== 1 ? 's' : ''}
                        </p>
                    </div>
                    ${usuariosHTML}
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Consejo:</strong> Puedes cambiar el rol de un usuario directamente desde el selector desplegable.
                            Los cambios se aplican inmediatamente.
                        </p>
                    </div>
                </div>
            `;
            
            // Crear modal
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
            modal.id = 'modalUsuariosRol';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-6 rounded-t-2xl flex justify-between items-center sticky top-0 z-10">
                        <h3 class="text-2xl font-bold">Gestión de Usuarios</h3>
                        <button onclick="document.getElementById('modalUsuariosRol').remove()" class="text-white hover:text-gray-200">
                            <i class="fas fa-times text-2xl"></i>
                        </button>
                    </div>
                    ${html}
                </div>
            `;
            document.body.appendChild(modal);
            
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al cargar usuarios del rol', 'error');
        }
    }
    
    // Cambiar rol de un usuario
    async function cambiarRolUsuario(usuarioId, nuevoRolId, nombreUsuario) {
        if (!confirm(`¿Cambiar el rol de "${nombreUsuario}"?`)) {
            // Recargar la vista para restaurar el selector
            const modal = document.getElementById('modalUsuariosRol');
            if (modal) modal.remove();
            return;
        }
        
        try {
            const response = await apiRequest(`/Dulcería/api/usuarios.php/${usuarioId}`, {
                method: 'PUT',
                body: JSON.stringify({ rol_id: nuevoRolId })
            });
            
            if (response.success) {
                showNotification('Rol actualizado exitosamente', 'success');
                // Cerrar modal y recargar datos
                const modal = document.getElementById('modalUsuariosRol');
                if (modal) modal.remove();
                cargarDatos();
            } else {
                showNotification(response.message || 'Error al cambiar el rol', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Error al cambiar el rol del usuario', 'error');
        }
    }
    
    // Inicializar cuando el DOM y el layout estén listos
    document.addEventListener('DOMContentLoaded', () => {
        // Pequeño delay para asegurar que apiRequest esté disponible
        setTimeout(() => {
            console.log('🚀 DOM cargado, iniciando carga de datos...');
            cargarDatos();
        }, 100);
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/layout.php';
?>
