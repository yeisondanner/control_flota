<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}

// Validar acceso de conductor: solo pueden ver kilometrajes
include "../modelo/validar_conductor.php";

include "../modelo/conexion.php";

/* Controladores ANTES del HTML (para permitir header Location/redirecciones) */
include "../controlador/controlador_registrar_certificado.php";
include "../controlador/controlador_eliminar_certificado.php";

/* Flash message */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* Consulta de vehículos (matrícula, tipo) */
$vehiculos = $conexion->query("
  SELECT v.id_vehiculo, v.matricula, v.tipo
  FROM vehiculos v
  ORDER BY v.id_vehiculo DESC
");
?>
<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo (ajusta índice según tu sidebar) */
    ul li:nth-child(9) .activo {
        background: #0b96d6 !important;
    }

    /* Titular */
    h4.text-secondary {
        color: #374151 !important;
        font-weight: 800;
        letter-spacing: .3px;
    }

    /* Card contenedor */
    .card-like {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, .04);
        margin-top: 14px;
    }

    /* Tabla / DataTables */
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

    /* Encabezado azul */
    .table thead th,
    .thead-dark th,
    .modal .table thead th {
        background: #0b96d6 !important;
        color: #fff !important;
        border-color: #0b86c0 !important;
    }

    /* Botonera de acciones */
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

    /* Columna ACCIONES */
    th:nth-child(3),
    td:nth-child(3) {
        width: 220px;
        white-space: nowrap;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">CERTIFICADOS DE VEHÍCULOS</h4>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" referrerpolicy="no-referrer" />

    <div class="card-like">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="example">
                <thead>
                    <tr>
                        <th>MATRÍCULA</th>
                        <th>TIPO DE VEHÍCULO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Acumulador de modales para pintarlos fuera de la tabla */
                    $modales = '';

                    while ($v = $vehiculos->fetch_object()):
                        $idVehiculo = (int)$v->id_vehiculo;
                        $matricula  = htmlspecialchars($v->matricula ?? '', ENT_QUOTES, 'UTF-8');
                        $tipo       = htmlspecialchars($v->tipo ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td><?= $matricula ?></td>
                            <td><?= $tipo ?></td>
                            <td class="text-center">
                                <div class="acciones-boton">
                                    <!-- Registrar certificado -->
                                    <a href="#" data-toggle="modal" data-target="#modalRegistrar<?= $idVehiculo ?>" class="btn btn-primary btn-sm" title="Registrar certificado">
                                        <i class="fa-solid fa-file-circle-plus"></i>
                                    </a>
                                    <!-- Ver certificados -->
                                    <a href="#" data-toggle="modal" data-target="#modalVer<?= $idVehiculo ?>" class="btn btn-info btn-sm" title="Ver certificados">
                                        <i class="fa-solid fa-clipboard-list"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php
                        /* ========== BLOQUE DE MODALES (se acumula y se imprime fuera de la tabla) ========== */
                        ob_start();
                        ?>
                        <!-- ========== MODAL REGISTRAR CERTIFICADO ========== -->
                        <div class="modal fade veh-modal" id="modalRegistrar<?= $idVehiculo ?>" tabindex="-1" aria-labelledby="modalRegistrarLabel<?= $idVehiculo ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header d-flex justify-content-between">
                                        <h5 class="modal-title w-100" id="modalRegistrarLabel<?= $idVehiculo ?>">Registrar certificado para: <?= $matricula ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>

                                    <form action="" method="POST" autocomplete="off" class="w-100">
                                        <div class="modal-body">
                                            <input type="hidden" name="vehiculo_id" value="<?= $idVehiculo ?>">

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Tipo de certificado</label>
                                                    <input type="text" name="tipo_certificado" class="form-control" maxlength="100" placeholder="Ej: SOAT / Tecnomecánica" required>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Fecha de emisión</label>
                                                    <input type="date" name="fecha_emision" class="form-control" required>
                                                </div>
                                                <div class="form-group col-md-3">
                                                    <label>Fecha de vencimiento</label>
                                                    <input type="date" name="fecha_vencimiento" class="form-control" required>
                                                </div>
                                            </div>
                                            <small class="text-muted">Se validará que no exista un certificado idéntico para este vehículo.</small>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary" name="btnregistrar_cert" value="ok">
                                                <i class="fa fa-save"></i> Registrar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- ========== /MODAL REGISTRAR CERTIFICADO ========== -->

                        <!-- ========== MODAL VER CERTIFICADOS ========== -->
                        <div class="modal fade veh-modal" id="modalVer<?= $idVehiculo ?>" tabindex="-1" aria-labelledby="modalVerLabel<?= $idVehiculo ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header d-flex justify-content-between">
                                        <h5 class="modal-title w-100" id="modalVerLabel<?= $idVehiculo ?>">Certificados de: <?= $matricula ?></h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>

                                    <div class="modal-body">
                                        <?php
                                        $certs = $conexion->prepare("
            SELECT id_certificado, tipo_certificado, fecha_emision, fecha_vencimiento
            FROM certificados
            WHERE id_vehiculo = ?
            ORDER BY id_certificado DESC
          ");
                                        $certs->bind_param('i', $idVehiculo);
                                        $certs->execute();
                                        $rs = $certs->get_result();
                                        ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>TIPO</th>
                                                        <th>F.EMISIÓN</th>
                                                        <th>F.VENCIMIENTO</th>
                                                        <th>ACCIONES</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if ($rs->num_rows > 0): ?>
                                                        <?php while ($c = $rs->fetch_object()):
                                                            $idCert = (int)$c->id_certificado;
                                                        ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($c->tipo_certificado ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><?= htmlspecialchars($c->fecha_emision ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td><?= htmlspecialchars($c->fecha_vencimiento ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                                                <td class="text-center">
                                                                    <a href="certificados.php?del=<?= $idCert ?>&veh=<?= $idVehiculo ?>" class="btn btn-danger btn-sm" title="Eliminar" onclick="advertenciaCert(event)">
                                                                        <i class="fa-solid fa-trash"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php else: ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted">Sin certificados registrados.</td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php $certs->close(); ?>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ========== /MODAL VER CERTIFICADOS ========== -->
                    <?php
                        $modales .= ob_get_clean(); // acumulamos estos modales
                    endwhile;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pintar los MODALES fuera de la tabla para evitar que se desborden/estilen como filas -->
<?= $modales ?>

<?php require('./layout/footer.php'); ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Normalizar búsqueda (acentos/ñ)
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
                targets: 2
            }],
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

        // Por si algún contenedor aplica overflow/posicionamientos, asegura los modales bajo <body>
        $('.veh-modal').appendTo('body');
    });

    // Confirmación de eliminación (SweetAlert2)
    function advertenciaCert(e) {
        e.preventDefault();
        const url = e.currentTarget.getAttribute('href');
        Swal.fire({
            title: '¿Eliminar certificado?',
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