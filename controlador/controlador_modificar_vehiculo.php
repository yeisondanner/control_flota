<?php
if (!empty($_POST['btnmodificar']) && $_POST['btnmodificar'] === 'ok') {
    // Conexión ya incluida en vehiculos.php
    try {
        // Obtener los valores del formulario
        $idVehiculo    = (int)($_POST['txtidvehiculo'] ?? 0);
        $matricula     = trim($_POST['txtmatricula'] ?? '');
        $marca         = trim($_POST['txtmarca'] ?? '');
        $modelo        = trim($_POST['txtmodelo'] ?? '');
        $tipo          = trim($_POST['txttipo'] ?? '');
        $año           = (int)($_POST['txtaño'] ?? 0);
        $kilometraje   = (float)($_POST['txtkilometraje'] ?? 0);
        $unidadMinera  = (int)($_POST['txtunidadminera'] ?? 0);

        // Validaciones
        if ($matricula === '' || $marca === '' || $modelo === '' || $tipo === '' || $año <= 0 || $kilometraje <= 0 || $unidadMinera <= 0) {
            $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Atención', 'mensaje' => 'Complete correctamente los campos.'];
            header('Location: vehiculos.php');
            exit;
        }

        // Actualizar vehículo
        $stmt = $conexion->prepare("UPDATE vehiculos SET 
            matricula = ?, 
            marca = ?, 
            modelo = ?, 
            tipo = ?, 
            year = ?, 
            kilometraje = ?, 
            id_unidadminera = ? 
            WHERE id_vehiculo = ?");
        $stmt->bind_param("sssssdis", $matricula, $marca, $modelo, $tipo, $año, $kilometraje, $unidadMinera, $idVehiculo);

        // Ejecutar la consulta
        if (!$stmt->execute()) {
            throw new Exception("Error al modificar el vehículo.");
        }
        $stmt->close();

        // Mensaje de éxito
        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Vehículo modificado correctamente.'];
        header('Location: vehiculos.php');
        exit;
    } catch (Exception $e) {
        // En caso de error
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo modificar el vehículo: ' . $e->getMessage()];
        header('Location: vehiculos.php');
        exit;
    }
}
