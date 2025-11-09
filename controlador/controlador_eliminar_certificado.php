<?php
if (isset($_GET['del'])) {
    $idCert = (int)$_GET['del'];
    // (opcional) puedes usar 'veh' para validar pertenencia
    //$veh = isset($_GET['veh']) ? (int)$_GET['veh'] : 0;

    if ($idCert > 0) {
        $del = $conexion->prepare("DELETE FROM certificados WHERE id_certificado = ?");
        $del->bind_param("i", $idCert);
        if ($del->execute()) {
            $_SESSION['flash'] = ['tipo' => 'success', 'titulo' => 'Eliminado', 'mensaje' => 'Certificado eliminado.'];
        } else {
            $_SESSION['flash'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => 'No se pudo eliminar el certificado.'];
        }
        $del->close();
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'titulo' => 'Aviso', 'mensaje' => 'Identificador inválido.'];
    }

    header("Location: certificados.php");
    exit;
}
