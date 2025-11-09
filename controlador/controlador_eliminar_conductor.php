<?php
if (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $ok = false;

    $stmt = $conexion->prepare("DELETE FROM conductor WHERE id_conductor = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        $stmt->close();
    }

    if ($ok) {
        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Eliminado', 'mensaje' => 'Conductor eliminado correctamente.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo eliminar el registro.'];
    }
    header('Location: conductores.php');
    exit;
}
