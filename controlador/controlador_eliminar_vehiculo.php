<?php
if (isset($_GET['id'])) {
    $idVehiculo = (int)$_GET['id'];

    try {
        // Eliminar vehículo
        $stmt = $conexion->prepare("DELETE FROM vehiculos WHERE id_vehiculo = ?");
        $stmt->bind_param("i", $idVehiculo);
        if (!$stmt->execute()) {
            throw new Exception("Error al eliminar el vehículo.");
        }
        $stmt->close();

        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Vehículo eliminado correctamente.'];
        header('Location: vehiculos.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo eliminar el vehículo: ' . $e->getMessage()];
        header('Location: vehiculos.php');
        exit;
    }
}
