<?php
if (!empty($_POST['btnmodificar']) && $_POST['btnmodificar'] === 'ok') {
    // Conexión ya incluida en conductores.php
    try {
        $idUnidadMinera = (int)($_POST['txtidunidad'] ?? 0);
        $unidad         = trim($_POST['txtnombreunidad'] ?? '');
        $descripcion    = trim($_POST['txtdescripcion'] ?? '');

        // Validaciones
        if ($unidad === '' || $descripcion === '') {
            $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Atención', 'mensaje' => 'Complete correctamente los campos.'];
            header('Location: unidad_minera.php');
            exit;
        }

        // Actualizar unidad minera
        $stmt = $conexion->prepare("UPDATE unidad_minera SET nombre_unidad = ?, descripcion = ? WHERE id_unidadminera = ?");
        $stmt->bind_param("ssi", $unidad, $descripcion, $idUnidadMinera);
        if (!$stmt->execute()) {
            throw new Exception("Error al modificar la unidad minera.");
        }
        $stmt->close();

        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Unidad minera modificada correctamente.'];
        header('Location: unidad_minera.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo modificar la unidad minera: ' . $e->getMessage()];
        header('Location: unidad_minera.php');
        exit;
    }
}
