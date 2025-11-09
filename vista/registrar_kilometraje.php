<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}

include "../modelo/conexion.php";
/* Controlador (PRG) */
include "../controlador/controlador_registrar_kilometraje.php";

/* Flash */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* Conductores (persona <- usuario <- conductor) */
$conductores = $conexion->query("
  SELECT c.id_conductor, p.nombres, p.apellidos, p.dni
  FROM conductor c
  JOIN usuario u  ON u.id_usuario = c.id_usuario
  JOIN persona p  ON p.id_persona = u.id_persona
  ORDER BY p.apellidos ASC, p.nombres ASC
");
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo */
    ul li:nth-child(11) .activo {
        background: #0b96d6 !important;
    }

    h4.text-center.text-secondary {
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

    @media(max-width:575.98px) {
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
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">REGISTRO DE KILOMETRAJE / HORAS SEMANALES</h4>

    <div class="card-like">
        <form action="" method="POST" autocomplete="off" class="w-100 js-validate" novalidate>
            <div class="form-error-summary">
                Por favor complete conductor/vehículo y al menos <strong>kilometraje</strong> o <strong>horas semanales</strong>.
            </div>

            <!-- Fila 1: Conductor / Vehículo -->
            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="conductor" class="form-label">Conductor</label>
                    <select id="conductor" name="id_conductor" class="form-control" required>
                        <option value="">-- Seleccione un conductor --</option>
                        <?php while ($c = $conductores->fetch_object()): ?>
                            <option value="<?= (int)$c->id_conductor ?>">
                                <?= htmlspecialchars($c->apellidos . ' ' . $c->nombres, ENT_QUOTES, 'UTF-8') ?>
                                (DNI: <?= htmlspecialchars($c->dni ?? '', ENT_QUOTES, 'UTF-8') ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="form-text">Solo se mostrarán los vehículos asignados al conductor.</small>
                    <div class="invalid-feedback">Seleccione un conductor.</div>
                </div>

                <div class="form-group col-12 col-md-6">
                    <label for="vehiculo" class="form-label">Vehículo</label>
                    <select id="vehiculo" name="id_vehiculo" class="form-control" required disabled>
                        <option value="">-- Seleccione primero un conductor --</option>
                    </select>
                    <small class="form-text">Matrícula y modelo del vehículo asignado.</small>
                    <div class="invalid-feedback">Seleccione un vehículo.</div>
                </div>
            </div>

            <!-- Fila 2: Kilometraje y Horas (ambos opcionales, al menos uno) -->
            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="kilometraje" class="form-label">Kilometraje semanal <span class="text-muted"></span></label>
                    <input type="number" step="0.01" min="0" id="kilometraje" name="kilometraje"
                        class="form-control" placeholder="Ej: 15234.50">
                    <small class="form-text">Si existe un <strong>próximo mantenimiento (km)</strong>, el valor debe ser menor.</small>
                </div>

                <div class="form-group col-12 col-md-6">
                    <label for="horas" class="form-label">Horas semanales <span class="text-muted"></span></label>
                    <input type="time" id="horas" name="horas" class="form-control">
                    <small class="form-text">Tiempo acumulado semanal, si aplica.</small>
                </div>
            </div>

            <!-- Fila 3: Referencia (km/hora próxima) -->
            <div class="form-row">
                <div class="form-group col-12">
                    <label class="form-label">Referencia último mantenimiento</label>
                    <input type="text" id="info_mant" class="form-control" value="Seleccione un vehículo" readonly>
                    <small class="form-text">Se muestra el límite (kilometraje próximo / hora próxima) si existe.</small>
                </div>
            </div>

            <!-- Acciones -->
            <div class="actions-row">
                <div class="actions-right">
                    <a href="kilometrajes.php" class="btn btn-secondary btn-rounded">Atrás</a>
                    <button type="submit" class="btn btn-primary btn-rounded" name="btnregistrar_km" value="ok">
                        Registrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require('./layout/footer.php'); ?>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Validación: conductor y vehículo requeridos; además, exigir al menos KM o HORAS
    (function() {
        function validateForm(form) {
            let ok = true;

            // Requeridos básicos
            ['#conductor', '#vehiculo'].forEach(sel => {
                const el = form.querySelector(sel);
                if (!el || !(el.value || '').trim()) {
                    el.classList.add('is-invalid');
                    ok = false;
                } else {
                    el.classList.remove('is-invalid');
                }
            });

            // Al menos uno: kilometraje o horas
            const km = (form.querySelector('#kilometraje')?.value || '').trim();
            const hs = (form.querySelector('#horas')?.value || '').trim();
            if (km === '' && hs === '') {
                ok = false;
                form.querySelector('#kilometraje')?.classList.add('is-invalid');
                form.querySelector('#horas')?.classList.add('is-invalid');
            } else {
                form.querySelector('#kilometraje')?.classList.remove('is-invalid');
                form.querySelector('#horas')?.classList.remove('is-invalid');
            }

            form.querySelector('.form-error-summary').style.display = ok ? 'none' : 'block';
            return ok;
        }

        document.addEventListener('submit', function(e) {
            const f = e.target.closest('form.js-validate');
            if (!f) return;
            if (!validateForm(f)) {
                e.preventDefault();
                (f.querySelector('.is-invalid') || {}).focus?.();
            }
        });
    })();

    // Cargar vehículos por conductor (AJAX)
    $('#conductor').on('change', function() {
        const id = this.value;
        const $veh = $('#vehiculo');
        const $info = $('#info_mant');
        $veh.prop('disabled', true).html('<option value="">Cargando...</option>');
        $info.val('Buscando mantenimiento...');
        if (!id) {
            $veh.html('<option value="">-- Seleccione primero un conductor --</option>');
            $info.val('Seleccione un vehículo');
            return;
        }
        $.getJSON('../controlador/controlador_ajax_vehiculos_por_conductor.php', {
                id_conductor: id
            })
            .done(function(resp) {
                const items = resp?.vehiculos || [];
                if (items.length === 0) {
                    $veh.html('<option value="">(sin vehículos asignados)</option>');
                    $info.val('Sin datos');
                } else {
                    $veh.html('<option value="">-- Seleccione un vehículo --</option>');
                    items.forEach(v => {
                        const label = `${v.matricula} - ${v.marca||''} ${v.modelo||''}`.trim();
                        $veh.append(new Option(label, v.id_vehiculo));
                    });
                }
                $veh.prop('disabled', false);
            })
            .fail(function() {
                $veh.html('<option value="">Error cargando vehículos</option>');
                $veh.prop('disabled', false);
                $info.val('Error de consulta');
            });
    });

    // Al elegir vehículo, pedir límites de mantenimiento (km/hora) para mostrarlos
    $('#vehiculo').on('change', function() {
        const idv = this.value;
        const $info = $('#info_mant');
        if (!idv) {
            $info.val('Seleccione un vehículo');
            return;
        }
        $.getJSON('../controlador/controlador_ajax_vehiculos_por_conductor.php', {
                ultimo_mant: idv
            })
            .done(function(r) {
                const k = (r && r.kilometraje_proximo != null) ? r.kilometraje_proximo : null;
                const h = (r && r.hora_proxima != null) ? r.hora_proxima : null;

                const kmTxt = (k == null) ? 'Sin km próximo' : ('Km próximo: ' + k);
                const hrTxt = (!h || h === '00:00:00') ? 'Sin hora próxima' : ('Hora próxima: ' + h.substring(0, 5)); // HH:MM

                $info.val(kmTxt + ' | ' + hrTxt);
            })
            .fail(function() {
                $info.val('No se pudo obtener la referencia.');
            });
    });
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
                timer: 2600,
                timerProgressBar: true
            });
        });
    </script>
<?php endif; ?>