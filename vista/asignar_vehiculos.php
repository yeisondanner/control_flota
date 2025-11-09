<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}

// Validar acceso de conductor: solo pueden ver kilometrajes
include "../modelo/validar_conductor.php";

include "../modelo/conexion.php";
/* Controladores ANTES del HTML (para que header('Location') funcione) */
include "../controlador/controlador_asignar_vehiculo.php";

/* Flash message tras redirección */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* ======================= CONSULTA CONDUCTORES (NUEVO ESQUEMA) =======================
   Esquema actual: persona <- usuario <- conductor
   conductor.id_usuario -> usuario.id_usuario -> persona.id_persona
   =============================================================================== */
$sql_conductores = $conexion->query("
    SELECT 
        c.id_conductor,
        p.nombres,
        p.apellidos,
        p.dni
    FROM conductor c
    INNER JOIN usuario u ON u.id_usuario = c.id_usuario
    INNER JOIN persona p ON p.id_persona = u.id_persona
    ORDER BY p.apellidos ASC, p.nombres ASC
");

/* Consulta de los vehículos (igual que antes) */
$sql_vehiculos = $conexion->query("
    SELECT id_vehiculo, matricula, marca, modelo 
    FROM vehiculos 
    ORDER BY marca ASC, modelo ASC
");

/* ======================= CONSULTA ASIGNACIONES (NUEVO ESQUEMA) =======================
   conductor_vehiculo -> conductor -> usuario -> persona  y  -> vehiculos
   =============================================================================== */
$sql_asignaciones = $conexion->query("
    SELECT 
        cv.id_conductorvehiculo,
        p.nombres,
        p.apellidos,
        v.matricula,
        cv.fecha_registro
    FROM conductor_vehiculo cv
    INNER JOIN conductor c ON c.id_conductor = cv.id_conductor
    INNER JOIN usuario u ON u.id_usuario = c.id_usuario
    INNER JOIN persona p ON p.id_persona = u.id_persona
    INNER JOIN vehiculos v ON v.id_vehiculo = cv.id_vehiculo
    ORDER BY cv.id_conductorvehiculo DESC
");
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo (ajusta el índice según tu sidebar) */
    ul li:nth-child(6) .activo {
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

    /* Columna ACCIONES (4ta) */
    th:nth-child(4),
    td:nth-child(4) {
        width: 150px;
        white-space: nowrap;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">ASIGNAR VEHÍCULOS A CONDUCTORES</h4>

    <div class="card-like">
        <form action="" method="POST">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="conductor">Seleccionar Conductor</label>
                    <select name="id_conductor" class="form-control" required>
                        <option value="">Selecciona un conductor</option>
                        <?php while ($row = $sql_conductores->fetch_object()): ?>
                            <option value="<?= (int)$row->id_conductor ?>">
                                <?= htmlspecialchars($row->nombres . ' ' . $row->apellidos, ENT_QUOTES, 'UTF-8') ?>
                                (DNI: <?= htmlspecialchars($row->dni, ENT_QUOTES, 'UTF-8') ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="vehiculo">Seleccionar Vehículo</label>
                    <select name="id_vehiculo" class="form-control" required>
                        <option value="">Selecciona un vehículo</option>
                        <?php while ($row = $sql_vehiculos->fetch_object()): ?>
                            <option value="<?= (int)$row->id_vehiculo ?>">
                                <?= htmlspecialchars($row->matricula . ' - ' . $row->marca . ' ' . $row->modelo, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" name="asignar_vehiculo">
                Asignar Vehículo
            </button>
        </form>
    </div>

    <div class="card-like mt-4">
        <h5>Registros de Asignación</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="example">
                <thead>
                    <tr>
                        <th>CONDUCTOR</th>
                        <th>VEHÍCULO</th>
                        <th>FECHA DE ASIGNACIÓN</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $sql_asignaciones->fetch_object()):
                        $fechaRaw = $row->fecha_registro ?? '';
                        if ($fechaRaw) {
                            // Si viene como 'YYYY-MM-DD' o 'YYYY-MM-DD HH:MM:SS', ambos casos funcionarán
                            $dt = DateTime::createFromFormat('Y-m-d', $fechaRaw);
                            if (!$dt) {
                                $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fechaRaw);
                            }
                            $fechaFmt = $dt ? $dt->format('d/m/Y') : date('d/m/Y', strtotime($fechaRaw));
                        } else {
                            $fechaFmt = '';
                        }
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row->nombres . ' ' . $row->apellidos, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row->matricula, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($fechaFmt, ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center">
                                <div class="acciones-boton">
                                    <a href="../controlador/controlador_eliminar_asignacion.php?id=<?= (int)$row->id_conductorvehiculo ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="advertencia(event)"
                                        title="Eliminar">
                                        Eliminar
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(function() {
        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }
        $('#example').DataTable({
            columnDefs: [{
                orderable: false,
                targets: 3
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
    });

    function advertencia(e) {
        e.preventDefault();
        const url = e.currentTarget.getAttribute('href');
        Swal.fire({
            title: '¿Está seguro?',
            text: '¡No podrá recuperar este registro!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, Eliminar',
            cancelButtonText: 'No, Salir',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url; // PRG
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