<?php
session_start();
include "../modelo/conexion.php";  // Asegúrate de que esta ruta es correcta

if (isset($_GET['id'])) {
    $id_asignacion = $_GET['id'];

    // Verificar si el ID existe en la base de datos antes de intentar eliminar
    $sql_check = "SELECT * FROM conductor_vehiculo WHERE id_conductorvehiculo = '$id_asignacion'";
    $result_check = $conexion->query($sql_check);

    if ($result_check->num_rows > 0) {
        // Si el ID existe, proceder con la eliminación
        $sql = "DELETE FROM conductor_vehiculo WHERE id_conductorvehiculo = '$id_asignacion'";
        if ($conexion->query($sql) === TRUE) {
            $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Asignación eliminada correctamente'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'Error al eliminar la asignación'];
        }
    } else {
        $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'La asignación no existe'];
    }

    // Redirigir de vuelta a la página de asignaciones
    header('Location: ../vista/asignar_vehiculos.php');
    exit;
} else {
    $_SESSION['flash'] = ['tipo' => 'error', 'mensaje' => 'No se ha proporcionado un ID válido'];
    header('Location: ../vista/asignar_vehiculos.php');
    exit;
}
