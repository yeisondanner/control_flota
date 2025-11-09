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
include "../controlador/controlador_modificar_unidadminera.php";
include "../controlador/controlador_eliminar_unidadminera.php";

/* Flash message tras redirección */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* Consulta de la tabla */
$sql = $conexion->query("
    SELECT 
        um.id_unidadminera, um.nombre_unidad, um.descripcion
    FROM unidad_minera um
    ORDER BY um.id_unidadminera DESC
");
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo (ajusta el índice según tu sidebar) */
    ul li:nth-child(3) .activo {
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
    th:nth-child(3),
    td:nth-child(3) {
        width: 150px;
        white-space: nowrap;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">LISTA DE UNIDADES MINERAS</h4>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" referrerpolicy="no-referrer" />

    <div class="card-like">
        <div class="d-flex justify-content-end mb-2">
            <a href="registrar_unidadminera.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i>&nbsp;Registrar
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="example">
                <thead>
                    <tr>
                        <th>NOMBRE DE UNIDAD</th>
                        <th>DESCRIPCIÓN</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $sql->fetch_object()):
                        $idUnidad    = (int)$row->id_unidadminera;
                        $nombreUnidad = htmlspecialchars($row->nombre_unidad ?? '', ENT_QUOTES, 'UTF-8');
                        $descripcion = htmlspecialchars($row->descripcion ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td><?= $nombreUnidad ?></td>
                            <td><?= $descripcion ?></td>
                            <td class="text-center">
                                <div class="acciones-boton">
                                    <a href="#" data-toggle="modal" data-target="#modalEditar<?= $idUnidad ?>" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="unidad_minera.php?id=<?= $idUnidad ?>" onclick="advertencia(event)" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- ========= MODAL EDITAR ========= -->
                        <div class="modal fade" id="modalEditar<?= $idUnidad ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?= $idUnidad ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header d-flex justify-content-between">
                                        <h5 class="modal-title w-100" id="modalEditarLabel<?= $idUnidad ?>">Modificar unidad minera</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <form action="" method="POST" autocomplete="off">
                                        <div class="modal-body">
                                            <input type="hidden" name="txtidunidad" value="<?= $idUnidad ?>">

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Nombre de unidad</label>
                                                    <input type="text" name="txtnombreunidad" class="form-control" placeholder="Nombre de la unidad" value="<?= $nombreUnidad ?>">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Descripción</label>
                                                    <input type="text" name="txtdescripcion" class="form-control" placeholder="Descripción" value="<?= $descripcion ?>">
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
                icon: '<?= $flash['tipo'] ?>', // success | error | warning | info
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