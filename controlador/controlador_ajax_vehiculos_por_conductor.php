<?php
// Respuestas JSON para 2 casos:
// a) ?id_conductor=ID         -> lista de vehículos asignados
// b) ?ultimo_mant=ID_VEHICULO -> devuelve {kilometraje_proximo: ...}

header('Content-Type: application/json; charset=utf-8');

require_once("../modelo/conexion.php");

// a) Vehículos por conductor
if (isset($_GET['id_conductor'])) {
    $idc = (int)$_GET['id_conductor'];
    if ($idc <= 0) {
        echo json_encode(['vehiculos' => []]);
        exit;
    }

    $q = $conexion->prepare("
    SELECT v.id_vehiculo, v.matricula, v.marca, v.modelo
    FROM conductor_vehiculo cv
    JOIN vehiculos v ON v.id_vehiculo = cv.id_vehiculo
    WHERE cv.id_conductor = ?
    ORDER BY v.marca ASC, v.modelo ASC
  ");
    $q->bind_param('i', $idc);
    $q->execute();
    $rs = $q->get_result();

    $data = [];
    while ($r = $rs->fetch_assoc()) {
        $data[] = $r;
    }
    $q->close();

    echo json_encode(['vehiculos' => $data]);
    exit;
}

// b) Último mantenimiento de un vehículo (solo referencia informativa)
// b) Último mantenimiento de un vehículo (solo referencia informativa)
if (isset($_GET['ultimo_mant'])) {
    $idv = (int)$_GET['ultimo_mant'];
    if ($idv <= 0) {
        echo json_encode(['kilometraje_proximo' => null, 'hora_proxima' => null]);
        exit;
    }

    $q = $conexion->prepare("
        SELECT kilometraje_proximo, hora_proxima
        FROM mantenimientos
        WHERE id_vehiculo = ?
        ORDER BY fecha DESC, id_mantenimiento DESC
        LIMIT 1
    ");
    $q->bind_param('i', $idv);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    $q->close();

    echo json_encode([
        'kilometraje_proximo' => $r['kilometraje_proximo'] ?? null,
        'hora_proxima'        => $r['hora_proxima'] ?? null
    ]);
    exit;
}

// Default
echo json_encode(['error' => 'Parámetros inválidos']);
