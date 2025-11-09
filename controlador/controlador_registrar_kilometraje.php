<?php
if (isset($_POST['btnregistrar_km'])) {
    // Sanitizar obligatorios
    $id_conductor = isset($_POST['id_conductor']) ? (int)$_POST['id_conductor'] : 0;
    $id_vehiculo  = isset($_POST['id_vehiculo'])  ? (int)$_POST['id_vehiculo']  : 0;

    // Opcionales
    $kilometraje_in = trim($_POST['kilometraje'] ?? '');
    $horas_in       = trim($_POST['horas'] ?? '');   // 'HH:MM' o ''

    // Normalizar
    $kilometraje = ($kilometraje_in === '') ? null : (float)$kilometraje_in;
    // Guardar TIME como 'HH:MM:SS' o NULL
    $horas_db = ($horas_in === '') ? null : ($horas_in . (strlen($horas_in) === 5 ? ':00' : ''));

    // Helpers
    $toSeconds = function (?string $hhmmss): ?int {
        if ($hhmmss === null || $hhmmss === '') return null;
        // Espera 'HH:MM:SS' (si viene 'HH:MM' desde BD por alguna razón, lo completamos)
        if (strlen($hhmmss) === 5) $hhmmss .= ':00';
        [$H, $M, $S] = array_map('intval', explode(':', $hhmmss));
        return ($H * 3600) + ($M * 60) + $S;
    };
    $fmtHHMM = function (?string $hhmmss): string {
        if ($hhmmss === null || $hhmmss === '') return '';
        if (strlen($hhmmss) === 5) $hhmmss .= ':00';
        return substr($hhmmss, 0, 5); // HH:MM
    };

    // Validación mínima
    $faltantes = [];
    if ($id_conductor <= 0) $faltantes[] = 'conductor';
    if ($id_vehiculo  <= 0) $faltantes[] = 'vehículo';
    // Requerimiento: al menos kilometraje o horas
    if ($kilometraje === null && $horas_db === null) {
        $faltantes[] = 'kilometraje u horas (al menos uno)';
    }
    if (!empty($faltantes)) {
        $_SESSION['flash'] = [
            'tipo' => 'warning',
            'titulo' => 'Campos incompletos',
            'mensaje' => 'Complete: ' . implode(', ', $faltantes) . '.'
        ];
        header("Location: registrar_kilometraje.php");
        exit;
    }

    // 1) Verificar asignación vehículo-conductor
    $q = $conexion->prepare("SELECT 1 FROM conductor_vehiculo WHERE id_conductor=? AND id_vehiculo=? LIMIT 1");
    $q->bind_param('ii', $id_conductor, $id_vehiculo);
    $q->execute();
    $asig = $q->get_result()->fetch_row();
    $q->close();
    if (!$asig) {
        $_SESSION['flash'] = [
            'tipo' => 'error',
            'titulo' => 'No asignado',
            'mensaje' => 'El vehículo seleccionado no está asignado al conductor.'
        ];
        header("Location: registrar_kilometraje.php");
        exit;
    }

    // 2) Obtener última referencia de mantenimiento (km/hora próxima)
    $q = $conexion->prepare("
        SELECT kilometraje_proximo, hora_proxima
        FROM mantenimientos
        WHERE id_vehiculo = ?
        ORDER BY fecha DESC, id_mantenimiento DESC
        LIMIT 1
    ");
    $q->bind_param('i', $id_vehiculo);
    $q->execute();
    $ref = $q->get_result()->fetch_assoc();
    $q->close();

    // 3A) Regla negocio para KM: si hay 'kilometraje_proximo', exigir km < kprox
    if ($kilometraje !== null && $ref && $ref['kilometraje_proximo'] !== null) {
        $kprox = (float)$ref['kilometraje_proximo'];
        if (!($kilometraje < $kprox)) {
            $_SESSION['flash'] = [
                'tipo' => 'error',
                'titulo' => 'Regla de validación',
                'mensaje' => "El kilometraje ingresado ($kilometraje) debe ser menor al próximo mantenimiento ($kprox)."
            ];
            header("Location: registrar_kilometraje.php");
            exit;
        }
    }

    // 3B) Regla negocio para HORAS: si hay 'hora_proxima', exigir horas < hprox
    if ($horas_db !== null && $ref && $ref['hora_proxima'] !== null) {
        $horas_seg = $toSeconds($horas_db);
        $hprox_seg = $toSeconds($ref['hora_proxima']);
        if ($horas_seg !== null && $hprox_seg !== null && !($horas_seg < $hprox_seg)) {
            $_SESSION['flash'] = [
                'tipo' => 'error',
                'titulo' => 'Regla de validación',
                'mensaje' => "La hora semanal ingresada (" . $fmtHHMM($horas_db) . ") debe ser menor a la hora próxima (" . $fmtHHMM($ref['hora_proxima']) . ")."
            ];
            header("Location: registrar_kilometraje.php");
            exit;
        }
    }

    // 4) Insertar registro
    $ins = $conexion->prepare("
        INSERT INTO kilometraje_semanal (id_conductor, id_vehiculo, kilometraje, horas)
        VALUES (?,?,?,?)
    ");
    // Tipos: i i d s  (NULL es aceptado por bind_param)
    $ins->bind_param('iids', $id_conductor, $id_vehiculo, $kilometraje, $horas_db);

    if ($ins->execute()) {
        $hayRef = ($ref && ($ref['kilometraje_proximo'] !== null || $ref['hora_proxima'] !== null));
        $_SESSION['flash'] = [
            'tipo' => 'success',
            'titulo' => 'Éxito',
            'mensaje' => $hayRef ? 'Registro guardado correctamente.' : 'Registro guardado. (Sin referencia de mantenimiento programado)'
        ];
    } else {
        $_SESSION['flash'] = [
            'tipo' => 'error',
            'titulo' => 'Error',
            'mensaje' => 'No se pudo registrar.'
        ];
    }
    $ins->close();

    header("Location: registrar_kilometraje.php");
    exit;
}
