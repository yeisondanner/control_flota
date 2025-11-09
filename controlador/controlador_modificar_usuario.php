<?php
// Requiere $conexion y sesión iniciada.

if (!empty($_POST["btnmodificar"])) {

    // Validación mínima
    $ok = !empty($_POST["txtnombre"]) &&
        !empty($_POST["txtapellidos"]) &&
        !empty($_POST["txtdni"]) &&
        !empty($_POST["txtusuario"]) &&
        !empty($_POST["txtrol"]) &&
        !empty($_POST["txtidusuario"]) &&
        !empty($_POST["txtidpersona"]);

    if (!$ok) {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'Complete todos los campos obligatorios.'];
        header("Location: usuario.php");
        exit;
    }

    $nombre     = trim($_POST["txtnombre"]);
    $apellidos  = trim($_POST["txtapellidos"]);
    $dni        = trim($_POST["txtdni"]);
    $usuario    = trim($_POST["txtusuario"]);
    $rol        = trim($_POST["txtrol"]);
    $id_usuario = (int)$_POST["txtidusuario"];
    $id_persona = (int)$_POST["txtidpersona"];
    $password   = isset($_POST["txtpassword"]) && $_POST["txtpassword"] !== '' ? (string)$_POST["txtpassword"] : null;

    try {
        // Username duplicado en otro usuario
        $dup = 0;
        if ($stmt = $conexion->prepare("SELECT COUNT(*) FROM usuario WHERE username=? AND id_usuario<>?")) {
            $stmt->bind_param("si", $usuario, $id_usuario);
            $stmt->execute();
            $stmt->bind_result($dup);
            $stmt->fetch();
            $stmt->close();
        } else {
            throw new Exception("No se pudo preparar verificación de duplicado.");
        }
        if ($dup > 0) {
            $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Duplicado', 'mensaje' => "El usuario «{$usuario}» ya existe."];
            header("Location: usuario.php");
            exit;
        }

        $conexion->begin_transaction();

        // PERSONA
        if (!($stmt = $conexion->prepare("UPDATE persona SET nombres=?, apellidos=?, dni=? WHERE id_persona=?"))) {
            throw new Exception("Error preparando UPDATE persona: " . $conexion->error);
        }
        $stmt->bind_param("sssi", $nombre, $apellidos, $dni, $id_persona);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando UPDATE persona: " . $stmt->error);
        }
        $stmt->close();

        // USUARIO
        if ($password !== null) {
            if (!($stmt = $conexion->prepare("UPDATE usuario SET username=?, password=?, rol=? WHERE id_usuario=?"))) {
                throw new Exception("Error preparando UPDATE usuario: " . $conexion->error);
            }
            $stmt->bind_param("sssi", $usuario, $password, $rol, $id_usuario);
        } else {
            if (!($stmt = $conexion->prepare("UPDATE usuario SET username=?, rol=? WHERE id_usuario=?"))) {
                throw new Exception("Error preparando UPDATE usuario: " . $conexion->error);
            }
            $stmt->bind_param("ssi", $usuario, $rol, $id_usuario);
        }
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando UPDATE usuario: " . $stmt->error);
        }
        $stmt->close();

        $conexion->commit();

        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Modificado', 'mensaje' => 'El usuario se modificó correctamente.'];
        header("Location: usuario.php");
        exit;
    } catch (Exception $e) {
        if ($conexion->errno) {
            $conexion->rollback();
        }
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo modificar el usuario. Detalle: ' . $e->getMessage()];
        header("Location: usuario.php");
        exit;
    }
}
