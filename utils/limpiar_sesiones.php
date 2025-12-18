<?php
/**
 * Script para limpiar sesiones expiradas o cerrar todas las sesiones
 */

require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance()->getConnection();

// Opción 1: Desactivar todas las sesiones (forzar re-login)
$stmt = $db->prepare("UPDATE sesiones SET activo = 0 WHERE activo = 1");
$stmt->execute();
echo "✅ Todas las sesiones han sido cerradas. Los usuarios deberán iniciar sesión nuevamente.\n";

// Opción 2: Limpiar session_token de usuarios
$stmt = $db->prepare("UPDATE usuarios SET session_token = NULL");
$stmt->execute();
echo "✅ Tokens de sesión limpiados de todos los usuarios.\n";

// Opción 3: Eliminar sesiones expiradas (más de 30 días)
$stmt = $db->prepare("DELETE FROM sesiones WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute();
$deleted = $stmt->rowCount();
echo "✅ Se eliminaron {$deleted} sesiones antiguas (más de 30 días).\n";

echo "\n✅ Limpieza completada. Ahora puedes iniciar sesión nuevamente.\n";
