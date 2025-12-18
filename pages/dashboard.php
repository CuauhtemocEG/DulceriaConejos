<?php
$pageTitle = 'Dashboard';
ob_start();
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6">
    <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <h3 class="text-sm font-medium opacity-90 mb-2">Ventas Hoy</h3>
        <div class="text-3xl font-bold" id="ventasHoy">$0.00</div>
        <p class="text-xs opacity-75 mt-2" id="numVentasHoy">0 transacciones</p>
    </div>
    
    <div class="bg-gradient-to-br from-pink-500 to-rose-700 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <h3 class="text-sm font-medium opacity-90 mb-2">Productos</h3>
        <div class="text-3xl font-bold" id="totalProductos">0</div>
        <p class="text-xs opacity-75 mt-2" id="productosActivos">0 activos</p>
    </div>
    
    <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <h3 class="text-sm font-medium opacity-90 mb-2">Stock Bajo</h3>
        <div class="text-3xl font-bold" id="stockBajo">0</div>
        <p class="text-xs opacity-75 mt-2">Productos con stock mínimo</p>
    </div>
    
    <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform">
        <h3 class="text-sm font-medium opacity-90 mb-2">Ventas del Mes</h3>
        <div class="text-3xl font-bold" id="ventasMes">$0.00</div>
        <p class="text-xs opacity-75 mt-2" id="numVentasMes">0 transacciones</p>
    </div>
</div>

<!-- Charts Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">📈 Productos Más Vendidos</h3>
        <div id="productosTop" class="overflow-x-auto">
            <div class="flex items-center justify-center h-40">
                <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-10 w-10 object-contain">
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">⚠️ Stock Bajo</h3>
        <div id="listaStockBajo" class="overflow-x-auto">
            <div class="flex items-center justify-center h-40">
                <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-10 w-10 object-contain">
            </div>
        </div>
    </div>
</div>

<!-- Recent Sales -->
<div class="bg-white rounded-xl shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">🧾 Últimas Ventas</h3>
    <div id="ultimasVentas" class="overflow-x-auto">
        <div class="flex items-center justify-center h-40">
            <img src="/DulceriaConejos/public/img/DulceriaConejos.png" alt="Cargando" class="logo-spinner h-10 w-10 object-contain">
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>

<script>
    // Cargar datos del dashboard
    async function cargarDashboard() {
        try {
            // Ventas de hoy
            const hoy = new Date().toISOString().split('T')[0];
            const ventasHoyData = await apiRequest(`/DulceriaConejos/api/reportes.php/ventas?periodo=diario&fecha=${hoy}`);
            
            if (ventasHoyData.success && ventasHoyData.data.resumen) {
                const resumen = ventasHoyData.data.resumen;
                document.getElementById('ventasHoy').textContent = '$' + (resumen.total_ventas || 0).toLocaleString('es-MX', {minimumFractionDigits: 2});
                document.getElementById('numVentasHoy').textContent = (resumen.num_ventas || 0) + ' transacciones';
            }
            
            // Ventas del mes
            const ventasMesData = await apiRequest(`/DulceriaConejos/api/reportes.php/ventas?periodo=mensual&fecha=${hoy}`);
            
            if (ventasMesData.success && ventasMesData.data.resumen) {
                const resumen = ventasMesData.data.resumen;
                document.getElementById('ventasMes').textContent = '$' + (resumen.total_ventas || 0).toLocaleString('es-MX', {minimumFractionDigits: 2});
                document.getElementById('numVentasMes').textContent = (resumen.num_ventas || 0) + ' transacciones';
            }
            
            // Productos
            const productosData = await apiRequest('/DulceriaConejos/api/productos.php');
            if (productosData.success) {
                document.getElementById('totalProductos').textContent = productosData.data.length;
                const activos = productosData.data.filter(p => p.activo == 1).length;
                document.getElementById('productosActivos').textContent = activos + ' activos';
            }
            
            // Stock bajo
            const stockBajoData = await apiRequest('/DulceriaConejos/api/reportes.php/stock-bajo');
            if (stockBajoData.success) {
                document.getElementById('stockBajo').textContent = stockBajoData.data.length;
                mostrarStockBajo(stockBajoData.data);
            }
            
            // Productos más vendidos
            const productosTopData = await apiRequest('/DulceriaConejos/api/reportes.php/productos-top?limite=5');
            if (productosTopData.success) {
                mostrarProductosTop(productosTopData.data);
            }
            
            // Últimas ventas
            const ventasData = await apiRequest('/DulceriaConejos/api/pos.php/ventas?limite=10');
            if (ventasData.success) {
                mostrarUltimasVentas(ventasData.data);
            }
            
        } catch (error) {
            console.error('Error al cargar dashboard:', error);
        }
    }
    
    function mostrarProductosTop(productos) {
        const container = document.getElementById('productosTop');
        
        if (productos.length === 0) {
            container.innerHTML = '<p class="text-center text-gray-400 py-10">No hay datos disponibles</p>';
            return;
        }
        
        let html = '<table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>';
        html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
        
        productos.forEach(p => {
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-gray-900">${p.nombre}</div>
                        <div class="text-xs text-gray-500">${p.categoria}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">${p.num_ventas}</td>
                    <td class="px-4 py-3 text-sm font-medium text-green-600">$${parseFloat(p.total_vendido).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }
    
    function mostrarStockBajo(productos) {
        const container = document.getElementById('listaStockBajo');
        
        if (productos.length === 0) {
            container.innerHTML = '<p class="text-center text-gray-400 py-10">No hay productos con stock bajo</p>';
            return;
        }
        
        let html = '<table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faltante</th>';
        html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
        
        productos.forEach(p => {
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${p.nombre}</td>
                    <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">${p.stock_actual} ${p.unidad_medida}</span></td>
                    <td class="px-4 py-3"><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">${p.cantidad_faltante}</span></td>
                </tr>
            `;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }
    
    function mostrarUltimasVentas(ventas) {
        const container = document.getElementById('ultimasVentas');
        
        if (ventas.length === 0) {
            container.innerHTML = '<p class="text-center text-gray-400 py-10">No hay ventas registradas</p>';
            return;
        }
        
        let html = '<table class="min-w-full divide-y divide-gray-200"><thead class="bg-gray-50"><tr>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folio</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Fecha</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Vendedor</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>';
        html += '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>';
        html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
        
        ventas.forEach(v => {
            const fecha = new Date(v.created_at).toLocaleString('es-MX', {dateStyle: 'short', timeStyle: 'short'});
            const estado = v.estado === 'completada' ? 
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completada</span>' : 
                '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Cancelada</span>';
            
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">${v.folio}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 hidden sm:table-cell">${fecha}</td>
                    <td class="px-4 py-3 text-sm text-gray-500 hidden md:table-cell">${v.vendedor_nombre}</td>
                    <td class="px-4 py-3 text-sm font-medium text-green-600">$${parseFloat(v.total).toLocaleString('es-MX', {minimumFractionDigits: 2})}</td>
                    <td class="px-4 py-3">${estado}</td>
                </tr>
            `;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }
    
    // Cargar dashboard al iniciar
    cargarDashboard();
    
    // Recargar cada 30 segundos
    setInterval(cargarDashboard, 30000);
</script>
