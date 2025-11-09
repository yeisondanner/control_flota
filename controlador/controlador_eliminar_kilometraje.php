<?php

// Verificar si el usuario es conductor - los conductores no pueden eliminar
$es_conductor = isset($_SESSION['rol']) && $_SESSION['rol'] === 'Conductor';

if ($es_conductor) {
    // Los conductores no pueden eliminar registros
    $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Acceso denegado', 'mensaje' => 'No tiene permisos para eliminar registros.'];
    header("Location: ../vista/kilometrajes.php");
    exit;
}

if (isset($_GET['del'])) {
    $idKm = (int)$_GET['del'];

    if ($idKm > 0) {
        $stmt = $conexion->prepare("DELETE FROM kilometraje_semanal WHERE id_kilometrajesemanal = ?");
        if ($stmt) {
            $stmt->bind_param("i", $idKm);
            $ok = $stmt->execute();
            $stmt->close();

            $_SESSION['flash'] = $ok
                ? ['tipo' => 'success', 'titulo' => 'Eliminado', 'mensaje' => 'Registro de kilometraje eliminado.']
                : ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo eliminar el registro.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo preparar la eliminación.'];
        }
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Aviso', 'mensaje' => 'Identificador inválido.'];
    }

    /* PRG */
    header("Location: ../vista/kilometrajes.php");
    exit;
}
