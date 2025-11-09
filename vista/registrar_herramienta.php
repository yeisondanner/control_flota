<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
    header('location:login/login.php');
    exit;
}

// Validar acceso de conductor: solo pueden ver kilometrajes
include "../modelo/validar_conductor.php";

include "../modelo/conexion.php";
include "../controlador/controlador_registrar_herramienta.php";

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
    /* Menú activo */
    ul li:nth-child(7) .activo {
        background: #0b96d6 !important;
    }

    /* Titular */
    h4.text-center.text-secondary {
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
        font-size: 0.95rem;
    }
</style>

<div class="page-content">
    <h4 class="text-center text-secondary">REGISTRO DE REPUESTO</h4>

    <div class="card-like">
        <form action="" method="POST" autocomplete="off" class="w-100 js-validate" novalidate>
            <div class="form-error-summary">Por favor, complete todos los campos obligatorios antes de registrar.</div>

            <!-- Fila 1 -->
            <div class="form-row">
                <div class="form-group col-12 col-md-6">
                    <label for="txtnombre" class="form-label">Nombre deL repuesto</label>
                    <input type="text" id="txtnombre" name="txtnombre" class="form-control" placeholder="Ej: Martillo" maxlength="100" required>
                    <small class="form-text">Nombre del repuesto que estás registrando.</small>
                    <div class="invalid-feedback">Ingrese el nombre del repuesto.</div>
                </div>

                <div class="form-group col-12 col-md-6">
                    <label for="txtdescripcion" class="form-label">Descripción</label>
                    <input type="text" id="txtdescripcion" name="txtdescripcion" class="form-control" placeholder="Descripción de la herramienta" maxlength="100" required>
                    <small class="form-text">Breve descripción del repuesto.</small>
                    <div class="invalid-feedback">Ingrese la descripción.</div>
                </div>
            </div>

            <!-- Fila 4 -->
            <div class="actions-row">
                <div class="actions-right">
                    <a href="herramientas.php" class="btn btn-secondary btn-rounded">Atrás</a>
                    <button type="submit" class="btn btn-primary btn-rounded" value="ok" name="btnregistrar">
                        Registrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require('./layout/footer.php'); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    // Validación simple
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