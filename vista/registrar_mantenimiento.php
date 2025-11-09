<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}

include "../modelo/conexion.php";
/* ⬇️ Controlador ANTES del HTML para permitir header('Location') tras guardar */
include "../controlador/controlador_registrar_mantenimiento.php";

/* Flash */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* Vehículos */
$vehiculos = $conexion->query("
    SELECT v.id_vehiculo, v.matricula, v.tipo
    FROM vehiculos v
    ORDER BY v.id_vehiculo DESC
");

/* Herramientas */
$herramientas = $conexion->query("
    SELECT h.id_herramientas, h.nombre
    FROM herramientas h
    ORDER BY h.nombre
");

/* Suministros */
$suministros = $conexion->query("
    SELECT s.id_suministros, s.nombre
    FROM suministros s
    ORDER BY s.nombre
");
?>
<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo */
    ul li:nth-child(10) .activo {
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

    .form-text {
        color: #6b7280;
        font-size: .88rem;
    }

    .card-like .form-control,
    .card-like select.form-control {
        width: 100%;
    }

    .actions-row {
        display: block;
        width: 100%;
        clear: both;
        margin-top: 10px;
    }

    .actions-right {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .actions-right .btn+.btn {
        margin-left: 6px;
    }

    @media (max-width:575.98px) {
        .actions-right {
            flex-direction: column;
        }

        .actions-right .btn {
            width: 100%;
        }
    }

    .form-error-summary {
        display: none;
        border: 1px solid #f5c6cb;
        background: #f8d7da;
        color: #721c24;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
        font-size: .95rem;
    }

    fieldset {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px;
    }

    fieldset legend {
        font-size: .95rem;
        font-weight: 600;
        padding: 0 8px;
        width: auto;
    }

    .choices-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 18px;
    }

    .choices-wrap .form-check {
        min-width: 220px;
    }

    .muted {
        color: #6b7280;
        font-size: .85rem;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">REGISTRO DE MANTENIMIENTO DE VEHÍCULOS</h4>

    <div class="card-like">
        <form action="" method="POST" autocomplete="off" class="w-100 js-validate" novalidate>
            <div class="form-error-summary">Por favor, complete todos los campos obligatorios antes de registrar.</div>

            <!-- Fila 1 -->
            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="vehiculo" class="form-label">Vehículo</label>
                    <select id="vehiculo" name="vehiculo" class="form-control" required>
                        <option value="">Selecciona un vehículo</option>
                        <?php while ($v = $vehiculos->fetch_object()): ?>
                            <option value="<?= (int)$v->id_vehiculo ?>"><?= htmlspecialchars($v->matricula) ?> - <?= htmlspecialchars($v->tipo) ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="invalid-feedback">Seleccione un vehículo.</div>
                </div>

                <div class="form-group col-12 col-md-6">
                    <label for="tipo_mantenimiento" class="form-label">Tipo de mantenimiento</label>
                    <select id="tipo_mantenimiento" name="tipo_mantenimiento" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="Preventivo">Preventivo</option>
                        <option value="Correctivo">Correctivo</option>
                    </select>
                    <small class="form-text">Elija si es mantenimiento preventivo o correctivo.</small>
                    <div class="invalid-feedback">Seleccione el tipo de mantenimiento.</div>
                </div>
            </div>

            <!-- Fila 2 -->
            <div class="form-row">
                <div class="form-group col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" placeholder="Descripción del mantenimiento" rows="3" required></textarea>
                    <div class="invalid-feedback">Ingrese una descripción del mantenimiento.</div>
                </div>
            </div>

            <!-- Fila 3: Fecha y horas -->
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="fecha" class="form-label">Fecha del mantenimiento</label>
                    <input type="date" id="fecha" name="fecha" class="form-control" required>
                    <div class="invalid-feedback">Seleccione la fecha.</div>
                </div>
                <div class="form-group col-md-4">
                    <label for="hora_actual" class="form-label">Hora actual</label>
                    <!-- quitado required -->
                    <input type="time" id="hora_actual" name="hora_actual" class="form-control">
                </div>
                <div class="form-group col-md-4">
                    <label for="hora_proxima" class="form-label">Hora próxima (referencial)</label>
                    <!-- quitado required -->
                    <input type="time" id="hora_proxima" name="hora_proxima" class="form-control">
                </div>
            </div>

            <!-- Fila 4: Kilometrajes y gasto -->
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="kilometraje_actual" class="form-label">Kilometraje actual</label>
                    <!-- quitado required -->
                    <input type="number" step="0.01" id="kilometraje_actual" name="kilometraje_actual" class="form-control" placeholder="Kilometraje actual">
                </div>
                <div class="form-group col-md-4">
                    <label for="kilometraje_proximo" class="form-label">Kilometraje próximo mantenimiento</label>
                    <!-- quitado required -->
                    <input type="number" step="0.01" id="kilometraje_proximo" name="kilometraje_proximo" class="form-control" placeholder="Kilometraje próximo mantenimiento">
                </div>
                <div class="form-group col-md-4">
                    <label for="gasto_mantenimiento" class="form-label">Gasto de mantenimiento</label>
                    <!-- se mantiene requerido -->
                    <input type="number" step="0.01" id="gasto_mantenimiento" name="gasto_mantenimiento" class="form-control" placeholder="Gasto de mantenimiento" required>
                    <div class="invalid-feedback">Ingrese el gasto del mantenimiento.</div>
                </div>
            </div>

            <!-- Fila 5: Herramientas -->
            <div class="form-row">
                <div class="form-group col-12">
                    <fieldset id="fs-herramientas">
                        <legend>Repuestos utilizados</legend>
                        <div class="choices-wrap">
                            <?php while ($h = $herramientas->fetch_object()): ?>
                                <div class="form-check">
                                    <input class="form-check-input js-check-h" type="checkbox" name="herramientas[]" id="herramienta<?= (int)$h->id_herramientas ?>" value="<?= (int)$h->id_herramientas ?>">
                                    <label class="form-check-label" for="herramienta<?= (int)$h->id_herramientas ?>">
                                        <?= htmlspecialchars($h->nombre ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="muted">Si el mantenimiento es <strong>Correctivo</strong>, debe seleccionar al menos un repuesto.</div>
                    </fieldset>
                </div>
            </div>

            <!-- Fila 6: Suministros -->
            <div class="form-row">
                <div class="form-group col-12">
                    <fieldset id="fs-suministros">
                        <legend>Suministros utilizados</legend>
                        <div class="choices-wrap">
                            <?php while ($s = $suministros->fetch_object()): ?>
                                <div class="form-check">
                                    <input class="form-check-input js-check-s" type="checkbox" name="suministros[]" id="suministro<?= (int)$s->id_suministros ?>" value="<?= (int)$s->id_suministros ?>">
                                    <label class="form-check-label" for="suministro<?= (int)$s->id_suministros ?>">
                                        <?= htmlspecialchars($s->nombre ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="muted">Si el mantenimiento es <strong>Preventivo</strong>, debe seleccionar al menos un suministro.</div>
                    </fieldset>
                </div>
            </div>

            <!-- Acciones -->
            <div class="actions-row">
                <div class="actions-right">
                    <a href="mantenimiento.php" class="btn btn-secondary btn-rounded">Atrás</a>
                    <button type="submit" class="btn btn-primary btn-rounded" name="btnregistrar_mantenimiento" value="ok">
                        Registrar mantenimiento
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require('./layout/footer.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Habilitar/Deshabilitar grupos según tipo seleccionado
    function toggleGroups() {
        const tipo = document.getElementById('tipo_mantenimiento')?.value;
        const hInputs = document.querySelectorAll('.js-check-h');
        const sInputs = document.querySelectorAll('.js-check-s');

        if (tipo === 'Preventivo') {
            // Herramientas OFF
            hInputs.forEach(i => {
                i.checked = false;
                i.disabled = true;
                i.closest('.form-check').classList.add('disabled');
            });
            // Suministros ON
            sInputs.forEach(i => {
                i.disabled = false;
                i.closest('.form-check').classList.remove('disabled');
            });
        } else if (tipo === 'Correctivo') {
            // Suministros OFF
            sInputs.forEach(i => {
                i.checked = false;
                i.disabled = true;
                i.closest('.form-check').classList.add('disabled');
            });
            // Herramientas ON
            hInputs.forEach(i => {
                i.disabled = false;
                i.closest('.form-check').classList.remove('disabled');
            });
        } else {
            // Ninguno seleccionado: habilitar ambos
            hInputs.forEach(i => {
                i.disabled = false;
                i.closest('.form-check').classList.remove('disabled');
            });
            sInputs.forEach(i => {
                i.disabled = false;
                i.closest('.form-check').classList.remove('disabled');
            });
        }
    }

    document.getElementById('tipo_mantenimiento')?.addEventListener('change', toggleGroups);
    document.addEventListener('DOMContentLoaded', toggleGroups);

    // Validación simple + regla de al menos un ítem en el grupo habilitado
    (function() {
        function validateForm(form) {
            let valido = true;
            const requiredFields = form.querySelectorAll('[required]');

            requiredFields.forEach(campo => {
                const value = (campo.value || '').trim();
                if (value === '') {
                    campo.classList.add('is-invalid');
                    valido = false;
                } else {
                    campo.classList.remove('is-invalid');
                }
            });

            // Validación de grupo según tipo
            const tipo = document.getElementById('tipo_mantenimiento')?.value;
            if (tipo === 'Preventivo') {
                const anyS = [...document.querySelectorAll('.js-check-s')].some(i => !i.disabled && i.checked);
                if (!anyS) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Falta seleccionar',
                        text: 'Seleccione al menos un suministro para mantenimiento preventivo.'
                    });
                    valido = false;
                }
            } else if (tipo === 'Correctivo') {
                const anyH = [...document.querySelectorAll('.js-check-h')].some(i => !i.disabled && i.checked);
                if (!anyH) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Falta seleccionar',
                        text: 'Seleccione al menos una herramienta para mantenimiento correctivo.'
                    });
                    valido = false;
                }
            }

            const summary = form.querySelector('.form-error-summary');
            if (summary) summary.style.display = valido ? 'none' : 'block';
            return valido;
        }

        document.addEventListener('submit', function(e) {
            const form = e.target.closest('form.js-validate');
            if (!form) return;
            if (!validateForm(form)) {
                e.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
            }
        });
    })();
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