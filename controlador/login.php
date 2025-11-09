<?php

session_start();

if (!empty($_POST["btningresar"])) {
    if (!empty($_POST["usuario"]) and !empty($_POST["password"])) {
        $usuario = $_POST["usuario"];
        $password = $_POST["password"];
        
        // Escapar el usuario para prevenir SQL injection
        $usuario_escaped = $conexion->real_escape_string($usuario);
        
        // Consulta para obtener usuario con datos de persona
        $sql = $conexion->query(" 
            SELECT u.*, p.nombres, p.apellidos 
            FROM usuario u 
            LEFT JOIN persona p ON p.id_persona = u.id_persona 
            WHERE u.username = '$usuario_escaped'
        ");
        
        if ($datos = $sql->fetch_object()) {
            // Verificar la contraseña: puede ser texto plano o hash bcrypt
            $password_valida = false;
            
            // Si la contraseña en BD comienza con $2y$ es un hash bcrypt
            if (substr($datos->password, 0, 4) === '$2y$') {
                // Verificar contraseña encriptada
                $password_valida = password_verify($password, $datos->password);
            } else {
                // Comparar contraseña en texto plano
                $password_valida = ($datos->password === $password);
            }
            
            if ($password_valida) {
                // Guardar datos básicos de sesión
                $_SESSION["nombre"] = !empty($datos->nombres) ? $datos->nombres : $datos->username;
                $_SESSION["apellidos"] = !empty($datos->apellidos) ? $datos->apellidos : '';
                $_SESSION["id"] = $datos->id_usuario;
                $_SESSION["rol"] = $datos->rol ?? '';
                
                // Si el usuario tiene rol "Conductor", verificar que tenga registro en tabla conductor
                if ($datos->rol == 'Conductor') {
                    // Buscar el id_conductor asociado al usuario
                    $sql_conductor = $conexion->query(" 
                        SELECT id_conductor 
                        FROM conductor 
                        WHERE id_usuario = " . intval($datos->id_usuario) . " 
                        LIMIT 1 
                    ");
                    
                    if ($conductor = $sql_conductor->fetch_object()) {
                        // Usuario es conductor válido - guardar id_conductor y redirigir a kilometrajes.php
                        $_SESSION["id_conductor"] = $conductor->id_conductor;
                        $_SESSION["es_conductor"] = true;
                        header("location:../kilometrajes.php");
                        exit;
                    } else {
                        // Usuario tiene rol Conductor pero no tiene registro en tabla conductor
                        echo "<div class='alert alert-danger'>El usuario no tiene un registro de conductor válido. Contacte al administrador.</div>";
                    }
                } else {
                    // Usuario normal (no conductor)
                    $_SESSION["es_conductor"] = false;
                    header("location:../inicio.php");
                    exit;
                }
            } else {
                echo "<div class='alert alert-danger'>Usuario o contraseña incorrectos</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Usuario o contraseña incorrectos</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Los campos estan vacíos</div>";
    }
}
