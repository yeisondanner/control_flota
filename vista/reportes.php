<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}

include "../modelo/conexion.php";
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    ul li:nth-child(12) .activo {
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

    .table thead th {
        background: #0b96d6 !important;
        color: #fff !important;
        border-color: #0b86c0 !important;
    }

    .report-section {
        display: none;
        margin-top: 25px;
    }

    #filtros {
        display: none;
    }

    .export-bar {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-bottom: 10px;
    }

    @media print {

        /* Ocultar controles en PDF */
        #filtros,
        .export-bar,
        .navbar,
        .sidebar,
        .page-header,
        .btn,
        .dataTables_filter,
        .dataTables_length,
        .dataTables_paginate,
        .dataTables_info {
            display: none !important;
        }

        .card-like {
            border: 0;
            box-shadow: none;
        }

        table {
            font-size: 12px;
        }
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">REPORTES DEL SISTEMA</h4>

    <div class="card-like">
        <form id="formReporte" method="GET" class="mb-3">
            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="tipo_reporte">Seleccionar tipo de reporte:</label>
                    <select id="tipo_reporte" name="tipo" class="form-control" required>
                        <option value="">-- Seleccione un reporte --</option>
                        <option value="vehiculos_unidad" <?= (($_GET['tipo'] ?? '') == 'vehiculos_unidad' ? 'selected' : '') ?>>Vehículos por Unidad Minera</option>
                        <option value="conductores_vehiculos" <?= (($_GET['tipo'] ?? '') == 'conductores_vehiculos' ? 'selected' : '') ?>>Conductores y Vehículos Asignados</option>
                        <option value="certificados_vigentes" <?= (($_GET['tipo'] ?? '') == 'certificados_vigentes' ? 'selected' : '') ?>>Certificados Vigentes</option>
                        <option value="certificados_proximos" <?= (($_GET['tipo'] ?? '') == 'certificados_proximos' ? 'selected' : '') ?>>Certificados Próximos a Vencer (30 días)</option>
                        <option value="mantenimientos_rango" <?= (($_GET['tipo'] ?? '') == 'mantenimientos_rango' ? 'selected' : '') ?>>Mantenimientos por Rango de Fechas</option>
                        <option value="kilometrajes_semana" <?= (($_GET['tipo'] ?? '') == 'kilometrajes_semana' ? 'selected' : '') ?>>Kilometrajes Registrados por Semana</option>
                    </select>
                </div>

                <!-- Filtros visibles solo para "mantenimientos_rango" -->
                <div id="filtros" class="form-group col-12 col-md-6">
                    <div class="form-row">
                        <div class="col-12 col-md-5">
                            <label for="desde">Desde</label>
                            <input type="date" name="desde" id="desde" class="form-control" value="<?= htmlspecialchars($_GET['desde'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="hasta">Hasta</label>
                            <input type="date" name="hasta" id="hasta" class="form-control" value="<?= htmlspecialchars($_GET['hasta'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">Filtrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <?php
        $tipo  = $_GET['tipo']  ?? '';
        $desde = $_GET['desde'] ?? '';
        $hasta = $_GET['hasta'] ?? '';

        // Utilitario para establecer rango por defecto si falta alguno
        function rango_defecto(&$desde, &$hasta)
        {
            if ($desde === '' && $hasta === '') {
                $desde = date('Y-m-01');
                $hasta = date('Y-m-t');
            } elseif ($desde === '' && $hasta !== '') {
                // 30 días antes del hasta
                $desde = date('Y-m-d', strtotime($hasta . ' -30 days'));
            } elseif ($desde !== '' && $hasta === '') {
                // 30 días después del desde
                $hasta = date('Y-m-d', strtotime($desde . ' +30 days'));
            }
        }
        ?>

        <!-- ===================== Reporte: Vehículos por Unidad ===================== -->
        <?php if ($tipo == 'vehiculos_unidad'): ?>
            <div class="report-section" style="display:block;">
                <div class="export-bar">
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportToCSV('vehiculos_por_unidad.csv');return false;">Exportar Excel (CSV)</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="window.print();return false;">Exportar PDF</button>
                </div>
                <h5 class="text-primary">Vehículos por Unidad Minera</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaReporte">
                        <thead>
                            <tr>
                                <th>Unidad Minera</th>
                                <th>Matrícula</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Año</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conexion->query("
                SELECT u.nombre_unidad, v.matricula, v.marca, v.modelo, v.year, v.tipo
                FROM vehiculos v
                INNER JOIN unidad_minera u ON u.id_unidadminera = v.id_unidadminera
                ORDER BY u.nombre_unidad, v.marca
              ");
                            while ($r = $res->fetch_object()):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($r->nombre_unidad) ?></td>
                                    <td><?= htmlspecialchars($r->matricula) ?></td>
                                    <td><?= htmlspecialchars($r->marca) ?></td>
                                    <td><?= htmlspecialchars($r->modelo) ?></td>
                                    <td><?= htmlspecialchars($r->year) ?></td>
                                    <td><?= htmlspecialchars($r->tipo) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============ Reporte: Conductores y Vehículos Asignados ============ -->
        <?php if ($tipo == 'conductores_vehiculos'): ?>
            <div class="report-section" style="display:block;">
                <div class="export-bar">
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportToCSV('conductores_vehiculos.csv');return false;">Exportar Excel (CSV)</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="window.print();return false;">Exportar PDF</button>
                </div>
                <h5 class="text-primary">Conductores y Vehículos Asignados</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaReporte">
                        <thead>
                            <tr>
                                <th>Conductor</th>
                                <th>DNI</th>
                                <th>Vehículo</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Fecha Asignación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conexion->query("
                SELECT p.nombres, p.apellidos, p.dni, v.matricula, v.marca, v.modelo, cv.fecha_registro
                FROM conductor_vehiculo cv
                JOIN conductor c ON c.id_conductor = cv.id_conductor
                JOIN usuario u   ON u.id_usuario = c.id_usuario
                JOIN persona p   ON p.id_persona = u.id_persona
                JOIN vehiculos v ON v.id_vehiculo = cv.id_vehiculo
                ORDER BY p.apellidos, p.nombres
              ");
                            while ($r = $res->fetch_object()):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($r->nombres . ' ' . $r->apellidos) ?></td>
                                    <td><?= htmlspecialchars($r->dni) ?></td>
                                    <td><?= htmlspecialchars($r->matricula) ?></td>
                                    <td><?= htmlspecialchars($r->marca) ?></td>
                                    <td><?= htmlspecialchars($r->modelo) ?></td>
                                    <td><?= htmlspecialchars($r->fecha_registro) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ==================== Reporte: Certificados Vigentes ==================== -->
        <?php if ($tipo == 'certificados_vigentes'): ?>
            <div class="report-section" style="display:block;">
                <div class="export-bar">
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportToCSV('certificados_vigentes.csv');return false;">Exportar Excel (CSV)</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="window.print();return false;">Exportar PDF</button>
                </div>
                <h5 class="text-primary">Certificados Vigentes</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaReporte">
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Tipo de Certificado</th>
                                <th>Fecha Emisión</th>
                                <th>Fecha Vencimiento</th>
                                <th>Días Restantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conexion->query("
                SELECT v.matricula, c.tipo_certificado, c.fecha_emision, c.fecha_vencimiento,
                       DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias_restantes
                FROM certificados c
                JOIN vehiculos v ON v.id_vehiculo = c.id_vehiculo
                WHERE c.fecha_vencimiento >= CURDATE()
                ORDER BY c.fecha_vencimiento ASC
              ");
                            while ($r = $res->fetch_object()):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($r->matricula) ?></td>
                                    <td><?= htmlspecialchars($r->tipo_certificado) ?></td>
                                    <td><?= htmlspecialchars($r->fecha_emision) ?></td>
                                    <td><?= htmlspecialchars($r->fecha_vencimiento) ?></td>
                                    <td><?= (int)$r->dias_restantes ?> días</td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ========= Reporte: Certificados Próximos a Vencer (30 días) ========= -->
        <?php if ($tipo == 'certificados_proximos'): ?>
            <div class="report-section" style="display:block;">
                <div class="export-bar">
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportToCSV('certificados_proximos.csv');return false;">Exportar Excel (CSV)</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="window.print();return false;">Exportar PDF</button>
                </div>
                <h5 class="text-primary">Certificados Próximos a Vencer (en 30 días)</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaReporte">
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Tipo Certificado</th>
                                <th>Fecha Emisión</th>
                                <th>Fecha Vencimiento</th>
                                <th>Días Restantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $res = $conexion->query("
                SELECT v.matricula, c.tipo_certificado, c.fecha_emision, c.fecha_vencimiento,
                       DATEDIFF(c.fecha_vencimiento, CURDATE()) AS dias_restantes
                FROM certificados c
                JOIN vehiculos v ON v.id_vehiculo = c.id_vehiculo
                WHERE c.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                ORDER BY c.fecha_vencimiento
              ");
                            while ($r = $res->fetch_object()):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($r->matricula) ?></td>
                                    <td><?= htmlspecialchars($r->tipo_certificado) ?></td>
                                    <td><?= htmlspecialchars($r->fecha_emision) ?></td>
                                    <td><?= htmlspecialchars($r->fecha_vencimiento) ?></td>
                                    <td><?= (int)$r->dias_restantes ?> días</td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============== Reporte: Mantenimientos por Rango de Fechas ============== -->
        <?php if ($tipo == 'mantenimientos_rango'):
            rango_defecto($desde, $hasta);
            // Consulta con rango
            $stmt = $conexion->prepare("
        SELECT v.matricula, v.marca, v.modelo, m.tipo AS tipo_mantenimiento,
               m.fecha, m.kilometraje_actual, m.kilometraje_proximo, m.gasto_mantenimiento
        FROM mantenimientos m
        JOIN vehiculos v ON v.id_vehiculo = m.id_vehiculo
        WHERE m.fecha BETWEEN ? AND ?
        ORDER BY m.fecha DESC
      ");
            $stmt->bind_param('ss', $desde, $hasta);
            $stmt->execute();
            $res = $stmt->get_result();
        ?>
            <div class="report-section" style="display:block;">
                <div class="export-bar">
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportToCSV('mantenimientos_<?= htmlspecialchars($desde) ?>_<?= htmlspecialchars($hasta) ?>.csv');return false;">Exportar Excel (CSV)</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="window.print();return false;">Exportar PDF</button>
                </div>
                <h5 class="text-primary">Mantenimientos del <?= htmlspecialchars($desde) ?> al <?= htmlspecialchars($hasta) ?></h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaReporte">
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Tipo Mantenimiento</th>
                                <th>Fecha</th>
                                <th>Km Actual</th>
                                <th>Km Próximo</th>
                                <th>Gasto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = $res->fetch_object()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r->matricula) ?></td>
                                    <td><?= htmlspecialchars($r->marca) ?></td>
                                    <td><?= htmlspecialchars($r->modelo) ?></td>
                                    <td><?= htmlspecialchars($r->tipo_mantenimiento) ?></td>
                                    <td><?= htmlspecialchars($r->fecha) ?></td>
                                    <td><?= htmlspecialchars($r->kilometraje_actual) ?></td>
                                    <td><?= htmlspecialchars($r->kilometraje_proximo) ?></td>
                                    <td><?= htmlspecialchars($r->gasto_mantenimiento) ?></td>
                                </tr>
                            <?php endwhile;
                            $stmt->close(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- =========== Reporte: Kilometrajes registrados por semana =========== -->
        <?php if ($tipo == 'kilometrajes_semana'): ?>
            <div class="report-section" style="display:block;">
                <div class="export-bar">
                    <button class="btn btn-outline-secondary btn-sm" onclick="exportToCSV('kilometrajes_semana.csv');return false;">Exportar Excel (CSV)</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="window.print();return false;">Exportar PDF</button>
                </div>
                <h5 class="text-primary">Kilometrajes Registrados por Semana</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaReporte">
                        <thead>
                            <tr>
                                <th>Conductor</th>
                                <th>DNI</th>
                                <th>Vehículo</th>
                                <th>Semana Inicio</th>
                                <th>Semana Fin</th>
                                <th>Kilometraje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ajusta los nombres de columnas según tu tabla real (semana_inicio/semana_fin/kilometraje)
                            $res = $conexion->query("
                SELECT p.nombres, p.apellidos, p.dni, v.matricula,
                       ks.semana_inicio, ks.semana_fin, ks.kilometraje
                FROM kilometraje_semanal ks
                JOIN conductor c ON c.id_conductor = ks.id_conductor
                JOIN usuario u   ON u.id_usuario   = c.id_usuario
                JOIN persona p   ON p.id_persona   = u.id_persona
                JOIN vehiculos v ON v.id_vehiculo  = ks.id_vehiculo
                ORDER BY ks.semana_inicio DESC, p.apellidos
              ");
                            while ($r = $res->fetch_object()):
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($r->nombres . ' ' . $r->apellidos) ?></td>
                                    <td><?= htmlspecialchars($r->dni) ?></td>
                                    <td><?= htmlspecialchars($r->matricula) ?></td>
                                    <td><?= htmlspecialchars($r->semana_inicio) ?></td>
                                    <td><?= htmlspecialchars($r->semana_fin) ?></td>
                                    <td><?= htmlspecialchars($r->kilometraje) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require('./layout/footer.php'); ?>

<!-- JS y plugins -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Mostrar/ocultar filtros por tipo
    (function() {
        function toggleFiltros() {
            const tipo = document.getElementById('tipo_reporte').value;
            const filtros = document.getElementById('filtros');
            filtros.style.display = (tipo === 'mantenimientos_rango') ? 'block' : 'none';
        }
        document.getElementById('tipo_reporte').addEventListener('change', function() {
            const tipo = this.value;
            if (tipo) {
                // Navega para renderizar el reporte
                if (tipo === 'mantenimientos_rango') {
                    // Mantén parámetros actuales de fechas si existen
                    const desde = document.getElementById('desde')?.value || '';
                    const hasta = document.getElementById('hasta')?.value || '';
                    const params = new URLSearchParams({
                        tipo
                    });
                    if (desde) params.set('desde', desde);
                    if (hasta) params.set('hasta', hasta);
                    window.location = 'reportes.php?' + params.toString();
                } else {
                    window.location = 'reportes.php?tipo=' + tipo;
                }
            }
        });
        toggleFiltros();
    })();

    // DataTables básico
    $(function() {
        if ($('#tablaReporte').length) {
            $('#tablaReporte').DataTable({
                order: [],
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
        }
    });

    // Exportar a CSV (abre en Excel)
    function exportToCSV(filename) {
        const table = document.getElementById('tablaReporte');
        if (!table) {
            return;
        }
        let csv = [];
        for (let i = 0; i < table.rows.length; i++) {
            let row = [],
                cols = table.rows[i].querySelectorAll('th, td');
            for (let j = 0; j < cols.length; j++) {
                // Escapar comas y comillas
                let data = cols[j].innerText.replace(/\n/g, ' ').replace(/\s\s+/g, ' ').trim();
                data = '"' + data.replace(/"/g, '""') + '"';
                row.push(data);
            }
            csv.push(row.join(','));
        }
        const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
        const link = document.createElement('a');
        link.setAttribute('href', encodeURI(csvContent));
        link.setAttribute('download', filename || 'reporte.csv');
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>