<?php
if (isset($_POST['btnmodificar'])) {
    // Recuperar los datos del formulario
    $idHerramienta = $_POST['txtidherramienta'];
    $nombreHerramienta = $_POST['txtnombreherramienta'];
    $descripcion = $_POST['txtdescripcion'];

    // Comprobar si ya existe una herramienta con el mismo nombre (excluyendo la herramienta que estamos modificando)
    $sqlCheck = "SELECT COUNT(*) FROM herramientas WHERE nombre = ? AND id_herramientas != ?";
    $stmtCheck = $conexion->prepare($sqlCheck);
    $stmtCheck->bind_param("si", $nombreHerramienta, $idHerramienta);
    $stmtCheck->execute();
    $stmtCheck->bind_result($count);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($count > 0) {
        // Si ya existe una herramienta con ese nombre, mostrar un mensaje de error
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'El nombre de la herramienta ya existe.'];
    } else {
        // Si no existe, proceder con la actualización
        $sqlUpdate = "UPDATE herramientas SET nombre = ?, descripcion = ? WHERE id_herramientas = ?";
        $stmtUpdate = $conexion->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssi", $nombreHerramienta, $descripcion, $idHerramienta);
        if ($stmtUpdate->execute()) {
            $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Herramienta modificada con éxito'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'Hubo un problema al modificar la herramienta'];
        }
        $stmtUpdate->close();
    }
}
