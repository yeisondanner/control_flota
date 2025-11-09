<?php
if (!empty($_POST['btnregistrar']) && $_POST['btnregistrar'] === 'ok') {
    // Sanitización
    $unidad      = trim($_POST['txtunidad'] ?? '');
    $descripcion = trim($_POST['txtdescripcion'] ?? '');

    // Validaciones
    if ($unidad === '' || $descripcion === '') {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Atención', 'mensaje' => 'Complete correctamente los campos.'];
        header('Location: registrar_unidadminera.php');
        exit;
    }

    // Insertar unidad minera
    $stmt = $conexion->prepare("INSERT INTO unidad_minera (nombre_unidad, descripcion) VALUES (?, ?)");
    $stmt->bind_param("ss", $unidad, $descripcion);
    if ($stmt->execute()) {
        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Unidad minera registrada correctamente.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo registrar la unidad minera.'];
    }
    header('Location: registrar_unidadminera.php');
    exit;
}
