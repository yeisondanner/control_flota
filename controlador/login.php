<?php

session_start();

if (!empty($_POST["btningresar"])) {
    if (!empty($_POST["usuario"]) and !empty($_POST["password"])) {
        $usuario = $_POST["usuario"];
        $password = $_POST["password"];
        $sql = $conexion->query(" select * from usuario where username='$usuario' and password='$password' ");
        if ($datos = $sql->fetch_object()) {
            $_SESSION["nombre"] = $datos->username;
            $_SESSION["apellido"] = $datos->apellidos;
            $_SESSION["id"] = $datos->id_usuario;
            $_SESSION["rol"] = $datos->rol ?? '';
            header("location:../inicio.php");
        } else {
            echo "<div class='alert alert-danger'>Error al ingresar los datos</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Los campos estan vacíos</div>";
    }
}
