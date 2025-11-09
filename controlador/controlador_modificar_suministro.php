<?php
if (!empty($_POST["btnmodificar"])) {
    if (!empty($_POST["txtidsuministro"]) && !empty($_POST["txtnombre"]) && !empty($_POST["txtdescripcion"])) {
        $id   = (int)$_POST["txtidsuministro"];
        $nom  = trim($_POST["txtnombre"]);
        $desc = trim($_POST["txtdescripcion"]);

        // Verificar duplicado de nombre (excluyendo el actual)
        $chk = $conexion->prepare("SELECT COUNT(*) AS total FROM suministros WHERE nombre = ? AND id_suministros <> ?");
        $chk->bind_param("si", $nom, $id);
        $chk->execute();
        $res = $chk->get_result();
        $dup = (int)($res->fetch_assoc()['total'] ?? 0);
        $chk->close();

        if ($dup > 0) {
            $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Duplicado', 'mensaje' => 'Ya existe un suministro con ese nombre.'];
            header("Location: suministros.php");
            exit;
        }

        $upd = $conexion->prepare("UPDATE suministros SET nombre = ?, descripcion = ? WHERE id_suministros = ?");
        $upd->bind_param("ssi", $nom, $desc, $id);

        if ($upd->execute()) {
            $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Correcto', 'mensaje' => 'Suministro modificado correctamente.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo modificar el suministro.'];
        }
        $upd->close();

        header("Location: suministros.php");
        exit;
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Campos vacíos', 'mensaje' => 'Complete todos los campos.'];
        header("Location: suministros.php");
        exit;
    }
}
