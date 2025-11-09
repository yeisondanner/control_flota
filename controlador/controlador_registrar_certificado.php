<?php
if (isset($_POST['btnregistrar_cert'])) {
    $vehiculo_id      = (int)($_POST['vehiculo_id'] ?? 0);
    $tipo_certificado = trim($_POST['tipo_certificado'] ?? '');
    $fecha_emision    = $_POST['fecha_emision'] ?? '';
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';

    if ($vehiculo_id <= 0 || $tipo_certificado === '' || $fecha_emision === '' || $fecha_vencimiento === '') {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Datos incompletos', 'mensaje' => 'Complete todos los campos.'];
        return;
    }

    // Validación: evitar duplicado exacto para el mismo vehículo
    $sqlCheck = $conexion->prepare("
    SELECT COUNT(*) 
    FROM certificados
    WHERE id_vehiculo = ? AND tipo_certificado = ? AND fecha_emision = ? AND fecha_vencimiento = ?
  ");
    $sqlCheck->bind_param("isss", $vehiculo_id, $tipo_certificado, $fecha_emision, $fecha_vencimiento);
    $sqlCheck->execute();
    $sqlCheck->bind_result($count);
    $sqlCheck->fetch();
    $sqlCheck->close();

    if ($count > 0) {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Duplicado', 'mensaje' => 'Ya existe un certificado idéntico para este vehículo.'];
        return;
    }

    // Insertar
    $ins = $conexion->prepare("
    INSERT INTO certificados (id_vehiculo, tipo_certificado, fecha_emision, fecha_vencimiento)
    VALUES (?, ?, ?, ?)
  ");
    $ins->bind_param("isss", $vehiculo_id, $tipo_certificado, $fecha_emision, $fecha_vencimiento);

    if ($ins->execute()) {
        $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Éxito', 'mensaje' => 'Certificado registrado.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo registrar el certificado.'];
    }
    $ins->close();
}
