<?php
$conexion = new mysqli("localhost", "root", "", "control_flota", "3306");

// Verificar si la conexión fue exitosa
if ($conexion->connect_error) {
    die("Connection failed: " . $conexion->connect_error);
}

// Establecer el conjunto de caracteres
$conexion->set_charset("utf8");

// Configuración de la zona horaria
date_default_timezone_set("America/Lima");
