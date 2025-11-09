<?php
if (!empty($_POST['btnmodificar']) && $_POST['btnmodificar'] === 'ok') {
    // $conexion ya está incluido desde la vista
    try {
        $idConductor = (int)($_POST['txtidconductor'] ?? 0);
        $idPersona   = (int)($_POST['txtidpersona'] ?? 0);

        $nombres     = trim($_POST['txtnombre'] ?? '');
        $apellidos   = trim($_POST['txtapellidos'] ?? '');
        $dni         = preg_replace('/\D/', '', $_POST['txtdni'] ?? '');
        $nlicencia   = trim($_POST['txtnlicencia'] ?? '');
        $catlicencia = trim($_POST['txtcatlicencia'] ?? '');
        $fvenc       = trim($_POST['txtfvenc'] ?? '');
        $telefono    = trim($_POST['txttelefono'] ?? '');
        $email       = trim($_POST['txtcorreo'] ?? '');
        $direccion   = trim($_POST['txtdireccion'] ?? '');
        $fecha_nacimiento = trim($_POST['txtfechanacimiento'] ?? '');

        if ($nombres === '' || $apellidos === '' || !preg_match('/^\d{8}$/', $dni) || $nlicencia === '' || $catlicencia === '') {
            $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Atención', 'mensaje' => 'Complete correctamente los campos obligatorios.'];
            header('Location: conductores.php');
            exit;
        }

        $conexion->begin_transaction();

        // Actualización de la tabla persona
        $stmt1 = $conexion->prepare("UPDATE persona SET nombres=?, apellidos=?, dni=?, telefono=?, email=?, direccion=?, fecha_nacimiento=? WHERE id_persona=?");
        $stmt1->bind_param("sssssssi", $nombres, $apellidos, $dni, $telefono, $email, $direccion, $fecha_nacimiento, $idPersona);
        if (!$stmt1->execute()) throw new Exception("Error al actualizar persona");
        $stmt1->close();

        // Actualización de la tabla conductor
        if ($fvenc === '') {
            $stmt2 = $conexion->prepare("UPDATE conductor SET numero_licencia=?, categoria_licencia=?, fvencimiento_licencia=NULL WHERE id_conductor=?");
            $stmt2->bind_param("ssi", $nlicencia, $catlicencia, $idConductor);
        } else {
            $stmt2 = $conexion->prepare("UPDATE conductor SET numero_licencia=?, categoria_licencia=?, fvencimiento_licencia=? WHERE id_conductor=?");
            $stmt2->bind_param("sssi", $nlicencia, $catlicencia, $fvenc, $idConductor);
        }
        if (!$stmt2->execute()) throw new Exception("Error al actualizar conductor");
        $stmt2->close();

        $conexion->commit();

        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Conductor modificado correctamente.'];
        header('Location: conductores.php');
        exit;
    } catch (Exception $e) {
        if ($conexion->errno) {
            $conexion->rollback();
        }
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo modificar: ' . $e->getMessage()];
        header('Location: conductores.php');
        exit;
    }
}
