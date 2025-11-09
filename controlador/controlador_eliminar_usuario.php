<?php
// Requiere $conexion y sesión iniciada.

if (!empty($_GET["id"])) {
    $id_usuario = (int)$_GET["id"];

    try {
        // Obtener persona
        $id_persona = null;
        if ($stmt = $conexion->prepare("SELECT id_persona FROM usuario WHERE id_usuario=?")) {
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->bind_result($id_persona);
            $stmt->fetch();
            $stmt->close();
        }
        if (!$id_persona) {
            $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Aviso', 'mensaje' => 'Usuario no encontrado.'];
            header("Location: usuario.php");
            exit;
        }

        $conexion->begin_transaction();

        // Eliminar usuario
        if (!($stmt = $conexion->prepare("DELETE FROM usuario WHERE id_usuario=?"))) {
            throw new Exception("Error preparando DELETE usuario: " . $conexion->error);
        }
        $stmt->bind_param("i", $id_usuario);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando DELETE usuario: " . $stmt->error);
        }
        $stmt->close();

        // Eliminar persona
        if (!($stmt = $conexion->prepare("DELETE FROM persona WHERE id_persona=?"))) {
            throw new Exception("Error preparando DELETE persona: " . $conexion->error);
        }
        $stmt->bind_param("i", $id_persona);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando DELETE persona: " . $stmt->error);
        }
        $stmt->close();

        $conexion->commit();

        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Eliminado', 'mensaje' => 'Usuario eliminado correctamente.'];
        header("Location: usuario.php");
        exit;
    } catch (Exception $e) {
        if ($conexion->errno) {
            $conexion->rollback();
        }
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo eliminar: ' . $e->getMessage()];
        header("Location: usuario.php");
        exit;
    }
}
