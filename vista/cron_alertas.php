<?php
// cron_alertas.php
session_write_close();
date_default_timezone_set('America/Lima');

// ==== Composer autoload (PHPMailer, etc.) ====
echo "Cargando Composer autoload...\n";
$autoload_candidates = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    'C:/xampp/htdocs/control_flota/vendor/autoload.php',
];
$composerAutoload = null;
foreach ($autoload_candidates as $cand) {
    if (file_exists($cand)) {
        $composerAutoload = $cand;
        break;
    }
}
if ($composerAutoload) {
    require_once $composerAutoload;
    echo "Autoload de Composer cargado exitosamente.\n";
} else {
    echo "Error al cargar el autoload de Composer.\n";
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ==== Conexión DB ====
require_once __DIR__ . "/../modelo/conexion.php";
echo "Conexión a la base de datos establecida...\n";

// ================== CONFIG ==================
$UMBRAL_KM     = 500; // alerta si faltan <= 500 km
$UMBRAL_HORAS  = 5;   // alerta si faltan <= 5 horas (HH:MM:SS -> horas decimales)
$ENVIAR_EMAIL  = true;

// SMTP
$SMTP_HOST = 'smtp.gmail.com';
$SMTP_USER = 'vypsoporte30@gmail.com';
$SMTP_PASS = 'muga akta guef hfyf';
$SMTP_PORT = 587;
$MAIL_FROM = 'vypsoporte30@gmail.com';
$MAIL_FROM_NAME = 'Alertas Flota';

// ================== FUNCIONES ==================
function getDestinatarios(mysqli $conexion): array
{
    $out = [];
    if ($res = $conexion->query("SELECT nombre, email FROM alerta_destinatarios WHERE activo=1 AND email IS NOT NULL AND email<>''")) {
        while ($r = $res->fetch_assoc()) $out[] = $r;
        $res->close();
    }
    echo "Destinatarios activos: " . count($out) . "\n";
    return $out;
}

function enviarEmail(
    array $destinatarios,
    string $subject,
    string $html,
    string $from,
    string $fromName,
    bool $usarPHPMailer,
    ?string $host = null,
    ?string $user = null,
    ?string $pass = null,
    ?int $port = null
): bool {
    $tos = [];
    foreach ($destinatarios as $d) if (!empty($d['email'])) $tos[] = $d['email'];
    if (empty($tos)) {
        echo "No hay destinatarios con email.\n";
        return false;
    }

    if ($usarPHPMailer && class_exists('PHPMailer\\PHPMailer\\PHPMailer') && $host && $user && $pass && $port) {
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $port;
            $mail->setFrom($from, $fromName);
            foreach ($destinatarios as $dest) {
                if (!empty($dest['email'])) $mail->addAddress($dest['email'], $dest['nombre'] ?? '');
            }
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = strip_tags($html);
            $mail->send();
            echo "Correo enviado correctamente.\n";
            return true;
        } catch (Exception $e) {
            echo "Error al enviar correo (PHPMailer): " . $e->getMessage() . "\n";
        }
    }

    // Fallback nativo
    $toHeader = implode(',', $tos);
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $fromName . " <" . $from . ">\r\n";
    $ok = @mail($toHeader, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html, $headers);
    if (!$ok) echo "Error al enviar correo (mail() nativo).\n";
    return $ok;
}

function timeToHours(string $hhmmss): float
{
    if (strlen($hhmmss) < 5) return 0.0; // defensivo
    $parts = explode(':', $hhmmss);
    $hh = (int)($parts[0] ?? 0);
    $mm = (int)($parts[1] ?? 0);
    $ss = (int)($parts[2] ?? 0);
    return $hh + ($mm / 60) + ($ss / 3600);
}

function fmtHM(string $hhmmss): string
{
    return substr($hhmmss, 0, 5); // "HH:MM"
}

// ================== LÓGICA: CARGAR ÚLTIMO MANTENIMIENTO ==================
echo "Obteniendo último mantenimiento por vehículo...\n";
$ultimos = [];
if ($res = $conexion->query("SELECT id_vehiculo, MAX(fecha) AS fecha_ult FROM mantenimientos GROUP BY id_vehiculo")) {
    while ($r = $res->fetch_assoc()) {
        if (!empty($r['id_vehiculo']) && !empty($r['fecha_ult'])) {
            $ultimos[(int)$r['id_vehiculo']] = $r['fecha_ult']; // último mantenimiento real
        }
    }
    $res->close();
}
echo "Últimos mantenimientos obtenidos para los vehículos...\n";

