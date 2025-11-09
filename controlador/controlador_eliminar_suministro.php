<?php
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    if ($id > 0) {
        // Si hay relaciones, podrías borrar primero sus dependencias o tener FK ON DELETE CASCADE
        $del = $conexion->prepare("DELETE FROM suministros WHERE id_suministros = ?");
        $del->bind_param("i", $id);
        $ok = $del->execute();
        $del->close();

        if ($ok) {
            $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Eliminado', 'mensaje' => 'Suministro eliminado correctamente.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo eliminar el suministro (revise dependencias).'];
        }
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Aviso', 'mensaje' => 'Identificador inválido.'];
    }
    header("Location: suministros.php");
    exit;
}
