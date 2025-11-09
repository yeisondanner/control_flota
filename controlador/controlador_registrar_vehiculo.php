<?php
if (!empty($_POST['btnregistrar']) && $_POST['btnregistrar'] === 'ok') {
    // Conexión ya incluida en registrar_vehiculo.php
    try {
        // Recibir los datos del formulario
        $matricula      = trim($_POST['txtmatricula'] ?? '');
        $marca          = trim($_POST['txtmarca'] ?? '');
        $modelo         = trim($_POST['txtmodelo'] ?? '');
        $tipo           = trim($_POST['txttipo'] ?? '');
        $año            = (int)($_POST['txtaño'] ?? 0);
        $kilometraje    = (int)($_POST['txtkilometraje'] ?? 0);
        $idUnidadMinera = (int)($_POST['txtunidadminera'] ?? 0);

        // Validaciones
        if ($matricula === '' || $marca === '' || $modelo === '' || $tipo === '' || $año <= 0 || $kilometraje <= 0 || $idUnidadMinera === 0) {
            $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Atención', 'mensaje' => 'Complete correctamente todos los campos.'];
            header('Location: registrar_vehiculo.php');
            exit;
        }

        // Insertar vehículo en la base de datos
        $stmt = $conexion->prepare("INSERT INTO vehiculos (matricula, marca, modelo, tipo, year, kilometraje, id_unidadminera) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdis", $matricula, $marca, $modelo, $tipo, $año, $kilometraje, $idUnidadMinera);
        if (!$stmt->execute()) {
            throw new Exception("Error al registrar el vehículo.");
        }
        $stmt->close();

        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Vehículo registrado correctamente.'];
        header('Location: vehiculos.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo registrar el vehículo: ' . $e->getMessage()];
        header('Location: registrar_vehiculo.php');
        exit;
    }
}
