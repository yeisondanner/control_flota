<?php
if (isset($_GET['del'])) {
    $idM = (int)$_GET['del'];
    if ($idM > 0) {
        // Borrar relaciones (FK) primero
        if ($delRelH = $conexion->prepare("DELETE FROM mantenimientos_herramientas WHERE id_mantenimiento = ?")) {
            $delRelH->bind_param("i", $idM);
            $delRelH->execute();
            $delRelH->close();
        }
        if ($delRelS = $conexion->prepare("DELETE FROM mantenimientos_suministros WHERE id_mantenimiento = ?")) {
            $delRelS->bind_param("i", $idM);
            $delRelS->execute();
            $delRelS->close();
        }

        // Borrar mantenimiento
        if ($del = $conexion->prepare("DELETE FROM mantenimientos WHERE id_mantenimiento = ?")) {
            $del->bind_param("i", $idM);
            $ok = $del->execute();
            $del->close();

            if ($ok) {
                $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Eliminado', 'mensaje' => 'Mantenimiento eliminado.'];
            } else {
                $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo eliminar el mantenimiento.'];
            }
        } else {
            $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo preparar la eliminación.'];
        }
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Aviso', 'mensaje' => 'Identificador inválido.'];
    }
    header("Location: mantenimiento.php");
    exit;
}
