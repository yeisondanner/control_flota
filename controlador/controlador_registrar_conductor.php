<?php
// Debe existir $conexion (desde include "../modelo/conexion.php")
if (!isset($conexion)) {
    exit;
}

if (isset($_POST['btnregistrar'])) {

    // ========= 1) Recoger datos =========
    // PERSONA
    $nombres   = trim($_POST['txtnombre'] ?? '');
    $apellidos = trim($_POST['txtapellidos'] ?? '');
    $dni       = trim($_POST['txtdni'] ?? '');
    $direccion = trim($_POST['txtdireccion'] ?? '');
    $telefono  = trim($_POST['txttelefono'] ?? '');
    $email     = trim($_POST['txtemail'] ?? '');
    $fnac      = trim($_POST['txtfechanac'] ?? '');

    // CONDUCTOR
    $numero_licencia    = strtoupper(trim($_POST['txtnlicencia'] ?? ''));
    $categoria_licencia = trim($_POST['txtcatlicencia'] ?? '');
    $fvenc              = trim($_POST['txtfvenc'] ?? '');
    if ($fvenc === '') {
        $fvenc = null;
    }

    // USUARIO
    $username = trim($_POST['txtusuario'] ?? '');
    $clave    = trim($_POST['txtclave'] ?? '');
    $clave2   = trim($_POST['txtclave2'] ?? '');
    $rol      = trim($_POST['rol'] ?? 'Conductor'); // oculto en el form

    // ========= 2) Validaciones básicas =========
    if (
        $nombres === '' || $apellidos === '' || $dni === '' || $direccion === '' ||
        $telefono === '' || $email === '' || $fnac === '' ||
        $numero_licencia === '' || $categoria_licencia === '' ||
        $username === '' || $clave === '' || $clave2 === ''
    ) {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Campos incompletos', 'mensaje' => 'Complete todos los campos obligatorios.'];
        return;
    }
    if (!preg_match('/^\d{8}$/', $dni)) {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'DNI inválido', 'mensaje' => 'El DNI debe tener exactamente 8 dígitos.'];
        return;
    }
    if (!preg_match('/^[A-Za-z0-9._-]{4,32}$/', $username)) {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Usuario inválido', 'mensaje' => 'El usuario debe tener 4–32 caracteres (letras, números, punto, guion o guion_bajo).'];
        return;
    }
    if (strlen($clave) < 6) {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Contraseña débil', 'mensaje' => 'La contraseña debe tener al menos 6 caracteres.'];
        return;
    }
    if ($clave !== $clave2) {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'No coinciden', 'mensaje' => 'Las contraseñas no coinciden.'];
        return;
    }

    // ========= 3) Duplicados: persona.dni / usuario.username / conductor.numero_licencia =========
    // DNI
    $stmt = $conexion->prepare("SELECT id_persona FROM persona WHERE dni = ?");
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Duplicado', 'mensaje' => 'El DNI ya existe.'];
        return;
    }
    $stmt->close();

    // USERNAME
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuario WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Duplicado', 'mensaje' => 'El nombre de usuario ya existe.'];
        return;
    }
    $stmt->close();

    // NÚMERO DE LICENCIA
    $stmt = $conexion->prepare("SELECT id_conductor FROM conductor WHERE numero_licencia = ?");
    $stmt->bind_param("s", $numero_licencia);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Duplicado', 'mensaje' => 'El número de licencia ya está registrado.'];
        return;
    }
    $stmt->close();

    // ========= 4) Transacción: persona -> usuario -> conductor =========
    try {
        $conexion->begin_transaction();

        // 4.1 Insert PERSONA
        $sqlPer = "INSERT INTO persona (nombres, apellidos, dni, direccion, telefono, email, fecha_nacimiento)
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
        $p1 = $conexion->prepare($sqlPer);
        $p1->bind_param("sssssss", $nombres, $apellidos, $dni, $direccion, $telefono, $email, $fnac);
        if (!$p1->execute()) {
            throw new Exception("Error al registrar persona");
        }
        $id_persona = $p1->insert_id;
        $p1->close();

        // 4.2 Insert USUARIO (FK a persona)
        $hash = password_hash($clave, PASSWORD_DEFAULT); // guarda hash en usuario.password
        $sqlUsu = "INSERT INTO usuario (id_persona, username, password, rol)
                   VALUES (?, ?, ?, ?)";
        $p2 = $conexion->prepare($sqlUsu);
        $p2->bind_param("isss", $id_persona, $username, $hash, $rol);
        if (!$p2->execute()) {
            throw new Exception("Error al registrar usuario");
        }
        $id_usuario = $p2->insert_id;
        $p2->close();

        // 4.3 Insert CONDUCTOR (FK a usuario)  <<<< NUEVO ESQUEMA
        $sqlCon = "INSERT INTO conductor (id_usuario, categoria_licencia, numero_licencia, fvencimiento_licencia)
                   VALUES (?, ?, ?, ?)";
        $p3 = $conexion->prepare($sqlCon);
        $p3->bind_param("isss", $id_usuario, $categoria_licencia, $numero_licencia, $fvenc);
        if (!$p3->execute()) {
            throw new Exception("Error al registrar conductor");
        }
        $p3->close();

        $conexion->commit();

        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Conductor y usuario creados correctamente.'];
        header("Location: conductores.php");
        exit;
    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => $e->getMessage()];
        // no redirigimos: se queda en el form para corregir
    }
}
