<?php
/**
 * Valida si el usuario es conductor por rol y redirige a kilometrajes.php si intenta acceder a otros módulos
 * Este archivo debe incluirse después de session_start() en cada página
 * (excepto kilometrajes.php y registrar_kilometraje.php que son permitidas para conductores)
 */

// Verificar si el usuario tiene rol "Conductor"
$es_conductor = isset($_SESSION['rol']) && $_SESSION['rol'] === 'Conductor';

// Si el usuario es conductor, solo puede acceder a kilometrajes.php y registrar_kilometraje.php
if ($es_conductor) {
    // Obtener el nombre del archivo actual
    $archivo_actual = basename($_SERVER['PHP_SELF']);
    
    // Lista de archivos permitidos para conductores
    $archivos_permitidos = [
        'kilometrajes.php',
        'registrar_kilometraje.php'
    ];
    
    // Si el archivo actual NO está en la lista de permitidos, redirigir
    if (!in_array($archivo_actual, $archivos_permitidos)) {
        // Obtener el directorio base del script actual
        $script_dir = dirname($_SERVER['PHP_SELF']);
        // Si estamos en vista/, la ruta es directa, si no, necesitamos ajustar
        if (strpos($script_dir, 'vista') !== false) {
            header('location: kilometrajes.php');
        } else {
            header('location: vista/kilometrajes.php');
        }
        exit;
    }
}
?>

