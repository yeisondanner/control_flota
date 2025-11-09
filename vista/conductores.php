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
include "../controlador/controlador_modificar_conductor.php";
include "../controlador/controlador_eliminar_conductor.php";

/* Flash message tras redirección */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* ======================= CONSULTA ACTUALIZADA =======================
   Esquema actual: persona <- usuario <- conductor
   Agregamos u.username para mostrar el login en el modal Ver
===================================================================== */
$sql = $conexion->query("
    SELECT 
        c.id_conductor,
        u.id_usuario,
        u.username,               -- 🔹 NUEVO: username
        p.id_persona,
        c.categoria_licencia,
        c.numero_licencia,
        c.fvencimiento_licencia,
        p.nombres,
        p.apellidos,
        p.dni,
        p.telefono,
        p.email,
        p.direccion,
        p.fecha_nacimiento
    FROM conductor c
    INNER JOIN usuario u ON u.id_usuario = c.id_usuario
    INNER JOIN persona p ON p.id_persona = u.id_persona
    ORDER BY c.id_conductor DESC
");
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo (ajusta el índice según tu sidebar) */
    ul li:nth-child(4) .activo {
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

    /* Inputs readonly en modal */
    .modal .form-control[readonly] {
        background: #eef1f4;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">LISTA DE CONDUCTORES</h4>

    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" referrerpolicy="no-referrer" />

    <div class="card-like">
        <div class="d-flex justify-content-end mb-2">
            <a href="registrar_conductor.php" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-plus"></i>&nbsp;Registrar
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="example">
                <thead>
                    <tr>
                        <th>NOMBRE Y APELLIDOS</th>
                        <th>DNI</th>
                        <th>NÚMERO LICENCIA</th>
                        <th>CATEGORÍA LICENCIA</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $sql->fetch_object()):
                        $idCond    = (int)$row->id_conductor;
                        $idUser    = (int)$row->id_usuario;
                        $idPers    = (int)$row->id_persona;

                        $nombres   = htmlspecialchars($row->nombres ?? '', ENT_QUOTES, 'UTF-8');
                        $apellidos = htmlspecialchars($row->apellidos ?? '', ENT_QUOTES, 'UTF-8');
                        $dni       = htmlspecialchars($row->dni ?? '', ENT_QUOTES, 'UTF-8');
                        $nlic      = htmlspecialchars($row->numero_licencia ?? '', ENT_QUOTES, 'UTF-8');
                        $catlic    = htmlspecialchars($row->categoria_licencia ?? '', ENT_QUOTES, 'UTF-8');
                        $fvenc     = htmlspecialchars($row->fvencimiento_licencia ?? '', ENT_QUOTES, 'UTF-8');
                        $telefono  = htmlspecialchars($row->telefono ?? '', ENT_QUOTES, 'UTF-8');
                        $email     = htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8');
                        $direccion = htmlspecialchars($row->direccion ?? '', ENT_QUOTES, 'UTF-8');
                        $fecha_nacimiento = htmlspecialchars($row->fecha_nacimiento ?? '', ENT_QUOTES, 'UTF-8');

                        /* 🔹 NUEVO: username */
                        $username  = htmlspecialchars($row->username ?? '', ENT_QUOTES, 'UTF-8');
                    ?>
                        <tr>
                            <td><?= $nombres . ' ' . $apellidos ?></td>
                            <td><?= $dni ?></td>
                            <td><?= $nlic ?></td>
                            <td><?= $catlic ?></td>
                            <td class="text-center">
                                <div class="acciones-boton">
                                    <a href="#" data-toggle="modal" data-target="#modalVer<?= $idCond ?>" class="btn btn-info btn-sm" title="Ver">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="#" data-toggle="modal" data-target="#modalEditar<?= $idCond ?>" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="conductores.php?id=<?= $idCond ?>" onclick="advertencia(event)" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- ========= MODAL VER ========= -->
                        <div class="modal fade" id="modalVer<?= $idCond ?>" tabindex="-1" aria-labelledby="modalVerLabel<?= $idCond ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title w-100" id="modalVerLabel<?= $idCond ?>">DETALLES DEL CONDUCTOR</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Nombres:</strong> <?= $nombres ?></p>
                                        <p><strong>Apellidos:</strong> <?= $apellidos ?></p>
                                        <p><strong>DNI:</strong> <?= $dni ?></p>
                                        <p><strong>N° de licencia:</strong> <?= $nlic ?></p>
                                        <p><strong>Categoría de licencia:</strong> <?= $catlic ?></p>
                                        <p><strong>Fecha de vencimiento:</strong> <?= $fvenc ?></p>
                                        <p><strong>Teléfono:</strong> <?= $telefono ?></p>
                                        <p><strong>Correo:</strong> <?= $email ?></p>
                                        <p><strong>Dirección:</strong> <?= $direccion ?></p>
                                        <p><strong>Fecha de nacimiento:</strong> <?= $fecha_nacimiento ?></p>
                                        <p><strong>Nombre de usuario:</strong> <?= $username ?></p> <!-- 🔹 Ahora definido -->
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ========= /MODAL VER ========= -->

                        <!-- ========= MODAL EDITAR ========= -->
                        <div class="modal fade" id="modalEditar<?= $idCond ?>" tabindex="-1" aria-labelledby="modalEditarLabel<?= $idCond ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header d-flex justify-content-between">
                                        <h5 class="modal-title w-100" id="modalEditarLabel<?= $idCond ?>">Modificar conductor</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <form action="" method="POST" autocomplete="off">
                                        <div class="modal-body">
                                            <input type="hidden" name="txtidconductor" value="<?= $idCond ?>">
                                            <input type="hidden" name="txtidusuario" value="<?= $idUser ?>">
                                            <input type="hidden" name="txtidpersona" value="<?= $idPers ?>">

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Nombres</label>
                                                    <input type="text" name="txtnombre" class="form-control" placeholder="Nombres" value="<?= $nombres ?>">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Apellidos</label>
                                                    <input type="text" name="txtapellidos" class="form-control" placeholder="Apellidos" value="<?= $apellidos ?>">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Teléfono</label>
                                                    <input type="text" name="txttelefono" class="form-control" placeholder="Teléfono" value="<?= $telefono ?>">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Correo</label>
                                                    <input type="text" name="txtcorreo" class="form-control" placeholder="Correo" value="<?= $email ?>">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Dirección</label>
                                                    <input type="text" name="txtdireccion" class="form-control" placeholder="Dirección" value="<?= $direccion ?>">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>Fecha de nacimiento</label>
                                                    <input type="date" name="txtfechanacimiento" class="form-control" value="<?= $fecha_nacimiento ?>">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>DNI</label>
                                                    <input type="text" name="txtdni" class="form-control" placeholder="DNI" value="<?= $dni ?>" inputmode="numeric" maxlength="8" oninput="this.value=this.value.replace(/\D/g,'').slice(0,8)">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>N° de licencia</label>
                                                    <input type="text" name="txtnlicencia" class="form-control" placeholder="Número de licencia" value="<?= $nlic ?>">
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                    <label>Categoría de licencia</label>
                                                    <select name="txtcatlicencia" class="form-control" style="font-weight:bold;">
                                                        <?php
                                                        $cats = ['A-I', 'A-IIa', 'A-IIb', 'A-IIIa', 'A-IIIb', 'A-IIIc', 'B-I', 'B-IIa', 'B-IIb'];
                                                        foreach ($cats as $c) {
                                                            $sel = ($c === $catlic) ? 'selected' : '';
                                                            echo "<option value=\"{$c}\" {$sel}>{$c}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label>F. vencimiento (opcional)</label>
                                                    <input type="date" name="txtfvenc" class="form-control" value="<?= $fvenc ?>">
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
                targets: 4
            }], // Columna "Acciones"
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