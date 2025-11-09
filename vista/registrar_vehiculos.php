<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
  header('location:login/login.php');
  exit;
}

include "../modelo/conexion.php";
/* ⬇️ Controlador antes de HTML (PRG al guardar) */
include "../controlador/controlador_registrar_vehiculo.php";

/* Flash */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
  /* Menú activo */
  ul li:nth-child(5) .activo {
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
    float: none !important;
    position: static !important;
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
  <h4 class="text-center text-secondary">REGISTRO DE VEHÍCULO</h4>

  <div class="card-like">
    <form action="" method="POST" autocomplete="off" class="w-100 js-validate" novalidate>

      <div class="form-error-summary">Por favor, complete todos los campos obligatorios antes de registrar.</div>

      <!-- Fila 1 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtmatricula" class="form-label">Matrícula</label>
          <input type="text" id="txtmatricula" name="txtmatricula" class="form-control"
            placeholder="Ej: ABC1234" maxlength="15" required>
          <small class="form-text">Número de matrícula del vehículo.</small>
          <div class="invalid-feedback">Ingrese la matrícula.</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtmarca" class="form-label">Marca</label>
          <input type="text" id="txtmarca" name="txtmarca" class="form-control"
            placeholder="Ej: Toyota" maxlength="50" required>
          <small class="form-text">Marca del vehículo.</small>
          <div class="invalid-feedback">Ingrese la marca.</div>
        </div>
      </div>

      <!-- Fila 2 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtmodelo" class="form-label">Modelo</label>
          <input type="text" id="txtmodelo" name="txtmodelo" class="form-control"
            placeholder="Ej: Corolla" maxlength="50" required>
          <small class="form-text">Modelo del vehículo.</small>
          <div class="invalid-feedback">Ingrese el modelo.</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txttipo" class="form-label">Tipo</label>
          <input type="text" id="txttipo" name="txttipo" class="form-control"
            placeholder="Ej: Sedan" maxlength="30" required>
          <small class="form-text">Tipo de vehículo.</small>
          <div class="invalid-feedback">Ingrese el tipo de vehículo.</div>
        </div>
      </div>

      <!-- Fila 3 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtaño" class="form-label">Año</label>
          <input type="number" id="txtaño" name="txtaño" class="form-control" placeholder="Ej: 2020" required>
          <small class="form-text">Año del vehículo.</small>
          <div class="invalid-feedback">Ingrese el año del vehículo.</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtkilometraje" class="form-label">Kilometraje</label>
          <input type="decimal" id="txtkilometraje" name="txtkilometraje" class="form-control" placeholder="Ej: 100000" required>
          <small class="form-text">Kilometraje del vehículo.</small>
          <div class="invalid-feedback">Ingrese el kilometraje del vehículo.</div>
        </div>
      </div>

      <!-- Fila 4 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtunidadminera" class="form-label">Unidad Minera</label>
          <select id="txtunidadminera" name="txtunidadminera" class="form-control" required>
            <option value="">-- Seleccione una unidad minera --</option>
            <?php
            $unidades = $conexion->query("SELECT id_unidadminera, nombre_unidad FROM unidad_minera");
            while ($unidad = $unidades->fetch_object()) {
              echo "<option value=\"{$unidad->id_unidadminera}\">{$unidad->nombre_unidad}</option>";
            }
            ?>
          </select>
          <small class="form-text">Selecciona la unidad minera.</small>
          <div class="invalid-feedback">Seleccione una unidad minera.</div>
        </div>
      </div>

      <!-- Fila 5 -->
      <div class="actions-row">
        <div class="actions-right">
          <a href="vehiculos.php" class="btn btn-secondary btn-rounded">Atrás</a>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // Validación personalizada simple
  (function() {
    function validateForm(form) {
      let valido = true;
      const requiredFields = form.querySelectorAll('[required]');
      requiredFields.forEach(campo => {
        const value = (campo.value || '').trim();
        let ok = value !== '';
        if (campo.id === 'txtdni' && value !== '') ok = /^\d{8}$/.test(value);
        if (!ok) {
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

    document.addEventListener('input', function(e) {
      const el = e.target;
      if (el.hasAttribute('required') && el.value.trim() !== '') {
        el.classList.remove('is-invalid');
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