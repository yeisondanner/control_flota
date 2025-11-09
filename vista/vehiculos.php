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
include "../controlador/controlador_modificar_vehiculo.php";
include "../controlador/controlador_eliminar_vehiculo.php";

/* Flash message tras redirección */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* Consulta de la tabla */
$sql = $conexion->query("
    SELECT 
        v.id_vehiculo, v.matricula, v.marca, v.modelo, v.tipo, v.year, v.kilometraje, v.id_unidadminera, um.nombre_unidad
    FROM vehiculos v
    INNER JOIN unidad_minera um ON v.id_unidadminera = um.id_unidadminera
    ORDER BY v.id_vehiculo DESC
");
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo (ajusta el índice según tu sidebar) */
    ul li:nth-child(5) .activo {
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

    /* Columna ACCIONES (5ta) */
    th:nth-child(5),
    td:nth-child(5) {
        width: 150px;
        white-space: nowrap;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">LISTA DE VEHÍCULOS</h4>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" referrerpolicy="no-referrer" />

    <div class="card-like">
        <div class="d-flex justify-content-end mb-2">
            <a href="registrar_vehiculos.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i>&nbsp;Registrar
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="example">
                <thead>
                    <tr>
                        <th>TIPO</th>
                        <th>MATRICULA</th>
                        <th>MARCA</th>
                        <th>UNIDAD MINERA</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $sql->fetch_object()):
                        $idVehiculo = (int)$row->id_vehiculo;
                        $matricula  = htmlspecialchars($row->matricula ?? '', ENT_QUOTES, 'UTF-8');
                        $marca      = htmlspecialchars($row->marca ?? '', ENT_QUOTES, 'UTF-8');
                        $modelo     = htmlspecialchars($row->modelo ?? '', ENT_QUOTES, 'UTF-8');
                        $tipo       = htmlspecialchars($row->tipo ?? '', ENT_QUOTES, 'UTF-8');
                        $nombreUnidad = htmlspecialchars($row->nombre_unidad ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td><?= $tipo ?></td>
                            <td><?= $matricula ?></td>
                            <td><?= $marca ?></td>
                            <td><?= $nombreUnidad ?></td>
                            <td class="text-center">
                                <div class="acciones-boton">
                                    <a href="#" data-toggle="modal" data-target="#modalVer<?= $idVehiculo ?>" class="btn btn-info btn-sm" title="Ver">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="#" data-toggle="modal" data-target="#modalEditar<?= $idVehiculo ?>" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="vehiculos.php?id=<?= $idVehiculo ?>" onclick="advertencia(event)" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- ========= MODAL VER ========= -->
                        <div class="modal fade" id="modalVer<?= $idVehiculo ?>" tabindex="-1" aria-labelledby="modalVerLabel<?= $idVehiculo ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title w-100" id="modalVerLabel<?= $idVehiculo ?>">DETALLES DEL VEHÍCULO</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Matricula:</strong> <?= $matricula ?></p>
                                        <p><strong>Marca:</strong> <?= $marca ?></p>
                                        <p><strong>Modelo:</strong> <?= $modelo ?></p>
                                        <p><strong>Tipo:</strong> <?= $tipo ?></p>
                                        <p><strong>Unidad Minera:</strong> <?= $nombreUnidad ?></p>
                                        <p><strong>Año:</strong> <?= $row->year ?></p> <!-- Añadido Año -->
                                        <p><strong>Kilometraje:</strong> <?= $row->kilometraje ?></p> <!-- Añadido Kilometraje -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ========= /MODAL VER ========= -->

                        <!-- ========= MODAL EDITAR ========= -->
                        <div class="modal fade" id="modalEditar<?= $idVehiculo ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?= $idVehiculo ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title w-100" id="modalEditarLabel<?= $idVehiculo ?>">MODIFICAR VEHÍCULO</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <form action="" method="POST" autocomplete="off">
                                        <div class="modal-body">
                                            <input type="hidden" name="txtidvehiculo" value="<?= $idVehiculo ?>">

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Matricula</label>
                                                    <input type="text" name="txtmatricula" class="form-control" placeholder="Matrícula" value="<?= $matricula ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Marca</label>
                                                    <input type="text" name="txtmarca" class="form-control" placeholder="Marca" value="<?= $marca ?>" required>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Modelo</label>
                                                    <input type="text" name="txtmodelo" class="form-control" placeholder="Modelo" value="<?= $modelo ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Tipo</label>
                                                    <input type="text" name="txttipo" class="form-control" placeholder="Tipo" value="<?= $tipo ?>" required>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Año</label>
                                                    <input type="number" name="txtaño" class="form-control" placeholder="Año" value="<?= $row->year ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Kilometraje</label>
                                                    <input type="decimal" name="txtkilometraje" class="form-control" placeholder="Kilometraje" value="<?= $row->kilometraje ?>" required>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Unidad Minera</label>
                                                    <select name="txtunidadminera" class="form-control" required>
                                                        <?php
                                                        // Obtén todas las unidades mineras
                                                        $unidades = $conexion->query("SELECT id_unidadminera, nombre_unidad FROM unidad_minera");
                                                        while ($unidad = $unidades->fetch_object()) {
                                                            $selected = ($unidad->id_unidadminera === $row->id_unidadminera) ? 'selected' : '';
                                                            echo "<option value=\"{$unidad->id_unidadminera}\" {$selected}>{$unidad->nombre_unidad}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary" name="btnmodificar" value="ok">
                                                <i class="fa fa-save"></i> Modificar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- ========= /MODAL EDITAR ========= -->
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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
                targets: 4 // Este es el número de la columna "Acciones"
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

    // Confirmación de eliminación (SweetAlert2)
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
                window.location.href = url; // PRG se encarga
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
