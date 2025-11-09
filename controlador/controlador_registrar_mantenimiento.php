<?php
if (!empty($_POST['btnregistrar_mantenimiento'])) {

    // Campos del formulario
    $vehiculo             = isset($_POST['vehiculo']) ? (int)$_POST['vehiculo'] : 0;
    $tipo                 = trim($_POST['tipo_mantenimiento'] ?? '');
    $descripcion          = trim($_POST['descripcion'] ?? '');
    $fecha                = $_POST['fecha'] ?? null;

    // === OPCIONALES: normalizados a NULL si vienen vacíos ===
    $hora_actual          = $_POST['hora_actual']  ?? null;
    if ($hora_actual === '') $hora_actual = null;

    $hora_proxima         = $_POST['hora_proxima'] ?? null;
    if ($hora_proxima === '') $hora_proxima = null;

    if (isset($_POST['kilometraje_actual']) && $_POST['kilometraje_actual'] !== '') {
        $kilometraje_actual = (float)$_POST['kilometraje_actual'];
    } else {
        $kilometraje_actual = null;
    }

    if (isset($_POST['kilometraje_proximo']) && $_POST['kilometraje_proximo'] !== '') {
        $kilometraje_proximo = (float)$_POST['kilometraje_proximo'];
    } else {
        $kilometraje_proximo = null;
    }
    // ========================================================

    // Requerido: gasto_mantenimiento (si quieres que sea opcional, quítalo de la validación)
    if (isset($_POST['gasto_mantenimiento']) && $_POST['gasto_mantenimiento'] !== '') {
        $gasto_mantenimiento = (float)$_POST['gasto_mantenimiento'];
    } else {
        $gasto_mantenimiento = null;
    }

    // Arrays de relación
    $herrSel = isset($_POST['herramientas']) && is_array($_POST['herramientas']) ? array_map('intval', $_POST['herramientas']) : [];
    $sumSel  = isset($_POST['suministros'])  && is_array($_POST['suministros'])  ? array_map('intval', $_POST['suministros'])  : [];

    // Normalizar según reglas de negocio
    if ($tipo === 'Preventivo') {
        $herrSel = []; // ignorar herramientas
    } elseif ($tipo === 'Correctivo') {
        $sumSel = [];  // ignorar suministros
    }

    // Validación mínima lado servidor
    $faltantes = [];
    if ($vehiculo <= 0) $faltantes[] = 'vehículo';
    if ($tipo !== 'Preventivo' && $tipo !== 'Correctivo') $faltantes[] = 'tipo de mantenimiento';
    if ($descripcion === '') $faltantes[] = 'descripción';
    if (empty($fecha)) $faltantes[] = 'fecha';
    // ← OPCIONALES: NO se validan como obligatorios:
    // hora_actual, hora_proxima, kilometraje_actual, kilometraje_proximo

    // gasto_mantenimiento requerido (déjalo o quítalo según tu flujo)
    if ($gasto_mantenimiento === null) $faltantes[] = 'gasto de mantenimiento';

    // Validación de grupo según tipo
    if ($tipo === 'Preventivo' && count($sumSel) === 0) $faltantes[] = 'al menos un suministro';
    if ($tipo === 'Correctivo' && count($herrSel) === 0) $faltantes[] = 'al menos una herramienta';

    if (!empty($faltantes)) {
        $_SESSION['flash'] = [
            'tipo' => 'warning',
            'titulo' => 'Campos incompletos',
            'mensaje' => 'Complete: ' . implode(', ', $faltantes) . '.'
        ];
        return; // permanece en la misma vista
    }

    // Inserción transaccional
    $conexion->begin_transaction();
    try {
        // Insert en mantenimientos
        $stmt = $conexion->prepare("
            INSERT INTO mantenimientos
              (id_vehiculo, tipo, descripcion, fecha, kilometraje_actual, kilometraje_proximo, hora_actual, hora_proxima, gasto_mantenimiento)
            VALUES
              (?,?,?,?,?,?,?,?,?)
        ");
        if (!$stmt) throw new Exception("Error preparando INSERT: " . $conexion->error);

        // Tipos: i s s s d d s s d
        $stmt->bind_param(
            "isssddssd",
            $vehiculo,
            $tipo,
            $descripcion,
            $fecha,
            $kilometraje_actual,   // puede ser NULL
            $kilometraje_proximo,  // puede ser NULL
            $hora_actual,          // puede ser NULL
            $hora_proxima,         // puede ser NULL
            $gasto_mantenimiento
        );

        if (!$stmt->execute()) throw new Exception("Error ejecutando INSERT: " . $stmt->error);
        $id_mant = $stmt->insert_id;
        $stmt->close();

        // Relación herramientas
        if (!empty($herrSel)) {
            $stmtH = $conexion->prepare("
                INSERT INTO mantenimientos_herramientas (id_mantenimiento, id_herramientas, fecha_registro)
                VALUES (?, ?, CURDATE())
            ");
            if (!$stmtH) throw new Exception("Error preparando INSERT herramientas: " . $conexion->error);
            foreach ($herrSel as $idH) {
                $stmtH->bind_param("ii", $id_mant, $idH);
                if (!$stmtH->execute()) throw new Exception("Error insertando herramienta: " . $stmtH->error);
            }
            $stmtH->close();
        }

        // Relación suministros
        if (!empty($sumSel)) {
            $stmtS = $conexion->prepare("
                INSERT INTO mantenimientos_suministros (id_mantenimiento, id_suministros, fecha_registro)
                VALUES (?, ?, CURDATE())
            ");
            if (!$stmtS) throw new Exception("Error preparando INSERT suministros: " . $conexion->error);
            foreach ($sumSel as $idS) {
                $stmtS->bind_param("ii", $id_mant, $idS);
                if (!$stmtS->execute()) throw new Exception("Error insertando suministro: " . $stmtS->error);
            }
            $stmtS->close();
        }

        $conexion->commit();

        $_SESSION['flash'] = [
            'tipo' => 'success',
            'titulo' => 'Registrado',
            'mensaje' => 'El mantenimiento se registró correctamente.'
        ];
        header("Location: registrar_mantenimiento.php");
        exit;
    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['flash'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'mensaje' => 'No se pudo registrar el mantenimiento: ' . $e->getMessage()
        ];
        return;
    }
}
