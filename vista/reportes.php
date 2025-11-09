<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}
include "../modelo/conexion.php";
date_default_timezone_set('America/Lima');

/* ==== Captura (para tus otras pestañas de ejemplo si quisieras) ==== */
$fd_raw = isset($_GET['fd']) ? trim($_GET['fd']) : '';
$fh_raw = isset($_GET['fh']) ? trim($_GET['fh']) : '';

$fd = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fd_raw)) ? $fd_raw : '';
$fh = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fh_raw)) ? $fh_raw : '';

/* ==== WHERE de ejemplo (idéntico a tu referencia para Cursos/Exámenes) ==== */
$whereCursos = '';
if ($fd && $fh)       $whereCursos = "WHERE rcp.fecha_resultado BETWEEN '$fd' AND '$fh'";
elseif ($fd)          $whereCursos = "WHERE rcp.fecha_resultado >= '$fd'";
elseif ($fh)          $whereCursos = "WHERE rcp.fecha_resultado <= '$fh'";

$whereExamenes = '';
if ($fd && $fh)       $whereExamenes = "WHERE remp.fecha_resultado BETWEEN '$fd' AND '$fh'";
elseif ($fd)          $whereExamenes = "WHERE remp.fecha_resultado >= '$fd'";
elseif ($fh)          $whereExamenes = "WHERE remp.fecha_resultado <= '$fh'";
?>
<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Marca el item del menú lateral (ajusta el índice según tu sidebar) */
    ul li:nth-child(12) .activo {
        background: #0b96d6 !important;
    }

    table {
        table-layout: fixed;
        width: 100%;
    }

    .dataTables_filter {
        margin-bottom: 15px;
    }

    .tabs-wrap {
        position: sticky;
        top: 0;
        z-index: 9;
        background: #fff;
        padding: 6px 0;
        border-bottom: 1px solid #eef2f7;
        margin-top: .25rem;
    }

    .tabs-scroller {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }

    .tabs-scroller::-webkit-scrollbar {
        display: none;
    }

    .nav-tabs.tabs-modern {
        border-bottom: none;
        white-space: nowrap;
        display: inline-flex;
        gap: 8px;
        padding: 4px 2px;
    }

    .nav-tabs.tabs-modern .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none !important;
        border-radius: 12px;
        padding: 10px 14px;
        color: #6b7280;
        background: #f8fafc;
        font-weight: 600;
        transition: all .2s ease;
    }

    .nav-tabs.tabs-modern .nav-link.active {
        color: #0b96d6;
        background: #e6f6fd;
        box-shadow: 0 6px 18px rgba(11, 150, 214, .15);
    }

    h4.text-secondary {
        color: #374151 !important;
        font-weight: 800;
        letter-spacing: .3px;
    }

    .tab-content.modern {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
        margin-top: 14px;
    }

    .table thead th,
    .thead-dark th {
        background: #0b96d6 !important;
        color: #fff !important;
        border-color: #0b86c0 !important;
    }

    .filters-bar {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
        margin: 10px 0 14px;
        display: flex;
        gap: 10px;
        align-items: end;
        flex-wrap: wrap;
    }

    .filters-bar label {
        font-weight: 600;
        margin-bottom: 4px;
    }

    /* Colores EXACTOS de los botones DataTables */
    .dt-button {
        border: none !important;
        border-radius: 6px !important;
        padding: 6px 12px !important;
        font-size: 13px !important;
        margin-right: 6px !important;
        color: #fff !important;
    }

    .dt-button.buttons-excel {
        background-color: #28a745 !important;
    }

    /* Verde */
    .dt-button.buttons-pdf {
        background-color: #dc3545 !important;
    }

    /* Rojo  */

    @media print {

        .navbar,
        .sidebar,
        .tabs-wrap,
        .filters-bar,
        .btn,
        .dataTables_filter,
        .dataTables_length,
        .dataTables_paginate,
        .dataTables_info {
            display: none !important;
        }

        .tab-content.modern {
            border: 0;
            box-shadow: none;
        }

        table {
            font-size: 12px;
        }
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">REPORTES</h4>

    <!-- Barra de filtros (SIEMPRE visible) -->
    <form id="filters-bar" class="filters-bar" onsubmit="return false;">
        <div>
            <label for="fd">Desde</label>
            <input type="date" id="fd" name="fd" class="form-control">
        </div>
        <div>
            <label for="fh">Hasta</label>
            <input type="date" id="fh" name="fh" class="form-control">
        </div>
        <div>
            <label for="tm">Tipo mant.</label>
            <select id="tm" name="tm" class="form-control">
                <option value="">Todos</option>
                <option value="Preventivo">Preventivo</option>
                <option value="Correctivo">Correctivo</option>
            </select>
        </div>
        <div>
            <button type="button" id="btnFiltrar" class="btn btn-primary">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>
            <button type="button" id="btnLimpiar" class="btn btn-secondary">
                <i class="fa-solid fa-rotate-left"></i> Limpiar
            </button>
        </div>
        <small class="text-muted" style="display:block;width:100%">
            * El rango y tipo aplican a la pestaña <b>Mantenimientos</b> (filtrado en cliente como tu ejemplo).
        </small>
    </form>

    <!-- Tabs -->
    <div class="tabs-wrap">
        <div class="tabs-scroller">
            <ul class="nav nav-tabs tabs-modern" id="reportTabs" role="tablist">
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#vehiculos_unidad" role="tab"><i class="fa-solid fa-truck"></i> Vehículos por Unidad</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#conductores_vehiculos" role="tab"><i class="fa-solid fa-id-card"></i> Conductores & Vehículos</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#mantenimientos" role="tab"><i class="fa-solid fa-wrench"></i> Mantenimientos</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#certificados_vig" role="tab"><i class="fa-solid fa-certificate"></i> Certificados Vigentes</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#certificados_prox" role="tab"><i class="fa-solid fa-hourglass-half"></i> Certificados Próximos</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#kilometrajes" role="tab"><i class="fa-solid fa-road"></i> Kilometrajes por Semana</a></li>
            </ul>
        </div>
    </div>

    <div class="tab-content modern" id="reportTabsContent">
        <!-- Vehículos por Unidad -->
        <div class="tab-pane fade" id="vehiculos_unidad" role="tabpanel">
            <table class="table table-bordered table-hover" id="tabla_vehiculos_unidad">
                <thead class="thead-dark">
                    <tr>
                        <th>Unidad Minera</th>
                        <th>Matrícula</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Año</th>
                        <th>Tipo</th>
                        <th>Kilometraje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "
          SELECT u.nombre_unidad, v.matricula, v.marca, v.modelo, v.year, v.tipo, v.kilometraje
          FROM vehiculos v
          LEFT JOIN unidad_minera u ON u.id_unidadminera = v.id_unidadminera
          ORDER BY u.nombre_unidad, v.marca, v.modelo
        ";
                    $res = $conexion->query($sql);
                    while ($row = $res->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombre_unidad']) ?></td>
                            <td><?= htmlspecialchars($row['matricula']) ?></td>
                            <td><?= htmlspecialchars($row['marca']) ?></td>
                            <td><?= htmlspecialchars($row['modelo']) ?></td>
                            <td><?= htmlspecialchars($row['year']) ?></td>
                            <td><?= htmlspecialchars($row['tipo']) ?></td>
                            <td><?= htmlspecialchars($row['kilometraje']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Conductores & Vehículos -->
        <div class="tab-pane fade" id="conductores_vehiculos" role="tabpanel">
            <table class="table table-bordered table-hover" id="tabla_conductores_vehiculos">
                <thead class="thead-dark">
                    <tr>
                        <th>Conductor</th>
                        <th>DNI</th>
                        <th>Licencia</th>
                        <th>Vehículo</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Fecha Asignación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "
          SELECT
            p.nombres, p.apellidos, p.dni,
            c.categoria_licencia,
            v.matricula, v.marca, v.modelo,
            cv.fecha_registro
          FROM conductor_vehiculo cv
          JOIN conductor c ON c.id_conductor = cv.id_conductor
          JOIN usuario u ON u.id_usuario = c.id_usuario
          JOIN persona p ON p.id_persona = u.id_persona
          JOIN vehiculos v ON v.id_vehiculo = cv.id_vehiculo
          ORDER BY p.apellidos, p.nombres, cv.fecha_registro DESC
        ";
                    $res = $conexion->query($sql);
                    while ($row = $res->fetch_assoc()):
                        $rawFecha = $row['fecha_registro'];
                        $dFecha = ($rawFecha && $rawFecha !== '0000-00-00') ? date('d/m/Y', strtotime($rawFecha)) : '';
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombres'] . ' ' . $row['apellidos']) ?></td>
                            <td><?= htmlspecialchars($row['dni']) ?></td>
                            <td><?= htmlspecialchars($row['categoria_licencia']) ?></td>
                            <td><?= htmlspecialchars($row['matricula']) ?></td>
                            <td><?= htmlspecialchars($row['marca']) ?></td>
                            <td><?= htmlspecialchars($row['modelo']) ?></td>
                            <td data-order="<?= htmlspecialchars($rawFecha) ?>"><?= htmlspecialchars($dFecha) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Mantenimientos (SIN filtros por GET; todo se filtra en cliente) -->
        <div class="tab-pane fade" id="mantenimientos" role="tabpanel">
            <table class="table table-bordered table-hover" id="tabla_mantenimientos">
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Vehículo</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Tipo</th>
                        <th>Km Actual</th>
                        <th>Km Próximo</th>
                        <th>Hora Actual</th>
                        <th>Hora Próxima</th>
                        <th>Gasto</th>
                        <th>Repuestos</th>
                        <th>Suministros</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "
          SELECT
            m.fecha, v.matricula, v.marca, v.modelo, m.tipo,
            m.kilometraje_actual, m.kilometraje_proximo,
            m.hora_actual, m.hora_proxima, m.gasto_mantenimiento,
            COALESCE(GROUP_CONCAT(DISTINCT h.nombre ORDER BY h.nombre SEPARATOR ', '), '-') AS herramientas,
            COALESCE(GROUP_CONCAT(DISTINCT s.nombre ORDER BY s.nombre SEPARATOR ', '), '-') AS suministros
          FROM mantenimientos m
          JOIN vehiculos v ON v.id_vehiculo = m.id_vehiculo
          LEFT JOIN mantenimientos_herramientas mh ON mh.id_mantenimiento = m.id_mantenimiento
          LEFT JOIN herramientas h ON h.id_herramientas = mh.id_herramientas
          LEFT JOIN mantenimientos_suministros ms ON ms.id_mantenimiento = m.id_mantenimiento
          LEFT JOIN suministros s ON s.id_suministros = ms.id_suministros
          GROUP BY m.id_mantenimiento, m.fecha, v.matricula, v.marca, v.modelo, m.tipo,
                   m.kilometraje_actual, m.kilometraje_proximo, m.hora_actual, m.hora_proxima, m.gasto_mantenimiento
          ORDER BY m.fecha DESC, v.matricula
        ";
                    $res = $conexion->query($sql);
                    while ($row = $res->fetch_assoc()):
                        $rawFecha = $row['fecha'];
                        $dFecha = ($rawFecha && $rawFecha !== '0000-00-00') ? date('d/m/Y', strtotime($rawFecha)) : '';
                    ?>
                        <tr>
                            <td data-order="<?= htmlspecialchars($rawFecha) ?>"><?= htmlspecialchars($dFecha) ?></td>
                            <td><?= htmlspecialchars($row['matricula']) ?></td>
                            <td><?= htmlspecialchars($row['marca']) ?></td>
                            <td><?= htmlspecialchars($row['modelo']) ?></td>
                            <td><?= htmlspecialchars($row['tipo']) ?></td>
                            <td><?= htmlspecialchars($row['kilometraje_actual']) ?></td>
                            <td><?= htmlspecialchars($row['kilometraje_proximo']) ?></td>
                            <td><?= htmlspecialchars($row['hora_actual']) ?></td>
                            <td><?= htmlspecialchars($row['hora_proxima']) ?></td>
                            <td><?= htmlspecialchars($row['gasto_mantenimiento']) ?></td>
                            <td><?= htmlspecialchars($row['herramientas']) ?></td>
                            <td><?= htmlspecialchars($row['suministros']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Certificados Vigentes -->
        <div class="tab-pane fade" id="certificados_vig" role="tabpanel">
            <table class="table table-bordered table-hover" id="tabla_cert_vig">
                <thead class="thead-dark">
                    <tr>
                        <th>Vehículo</th>
                        <th>Tipo</th>
                        <th>Emisión</th>
                        <th>Vencimiento</th>
                        <th>Días Restantes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "
          SELECT v.matricula, c.tipo_certificado, c.fecha_emision, c.fecha_vencimiento,
                 DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias_restantes
          FROM certificados c
          JOIN vehiculos v ON v.id_vehiculo = c.id_vehiculo
          WHERE c.fecha_vencimiento >= CURDATE()
          ORDER BY c.fecha_vencimiento ASC
        ";
                    $res = $conexion->query($sql);
                    while ($row = $res->fetch_assoc()):
                        $fe = $row['fecha_emision'];
                        $de = ($fe && $fe !== '0000-00-00') ? date('d/m/Y', strtotime($fe)) : '';
                        $fv = $row['fecha_vencimiento'];
                        $dv = ($fv && $fv !== '0000-00-00') ? date('d/m/Y', strtotime($fv)) : '';
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['matricula']) ?></td>
                            <td><?= htmlspecialchars($row['tipo_certificado']) ?></td>
                            <td data-order="<?= htmlspecialchars($fe) ?>"><?= htmlspecialchars($de) ?></td>
                            <td data-order="<?= htmlspecialchars($fv) ?>"><?= htmlspecialchars($dv) ?></td>
                            <td><?= (int)$row['dias_restantes'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Certificados Próximos (30 días) -->
        <div class="tab-pane fade" id="certificados_prox" role="tabpanel">
            <table class="table table-bordered table-hover" id="tabla_cert_prox">
                <thead class="thead-dark">
                    <tr>
                        <th>Vehículo</th>
                        <th>Tipo</th>
                        <th>Emisión</th>
                        <th>Vencimiento</th>
                        <th>Días Restantes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "
          SELECT v.matricula, c.tipo_certificado, c.fecha_emision, c.fecha_vencimiento,
                 DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias_restantes
          FROM certificados c
          JOIN vehiculos v ON v.id_vehiculo = c.id_vehiculo
          WHERE c.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
          ORDER BY c.fecha_vencimiento
        ";
                    $res = $conexion->query($sql);
                    while ($row = $res->fetch_assoc()):
                        $fe = $row['fecha_emision'];
                        $de = ($fe && $fe !== '0000-00-00') ? date('d/m/Y', strtotime($fe)) : '';
                        $fv = $row['fecha_vencimiento'];
                        $dv = ($fv && $fv !== '0000-00-00') ? date('d/m/Y', strtotime($fv)) : '';
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['matricula']) ?></td>
                            <td><?= htmlspecialchars($row['tipo_certificado']) ?></td>
                            <td data-order="<?= htmlspecialchars($fe) ?>"><?= htmlspecialchars($de) ?></td>
                            <td data-order="<?= htmlspecialchars($fv) ?>"><?= htmlspecialchars($dv) ?></td>
                            <td><?= (int)$row['dias_restantes'] ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Kilometrajes por Semana -->
        <div class="tab-pane fade" id="kilometrajes" role="tabpanel">
            <table class="table table-bordered table-hover" id="tabla_kilometrajes">
                <thead class="thead-dark">
                    <tr>
                        <th>Semana (ISO)</th>
                        <th>Conductor</th>
                        <th>DNI</th>
                        <th>Vehículo</th>
                        <th>Unidad Minera</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Kilometraje Total</th>
                        <th>Horas Totales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "
          SELECT
            YEARWEEK(ks.fecha_registro, 3) AS semana_iso,
            MIN(DATE(ks.fecha_registro)) AS sem_ini,
            MAX(DATE(ks.fecha_registro)) AS sem_fin,
            COALESCE(SUM(ks.kilometraje), 0) AS km_total,
            SEC_TO_TIME(SUM(CASE WHEN ks.horas IS NULL THEN 0 ELSE TIME_TO_SEC(ks.horas) END)) AS horas_total,
            p.nombres, p.apellidos, p.dni,
            v.matricula,
            um.nombre_unidad
          FROM kilometraje_semanal ks
          JOIN conductor c ON c.id_conductor = ks.id_conductor
          JOIN usuario u ON u.id_usuario = c.id_usuario
          JOIN persona p ON p.id_persona = u.id_persona
          JOIN vehiculos v ON v.id_vehiculo = ks.id_vehiculo
          LEFT JOIN unidad_minera um ON um.id_unidadminera = v.id_unidadminera
          GROUP BY semana_iso, p.nombres, p.apellidos, p.dni, v.matricula, um.nombre_unidad
          ORDER BY semana_iso DESC, p.apellidos
        ";
                    $res = $conexion->query($sql);
                    while ($row = $res->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['semana_iso']) ?></td>
                            <td><?= htmlspecialchars($row['nombres'] . ' ' . $row['apellidos']) ?></td>
                            <td><?= htmlspecialchars($row['dni']) ?></td>
                            <td><?= htmlspecialchars($row['matricula']) ?></td>
                            <td><?= htmlspecialchars($row['nombre_unidad']) ?></td>
                            <td><?= htmlspecialchars($row['sem_ini']) ?></td>
                            <td><?= htmlspecialchars($row['sem_fin']) ?></td>
                            <td><?= htmlspecialchars($row['km_total']) ?></td>
                            <td><?= htmlspecialchars($row['horas_total']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /tab-content -->
</div><!-- /page-content -->

<?php require('./layout/footer.php'); ?>

<!-- Scripts base -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables + Buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    /* Persistir pestaña activa por hash / localStorage */
    (function() {
        const KEY = 'reportes.activeTab';

        function showTab(sel) {
            const $el = $('#reportTabs a[href="' + sel + '"]');
            if ($el.length) {
                $el.tab('show');
                return true;
            }
            return false;
        }
        const hash = window.location.hash;
        if (!(hash && showTab(hash))) {
            const saved = localStorage.getItem(KEY);
            if (saved) showTab(saved);
        }
        $('#reportTabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).attr('href');
            localStorage.setItem(KEY, target);
            if (history.replaceState) history.replaceState(null, null, target);
            else window.location.hash = target;
        });
    })();

    /* Normalizar búsquedas (acentos/ñ) */
    jQuery.extend(jQuery.fn.dataTable.ext.type.search, {
        string: function(data) {
            if (!data) return '';
            if (typeof data !== 'string') return data;
            return data.normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                .replace(/ñ/g, "n").replace(/Ñ/g, "n").trim().toLowerCase();
        }
    });

    /* Config base DataTables (con clases buttons-excel/pdf para los colores) */
    const configBase = {
        dom: 'Bfrtip',
        buttons: [{
                extend: 'excelHtml5',
                text: '<i class="fa-solid fa-file-excel"></i> Excel'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa-solid fa-file-pdf"></i> PDF'
            }
        ],
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
    };

    /* Inicializar DataTables */
    let tablaVehiculos, tablaConductores, tablaMant, tablaVig, tablaProx, tablaKms;

    $(function() {
        tablaVehiculos = $('#tabla_vehiculos_unidad').DataTable($.extend(true, {}, configBase));
        tablaConductores = $('#tabla_conductores_vehiculos').DataTable($.extend(true, {}, configBase));
        tablaMant = $('#tabla_mantenimientos').DataTable($.extend(true, {}, configBase, {
            order: [
                [0, 'desc']
            ]
        }));
        tablaVig = $('#tabla_cert_vig').DataTable($.extend(true, {}, configBase, {
            order: [
                [3, 'asc']
            ]
        }));
        tablaProx = $('#tabla_cert_prox').DataTable($.extend(true, {}, configBase, {
            order: [
                [3, 'asc']
            ]
        }));
        tablaKms = $('#tabla_kilometrajes').DataTable($.extend(true, {}, configBase, {
            order: [
                [0, 'desc']
            ]
        }));
    });

    /* ========= FILTRADO CLIENTE SOLO PARA MANTENIMIENTOS (como tu ejemplo) ========= */

    /* Parseo robusto de fechas: acepta yyyy-mm-dd y dd/mm/yyyy */
    function parseDateStringToDate(dstr) {
        if (!dstr) return null;
        dstr = ('' + dstr).trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(dstr)) { // yyyy-mm-dd
            const p = dstr.split('-');
            return new Date(+p[0], +p[1] - 1, +p[2]);
        }
        if (/^\d{2}\/\d{2}\/\d{4}$/.test(dstr)) { // dd/mm/yyyy (en la tabla mostramos así)
            const p = dstr.split('/');
            return new Date(+p[2], +p[1] - 1, +p[0]);
        }
        const dt = new Date(dstr);
        return isNaN(dt.getTime()) ? null : dt;
    }

    /* Filtro global DataTables: se aplica SOLO cuando la tabla evaluada es la de mantenimientos */
    $.fn.dataTable.ext.search.push(function(settings, data) {
        if (!tablaMant || settings.nTable.getAttribute('id') !== 'tabla_mantenimientos') return true;

        const fd = $('#fd').val();
        const fh = $('#fh').val();
        const tm = $('#tm').val(); // Preventivo / Correctivo / ''

        // Columna 0 = Fecha (formato dd/mm/yyyy en pantalla)
        const cellDateStr = data[0] || '';
        const cellDate = parseDateStringToDate(cellDateStr);
        if (fd) {
            const minD = parseDateStringToDate(fd);
            if (!cellDate || !minD || cellDate < new Date(minD.getFullYear(), minD.getMonth(), minD.getDate())) return false;
        }
        if (fh) {
            const maxD = parseDateStringToDate(fh);
            if (!cellDate || !maxD || cellDate > new Date(maxD.getFullYear(), maxD.getMonth(), maxD.getDate())) return false;
        }

        // Columna 4 = Tipo (Preventivo/Correctivo)
        const cellTipo = (data[4] || '').toLowerCase().trim();
        if (tm) {
            if (cellTipo !== tm.toLowerCase()) return false;
        }
        return true;
    });

    /* Botón Filtrar: redibuja SOLO la tabla de mantenimientos (no desaparece nada) */
    $('#btnFiltrar').on('click', function() {
        // Asegura quedarse en la pestaña actual; si no hay, forzar mantenimientos
        const activeHref = $('#reportTabs .nav-link.active').attr('href') || '#mantenimientos';
        if (window.location.hash !== activeHref) {
            if (history.replaceState) history.replaceState(null, null, activeHref);
            else window.location.hash = activeHref;
        }
        if (tablaMant) {
            tablaMant.draw();
        }
    });

    /* Botón Limpiar: borra campos y redibuja mantenimientos */
    $('#btnLimpiar').on('click', function() {
        $('#fd').val('');
        $('#fh').val('');
        $('#tm').val('');
        const activeHref = $('#reportTabs .nav-link.active').attr('href') || '#mantenimientos';
        if (window.location.hash !== activeHref) {
            if (history.replaceState) history.replaceState(null, null, activeHref);
            else window.location.hash = activeHref;
        }
        if (tablaMant) {
            tablaMant.search('').columns().search('');
            tablaMant.draw();
        }
    });

    /* Aplicar inmediatamente si cambian inputs (opcional, consistente con ejemplo) */
    $('#fd, #fh, #tm').on('change', function() {
        if (tablaMant) {
            tablaMant.draw();
        }
    });
</script>