<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}

include "../modelo/conexion.php";
/* ⬇️ Controlador de eliminación (PRG) */
include "../controlador/controlador_eliminar_kilometraje.php";

/* Flash */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* Consulta principal */
$sql = $conexion->query("
    SELECT 
        ks.id_kilometrajesemanal AS id_km,
        ks.kilometraje,
        ks.horas,
        ks.fecha_registro,
        p.nombres,
        p.apellidos,
        p.dni,
        v.matricula,
        v.marca,
        v.modelo
    FROM kilometraje_semanal ks
    JOIN conductor c ON c.id_conductor = ks.id_conductor
    JOIN usuario u   ON u.id_usuario = c.id_usuario
    JOIN persona p   ON p.id_persona = u.id_persona
    JOIN vehiculos v ON v.id_vehiculo = ks.id_vehiculo
    ORDER BY ks.fecha_registro DESC, id_km DESC
");
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Marca de menú activo */
    ul li:nth-child(11) .activo {
        background: #0b96d6 !important;
    }

    /* Evita barra horizontal del body en zooms */
    html,
    body {
        overflow-x: hidden;
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

    /* Tabla: layout auto y quiebre de texto */
    table.table {
        table-layout: auto;
        width: 100%;
    }

    .table th,
    .table td {
        white-space: normal;
        word-break: break-word;
    }

    /* DataTables */
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

    td.col-acciones {
        white-space: nowrap;
    }

    /* Alineación numérica derecha para KM */
    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">KILOMETRAJES SEMANALES</h4>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" referrerpolicy="no-referrer" />

    <div class="card-like">
        <div class="d-flex justify-content-end mb-2">
            <a href="registrar_kilometraje.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i>&nbsp;Registrar
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="example">
                <thead>
                    <tr>
                        <th>CONDUCTOR</th>
                        <th>DNI</th>
                        <th>VEHÍCULO</th>
                        <th>MARCA / MODELO</th>
                        <th>KILOMETRAJE</th>
                        <th>HORA</th>
                        <th>FECHA</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = $sql->fetch_object()):
                        $idKm   = (int)$r->id_km;
                        $nom    = htmlspecialchars($r->nombres ?? '', ENT_QUOTES, 'UTF-8');
                        $ape    = htmlspecialchars($r->apellidos ?? '', ENT_QUOTES, 'UTF-8');
                        $dni    = htmlspecialchars($r->dni ?? '', ENT_QUOTES, 'UTF-8');
                        $mat    = htmlspecialchars($r->matricula ?? '', ENT_QUOTES, 'UTF-8');
                        $marca  = htmlspecialchars($r->marca ?? '', ENT_QUOTES, 'UTF-8');
                        $modelo = htmlspecialchars($r->modelo ?? '', ENT_QUOTES, 'UTF-8');

                        /* ===== KILOMETRAJE: miles '.' (0 decimales) o 'Sin registro' ===== */
                        if ($r->kilometraje === null || $r->kilometraje === '' || !is_numeric($r->kilometraje)) {
                            $km = 'Sin registro';
                        } else {
                            $km = number_format((float)$r->kilometraje, 0, ',', '.');
                        }

                        /* ===== HORA: HH:MM o 'Sin registro' ===== */
                        $horaRaw = $r->horas ?? null; // puede venir 'HH:MM:SS', 'HH:MM' o NULL
                        if ($horaRaw && is_string($horaRaw)) {
                            // Normalizar a HH:MM
                            $hora = substr($horaRaw, 0, 5); // si viene HH:MM:SS => HH:MM
                            // Validación mínima
                            if (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
                                $hora = 'Sin registro';
                            }
                        } else {
                            $hora = 'Sin registro';
                        }

                        /* ===== FECHA: DD/MM/AAAA ===== */
                        $fechaRaw = $r->fecha_registro ?? '';
                        if ($fechaRaw) {
                            $dt = DateTime::createFromFormat('Y-m-d', $fechaRaw);
                            $fecha = $dt ? $dt->format('d/m/Y') : date('d/m/Y', strtotime($fechaRaw));
                        } else {
                            $fecha = '';
                        }
                    ?>
                        <tr>
                            <td><?= $nom . ' ' . $ape ?></td>
                            <td><?= $dni ?></td>
                            <td><?= $mat ?></td>
                            <td><?= $marca . ' ' . $modelo ?></td>
                            <td class="text-right"><?= $km ?></td>
                            <td class="text-center"><?= $hora ?></td>
                            <td><?= $fecha ?></td>
                            <td class="text-center col-acciones">
                                <div class="acciones-boton">
                                    <a href="kilometrajes.php?del=<?= $idKm ?>" class="btn btn-danger btn-sm" title="Eliminar" onclick="confirmarEliminar(event)">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require('./layout/footer.php'); ?>

<!-- JS y plugins -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables Core -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- DataTables Responsive -->
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

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
            responsive: {
                details: {
                    type: 'inline'
                }
            },
            autoWidth: false,
            order: [], // respeta el ORDER BY del servidor
            columnDefs: [{
                    orderable: false,
                    targets: 7
                }, // ACCIONES (ahora es la col 8 -> índice 7)
                {
                    targets: 0,
                    responsivePriority: 1
                }, // CONDUCTOR
                {
                    targets: 7,
                    responsivePriority: 2
                } // ACCIONES
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
        });
    });

    function confirmarEliminar(e) {
        e.preventDefault();
        const url = e.currentTarget.getAttribute('href');
        Swal.fire({
            title: '¿Eliminar registro?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            reverseButtons: true
        }).then((r) => {
            if (r.isConfirmed) {
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