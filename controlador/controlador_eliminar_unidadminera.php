<?php
if (!empty($_GET['id'])) {
    $idUnidadMinera = (int)$_GET['id'];

    try {
        $stmt = $conexion->prepare("DELETE FROM unidad_minera WHERE id_unidadminera = ?");
        $stmt->bind_param("i", $idUnidadMinera);
        if (!$stmt->execute()) {
            throw new Exception("No se pudo eliminar la unidad minera.");
        }
        $stmt->close();

        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Eliminado', 'mensaje' => 'Unidad minera eliminada correctamente.'];
    } catch (Exception $e) {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo eliminar la unidad minera: ' . $e->getMessage()];
    }

    // PRG: redirige después de la acción
    header('Location: unidad_minera.php');
    exit;
}
