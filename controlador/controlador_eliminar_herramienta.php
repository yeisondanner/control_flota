<?php
if (isset($_GET['id'])) {
    $idHerramienta = $_GET['id'];

    // Hacer la consulta para eliminar la herramienta
    $sql = "DELETE FROM herramientas WHERE id_herramientas = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $idHerramienta);
    if ($stmt->execute()) {
        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Herramienta eliminada con éxito'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'Hubo un problema al eliminar la herramienta'];
    }
    $stmt->close();
    header('Location: herramientas.php');
    exit;
}