// ================== EVALUACIÓN DE ALERTAS ==================
$alertasKM = [];
$alertasHR = [];

foreach ($ultimos as $idVeh => $fechaUlt) {
    // Traer datos del último mantenimiento (KP y HP)
    $stmt = $conexion->prepare("
        SELECT m.id_mantenimiento, m.fecha,
               m.kilometraje_proximo,
               m.hora_proxima,
               v.matricula, v.marca, v.modelo
        FROM mantenimientos m
        JOIN vehiculos v ON v.id_vehiculo = m.id_vehiculo
        WHERE m.id_vehiculo=? AND m.fecha=? 
        LIMIT 1
    ");
    $stmt->bind_param("is", $idVeh, $fechaUlt);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) continue;

    echo "Procesando vehículo ID: {$idVeh} Matricula: {$row['matricula']}\n";

    // ================= KM =================
    $km_prox = $row['kilometraje_proximo'];
    if ($km_prox !== null && (float)$km_prox > 0) {
        $km_prox = (float)$km_prox;

        // KA: registro más reciente ESTRICTAMENTE POSTERIOR al último mantenimiento
        $stmt2 = $conexion->prepare("
            SELECT kilometraje, fecha_registro
            FROM kilometraje_semanal
            WHERE id_vehiculo=? AND fecha_registro > ?
            ORDER BY fecha_registro DESC
            LIMIT 1
        ");
        $stmt2->bind_param("is", $idVeh, $fechaUlt);
        $stmt2->execute();
        $recKm = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();

        if ($recKm) {
            $km_act  = (float)$recKm['kilometraje'];
            $fec_km  = $recKm['fecha_registro'];
            $km_falt = $km_prox - $km_act;

            echo "  [KM] KA: {$km_act} (reg: {$fec_km}) | KP: {$km_prox} | Faltan: {$km_falt}\n";

            if ($km_falt <= $UMBRAL_KM) {
                echo "  [KM] Alerta cumplida.\n";
                $alertasKM[] = [
                    'id_vehiculo' => $idVeh,
                    'matricula'   => $row['matricula'],
                    'marca'       => $row['marca'],
                    'modelo'      => $row['modelo'],
                    'ka_valor'    => $km_act,
                    'ka_fecha'    => $fec_km,
                    'km_proximo'  => $km_prox,
                    'faltan'      => $km_falt
                ];
            } else {
                echo "  [KM] No cumple umbral.\n";
            }
        } else {
            echo "  [KM] No hay kilometraje registrado posterior a {$fechaUlt}.\n";
        }
    } else {
        echo "  [KM] Sin 'kilometraje_proximo' en el último mantenimiento (se omite).\n";
    }

    // ================= HORAS =================
    $hora_prox = $row['hora_proxima'];
    if (!empty($hora_prox) && $hora_prox !== '00:00:00') {
        $hp_dec = timeToHours($hora_prox);

        // Horas actuales: registro más reciente ESTRICTAMENTE POSTERIOR al último mantenimiento
        $stmt3 = $conexion->prepare("
            SELECT horas, fecha_registro
            FROM kilometraje_semanal
            WHERE id_vehiculo=? 
              AND fecha_registro > ?
              AND horas IS NOT NULL
              AND horas <> '00:00:00'
            ORDER BY fecha_registro DESC
            LIMIT 1
        ");
        $stmt3->bind_param("is", $idVeh, $fechaUlt);
        $stmt3->execute();
        $recHs = $stmt3->get_result()->fetch_assoc();
        $stmt3->close();

        if ($recHs) {
            $hs_act_str = $recHs['horas'];
            $hs_act_dec = timeToHours($hs_act_str);
            $fec_hs     = $recHs['fecha_registro'];
            $hs_falt    = $hp_dec - $hs_act_dec;

            echo "  [HR] HA: " . fmtHM($hs_act_str) . " ({$hs_act_dec}h) (reg: {$fec_hs}) | HP: " . fmtHM($hora_prox) . " ({$hp_dec}h) | Faltan: {$hs_falt}h\n";

            if ($hs_falt <= $UMBRAL_HORAS) {
                echo "  [HR] Alerta cumplida.\n";
                $alertasHR[] = [
                    'id_vehiculo' => $idVeh,
                    'matricula'   => $row['matricula'],
                    'marca'       => $row['marca'],
                    'modelo'      => $row['modelo'],
                    'ha_valor'    => fmtHM($hs_act_str),
                    'ha_valor_dec' => $hs_act_dec,
                    'ha_fecha'    => $fec_hs,
                    'hora_prox'   => fmtHM($hora_prox),
                    'hora_prox_dec' => $hp_dec,
                    'faltan_h'    => $hs_falt
                ];
            } else {
                echo "  [HR] No cumple umbral.\n";
            }
        } else {
            echo "  [HR] No hay horas registradas posteriores a {$fechaUlt}.\n";
        }
    } else {
        echo "  [HR] Sin 'hora_proxima' en el último mantenimiento (se omite).\n";
    }
}

// ====== Envío de correo si hay alertas ======
echo "Alertas KM: " . count($alertasKM) . " | Alertas HR: " . count($alertasHR) . "\n";
if (!empty($alertasKM) || !empty($alertasHR)) {
    echo "Hay alertas para enviar.\n";

    $html = '<h2>Alertas de Flota</h2>';
    $html .= '<p>Se encontraron vehículos dentro de los umbrales configurados.</p>';

    if (!empty($alertasKM)) {
        $html .= '<h3>Por Kilometraje (≤ ' . $UMBRAL_KM . ' km)</h3>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
                    <tr>
                        <th>Matrícula</th>
                        <th>Marca/Modelo</th>
                        <th>KA (fecha)</th>
                        <th>KP</th>
                        <th>Faltan</th>
                    </tr>';
        foreach ($alertasKM as $a) {
            $html .= '<tr>
                        <td>' . htmlspecialchars($a['matricula']) . '</td>
                        <td>' . htmlspecialchars(trim(($a['marca'] ?? '') . ' ' . ($a['modelo'] ?? ''))) . '</td>
                        <td>' . number_format($a['ka_valor'], 0, '.', ',') . ' (' . htmlspecialchars($a['ka_fecha']) . ')</td>
                        <td>' . number_format($a['km_proximo'], 0, '.', ',') . '</td>
                        <td>' . number_format($a['faltan'], 0, '.', ',') . '</td>
                      </tr>';
        }
        $html .= '</table>';
    }

    if (!empty($alertasHR)) {
        $html .= '<h3>Por Horas (≤ ' . $UMBRAL_HORAS . ' h)</h3>';
        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;">
                    <tr>
                        <th>Matrícula</th>
                        <th>Marca/Modelo</th>
                        <th>HA (fecha)</th>
                        <th>HP</th>
                        <th>Faltan (h)</th>
                    </tr>';
        foreach ($alertasHR as $a) {
            $html .= '<tr>
                        <td>' . htmlspecialchars($a['matricula']) . '</td>
                        <td>' . htmlspecialchars(trim(($a['marca'] ?? '') . ' ' . ($a['modelo'] ?? ''))) . '</td>
                        <td>' . htmlspecialchars($a['ha_valor']) . ' (' . htmlspecialchars($a['ha_fecha']) . ')</td>
                        <td>' . htmlspecialchars($a['hora_prox']) . '</td>
                        <td>' . number_format($a['faltan_h'], 2, '.', ',') . '</td>
                      </tr>';
        }
        $html .= '</table>';
    }

    echo "Enviando correo...\n";
    $resultadoEmail = enviarEmail(
        getDestinatarios($conexion),
        "Alertas de Flota - Kilometraje/Horas",
        $html,
        $MAIL_FROM,
        $MAIL_FROM_NAME,
        (bool)$composerAutoload,
        $SMTP_HOST,
        $SMTP_USER,
        $SMTP_PASS,
        $SMTP_PORT
    );
    echo $resultadoEmail ? "Correo enviado con éxito.\n" : "Hubo un problema al enviar el correo.\n";
} else {
    echo "No hay alertas para enviar.\n";
}

echo "OK " . date('Y-m-d H:i:s') . PHP_EOL;
