<?php
session_start();
if (empty($_SESSION['nombre']) && empty($_SESSION['apellidos'])) {
  header('location:login/login.php');
  exit;
}

include "../modelo/conexion.php";
/* ⬇️ Controlador antes de HTML (PRG al guardar) */
include "../controlador/controlador_registrar_conductor.php";

/* Flash */
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php require('./layout/topbar.php'); ?>
<?php require('./layout/sidebar.php'); ?>

<style>
  /* Menú activo */
  ul li:nth-child(4) .activo {
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
  <h4 class="text-center text-secondary">REGISTRO DE CONDUCTOR</h4>

  <div class="card-like">
    <form action="" method="POST" autocomplete="off" class="w-100 js-validate" novalidate>

      <div class="form-error-summary">Por favor, complete todos los campos obligatorios antes de registrar.</div>

      <!-- Fila 1 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtnombre" class="form-label">Nombre</label>
          <input type="text" id="txtnombre" name="txtnombre" class="form-control"
            placeholder="Ej: Carlos Alberto" maxlength="80" required>
          <small class="form-text">Nombres del conductor.</small>
          <div class="invalid-feedback">Ingrese el nombre.</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtapellidos" class="form-label">Apellidos</label>
          <input type="text" id="txtapellidos" name="txtapellidos" class="form-control"
            placeholder="Ej: Pérez Humán" maxlength="120" required>
          <small class="form-text">Apellidos paterno y materno.</small>
          <div class="invalid-feedback">Ingrese los apellidos.</div>
        </div>
      </div>

      <!-- Fila 2 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtdni" class="form-label">DNI</label>
          <input type="text" id="txtdni" name="txtdni" class="form-control"
            placeholder="8 dígitos" inputmode="numeric" maxlength="8" required>
          <small class="form-text">Solo números, exactamente 8 dígitos.</small>
          <div class="invalid-feedback">Ingrese un DNI válido de 8 dígitos.</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtdireccion" class="form-label">Dirección</label>
          <input type="text" id="txtdireccion" name="txtdireccion" class="form-control"
            placeholder="Ej: Calle Ficticia 123" maxlength="150" required>
          <small class="form-text">Dirección de residencia.</small>
        </div>
      </div>

      <!-- Fila 3 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txttelefono" class="form-label">Teléfono</label>
          <input type="text" id="txttelefono" name="txttelefono" class="form-control"
            placeholder="Ej: 999-999-999" maxlength="15" required>
          <small class="form-text">Número de teléfono de contacto.</small>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtemail" class="form-label">Correo electrónico</label>
          <input type="email" id="txtemail" name="txtemail" class="form-control"
            placeholder="Ej: ejemplo@correo.com" maxlength="100" required>
          <small class="form-text">Correo electrónico de contacto.</small>
        </div>
      </div>

      <!-- Fila 4 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtfechanac" class="form-label">Fecha de nacimiento</label>
          <input type="date" id="txtfechanac" name="txtfechanac" class="form-control" required>
          <small class="form-text">Fecha de nacimiento del conductor.</small>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtnlicencia" class="form-label">Número de licencia</label>
          <input type="text" id="txtnlicencia" name="txtnlicencia" class="form-control"
            placeholder="Ej: M12345678" maxlength="30" required>
          <small class="form-text">Número exacto que figura en la licencia.</small>
          <div class="invalid-feedback">Ingrese el número de licencia.</div>
        </div>
      </div>

      <!-- Fila 5 -->
      <div class="form-row">
        <div class="form-group col-12 col-md-6">
          <label for="txtcatlicencia" class="form-label">Categoría de licencia</label>
          <select id="txtcatlicencia" name="txtcatlicencia" class="form-control" required>
            <option value="">-- Seleccione una categoría --</option>
            <option value="A-I">A-I</option>
            <option value="A-IIa">A-IIa</option>
            <option value="A-IIb">A-IIb</option>
            <option value="A-IIIa">A-IIIa</option>
            <option value="A-IIIb">A-IIIb</option>
            <option value="A-IIIc">A-IIIc</option>
            <option value="B-I">B-I</option>
            <option value="B-IIa">B-IIa</option>
            <option value="B-IIb">B-IIb</option>
          </select>
          <small class="form-text">Categoría según tu regulación.</small>
          <div class="invalid-feedback">Seleccione una categoría.</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtfvenc" class="form-label">Fecha de vencimiento (opcional)</label>
          <input type="date" id="txtfvenc" name="txtfvenc" class="form-control">
          <small class="form-text">Dejar en blanco si no corresponde.</small>
        </div>
      </div>

      <!-- Fila 6: Credenciales de acceso -->
      <div class="form-row">
        <div class="form-group col-12 col-md-12">
          <label for="txtusuario" class="form-label">Usuario (login)</label>
          <input type="text" id="txtusuario" name="txtusuario" class="form-control"
            placeholder="Ej: c.perez" maxlength="32" required>
          <small class="form-text">4–32 caracteres. Letras, números, punto, guion o guion_bajo.</small>
          <div class="invalid-feedback">Ingrese un usuario válido.</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtclave" class="form-label">Contraseña</label>
          <input type="password" id="txtclave" name="txtclave" class="form-control"
            placeholder="Mín. 6 caracteres" minlength="6" required>
          <div class="invalid-feedback">Ingrese una contraseña (mín. 6).</div>
        </div>

        <div class="form-group col-12 col-md-6">
          <label for="txtclave2" class="form-label">Repetir contraseña</label>
          <input type="password" id="txtclave2" name="txtclave2" class="form-control"
            placeholder="Repita la contraseña" minlength="6" required>
          <div class="invalid-feedback">Las contraseñas no coinciden.</div>
        </div>
      </div>

      <!-- Rol por defecto -->
      <input type="hidden" name="rol" value="Conductor">

      <!-- Acciones -->
      <div class="actions-row">
        <div class="actions-right">
          <a href="conductores.php" class="btn btn-secondary btn-rounded">Atrás</a>
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
  // DNI: solo números, 8 dígitos
  (function() {
    const dni = document.getElementById('txtdni');
    if (!dni) return;

    function sanitize() {
      dni.value = dni.value.replace(/\D/g, '').slice(0, 8);
    }
    ['input', 'paste', 'blur', 'keyup'].forEach(evt => dni.addEventListener(evt, () => setTimeout(sanitize, 0)));
  })();

  // Licencia a MAYÚSCULAS y límite 30
  (function() {
    const lic = document.getElementById('txtnlicencia');
    if (!lic) return;
    lic.addEventListener('input', function() {
      this.value = this.value.toUpperCase().slice(0, 30);
    });
  })();

  // Usuario: 4-32, letras/números/._-
  (function() {
    const u = document.getElementById('txtusuario');
    if (!u) return;
    u.addEventListener('input', function() {
      this.value = this.value.replace(/[^A-Za-z0-9._-]/g, '').slice(0, 32);
    });
  })();

  // Validación personalizada
  (function() {
    function validateForm(form) {
      let valido = true;
      const requiredFields = form.querySelectorAll('[required]');
      requiredFields.forEach(campo => {
        const value = (campo.value || '').trim();
        let ok = value !== '';

        if (campo.id === 'txtdni' && value !== '') ok = /^\d{8}$/.test(value);
        if (campo.id === 'txtusuario' && value !== '') ok = /^[A-Za-z0-9._-]{4,32}$/.test(value);
        if (campo.id === 'txtclave' && value !== '') ok = value.length >= 6;
        if (campo.id === 'txtclave2' && value !== '') ok = (value === (form.querySelector('#txtclave')?.value || ''));

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