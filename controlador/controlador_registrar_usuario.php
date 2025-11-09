<?php
// Debe incluirse DESPUÉS de include "../modelo/conexion.php"
// y ANTES de cualquier salida HTML.

if (!empty($_POST["btnregistrar"])) {

    // Validación básica de presencia
    $campos_ok =
        !empty($_POST["txtnombre"])   &&
        !empty($_POST["txtapellidos"]) &&
        !empty($_POST["txtdni"])       &&
        !empty($_POST["txtusuario"])   &&
        !empty($_POST["txtpassword"])  &&
        !empty($_POST["txtrol"]);

    if (!$campos_ok) {
        $_SESSION['flash'] = [
            'tipo' => 'error',
            'titulo' => 'Campos faltantes',
            'mensaje' => 'Por favor complete todos los campos obligatorios.'
        ];
        header("Location: registrar_usuario.php");
        exit;
    }

    // Sanitizar
    $nombre    = trim($_POST["txtnombre"]);
    $apellidos = trim($_POST["txtapellidos"]);
    $dni       = trim($_POST["txtdni"]);
    $usuario   = trim($_POST["txtusuario"]);
    $password  = (string)$_POST["txtpassword"]; // ⚠️ Mantengo tal cual para no romper tu login actual
    $rol       = trim($_POST["txtrol"]);

    // Verificar duplicados
    try {
        // username duplicado
        $existeUser = 0;
        if ($stmt = $conexion->prepare("SELECT COUNT(*) FROM usuario WHERE username = ?")) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $stmt->bind_result($existeUser);
            $stmt->fetch();
            $stmt->close();
        } else {
            throw new Exception("No se pudo preparar verificación de usuario.");
        }

        // dni duplicado
        $existeDni = 0;
        if ($stmt = $conexion->prepare("SELECT COUNT(*) FROM persona WHERE dni = ?")) {
            $stmt->bind_param("s", $dni);
            $stmt->execute();
            $stmt->bind_result($existeDni);
            $stmt->fetch();
            $stmt->close();
        } else {
            throw new Exception("No se pudo preparar verificación de DNI.");
        }

        if ($existeUser > 0 || $existeDni > 0) {
            $msgs = [];
            if ($existeUser > 0) {
                $msgs[] = "El usuario «{$usuario}» ya existe";
            }
            if ($existeDni  > 0) {
                $msgs[] = "El DNI «{$dni}» ya está registrado";
            }
            $_SESSION['flash'] = [
                'tipo' => 'warning',
                'titulo' => 'Duplicados',
                'mensaje' => implode(" y ", $msgs) . "."
            ];
            header("Location: registro_usuario.php");
            exit;
        }

        // Insertar (transacción)
        $conexion->begin_transaction();

        // persona
        if (!($stmt = $conexion->prepare(
            "INSERT INTO persona (nombres, apellidos, dni) VALUES (?, ?, ?)"
        ))) {
            throw new Exception("Error preparando INSERT persona: " . $conexion->error);
        }
        $stmt->bind_param("sss", $nombre, $apellidos, $dni);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando INSERT persona: " . $stmt->error);
        }
        $id_persona = $conexion->insert_id;
        $stmt->close();

        // usuario
        if (!($stmt = $conexion->prepare(
            "INSERT INTO usuario (id_persona, username, password, rol) VALUES (?, ?, ?, ?)"
        ))) {
            throw new Exception("Error preparando INSERT usuario: " . $conexion->error);
        }
        $stmt->bind_param("isss", $id_persona, $usuario, $password, $rol);
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando INSERT usuario: " . $stmt->error);
        }
        $stmt->close();

        $conexion->commit();

        $_SESSION['flash'] = [
            'tipo' => 'success',
            'titulo' => 'Éxito',
            'mensaje' => 'El usuario se registró correctamente.'
        ];
        header("Location: registro_usuario.php");
        exit;
    } catch (Exception $e) {
        if ($conexion->errno) {
            $conexion->rollback();
        }
        $_SESSION['flash'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'mensaje' => 'No se pudo registrar el usuario. Detalle: ' . $e->getMessage()
        ];
        header("Location: registro_usuario.php");
        exit;
    }
}
