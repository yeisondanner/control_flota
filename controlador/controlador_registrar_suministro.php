<?php
if (!empty($_POST["btnregistrar"])) {
    if (!empty($_POST["txtnombre"]) && !empty($_POST["txtdescripcion"])) {
        $nombre = trim($_POST["txtnombre"]);
        $descripcion = trim($_POST["txtdescripcion"]);

        include "../modelo/conexion.php";

        // Verificar duplicado
        $check = $conexion->prepare("SELECT COUNT(*) AS total FROM suministros WHERE nombre = ?");
        $check->bind_param("s", $nombre);
        $check->execute();
        $res = $check->get_result();
        $row = $res->fetch_assoc();
        $check->close();

        if ($row['total'] > 0) {
            $_SESSION['flash'] = [
                'tipo' => 'error',
                'titulo' => 'Error',
                'mensaje' => 'El suministro ya está registrado.'
            ];
        } else {
            $stmt = $conexion->prepare("INSERT INTO suministros (nombre, descripcion) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombre, $descripcion);

            if ($stmt->execute()) {
                $_SESSION['flash'] = [
                    'tipo' => 'success',
                    'titulo' => 'Correcto',
                    'mensaje' => 'El suministro se registró correctamente.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'tipo' => 'error',
                    'titulo' => 'Error',
                    'mensaje' => 'Ocurrió un problema al registrar el suministro.'
                ];
            }
            $stmt->close();
        }

        header("Location: registrar_suministros.php");
        exit;
    } else {
        $_SESSION['flash'] = [
            'tipo' => 'error',
            'titulo' => 'Campos vacíos',
            'mensaje' => 'Debe completar todos los campos requeridos.'
        ];
        header("Location: registrar_suministros.php");
        exit;
    }
}
