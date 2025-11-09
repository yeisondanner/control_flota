<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}

include "../modelo/conexion.php";
/* Controlador de eliminación ANTES del HTML */
include "../controlador/controlador_eliminar_mantenimiento.php";

/* Flash (post redirección) */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* ====== Filtros ====== */
$tipo         = isset($_GET['tipo']) ? trim($_GET['tipo']) : '';
$vehiculo_id  = isset($_GET['vehiculo']) ? trim($_GET['vehiculo']) : '';
$fdesde       = isset($_GET['fdesde']) ? trim($_GET['fdesde']) : '';
$fhasta       = isset($_GET['fhasta']) ? trim($_GET['fhasta']) : '';

$where = [];
if ($tipo !== '' && in_array($tipo, ['Preventivo', 'Correctivo'])) {
    $where[] = "m.tipo = '" . $conexion->real_escape_string($tipo) . "'";
}
if ($vehiculo_id !== '' && ctype_digit($vehiculo_id)) {
    $where[] = "m.id_vehiculo = " . intval($vehiculo_id);
}
if ($fdesde !== '') {
    $where[] = "m.fecha >= '" . $conexion->real_escape_string($fdesde) . "'";
}
if ($fhasta !== '') {
    $where[] = "m.fecha <= '" . $conexion->real_escape_string($fhasta) . "'";
}
$cond = (count($where) ? ("WHERE " . implode(" AND ", $where)) : "");

