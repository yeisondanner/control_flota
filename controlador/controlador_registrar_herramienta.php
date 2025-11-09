<?php
if (isset($_POST['btnregistrar'])) {
    // Recuperar los datos del formulario
    $nombreHerramienta = $_POST['txtnombre'];
    $descripcion = $_POST['txtdescripcion'];

    // Verificar si ya existe una herramienta con el mismo nombre
    $sqlCheck = "SELECT COUNT(*) FROM herramientas WHERE nombre = ?";
    $stmtCheck = $conexion->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $nombreHerramienta);
    $stmtCheck->execute();
    $stmtCheck->bind_result($count);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($count > 0) {
        // Si ya existe, mostrar mensaje de error
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'El nombre de la herramienta ya está registrado.'];
    } else {
        // Si no existe, insertar la nueva herramienta en la base de datos
        $sqlInsert = "INSERT INTO herramientas (nombre, descripcion) VALUES (?, ?)";
        $stmtInsert = $conexion->prepare($sqlInsert);
        $stmtInsert->bind_param("ss", $nombreHerramienta, $descripcion);
        if ($stmtInsert->execute()) {
            // Si la inserción es exitosa, mostrar mensaje de éxito
            $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Herramienta registrada con éxito.'];
        } else {
            // Si hubo un error al insertar
            $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'Hubo un problema al registrar la herramienta.'];
        }
        $stmtInsert->close();
    }
}
