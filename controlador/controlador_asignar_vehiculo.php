<?php
// Verificar si se ha enviado el formulario para asignar vehículo
if (isset($_POST['asignar_vehiculo'])) {
    $id_conductor = $_POST['id_conductor'];
    $id_vehiculo = $_POST['id_vehiculo'];

    // Comprobar si ya existe la asignación del vehículo al conductor
    $check_sql = "SELECT COUNT(*) AS count FROM conductor_vehiculo WHERE id_conductor = '$id_conductor' AND id_vehiculo = '$id_vehiculo'";
    $result = $conexion->query($check_sql);
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Si ya existe, mostrar mensaje de error
        $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'Este vehículo ya ha sido asignado a este conductor.'];
    } else {
        // Si no existe, proceder con la asignación
        $sql = "INSERT INTO conductor_vehiculo (id_conductor, id_vehiculo) VALUES ('$id_conductor', '$id_vehiculo')";
        if ($conexion->query($sql) === TRUE) {
            $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Vehículo asignado correctamente'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'Error al asignar el vehículo'];
        }
    }

    // Redirigir a la misma página después de la operación
    header('Location: asignar_vehiculos.php');
    exit;
}