/* Combo de vehículos para filtro */
$vehiculos = $conexion->query("
    SELECT v.id_vehiculo, CONCAT(v.matricula, ' - ', v.tipo) AS label
    FROM vehiculos v
    ORDER BY v.matricula ASC
");

/* Consulta principal */
$sql = $conexion->query("
  SELECT 
    m.id_mantenimiento,
    v.matricula,
    v.tipo AS tipo_vehiculo,
    m.tipo AS tipo_mantenimiento,
    m.descripcion,
    m.fecha,
    m.hora_actual,
    m.hora_proxima,
    m.kilometraje_actual,
    m.kilometraje_proximo,
    m.gasto_mantenimiento
  FROM mantenimientos m
  LEFT JOIN vehiculos v ON v.id_vehiculo = m.id_vehiculo
  $cond
  ORDER BY m.id_mantenimiento DESC
");
?>
<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo */
    ul li:nth-child(10) .activo {
        background: #0b96d6 !important;
    }

    h4.text-secondary {
        color: #374151 !important;
        font-weight: 800;
        letter-spacing: .3px;
    }

    .card-like {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
        margin-top: 14px;
    }

    table {
        table-layout: fixed;
        width: 100%;
    }

    .dataTables_filter {
        margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: .2em .8em;
        margin-left: 2px;
    }

    .table thead th,
    .thead-dark th,
    .modal .table thead th {
        background: #0b96d6 !important;
        color: #fff !important;
        border-color: #0b86c0 !important;
    }

    .acciones-boton {
        display: flex;
        gap: 6px;
        justify-content: center;
        flex-wrap: nowrap;
    }

    .acciones-boton .btn.btn-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        min-height: 28px;
    }

    .filters .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .filters .form-group {
        flex: 1 1 20%;
        margin-bottom: 10px;
    }

    /* Alineación numérica derecha */
    .text-right {
        text-align: right;
    }

    .modal .table th,
    .modal .table td {
        vertical-align: middle;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">LISTA DE MANTENIMIENTOS</h4>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" referrerpolicy="no-referrer" />

    <!-- Barra de filtros -->
    <div class="card-like">
        <form class="filters" method="GET" action="">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="">-- Todos --</option>
                        <option value="Preventivo" <?= $tipo === 'Preventivo' ? 'selected' : ''; ?>>Preventivo</option>
                        <option value="Correctivo" <?= $tipo === 'Correctivo' ? 'selected' : ''; ?>>Correctivo</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Vehículo</label>
                    <select name="vehiculo" class="form-control">
                        <option value="">-- Todos --</option>
                        <?php while ($v = $vehiculos->fetch_object()): ?>
                            <option value="<?= (int)$v->id_vehiculo ?>" <?= ($vehiculo_id == (string)$v->id_vehiculo ? 'selected' : '') ?>>
                                <?= htmlspecialchars($v->label, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Desde</label>
                    <input type="date" name="fdesde" class="form-control" value="<?= htmlspecialchars($fdesde, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group col-md-2">
                    <label>Hasta</label>
                    <input type="date" name="fhasta" class="form-control" value="<?= htmlspecialchars($fhasta, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end" style="gap:6px;">
                    <button class="btn btn-primary btn-block" type="submit"><i class="fa fa-filter"></i>&nbsp;Filtrar</button>
                    <a class="btn btn-secondary btn-block" href="mantenimiento.php"><i class="fa fa-eraser"></i>&nbsp;Limpiar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card-like">
        <div class="d-flex justify-content-end mb-2">
            <a href="registrar_mantenimiento.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i>&nbsp;Registrar
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="example">
                <thead>
                    <tr>
                        <th>MATRÍCULA</th>
                        <th>TIPO</th>
                        <th>FECHA</th>
                        <th>HORA ACT.</th>
                        <th>HORA PRÓX.</th>
                        <th>KM ACTUAL</th>
                        <th>KM PRÓX.</th>
                        <th>GASTO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $modales = '';
                    while ($row = $sql->fetch_object()):
                        $idM   = (int)$row->id_mantenimiento;
                        $mat   = htmlspecialchars($row->matricula ?? '', ENT_QUOTES, 'UTF-8');
                        $tipoM = htmlspecialchars($row->tipo_mantenimiento ?? '', ENT_QUOTES, 'UTF-8');
                        $desc  = htmlspecialchars($row->descripcion ?? '', ENT_QUOTES, 'UTF-8');

                        /* ===== FECHA: DD/MM/AAAA ===== */
                        $fechaRaw = $row->fecha ?? '';
                        if ($fechaRaw) {
                            $dt = DateTime::createFromFormat('Y-m-d', $fechaRaw);
                            $fechaFmt = $dt ? $dt->format('d/m/Y') : date('d/m/Y', strtotime($fechaRaw));
                        } else {
                            $fechaFmt = '';
                        }

                        /* ===== HORAS: 'Sin registro' si NULL, vacío o '00:00:00' ===== */
                        $hActRaw  = $row->hora_actual;
                        $hProxRaw = $row->hora_proxima;
                        $hAct  = ($hActRaw  === null || $hActRaw  === '' || $hActRaw  === '00:00:00') ? 'Sin registro' : htmlspecialchars($hActRaw, ENT_QUOTES, 'UTF-8');
                        $hProx = ($hProxRaw === null || $hProxRaw === '' || $hProxRaw === '00:00:00') ? 'Sin registro' : htmlspecialchars($hProxRaw, ENT_QUOTES, 'UTF-8');

                        /* ===== KILOMETRAJES: 'Sin registro' si NULL/vacío; caso contrario con miles '.' ===== */
                        $kmA = ($row->kilometraje_actual  === null || $row->kilometraje_actual  === '')
                            ? 'Sin registro'
                            : number_format((float)$row->kilometraje_actual, 0, ',', '.');

                        $kmP = ($row->kilometraje_proximo === null || $row->kilometraje_proximo === '')
                            ? 'Sin registro'
                            : number_format((float)$row->kilometraje_proximo, 0, ',', '.');

                        /* ===== GASTO: miles '.' y 2 decimales con coma ===== */
                        $gasto = ($row->gasto_mantenimiento !== null && $row->gasto_mantenimiento !== '')
                            ? number_format((float)$row->gasto_mantenimiento, 2, ',', '.')
                            : '';

                    ?>
                        <tr>
                            <td><?= $mat ?></td>
                            <td><?= $tipoM ?></td>
                            <td><?= $fechaFmt ?></td>
                            <td><?= $hAct ?></td>
                            <td><?= $hProx ?></td>
                            <td class="text-right"><?= $kmA ?></td>
                            <td class="text-right"><?= $kmP ?></td>
                            <td class="text-right"><?= $gasto ?></td>
                            <td class="text-center">
                                <div class="acciones-boton">
                                    <!-- Ver detalle -->
                                    <a href="#" class="btn btn-info btn-sm" title="Ver detalle" data-toggle="modal" data-target="#modalVer<?= $idM ?>">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <!-- Eliminar -->
                                    <a href="mantenimiento.php?del=<?= $idM ?>" class="btn btn-danger btn-sm" title="Eliminar" onclick="advertenciaEliminar(event)">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <?php
                        /* ====== MODAL DETALLE ====== */
                        ob_start();
                        ?>
                        <div class="modal fade mantto-modal" id="modalVer<?= $idM ?>" tabindex="-1" aria-labelledby="modalVerLabel<?= $idM ?>" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header d-flex justify-content-between">
                                        <h5 class="modal-title w-100" id="modalVerLabel<?= $idM ?>">Detalle del mantenimiento (<?= $mat ?>)</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Tipo:</strong> <?= $tipoM ?></p>
                                                <p><strong>Fecha:</strong> <?= $fechaFmt ?></p>
                                                <p><strong>Hora actual:</strong> <?= $hAct ?></p>
                                                <p><strong>Hora próxima:</strong> <?= $hProx ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Km actual:</strong> <?= $kmA ?></p>
                                                <p><strong>Km próximo:</strong> <?= $kmP ?></p>
                                                <p><strong>Gasto:</strong> <?= $gasto ?></p>
                                            </div>
                                            <div class="col-12">
                                                <p><strong>Descripción:</strong><br><?= nl2br($desc) ?></p>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="mb-2"><i class="fa fa-screwdriver-wrench"></i> Repuestos utilizados</h6>
                                                <?php
                                                $qh = $conexion->prepare("
                                                    SELECT h.nombre, h.descripcion
                                                    FROM mantenimientos_herramientas mh
                                                    JOIN herramientas h ON h.id_herramientas = mh.id_herramientas
                                                    WHERE mh.id_mantenimiento = ?
                                                    ORDER BY h.nombre
                                                ");
                                                $qh->bind_param('i', $idM);
                                                $qh->execute();
                                                $rsh = $qh->get_result();
                                                if ($rsh->num_rows > 0) {
                                                    while ($h = $rsh->fetch_object()) {
                                                        echo "<p><strong>" . htmlspecialchars($h->nombre) . ":</strong> " . htmlspecialchars($h->descripcion) . "</p>";
                                                    }
                                                } else {
                                                    echo "<p>No se registraron herramientas para este mantenimiento.</p>";
                                                }
                                                ?>
                                            </div>

                                            <div class="col-md-6">
                                                <h6 class="mb-2"><i class="fa fa-box"></i> Suministros utilizados</h6>
                                                <?php
                                                $qs = $conexion->prepare("
                                                    SELECT s.nombre, s.descripcion
                                                    FROM mantenimientos_suministros ms
                                                    JOIN suministros s ON s.id_suministros = ms.id_suministros
                                                    WHERE ms.id_mantenimiento = ?
                                                    ORDER BY s.nombre
                                                ");
                                                $qs->bind_param('i', $idM);
                                                $qs->execute();
                                                $rss = $qs->get_result();
                                                if ($rss->num_rows > 0) {
                                                    while ($s = $rss->fetch_object()) {
                                                        echo "<p><strong>" . htmlspecialchars($s->nombre) . ":</strong> " . htmlspecialchars($s->descripcion) . "</p>";
                                                    }
                                                } else {
                                                    echo "<p>No se registraron suministros para este mantenimiento.</p>";
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <?php $qh->close();
                                        $qs->close(); ?>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                        $modales .= ob_get_clean();
                    endwhile;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modales fuera del flujo -->
<?= $modales ?>

<?php require('./layout/footer.php'); ?>

<!-- JS y plugins -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Normalizar búsqueda (acentos/ñ) para DataTables
    jQuery.extend(jQuery.fn.dataTable.ext.type.search, {
        string: function(data) {
            if (!data) return '';
            if (typeof data !== 'string') return data;
            return data.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/ñ/g, 'n').replace(/Ñ/g, 'n')
                .trim().toLowerCase();
        }
    });

    $(function() {
        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }
        $('#example').DataTable({
            columnDefs: [{
                orderable: false,
                targets: 8
            }],
            order: [], // respeta el ORDER BY del servidor
            autoWidth: false,
            language: {
                lengthMenu: "Mostrar _MENU_ registros por página",
                zeroRecords: "No se encontraron registros",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            }
        });

        // Por si algún contenedor aplica overflow/posicionamiento
        $('.mantto-modal').appendTo('body');
    });

    // Confirmación de eliminación
    function advertenciaEliminar(e) {
        e.preventDefault();
        const url = e.currentTarget.getAttribute('href');
        Swal.fire({
            title: '¿Eliminar mantenimiento?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>

<?php if (!empty($flash)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '<?= $flash['tipo'] ?>',
                title: '<?= $flash['titulo'] ?? 'Listo' ?>',
                text: '<?= $flash['mensaje'] ?>',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        });
    </script>
<?php endif; ?>