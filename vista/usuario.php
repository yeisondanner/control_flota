<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}

include "../modelo/conexion.php";

/* Controladores ANTES del HTML (PRG) */
include "../controlador/controlador_modificar_usuario.php";
include "../controlador/controlador_eliminar_usuario.php";

/* Flash */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* Consulta: últimos primero */
$sql = $conexion->query("
    SELECT 
      u.id_usuario, u.username, u.rol, u.id_persona,
      p.nombres, p.apellidos, p.dni
    FROM usuario u
    INNER JOIN persona p ON p.id_persona = u.id_persona
    ORDER BY u.id_usuario DESC
");
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    ul li:nth-child(2) .activo {
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

    th:nth-child(5),
    td:nth-child(5) {
        width: 150px;
        white-space: nowrap;
    }

    /* ACCIONES */
    .modal .form-control[readonly] {
        background: #eef1f4;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">LISTA DE USUARIOS</h4>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" referrerpolicy="no-referrer" />

    <div class="card-like">
        <div class="d-flex justify-content-end mb-2">
            <!-- Asegúrate que el archivo de registro sea el correcto -->
            <a href="registro_usuario.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i>&nbsp;Registrar
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="example">
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>APELLIDOS</th>
                        <th>USUARIO</th>
                        <th>ROL</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $sql->fetch_object()):
                        $idUser    = (int)$row->id_usuario;
                        $idPers    = (int)$row->id_persona;
                        $nombres   = htmlspecialchars($row->nombres ?? '', ENT_QUOTES, 'UTF-8');
                        $apellidos = htmlspecialchars($row->apellidos ?? '', ENT_QUOTES, 'UTF-8');
                        $usuario   = htmlspecialchars($row->username ?? '', ENT_QUOTES, 'UTF-8');
                        $rol       = htmlspecialchars($row->rol ?? '', ENT_QUOTES, 'UTF-8');
                        $dni       = htmlspecialchars($row->dni ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td><?= $nombres ?></td>
                            <td><?= $apellidos ?></td>
                            <td><?= $usuario ?></td>
                            <td><?= $rol ?></td>
                            <td class="text-center">
                                <div class="acciones-boton">
                                    <a href="#" data-toggle="modal" data-target="#modalEditar<?= $idUser ?>" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="usuario.php?id=<?= $idUser ?>" onclick="advertencia(event)" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- ========= MODAL EDITAR ========= -->
                        <div class="modal fade" id="modalEditar<?= $idUser ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?= $idUser ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header d-flex justify-content-between">
                                        <h5 class="modal-title w-100" id="modalEditarLabel<?= $idUser ?>">Modificar usuario</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <form action="" method="POST" autocomplete="off">
                                        <div class="modal-body">
                                            <input type="hidden" name="txtidusuario" value="<?= $idUser ?>">
                                            <input type="hidden" name="txtidpersona" value="<?= $idPers ?>">

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Nombres</label>
                                                    <input type="text" name="txtnombre" class="form-control" value="<?= $nombres ?>" placeholder="Nombres">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Apellidos</label>
                                                    <input type="text" name="txtapellidos" class="form-control" value="<?= $apellidos ?>" placeholder="Apellidos">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>DNI</label>
                                                    <input type="text" name="txtdni" class="form-control"
                                                        value="<?= $dni ?>" placeholder="DNI"
                                                        inputmode="numeric" maxlength="8"
                                                        oninput="this.value=this.value.replace(/\\D/g,'').slice(0,8)">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Usuario</label>
                                                    <input type="text" name="txtusuario" class="form-control" value="<?= $usuario ?>" placeholder="Usuario">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Nueva contraseña</label>
                                                    <input type="password" name="txtpassword" class="form-control" placeholder="Dejar vacío para mantener">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Rol</label>
                                                    <select name="txtrol" class="form-control" style="font-weight:bold;">
                                                        <?php
                                                        $roles = ['Administrador', 'Asistente de flota', 'Conductor'];
                                                        foreach ($roles as $r) {
                                                            $sel = ($r === $rol) ? 'selected' : '';
                                                            echo "<option value=\"{$r}\" {$sel}>{$r}</option>";
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

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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
                targets: 4
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

    // Confirmación de eliminación
    function advertencia(e) {
        e.preventDefault();
        const url = e.currentTarget.getAttribute('href');
        Swal.fire({
            title: '¿Eliminar usuario?',
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