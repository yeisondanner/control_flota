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
include "../controlador/controlador_modificar_suministro.php";
include "../controlador/controlador_eliminar_suministro.php";

/* Flash */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* Consulta principal */
$sql = $conexion->query("
    SELECT id_suministros, nombre, descripcion
    FROM suministros
    ORDER BY id_suministros DESC
");
?>
<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo: ajusta el índice si tu sidebar difiere */
    ul li:nth-child(8) .activo {
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
        width: 150px;
        white-space: nowrap;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">LISTA DE SUMINISTROS</h4>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" referrerpolicy="no-referrer" />

    <div class="card-like">
        <div class="d-flex justify-content-end mb-2">
            <a href="registrar_suministros.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i>&nbsp;Registrar
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="example">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>DESCRIPCIÓN</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $sql->fetch_object()):
                        $id    = (int)$row->id_suministros;
                        $nom   = htmlspecialchars($row->nombre ?? '', ENT_QUOTES, 'UTF-8');
                        $desc  = htmlspecialchars($row->descripcion ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td><?= $nom ?></td>
                            <td><?= $desc ?></td>
                            <td class="text-center">
                                <div class="acciones-boton">
                                    <a href="#" class="btn btn-warning btn-sm" title="Editar" data-toggle="modal" data-target="#modalEditar<?= $id ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="suministros.php?del=<?= $id ?>" class="btn btn-danger btn-sm" title="Eliminar" onclick="advertenciaEliminar(event)">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- ===== Modal Editar ===== -->
                        <div class="modal fade" id="modalEditar<?= $id ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?= $id ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header d-flex justify-content-between">
                                        <h5 class="modal-title w-100" id="modalEditarLabel<?= $id ?>">Modificar suministro</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <form action="" method="POST" autocomplete="off">
                                        <div class="modal-body">
                                            <input type="hidden" name="txtidsuministro" value="<?= $id ?>">

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Nombre</label>
                                                    <input type="text" name="txtnombre" class="form-control" maxlength="200" value="<?= $nom ?>" required>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Descripción</label>
                                                    <input type="text" name="txtdescripcion" class="form-control" maxlength="500" value="<?= $desc ?>" required>
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
                        <!-- ===== /Modal Editar ===== -->
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
            order: [], // respeta ORDER BY del servidor
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

    // Confirmación de eliminación
    function advertenciaEliminar(e) {
        e.preventDefault();
        const url = e.currentTarget.getAttribute('href');
        Swal.fire({
            title: '¿Eliminar suministro?',
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